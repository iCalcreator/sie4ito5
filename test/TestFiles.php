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

        $case     = 1;
        foreach( $dir as $file ) {
            if( ! $file->isFile() ) {
                continue;
            }
            $dataArr[] =
                [
                    $case++,
                    $file->getPathname(),
                ];
        }

        return $dataArr;
    }

    /**
     * Reading Sie4I file, parse, write SieEntry xml and convert back and compare
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
        static $FMT1 = '%s (#%s) %s not valid%s%s%s';

        echo sprintf( self::$FMT0, PHP_EOL, __FUNCTION__, $case, basename( $fileName ), PHP_EOL );

        // convert file content to CP437, save into tempFile
        $tempFile1 = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_1_');
        file_put_contents(
            $tempFile1,
            StringUtil::utf8toCP437(
                file_get_contents( $fileName )
            )
        );
        $sie4Istring1 = file_get_contents( $tempFile1 );
            // parse Sie4I into SieEntry
        $sieEntry1 = Sie4I::factory()->parse4I( $tempFile1 );
        unlink( $tempFile1 );
        $expected = [];
        $this->assertTrue(         // ---- validate
            $sieEntry1->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $case, $fileName, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );
        // write SieEntry into XML
        // echo Sie5Writer::factory()->write( $sieEntry ) . PHP_EOL;

        // write Sie4I from SieEntry to (temp)file and string
        $tempFile2    = tempnam( sys_get_temp_dir(), __FUNCTION__ . '_2_');
        $sie4Istring2 = Sie4I::factory()->write4I( $sieEntry1, $tempFile2 );
        $sie4Istring3 = implode( '', file( $tempFile2 ));
        $sieEntry2    = Sie4I::factory()->parse4I( $tempFile2 );
        unlink( $tempFile2 );

        // $sieEntry1 and $sieEntry2 has the same load? but files have rows in different order
        $this->assertEquals(
            /*
            $sieEntry1->toString(), // väntar på fix i Sie5Sdk
            $sieEntry2->toString()
            */
            var_export( $sieEntry1, true ),
            var_export( $sieEntry2, true ),
            'sieEntry1 and sieEntry2 has NOT the same load'
        );

        $sie4Istring1 = StringUtil::cp437toUtf8( $sie4Istring1 );
        $sie4Istring2 = StringUtil::cp437toUtf8( $sie4Istring2 );
        $sie4Istring3 = StringUtil::cp437toUtf8( $sie4Istring3 );

        /*
        // view output files
        echo PHP_EOL . 'sie4Istring1 :' . PHP_EOL . $sie4Istring1 . PHP_EOL;
        echo PHP_EOL . 'sie4Istring2 :' . PHP_EOL . $sie4Istring2 . PHP_EOL;
        */
        echo PHP_EOL . 'sie4Istring3 :' . PHP_EOL . $sie4Istring3 . PHP_EOL;

        /* file 1-2  diff but in PHP from http://www.holomind.de/phpnet/diff.php
        $diff1_2 = PHPDiff( $sie4Istring1, $sie4Istring2 );
        if( ! empty( $diff1_2 )) {
            echo 'diff 1/2 : ' . PHP_EOL . PHPDiff( $sie4Istring1, $sie4Istring2 ) . PHP_EOL . PHP_EOL;
        }
        */
        /*
        // compare Sie4I 1-2 files, will turn up in some inconsistency
        $this->assertEquals(
            $sie4Istring1,
            $sie4Istring2
        );
        */

        // file 2-3  diff but in PHP from http://www.holomind.de/phpnet/diff.php
        $diff2_3 = PHPDiff( $sie4Istring2, $sie4Istring3 );
        if( ! empty( $diff2_3 )) {
            echo 'diff 2/3 : ' . PHP_EOL . PHPDiff( $sie4Istring2, $sie4Istring3 ) . PHP_EOL . PHP_EOL;
        }
        $this->assertEquals(
            $sie4Istring2,
            $sie4Istring3
        );

        // echo PHP_EOL .$sie4Istring1 . PHP_EOL;
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

        $case     = 1;
        foreach( $dir as $file ) {
            if( ! $file->isFile() ) {
                continue;
            }
            $dataArr[] =
                [
                    $case++,
                    $file->getPathname(),
                ];
        }

        return $dataArr;
    }

    /**
     * Reading SieEntry file from Sie5_files, parse and write Sie4I, convert back and compare
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
        static $FMT1 = '%s \'%s\' not valid%s%s%s';

        // parse SieEntry XML using Sie5Sdk
        echo sprintf( self::$FMT0, PHP_EOL, __FUNCTION__, $case, basename( $fileName ), PHP_EOL );
        $sieEntry1 = Sie5Parser::factory()->parseXmlFromFile( $fileName );

        $expected = [];
        $this->assertTrue(         // ---- validate SieEntry
            $sieEntry1->isValid( $expected ),
            sprintf( $FMT1, __FUNCTION__, $fileName, PHP_EOL, var_export( $expected, true ), PHP_EOL )
        );

        // convert SieEntry to Sie4I string
        $sie4I        = Sie4I::factory();
        $sie4Istring1 = $sie4I->write4I( $sieEntry1 );
        // display (file) string
//      echo $sie4Istring1 . PHP_EOL; // test ###

        // convert Sie4I string back to SieEntry
        try {
            $sieEntry2 = $sie4I->parse4I( $sie4Istring1 );
        }
        catch( Exception $e ) {
            echo 'Error Sie5-parse of Sie4I, ' . $e->getMessage() . PHP_EOL . $sie4Istring1 . PHP_EOL;
            return;
        }

        // compare SieEntry xml's, will turn up in some inconsistency
        $this->assertEquals(
            Sie5Writer::factory()->write( $sieEntry1 ),
            Sie5Writer::factory()->write( $sieEntry2 ),
            'Error comparing XML, Sie4I : '
        );

    }
}
