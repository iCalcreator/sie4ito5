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

use function usort;

/**
 * Class Sie5EntryLoader
 *
 * Assemble Sie4I data
 *   using
 *     Sie5EntrySie4IDto\IdDto
 *     Sie5EntrySie4IDto\DimDto
 *     Sie5EntrySie4IDto\DimObjektDto
 *     Sie5EntrySie4IDto\AccountDto
 *     Sie5EntrySie4IDto\VerDto (transDtos/DimObjektDto)
 */
class Sie4IDto
{
    /**
     * @var IdDto
     */
    private $idDto = null;

    /**
     * @var AccountDto[]
     */
    private $accountDtos = [];

    /**
     * @var DimDto[]
     */
    private $dimDtos = [];

    /**
     * @var DimObjektDto[]
     */
    private $dimObjektDtos = [];

    /**
     * @var VerDto[]
     */
    private $verDtos = [];

    /**
     * Class factory method, set idDto
     *
     * @param IdDto $idDto
     * @return static
     */
    public static function factory( IdDto $idDto ) : self
    {
        $instance = new self();
        $instance->setIdDto( $idDto );
        return $instance;
    }

    /**
     * Return IdDto
     *
     * @return null|IdDto
     */
    public function getIdDto()
    {
        return $this->idDto;
    }

    /**
     * Return bool true if IdDto is set
     *
     * @return bool
     */
    public function isIdDtoSet() : bool
    {
        return ( null !== $this->idDto );
    }

    /**
     * Set IdDto
     *
     * @param IdDto $idDto
     * @return static
     */
    public function setIdDto( IdDto $idDto ) : self
    {
        $this->idDto = $idDto;
        return $this;
    }

    /**
     * Return int count AccountDtos
     *
     * @return int
     */
    public function countAccountDtos() : int
    {
        return count( $this->accountDtos );
    }

    /**
     * Return array AccountDto
     *
     * @return AccountDto[]
     */
    public function getAccountDtos() : array
    {
        static $SORTER = [ AccountDto::class, 'sorter' ];
        usort( $this->accountDtos, $SORTER );
        return $this->accountDtos;
    }

    /**
     * Add single AccountDto using kontoNr/namn/typ, enhet opt
     *
     * @param int|string $kontoNr
     * @param string $kontoNamn
     * @param string $kontoTyp
     * @param null|string $enhet
     * @return static
     */
    public function addAccount(
        $kontoNr,
        string $kontoNamn,
        string $kontoTyp,
        $enhet = null
    ) : self
    {
        return $this->addAccountDto(
            AccountDto::factory(
                $kontoNr,
                $kontoNamn,
                $kontoTyp,
                $enhet
           )
        );
    }

    /**
     * Add single AccountDto
     *
     * @param AccountDto $accountData
     * @return static
     */
    public function addAccountDto( AccountDto $accountData ) : self
    {
        $this->accountDtos[] = $accountData;
        return $this;
    }

    /**
     * Set array AccountDto[]
     *
     * @param AccountDto[] $accountDtos
     * @return static
     */
    public function setAccountDtos( array $accountDtos ) : self
    {
        foreach( $accountDtos as $accountDto ) {
            $this->addAccountDto( $accountDto );
        }
        return $this;
    }

    /**
     * Return int count DimDtos
     *
     * @return int
     */
    public function countDimDtos() : int
    {
        return count( $this->dimDtos );
    }

    /**
     * Return array DimDto
     *
     * @return DimDto[]
     */
    public function getDimDtos() : array
    {
        static $SORTER = [ DimDto::class, 'sorter' ];
        usort( $this->dimDtos, $SORTER );
        return $this->dimDtos;
    }

    /**
     * Add single DimObjektDto using dimensionsNr and dimensionsNamn
     *
     * @param int|string $dimensionsNr
     * @param string $dimensionsNamn
     * @return static
     */
    public function addDim( $dimensionsNr, string $dimensionsNamn ) : self
    {
        return $this->addDimDto(
            DimDto::factoryDim(
                $dimensionsNr,
                $dimensionsNamn
            )
        );
    }

    /**
     * Add single DimDto
     *
     * @param DimDto $dimData
     * @return static
     */
    public function addDimDto( DimDto $dimData ) : self
    {
        $this->dimDtos[] = $dimData;
        return $this;
    }

    /**
     * Set array DimDto
     *
     * @param DimDto[] $dimDtos
     * @return static
     */
    public function setDimDtos( array $dimDtos ) : self
    {
        foreach( $dimDtos as $dim ) {
            $this->addDimDto( $dim );
        }
        return $this;
    }

    /**
     * Return int count DimObjektDtos
     *
     * @return int
     */
    public function countDimObjektDtos() : int
    {
        return count( $this->dimObjektDtos );
    }

    /**
     * Return array DimObjekttDto
     *
     * @return DimObjektDto[]
     */
    public function getDimObjektDtos() : array
    {
        static $SORTER = [ DimObjektDto::class, 'sorter' ];
        usort( $this->dimObjektDtos, $SORTER );
        return $this->dimObjektDtos;
    }

    /**
     * Add single DimObjektDto using dimensionsNr, objektNr and $objektNamn
     *
     * @param int|string $dimensionsNr
     * @param int|string $objektNr
     * @param string $objektNamn
     * @return static
     */
    public function addDimObjekt( $dimensionsNr, $objektNr, string $objektNamn ) : self
    {
        return $this->addDimObjektDto(
            DimObjektDto::factoryDimObject(
                $dimensionsNr,
                $objektNr,
                $objektNamn
            )
        );
    }

    /**
     * Add single DimObjektDto
     *
     * @param DimObjektDto $dimObjektDto
     * @return static
     */
    public function addDimObjektDto( DimObjektDto $dimObjektDto ) : self
    {
        $this->dimObjektDtos[] = $dimObjektDto;
        return $this;
    }

    /**
     * Set array DimObjektDto[]
     *
     * @param DimObjektDto[] $dimObjektDtos
     * @return static
     */
    public function setDimObjektDtos( array $dimObjektDtos ) : self
    {
        foreach( $dimObjektDtos as $dimObjektDto ) {
            $this->addDimObjektDto( $dimObjektDto );
        }
        return $this;
    }

    /**
     * Return int count verDtos
     *
     * @return int
     */
    public function countVerDtos() : int
    {
        return count( $this->verDtos );
    }

    /**
     * Return int total count of transDtos in VerDtos
     *
     * @return int
     */
    public function countVerTransDtos() : int
    {
        $count = 0;
        foreach( $this->verDtos as $verDto ) {
            $count += $verDto->countTransDtos();
        }
        return $count;
    }

    /**
     * Return sorted array VerDto
     *
     * @return VerDto[]
     */
    public function getVerDtos() : array
    {
        static $SORTER = [ VerDto::class, 'sorter' ];
        usort( $this->verDtos, $SORTER );
        return $this->verDtos;
    }

    /**
     * Add single VerDto
     *
     * @param VerDto $verDto
     *
     * @return static
     */
    public function addVerDto( VerDto $verDto ) : self
    {
        $this->verDtos[] = $verDto;
        return $this;
    }

    /**
     * Set array VerDto[]
     *
     * @param VerDto[] $verDtos
     * @return static
     */
    public function setVerDtos( array $verDtos ) : self
    {
        foreach( $verDtos as $verDto ) {
            $this->addVerDto( $verDto );
        }
        return $this;
    }
}