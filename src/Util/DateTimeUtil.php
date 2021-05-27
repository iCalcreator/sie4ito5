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
namespace Kigkonsult\Sie4Ito5\Util;

use DateTime;
use Exception;
use RuntimeException;

use function sprintf;

class DateTimeUtil
{

    /**
     * @param string $dateTimeString
     * @param string $label
     * @param int    $errCode
     * @return DateTime
     */
    public static function getDateTime( string $dateTimeString, string $label, int $errCode ) : DateTime
    {
        static $FMT0 = '%s : %s, %s';
        try {
            $dateTime = new DateTime( $dateTimeString );
        }
        catch( Exception $e ) {
            throw new RuntimeException(
                sprintf( $FMT0, $label, $dateTimeString, $e->getMessage()),
                $errCode,
                $e
            );
        }
        return $dateTime;
    }

}
