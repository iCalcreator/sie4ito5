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
use function strcmp;

/**
 * Class VerDto
 *
 * Verdatum and trans required
 * In each trans, kontonr and belopp required,
 *   in trans objektlista, if exists, pairs of dimension and objektnr required
 */
class VerDto
{
    /**
     * @var null|int|string
     */
    private $serie = null;

    /**
     * @var null|int
     */
    private $vernr = null;

    /**
     * @var DateTime
     */
    private $verdatum = null;

    /**
     * @var string
     */
    private $vertext = null;

    /**
     * @var DateTime
     */
    private $regdatum = null;

    /**
     * @var null|string
     */
    private $sign = null;

    /**
     * @var TransDto[]
     */
    private $transDtos = [];

    /**
     * Sort VerDto[] on serie and vernr
     *
     * @param VerDto $a
     * @param VerDto $b
     * @return int
     */
    public static function sorter( VerDto $a, VerDto $b ) : int
    {
        $aSerie = $a->getSerie();
        $bSerie = $b->getSerie();
        if( $aSerie < $bSerie ) {
            return -1;
        }
        if( $aSerie > $bSerie ) {
            return 1;
        }
        return strcmp((string) $a->getVernr(), (string) $b->getVernr());
    }

    /**
     * VerDto constructor
     */
    public function __construct()
    {
        $this->setVerdatum( new DateTime());
    }

    /**
     * Class factory method, opt vernr/text, verDatum default 'now'
     *
     * @param null|int      $vernr
     * @param null|string   $verText
     * @param null|DateTime $verDatum  default 'now'
     * @return static
     */
    public static function factory(
        $vernr = null,
        $verText = null,
        $verDatum = null
    ) : self
    {
        $instance = new self();
        if( ! empty( $vernr )) {
            $instance->setVernr( $vernr );
        }
        if( ! empty( $verText )) {
            $instance->setVertext( $verText );
        }
        if( ! empty( $verDatum )) {
            $instance->setVerdatum( $verDatum );
        }
        return $instance;
    }

    /**
     * Return serie
     *
     * @return null|int|string
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Return bool true if serie is set
     * 
     * @return bool
     */
    public function isSerieSet() : bool
    {
        return ( null !== $this->serie );
    }

    /**
     * Set serie
     *
     * @param int|string $serie
     * @return static
     */
    public function setSerie( $serie ) : self
    {
        $this->serie = (string) $serie;
        return $this;
    }

    /**
     * Return vernr
     *
     * @return null|int
     */
    public function getVernr()
    {
        return $this->vernr;
    }

    /**
     * Return bool true if vernr is set
     *
     * @return bool
     */
    public function isVernrSet() : bool
    {
        return ( null !== $this->vernr );
    }

    /**
     * Set vernr
     *
     * @param int $vernr
     * @return static
     */
    public function setVernr( int $vernr ) : self
    {
        $this->vernr = $vernr;
        return $this;
    }

    /**
     * Return verdatum
     *
     * @return DateTime
     */
    public function getVerdatum() : DateTime
    {
        return $this->verdatum;
    }

    /**
     * Return bool true if verdatum is set
     *
     * @return bool
     */
    public function isVerdatumSet() : bool
    {
        return ( null !== $this->verdatum );
    }

    /**
     * Set verdatum, DateTime
     *
     * @param DateTime $verdatum
     * @return VerDto
     */
    public function setVerdatum( DateTime $verdatum ) : VerDto
    {
        $this->verdatum = $verdatum;
        return $this;
    }

    /**
     * Return vertext
     *
     * @return null|string
     */
    public function getVertext()
    {
        return $this->vertext;
    }

    /**
     * Return bool true if vertext is set
     *
     * @return bool
     */
    public function isVertextSet() : bool
    {
        return ( null !== $this->vertext );
    }

    /**
     * Set vertext
     *
     * @param string $vertext
     * @return static
     */
    public function setVertext( string $vertext ) : self
    {
        $this->vertext = $vertext;
        return $this;
    }

    /**
     * Return regdatum
     *
     * @return null|DateTime
     */
    public function getRegdatum()
    {
        return $this->regdatum;
    }

    /**
     * Return bool true if regdatum is set
     *
     * @return bool
     */
    public function isRegdatumSet() : bool
    {
        return ( null !== $this->regdatum );
    }

    /**
     * Set regdatum, DateTime
     *
     * @param DateTime $regdatum
     * @return static
     */
    public function setRegdatum( DateTime $regdatum ) : self
    {
        $this->regdatum = $regdatum;
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

    /**
     * Return int count transDtos
     *
     * @return int
     */
    public function countTransDtos() : int
    {
        return count( $this->transDtos );
    }

    /**
     * Return transDtos, array TransDto[]
     *
     * @return TransDto[]
     */
    public function getTransDtos() : array
    {
        return $this->transDtos;
    }

    /**
     * Add single transDto, kontoNr, belopp
     *
     * @param int|string $kontoNr
     * @param float  $belopp
     * @return static
     */
    public function addTransKontoNrBelopp( $kontoNr, float $belopp ) : self
    {
        return $this->addTransDto(
            TransDto::factory( $kontoNr, $belopp )
        );
    }

    /**
     * Add single transDto
     *
     * @param TransDto $transDto
     * @return static
     */
    public function addTransDto( TransDto $transDto ) : self
    {
        $this->transDtos[] = $transDto;
        return $this;
    }

    /**
     * Set transDtos, array TransDto[]
     *
     * @param TransDto[] $transDtos
     * @return static
     */
    public function setTransDtos( array $transDtos ) : self
    {
        foreach( $transDtos as $transDto ) {
            $this->addTransDto( $transDto );
        }
        return $this;
    }
}