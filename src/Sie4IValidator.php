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
use Kigkonsult\Sie4Ito5\Util\StringUtil;

class Sie4IValidator implements Sie4IInterface
{

    /**
     * Validate #FLAGGA, #SIETYP == 4 and at least one #VER must exist (in order)
     *
     * @param It $sie4Iinput file rows iterator
     * @return bool  true if two #KSUMMA is set otherwise false
     * @throws InvalidArgumentException
     */
    public static function validateInput( It $sie4Iinput )
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
}
