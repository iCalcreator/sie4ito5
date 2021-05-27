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
use Kigkonsult\Sie5Sdk\Dto\SieEntry;
use Kigkonsult\Sie5Sdk\XMLParse\Sie5Parser;
use Kigkonsult\Sie5Sdk\XMLWrite\Sie5Writer;
use RuntimeException;

/**
 * Class Sie4I
 *
 * @package Kigkonsult\Sie4Ito5
 *
 * Parse Sie4I comments :
 *
 *   Note för #GEN
 *     if 'sign' is missing, '#PROGRAM programnamn' is used
 *
 *   #PROSA, #FTYP, #ADRESS, #RAR, #TAXAR, #KPTYP are skipped
 *
 *   #SRU and #UNDERDIM are skipped
 *
 *   Note för #VER
 *     if 'regdatum' is missing, 'verdatum' is used
 *     if 'sign' is missing, '#GEN sign' is used
 *
 *   Note för #TRANS
 *     only support for 'dimensionsnummer och objektnummer' in the 'objektlista'
 *     i.e. no support for 'hierarkiska dimensioner'
 *   #RTRANS and #BTRANS are skipped
 *
 * Write Sie4I comments
 *   Sie4I file creation date has format 'YYYYmmdd', SieEntry 'YYYY-MM-DDThh:mm:ssZ'
 *
 *   The #KSUMMA checksum is experimental
 */
class Sie4I
{
    /**
     * Return instance
     *
     * @return static
     */
    public static function factory() : self
    {
        return new self();
    }

    /**
     * Parse Sie4I, input from Sie4I file, -array, -string, return SieEntry
     *
     * @param mixed $source
     * @return SieEntry
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function parse4I( $source ) : SieEntry
    {
        return Sie4Iparser::factory()->parse4I( $source );
    }

    /**
     * Write Sie4I from Sie5 (SieEntry)
     *
     * @param SieEntry     $sieEntry
     * @param null|string  $fileName
     * @param null|bool    $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     */
    public function write4I( SieEntry $sieEntry, $fileName = null, $writeKsumma = false ) : string
    {
        return Sie4Iwriter::factory()->write4I( $sieEntry, $fileName, $writeKsumma );
    }

    /**
     * Parse Sie4I string/file into Sie5 (SieEntry) XML string
     *
     * @param string|array $source       string: Sie4I content or fileName, array : Sie4I rows
     * @return string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function sie4Ito5Xml( $source ) : string
    {
        return
            Sie5Writer::factory()
                ->write(
                    Sie4Iparser::factory()->parse4I( $source )
                );
    }

    /**
     * Parse Sie5 (SieEntry) XML file info Sie4I string/file
     *
     * @param string $source          string: Sie4I content or fileName, array : Sie4I rows
     * @param null|string  $fileName  output fileName
     * @return string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function sie5XmlFileTo4I( string $source, $fileName = null) : string
    {
        return
            Sie4Iwriter::factory()
                       ->write4I(
                           Sie5Parser::factory()->parseXmlFromFile( $source ),
                           $fileName
                       );
    }

    /**
     * Parse Sie5 (SieEntry) XML string info Sie4I string/file
     *
     * @param string $source          string: Sie4I content or fileName, array : Sie4I rows
     * @param null|string  $fileName  output fileName
     * @return string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function sie5XmlStringTo4I( string $source, $fileName = null) : string
    {
        return
            Sie4Iwriter::factory()
                       ->write4I(
                           Sie5Parser::factory()->parseXmlFromString( $source ),
                           $fileName
                       );
    }
}
