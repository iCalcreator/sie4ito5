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
use Kigkonsult\Sie4Ito5\Dto\AccountDto;
use Kigkonsult\Sie4Ito5\Dto\DimDto;
use Kigkonsult\Sie4Ito5\Dto\DimObjektDto;
use Kigkonsult\Sie4Ito5\Dto\IdDto;
use Kigkonsult\Sie4Ito5\Dto\Sie4IDto;
use Kigkonsult\Sie4Ito5\Dto\TransDto;
use Kigkonsult\Sie4Ito5\Dto\VerDto;
use Kigkonsult\Sie4Ito5\Util\StringUtil;

use function is_scalar;
use function ctype_digit;
use function sprintf;
use function var_export;

class Sie4IValidator implements Sie4IInterface
{
    /**
     * Validate #FLAGGA, #SIETYP == 4 and at least one #VER must exist (in order)
     *
     * @param It $sie4Iinput file rows iterator
     * @return bool  true if two #KSUMMA is set otherwise false
     * @throws InvalidArgumentException
     */
    public static function assertSie4IInput( It $sie4Iinput ) : bool
    {
        static $FMT1 = 'Input saknar poster';
        static $FMT2 = 'Ogiltig 1:a post';
        static $FOUR = '4';
        static $FMTx = ' saknas';
        if( empty( $sie4Iinput->count())) {
            throw new InvalidArgumentException( $FMT1, 3211 );
        }
        $sie4Iinput->rewind();
        if( ! $sie4Iinput->valid()) {
            throw new InvalidArgumentException( $FMT2, 3212 );
        }
        $flaggaExist = $sieType4Exist = $orgNrExist = $verExist = false;
        $ksummaCnt   = 0;
        while( $sie4Iinput->valid()) {
            $post = $sie4Iinput->current();
            switch( true ) {
                case empty( $post ) :
                    break;
                case StringUtil::startsWith( $post, Sie4IInterface::FLAGGA ) :
                    $flaggaExist = true;
                    break;
                case StringUtil::startsWith( $post, Sie4IInterface::KSUMMA ) :
                    $ksummaCnt += 1;
                    break;
                case ( $flaggaExist &&
                    StringUtil::startsWith( $post, Sie4IInterface::SIETYP ) &&
                    StringUtil::isIn( $FOUR, StringUtil::after( Sie4IInterface::SIETYP, $post ))) :
                    $sieType4Exist = true;
                    break;
                case ( $sieType4Exist &&
                    $flaggaExist &&
                    StringUtil::startsWith( $post, Sie4IInterface::ORGNR )) :
                    $orgNrExist = true;
                    break;
                case ( $orgNrExist &&
                    $sieType4Exist &&
                    $flaggaExist &&
                    StringUtil::startsWith( $post, Sie4IInterface::VER )) :
                    $verExist = true;
                    break;
                case ( $verExist &&
                    $orgNrExist &&
                    $sieType4Exist &&
                    $flaggaExist ) :
                    // leave while if all ok
                    break;
            } // end switch
            $sie4Iinput->next();
        } // end while
        if( ! $flaggaExist ) {
            throw new InvalidArgumentException( Sie4IInterface::VER . $FMTx, 3213 );
        }
        if( ! $sieType4Exist ) {
            throw new InvalidArgumentException( Sie4IInterface::SIETYP . $FMTx, 3214 );
        }
        if( ! $orgNrExist ) {
            throw new InvalidArgumentException( Sie4IInterface::ORGNR . $FMTx, 3215 );
        }
        if( ! $verExist ) {
            throw new InvalidArgumentException( Sie4IInterface::VER . $FMTx, 3216 );
        }
        return ( 2 == $ksummaCnt );
    }

    /**
     * Assert mandatory sie4IDto properties
     *
     * @param Sie4IDto $sie4Idata
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function assertSie4IDto( Sie4IDto $sie4Idata ) : bool
    {
        static $FMT1 = 'Sie4I idDto saknas';
        static $FMT2 = 'verifikationer saknas';
        if( ! $sie4Idata->isIdDtoSet()) {
            throw new InvalidArgumentException( $FMT1, 3311 );
        }
        self::assertIdDto( $sie4Idata->getIdDto());
        if( 0 < $sie4Idata->countAccountDtos()) {
            foreach( $sie4Idata->getAccountDtos() as $ax => $accountDto ) {
                self::assertAccountDto( $ax, $accountDto );
            }
        }
        if( 0 < $sie4Idata->countDimDtos()) {
            foreach( $sie4Idata->getDimDtos() as $dx => $dimDto ) {
                self::assertDimDto( $dx, $dimDto );
            }
        }
        if( 0 < $sie4Idata->countDimObjektDtos()) {
            $dimDtos = $sie4Idata->getDimDtos();
            foreach( $sie4Idata->getDimObjektDtos() as $dox => $dimObjektDto ) {
                self::assertDimObjektDto( $dox, $dimObjektDto, $dimDtos );
            }
        }
        if( empty( $sie4Idata->countVerDtos())) {
            throw new InvalidArgumentException( $FMT2,3316 );
        }
        foreach( $sie4Idata->getVerDtos() as $vx => $verDto ) {
            self::assertVerDto( $vx, $verDto );
        } // end foreach
        return true;
    }

    /**
     * Validate mandatory properties in IdDto
     *
     * Program name/version, gen date and Company name required
     * gen date and program name/version auto set if missing
     *   in Sie4IDto, Sie4IWriter and Sie5EntryLoader
     *
     * @param IdDto $idDto
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function assertIdDto( IdDto $idDto ) : bool
    {
        static $FMT3 = 'Företagsnamn saknas';
        if( ! $idDto->isFnamnSet()) {
            throw new InvalidArgumentException( $FMT3, 3411 );
        }
        return true;
    }

    /**
     * Validate mandatory properties in AccountDto
     *
     * KontoNr/namn/typ required
     *
     * @param int $ax
     * @param AccountDto $accountDto
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function assertAccountDto( int $ax, AccountDto $accountDto ) : bool
    {
        static $FMT1 = '#%d KontoNr/namn/typ förväntas';
        if( ! $accountDto->isKontoNrSet() ||
            ! $accountDto->isKontonamnSet() ||
            ! $accountDto->isKontotypSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, $ax ),3511 );
        }
        return true;
    }

    /**
     * Validate mandatory properties in (array) DimDto
     *
     * Dimension nr and name required
     *
     * @param int $dx
     * @param DimDto $dimDto
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function assertDimDto( int $dx, DimDto $dimDto ) : bool
    {
        static $FMT1 = 'dimensionsNr (#%d) förväntas';
        static $FMT2 = 'dimensionsNamn (#%d) förväntas';
        if( ! $dimDto->isDimensionsNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, $dx ),3611 );
        }
        if( ! $dimDto->isDimensionsNamnSet()) {
            throw new InvalidArgumentException( sprintf( $FMT2, $dx ),3612 );
        }
        return true;
    }

    /**
     * Validate mandatory properties in (array) DimObjektDto
     *
     * Dimensionsnr, objektnr/name required
     * If dimensionsnamn missing, dimDto MUST exist for dimensionsnr
     *
     * @param int $dox
     * @param DimObjektDto $dimObjektDto
     * @param DimDto[]       $dimDtos
     * @return bool
     * @throws InvalidArgumentException
     */
    public static function assertDimObjektDto(
        int $dox,
        DimObjektDto $dimObjektDto,
        array $dimDtos
    ) : bool
    {
        static $FMT1 = '#%d, dimensionsNr förväntas';
        static $FMT2 = '#%d, objektNr förväntas';
        static $FMT3 = '#%d, objektNamn förväntas';
        static $FMT4 = '#%d, dimension %s %s olikt dimNr namn %s';
        if( ! $dimObjektDto->isDimensionsNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, $dox ),3711 );
        }
        if( ! $dimObjektDto->isObjektNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT2, $dox ),3712 );
        }
        if( ! $dimObjektDto->isObjektNamnSet()) {
            throw new InvalidArgumentException( sprintf( $FMT3, $dox ),3713 );
        }
        if( $dimObjektDto->isDimensionsNamnSet()) {
            $dimensionNr   = $dimObjektDto->getDimensionsNr();
            $dimensionNamn = $dimObjektDto->getDimensionsNamn();
            foreach( $dimDtos as $dimDto ) {
                if( $dimensionNr == $dimDto->getDimensionsNr()) {
                    $dimNamn = $dimDto->getDimensionsNamn();
                    if( $dimensionNamn != $dimNamn) {
                        throw new InvalidArgumentException(
                            sprintf( $FMT4, $dox, $dimensionNr, $dimensionNamn, $dimNamn ),
                            3715
                        );
                    }
                    break;
                }
            } // end foreach
        }
        return true;
    }

    /**
     * Validate mandatory properties in VerDto and TransDtos array property
     *
     * Verdatum and trans required
     *
     * @param int    $vx
     * @param VerDto $verDto
     * @return bool
     */
    public static function assertVerDto( int $vx, VerDto $verDto ) : bool
    {
        static $FMT1 = '#%d, verdatum saknas';
        static $FMT2 = '#%d, konteringsrader saknas';
        if( ! $verDto->isVerdatumSet()) {
            throw new InvalidArgumentException( sprintf( $FMT1, $vx ),3811 );
        }
        if( empty( $verDto->countTransDtos())) {
            throw new InvalidArgumentException( sprintf( $FMT2, $vx ),3814 );
        }
        foreach( $verDto->getTransDtos() as $kx => $transDto ) {
            self::assertTransDto( $vx, $kx, $transDto );
        }
        return true;
    }

    /**
     * Validate mandatory properties in each VerDto's property TransDtos array element
     *
     * In each trans, kontonr and belopp required,
     *   in trans objektlista, if exists, pairs of dimension and objektnr required
     *
     * @param int      $vx
     * @param int      $kx
     * @param TransDto $transDto
     * @return bool
     */
    public static function assertTransDto( int $vx, int $kx, TransDto $transDto ) : bool
    {
        static $FMT3 = 'ver #%d trans #%d, kontoNr saknas';
        static $FMT4 = 'ver #%d trans #%d, belopp saknas';
        static $FMT6 = 'ver #%d trans #%d, dimensionsnr och objektnr (%d) förväntas';
        if( ! $transDto->isKontoNrSet()) {
            throw new InvalidArgumentException( sprintf( $FMT3, $vx, $kx ), 3911 );
        }
        if( ! $transDto->isBeloppSet()) {
            throw new InvalidArgumentException( sprintf( $FMT4, $vx, $kx ), 3912 );
        }
        if( 0 < $transDto->countObjektlista()) {
            foreach( $transDto->getObjektlista() as $dox => $dimObjekt ) {
                if( ! $dimObjekt->isDimensionsNrSet() || ! $dimObjekt->isObjektNrSet() ) {
                    throw new InvalidArgumentException(
                        sprintf( $FMT6, $vx, $kx, $dox ),
                        3914
                    );
                }
            } // end foreach
        }
        return true;
    }

    /**
     * @param int|string $value
     * @throws InvalidArgumentException
     */
    public static function assertIntegerish( $value )
    {
        static $ERR = 'Expects integer value, got %s';
        if( ! is_scalar( $value ) || ! ctype_digit( (string) $value )) {
            throw new InvalidArgumentException(
                sprintf( $ERR, var_export( $value, true ))
            );
        }
    }
}
