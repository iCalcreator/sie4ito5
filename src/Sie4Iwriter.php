<?php
/**
 * Sie4Ito5   PHP Sie 4I to 5 conversion package
 *
 * This file is a part of Sie4Ito5
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @version   1.2
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

use InvalidArgumentException;
use Kigkonsult\Asit\It;
use Kigkonsult\Sie4Ito5\Util\ArrayUtil;
use Kigkonsult\Sie4Ito5\Util\FileUtil;
use Kigkonsult\Sie4Ito5\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Dto\AccountTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\DimensionTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\JournalEntryTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\LedgerEntryTypeEntry;
use Kigkonsult\Sie5Sdk\Impl\CommonFactory;
use Kigkonsult\Sie5Sdk\Dto\SieEntry;

use function crc32;
use function implode;
use function rtrim;
use function sprintf;

class Sie4Iwriter implements Sie4IInterface
{
    private static $SIEENTRYFMT1 = '%s %s';
    private static $SIEENTRYFMT2 = '%s %s %s';
    private static $SIEENTRYFMT3 = '%s %s %s %s';
    private static $SIEENTRYFMT6 = '%s %s %s %s %s %s %s';
    private static $YYYYMMDD     = 'Ymd';
    private static $DOUBLEQUOTE  = '""';

    /**
     * Output file rows, managed by Asit\It
     *
     * Rows without eol
     *
     * @var It
     */
    private $output = null;

    /**
     * @var SieEntry
     */
    private $sieEntry = null;

    /**
     * If true, write #KSUMMA in Sie4I output
     *
     * @var bool
     */
    private $writeKsumma = false;

    /**
     * String to base #KSUMMA crc-32 value on
     * @var string
     */
    private $ksummaBase  = null;

    /**
     * @param mixed ...$args
     */
    private function appendKsumma( ...$args )
    {
        if( $this->writeKsumma ) {
            $this->ksummaBase .= implode( $args );
        }
    }

    /**
     * @return null|string
     */
    public function getKsummaBase()
    {
        return $this->ksummaBase;
    }

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
     * @param SieEntry     $sieEntry
     * @param null|string  $fileName
     * @param null|bool    $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     */
    public function write4I(
        SieEntry $sieEntry,
        $fileName = null,
        $writeKsumma = false
    ) : string
    {
        static $FMT1 = 'Ofullständig SieEntry indata';
        if( ! empty( $fileName )) {
            FileUtil::assertWriteFile( $fileName );
        }
        if( ! $sieEntry->isValid()) {
            throw new InvalidArgumentException( $FMT1, 2201 );
        }
        $this->sieEntry    = $sieEntry;
        $this->output      = new It();
        $this->writeKsumma = (bool) $writeKsumma;

        $this->output->append(
            sprintf( self::$SIEENTRYFMT1, self::FLAGGA, StringUtil::$ZERO )
        );
        if( $this->writeKsumma ) {
            $this->output->append( self::KSUMMA );
        }
        $this->writeIdData();
        $this->writeAccountData();
        $this->writeLedgerEntryData();
        if( $this->writeKsumma ) {
            $this->computeAndWriteKsumma();
        }
        $output = ArrayUtil::eolEndElements( $this->output->get());
        if( ! empty( $fileName )) {
            FileUtil::writeFile( $fileName, $output );
        }
        return implode( StringUtil::$SP0, $output );
    }

    /**
     * Manage Sie4I 'Identifikationsposter'
     */
    private function writeIdData()
    {
        static $FORMATPC8 = 'PC8';
        static $SIETYP4   = '4';
        $fileInfo = $this->sieEntry->getFileInfo();

        // #PROGRAM programnamn version
        $softwareProduct = $fileInfo->getSoftwareProduct();
        $programnamn     = StringUtil::utf8toCP437( $softwareProduct->getName());
        $version         = StringUtil::utf8toCP437( $softwareProduct->getVersion());
        $this->appendKsumma( self::PROGRAM, $programnamn, $version );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::PROGRAM,
                StringUtil::quoteString( $programnamn ),
                StringUtil::quoteString( $version )
            )
        );

        // #FORMAT PC8
        $this->appendKsumma( self::FORMAT, $FORMATPC8 );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT1,
                self::FORMAT,
                $FORMATPC8
            )
        );

        // #GEN datum sign
        $fileCreation = $fileInfo->getFileCreation();
        $datum        = $fileCreation->getTime()->format( self::$YYYYMMDD );
        $sign         = StringUtil::utf8toCP437( $fileCreation->getBy());
        $this->appendKsumma( self::GEN, $datum, $sign );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::GEN,
                $datum,
                StringUtil::quoteString( $sign )
            )
        );

        // #SIETYP typnr
        $this->appendKsumma( self::SIETYP, $SIETYP4 );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT1,
                self::SIETYP,
                $SIETYP4
            )
        );

        $company = $fileInfo->getCompany();
        // #FNR företagsid
        $companyClientId = $company->getClientId();
        if( ! empty( $companyClientId )) {
            $companyClientId = StringUtil::utf8toCP437( $companyClientId );
            $this->appendKsumma( self::FNR, $companyClientId );
            $this->output->append(
                sprintf( self::$SIEENTRYFMT1, self::FNR, $companyClientId )
            );
        }
        // #ORGNR orgnr förvnr verknr (förvnr = multiple if not is null|1)
        $orgnr    = $company->getOrganizationId();
        $multiple = ( $company->getMultiple() ?: StringUtil::$SP0 );
        $this->appendKsumma( self::ORGNR, $orgnr, $multiple );
        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEENTRYFMT2,
                    self::ORGNR,
                    $orgnr,
                    $multiple
                )
            )
        );
        // #FNAMN företagsnamn
        $companyName = $company->getName();
        if( ! empty( $companyName )) {
            $companyName = StringUtil::utf8toCP437( $companyName );
            $this->appendKsumma( self::FNAMN, $companyName );
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT1,
                    self::FNAMN,
                    StringUtil::quoteString( $companyName )
                )
            );
        }

        // #VALUTA valutakod
        $accountingCurrency = $fileInfo->getAccountingCurrency();
        if( ! empty( $accountingCurrency )) {
            $valutakod = StringUtil::utf8toCP437(
                $accountingCurrency->getCurrency()
            );
            $this->appendKsumma( self::VALUTA, $valutakod );
            $this->output->append(
                sprintf( self::$SIEENTRYFMT1, self::VALUTA, $valutakod )
            );
        }
    }

    /**
     * Manage Sie4I  'Kontoplansuppgifter'
     *
     * #SRU and #UNDERDIM are skipped
     */
    private function writeAccountData()
    {
        $accounts = $this->sieEntry->getAccounts();
        if( ! empty( $accounts )) {
            foreach( $accounts->getAccount() as $accountTypeEntry ) {
                // empty row before each #KONTO
                $this->output->append( StringUtil::$SP0 );
                $this->writeKontoData( $accountTypeEntry );
            } // end foreach
        } // end if $accounts

        $dimensions = $this->sieEntry->getDimensions();
        if( ! empty( $dimensions )) {
            foreach( $dimensions->getDimension() as $dimensionTypeEntry ) {
                // empty row before each #DIM
                $this->output->append( StringUtil::$SP0 );
                $this->writeDimObjectData( $dimensionTypeEntry );
            } // end foreach $dimensions->getDimension()
        } // end $dimensions
    }

    /**
     * #KONTO kontonr kontonamn
     * #KTYP kontonr  kontotyp
     * #ENHET kontonr enhet
     *
     * @param AccountTypeEntry $accountTypeEntry
     */
    private function writeKontoData( AccountTypeEntry $accountTypeEntry )
    {
        static $KONTOTYPER = [
            AccountTypeEntry::ASSET     => 'T', // Tillgång
            AccountTypeEntry::LIABILITY => 'S', // Skuld
            AccountTypeEntry::COST      => 'K', // kostnad
            AccountTypeEntry::INCOME    => 'I', // Intäkt
        ];
        $kontoNr   = $accountTypeEntry->getId();
        $kontonamn = StringUtil::utf8toCP437( $accountTypeEntry->getName());
        $this->appendKsumma( self::KONTO, $kontoNr, $kontonamn );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::KONTO,
                $kontoNr,
                StringUtil::quoteString( $kontonamn )
            )
        );
        $kontotyp = StringUtil::utf8toCP437(
            $KONTOTYPER[$accountTypeEntry->getType()]
        );
        $this->appendKsumma( self::KTYP, $kontoNr, $kontotyp );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::KTYP,
                $kontoNr,
                $kontotyp
            )
        );
        $enhet = $accountTypeEntry->getUnit();
        if( ! empty( $enhet )) {
            $enhet = StringUtil::utf8toCP437( $enhet );
            $this->appendKsumma( self::ENHET, $kontoNr, $enhet );
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT2,
                    self::ENHET,
                    $kontoNr,
                    $enhet
                )
            );
        } // end if
    }

    /**
     * #DIM dimensionsnr namn
     * #OBJEKT dimensionsnr objektnr objektnamn
     *
     * @param DimensionTypeEntry $dimensionTypeEntry
     */
    private function writeDimObjectData( DimensionTypeEntry $dimensionTypeEntry )
    {
        $dimId = $dimensionTypeEntry->getId();
        $namn  = StringUtil::utf8toCP437( $dimensionTypeEntry->getName());
        $this->appendKsumma( self::DIM, $dimId, $namn );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::DIM,
                $dimId,
                StringUtil::quoteString( $namn )
            )
        );
        $objects = $dimensionTypeEntry->getObject();
        if( empty( $objects )) {
            return;
        }
        foreach( $objects as $objectType ) {
            $objektnr   = StringUtil::utf8toCP437( $objectType->getId());
            $objektnamn = StringUtil::utf8toCP437( $objectType->getName());
            $this->appendKsumma( self::OBJEKT, $dimId, $objektnr, $objektnamn );
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT3,
                    self::OBJEKT,
                    $dimId,
                    StringUtil::quoteString( $objektnr ),
                    StringUtil::quoteString( $objektnamn )
                )
            );
        } // end foreach $objects
    }

    /**
     * Managing writing of #VER and #TRANS
     */
    private function writeLedgerEntryData()
    {
        $journals = $this->sieEntry->getJournal();
        // fetch sign from #GEN (applied sign in #VER)
        $genSign  = $this->sieEntry->getFileInfo()->getFileCreation()->getBy();
        foreach( $journals as $journalTypeEntry ) {
            $serie = StringUtil::utf8toCP437((string) $journalTypeEntry->getId());
            foreach( $journalTypeEntry->getJournalEntry() as $journalEntryTypeEntry) {
                // empty row before each #VER
                $this->output->append( StringUtil::$SP0 );
                $this->writeVerTransData( $journalEntryTypeEntry, $serie, $genSign );

            } // end foreach $journalTypeEntry->getJournalEntry()
        } // end foreach $journals
    }

    /**
     * Writes #VER and #TRANS
     *
     * #VER serie vernr verdatum vertext regdatum sign
     *
     * @param JournalEntryTypeEntry $journalEntryTypeEntry
     * @param string                $serie
     * @param string                $genSign
     */
    private function writeVerTransData(
        JournalEntryTypeEntry $journalEntryTypeEntry,
        string $serie,
        string $genSign
    )
    {
        $this->appendKsumma( self::VER );
        if( empty( $serie)) {
            $serie = self::$DOUBLEQUOTE;
        }
        else {
            $this->appendKsumma( $serie );
        }

        $vernr     = $journalEntryTypeEntry->getId();
        if( empty( $vernr )) {
            $vernr = self::$DOUBLEQUOTE;
        }
        else {
            $this->appendKsumma( $vernr );
        }

        $verdatum  = $journalEntryTypeEntry->getJournalDate()->format( self::$YYYYMMDD );
        $this->appendKsumma( $verdatum );

        $vertext   = $journalEntryTypeEntry->getText();
        if( empty( $vertext )) {
            $vertext = self::$DOUBLEQUOTE;
        }
        else {
            $vertext = StringUtil::utf8toCP437( $vertext );
            $this->appendKsumma( $vertext );
            $vertext = StringUtil::quoteString( $vertext );
        }

        $originalEntryInfo = $journalEntryTypeEntry->getOriginalEntryInfo();
        $regdatum = $originalEntryInfo->getDate()->format( self::$YYYYMMDD );
        if( $verdatum == $regdatum ) {
            // skip if equal
            $regdatum = self::$DOUBLEQUOTE;
        }
        else {
            $this->appendKsumma( $regdatum );
        }
        $sign = $originalEntryInfo->getBy();
        if( $sign == $genSign ) {
            // if none found in input and #GEN used
            $sign = StringUtil::$SP0;
            if( self::$DOUBLEQUOTE == $regdatum ) {
                $regdatum = StringUtil::$SP0;
                if( self::$DOUBLEQUOTE == $vertext ) {
                    $vertext = StringUtil::$SP0;
                }
            }
        } // end if
        else {
            $sign = StringUtil::utf8toCP437( $sign );
            $this->appendKsumma( $sign );
        }

        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEENTRYFMT6,
                    self::VER,
                    $serie,
                    (string) $vernr,
                    $verdatum,
                    $vertext,
                    $regdatum,
                    $sign
                )
            )
        );
        $this->output->append( StringUtil::$CURLYBRACKETS[0] );
        foreach( $journalEntryTypeEntry->getLedgerEntry() as $ledgerEntryTypeEntry ) {
            $this->writeTransData( $ledgerEntryTypeEntry, $verdatum );
        }
        $this->output->append( StringUtil::$CURLYBRACKETS[1] );
    }

    /**
     * Write #TRANS
     *
     * #TRANS kontonr {objektlista} belopp transdat(opt) transtext(opt) kvantitet sign
     * ex  #TRANS 7010 {"1" "456" "7" "47"} 13200.00
     * Note, sign is skipped
     *
     * @param LedgerEntryTypeEntry $ledgerEntryTypeEntry
     * @param string $verdatum
     */
    private function writeTransData(
        LedgerEntryTypeEntry $ledgerEntryTypeEntry,
        string $verdatum
    )
    {
        $kontonr     = StringUtil::utf8toCP437( $ledgerEntryTypeEntry->getAccountId());
        $this->appendKsumma( self::TRANS, $kontonr );

        list( $objektlista, $ksummsPart ) = self::getObjektLista(
            $ledgerEntryTypeEntry->getLedgerEntryTypeEntries()
        );
        if( ! empty( $objektlista )) {
            $this->appendKsumma( $ksummsPart );
        }

        $belopp      = CommonFactory::formatAmount( $ledgerEntryTypeEntry->getAmount());
        $this->appendKsumma( $belopp );

        $transdat    = $ledgerEntryTypeEntry->getLedgerDate();
        if( empty( $transdat ) || ( $transdat == $verdatum )) {
            $transdat = self::$DOUBLEQUOTE;
        }
        else {
            $transdat = $transdat->format( self::$YYYYMMDD );
            $this->appendKsumma( $transdat );
        }

        $transtext = $ledgerEntryTypeEntry->getText();
        if( empty( $transtext )) {
            $transtext = self::$DOUBLEQUOTE;
        }
        else {
            $transtext = StringUtil::utf8toCP437( $transtext );
            $this->appendKsumma( $transtext );
            $transtext = StringUtil::quoteString( $transtext );
        }

        $kvantitet = $ledgerEntryTypeEntry->getQuantity();
        if( empty( $kvantitet )) {
            $kvantitet = StringUtil::$SP0;
            if( self::$DOUBLEQUOTE == $transtext ) {
                $transtext = StringUtil::$SP0;
                if( self::$DOUBLEQUOTE == $transdat ) {
                    $transdat = StringUtil::$SP0;
                }
            } // end if
        } // end if
        else {
            $this->appendKsumma( $kvantitet );
        }
        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEENTRYFMT6,
                    self::TRANS,
                    $kontonr,
                    $objektlista,
                    $belopp,
                    $transdat,
                    $transtext,
                    $kvantitet
                )
            )
        );
    }

    /**
     * Return string with (quoted) dimId and objectId pairs (if set)
     *
     * @param array $ledgerEntryTypeEntries
     * @return array
     */
    private static function getObjektLista( array $ledgerEntryTypeEntries ) : array
    {
        $objektlista = [];
        $ksummaPart  = StringUtil::$SP0;
        foreach( $ledgerEntryTypeEntries as $elementSets ) {
            foreach( $elementSets as $elementSet ) {
                foreach( $elementSet as $elementType => $objectReferenceType ) {
                    if( SieEntry::OBJECTREFERENCE == $elementType ) {
                        $dimId         = $objectReferenceType->getDimId();
                        $objektlista[] = StringUtil::quoteString((string) $dimId );
                        $objektId      = StringUtil::utf8toCP437(
                            $objectReferenceType->getObjectId()
                        );
                        $objektlista[] = StringUtil::quoteString( $objektId );
                        $ksummaPart   .= $dimId . $objektId;
                    } // end if
                } // end foreach $elementSet
            } // end foreach $elementSets
        } // end foreach $ledgerEntryTypeEntries
        return [
            StringUtil::curlyBacketsString( implode( StringUtil::$SP1, $objektlista )),
            $ksummaPart
        ];
    }

    /**
     * Computes and writes trailing Ksumma
     */
    private function computeAndWriteKsumma()
    {
        // empty row before
        $this->output->append( StringUtil::$SP0 );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT1,
                self::KSUMMA,
                (string) crc32( $this->getKsummaBase())
            )
        );
    }
}
