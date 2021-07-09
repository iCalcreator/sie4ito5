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

use DirectoryIterator;
use Exception;
use InvalidArgumentException;
use Kigkonsult\Sie4Ito5\Util\StringUtil;
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
        // echo 'sie4Istring1' . PHP_EOL . StringUtil::cp437toUtf8( $sie4Istring1 ) . PHP_EOL;

        // parse Sie4I file (!!) into SieEntry
        $sie4IDto    = Sie4I::sie4IFileString2Sie4IDto( $tempFile1 );

        $isKsummaSet1 = ( 2 == substr_count( $sie4Istring1Utf8, Sie4I::KSUMMA ));

        unlink( $tempFile1 );
        // echo 'sie4IDto' . PHP_EOL . var_export( $sie4IDto, true ) . PHP_EOL; // test ###

        $sieEntry1 = Sie4I::sie4IDto2SieEntry( $sie4IDto );
        $expected = [];
        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry1->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 1, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );

        // write SieEntry1 to XML
        $sieEntry1String = Sie5Writer::factory()->write( $sieEntry1 );
        // parse xml back into SieEntry2
        $sieEntry2 = Sie5Parser::factory()->parseXmlFromString( $sieEntry1String );

        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry2->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 2, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );

        // write Sie4I from SieEntry2 to string

        $sie4Istring2 = Sie4I::sie4IDto2String(
            Sie4I::sieEntry2Sie4IDto( $sieEntry2 ),
            $isKsummaSet1
        );

        // echo 'sie4Istring2' . PHP_EOL . StringUtil::cp437toUtf8( $sie4Istring2 ) . PHP_EOL;

        // parse Sie4I string (!!) back into SieEntry
        $sieEntry3 = Sie4I::sie4IDto2SieEntry(
            Sie4I::sie4IFileString2Sie4IDto( $sie4Istring2 )
        );
        $sieEntry3String = Sie5Writer::factory()->write( $sieEntry3 );
        $isKsummaSet2 = ( 2 == substr_count( $sie4Istring2, Sie4I::KSUMMA ));

        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry3->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case + 3, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );
        $this->assertTrue(
            ( $isKsummaSet1 == $isKsummaSet2 ),
            'KSUMMA diff' .
            ', isKsummaSet1 : ' . var_export( $isKsummaSet1, true ) .
            ', isKsummaSet2 : ' . var_export( $isKsummaSet2, true )
        );
        // $sieEntry1 and $sieEntry3 has the same content
        $this->assertEquals(
            $sieEntry1String,
            $sieEntry3String,
            'sieEntry1 and sieEntry3 has NOT the same load'
        );

        // echo 'passed \'var_export( $sieEntryX, true )\', OK'; // test ###

        // convert SieEntry (again) to Sie4I string and file
        $tempFile3 = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_2_');
        // $sie4Istring3 = self::sieEntryToSie4I( $sieEntry3, $tempFile2, $isKsummsSet3 );
        $sie4IDto     = Sie4ILoader::factory( $sieEntry3 )->getSie4IDto();
        if( $isKsummaSet2 ) {
            $sie4Iwriter = Sie4IWriter::factory( $sie4IDto );
            $dummy       = $sie4Iwriter->write4I( null, null, true );
            $kSummaBase  = $sie4Iwriter->getKsummaBase();
            echo 'sie4Istring3 (ksumma base in utf8) :' . PHP_EOL .
                StringUtil::cp437toUtf8(
                    chunk_split( $kSummaBase, 76, PHP_EOL )
                )
                . PHP_EOL;
        }

        $sie4Istring3 = Sie4I::sie4IDto2String( $sie4IDto, $isKsummaSet2 );
        Sie4I::sie4IDto2File( $sie4IDto, $tempFile3, $isKsummaSet2 );

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
        // echo PHP_EOL . 'sie4Istring3 (i utf8) :' . PHP_EOL . $sie4Istring3Utf8 . PHP_EOL;

        // file strings diff but in PHP from http://www.holomind.de/phpnet/diff.php
        $diff = PHPDiff( $sie4Istring1Utf8, $sie4Istring3Utf8 );
        $this->assertEmpty(
            $diff,
            'diff 1/3 (i utf8) : ' . PHP_EOL . $diff
        );
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
        echo sprintf( self::$FMT0, PHP_EOL, __FUNCTION__, $case, basename( $fileName ), PHP_EOL );

        // convert Sie5 (SieEntry) XML file to Sie4I string
        $sie4IString1 = Sie4I::sie4IDto2String(
            Sie4I::sieEntryfile2Sie4IDto( $fileName )
        );
        $sie4IString2 = Sie4I::sie4IDto2String(
            Sie4I::sieEntryXML2Sie4IDto( file_get_contents( $fileName ))
        );

        $this->assertEquals(
            $sie4IString1,
            $sie4IString2,
            'Error comparing Sie4Is'
        );

        // echo 'sie4Istring1' . PHP_EOL . StringUtil::cp437toUtf8( $sie4IString1 ) . PHP_EOL;

        // convert Sie4I string to Sie5 (SieEntry) XML string

        $sie5XMLstring2 = Sie4I::sie4IDto2SieEntryXml(
            Sie4I::sie4IFileString2Sie4IDto( $sie4IString1 )
        );

        // compare SieEntry xml's, will turn up in some inconsistency
        $this->assertXmlStringEqualsXmlFile(
            $fileName,
            $sie5XMLstring2,
            'Error comparing XMLs'
        );
    }
}
