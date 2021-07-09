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
namespace Kigkonsult\Sie4Ito5\Util;

use function array_change_key_case;
use function array_map;
use function array_pad;
use function is_array;
use function count;

class ArrayUtil
{
    /**
     * @param array  $array
     * @param string|array $key
     */
    public static function assureIsArray( array & $array, $key )
    {
        foreach((array) $key as $k ) {
            if( ! isset( $array[$k] ) ) {
                $array[$k] = [];
            }
        }
    }

    /**
     * @param array  $array
     * @param int    $length
     */
    public static function assureArrayLength( array & $array, int $length )
    {
        if( $length > count( $array )) {
            $array = array_pad( $array, $length, null );
        }
    }

    /**
     * Recursive array_change_key_case, uppercased
     *
     * @param array $array
     * @return array|array[]
     * @link https://www.php.net/manual/en/function.array-change-key-case.php#114914
     */
    public static function arrayChangeKeyCaseRecursive( array $array ) : array
    {
        return array_map( function( $item ) {
            if( is_array( $item )) {
                $item = self::arrayChangeKeyCaseRecursive( $item );
            }
            return $item;
        },
            array_change_key_case( $array, CASE_UPPER )
        );
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
