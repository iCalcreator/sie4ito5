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
use Kigkonsult\Sie4Ito5\Dto\AccountDto;
use Kigkonsult\Sie4Ito5\Dto\IdDto;
use Kigkonsult\Sie4Ito5\Dto\Sie4IDto;
use Kigkonsult\Sie4Ito5\Dto\TransDto;
use Kigkonsult\Sie4Ito5\Dto\VerDto;
use Kigkonsult\Sie5Sdk\Dto\JournalEntryTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\LedgerEntryTypeEntry;
use Kigkonsult\Sie5Sdk\Dto\SieEntry;

/**
 * Class Sie4ILoader
 *
 * Convert SieEntry data into Sie4IDto
 */
class Sie4ILoader
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
     * Sie4ILoader constructor
     */
    public function __construct()
    {
        $this->sie4IDto = new Sie4IDto();
    }

    /**
     * @param null|SieEntry $sieEntry
     * @return static
     */
    public static function factory( $sieEntry = null ) : self
    {
        $instance = new self();
        if( ! empty( $sieEntry )) {
            $instance->setSieEntry( $sieEntry );
        }
        return $instance;
    }

    /**
     * @param null|SieEntry $sieEntry
     * @return Sie4IDto
     */
    public function getSie4IDto( $sieEntry = null ) : Sie4IDto
    {
        static $FMT1 = 'SieEntry saknas';
        static $FMT2 = 'Ofullständig SieEntry indata';
        if( ! empty( $sieEntry )) {
            $this->sie4IDto = new Sie4IDto();
            $this->setSieEntry( $sieEntry );
        }
        if( ! $this->isSieEntrySet()) {
            throw new InvalidArgumentException( $FMT1, 4201 );
        }
        if( ! $this->sieEntry->isValid()) {
            throw new InvalidArgumentException( $FMT2, 4201 );
        }

        $this->processIdData();
        $this->processAccountData();
        $this->processDimData();
        $this->processVerData();

        return $this->sie4IDto;
    }

    /**
     * Updates IdData
     */
    private function processIdData()
    {
        $idDto = new IdDto();
        $fileInfo = $this->sieEntry->getFileInfo();

        $softwareProduct = $fileInfo->getSoftwareProduct();
        $value = $softwareProduct->getName();
        if( ! empty( $value )) {
            $idDto->setProgramnamn( $value );
        }
        $value = $softwareProduct->getVersion();
        if( ! empty( $value )) {
            $idDto->setVersion( $value );
        }

        $fileCreation = $fileInfo->getFileCreation();
        $value = $fileCreation->getTime();
        if( ! empty( $value )) {
            $idDto->setGenDate( $value);
        }
        $value = $fileCreation->getBy();
        if( ! empty( $value )) {
            $idDto->setGenSign( $value);
        }

        $company = $fileInfo->getCompany();
        $value   = $company->getClientId();
        if( ! empty( $value )) {
            $idDto->setFnrId(  $value );
        }

        $value = $company->getOrganizationId();
        if( ! empty( $value )) {
            $idDto->setOrgnr( $value );
            $value = $company->getMultiple();
            if( ! empty( $value )) {
                $idDto->setMultiple( $company->getMultiple() );
            }
        }

        $value = $company->getName();
        if( ! empty( $value )) {
            $idDto->setFnamn( $value );
        }

        $accountingCurrency = $fileInfo->getAccountingCurrency();
        if( ! empty( $accountingCurrency )) {
            $value = $accountingCurrency->getCurrency();
            if( ! empty( $value ) ) {
                $idDto->setValutakod( $value );
            }
        }

        $this->sie4IDto->setIdDto( $idDto );
    }

    /**
     * Updates AccountData
     */
    private function processAccountData()
    {
        $accounts = $this->sieEntry->getAccounts();
        if( empty( $accounts )) {
            return;
        }
        foreach( $accounts->getAccount() as $accountTypeEntry ) {
            $this->sie4IDto->addAccount(
                $accountTypeEntry->getId(),
                $accountTypeEntry->getName(),
                AccountDto::getKontoType( $accountTypeEntry->getType(), true ),
                $accountTypeEntry->getUnit()
            );
        } // end foreach
    }

    /**
     * Updates DimData and DimObjektData
     */
    private function processDimData()
    {
        $dimensions = $this->sieEntry->getDimensions();
        if( empty( $dimensions )) {
            return;
        }
        foreach( $dimensions->getDimension() as $dimensionTypeEntry ) {
            $dimensionsNr   = $dimensionTypeEntry->getId();
            $dimensionsNamn = $dimensionTypeEntry->getName();
            $this->sie4IDto->addDim(
                $dimensionsNr,
                $dimensionsNamn
            );
            $objects = $dimensionTypeEntry->getObject();
            if( empty( $objects )) {
                continue;
            }
            foreach( $objects as $objectType ) {
                $this->sie4IDto->addDimObjekt(
                    $dimensionsNr,
                    $objectType->getId(),
                    $objectType->getName()
                );
            } // end foreach
        } // end foreach
    }

    /**
     * Updates verDto/TransDto
     */
    private function processVerData()
    {
        $journals = $this->sieEntry->getJournal();
        if( empty( $journals ) ) {
            return; // ??
        }
        foreach( $journals as $journalTypeEntry ) {
            $serie = $journalTypeEntry->getId();
            foreach( $journalTypeEntry->getJournalEntry() as $journalEntryTypeEntry ) {
                $this->sie4IDto->addVerDto(
                    self::getVerDto(
                        $journalEntryTypeEntry,
                        $serie
                    )
                );
            } // end foreach
        } // end foreach
    }

    /**
     * @param JournalEntryTypeEntry $journalEntryTypeEntry
     * @param null|int|string $serie
     * @return VerDto
     */
    private static function getVerDto(
        JournalEntryTypeEntry $journalEntryTypeEntry,
        $serie
    ) : VerDto
    {
        $verDto  = new VerDto();
        if( ! empty( $serie ) || ( '0' === $serie )) {
            $verDto->setSerie( $serie );
        }
        $verNr   = $journalEntryTypeEntry->getId();
        if( ! empty( $verNr )) {
            $verDto->setVernr( $journalEntryTypeEntry->getId());
        }
        $verDto->setVerdatum( $journalEntryTypeEntry->getJournalDate());
        $vertext = $journalEntryTypeEntry->getText();
        if( ! empty( $vertext )) {
            $verDto->setVertext( $vertext );
        }
        $originalEntryInfo = $journalEntryTypeEntry->getOriginalEntryInfo();
        $verDto->setRegdatum( $originalEntryInfo->getDate());
        $verDto->setSign( $originalEntryInfo->getBy());
        foreach( $journalEntryTypeEntry->getLedgerEntry() as $ledgerEntryTypeEntry ) {
            $verDto->addTransDto( self::getTransDto( $ledgerEntryTypeEntry ));
        } // end foreach
        return $verDto;
    }

    /**
     * @param LedgerEntryTypeEntry $ledgerEntryTypeEntry
     * @return TransDto
     */
    private static function getTransDto( LedgerEntryTypeEntry $ledgerEntryTypeEntry ) : TransDto
    {
        $transDto      = new TransDto();
        $transDto->setKontoNr( $ledgerEntryTypeEntry->getAccountId());
        $dimObjektData = $ledgerEntryTypeEntry->getLedgerEntryTypeEntries();
        if( ! empty( $dimObjektData )) {
            foreach( $dimObjektData as $elementSets ) {
                foreach( $elementSets as $elementSet ) {
                    if( ! isset( $elementSet[LedgerEntryTypeEntry::OBJECTREFERENCE] )) {
                        continue 2;
                    }
                    $objectReferenceType = $elementSet[LedgerEntryTypeEntry::OBJECTREFERENCE];
                    $transDto->addDimIdObjektId(
                        $objectReferenceType->getDimId(),
                        $objectReferenceType->getObjectId()
                    );
                } // end foreach
            } // end foreach
        } // end if
        $transDto->setBelopp( $ledgerEntryTypeEntry->getAmount());
        $transDate = $ledgerEntryTypeEntry->getLedgerDate();
        if( ! empty( $transDate )) {
            $transDto->setTransdat( $transDate );
        }
        $transtext = $ledgerEntryTypeEntry->getText();
        if( ! empty( $transtext )) {
            $transDto->setTranstext( $transtext );
        }
        $kvantitet = $ledgerEntryTypeEntry->getQuantity();
        if( null !== $kvantitet ) {
            $transDto->setKvantitet( $kvantitet );
        }
        return $transDto;
    }

    /**
     * @return SieEntry
     */
    public function getSieEntry() : SieEntry
    {
        return $this->sieEntry;
    }

    /**
     * @return bool
     */
    public function isSieEntrySet() : bool
    {
        return ( null !== $this->sieEntry );
    }

    /**
     * @param SieEntry $sieEntry
     * @return static
     */
    public function setSieEntry( SieEntry $sieEntry ) : self
    {
        $this->sieEntry = $sieEntry;
        return $this;
    }
}