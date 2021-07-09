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
namespace Kigkonsult\Sie4Ito5\Dto;

use Kigkonsult\Sie4Ito5\Sie4IValidator;

class DimDto
{
    /**
     * @var int
     */
    protected $dimensionsNr = null;

    /**
     * @var null|string
     */
    protected $dimensionsNamn = null;

    /**
     * Sort DimDto[] on dimensionsnr
     *
     * @param DimDto $a
     * @param DimDto $b
     * @return int
     */
    public static function sorter( $a, $b ) : int
    {
        return strcmp((string) $a->getDimensionsNr(), (string) $b->getDimensionsNr());
    }

    /**
     * Class factory method, set dimensionsNr and, dimensionsNamn
     *
     * @param int|string $dimensionsNr
     * @param string $dimensionsNamn
     * @return static
     */
    public static function factoryDim( $dimensionsNr, string $dimensionsNamn ) : self
    {
        $instance = new self();
        $instance->setDimensionsNr( $dimensionsNr );
        if( ! empty( $dimensionsNamn )) {
            $instance->setDimensionsNamn( $dimensionsNamn );
        }
        return $instance;
    }

    /**
     * Return dimensionsNr
     *
     * @return null|int
     */
    public function getDimensionsNr()
    {
        return $this->dimensionsNr;
    }

    /**
     * Return bool true if dimensionsNr is set
     *
     * @return bool
     */
    public function isDimensionsNrSet() : bool
    {
        return ( null !== $this->dimensionsNr );
    }

    /**
     * Set dimensionsNr
     *
     * @param int|string $dimensionsNr
     * @return static
     */
    public function setDimensionsNr( $dimensionsNr ) : self
    {
        Sie4IValidator::assertIntegerish( $dimensionsNr );
        $this->dimensionsNr = (int) $dimensionsNr;
        return $this;
    }

    /**
     * Return dimensionsNamn
     *
     * @return null|string
     */
    public function getDimensionsNamn()
    {
        return $this->dimensionsNamn;
    }

    /**
     * Return bool true if dimensionsNamn is set
     *
     * @return bool
     */
    public function isDimensionsNamnSet() : bool
    {
        return ( null !== $this->dimensionsNamn );
    }

    /**
     * Set dimensionsNamn
     *
     * @param string $dimensionsNamn
     * @return static
     */
    public function setDimensionsNamn( string $dimensionsNamn ) : self
    {
        $this->dimensionsNamn = $dimensionsNamn;
        return $this;
    }
}