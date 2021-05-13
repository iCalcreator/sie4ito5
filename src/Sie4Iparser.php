<?php
/**
 * Sie4Ito5   PHP Sie 4I to 5 conversion package
 *
 * This file is a part of Sie4Ito5
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @version   1.0
 * @license   Subject matter of licence is the software Sie4Ito5.
 *            The above copyright, link, package and version notices,
 *            this licence notice shall be included in all copies or substantial
 *            portions of the Sie4Ito5.
 *
 *            Sie4Ito5 is free software: you can redistribute it and/or modify
 *            it under the terms of the GNU Lesser General Public License as
 *            published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *            Sie4Ito5 is distributed in the hope that it will be useful,
 *            but WITHOUT ANY WARRANTY; without even the implied warranty of
 *            MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *            GNU Lesser General Public License for more details.
 *
 *            You should have received a copy of the GNU Lesser General Public License
 *            along with Sie4Ito5. If not, see <https://www.gnu.org/licenses/>.
 */
declare( strict_types = 1 );
namespace Kigkonsult\Sie4Ito5;

use DateTime;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Asit\It;
use Kigkonsult\Sie4Ito5\Util\ArrayUtil;
use Kigkonsult\Sie4Ito5\Util\FileUtil;
use Kigkonsult\Sie4Ito5\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Dto\AccountingCurrencyType;
use Kigkonsult\Sie5Sdk\Dto\AccountsTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\AccountTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\CompanyTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\DimensionsTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\DimensionTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\FileCreationType;
use Kigkonsult\Sie5Sdk\Dto\FileInfoTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\JournalEntryTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\JournalTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\LedgerEntryTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\ObjectReferenceType;
use Kigkonsult\Sie5Sdk\Dto\ObjectType;
use Kigkonsult\Sie5Sdk\Dto\OriginalEntryInfoType;
use Kigkonsult\Sie5Sdk\Dto\SieEntry;
use Kigkonsult\Sie5Sdk\Dto\SoftwareProductType;
use RuntimeException;

use function array_map;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_file;
use function is_string;
use function ksort;
use function sprintf;
use function trim;

class Sie4Iparser implements Sie4IInterface
{
    /**
     * posterna förekommer i följande ordning:
     * 1 Flaggpost
     * 2 Identifikationsposter
     * 3 Kontoplansuppgifter
     * 4 Saldoposter/Verifikationsposter
     */

    /**
     * Identifikationsposter
     *
     * @var array  may NOT occur in order in Sie4I
     */
    private static $IDLABELS = [
        self::PROGRAM,
        self::FORMAT,
        self::GEN,
        self::SIETYP,
        self::PROSA,
        self::FTYP,
        self::FNR,
        self::ORGNR,
        self::ADRESS,
        self::FNAMN,
        self::RAR,
        self::TAXAR,
        self::KPTYP,
        self::VALUTA,
    ];

    /**
     * Kontoplansuppgifter
     *
     * @var array  may NOT occur in order in Sie4I
     */
    private static $ACCOUNTLABELS = [
        self::KONTO,
        self::KTYP,
        self::ENHET,
        self::SRU,
        self::DIM,
        self::UNDERDIM,
        self::OBJEKT,
    ];

    /**
     * Saldoposter/Verifikationsposter
     *
     * @var array  may NOT occur in order in Sie4I
     */
    private static $LEDGERENTRYLABELS = [
        self::VER,
        self::TRANS,
        self::RTRANS,
        self::BTRANS,
    ];

    /**
     * Kontrollsummeposter
     *
     * @var array
     */
    private static $CHECKSUMLABELS = [
        self::KSUMMA
    ];

    /**
     * Input file rows, managed by Asit\It
     *
     * @var It
     */
    private $input = null;

    /**
     * @var SieEntry
     */
    private $sieEntry = null;

    private $currentJournalEntryTypeEntry = null;

    /**
     * @var array
     */
    private $postReadGroupActionKeys = [];

    /**
     * Return instance
     *
     * @return static
     */
    public static function factory() : self
    {
        return new self();
    }

    /**
     * Set input from Sie4I file, -array, -string
     *
     * @param string|array $source
     * @return static
     * @throws InvalidArgumentException
     */
    public function setInput( $source ) : self
    {
        static $TRIM      = [ StringUtil::class, 'trimString' ];
        static $TAB2SPACE = [ StringUtil::class, 'tab2Space' ];
        static $FMT1      = 'Unvalid source';
        switch( true ) {
            case is_array( $source ) :
                $input = $source;
                break;
            case ( ! is_string( $source )) :
                throw new InvalidArgumentException( $FMT1, 1111 );
            case is_file( $source ) :
                FileUtil::assertReadFile((string) $source );
                $input = FileUtil::readFile((string) $source );
                break;
            default :
                $input = StringUtil::string2Arr(
                    StringUtil::convEolChar((string) $source )
                );
                break;
        } // end switch
        $this->input = new It(
            array_map( $TRIM, array_map( $TAB2SPACE, $input ))
        );
        $this->validateInput();
        return $this;
    }

    /**
     * Validate #FLAGGA, #SIETYP == 4 and at least one #VER must exist (in order)
     *
     * @throws InvalidArgumentException
     */
    private function validateInput()
    {
        static $FMT1 = 'Input saknar poster';
        static $FMT2 = 'Ogiltig 1:a post';
        static $FMT3 = 'FLAGGpost saknas';
        static $FOUR = '4';
        static $FMT4 = 'SIETYP 4 saknas';
        static $FMT5 = 'ORGNR saknas, krävs aV SieEntry';
        static $FMT6 = '#VER saknas ';
        if( empty( $this->input->count())) {
            throw new InvalidArgumentException( $FMT1, 1211 );
        }
        $this->input->rewind();
        if( ! $this->input->valid()) {
            throw new InvalidArgumentException( $FMT2, 1212 );
        }
        $flaggaExist = $sieType4Exist = $orgNrExist = $verExist = false;
        while( $this->input->valid()) {
            $post = $this->input->current();
            switch( true ) {
                case empty( $post ) :
                    break;
                case StringUtil::startsWith( $post, self::FLAGGA ) :
                    $flaggaExist = true;
                    break;
                case ( $flaggaExist &&
                    StringUtil::startsWith( $post, self::SIETYP ) &&
                    StringUtil::isIn( $FOUR, StringUtil::after( self::SIETYP, $post ))) :
                    $sieType4Exist = true;
                    break;
                case ( $flaggaExist &&
                    $sieType4Exist &&
                    StringUtil::startsWith( $post, self::ORGNR )) :
                    $orgNrExist = true;
                    break;
                case ( $flaggaExist &&
                    $sieType4Exist &&
                    $orgNrExist &&
                    StringUtil::startsWith( $post, self::VER )) :
                    $verExist = true;
                    break;
            } // end switch
            $this->input->next();
        } // end while
        if( ! $flaggaExist ) {
            throw new InvalidArgumentException( $FMT3, 1213 );
        }
        if( ! $sieType4Exist ) {
            throw new InvalidArgumentException( $FMT4, 1214 );
        }
        if( ! $orgNrExist ) {
            throw new InvalidArgumentException( $FMT5, 1215 );
        }
        if( ! $verExist ) {
            throw new InvalidArgumentException( $FMT6, 1216 );
        }
    }

    /**
     * Init SieEntry, prepare for Sie5 XML write
     */
    private function initSieEntry()
    {
        $this->sieEntry = sieEntry::factory()
            ->setXMLattribute( SieEntry::XMLNS,              SieEntry::SIE5URI )
            ->setXMLattribute( SieEntry::XMLNS_XSI,          SieEntry::XMLSCHEMAINSTANCE )
            ->setXMLattribute( SieEntry::XMLNS_XSD,          SieEntry::XMLSCHEMA )
            ->setXMLattribute( SieEntry::XSI_SCHEMALOCATION, SieEntry::SIE5SCHEMALOCATION )
            ->setFileInfo(
                FileInfoTypeEntry::factory()
                    ->setCompany( CompanyTypeEntry::factory())
            );
    }

    /**
     * Parse Sie4I, opt input from Sie4I file, -array, -string, return SieEntry
     *
     * @param mixed $source
     * @return SieEntry
     * @throws RuntimeException
     */
    public function parse4I( $source = null ) : SieEntry
    {
        static $FMT1 = 'Input error (#%d) on post %s';
        if( ! empty( $source )) {
            $this->setInput( $source );
        }
        $this->initSieEntry();
        $this->input->rewind();
        $currentGroup = 0;
        $post = $prevLabel = null;
        $this->postReadGroupActionKeys = [];
        while( $this->input->valid()) {
            $post = $this->input->current();
            if( empty( $post )) {
                $this->input->next();
                continue;
            }
            $post = StringUtil::cp437toUtf8( $post );
            list( $label, $content ) = StringUtil::splitPost( $post );
            switch( true ) {
                case (( 0 === $currentGroup ) && ( self::FLAGGA == $label )) :
                    $currentGroup = 1;
                    break;

                case ( in_array( $currentGroup, [ 1, 2 ] )
                    && in_array( $label, self::$IDLABELS )) :
                    $currentGroup = 2;
                    $this->readIdData( $label, $content );
                    break;
                case (( 2 === $currentGroup ) && empty( $label )) :
                    // data content for previous Label
                    $this->readIdData( $prevLabel, $content );
                    break;

                case ( in_array( $currentGroup, [ 2, 3 ] )
                    && in_array( $label, self::$ACCOUNTLABELS )) :
                    if( 2 == $currentGroup ) {
                        // finish off group 2 actions
                        $this->postReadGroupAction();
                        $currentGroup = 3;
                    }
                    $this->readAccountData( $label, $content );
                    break;
                case (( 3 === $currentGroup ) && empty( $label )) :
                    // data content for previous Label
                    $this->readAccountData( $prevLabel, $content );
                    break;

                case ( in_array( $currentGroup, [ 2, 3, 4 ] )
                    && in_array( $label, self::$LEDGERENTRYLABELS )) :
                    if( in_array( $currentGroup, [ 2, 3 ] )) {
                        // finish off group (2-)3 actions
                        $this->postReadGroupAction();
                        $currentGroup = 4;
                    }
                    $this->readLedgerEntryData( $label, $content );
                    break;
                case (( 4 === $currentGroup ) && empty( $label )) :
                    // data content for previous Label
                    $this->readLedgerEntryData( $prevLabel, $content );
                    break;

                default :
                    throw new RuntimeException( sprintf( $FMT1, 1, $post ), 1411 );
            } // end switch
            if( ! empty( $label )) {
                $prevLabel = $label;
            }
            $this->input->next();
        } // end while
        if( ! empty( $this->postReadGroupActionKeys )) {
            // finish off group 4 actions
            $this->postReadGroupAction();
        }
        if( 4 !== $currentGroup ) {
            throw new RuntimeException( sprintf( $FMT1, 2, $post ), 1414);
        }
        return $this->sieEntry;
    }

    /**
     * Manage Sie4I 'Identifikationsposter'
     *
     * Note för #GEN
     *   if 'sign' is missing, '#PROGRAM programnamn' is used
     *
     * @param string $label
     * @param array  $content
     * @throws RuntimeException
     */
    private function readIdData( string $label, array $content )
    {
        static $FMT0 = '%s: %s, %s';
        $fileInfo = $this->sieEntry->getFileInfo();
        switch( $label ) {
            /**
             * Vilket program som genererat filen
             * Obligatorisk
             * #PROGRAM programnamn version
             */
            case self::PROGRAM :
                $fileInfo->setSoftwareProduct(
                    SoftwareProductType::factoryNameVersion( $content[0], $content[1] )
                );
                // prepare if missing #GEN sign
                $this->postReadGroupActionKeys[self::GEN] = $content[0];
                break;

            /**
             * Vilken teckenuppsättning som använts
             *
             * Obligatorisk
             * #FORMAT PC8
             * SKA vara IBM PC 8-bitars extended ASCII (Codepage 437)
             * https://en.wikipedia.org/wiki/Code_page_437
             */
            case self::FORMAT :
                // skip
                break;

            /**
             * När och av vem som filen genererats
             * #GEN datum sign
             * Obligatorisk (sign opt)
             */
            case self::GEN :
                try {
                    $dateTime = new DateTime( $content[0] );
                }
                catch( Exception $e ) {
                    $msg = sprintf( $FMT0, self::GEN, $content[0], $e->getMessage());
                    throw new RuntimeException( $msg, 1511, $e );
                }
                if( ! ArrayUtil::arrayKeyExists( $content, 1 )) {
                    $fileInfo->setFileCreation(
                        FileCreationType::factory()->setTime( $dateTime )
                    );
                }
                else {
                    // undo #GEN prepare (above) due it is found
                    unset( $this->postReadGroupActionKeys[self::GEN] );
                    $fileInfo->setFileCreation(
                        FileCreationType::factoryByTime( $content[1], $dateTime )
                    );
                }
                break;

            /**
             * Vilken typ av SIE-formatet filen följer
             *
             * #SIETYP typnr
             * SKA vara 4, tidigare evaluerat
             * Obligatorisk
             */
            case self::SIETYP :
                // skip
                break;

            /**
             * Fri kommentartext kring filens innehåll
             *
             * #PROSA text
             * valfri
             */
            case self::PROSA :
                // skip
                break;

            /**
             * Företagstyp
             *
             * #FTYP Företagstyp
             * valfri
             */
            case self::FTYP :
                // skip
                break;

            /**
             * Redovisningsprogrammets internkod för exporterat företag
             *
             * #FNR företagsid
             * valfri
             */
            case self::FNR :
                $fileInfo->getCompany()->setClientId( $content[0] );
                break;

            /**
             * Organisationsnummer för det företag som exporterats
             *
             * #ORGNR orgnr förvnr verknr
             * förvnr : anv då ensk. person driver flera ensk. firmor (ordningsnr)
             * verknr : anv ej
             * valfri, MEN orgnr obligatoriskt i SieEntry (FileInfoTypeEntry/CompanyTypeEntry)
             */
            case self::ORGNR :
                $company = $fileInfo->getCompany();
                $company->setOrganizationId( $content[0] );
                $company->setMultiple(
                    (
                        ArrayUtil::arrayKeyExists( $content, 1 )
                            ? (int) $content[1]
                            : 1
                    )
                );
                break;

            /**
             * Adressuppgifter för det aktuella företaget
             *
             * #ADRESS kontakt utdelningsadr postadr tel
             * valfri
             */
            case self::ADRESS :
                // skip
                break;

            /**
             * Fullständigt namn för det företag som exporterats
             *
             * #FNAMN företagsnamn
             * Obligatorisk men valfri i SieEntry (FileInfoTypeEntry/CompanyTypeEntry)
             */
            case self::FNAMN :
                $company = $fileInfo->getCompany();
                $company->setName( $content[0] );
                break;

            /**
             * Räkenskapsår från vilket exporterade data hämtats
             *
             * #RAR årsnr start slut
             * valfri
             */
            case self::RAR :
                // skip
                break;

            /**
             * Taxeringsår för deklarations- information (SRU-koder)
             *
             * #TAXAR år
             * valfri
             */
            case self::TAXAR :
                // skip
                break;

            /**
             * Kontoplanstyp
             *
             * #KPTYP typ
             * valfri
             */
            case self::KPTYP :
                // skip
                break;

            /**
             * Redovisningsvaluta
             *
             * #VALUTA valutakod
             * valfri
             */
            case self::VALUTA :
                $fileInfo->setAccountingCurrency(
                    AccountingCurrencyType::factory()->setCurrency( $content[0] )
                );
                break;
        } // end switch
    }

    /**
     * Manage Sie4I  'Kontoplansuppgifter'
     *
     * #SRU and #UNDERDIM are skipped
     * #KONTO etc and #DIM/#OBJEKT : prepare for postGroup actions
     *
     * @param string $label
     * @param array  $content
     */
    private function readAccountData( string $label, array $content )
    {
        switch( $label ) {
            /**
             * Kontouppgifter
             *
             * #KONTO kontonr kontonamn
             * valfri
             */
            case self::KONTO :
                if( ! isset( $this->postReadGroupActionKeys[self::KONTO][$content[0]] )) {
                    $this->postReadGroupActionKeys[self::KONTO][$content[0]] = [];
                }
                // kontonr[0] = kontonamn
                $this->postReadGroupActionKeys[self::KONTO][$content[0]][0] = $content[1];
                break;
            /**
             * Kontotyp
             *
             * #KTYP kontonr  kontotyp
             * valfri
             */
            case self::KTYP :
                if( ! isset( $this->postReadGroupActionKeys[self::KONTO][$content[0]] )) {
                    $this->postReadGroupActionKeys[self::KONTO][$content[0]] = [];
                }
                // kontonr[1] = kontotyp
                $this->postReadGroupActionKeys[self::KONTO][$content[0]][1] = $content[1];
                break;

            /**
             * Enhet vid kvantitetsredovisning
             *
             * #ENHET kontonr enhet
             * valfri
             */
            case self::ENHET :
                if( ! isset( $this->postReadGroupActionKeys[self::KONTO][$content[0]] )) {
                    $this->postReadGroupActionKeys[self::KONTO][$content[0]] = [];
                }
                // kontonr[2] = enhet
                $this->postReadGroupActionKeys[self::KONTO][$content[0]][2] = $content[1];
                break;

            /**
             * RSV-kod för standardiserat räkenskapsutdrag
             *
             * #SRU konto SRU-kod
             * valfri
             */
            case self::SRU :
                break;

            /**
             * Dimension
             *
             * #DIM dimensionsnr namn
             * valfri
             */
            case self::DIM :
                if( ! isset( $this->postReadGroupActionKeys[self::DIM] )) {
                    $this->postReadGroupActionKeys[self::DIM] = [];
                }
                $dimensionsnr = $content[0];
                if( ! isset( $this->postReadGroupActionKeys[self::DIM][$dimensionsnr] )) {
                    $this->postReadGroupActionKeys[self::DIM][$dimensionsnr] = [];
                }
                // dimensionsnr[0] => namn
                $this->postReadGroupActionKeys[self::DIM][$dimensionsnr][0] = $content[1];
                break;

            /**
             * Underdimension
             *
             * #UNDERDIM dimensionsnr namn superdimension
             * valfri
             */
            case self::UNDERDIM :
                break;

            /**
             * Objekt
             *
             * #OBJEKT dimensionsnr objektnr objektnamn
             * valfri
             */
            case self::OBJEKT :
                if( ! isset( $this->postReadGroupActionKeys[self::DIM] )) {
                    $this->postReadGroupActionKeys[self::DIM] = [];
                }
                $dimensionsnr = $content[0];
                if( ! isset( $this->postReadGroupActionKeys[self::DIM][$dimensionsnr] )) {
                    $this->postReadGroupActionKeys[self::DIM][$dimensionsnr] = [];
                }
                if( ! isset( $this->postReadGroupActionKeys[self::DIM][$dimensionsnr][self::OBJEKT] )) {
                    $this->postReadGroupActionKeys[self::DIM][$dimensionsnr][self::OBJEKT] = [];
                }
                // dimensionsnr[self::OBJEKT][objektnr] = objektnamn
                $this->postReadGroupActionKeys[self::DIM][$dimensionsnr][self::OBJEKT][$content[1]] = $content[2];
                break;
        } // end switch
    }

    /**
     * Manage 'Verifikationsposter'
     *
     * Note för #VER
     *   if 'regdatum' is missing, 'verdatum' is used
     *   if 'sign' is missing, #GEN is used
     *
     * Note för #TRANS
     *   only support for 'dimensionsnummer och objektnummer' in the 'objektlista'
     *   i.e. no support for 'hierarkiska dimensioner'
     *
     * @param string $label
     * @param array  $content
     * @throws RuntimeException
     */
    private function readLedgerEntryData( string $label, array $content )
    {
        if( in_array( $content[0], StringUtil::$CURLYBRACKETS )) {
            return;
        }
        switch( $label ) {
            /**
             * Verifikationspost
             *
             * valfri
             */
            case self::VER :
                $this->readLedgerEntryVERData( $content );
                break;

            /**
             * Transaktionspost (inom Verifikationspost)
             *
             * valfri
             */
            case self::TRANS :
                $this->readLedgerEntryTRANSData( $content );
                break;

            /**
             * Tillagd transaktionspost
             *
             * #RTRANS kontonr {objektlista} belopp transdat transtext kvantitet sign
             * valfri
             */
            case self::RTRANS :
                // skip
                break;

            /**
             * Borttagen transaktionspost
             *
             * #BTRANS kontonr {objektlista} belopp transdat transtext kvantitet sign
             * valfri
             */
            case self::BTRANS :
                // skip
                break;
        } // end switch
    }

    /**
     * Manage #VER data
     *
     * #VER serie vernr verdatum vertext regdatum sign
     *
     * @param array $content
     */
    private function readLedgerEntryVERData( array $content )
    {
        static $FMT0 = '%s, %s';
        // check if journal id exists
        $journalTypeEntryFound = false;
        $journalTypeEntry      = null;
        $journals = $this->sieEntry->getJournal();
        if( ! empty( $journals )) {
            foreach( $journals as $journalTypeEntry ) {
                $journalTypeEntryId = $journalTypeEntry->getId();
                if( empty( $content[0] ) && empty( $journalTypeEntryId )) {
                    $journalTypeEntryFound = true;
                    break;
                }
                if( $content[0] == $journalTypeEntryId ) {
                    $journalTypeEntryFound = true;
                    break;
                }
            } // end foreach
        } // end if
        if( ! $journalTypeEntryFound ) {
            $journalTypeEntry = JournalTypeEntry::factory();
            $this->sieEntry->addJournal( $journalTypeEntry );
            if( ! empty( $content[0] )) {
                $journalTypeEntry->setId( $content[0] );
            }
        } // end if

        // save for later #TRANS use
        $this->currentJournalEntryTypeEntry = JournalEntryTypeEntry::factory();
        if( ! empty( $content[1] )) {
            $this->currentJournalEntryTypeEntry->setId((int) $content[1] );
        }
        try {
            $dateTime = new DateTime( $content[2] );
        }
        catch( Exception $e ) {
            $msg = sprintf( $FMT0, self::VER, $content[2], $e->getMessage());
            throw new RuntimeException( $msg, 1711, $e );
        }
        $this->currentJournalEntryTypeEntry->setJournalDate( $dateTime );
        if( ArrayUtil::arrayKeyExists( $content, 3 )) {
            $this->currentJournalEntryTypeEntry->setText( $content[3] );
        }

        $journalTypeEntry->addJournalEntry( $this->currentJournalEntryTypeEntry );
        if( ArrayUtil::arrayKeyExists( $content, 4 )) {
            try {
                $dateTime = new DateTime( $content[4] );
            }
            catch( Exception $e ) {
                $msg = sprintf( $FMT0, self::VER, $content[4], $e->getMessage());
                throw new RuntimeException( $msg, 1712, $e );
            }
        } // end if
        else {
            // if missing, set same as verdatum
            $dateTime = clone $dateTime;
        }

        if( ! ArrayUtil::arrayKeyExists( $content, 5 )) {
            // fetch sign from #GEN
            $content[5] = $this->sieEntry->getFileInfo()->getFileCreation()->getBy();
        }
        $this->currentJournalEntryTypeEntry->setOriginalEntryInfo(
            OriginalEntryInfoType::factoryByDate( $content[5], $dateTime )
        );
    }

    /**
     * Manage #TRANS data
     *
     * #TRANS kontonr {objektlista} belopp transdat(opt) transtext(opt) kvantitet sign
     *
     * @param array $content
     */
    private function readLedgerEntryTRANSData( array $content )
    {
        static $FMT0          = '%s, %s';
        // $this->currentJournalEntryTypeEntry holds current journalEntryTypeEntry
        // created in #VER
        $ledgerEntryTypeEntry = LedgerEntryTypeEntry::factory();
        $this->currentJournalEntryTypeEntry->addLedgerEntry( $ledgerEntryTypeEntry );
        $ledgerEntryTypeEntry->setAccountId( $content[0] );
        // {objektlista} pairs of dimId/objectId
        if( ! empty( $content[1] )) {
            $dimObjList = explode( StringUtil::$SP1, trim( $content[1] ));
            $len        = count( $dimObjList ) - 1;
            for( $x1 = 0; $x1 < $len; $x1 += 2 ) {
                $x2     = $x1 + 1;
                $ledgerEntryTypeEntry->addLedgerEntryTypeEntry(
                    LedgerEntryTypeEntry::OBJECTREFERENCE,
                    ObjectReferenceType::factory()
                        ->setDimId( $dimObjList[$x1] )
                        ->setObjectId( $dimObjList[$x2] )
                );
            } // end for
        } // end if
        $ledgerEntryTypeEntry->setAmount( $content[2] );
        if( ArrayUtil::arrayKeyExists( $content, 3 )) {
            try {
                $dateTime = new DateTime( $content[3] );
            }
            catch( Exception $e ) {
                $msg = sprintf( $FMT0, self::TRANS, $content[3], $e->getMessage());
                throw new RuntimeException( $msg, 1713, $e );
            }
            $ledgerEntryTypeEntry->setLedgerDate( $dateTime );
        } // end if
        if( ArrayUtil::arrayKeyExists( $content, 4 )) {
            $ledgerEntryTypeEntry->setText( $content[4] );
        }
        if( ArrayUtil::arrayKeyExists( $content, 5 )) {
            $ledgerEntryTypeEntry->setQuantity( $content[5] );
        }
        // skip sign
    }

    /**
     * Due to labels in group are NOT required to be in order, aggregate or opt fix read missing parts here
     */
    private function postReadGroupAction()
    {
        if( empty( $this->postReadGroupActionKeys )) {
            return;
        }
        foreach( $this->postReadGroupActionKeys as $groupActionKey => $values ) {
            switch( $groupActionKey ) {

                case self::GEN :
                    $this->sieEntry->getFileInfo()->getFileCreation()->setBy( $values );
                    break;

                case self::DIM :
                    $this->postDimActions( $values );
                    break;

                case self::KONTO :
                    $this->postKontoActions( $values );
                    break;

            } // end switch
        } // end foreach
        $this->postReadGroupActionKeys = [];
    }

    /**
     * Create DimensionsTypeEntry for all DIM/OBECT
     *
     * @param array $dimValues
     */
    private function postDimActions( array $dimValues )
    {
        static $FMT4 = 'Namn för dimension %s saknas';
        $dimensions = $this->sieEntry->getDimensions();
        if( empty( $dimensions )) {
            $dimensions = DimensionsTypeEntry::factory();
            $this->sieEntry->setDimensions( $dimensions );
        }
        // $dimensionId[0] = namn
        // $dimensionId[self::OBJEKT][objektnr] = objektnamn
        ksort( $dimValues );
        foreach( $dimValues as $dimensionId => $dimensionData ) {
            if( ! isset( $dimensionData[0] )) {
                throw new RuntimeException( sprintf( $FMT4, $dimensionId ), 1815 );
            }
            $dimensionTypeEntry = DimensionTypeEntry::factoryIdName(
                $dimensionId,
                $dimensionData[0]
            );
            if( isset( $dimensionData[self::OBJEKT] )) {
                foreach( $dimensionData[self::OBJEKT] as $objectId => $objectName ) {
                    $dimensionTypeEntry->addObject(
                        ObjectType::factoryIdName((string) $objectId, $objectName )
                    );
                } // end foreach
            } // end if
            $dimensions->addDimension( $dimensionTypeEntry );
        } // end foreach
    }

    /**
     * Create AccountsTypeEntry for all KONTO/KTYP/ENHET
     *
     * @param array $kontoValues
     */
    private function postKontoActions( array $kontoValues )
    {
        static $KONTOTYPER = [
            'T' => AccountTypeEntry::ASSET,     // Tillgång
            'S' => AccountTypeEntry::LIABILITY, // Skuld
            'K' => AccountTypeEntry::COST,      // kostnad
            'I' => AccountTypeEntry::INCOME,    // Intäkt
        ];
        static $FMT1 = 'Namn saknas för konto ';
        static $FMT2 = 'Typ saknas för konto ';
        static $FMT3 = 'Ogiltig kontotyp för konto %s : >%s<';
        $accounts = $this->sieEntry->getAccounts();
        if( empty( $accounts )) {
            $accounts = AccountsTypeEntry::factory();
            $this->sieEntry->setAccounts( $accounts );
        }
        // kontoNr[0] = kontonamn
        // kontoNr[1] = kontotyp, Ska vara någon av typerna asset, liability, cost eller income.
        // kontoNr[2] = enhet
        ksort( $kontoValues );
        foreach( $kontoValues as $kontoNr => $kontoData ) {
            if( ! isset( $kontoData[0] )) {
                throw new RuntimeException( $FMT1 . $kontoNr, 1821 );
            }
            if( ! isset( $kontoData[1] )) {
                throw new RuntimeException( $FMT2 . $kontoNr, 1822 );
            }
            if( ! isset( $KONTOTYPER[$kontoData[1]] )) {
                throw new RuntimeException( sprintf( $FMT3, $kontoNr, $kontoData[1] ), 1823 );
            }
            $accountTypeEntry = AccountTypeEntry::factoryIdNameType(
                (string) $kontoNr,
                $kontoData[0],
                $KONTOTYPER[$kontoData[1]]
            );
            if( isset( $kontoData[2] )) {
                $accountTypeEntry->setUnit( $kontoData[2] );
            }
            $accounts->addAccount( $accountTypeEntry );
        } // end foreach
    }
}
