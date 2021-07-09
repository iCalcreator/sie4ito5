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
use Kigkonsult\Sie4Ito5\Sie4IInterface;

/**
 * Class IdDto
 *
 * CompanyId and organization number required
 *
 * genDate  default 'now'
 * programnamn/version default set
 */
class IdDto implements Sie4IInterface
{
    /**
     * @var null|string
     */
    private $programnamn = null;

    /**
     * @var null|string
     */
    private $version = null;

    /**
     * @var DateTime
     */
    private $genDate = null;

    /**
     * @var null|string
     */
    private $genSign = null;

    /**
     * @var string
     */
    private $fnrId = null;

    /**
     * @var string
     */
    private $orgnr = null;

    /**
     * @var int  default 1
     */
    private $multiple = 1;

    /**
     * @var null|string
     */
    private $fnamn = null;

    /**
     * @var null|string
     */
    private $valutakod = null;

    /**
     * IdDto constructor
     */
    public function __construct()
    {
        $this->genDate     = new DateTime();
        $this->programnamn = self::PRODUCTNAME;
        $this->version     = self::PRODUCTVERSION;
    }


    /**
     * Class factory method, fnrId/orgnr
     *
     * @param string $fnrId
     * @param string $orgnr
     * @return static
     */
    public static function factory( string $fnrId, string $orgnr ) : self
    {
        $instance = new self();
        $instance->setFnrId( $fnrId );
        $instance->setOrgnr( $orgnr );
        return $instance;
    }

    /**
     * Return programnamn
     *
     * @return null|string
     */
    public function getProgramnamn()
    {
        return $this->programnamn;
    }

    /**
     * Set programnamn
     *
     * @param string $programnamn
     * @return static
     */
    public function setProgramnamn( string $programnamn ) : self
    {
        $this->programnamn = $programnamn;
        return $this;
    }

    /**
     * Return version
     *
     * @return null|string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set version
     *
     * @param string $version
     * @return static
     */
    public function setVersion( string $version ) : self
    {
        $this->version = $version;
        return $this;
    }

    /**
     * Return generation date
     *
     * @return null|DateTime
     */
    public function getGenDate()
    {
        return $this->genDate;
    }

    /**
     * Set generation date
     *
     * @param DateTime $genDate
     * @return static
     */
    public function setGenDate( DateTime $genDate ) : self
    {
        $this->genDate = $genDate;
        return $this;
    }

    /**
     * Return generation sign
     *
     * @return null|string
     */
    public function getGenSign()
    {
        return $this->genSign;
    }

    /**
     * Return bool true if generation sign is set
     *
     * @return bool
     */
    public function isGenSignSet() : bool
    {
        return ( null !== $this->genSign );
    }

    /**
     * Set generation sign
     *
     * @param string $genSign
     * @return static
     */
    public function setGenSign( string $genSign ) : self
    {
        $this->genSign = $genSign;
        return $this;
    }

    /**
     * Return fnrId
     *
     * @return null|string
     */
    public function getFnrId()
    {
        return $this->fnrId;
    }

    /**
     * Return bool true if fnr (company id) is set
     *
     * @return bool
     */
    public function isFnrIdSet() : bool
    {
        return ( null !== $this->fnrId );
    }

    /**
     * Set fnrId
     *
     * @param string $fnrId
     * @return static
     */
    public function setFnrId( string $fnrId ) : self
    {
        $this->fnrId = $fnrId;
        return $this;
    }

    /**
     * Return orgnr
     *
     * @return null|string
     */
    public function getOrgnr()
    {
        return $this->orgnr;
    }

    /**
     * Return bool true if orgnr is set
     *
     * @return bool
     */
    public function isOrgnrSet() : bool
    {
        return ( null !== $this->orgnr );
    }

    /**
     * Set orgnr
     *
     * @param string $orgnr
     * @return static
     */
    public function setOrgnr( string $orgnr ) : self
    {
        $this->orgnr = $orgnr;
        return $this;
    }

    /**
     * Return multiple (default 1)
     *
     * @return int
     */
    public function getMultiple() : int
    {
        return $this->multiple;
    }

    /**
     * Set multiple
     *
     * @param int $multiple
     * @return static
     */
    public function setMultiple( int $multiple ) : self
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * Return fnamn
     *
     * @return null|string
     */
    public function getFnamn()
    {
        return $this->fnamn;
    }

    /**
     * Return bool true if fnamn is set
     *
     * @return bool
     */
    public function isFnamnSet() : bool
    {
        return ( null !== $this->fnamn );
    }

    /**
     * Set fnamn
     *
     * @param string $fnamn
     * @return static
     */
    public function setFnamn( string $fnamn ) : self
    {
        $this->fnamn = $fnamn;
        return $this;
    }

    /**
     * Return valutakod
     *
     * @return null|string
     */
    public function getValutakod()
    {
        return $this->valutakod;
    }

    /**
     * Return bool true if valutakod is set
     *
     * @return bool
     */
    public function isValutakodSet() : bool
    {
        return ( null !== $this->valutakod );
    }

    /**
     * Set valutakod
     *
     * @param string $valutakod
     * @return static
     */
    public function setValutakod( string $valutakod ) : self
    {
        $this->valutakod = $valutakod;
        return $this;
    }
}