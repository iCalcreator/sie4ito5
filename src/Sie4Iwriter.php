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

use function rtrim;
use function sprintf;

class Sie4Iwriter implements Sie4IInterface
{
    private static $SIEENTRYFMT0 = '%s';
    private static $SIEENTRYFMT1 = '%s%s';
    private static $SIEENTRYFMT2 = '%s%s %s';
    private static $SIEENTRYFMT3 = '%s%s %s %s';
    private static $SIEENTRYFMT6 = '%s%s %s %s %s %s %s';
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
     */
    public function write4I( SieEntry $sieEntry, $fileName = null, $writeKsumma = false ) : string
    {
        if( ! empty( $fileName )) {
            FileUtil::assertWriteFile( $fileName );
        }
        $this->sieEntry    = $sieEntry;
        $this->output      = new It();
        $this->writeKsumma = (bool) $writeKsumma;

        $this->output->append( sprintf( self::$SIEENTRYFMT1, self::FLAGGA, StringUtil::$ZERO ));
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
        static $FORMAT = '#FORMAT PC8';
        static $SIETYP = '#SIETYP 4';
        $fileInfo = $this->sieEntry->getFileInfo();

        // #PROGRAM programnamn version
        $softwareProduct = $fileInfo->getSoftwareProduct();
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::PROGRAM,
                StringUtil::quoteString( StringUtil::utf8toCP437( $softwareProduct->getName())),
                StringUtil::quoteString( StringUtil::utf8toCP437( $softwareProduct->getVersion()))
            )
        );

        // #FORMAT PC8
        $this->output->append( $FORMAT );

        // #GEN datum sign
        $fileCreation = $fileInfo->getFileCreation();
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::GEN,
                $fileCreation->getTime()->format( self::$YYYYMMDD ),
                StringUtil::quoteString( StringUtil::utf8toCP437( $fileCreation->getBy()))
            )
        );

        // #SIETYP typnr
        $this->output->append( $SIETYP );

        $company = $fileInfo->getCompany();
        // #FNR företagsid
        $companyClientId = $company->getClientId();
        if( ! empty( $companyClientId )) {
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT1,
                    self::FNR,
                    StringUtil::utf8toCP437( $company->getClientId() )
                )
            );
        }
        // #ORGNR orgnr förvnr verknr (förvnr = multiple if not is null|1)
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::ORGNR,
                $company->getOrganizationId(),
                ( $company->getMultiple() ?: 1 )
            )
        );
        // #FNAMN företagsnamn
        $companyName = $company->getName();
        if( ! empty( $companyName )) {
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT1,
                    self::FNAMN,
                    StringUtil::quoteString( StringUtil::utf8toCP437( $companyName ))
                )
            );
        }

        // #VALUTA valutakod
        $accountingCurrency = $fileInfo->getAccountingCurrency();
        if( ! empty( $accountingCurrency )) {
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT1,
                    self::VALUTA,
                    StringUtil::utf8toCP437( $accountingCurrency->getCurrency() )
                )
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
                // empty row before #KONTO
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
        $kontoNr = $accountTypeEntry->getId();
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::KONTO,
                $kontoNr,
                StringUtil::quoteString(
                    StringUtil::utf8toCP437( $accountTypeEntry->getName())
                )
            )
        );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::KTYP,
                $kontoNr,
                StringUtil::utf8toCP437( $KONTOTYPER[$accountTypeEntry->getType()] )
            )
        );
        $enhet = $accountTypeEntry->getUnit();
        if( ! empty( $enhet )) {
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT2,
                    self::ENHET,
                    $kontoNr,
                    StringUtil::utf8toCP437( $enhet )
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
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::DIM,
                $dimId,
                StringUtil::quoteString(
                    StringUtil::utf8toCP437( $dimensionTypeEntry->getName())
                )
            )
        );
        $objects = $dimensionTypeEntry->getObject();
        if( empty( $objects )) {
            return;
        }
        foreach( $objects as $objectType ) {
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT3,
                    self::OBJEKT,
                    $dimId,
                    StringUtil::quoteString(
                        StringUtil::utf8toCP437( $objectType->getId())
                    ),
                    StringUtil::quoteString(
                        StringUtil::utf8toCP437( $objectType->getName())
                    )
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
        foreach( $journals as $journalTypeEntry ) {
            $serie = $journalTypeEntry->getId();
            if( empty( $serie )) {
                $serie = self::$DOUBLEQUOTE;
            }
            foreach( $journalTypeEntry->getJournalEntry() as $journalEntryTypeEntry) {
                // empty row before #VER
                $this->output->append( StringUtil::$SP0 );
                $this->writeVerTransData( $journalEntryTypeEntry, $serie );

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
     */
    private function writeVerTransData( JournalEntryTypeEntry $journalEntryTypeEntry, string $serie )
    {
        $vernr     = $journalEntryTypeEntry->getId();
        if( empty( $vernr )) {
            $vernr = self::$DOUBLEQUOTE;
        }
        $verdatum  = $journalEntryTypeEntry->getJournalDate()->format( self::$YYYYMMDD );
        $vertext   = $journalEntryTypeEntry->getText();
        $originalEntryInfo = $journalEntryTypeEntry->getOriginalEntryInfo();
        $regdatum  = $originalEntryInfo->getDate()->format( self::$YYYYMMDD );
        $sign      = $originalEntryInfo->getBy();
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT6,
                self::VER,
                StringUtil::utf8toCP437( $serie ),
                StringUtil::utf8toCP437((string) $vernr ),
                $verdatum,
                StringUtil::quoteString( StringUtil::utf8toCP437( $vertext )),
                $regdatum,
                StringUtil::utf8toCP437( $sign )
            )
        );
        $this->output->append(
            sprintf( self::$SIEENTRYFMT0, StringUtil::$CURLYBRACKETS[0] )
        );
        foreach( $journalEntryTypeEntry->getLedgerEntry() as $ledgerEntryTypeEntry ) {
            $this->writeTransData( $ledgerEntryTypeEntry );
        }
        $this->output->append(
            sprintf( self::$SIEENTRYFMT0, StringUtil::$CURLYBRACKETS[1] )
        );
    }

    /**
     * Write #TRANS
     *
     * #TRANS kontonr {objektlista} belopp transdat(opt) transtext(opt) kvantitet sign
     * ex  #TRANS 7010 {"1" "456" "7" "47"} 13200.00
     *
     * @param LedgerEntryTypeEntry $ledgerEntryTypeEntry
     */
    private function writeTransData( LedgerEntryTypeEntry $ledgerEntryTypeEntry )
    {
        $kontonr     = $ledgerEntryTypeEntry->getAccountId();
        $objektlista = self::getObjektLista( $ledgerEntryTypeEntry->getLedgerEntryTypeEntries());
        $belopp      = CommonFactory::formatAmount( $ledgerEntryTypeEntry->getAmount());
        $transdat    = $ledgerEntryTypeEntry->getLedgerDate();
        $transdat    = empty( $transdat )
            ? self::$DOUBLEQUOTE
            : $transdat->format( self::$YYYYMMDD );
        $transtext = $ledgerEntryTypeEntry->getText();
        if( empty( $transtext )) {
            $transtext = self::$DOUBLEQUOTE;
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
        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEENTRYFMT6,
                    self::TRANS,
                    StringUtil::utf8toCP437( $kontonr ),
                    StringUtil::utf8toCP437( $objektlista ),
                    $belopp,
                    $transdat,
                    StringUtil::utf8toCP437( $transtext ),
                    $kvantitet
                )
            )
        );
    }

    /**
     * Return string of (quoted) dimId and objectId pairs
     *
     * @param mixed $ledgerEntryTypeEntries
     * @return string
     */
    private static function getObjektLista( $ledgerEntryTypeEntries ) : string
    {
        $objektlista = StringUtil::$SP0;
        if( ! empty( $ledgerEntryTypeEntries )) {
            foreach( $ledgerEntryTypeEntries as $elementSets ) {
                foreach( $elementSets as $elementSet ) {
                    if( ! empty( $objektlista )) {
                        $objektlista .= StringUtil::$SP1;
                    }
                    foreach( $elementSet as $elementType => $ledgerEntryType ) {
                        if( SieEntry::OBJECTREFERENCE !== $elementType ) {
                            continue;
                        }
                        $objektlista .= StringUtil::quoteString((string) $ledgerEntryType->getDimId());
                        $objektlista .= StringUtil::$SP1;
                        $objektlista .= StringUtil::quoteString( $ledgerEntryType->getObjectId() );
                    } // end foreach $elementSet
                } // end foreach $elementSets
            } // end foreach $ledgerEntryTypeEntries
        } // end if $ledgerEntryTypeEntries
        return StringUtil::curlyBacketsString( $objektlista );
    }

    /**
     * Computes and writes trailing Ksumma
     */
    private function computeAndWriteKsumma()
    {
        // nothing for now
    }
}
