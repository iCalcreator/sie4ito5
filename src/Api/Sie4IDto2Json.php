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
use Kigkonsult\Sie4Ito5\Sie4IValidator;

class Sie4IDto2Json
{
    /**
     * Transform Sie4IDto to json string
     *
     * Assert input Sie4IDto
     *
     * @param Sie4IDto $sie4IDto
     * @return string
     */
    public static function process( Sie4IDto $sie4IDto ) : string
    {
        static $ERR1 = 'array to json string error, ';
        Sie4IValidator::assertSie4IDto( $sie4IDto );
        if( false === ( $string = json_encode( Sie4IDto2Array::process( $sie4IDto )))) {
            throw new InvalidArgumentException( $ERR1 . json_last_error_msg(), 7001 );
        }
        return $string;
    }
}