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
namespace Kigkonsult\Sie4Ito5\Api;

use Kigkonsult\Sie4Ito5\Dto\AccountDto;
use Kigkonsult\Sie4Ito5\Dto\DimDto;
use Kigkonsult\Sie4Ito5\Dto\DimObjektDto;
use Kigkonsult\Sie4Ito5\Dto\IdDto;
use Kigkonsult\Sie4Ito5\Dto\Sie4IDto;
use Kigkonsult\Sie4Ito5\Sie4IInterface;
use Kigkonsult\Sie4Ito5\Dto\TransDto;
use Kigkonsult\Sie4Ito5\Dto\VerDto;
use Kigkonsult\Sie4Ito5\Util\ArrayUtil;
use Kigkonsult\Sie4Ito5\Util\DateTimeUtil;

/**
 * Class Array2Sie4IDto
 *
 * Transform (HTTP, $_REQUEST) input array to Sie4IDto
 *
 * input format
 * [
 *     self::PROGRAMNAMN       => <programNamn>,
 *     self::PROGRAMVERSION    => <programVersion>,
 *     self::GENDATUM          => <YYYYMMDD-datum>,
 *     self::GENSIGN           => <sign>,
 *     self::FNRID             => <företagsid>,
 *     self::ORGNRORGNR        => <orgnr>,
 *     self::ORGNRFORNVR       => <förvnr>,
 *     self::FTGNAMN           => <företagsnamn>,
 *     self::VALUTAKOD         => <valutakod>,
 *
 *     // Konto data instance share same index
 *     self::KONTONR           => [ *<kontonr> ],
 *     self::KONTONAMN         => [ *<kontonamn> ],
 *     self::KONTOTYP          => [ *<kontoTyp> ],
 *     self::KONTOENHET        => [ *<enhet> ],
 *
 *     // Dimension data instance share same index
 *     self::DIMENSIONNR       => [ *<dimId> ],
 *     self::OBJEKTID          => [ *<objektId> ],
 *
 *     // Objekt(-dimension) data instance share same index
 *     self::OBJEKTDIMENSIONNR => [ *<dimId> ],
 *     self::OBJEKTNR          => [ *<objektNr> ],
 *     self::OBJEKTNAMN        => [ *<objektNamn> ],
 *
 *     // Journal entry data instance share same index
 *     self::VERDATUM          => [ *<YYYYMMDD-verdatum> ],
 *     self::VERSERIE          => [ *serie> ],
 *     self::VERNR             => [ *<vernr> ],
 *     self::VERTEXT           => [ *<vertext> ],
 *     self::REGDATUM          => [ *<YYYYMMDD-regdatum> ],
 *     self::VERSIGN           => [ *<sign> ],
 *
 *     // Ledger data instances within Journal entry data instance share same index
 *     self::TRANSKONTONR     => [ *[ *<kontonr> ] ]
 *     self::TRANSDIMENSIONNR => [ *[ *[ *<dimId> ] ] ],
 *     self::TRANSOBJEKTNR    => [ *[ *[ *<objektnr> ] ] ],
 *     self::BELOPP           => [ *[ *<belopp> ] ]
 *     self::TRANSDAT         => [ *[ *<YYYYMMDD-transdat> ] ]
 *     self::TRANSTEXT        => [ *[ *<transText> ] ]
 *     self::KVANTITET        => [ *[ *<kvantitet> ] ]
 * ]
 */
class Array2Sie4IDto implements Sie4IInterface
{
    /**
     * @var array
     */
    private $input = [];

    /**
     * @var Sie4IDto
     */
    private $sie4IDto = null;

    /**
     * All keys in upper case
     *
     * @param null|array $input
     * @return Sie4IDto
     */
    public static function process( array $input = null ) : Sie4IDto
    {
        $instance = new self();
        if( ! empty( $input )) {
            $instance->setInput( $input );
        }
        $instance->sie4IDto = new Sie4IDto();

        $instance->readIdData();
        $instance->readAccountData();
        $instance->readVerTransData();

        return $instance->getSie4IDto();
    }

    /**
     * Process identifikationsposter in order
     *
     * Works as a parallell with Sie4IParser::readIdData
     */
    private function readIdData()
    {
        $idDto = new IdDto();
        $this->sie4IDto->setIdDto( $idDto );
        /**
         * Vilket program som genererat filen
         * Obligatorisk
         * #PROGRAM programnamn version
         * expected as
         * [
         *     ....
         *     self::PROGRAMNAMN    => <programNamn>,
         *     self::PROGRAMVERSION => <programVersion>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::PROGRAMNAMN] )) {
            $idDto->setProgramnamn( $this->input[self::PROGRAMNAMN] );
        }
        if( isset( $this->input[self::PROGRAMVERSION] )) {
            $idDto->setVersion( $this->input[self::PROGRAMVERSION] );
        }
        /**
         * När och av vem som filen genererats
         * #GEN datum sign
         * Obligatorisk (sign opt) Sie4I, båda obl. Sie5 SieEntry
         * expected as
         * [
         *     ....
         *     self::GENDATUM  => <YYYYMMDD-datum>,
         *     self::GENSIGN   => <sign>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::GENDATUM] )) {
            $idDto->setGenDate(
                DateTimeUtil::getDateTime(
                    $this->input[self::GENDATUM],
                    self::GEN,
                    3511
                )
            );
        }
        if( isset( $this->input[self::GENSIGN] )) {
            $idDto->setGenSign( $this->input[self::GENSIGN] );
        }
        /**
         * Redovisningsprogrammets internkod för exporterat företag
         *
         * #FNR företagsid
         * valfri
         * expected as
         * [
         *     ....
         *     self::FNRID    => <företagsid>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::FNRID] )) {
            $idDto->setFnrId( $this->input[self::FNRID] );
        }
        /**
         * Organisationsnummer för det företag som exporterats
         *
         * #ORGNR orgnr förvnr verknr
         * förvnr : anv då ensk. person driver flera ensk. firmor (ordningsnr)
         * verknr : anv ej
         * valfri, MEN orgnr obligatoriskt i sie4IDto (FileInfoTypeEntry/CompanyTypeEntry)
         * expected as
         * [
         *     ....
         *     self::ORGNRORGNR  => <orgnr>,
         *     self::ORGNRFORNVR => <förvnr>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::ORGNRORGNR] )) {
            $idDto->setOrgnr( $this->input[self::ORGNRORGNR] );
        }
        if( isset( $this->input[self::ORGNRFORNVR] )) {
            $idDto->setMultiple( $this->input[self::ORGNRFORNVR] );
        }
        /**
         * Fullständigt namn för det företag som exporterats
         *
         * #FNAMN företagsnamn
         * Obligatorisk men valfri i sie4IDto (FileInfoTypeEntry/CompanyTypeEntry)
         * expected as
         * [
         *     ....
         *     self::FTGNAMN => <företagsnamn>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::FTGNAMN] )) {
            $idDto->setFnamn( $this->input[self::FTGNAMN] );
        }
        /**
         * Redovisningsvaluta
         *
         * #VALUTA valutakod
         * valfri
         * expected as
         * [
         *     ....
         *     self::VALUTAKOD => <valutakod>,
         *     ....
         * ]
         */
        if( isset( $this->input[self::VALUTAKOD] )) {
            $idDto->setValutakod( $this->input[self::VALUTAKOD] );
        }
    }

    /**
     * Manage Sie4I  'Kontoplansuppgifter'
     *
     * Works as a parallell with Sie4IParser::readAccountData
     */
    private function readAccountData()
    {
        /**
         * expected as
         * [
         *     ....
         *     self::KONTONR    => [ *<kontonr> ],
         *     self::KONTONAMN  => [ *<kontonamn> ],
         *     self::KONTOTYP   => [ *<kontoTyp> ],
         *     self::KONTOENHET => [ *<enhet> ],
         *     ....
         * ]
         */
        if( isset( $this->input[self::KONTONR] )) {
            foreach( array_keys( $this->input[self::KONTONR] ) as $ktoX ) {
                $accountDto = new AccountDto();
                $accountDto->setKontoNr( $this->input[self::KONTONR][$ktoX] );
                if( isset( $this->input[self::KONTONAMN][$ktoX] )) {
                    $accountDto->setKontoNamn( $this->input[self::KONTONAMN][$ktoX] );
                }
                if( isset( $this->input[self::KONTOTYP][$ktoX] )) {
                    $accountDto->setKontoTyp( $this->input[self::KONTOTYP][$ktoX] );
                }
                if( isset( $this->input[self::KONTOENHET][$ktoX] )) {
                    $accountDto->setEnhet( $this->input[self::KONTOENHET][$ktoX] );
                }
                $this->sie4IDto->addAccountDto( $accountDto );
            } // end foreach
        } // end if
        /**
         * expected as
         * [
         *     ....
         *     self::DIMENSIONNR    => [ *<dimId> ],
         *     self::OBJEKTID       => [ *<objektId> ],
         *     ....
         * ]
         */
        if( isset( $this->input[self::DIMENSIONNR] )) {
            foreach( array_keys( $this->input[self::DIMENSIONNR] ) as $dimX ) {
                $dimDto = new DimDto();
                $dimDto->setDimensionsNr( $this->input[self::DIMENSIONNR][$dimX] );
                if( isset( $this->input[self::DIMENSIONNAMN][$dimX] )) {
                    $dimDto->setDimensionsNamn( $this->input[self::DIMENSIONNAMN][$dimX] );
                }
                $this->sie4IDto->addDimDto( $dimDto );
            } // end foreach
        } // end if
        /**
         * expected as
         * [
         *     ....
         *     self::OBJEKTDIMENSIONNR => [ *<dimId> ],
         *     self::OBJEKTNR          => [ *<objektNr> ],
         *     self::OBJEKTNAMN        => [ *<objektNamn> ],
         *     ....
         * ]
         */
        if( isset( $this->input[self::OBJEKTDIMENSIONNR] )) {
            foreach( array_keys( $this->input[self::OBJEKTDIMENSIONNR] ) as $doX ) {
                $dimObjektDto = new DimObjektDto();
                $dimObjektDto->setDimensionsNr( $this->input[self::OBJEKTDIMENSIONNR][$doX] );
                if( isset( $this->input[self::OBJEKTNR][$doX] )) {
                    $dimObjektDto->setObjektNr( $this->input[self::OBJEKTNR][$doX] );
                }
                if( isset( $this->input[self::OBJEKTNAMN][$doX] )) {
                    $dimObjektDto->setObjektNamn( $this->input[self::OBJEKTNAMN][$doX] );
                }
                $this->sie4IDto->addDimObjektDto( $dimObjektDto );
            } // end foreach
        } // end if
    }

    /**
     * Manage Sie4I  'Verifikationsposter' with  #TRANS data
     *
     * Works as a parallell with Sie4IParser::readVerTransData
     * #VER serie vernr verdatum vertext regdatum sign
     *
     * verdatum mandatory in array input
     * expected as
     * [
     *     ....
     *     self::VERDATUM => [ *<YYYYMMDD-verdatum> ],
     *     self::VERSERIE => [ *serie> ],
     *     self::VERNR    => [ *<vernr> ],
     *     self::VERTEXT  => [ *<vertext> ],
     *     self::REGDATUM => [ *<YYYYMMDD-regdatum> ],
     *     self::VERSIGN  => [ *<sign> ],
     *     .... // trans below
     * ]
     */
    private function readVerTransData()
    {
        if( ! isset( $this->input[self::VERDATUM] )) {
            return;
        }
        foreach( array_keys( $this->input[self::VERDATUM] ) as $verX ) {
            $verDto = new VerDto();
            $verDto->setVerdatum(
                DateTimeUtil::getDateTime(
                    $this->input[self::VERDATUM][$verX],
                    self::VER,
                    3711
                )
            );
            if( isset( $this->input[self::VERSERIE][$verX] ) &&
                ! empty( $this->input[self::VERSERIE][$verX] )) {
                $verDto->setSerie( $this->input[self::VERSERIE][$verX] );
            }
            if( isset( $this->input[self::VERNR][$verX] ) &&
                ! empty( $this->input[self::VERNR][$verX] )) {
                $verDto->setVernr( $this->input[self::VERNR][$verX] );
            }
            if( isset( $this->input[self::VERTEXT][$verX] ) &&
                ! empty( $this->input[self::VERTEXT][$verX] )) {
                $verDto->setVertext( $this->input[self::VERTEXT][$verX] );
            }
            if( isset( $this->input[self::REGDATUM][$verX] ) &&
                ! empty( $this->input[self::REGDATUM][$verX] )) {
                $verDto->setRegdatum(
                    DateTimeUtil::getDateTime(
                        $this->input[self::REGDATUM][$verX],
                        self::VER,
                        3712
                    )
                );
            }
            else {
                $verDto->setRegdatum( $verDto->getVerdatum());
            }
            if( isset( $this->input[self::VERSIGN][$verX] ) &&
                ! empty( $this->input[self::VERSIGN][$verX] )) {
                $verDto->setSign( $this->input[self::VERSIGN][$verX] );
            }
            if( isset( $this->input[self::TRANSKONTONR][$verX] )) {
                $this->readTransData( $verX, $verDto );
            }
            $this->sie4IDto->addVerDto( $verDto );
        } // end foreach
    }

    /**
     * expected as
     * [
     *     self::TRANSKONTONR     => [ *[ *<kontonr> ] ]
     *     self::TRANSDIMENSIONNR => [ *[ *[ *<dimId> ] ] ],
     *     self::TRANSOBJEKTNR    => [ *[ *[ *<objektnr> ] ] ],
     *     self::BELOPP           => [ *[ *<belopp> ] ]
     *     self::TRANSDAT         => [ *[ *<YYYYMMDD-transdat> ] ]
     *     self::TRANSTEXT        => [ *[ *<transText> ] ]
     *     self::KVANTITET        => [ *[ *<kvantitet> ] ]
     * ]
     *
     * @param int    $verX
     * @param VerDto $verDto
     */
    private function readTransData( int $verX, VerDto $verDto )
    {
        foreach( array_keys( $this->input[self::TRANSKONTONR][$verX] ) as $transX ) {
            $transDto = new TransDto();
            $transDto->setKontoNr( $this->input[self::TRANSKONTONR][$verX][$transX] );
            if( isset( $this->input[self::TRANSDIMENSIONNR][$verX][$transX] )) {
                foreach( array_keys( $this->input[self::TRANSDIMENSIONNR][$verX][$transX] ) as $doX ) {
                    $dimObjektDto = new DimObjektDto();
                    $dimObjektDto->setDimensionsNr(
                        $this->input[self::TRANSDIMENSIONNR][$verX][$transX][$doX]
                    );
                    if( isset( $this->input[self::TRANSOBJEKTNR][$verX][$transX][$doX] )) {
                        $dimObjektDto->setObjektNr(
                            $this->input[self::TRANSOBJEKTNR][$verX][$transX][$doX]
                        );
                    }
                    $transDto->addObjektlista( $dimObjektDto );
                } // end foreach
            } // end objektLista
            if( isset( $this->input[self::BELOPP][$verX][$transX] )) {
                // accepts empty
                $transDto->setBelopp( $this->input[self::BELOPP][$verX][$transX] );
            }
            if( isset( $this->input[self::TRANSDAT][$verX][$transX] ) &&
                ! empty( $this->input[self::TRANSDAT][$verX][$transX] )) {
                $transDto->setTransdat(
                    DateTimeUtil::getDateTime(
                        $this->input[self::TRANSDAT][$verX][$transX],
                        self::TRANS,
                        3713
                    )
                );
            }
            if( isset( $this->input[self::TRANSTEXT][$verX][$transX] ) &&
                ! empty( $this->input[self::TRANSTEXT][$verX][$transX] )) {
                $transDto->setTranstext( $this->input[self::TRANSTEXT][$verX][$transX] );
            }
            if( isset( $this->input[self::KVANTITET][$verX][$transX] )) {
                // accepts empty
                $transDto->setKvantitet( $this->input[self::KVANTITET][$verX][$transX] );
            }
            if( isset( $this->input[self::TRANSSIGN][$verX][$transX] ) &&
                ! empty( $this->input[self::TRANSSIGN][$verX][$transX] )) {
                $transDto->setSign( $this->input[self::TRANSSIGN][$verX][$transX] );
            }
            $verDto->addTransDto( $transDto );
        } // end foreach
    }

    /**
     * @param array $input
     * @return static
     */
    public function setInput( array $input ) : self
    {
        $this->input = ArrayUtil::arrayChangeKeyCaseRecursive( $input );
        return $this;
    }

    /**
     * @return Sie4IDto
     */
    public function getSie4IDto() : Sie4IDto
    {
        return $this->sie4IDto;
    }
}