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

use DateTime;
use Exception;
use Kigkonsult\Sie4Ito5\Dto\AccountDto;
use Kigkonsult\Sie4Ito5\Dto\DimDto;
use Kigkonsult\Sie4Ito5\Dto\DimObjektDto;
use Kigkonsult\Sie4Ito5\Dto\IdDto;
use Kigkonsult\Sie4Ito5\Dto\Sie4IDto;
use Kigkonsult\Sie4Ito5\Dto\TransDto;
use Kigkonsult\Sie4Ito5\Dto\VerDto;
use Kigkonsult\Sie4Ito5\Util\StringUtil;
use PHPUnit\Framework\TestCase;

class TestDemo extends TestCase
{
    private static $FMT0 = '%s START (#%s) %s on \'%s\'%s';
    /**
     * @test
     */
    public function demoTest()
    {
        echo sprintf( PHP_EOL . 'START ' . __METHOD__ . PHP_EOL );

        $sie4IDto1 = Sie4IDto::factory(
            IdDto::factory( 'AC', '1234567890' )
                 ->setFnamn( 'Acme Corp' )
                 ->setValutakod( 'SEK' )
            )
                 ->setAccountDtos(
                     [
                         AccountDto::factory(
                             1510,
                             'Kundfordringar',
                             AccountDto::T
                         ),
                     ]
                 ) //setAccountDtos
                 ->addAccount(
                     4950,
                     'Förändring av lager av färdiga varor',
                     AccountDto::K,
                     'st'
                 )
                 ->setDimDtos(
                     [
                         DimDto::factoryDim( 1, 'Avdelning'),
                     ]
                 ) // end setDimDtos
                 ->addDim(6, 'Projekt' )
                 ->addDimObjekt(
                     6,
                     '47',
                     'Sie5-projektet'
                 )
                 ->setDimObjektDtos(
                     [
                         DimObjektDto::factoryDimObject(
                             1,
                             '0123',
                             'Serviceavdelningen'
                         )
                         ->setDimensionsNamn( 'Avdelning' ),
                     ]
                 ) // end setDimObjektDtos
                 ->setVerDtos(
                     [
                         VerDto::factory( 12345, 'ver text for 12345' )
                               ->setTransDtos(
                                   [
                                       TransDto::factory( 1910, -2000.00 )
                                               ->setTranstext( 'trans text 1' )
                                               ->setTransdat( new DateTime()),
                                       TransDto::factory( 2640, 400 ),
                                       TransDto::factory( 6250, 1600.00 )
                                               ->setObjektlista(
                                                   [
                                                       DimObjektDto::factoryDimObject(
                                                           6,
                                                           '47'
                                                       )
                                                   ]
                                               )
                                               ->setSign( 'verSign 12345-3' )
                                   ]
                               ),
                         VerDto::factory( 23456, 'ver text for 23456' )
                               ->setSerie( 'A' )
                               ->setRegdatum( new DateTime( '-1 day' ))
                               ->setSign( 'sign 23456' )
                               ->setTransDtos(
                                   [
                                       TransDto::factory( 7010, 56900.00 )
                                               ->setTransdat( new DateTime( '-1 day' ))
                                               ->setTranstext( 'ver 23456 trans text 1' )
                                               ->setKvantitet( 10 )
                                               ->setSign( 'transSign 23456-1' )
                                               ->setObjektlista(
                                                   [
                                                       DimObjektDto::factoryDimObject(
                                                           1,
                                                           '456'
                                                       ),
                                                       DimObjektDto::factoryDimObject(
                                                           6,
                                                           '47'
                                                       )
                                                   ]
                                               ),
                                   ]
                               )
                               ->addTransKontoNrBelopp( 1910, -56900.00 ),
                         VerDto::factory()
                               ->addTransKontoNrBelopp( 3020, -28000.00 )
                               ->addTransKontoNrBelopp( 2610, -7000.00 )
                               ->addTransKontoNrBelopp( 1510, 35000.00 ),
                     ]
                 ); // end setVerDtos

        $this->assertTrue(
            Sie4IValidator::assertSie4IDto( $sie4IDto1 ),
            var_export( $sie4IDto1, true )
        );
        $countVerDtos      = $sie4IDto1->countVerDtos();
        $this->assertNotEmpty(
            $countVerDtos,
            'Sie4IDto has no VerDtos'
        );
        $countVerTransDtos = $sie4IDto1->countVerTransDtos();
        $this->assertNotEmpty(
            $countVerTransDtos,
            'Sie4IDto has no TransDtos'
        );
        echo 'Sie4IDto has ' . $countVerDtos . ' VerDtos and ' . $countVerTransDtos . ' TransDtos' . PHP_EOL;

        $sie4Istring1 = StringUtil::cp437toUtf8(
            Sie4I::sie4IDto2String( $sie4IDto1 )
        );
        echo $sie4Istring1 . PHP_EOL;

        // echo var_export( $sie4IDto1, true );

        $sie4IArray = Sie4I::sie4IDto2Array( $sie4IDto1 );
        $sie4IDto2  = Sie4I::array2Sie4IDto( $sie4IArray );
        $this->apiTests( 100, $sie4Istring1, $sie4IDto2 );

        $jsonString = Sie4I::sie4IDto2Json( $sie4IDto2 );
        $sie4IDto3  = Sie4I::json2Sie4IDto( $jsonString );
        $this->apiTests( 200, $sie4Istring1, $sie4IDto3 );

        // echo $jsonString . PHP_EOL;
    }

    public function apiTests( int $case, string $sie4Istring1, Sie4IDto $sie4IDto )
    {
        static $ERR1 = ' Sie4IDto assert error, ';
        static $ERR2 = ' Sie4IDto string compare error';
        $outcome = true;
        try{
            Sie4IValidator::assertSie4IDto( $sie4IDto );
        }
        catch( Exception $e ) {
            $outcome = $e->getMessage();
        }
        $this->assertTrue(
            $outcome,
            $case . $ERR1 . $outcome
        );
        $sie4Istring2 = StringUtil::cp437toUtf8(
            Sie4I::sie4IDto2String( $sie4IDto )
        );
        $this->assertEquals(
            $sie4Istring1,
            $sie4Istring2,
            $case . $ERR2
        );
    }
}
