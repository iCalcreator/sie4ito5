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
namespace Kigkonsult\Sie4Ito5;

use Kigkonsult\Sie5Sdk\Dto\SieEntry;

/**
 * Class Sie4I
 *
 * @package Kigkonsult\Sie4Ito5
 *
 * Parse comments :
 *
 *   Note för #GEN
 *     if 'sign' is missing, '#PROGRAM programnamn' is used
 *
 *   #SRU and #UNDERDIM are skipped
 *
 *   Note för #VER
 *     if 'regdatum' is missing, 'verdatum' is used
 *     if 'sign' is missing, '#GEN sign' is used
 *   When parsing Sie4I and #VER::serie(JournalTypeEntry::id) and/or
 *     #VER::vernr(JournalEntryTypeEntry::id) is empty
 *     #VER may come in any order within verdatum
 *
 *   Note för #TRANS
 *     only support for 'dimensionsnummer och objektnummer' in the 'objektlista'
 *     i.e. no support for 'hierarkiska dimensioner'
 *   #RTRANS and #BTRANS are skipped
 *
 *   #KSUMMA ignored at parse and, for now, write
 *
 * Write comments
 *   Sie4I file creation date has format 'YYYYmmdd', SieEntry 'YYYY-MM-DDThh:mm:ssZ'
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
     */
    public function write4I( SieEntry $sieEntry, $fileName = null, $writeKsumma = false ) : string
    {
        return Sie4Iwriter::factory()->write4I( $sieEntry, $fileName, $writeKsumma );
    }
}
