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

use Exception;
use RuntimeException;

use function explode;
use function iconv;
use function sprintf;
use function strlen;
use function strpos;
use function strrev;
use function str_replace;
use function substr;
use function trim;

class StringUtil
{
    /**
     * @var string
     */
    public static $ZERO = '0';
    public static $SP0  = '';
    public static $SP1  = ' ';
    public static $CURLYBRACKETS = [ '{', '}' ];
    public static $QUOTE = '"';

    /**
     * Encoding charsets
     *
     * @var string
     */
    private static $UTF8     = 'UTF-8';
    private static $CP437    = 'CP437';
    private static $TRANSLIT = '//TRANSLIT';
    private static $IGNORE   = '//IGNORE';

    /**
     * Error text
     *
     * @var string
     */
    private static $FMT1  = 'Error (%s) converting from %s to %s : %s';

    /**
     * @param string $string
     * @return string
     * @throws RuntimeException
     */
    public static function utf8toCP437( string $string ) : string
    {
        if( empty( $string )) {
            return $string;
        }
        $msg = self::$SP0;
        try {
            $output = iconv( self::$UTF8, self::$CP437 . self::$IGNORE, $string );
        }
        catch( Exception $e ) {
            $output = false;
            $msg = $e->getMessage();
        }
        if( false === $output ) {
            throw new RuntimeException(
                sprintf( self::$FMT1, $msg, self::$UTF8, self::$CP437 . self::$IGNORE, $string ),
                2111
            );
        }
        return $output;
    }

    /**
     * @param string $string
     * @return string
     * @throws RuntimeException
     */
    public static function cp437toUtf8( string $string ) : string
    {
        if( empty( $string )) {
            return $string;
        }
        $msg = self::$SP0;
        try {
            $output = iconv( self::$CP437, self::$UTF8 . self::$IGNORE, $string );
        }
        catch( Exception $e ) {
            $output = false;
            $msg = $e->getMessage();
        }
        if( false === $output ) {
            throw new RuntimeException(
                sprintf( self::$FMT1, $msg, self::$CP437, self::$UTF8 . self::$IGNORE, $string ),
                2211
            );
        }
        return $output;
    }


    /**
     * Convert all EOLs to PHP_EOL, alter double eols to single ones
     *
     * @param string $string
     * @return string
     */
    public static function convEolChar( string $string ) : string
    {
        static $CRLFs = [ "\r\n", "\n\r", "\n", "\r" ];
        static $EOL2  = PHP_EOL . PHP_EOL;
        /* fix eol chars */
        $string = str_replace( $CRLFs, PHP_EOL, $string );
        while( false !== strpos( $string, $EOL2 )) {
            $string = str_replace( $EOL2, PHP_EOL, $string );
        } // end while
        return $string;
    }

    /**
     * Explode string to array using PHP_EOL as separator
     *
     * @param string $string
     * @return array
     */
    public static function string2Arr( string $string ) : array
    {
        return explode( PHP_EOL, $string );
    }

    /**
     * Converts tab-char to space
     *
     * @param string $string
     * @return string
     */
    public static function tab2Space( string $string ) : string
    {
        static $TAB  = '\t';
        return str_replace( $TAB, self::$SP1, $string );
    }

    /**
     * Convert multi-space to single-space, return trimmed string
     *
     * @param string $string
     * @return string
     */
    public static function trimString( string $string ) : string
    {
        static $SP2 = '  ';
        while( self::isIn( $SP2, $string )) {
            $string = str_replace( $SP2, self::$SP1, $string );
        } // end while
        return trim( $string );
    }

    /**
     * Return quoted string
     *
     * @param string $string
     * @return string
     */
    public static function quoteString( string $string ) : string
    {
        return self::$QUOTE . trim( $string ) . self::$QUOTE;
    }

    /**
     * Return string surrounded by curly brackets
     *
     * @param string $string
     * @return string
     */
    public static function curlyBacketsString( string $string ) : string
    {
        return self::$CURLYBRACKETS[0] . trim( $string ) . self::$CURLYBRACKETS[1];
    }

    /**
     * Split post on label and label data, on missing leading #, all is content
     *
     * @param string $post
     * @return array  [ string, string[] ]  i.e. [ label, contentParts[] ]
     */
    public static function splitPost( string $post ) : array
    {
        static $HASH = '#';
        if( ! self::startsWith( $post, $HASH )) {
            return [ null, self::splitContent( $post ) ];
        }
        $label   = self::before( self::$SP1, $post ) . self::$SP1;
        $content = self::splitContent( self::after( $label, $post ));
        return [ $label, $content ];
    }

    /**
     * Split content on space as separator, text within quotes is maintained
     *
     * Content is trimmed, i.e. leading character is NOT space
     *
     * @param string $content
     * @return array
     */
    public static function splitContent( string $content ) : array
    {
        static $BSQ   = '\\"';
        static $QFSs  = [ ' "', '{"' ];
        $content      = trim( $content );
        if( empty( $content )) {
            return [];
        }
        if( in_array( $content, self::$CURLYBRACKETS )) {
            return [ $content ];
        }
        $output        = [];
        $len           = strlen( $content );
        $bracketsFound = false;
        $quoteFound    = false;
        $current       = self::$SP0;
        for( $x = 0; $x < $len; $x++ ) {
            $xNext     = $x + 1;
            $xNext2    = $x + 2;
            $xPrev     = $x - 1;
            switch( true ) {
                case ( ! $bracketsFound &&
                    ( $content[$x] == self::$CURLYBRACKETS[0] ) &&
                    isset( $content[$xNext] ) &&
                    ( self::$SP1  == $content[$xNext] ) &&
                    isset( $content[$xNext2] ) &&
                    ( $content[$xNext2] == self::$CURLYBRACKETS[1] )) :
                    // one space field within curly brackets
                    $output[] = self::$SP0;
                    $current  = self::$SP0;
                    $x       += 2;
                    break;
                case ( ! $bracketsFound &&
                    ( $content[$x] == self::$CURLYBRACKETS[0] ) &&
                    isset( $content[$xNext] ) &&
                    ( $content[$xNext] == self::$CURLYBRACKETS[1] )) :
                    // empty field within curly brackets
                    $output[] = self::$SP0;
                    $current  = self::$SP0;
                    $x       += 1;
                    break;
                case ( in_array( $content[$x], self::$CURLYBRACKETS )) :
                    $bracketsFound = ! $bracketsFound;
                    break;
                case ( ! $quoteFound && ( self::$QUOTE == $content[$x] ) &&
                    isset( $content[$xNext] ) && ( self::$QUOTE == $content[$xNext] )) :
                    // empty quoted field
                    $output[] = self::$SP0;
                    $current  = self::$SP0;
                    $x       += 1;
                    break;
                case ( ! $quoteFound && ( self::$QUOTE == $content[$x] ) &&
                    ( ! isset( $content[$xPrev] ) || in_array( substr( $content, $xPrev, 2 ), $QFSs ))) :
                    // quoted field start
                    $quoteFound = true;
                    break;
                case ( ( self::$QUOTE == $content[$x] ) &&
                    ( isset( $content[$xPrev] ) && ( $BSQ == substr( $content, $xPrev, 2 )))) :
                    // '\"' found
                    $current .= $content[$x];
                    break;
                case ( $quoteFound && ( self::$QUOTE == $content[$x] )) :
                    // quoted field end
                    $quoteFound = false;
                    break;
                case ( $bracketsFound || $quoteFound ) :
                    // character in field, within quotes or curly brackets
                    $current .= $content[$x];
                    break;
                case ( ! $bracketsFound && ( self::$SP1 == $content[$x] )) :
                    // new field, separated by one or more spaces but not within curly brackets
                    if( ! empty( $current )) {
                        // 'close' previous field
                        $output[] = trim( $current );
                        $current  = self::$SP0;
                    }
                    break;
                default :
                    $current .= $content[$x];
                    break;
            } // end switch
        } // end for
        // get last field
        if( ! empty( $current )) {
            $output[] = trim( $current );
        }
        return $output;
    }

    /**
     * @link https://php.net/manual/en/function.substr.php#112707
     */

    /**
     * Return bool true if needle is in haystack
     *
     * Case-sensitive search for needle in haystack
     *
     * @param string $needle
     * @param string $haystack
     * @return bool
     */
    public static function isIn( string $needle, string $haystack ) : bool
    {
        return ( false !== strpos( $haystack, $needle ));
    }

    /**
     * Return substring after first found needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function after( string $needle, string $haystack ) : string
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        $pos = strpos( $haystack, $needle );
        return substr( $haystack, $pos + strlen( $needle ));
    }

    /**
     * Return substring after last found  needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function afterLast( string $needle, string $haystack ) : string
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        $pos = self::strrevpos( $haystack, $needle );
        return substr( $haystack, $pos + strlen( $needle ));
    }

    /**
     * Return substring before first found needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function before( string $needle, string $haystack ) : string
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        return substr( $haystack, 0, strpos( $haystack, $needle ));
    }

    /**
     * Return substring before last needle in haystack, '' on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle
     * @param string $haystack
     * @return string
     */
    public static function beforeLast( string $needle, string $haystack ) : string
    {
        if( ! self::isIn( $needle, $haystack )) {
            return self::$SP0;
        }
        return substr( $haystack, 0, self::strrevpos( $haystack, $needle ));
    }

    /**
     * Return substring between needles in haystack
     *
     * Case-sensitive search for needles in haystack
     * If no needles found in haystack, '' is returned
     * If only needle1 found, substring after is returned
     * If only needle2 found, substring before is returned
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle1
     * @param string $needle2
     * @param string $haystack
     * @return string
     */
    public static function between( string $needle1, string $needle2, string $haystack ) : string
    {
        $exists1 = self::isIn( $needle1, $haystack );
        $exists2 = self::isIn( $needle2, $haystack );
        switch( true ) {
            case ( ! $exists1 && ! $exists2 ) :
                return self::$SP0;
            case ( $exists1  && ! $exists2 ) :
                return self::after( $needle1, $haystack );
            case ( ! $exists1 && $exists2 ) :
                return self::before( $needle2, $haystack );
            default :
                return self::before( $needle2, self::after( $needle1, $haystack ));
        } // end switch
    }

    /**
     * Return substring between last two needles in haystack
     *
     * Case-sensitive search for needles in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $needle1
     * @param string $needle2
     * @param string $haystack
     * @return string
     */
    public static function betweenLast( string $needle1, string $needle2, string $haystack ) : string
    {
        return self::afterLast( $needle1, self::beforeLast( $needle2, $haystack ));
    }

    /**
     * Return int for length from start to last needle in haystack, false on not found
     *
     * Case-sensitive search for needle in haystack
     *
     * @link https://php.net/manual/en/function.substr.php#112707
     * @param string $haystack
     * @param string $needle
     * @return int|bool
     */
    public static function strrevpos( string $haystack, string $needle )
    {
        return ( false !== ( $rev_pos = strpos( strrev( $haystack ), strrev( $needle ))))
            ? ( strlen( $haystack ) - $rev_pos - strlen( $needle ))
            : false;
    }

    /**
     * Return bool true if haystack starts with needle, false on not found or to large
     *
     * Case-sensitive search for needle in haystack
     *
     * @param string $haystack
     * @param string $needle
     * @param null|int $len       if found contains length of needle
     * @return bool
     */
    public static function startsWith( string $haystack, string $needle, & $len = null ) : bool
    {
        $len       = null;
        $needleLen = strlen( $needle );
        if( $needleLen > strlen( $haystack )) {
            return false;
        }
        if( 0 === strpos( $haystack, $needle )) {
            $len = $needleLen;
            return true;
        }
        return false;
    }

    /**
     * Return bool true if haystack ends with needle, false on not found or to large
     *
     * Case-sensitive search for needle in haystack
     *
     * @param string    $haystack
     * @param string    $needle
     * @param null|int  $len       if found contains length of needle
     * @return bool
     */
    public static function endsWith( string $haystack, string $needle, & $len = null ) : bool
    {
        $len       = null;
        $needleLen = strlen( $needle );
        if( $needleLen > strlen( $haystack )) {
            return false;
        }
        if( $needle == substr( $haystack, ( 0 - $needleLen ))) {
            $len = $needleLen;
            return true;
        }
        return false;
    }
}
