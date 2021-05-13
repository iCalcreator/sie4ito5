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
namespace Kigkonsult\Sie4Ito5\Util;

class ArrayUtil
{
    /**
     * Return bool true if array[key] is set and is NOT empty
     *
     * If array[key] contains ' ' or '0' returns false
     *
     * @param array $array
     * @param mixed $key
     * @return bool
     */
    public static function arrayKeyExists( array $array, $key ) : bool
    {
        return ( isset( $array[$key] ) && ! empty( $array[$key] ) );
    }

    /**
     * Add end eol to each array element
     *
     * @param array $array
     * @return array
     */
    public static function eolEndElements( array $array ) : array
    {
        $output = [];
        foreach( $array as $key => $value ) {
            $output[$key] = $value . PHP_EOL;
        }
        return $output;
    }
}
