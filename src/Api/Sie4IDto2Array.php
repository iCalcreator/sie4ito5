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

use InvalidArgumentException;
use Kigkonsult\Sie4Ito5\Dto\Sie4IDto;
use Kigkonsult\Sie4Ito5\Dto\TransDto;
use Kigkonsult\Sie4Ito5\Sie4IInterface;
use Kigkonsult\Sie4Ito5\Sie4IValidator;
use Kigkonsult\Sie4Ito5\Sie4IWriter;
use Kigkonsult\Sie4Ito5\Util\ArrayUtil;
use Kigkonsult\Sie5Sdk\Impl\CommonFactory;

use function array_keys;

/**
 * Class Sie4IDto2Array
 *
 * Transform Sie4IDto to array
 *
 * output format
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
 *     self::KONTONR           => [ *<kontonr> ],
 *     self::KONTONAMN         => [ *<kontonamn> ],
 *     self::KONTOTYP          => [ *<kontoTyp> ],
 *     self::KONTOENHET        => [ *<enhet> ],
 *
 *     self::DIMENSIONNR       => [ *<dimId> ],
 *     self::OBJEKTID          => [ *<objektId> ],
 *
 *     self::OBJEKTDIMENSIONNR => [ *<dimId> ],
 *     self::OBJEKTNR          => [ *<objektNr> ],
 *     self::OBJEKTNAMN        => [ *<objektNamn> ],
 *
 *
 *     self::VERDATUM          => [ *<YYYYMMDD-verdatum> ],
 *     self::VERSERIE          => [ *serie> ],
 *     self::VERNR             => [ *<vernr> ],
 *     self::VERTEXT           => [ *<vertext> ],
 *     self::REGDATUM          => [ *<YYYYMMDD-regdatum> ],
 *     self::VERSIGN           => [ *<sign> ],
 *
 *     self::TRANSKONTONR     => [ *[ *<kontonr> ] ]
 *     self::TRANSDIMENSIONNR => [ *[ *[ *<dimId> ] ] ],
 *     self::TRANSOBJEKTNR    => [ *[ *[ *<objektnr> ] ] ],
 *     self::BELOPP           => [ *[ *<belopp> ] ]
 *     self::TRANSDAT         => [ *[ *<YYYYMMDD-transdat> ] ]
 *     self::TRANSTEXT        => [ *[ *<transText> ] ]
 *     self::KVANTITET        => [ *[ *<kvantitet> ] ]
 * ]
 */
class Sie4IDto2Array implements Sie4IInterface
{
    /**
     * @var Sie4IDto
     */
    private $sie4IDto = null;

    /**
     * @var array
     */
    private $output = [];

    /**
     * Transform Sie4IDto to array
     *
     * Works as a parallell with Sie5EntryLoader::getSieEntry
     *
     * @param null|Sie4IDto $sie4IDto
     * @return array
     * @throws InvalidArgumentException
     */
    public static function process( $sie4IDto = null ) : array
    {
        $instance = new self();
        if( ! empty( $sie4IDto )) {
            $instance->setSie4IDto( $sie4IDto );
        }
        $instance->output = [];

        $instance->processIdDto();
        $instance->processAccountDtos();
        $instance->processDimDtos();
        $instance->processDimObjektDtos();
        $instance->processVerDtos();

        return $instance->getOutput();
    }

    /**
     */
    private function processIdDto()
    {
        if( ! $this->sie4IDto->isIdDtoSet()) {
            return;
        }
        $idDto    = $this->sie4IDto->getIdDto();
        $this->output[self::PROGRAMNAMN]    = $idDto->getProgramnamn();
        $this->output[self::PROGRAMVERSION] = $idDto->getVersion();
        $this->output[self::GENDATUM] = $idDto->getGenDate()->format( Sie4IWriter::$YYYYMMDD );
        $this->output[self::GENSIGN]  = $idDto->isGenSignSet()
            ? $idDto->getGenSign()
            : self::PRODUCTNAME;
        if( $idDto->isFnrIdSet()) {
            $this->output[self::FNRID] = $idDto->getFnrId();
        }
        if( $idDto->isOrgnrSet()) {
            $this->output[self::ORGNRORGNR]  = $idDto->getOrgnr();
            $this->output[self::ORGNRFORNVR] = $idDto->getMultiple();
        }
        if( $idDto->isFnamnSet()) {
            $this->output[self::FTGNAMN] = $idDto->getFnamn();
        }
        if( $idDto->isValutakodSet()) {
            $this->output[self::VALUTAKOD] = $idDto->getValutakod();
        }
    }

    /**
     */
    private function processAccountDtos()
    {
        static $KEYS = [
            self::KONTONR,
            self::KONTONAMN,
            self::KONTOTYP,
            self::KONTOENHET
        ];
        if( empty( $this->sie4IDto->countAccountDtos())) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        foreach( $this->sie4IDto->getAccountDtos() as $aX => $accountDto ) {
            if( $accountDto->isKontoNrSet()) {
                $this->output[self::KONTONR][$aX]    = $accountDto->getKontoNr();
            }
            if( $accountDto->isKontonamnSet()) {
                $this->output[self::KONTONAMN][$aX]  = $accountDto->getKontoNamn();
            }
            if( $accountDto->isKontotypSet()) {
                $this->output[self::KONTOTYP][$aX]   = $accountDto->getKontoTyp();
            }
            if( $accountDto->isEnhetSet()) {
                $this->output[self::KONTOENHET][$aX] = $accountDto->getEnhet();
            }
        } // end foreach
    }

    /**
     */
    private function processDimDtos()
    {
        static $KEYS = [
            self::DIMENSIONNR,
            self::DIMENSIONNAMN
        ];
        if( empty( $this->sie4IDto->countDimDtos())) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        ArrayUtil::assureIsArray( $this->output, self::DIMENSIONNR );
        ArrayUtil::assureIsArray( $this->output, self::DIMENSIONNAMN );
        foreach( $this->sie4IDto->getDimDtos() as $dX => $dimDto ) {
            if( $dimDto->isDimensionsNrSet()) {
                $this->output[self::DIMENSIONNR][$dX]   = $dimDto->getDimensionsNr();
            }
            if( $dimDto->isDimensionsNamnSet()) {
                $this->output[self::DIMENSIONNAMN][$dX] = $dimDto->getDimensionsNamn();
            }
        } // end foreach
    }

    /**
     */
    private function processDimObjektDtos()
    {
        static $KEYS = [
            self::OBJEKTDIMENSIONNR,
            self::OBJEKTNR,
            self::OBJEKTNAMN
        ];
        $dimObjektDtos = $this->sie4IDto->getDimObjektDtos();
        if( empty( $dimObjektDtos )) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        foreach( $this->sie4IDto->getDimObjektDtos() as $doX => $dimObjektDto ) {
            if( $dimObjektDto->isDimensionsNrSet()) {
                $this->output[self::OBJEKTDIMENSIONNR][$doX] =
                    $dimObjektDto->getDimensionsNr();
            }
            if( $dimObjektDto->isObjektNrSet()) {
                $this->output[self::OBJEKTNR][$doX] = $dimObjektDto->getObjektNr();
            }
            if( $dimObjektDto->isObjektNamnSet()) {
                $this->output[self::OBJEKTNAMN][$doX] = $dimObjektDto->getObjektNamn();
            }
        } // end foreach
    }

    /**
     */
    private function processVerDtos()
    {
        static $KEYS = [
            self::VERSERIE,
            self::VERNR,
            self::VERDATUM,
            self::VERTEXT,
            self::REGDATUM,
            self::VERSIGN,
            self::TRANSKONTONR,
            self::TRANSDIMENSIONNR,
            self::TRANSOBJEKTNR,
            self::BELOPP,
            self::TRANSDAT,
            self::TRANSTEXT,
            self::KVANTITET,
            self::TRANSSIGN
        ];
        if( empty( $this->sie4IDto->countVerDtos())) {
            return;
        }
        ArrayUtil::assureIsArray( $this->output, $KEYS );
        foreach( $this->sie4IDto->getVerDtos() as $vX => $verDto ) {
            if( $verDto->isSerieSet()) {
                $this->output[self::VERSERIE][$vX] = $verDto->getSerie();
            }
            if( $verDto->isVernrSet()) {
                $this->output[self::VERNR][$vX] = $verDto->getVernr();
            }
            if( $verDto->isVerdatumSet()) {
                $this->output[self::VERDATUM][$vX] =
                    $verDto->getVerdatum()->format( Sie4IWriter::$YYYYMMDD );
            }
            if( $verDto->isVertextSet()) {
                $this->output[self::VERTEXT][$vX] = $verDto->getVertext();
            }
            if( $verDto->isRegdatumSet()) {
                $this->output[self::REGDATUM][$vX] =
                    $verDto->getRegdatum()->format( Sie4IWriter::$YYYYMMDD );
            }
            if( $verDto->isSignSet()) {
                $this->output[self::VERSIGN][$vX] = $verDto->getSign();
            }
            foreach( $verDto->getTransDtos() as $tX =>$transDto ) {
                self::processSingleTransDto( $vX, $tX, $transDto );
            }
        } // end foreach
    }

    /**
     * @param int      $vX
     * @param int      $tX
     * @param TransDto $transDto
     */
    private function processSingleTransDto(
        int $vX,
        int $tX,
        TransDto $transDto
    )
    {
        if( $transDto->isKontoNrSet()) {
            $this->output[self::TRANSKONTONR][$vX][$tX] = $transDto->getKontoNr();
        }
        if( 0 < $transDto->countObjektlista()) {
            foreach( $transDto->getObjektlista() as $doX => $dimObjektDto ) {
                $this->output[self::TRANSDIMENSIONNR][$vX][$tX][$doX] =
                    $dimObjektDto->getDimensionsNr();
                $this->output[self::TRANSOBJEKTNR][$vX][$tX][$doX]    =
                    $dimObjektDto->getObjektNr();
            } // end foreach
        }
        if( $transDto->isBeloppSet()) {
            $this->output[self::BELOPP][$vX][$tX] =
                CommonFactory::formatAmount( $transDto->getBelopp());
        }
        if( $transDto->isTransdatSet()) {
            $this->output[self::TRANSDAT][$vX][$tX] =
                $transDto->getTransdat()->format( Sie4IWriter::$YYYYMMDD );
        }
        if( $transDto->isTranstextSet()) {
            $this->output[self::TRANSTEXT][$vX][$tX] = $transDto->getTranstext();
        }
        if( $transDto->isKvantitetSet()) {
            $this->output[self::KVANTITET][$vX][$tX] = $transDto->getKvantitet();
        }
        if( $transDto->isSignSet()) {
            $this->output[self::TRANSSIGN][$vX][$tX] = $transDto->getSign();
        }
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

    /**
     * @return array
     */
    public function getOutput() : array
    {
        foreach( array_keys( $this->output ) as $key ) {
            if( empty( $this->output[$key] )) {
                unset( $this->output[$key] );
            }
        }
        return $this->output;
    }
}