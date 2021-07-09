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

use InvalidArgumentException;
use RuntimeException;

use function file;
use function file_exists;
use function file_put_contents;
use function is_file;
use function is_readable;
use function is_writable;
use function touch;

class FileUtil
{
    /**
     * Assert file exists and is readable
     *
     * @param string $fileName
     * @param int    $errCode
     * @throws InvalidArgumentException
     */
    public static function assertReadFile( string $fileName, int $errCode )
    {
        static $FMT1 = ' do NO exists';
        static $FMT2 = ' is NO file';
        static $FMT3 = ' is NOT readable';
        $errCode *= 10;
        if( ! file_exists( $fileName )) {
            throw new InvalidArgumentException( $fileName . $FMT1, ( 2 + $errCode ));
        }
        if( ! is_file( $fileName )) {
            throw new InvalidArgumentException( $fileName . $FMT2, ( 2 + $errCode ));
        }
        if( ! is_readable( $fileName )) {
            throw new InvalidArgumentException( $fileName . $FMT3, ( 3 + $errCode ));
        }
        clearstatcache( false, $fileName );
    }

    /**
     * Read file into array, without line endings or empty lines
     *
     * @param string $fileName
     * @param int    $errCode
     * @return array
     * @throws RuntimeException
     */
    public static function readFile( string $fileName, int $errCode ) : array
    {
        static $FMT3 = 'Can\'t read ';
        static $FMT4 = ' is EMPTY';
        $input    = file( $fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        $errCode *= 10;
        if( false === $input ) {
            throw new RuntimeException( $FMT3 . $fileName, ( 1 + $errCode ));
        }
        if( empty( $input )) {
            throw new RuntimeException( $fileName . $FMT4, ( 2 + $errCode ));
        }
        return $input;
    }

    /**
     * Assert file is writable, created if not exists
     *
     * @param string $fileName
     * @param int    $errCode
     * @throws InvalidArgumentException
     */
    public static function assertWriteFile( string $fileName, int $errCode )
    {
        static $FMT1 = 'Can\'t create ';
        static $FMT2 = ' is NOT writeable ';
        $errCode *= 10;
        if( ! file_exists( $fileName ) && ( false === touch( $fileName ))) {
            throw new InvalidArgumentException( $FMT1 . $fileName, ( 1 + $errCode ));
        }
        if( ! is_writable( $fileName )) {
            throw new InvalidArgumentException( $fileName . $FMT2, ( 2 + $errCode ));
        }
    }

    /**
     * Write file from array
     *
     * @param string  $fileName
     * @param string|array $output
     * @param int     $errCode
     * @throws RunTimeException
     */
    public static function writeFile( string $fileName, $output, int $errCode )
    {
        static $FMT3 = 'Can\'t write to ';
        $errCode *= 10;
        if( false === file_put_contents( $fileName, $output )) {
            throw new RuntimeException( $FMT3 . $fileName, ( 1 + $errCode ));
        }
    }
}
