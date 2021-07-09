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

use Exception;
use InvalidArgumentException;
use Kigkonsult\Sie4Ito5\Api\Array2Sie4IDto;
use Kigkonsult\Sie4Ito5\Api\Json2Sie4IDto;
use Kigkonsult\Sie4Ito5\Api\Sie4IDto2Array;
use Kigkonsult\Sie4Ito5\Api\Sie4IDto2Json;
use Kigkonsult\Sie4Ito5\Dto\Sie4IDto;
use Kigkonsult\Sie5Sdk\Dto\SieEntry;
use Kigkonsult\Sie5Sdk\XMLParse\Sie5Parser;
use Kigkonsult\Sie5Sdk\XMLWrite\Sie5Writer;
use RuntimeException;

/**
 * Class Sie4I
 *
 * Parse Sie4I comments :
 *
 *   Note för #PROGRAM
 *     if missing, auto set
 *
 *   Note för #GEN
 *     if missing, 'datum' is set to 'now'
 *     if 'sign' is missing, '#PROGRAM programnamn' is used
 *
 *   #PROSA, #FTYP, #ADRESS, #RAR, #TAXAR, #KPTYP are skipped
 *
 *   #SRU and #UNDERDIM are skipped
 *
 *   Note för #VER
 *     if 'verdatum' is missing, 'now' is used
 *     if 'sign' is missing, '#GEN sign' is used (SieEntry)
 *
 *   Note för #TRANS
 *     only support for 'dimensionsnummer och objektnummer' in the 'objektlista'
 *     i.e. no support for 'hierarkiska dimensioner'
 *
 *   #RTRANS and #BTRANS are skipped
 *
 * Write Sie4I comments
 *   Sie4I file creation date has format 'YYYYmmdd', SieEntry 'YYYY-MM-DDThh:mm:ssZ'
 *
 *   The #KSUMMA checksum is experimental
 */
class Sie4I implements Sie4IInterface
{
    /**
     * Input to Sie4IDto
     */

    /**
     * Parse Sie4I file/string into Sie4IDto instance
     *
     * @param string $source
     * @return Sie4IDto
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sie4IFileString2Sie4IDto( string $source ) : Sie4IDto
    {
        return Sie4IParser::factory()->process( $source );
    }

    /**
     * Transform (HTTP, $_REQUEST) input array to Sie4IDto instance
     *
     * @param array $input
     * @return Sie4IDto
     */
    public static function array2Sie4IDto( array $input ) : Sie4IDto
    {
        return Array2Sie4IDto::process( $input );
    }

    /**
     * Transform input json string to Sie4IDto instance
     *
     * @param string $json
     * @return Sie4IDto
     * @throws InvalidArgumentException
     */
    public static function json2Sie4IDto( string $json ) : Sie4IDto
    {
        return Json2Sie4IDto::process( $json );
    }

    /**
     * Convert SieEntry (Sie5 instance) into Sie4IDto instance
     *
     * @param SieEntry $sieEntry
     * @return Sie4IDto
     */
    public static function sieEntry2Sie4IDto( SieEntry $sieEntry ) : Sie4IDto
    {
        return Sie4ILoader::factory( $sieEntry )->getSie4IDto();
    }

    /**
     * Transform SieEntry XML into Sie4IDto instance
     *
     * @param string $sieEntryXML
     * @return Sie4IDto
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sieEntryXML2Sie4IDto( string $sieEntryXML ) : Sie4IDto
    {
        $sieEntry = Sie5Parser::factory()->parseXmlFromString( $sieEntryXML );
        return Sie4ILoader::factory( $sieEntry )->getSie4IDto();
    }

    /**
     * Transform SieEntry XML file into Sie4IDto instance
     *
     * @param string $sieEntryFile
     * @return Sie4IDto
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sieEntryfile2Sie4IDto( string $sieEntryFile ) : Sie4IDto
    {
        $sieEntry = Sie5Parser::factory()->parseXmlFromFile( $sieEntryFile );
        return Sie4ILoader::factory( $sieEntry )->getSie4IDto();
    }

    /**
     * Process Sie4IDto output
     */

    /**
     * Write Sie4IDto instance to Sie4I string, opt with KSUMMA
     *
     * @param Sie4IDto    $sie4IDto
     * @param null|bool   $writeKsumma
     * @return string
     */
    public static function sie4IDto2String( Sie4IDto $sie4IDto, $writeKsumma = false ) : string
    {
        return Sie4IWriter::factory()->process( $sie4IDto, null, $writeKsumma );
    }

    /**
     * Write Sie4IDto instance to Sie4I file, opt with KSUMMA
     *
     * @param Sie4IDto    $sie4IDto
     * @param string      $outputfile
     * @param null|bool   $writeKsumma
     */
    public static function sie4IDto2File(
        Sie4IDto $sie4IDto,
        string $outputfile,
        $writeKsumma = false
    )
    {
        Sie4IWriter::factory()->process( $sie4IDto, $outputfile, $writeKsumma );
    }

    /**
     * Transform Sie4IDto instance to array
     *
     * @param Sie4IDto $sie4IDto
     * @return array
     */
    public static function sie4IDto2Array( Sie4IDto $sie4IDto ) : array
    {
        return Sie4IDto2Array::process( $sie4IDto );
    }

    /**
     * Transform Sie4IDto instance to json string
     *
     * @param Sie4IDto $sie4IDto
     * @return string
     */
    public static function sie4IDto2Json( Sie4IDto $sie4IDto ) : string
    {
        return Sie4IDto2Json::process( $sie4IDto );
    }

    /**
     * Convert Sie4IDto instance to SieEntry (Sie 5) instance
     *
     * @param Sie4IDto $sie4IDto
     * @return SieEntry
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sie4IDto2SieEntry( Sie4IDto $sie4IDto ) : SieEntry
    {
        return Sie5EntryLoader::factory( $sie4IDto )->getSieEntry();
    }

    /**
     * Transform Sie4IDto instance to SieEntry (Sie 5) XML string
     *
     * @param Sie4IDto $sie4IDto
     * @return string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public static function sie4IDto2SieEntryXml( Sie4IDto $sie4IDto ) : string
    {
        $sieEntry  = Sie5EntryLoader::factory( $sie4IDto )->getSieEntry();
        return Sie5Writer::factory()->write( $sieEntry );
    }

    /**
     * Deprecated methods
     */

    /**
     * Return instance
     *
     * @return static
     * @deprecated
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
     * @deprecated
     */
    public function parse4I( $source ) : SieEntry
    {
        $sie4IDto = Sie4IParser::factory( $source )->process();
        return Sie5EntryLoader::factory( $sie4IDto )->getSieEntry();
    }

    /**
     * Write Sie4I from Sie5 (SieEntry)
     *
     * @param SieEntry     $sieEntry
     * @param null|string  $fileName
     * @param null|bool    $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     * @deprecated
     */
    public function write4I( SieEntry $sieEntry, $fileName = null, $writeKsumma = false ) : string
    {
        return Sie4IWriter::factory()->process( $sieEntry, $fileName, $writeKsumma );
    }

    /**
     * Parse Sie4I string/file into Sie5 (SieEntry) XML string
     *
     * @param string|array $source       string: Sie4I content or fileName, array : Sie4I rows
     * @return string
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @deprecated
     */
    public function sie4Ito5Xml( $source ) : string
    {
        return
            Sie5Writer::factory()
                      ->write(
                          Sie5EntryLoader::factory(
                              Sie4Iparser::factory( $source )->process()
                          )->getSieEntry()
                      );
    }

    /**
     * Parse Sie5 (SieEntry) XML file info Sie4I string/file
     *
     * @param string $source          string: Sie4I content or fileName, array : Sie4I rows
     * @param null|string  $fileName  output fileName
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @deprecated
     */
    public function sie5XmlFileTo4I( string $source, $fileName = null) : string
    {
        return
            Sie4IWriter::factory()
                       ->process(
                           Sie4ILoader::factory(
                               Sie5Parser::factory()->parseXmlFromFile( $source )
                           ),
                           $fileName
                       );
    }

    /**
     * Parse Sie5 (SieEntry) XML string info Sie4I string/file
     *
     * @param string $source          string: Sie4I content or fileName, array : Sie4I rows
     * @param null|string  $fileName  output fileName
     * @return string
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @deprecated
     */
    public function sie5XmlStringTo4I( string $source, $fileName = null) : string
    {
        return
            Sie4IWriter::factory()
                       ->process(
                           Sie4ILoader::factory(
                               Sie5Parser::factory()->parseXmlFromString( $source )
                           ),
                           $fileName
                       );
    }
}
