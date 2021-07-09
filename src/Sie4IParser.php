<?php
/**
 * Sie4Ito5   PHP Sie4I SDK and Sie5 conversion package
 *
 * This file is a part of Sie4Ito5
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @license   Subject matter of licence is the software Sie4Ito5.
 *            The above package, copyright, link and this licence notice shall be
 *            included in all copies or substantial portions of the Sie4Ito5.
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

use InvalidArgumentException;
use Kigkonsult\Asit\It;
use Kigkonsult\Sie4Ito5\Dto\DimObjektDto;
use Kigkonsult\Sie4Ito5\Dto\IdDto;
use Kigkonsult\Sie4Ito5\Dto\TransDto;
use Kigkonsult\Sie4Ito5\Dto\VerDto;
use Kigkonsult\Sie4Ito5\Util\ArrayUtil;
use Kigkonsult\Sie4Ito5\Util\DateTimeUtil;
use Kigkonsult\Sie4Ito5\Util\FileUtil;
use Kigkonsult\Sie4Ito5\Util\StringUtil;
use Kigkonsult\Sie4Ito5\Dto\Sie4IDto;
use RuntimeException;

use function array_map;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_string;
use function ksort;
use function sprintf;
use function trim;

/**
 * Class Sie4IParser
 *
 * Parse Sie4I file/string into Sie4IDto
 */
class Sie4IParser implements Sie4IInterface
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
    protected static $IDLABELS = [
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
    protected static $ACCOUNTLABELS = [
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
    protected static $LEDGERENTRYLABELS = [
        self::VER,
        self::TRANS,
        self::RTRANS,
        self::BTRANS,
    ];

    /**
     * Input file rows, managed by Asit\It
     *
     * @var It
     */
    private $input = null;

    /**
     * @var bool
     */
    private $ksummaSet = false;

    /**
     * @return bool
     */
    public function isKsummaSet() : bool
    {
        return $this->ksummaSet;
    }

    /**
     * @var Sie4IDto
     */
    private $sie4IDto = null;

    /**
     * Current VerDto, 'parent' for TransDto's
     *
     * @var VerDto
     */
    private $currentVerDto = null;

    /**
     * @var array
     */
    private $postReadGroupActionKeys = [];

    /**
     * Return instance
     *
     * @param null|string|array $source
     * @return static
     */
    public static function factory( $source = null ) : self
    {
        $instance = new self();
        if( ! empty( $source )) {
            $instance->setInput( $source );
        }
        return $instance;
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
        if( is_array( $source )) {
            $input = $source;
        }
        else {
            if( ! is_string( $source )) {
                throw new InvalidArgumentException( $FMT1, 1111 );
            }
            $source = trim( $source );
            if( ! StringUtil::startsWith( $source, self::FLAGGA )) {
                FileUtil::assertReadFile((string) $source, 1112 );
                $input = FileUtil::readFile((string) $source, 1113 );
            }
            else {
                $input = StringUtil::string2Arr(
                    StringUtil::convEolChar((string) $source )
                );
            }
        } // end else
        $fileRowsIter = new It(
            array_map( $TRIM, array_map( $TAB2SPACE, $input ))
        );
        $isKsummaSet     = Sie4IValidator::assertSie4IInput( $fileRowsIter );
        $this->input     = $fileRowsIter;
        $this->ksummaSet = $isKsummaSet;
        return $this;
    }

    /**
     * Parse Sie4I, opt input from Sie4I file, -array (rows), -string, return sie4IDto
     *
     * @param mixed $source
     * @return Sie4IDto
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @deprecated
     */
    public function parse4I( $source = null ) : Sie4IDto
    {
        return $this->process( $source );
    }

    /**
     * Parse Sie4I, opt input from Sie4I file, -array (rows), -string, return sie4IDto
     *
     * @param mixed $source
     * @return Sie4IDto
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function process( $source = null ) : Sie4IDto
    {
        static $FMT1     = 'Input error (#%d) on post %s';
        static $GROUP12  = [ 1, 2 ];
        static $GROUP23  = [ 2, 3 ];
        static $GROUP234 = [ 2, 3, 4 ];
        if( ! empty( $source )) {
            $this->setInput( $source );
        }
        $this->sie4IDto = new Sie4IDto();
        $this->input->rewind();
        $currentGroup   = 0;
        $prevLabel      = null;
        $this->postReadGroupActionKeys = [];
        while( $this->input->valid()) {
            $post = $this->input->current();
            if( empty( $post )) {
                $this->input->next();
                continue;
            }
            $post = StringUtil::cp437toUtf8( $post );
            list( $label, $rowData ) = StringUtil::splitPost( $post );
            switch( true ) {
                case (( 0 === $currentGroup ) && ( self::FLAGGA == $label )) :
                    $currentGroup = 1;
                    break;
                case ( self::KSUMMA == $label ) :
                    break;

                case ( in_array( $currentGroup, $GROUP12 )
                    && in_array( $label, self::$IDLABELS )) :
                    $currentGroup = 2;
                    $this->readIdData( $label, $rowData );
                    break;
                case (( 2 === $currentGroup ) && empty( $label )) :
                    // data content for previous Label
                    $this->readIdData( $prevLabel, $rowData );
                    break;

                case ( in_array( $currentGroup, $GROUP23 )
                    && in_array( $label, self::$ACCOUNTLABELS )) :
                    if( 2 == $currentGroup ) {
                        // finish off group 2 actions
                        $this->postReadGroupAction();
                        $currentGroup = 3;
                    }
                    $this->readAccountData( $label, $rowData );
                    break;
                case (( 3 === $currentGroup ) && empty( $label )) :
                    // data content for previous Label
                    $this->readAccountData( $prevLabel, $rowData );
                    break;

                case ( in_array( $currentGroup, $GROUP234 )
                    && in_array( $label, self::$LEDGERENTRYLABELS )) :
                    if( in_array( $currentGroup, $GROUP23 )) {
                        // finish off group (2-)3 actions
                        $this->postReadGroupAction();
                        $currentGroup = 4;
                    }
                    $this->readVerTransData( $label, $rowData );
                    break;
                case (( 4 === $currentGroup ) && empty( $label )) :
                    // data content for previous Label
                    $this->readVerTransData( $prevLabel, $rowData );
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
        return $this->sie4IDto;
    }

    /**
     * Manage Sie4I 'Identifikationsposter'
     *
     * Note för #GEN
     *   if 'sign' is missing, '#PROGRAM programnamn' will be used in Sie4IWriter
     *
     * @param string $label
     * @param array  $rowData
     * @throws RuntimeException
     */
    private function readIdData( string $label, array $rowData )
    {
        if( ! $this->sie4IDto->isIdDtoSet()) {
            $this->sie4IDto->setIdDto( new IdDto());
        }
        $idDto = $this->sie4IDto->getIdDto();
        switch( $label ) {
            /**
             * Vilket program som genererat filen
             * Obligatorisk
             * #PROGRAM programnamn version
             */
            case self::PROGRAM :
                ArrayUtil::assureArrayLength( $rowData, 2 );
                $idDto->setProgramnamn( $rowData[0] );
                $idDto->setVersion( $rowData[1] );
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
             * Obligatorisk (sign opt) Sie4I, båda obl. Sie5 SieEntry
             */
            case self::GEN :
                ArrayUtil::assureArrayLength( $rowData, 2 );
                $idDto->setGenDate(
                    DateTimeUtil::getDateTime(
                        $rowData[0],
                        self::GEN,
                        1511
                    )
                );
                if( ! empty( $rowData[1] )) {
                    $idDto->setGenSign( $rowData[1] );
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
                ArrayUtil::assureArrayLength( $rowData, 1 );
                $idDto->setFnrId( $rowData[0] );
                break;

            /**
             * Organisationsnummer för det företag som exporterats
             *
             * #ORGNR orgnr förvnr verknr
             * förvnr : anv då ensk. person driver flera ensk. firmor (ordningsnr)
             * verknr : anv ej
             * valfri, MEN orgnr obligatoriskt i sie4IDto (FileInfoTypeEntry/CompanyTypeEntry)
             */
            case self::ORGNR :
                ArrayUtil::assureArrayLength( $rowData, 2 );
                $idDto->setOrgnr( $rowData[0] );
                $idDto->setMultiple(
                    ( ! empty( $rowData[1] ))
                    ? (int) $rowData[1] :
                    1
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
             * Obligatorisk men valfri i sie4IDto (FileInfoTypeEntry/CompanyTypeEntry)
             */
            case self::FNAMN :
                ArrayUtil::assureArrayLength( $rowData, 1 );
                $idDto->setFnamn( $rowData[0] );
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
                ArrayUtil::assureArrayLength( $rowData, 1 );
                $idDto->setValutakod( $rowData[0] );
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
     * @param array  $rowData
     */
    private function readAccountData( string $label, array $rowData )
    {
        switch( $label ) {
            /**
             * Kontouppgifter
             *
             * #KONTO kontonr kontoNamn
             * valfri
             */
            case self::KONTO :
                ArrayUtil::assureArrayLength( $rowData, 2 );
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys,
                    self::KONTO
                );
                list( $kontonr, $kontonamn ) = $rowData;
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys[self::KONTO],
                    $kontonr
                );
                $this->postReadGroupActionKeys[self::KONTO][$kontonr][0] = $kontonamn;
                break;
            /**
             * Kontotyp
             *
             * #KTYP kontonr  kontoTyp
             * valfri
             */
            case self::KTYP :
                ArrayUtil::assureArrayLength( $rowData, 2 );
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys,
                    self::KONTO
                );
                list( $kontonr, $kontotyp ) = $rowData;
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys[self::KONTO],
                    $kontonr
                );
                $this->postReadGroupActionKeys[self::KONTO][$kontonr][1] = $kontotyp;
                break;

            /**
             * Enhet vid kvantitetsredovisning
             *
             * #ENHET kontonr enhet
             * valfri
             */
            case self::ENHET :
                ArrayUtil::assureArrayLength( $rowData, 2 );
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys,
                    self::KONTO
                );
                list( $kontonr, $enhet ) = $rowData;
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys[self::KONTO],
                    $kontonr
                );
                $this->postReadGroupActionKeys[self::KONTO][$kontonr][2] = $enhet;
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
                ArrayUtil::assureArrayLength( $rowData, 2 );
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys,
                    self::DIM
                );
                list( $dimensionsnr, $namn ) = $rowData;
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys[self::DIM],
                    $dimensionsnr
                );
                $this->postReadGroupActionKeys[self::DIM][$dimensionsnr][0] = $namn;
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
                ArrayUtil::assureArrayLength( $rowData, 3 );
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys,
                    self::DIM
                );
                list( $dimensionsnr, $objektnr, $objeknamn ) = $rowData;
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys[self::DIM],
                    $dimensionsnr
                );
                ArrayUtil::assureIsArray(
                    $this->postReadGroupActionKeys[self::DIM][$dimensionsnr],
                    self::OBJEKT
                );
                $this->postReadGroupActionKeys[self::DIM][$dimensionsnr][self::OBJEKT][$objektnr] =
                    $objeknamn;
                break;
        } // end switch
    }

    /**
     * Manage 'Verifikationsposter'
     *
     * Note för #VER
     *   if 'regdatum' is missing, 'verdatum' is used
     *
     * Note för #TRANS
     *   only support for 'dimensionsnummer och objektnummer' in the 'objektlista'
     *   i.e. no support for 'hierarkiska dimensioner'
     *
     * @param string $label
     * @param array  $rowData
     * @throws RuntimeException
     */
    private function readVerTransData( string $label, array $rowData )
    {
        if( in_array( $rowData[0], StringUtil::$CURLYBRACKETS )) {
            return;
        }
        switch( $label ) {
            /**
             * Verifikationspost
             *
             * valfri
             */
            case self::VER :
                $this->readVerData( $rowData );
                break;

            /**
             * Transaktionspost (inom Verifikationspost)
             *
             * valfri
             */
            case self::TRANS :
                $this->readTransData( $rowData );
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
     * @param array $rowData
     */
    private function readVerData( array $rowData )
    {
        ArrayUtil::assureArrayLength( $rowData, 6 );
        list( $serie, $vernr, $verdatum, $vertext, $regdatum, $sign ) = $rowData;

        // save for later #TRANS use
        $this->currentVerDto = new VerDto();
        $this->sie4IDto->addVerDto( $this->currentVerDto );

        if( ! empty( $serie )) {
            $this->currentVerDto->setSerie( $serie );
        }
        if( ! empty( $vernr )) {
            $this->currentVerDto->setVernr((int) $vernr );
        }
        $this->currentVerDto->setVerdatum(
            DateTimeUtil::getDateTime( $verdatum, self::VER, 1711 )
        );
        if( ! empty( $vertext )) {
            $this->currentVerDto->setVertext( $vertext );
        }
        // set to verdatum if missing, skipped in Sie4Iwriter2 if equal
        $this->currentVerDto->setRegdatum(
            empty( $regdatum )
                ? $this->currentVerDto->getVerdatum()
                : DateTimeUtil::getDateTime( $regdatum, self::VER, 1712 )
        );
        if( ! empty( $sign )) {
            $this->currentVerDto->setSign( $sign );
        }
    }

    /**
     * Manage #TRANS data
     *
     * #TRANS kontonr {objektlista} belopp transdat(opt) transtext(opt) kvantitet sign
     *
     * @param array $rowData
     */
    private function readTransData( array $rowData )
    {
        ArrayUtil::assureArrayLength( $rowData, 7 );
        list(
            $kontonr,
            $objektlista,
            $belopp,
            $transdat,
            $transtext,
            $kvantitet,
            $sign
        )  = $rowData;

        $transDto = new TransDto();
        $transDto->setKontoNr( $kontonr );
        self::updObjektlista( $transDto, $objektlista );
        $transDto->setBelopp( $belopp );
        if( ! empty( $transdat )) {
            $transDto->setTransdat(
                DateTimeUtil::getDateTime( $transdat, self::TRANS, 1713 )
            );
        } // end if
        if( ! empty( $transtext )) {
            $transDto->setTranstext( $transtext );
        }
        if( null !== $kvantitet ) {
            $transDto->setKvantitet( $kvantitet );
        }
        if( ! empty( $sign )) {
            $transDto->setSign( $sign );
        }
        $this->currentVerDto->addTransDto( $transDto );
    }

    /**
     * Create DimObjektDtos from objektlista, i.e. pairs of dimId/objectId
     *
     * @param TransDto $transDto
     * @param string   $objektlista
     */
    private static function updObjektlista( TransDto $transDto, string $objektlista )
    {
        if( empty( $objektlista )) {
            return;
        } // end if
        $dimObjList = explode( StringUtil::$SP1, trim( $objektlista ));
        $len        = count( $dimObjList ) - 1;
        for( $x1 = 0; $x1 < $len; $x1 += 2 ) {
            $x2     = $x1 + 1;
            $transDto->addDimIdObjektId(
                (int) $dimObjList[$x1],
                $dimObjList[$x2]
            );
        } // end for
    }

    /**
     * Due to labels in group are NOT required to be in order, aggregate or opt fix read missing parts here
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function postReadGroupAction()
    {
        if( empty( $this->postReadGroupActionKeys )) {
            return;
        }
        foreach( $this->postReadGroupActionKeys as $groupActionKey => $values ) {
            switch( $groupActionKey ) {
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
        // $dimensionId[0] = namn
        // $dimensionId[self::OBJEKT][objektnr] = objektnamn
        ksort( $dimValues );
        foreach( $dimValues as $dimensionId => $dimensionData ) {
            $this->sie4IDto->addDimDto(
                DimObjektDto::factoryDim(
                    $dimensionId,
                    $dimensionData[0]
                )
            );
            if( isset( $dimensionData[self::OBJEKT] )) {
                foreach($dimensionData[self::OBJEKT] as $objektNr => $objektNamn ) {
                    $this->sie4IDto->addDimObjekt(
                        $dimensionId,
                        (string) $objektNr,
                        $objektNamn
                    );
                } // end foreach
            } // end if
        } // end foreach
    }

    /**
     * Create AccountsTypeEntry for all KONTO/KTYP/ENHET
     *
     * @param array $kontoValues
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    private function postKontoActions( array $kontoValues )
    {
        // kontoNr[0] = kontoNamn
        // kontoNr[1] = kontoTyp, Ska vara någon av typerna T, S, K, I
        // kontoNr[2] = enhet
        ksort( $kontoValues );
        foreach( $kontoValues as $kontoNr => $kontoData ) {
            $this->sie4IDto->addAccount(
                (string) $kontoNr,
                $kontoData[0],
                $kontoData[1],
                ( $kontoData[2] ?? null )
            );
        } // end foreach
    }
}
