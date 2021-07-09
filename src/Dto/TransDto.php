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

use DateTime;

use function count;

/**
 * Class TransDto
 *
 * kontonr and belopp required,
 *   in objektlista, pairs of dimension and objektnr required
 */
class TransDto
{
    use KontoNrTrait;

    /**
     * @var DimObjektDto[]
     */
    private $objektlista = [];

    /**
     * @var float
     */
    private $belopp = null;

    /**
     * @var null|DateTime
     */
    private $transdat = null;

    /**
     * @var null|string
     */
    private $transtext = null;

    /**
     * @var float
     */
    private $kvantitet = null;

    /**
     * @var null|string
     */
    private $sign = null;

    /**
     * Class factory method, kontoNr/belopp
     *
     * @param int|string $kontoNr
     * @param float  $belopp
     * @return static
     */
    public static function factory( $kontoNr, float $belopp ) : self
    {
        $instance = new self();
        $instance->setKontoNr( $kontoNr );
        $instance->setBelopp( $belopp );
        return $instance;
    }

    /**
     * Return int count DimObjektDtos in objektlista
     *
     * @return int
     */
    public function countObjektlista() : int
    {
        return count( $this->objektlista );
    }

    /**
     * Return objektlista, array DimObjektDto[]
     *
     * @return DimObjektDto[]
     */
    public function getObjektlista() : array
    {
        return $this->objektlista;
    }

    /**
     * Add objektlista element, dimId, objektId
     *
     * @param int $dimId
     * @param string $objektId
     * @return static
     */
    public function addDimIdObjektId( int $dimId, string $objektId ) : self
    {
        return $this->addObjektlista(
            DimObjektDto::factoryDimObject( $dimId, $objektId )
        );
    }

    /**
     * Add objektlista element, DimObjektDto
     *
     * @param DimObjektDto $dimObjektDto
     * @return static
     */
    public function addObjektlista( DimObjektDto $dimObjektDto ) : self
    {
        $this->objektlista[] = $dimObjektDto;
        return $this;
    }

    /**
     * Set objektlista, array DimObjektDto[]
     *
     * @param DimObjektDto[] $dimObjektDtos
     * @return static
     */
    public function setObjektlista( array $dimObjektDtos ) : self
    {
        foreach( $dimObjektDtos as $dimObjekt ) {
            $this->addObjektlista( $dimObjekt );
        }
        return $this;
    }

    /**
     * Return belopp
     *
     * @return null|float
     */
    public function getBelopp()
    {
        return $this->belopp;
    }

    /**
     * Return bool true if belopp is set
     *
     * @return bool
     */
    public function isBeloppSet() : bool
    {
        return ( null !== $this->belopp );
    }

    /**
     * Set belopp
     *
     * @param int|float|string $belopp
     * @return static
     */
    public function setBelopp( $belopp ) : self
    {
        $this->belopp = (float) $belopp;
        return $this;
    }

    /**
     * Return transdat
     *
     * @return null|DateTime
     */
    public function getTransdat()
    {
        return $this->transdat;
    }

    /**
     * Return bool true if transdat is set
     *
     * @return bool
     */
    public function isTransdatSet() : bool
    {
        return ( null !== $this->transdat );
    }

    /**
     * Set transdat
     *
     * @param DateTime $transdat
     * @return static
     */
    public function setTransdat( DateTime $transdat ) : self
    {
        $this->transdat = $transdat;
        return $this;
    }

    /**
     * Return transtext
     *
     * @return null|string
     */
    public function getTranstext()
    {
        return $this->transtext;
    }

    /**
     * Return bool true if transtext is set
     *
     * @return bool
     */
    public function isTranstextSet() : bool
    {
        return ( null !== $this->transtext );
    }

    /**
     * Set transtext
     *
     * @param string $transtext
     * @return static
     */
    public function setTranstext( string $transtext ) : self
    {
        $this->transtext = $transtext;
        return $this;
    }

    /**
     * Return kvantitet
     *
     * @return null|float
     */
    public function getKvantitet()
    {
        return $this->kvantitet;
    }

    /**
     * Return bool true if kvantitet is set
     *
     * @return bool
     */
    public function isKvantitetSet() : bool
    {
        return ( null !== $this->kvantitet );
    }

    /**
     * Set kvantitet
     *
     * @param int|float|string $kvantitet
     * @return static
     */
    public function setKvantitet( $kvantitet ) : self
    {
        $this->kvantitet = (float) $kvantitet;
        return $this;
    }

    /**
     * Return sign
     *
     * @return null|string
     */
    public function getSign()
    {
        return $this->sign;
    }

    /**
     * Return bool true if sign is set
     *
     * @return bool
     */
    public function isSignSet() : bool
    {
        return ( null !== $this->sign );
    }

    /**
     * Set sign
     *
     * @param string $sign
     * @return static
     */
    public function setSign( string $sign ) : self
    {
        $this->sign = $sign;
        return $this;
    }
}