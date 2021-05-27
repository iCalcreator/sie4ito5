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
namespace Kigkonsult\Sie4Ito5;

use DirectoryIterator;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Sie4Ito5\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Dto\SieEntry;
use Kigkonsult\Sie5Sdk\XMLParse\Sie5Parser;
use Kigkonsult\Sie5Sdk\XMLWrite\Sie5Writer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

if( ! class_exists( 'BaseTest' )) {
    include __DIR__ . '/../autoload.php';
}
include 'PHPDiff/PHPDiff.php';

/**
 * Class TestFiles
 */
class TestFiles extends TestCase
{
    private static $FMT0 = '%s START (#%s) %s on \'%s\'%s';

    /**
     * testSie4IFile dataProvider
     * @return array
     */
    public function sie4IFileProvider() : array
    {

        $testPath = 'test/Sie4I_files';
        $dir      = new DirectoryIterator( $testPath );
        $dataArr  = [];

        $case     = 100;
        foreach( $dir as $file ) {
            if( ! $file->isFile() ) {
                continue;
            }
            $dataArr[] =
                [
                    $case,
                    $file->getPathname(),
                ];
            $case += 100;
        }

        return $dataArr;
    }

    /**
     * Reading Sie4I file, parse, write SieEntry xml and convert back (twice) and compare
     *
     * Expects error due to attributes with default value
     *
     * @test
     * @dataProvider sie4IFileProvider
     * @param int $case
     * @param string $fileName
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws Exception
     */
    public function testSie4IFile( int $case, string $fileName )
    {
        static $FMT1 = '%s (#%s) not valid%s%s%s';

        echo sprintf( self::$FMT0, PHP_EOL, __FUNCTION__, $case, basename( $fileName ), PHP_EOL );

        $sie4Istring1Utf8 = file_get_contents( $fileName );
        $sie4Istring1     = StringUtil::utf8toCP437( $sie4Istring1Utf8 );
        // convert file content to CP437, save into tempFile
        $tempFile1 = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_1_');
        file_put_contents(
            $tempFile1,
            $sie4Istring1
        );

        // parse Sie4I file (!!) into SieEntry
        list( $sieEntry1, $isKsummaSet ) = self::Sie4ItoSieEntry( $tempFile1 );
        unlink( $tempFile1 );
        $expected = [];
        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry1->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 1, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );

        // parse #2 Sie4I (string) into SieEntry2 and compare 1/2
        $this->assertEquals(
            $sieEntry1->toString(),
            Sie4I::factory()->parse4I( $sie4Istring1 )->toString(),
            'sieEntry1 and sieEntry2 has NOT the same load'
        );

        // write SieEntry1 to XML
        $xml1 = Sie5Writer::factory()->write( $sieEntry1 );

        // parse xml into SieEntry2
        $sieEntry2 = self::XMLtoSieEntry( $xml1 );
        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry2->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 2, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );

        // write Sie4I from SieEntry2 to string
        $sie4Istring2 = self::sieEntryToSie4I( $sieEntry2, null, $isKsummaSet );

        // parse Sie4I string (!!) into SieEntry
        list( $sieEntry3, $isKsummsSet3 ) = self::Sie4ItoSieEntry( $sie4Istring2 );
        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry3->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 3, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );

        // $sieEntry1 and $sieEntry3 has the same content
        $this->assertEquals(
            $sieEntry1->toString(),
            $sieEntry3->toString(),
            'sieEntry1 and sieEntry3 has NOT the same load'
        );

        // echo 'passed \'var_export( $sieEntryX, true )\', OK'; // test ###

        // convert SieEntry (again) to Sie4I string and file
        $tempFile3 = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_2_');
        // $sie4Istring3 = self::sieEntryToSie4I( $sieEntry3, $tempFile2, $isKsummsSet3 );
        $sie4Iwriter  = Sie4Iwriter::factory();
        $sie4Istring3 = $sie4Iwriter->write4I( $sieEntry3, $tempFile3, $isKsummaSet );
        if( $isKsummaSet ) {
            echo 'sie4Istring3 (ksumma base in utf8) :' . PHP_EOL .
                StringUtil::cp437toUtf8(
                    chunk_split( $sie4Iwriter->getKsummaBase(), 76, PHP_EOL )
                )
                . PHP_EOL;
        }
       $this->assertStringEqualsFile(
            $tempFile3,
            $sie4Istring3,
            'tempFile3 and sie4Istring3 has NOT the same load'
        );
        unlink( $tempFile3 );

        // convert to utf8 for opt display
        $sie4Istring2Utf8 = StringUtil::cp437toUtf8( $sie4Istring2 );
        $sie4Istring3Utf8 = StringUtil::cp437toUtf8( $sie4Istring3 );

        /*
        // view output files
        echo PHP_EOL . 'sie4Istring1 :' . PHP_EOL . $sie4Istring1 . PHP_EOL;
        echo PHP_EOL . 'sie4Istring2 :' . PHP_EOL . $sie4Istring2 . PHP_EOL;
        */
        echo PHP_EOL . 'sie4Istring3 (i utf8) :' . PHP_EOL . $sie4Istring3Utf8 . PHP_EOL;

        // file strings diff but in PHP from http://www.holomind.de/phpnet/diff.php
        $diff = PHPDiff( $sie4Istring1Utf8, $sie4Istring3Utf8 );
        $this->assertEmpty(
            $diff,
            'diff 1/3 (i utf8) : ' . PHP_EOL . $diff
        );
    }

    /**
     * Return array ( SieEntry, isKsummaSet ) parsed from Sie4I string/file
     *
     * @param string $sie4Iinput  string or file
     * @return array [ SieEntry, bool ]
     */
    private static function Sie4ItoSieEntry( $sie4Iinput ) : array
    {
        $sie4Iparser = Sie4Iparser::factory( $sie4Iinput );
        return [
            $sie4Iparser->parse4I(),
            $sie4Iparser->isKsummaSet()
        ];
    }

    /**
     * Return Sie4I string from SieEntry, opt also write to file
     *
     * @param SieEntry     $sieEntry
     * @param null|string  $fileName
     * @param null|bool    $ksummaSet
     * @return string
     */
    private static function sieEntryToSie4I( SieEntry $sieEntry, $fileName = null, $ksummaSet = false ) : string
    {
        return Sie4I::factory()->write4I( $sieEntry, $fileName, $ksummaSet );
    }

    /**
     * Return SieEntry from SieEntry XML string/file
     *
     * @param null|string $sieEntryString
     * @param null|string $sieEntryFile
     * @return SieEntry
     */
    private static function XMLtoSieEntry( $sieEntryString = null, $sieEntryFile = null ) : SieEntry
    {
        $parser = Sie5Parser::factory();
        try {
            if( null !== $sieEntryString ) {
                $sieEntry = $parser->parseXmlFromString( $sieEntryString, false );
            }
            else {
                $sieEntry = $parser->parseXmlFromFile( $sieEntryFile, false );
            }
        }
        catch( Exception $e ) {
            echo $e->getMessage() . PHP_EOL;
            throw $e;
        }
        return $sieEntry;
    }

    /**
     * testSie5IFile dataProvider
     * @return array
     */
    public function sie5FileProvider() : array
    {

        $testPath = 'test/Sie5_files';
        $dir      = new DirectoryIterator( $testPath );
        $dataArr  = [];

        $case     = 100;
        foreach( $dir as $file ) {
            if( ! $file->isFile() ) {
                continue;
            }
            $dataArr[] =
                [
                    $case,
                    $file->getPathname(),
                ];
            $case += 100;
        }

        return $dataArr;
    }

    /**
     * Reading SieEntry file from Sie5_files, parse and write Sie4I, convert back and compare
     *
     * NO ksumma test here
     *
     * @test
     * @dataProvider sie5FileProvider
     * @param int $case
     * @param string $fileName
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws Exception
     */
    public function testSie5File( int $case, string $fileName )
    {
        static $FMT1 = '%s #%d not valid%s%s%s';

        echo sprintf( self::$FMT0, PHP_EOL, __FUNCTION__, $case, basename( $fileName ), PHP_EOL );

        $sie4I = Sie4I::factory();

        // convert Sie5 (SieEntry) XML file to Sie4I string
        $sie4IString1 = $sie4I->sie5XmlFileTo4I( $fileName );

        // convert Sie5 (SieEntry) XML string to Sie4I string, compare string1/2
        $sie4IString2 = $sie4I->sie5XmlStringTo4I( file_get_contents( $fileName ));
        $this->assertEquals(
            $sie4IString1,
            $sie4IString2
        );

        // convert Sie4I string source to Sie5 (SieEntry) XML string
        $sie5XMLstring2 = $sie4I->sie4Ito5Xml( $sie4IString1 );

        // compare SieEntry xml's, will turn up in some inconsistency
        $this->assertXmlStringEqualsXmlFile(
            $fileName,
            $sie5XMLstring2,
            'Error comparing XML, Sie4I : '
        );
    }

}
