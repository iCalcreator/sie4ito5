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

use DateTime;
use InvalidArgumentException;
use Kigkonsult\Sie4Ito5\Dto\AccountDto;
use Kigkonsult\Sie4Ito5\Dto\Sie4IDto;
use Kigkonsult\Sie4Ito5\Dto\TransDto;
use Kigkonsult\Sie4Ito5\Dto\VerDto;
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

/**
 * Class Sie5EntryLoader
 *
 * Load SieEntry instance using Sie4 oriented data
 *   using
 *     Sie5EntrySie4IDto\Sie4IDto
 *         Sie5EntrySie4IDto\IdDto
 *         Sie5EntrySie4IDto\DimDto
 *         Sie5EntrySie4IDto\DimObjektDto
 *         Sie5EntrySie4IDto\AccountDto
 *         Sie5EntrySie4IDto\VerDto
 *             Sie5EntrySie4IDto\transDtos
 *             Sie5EntrySie4IDto\DimObjektDto)
 */
class Sie5EntryLoader
{
    /**
     * @var Sie4IDto
     */
    private $sie4IDto = null;

    /**
     * @var SieEntry
     */
    private $sieEntry = null;

    /**
     * Sie5EntryLoader constructor
     */
    public function __construct()
    {
        $this->sieEntry = self::newSieEntry();
    }

    /**
     * @param null|Sie4IDto $sie4IDto
     * @return static
     * @throws InvalidArgumentException
     */
    public static function factory( $sie4IDto = null ): self
    {
        $instance = new self();
        if( ! empty( $sie4IDto )) {
            $instance->setSie4IDto( $sie4IDto );
        }
        return $instance;
    }

    /**
     * Init SieEntry, prepare for Sie5 XML write
     *
     * @return SieEntry
     */
    private static function newSieEntry() : SieEntry
    {
        return sieEntry::factory()
                       ->setXMLattribute(
                           SieEntry::XMLNS,
                           SieEntry::SIE5URI
                       )
                       ->setXMLattribute(
                           SieEntry::XMLNS_XSI,
                           SieEntry::XMLSCHEMAINSTANCE
                       )
                       ->setXMLattribute(
                           SieEntry::XMLNS_XSD,
                           SieEntry::XMLSCHEMA
                       )
                       ->setXMLattribute(
                           SieEntry::XSI_SCHEMALOCATION,
                           SieEntry::SIE5SCHEMALOCATION
                       )
                       ->setFileInfo(
                           FileInfoTypeEntry::factory()
                                            ->setCompany( CompanyTypeEntry::factory())
                       );
    }

    /**
     * @param null|Sie4IDto $sie4Idata
     * @return SieEntry
     * @throws InvalidArgumentException
     */
    public function getSieEntry( $sie4Idata = null ) : SieEntry
    {
        if( ! empty( $sie4Idata )) {
            $this->sieEntry = self::newSieEntry();
            $this->setSie4IDto( $sie4Idata );
        }

        $this->processIdDto();
        $this->processAccountDtos();
        $this->processDimDtos();
        $this->processDimObjektDtos();
        $this->processVerDtos();

        return $this->sieEntry;
    }

    /**
     * Process Sie4I idDto into SieEntry
     *
     * genSign logic also used in processVerDtos
     */
    private function processIdDto()
    {
        $idDto    = $this->sie4IDto->getIdDto();
        $fileInfo = $this->sieEntry->getFileInfo();
        $fileInfo->setSoftwareProduct(
            SoftwareProductType::factoryNameVersion(
                $idDto->getProgramnamn(),
                $idDto->getVersion()
            )
        );

        $genSign = $idDto->isGenSignSet()
            ? $idDto->getGenSign()
            : SieEntry::PRODUCTNAME;
        $fileInfo->setFileCreation(
            FileCreationType::factoryByTime(
                $genSign,
                $idDto->getGenDate()
            )
        );

        $company = $fileInfo->getCompany();
        if( $idDto->isFnrIdSet()) {
            $company->setClientId( $idDto->getFnrId() );
        }

        if( $idDto->isOrgnrSet()) {
            $company->setOrganizationId( $idDto->getOrgnr() );
            $company->setMultiple( $idDto->getMultiple() );
        }

        $company->setName( $idDto->getFnamn());

        if( $idDto->isValutakodSet()) {
            $fileInfo->setAccountingCurrency(
                AccountingCurrencyType::factoryCurrency( $idDto->getValutakod())
            );
        }
    }

    /**
     * Process Sie4I accountDtos into SieEntry
     */
    private function processAccountDtos()
    {
        if( empty( $this->sie4IDto->countAccountDtos())) {
            return;
        }
        $accountDtos = $this->sie4IDto->getAccountDtos();
        $accounts    = $this->sieEntry->getAccounts();
        if( empty( $accounts )) {
            $accounts = AccountsTypeEntry::factory();
            $this->sieEntry->setAccounts( $accounts );
        }
        foreach( $accountDtos as $accountDto ) {
            $accountTypeEntry = AccountTypeEntry::factoryIdNameType(
                $accountDto->getKontoNr(),
                $accountDto->getKontoNamn(),
                AccountDto::getKontoType( $accountDto->getKontoTyp())
            );
            if( $accountDto->isEnhetSet()) {
                $accountTypeEntry->setUnit( $accountDto->getEnhet());
            }
            $accounts->addAccount( $accountTypeEntry );
        } // end foreach
    }

    /**
     * Process Sie4I dimDtos into SieEntry
     */
    private function processDimDtos()
    {
        if( empty( $this->sie4IDto->countDimDtos())) {
            return;
        }
        $dimensions = $this->sieEntry->getDimensions();
        if( empty( $dimensions )) {
            $dimensions = DimensionsTypeEntry::factory();
            $this->sieEntry->setDimensions( $dimensions );
        }
        foreach( $this->sie4IDto->getDimDtos() as $dimDto ) {
            $dimensionTypeEntry = DimensionTypeEntry::factoryIdName(
                $dimDto->getDimensionsNr(),
                $dimDto->getDimensionsNamn()
            );
            $dimensions->addDimension( $dimensionTypeEntry );
        } // end foreach
    }

    /**
     * Process Sie4I dimObjektDtos into SieEntry
     */
    private function processDimObjektDtos()
    {
        $dimObjektDtos = $this->sie4IDto->getDimObjektDtos();
        if( empty( $dimObjektDtos )) {
            return;
        }
        $dimensions = $this->sieEntry->getDimensions();
        if( empty( $dimensions )) {
            $dimensions = DimensionsTypeEntry::factory();
            $this->sieEntry->setDimensions( $dimensions );
        }
        foreach( $dimObjektDtos as $dimObjektDto ) {
            $dimensionNr = $dimObjektDto->getDimensionsNr();
            // find or create DimensionTypeEntry
            $found = false;
            foreach( $dimensions->getDimension() as $dimensionTypeEntry ) {
                if( $dimensionNr == $dimensionTypeEntry->getId()) {
                    $found = true;
                    break;
                }
            } // end foreach
            if( ! $found ) { // create new dimensionTypeEntry
                $dimensionsNamn = StringUtil::$SP0;
                if( $dimObjektDto->isDimensionsNamnSet()) {
                    $dimensionsNamn = $dimObjektDto->getDimensionsNamn();
                }
                elseif( ! empty( $this->sie4IDto->countDimDtos())) {
                    foreach( $this->sie4IDto->getDimDtos() as $dimData ) {
                        // checked in dimDtos in validator, MUST exist
                        if( $dimensionNr == $dimData->getDimensionsNr()) {
                            $dimensionsNamn = $dimData->getDimensionsNamn();
                            break;
                        }
                    } // end forech
                } // end if
                $dimensionTypeEntry = DimensionTypeEntry::factoryIdName(
                    $dimensionNr,
                    $dimensionsNamn
                );
                $dimensions->addDimension( $dimensionTypeEntry );
            } // end if ! found
            $dimensionTypeEntry->addObject(
                ObjectType::factoryIdName(
                    $dimObjektDto->getObjektNr(),
                    $dimObjektDto->getObjektNamn()
                )
            );
        } // end foreach
    }

    /**
     * Process Sie4I verDtos into SieEntry
     */
    private function processVerDtos()
    {
        if( empty( $this->sie4IDto->countVerDtos())) {
            return;
        }
        $genSign = $this->sie4IDto->getIdDto()->isGenSignSet()
            ? $this->sie4IDto->getIdDto()->getGenSign()
            : SieEntry::PRODUCTNAME;
        foreach( $this->sie4IDto->getVerDtos() as $verDto ) {
            $journalTypeEntry      = $this->getJournalTypeEntry( $verDto->getSerie());
            $journalEntryTypeEntry = JournalEntryTypeEntry::factory();
            $journalTypeEntry->addJournalEntry( $journalEntryTypeEntry );
            self::processSingleVerDto( $verDto, $journalEntryTypeEntry, $genSign );
        } // end foreach
    }

    /**
     * Return found or new JournalTypeEntry
     *
     * @param null|string $serie
     * @return JournalTypeEntry
     */
    private function getJournalTypeEntry( $serie ) : JournalTypeEntry
    {
        $journalTypeEntryFound = false;
        $journalTypeEntry      = null;
        $journals = $this->sieEntry->getJournal();
        if( ! empty( $journals )) {
            foreach( $journals as $journalTypeEntry ) {
                $journalTypeEntryId = $journalTypeEntry->getId();
                if( empty( $serie ) && empty( $journalTypeEntryId )) {
                    $journalTypeEntryFound = true;
                    break;
                }
                if( 0 === strcmp((string) $serie, (string) $journalTypeEntryId )) {
                    $journalTypeEntryFound = true;
                    break;
                }
            } // end foreach
        } // end if
        if( ! $journalTypeEntryFound ) {
            // create if NOT exists
            $journalTypeEntry = JournalTypeEntry::factory();
            $this->sieEntry->addJournal( $journalTypeEntry );
            if( ! empty( $serie )) {
                $journalTypeEntry->setId( $serie );
            }
        } // end if
        return $journalTypeEntry;
    }

    /**
     * @var string
     */
    private static $YYYYMMDD = 'Ymd';

    /**
     * Process single VerDto
     *
     * If regdatum found, used if regdatum == verDatum
     *
     * @param VerDto                $verDto
     * @param JournalEntryTypeEntry $journalEntryTypeEntry
     * @param string $genSign
     */
    private static function processSingleVerDto(
        VerDto $verDto,
        JournalEntryTypeEntry $journalEntryTypeEntry,
        string $genSign
    )
    {
        if( $verDto->isVernrSet()) {
            $journalEntryTypeEntry->setId( $verDto->getVernr());
        }
        // required
        $verDatum = $verDto->isVerdatumSet()
            ? $verDto->getVerdatum()
            : new DateTime();
        $journalEntryTypeEntry->setJournalDate( $verDatum );
        if( $verDto->isVertextSet()) {
            $journalEntryTypeEntry->setText( $verDto->getVertext());
        }
        $journalEntryTypeEntry->setOriginalEntryInfo(
            OriginalEntryInfoType::factoryByDate(
                ( $verDto->isSignSet() ? $verDto->getSign() : $genSign ),
                ( $verDto->isRegdatumSet() ? $verDto->getRegdatum() : $verDatum )
            )
        );
        foreach( $verDto->getTransDtos() as $transDto ) {
            $ledgerEntryTypeEntry = LedgerEntryTypeEntry::factory();
            $journalEntryTypeEntry->addLedgerEntry( $ledgerEntryTypeEntry );
            self::processSingleTransDto(
                $transDto,
                $ledgerEntryTypeEntry,
                $verDatum->format( self::$YYYYMMDD )
            );
        } // end foreach
    }

    /**
     * @param TransDto             $transDto
     * @param LedgerEntryTypeEntry $ledgerEntryTypeEntry
     * @param string               $verDatum YYYYMMDD
     */
    private static function processSingleTransDto(
        TransDto $transDto,
        LedgerEntryTypeEntry $ledgerEntryTypeEntry,
        string $verDatum
    )
    {
        $ledgerEntryTypeEntry->setAccountId( $transDto->getKontoNr());
        if( 0 < $transDto->countObjektlista()) {
            foreach( $transDto->getObjektlista() as $dimObjektDto ) {
                $ledgerEntryTypeEntry->addLedgerEntryTypeEntry(
                    LedgerEntryTypeEntry::OBJECTREFERENCE,
                    ObjectReferenceType::factoryDimIdObjectId(
                        $dimObjektDto->getDimensionsNr(),
                        $dimObjektDto->getObjektNr()
                    )
                );
            } // end foreach
        }
        $ledgerEntryTypeEntry->setAmount( $transDto->getBelopp());
        if( $transDto->isTransdatSet()) {
            // skipped if equal to verDatum
            $transDat = $transDto->getTransdat();
            if( $verDatum != $transDat->format( self::$YYYYMMDD )) {
                $ledgerEntryTypeEntry->setLedgerDate( $transDto->getTransdat() );
            }
        }
        if( $transDto->isTranstextSet()) {
            $ledgerEntryTypeEntry->setText( $transDto->getTranstext());
        }
        if( $transDto->isKvantitetSet()) {
            $ledgerEntryTypeEntry->setQuantity( $transDto->getKvantitet());
        }
    } // end foreach

    /**
     * @return Sie4IDto
     */
    public function getSie4IDto() : Sie4IDto
    {
        return $this->sie4IDto;
    }

    /**
     * @param Sie4IDto $sie4IDto
     * @return static
     * @throws InvalidArgumentException
     */
    public function setSie4IDto( Sie4IDto $sie4IDto ) : self
    {
        Sie4IValidator::assertSie4IDto( $sie4IDto );
        $this->sie4IDto = $sie4IDto;
        return $this;
    }
}