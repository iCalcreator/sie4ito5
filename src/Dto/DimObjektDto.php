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

/**
 * Class DimObjektDto
 *
 * As (lable) #OBJEKT in Sie4I, $dimensionsNr and $objektNr/namn required
 * In (lable) #TRANS in Sie4I and (array) objektlista, $dimensionsNr and $objektNr required
 */
class DimObjektDto extends DimDto
{
    /**
     * @var string
     */
    private $objektNr = null;

    /**
     * @var null|string
     */
    private $objektNamn = null;

    /**
     * Sort DimObjektDto[] on dimensionsnr
     *
     * @override
     * @param DimObjektDto $a
     * @param DimObjektDto $b
     * @return int
     */
    public static function sorter( $a, $b ) : int
    {
        if( 0 === ( $dimCmp = parent::sorter( $a, $b ))) {
            return strcmp( $a->getObjektNr(), $b->getObjektNr());
        }
        return $dimCmp;
    }

    /**
     * Class factory method, set dimensionsNr and objektNr, objektName opt
     *
     * @param int|string $dimensionsNr
     * @param int|string $objektNr
     * @param null|string $objektNamn
     * @return static
     */
    public static function factoryDimObject( $dimensionsNr, $objektNr, $objektNamn = null ) : self
    {
        $instance = new self();
        $instance->setDimensionsNr( $dimensionsNr );
        $instance->setObjektNr((string) $objektNr );
        if( ! empty( $objektNamn )) {
            $instance->setObjektNamn( $objektNamn );
        }
        return $instance;
    }

    /**
     * Return objektNr
     *
     * @return null|string
     */
    public function getObjektNr()
    {
        return $this->objektNr;
    }

    /**
     * Return bool true if objektNr is set
     *
     * @return bool
     */
    public function isObjektNrSet() : bool
    {
        return ( null !== $this->objektNr );
    }

    /**
     * Set objektNr
     *
     * @param string $objektNr
     * @return static
     */
    public function setObjektNr( string $objektNr ) : self
    {
        $this->objektNr = $objektNr;
        return $this;
    }

    /**
     * Return objektNamn
     *
     * @return null|string
     */
    public function getObjektNamn()
    {
        return $this->objektNamn;
    }

    /**
     * Return bool true if objektNamn is set
     *
     * @return bool
     */
    public function isObjektNamnSet() : bool
    {
        return ( null !== $this->objektNamn );
    }

    /**
     * Set objektNamn
     *
     * @param string $objektNamn
     * @return static
     */
    public function setObjektNamn( string $objektNamn ) : self
    {
        $this->objektNamn = $objektNamn;
        return $this;
    }
}