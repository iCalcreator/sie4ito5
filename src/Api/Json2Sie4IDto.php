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

use function json_decode;

/**
 * Class Json2Sie4IDto
 *
 * Transform input json string to Sie4IDto
 */
class Json2Sie4IDto
{
    /**
     * @param string $json
     * @return Sie4IDto
     * @throws InvalidArgumentException
     */
    public static function process( string $json ) : Sie4IDto
    {
        static $ERR1 = 'json string to array error, ';
        $sie4IArray = json_decode( $json, true, 512, JSON_OBJECT_AS_ARRAY );
        if( ! is_array( $sie4IArray )) {
            throw new InvalidArgumentException( $ERR1 . json_last_error_msg(), 4001 ) ;
        }
        return Array2Sie4IDto::process( $sie4IArray );
    }
}