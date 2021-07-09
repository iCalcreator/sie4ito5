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

use InvalidArgumentException;
use Kigkonsult\Sie5Sdk\Dto\AccountTypeEntry;

use function array_flip;
use function strtoupper;

/**
 * Class AccountDto
 *
 * Kontonr/name/typ required
 */
class AccountDto
{
    /**
     * Sie4I konto types
     */
    const T = 'T';
    const S = 'S';
    const K = 'K';
    const I = 'I';

    /**
     * Return Sie5 kontoTyp for Sie4 kontoTyp and v.v., false if not found
     *
     * @param string     $type
     * @param null|bool  $forSie4I
     * @return false|string
     */
    public static function getKontoType( string $type, $forSie4I = false )
    {
        static $KONTOTYPER = [
            AccountDto::T => AccountTypeEntry::ASSET,     // Tillgång
            AccountDto::S => AccountTypeEntry::LIABILITY, // Skuld
            AccountDto::K => AccountTypeEntry::COST,      // kostnad
            AccountDto::I => AccountTypeEntry::INCOME,    // Intäkt
        ];
        static $KONTOTYPER2 = [];
        if( $forSie4I ) {
            if( empty( $KONTOTYPER2 )) {
                $KONTOTYPER2 = array_flip( $KONTOTYPER );
            }
            return $KONTOTYPER2[$type] ?? false;
        }
        else {
            return $KONTOTYPER[$type] ?? false;
        }
    }

    use KontoNrTrait;

    /**
     * @var string
     */
    private $kontoNamn = null;

    /**
     * @var string
     */
    private $kontoTyp = null;

    /**
     * @var null|string
     */
    private $enhet = null;

    /**
     * Sort AccountDto[] on kontonr
     *
     * @param AccountDto $a
     * @param AccountDto $b
     * @return int
     */
    public static function sorter( AccountDto $a, AccountDto $b ) : int
    {
        return strcmp((string) $a->getKontoNr(), (string) $b->getKontoNr());
    }

    /**
     * Class factory method, kontoNr/Namn/Typ, enhet opt
     *
     * @param int|string $kontoNr
     * @param string $kontoNamn
     * @param string $kontoTyp
     * @param null|string $enhet
     * @return static
     */
    public static function factory(
        $kontoNr,
        string $kontoNamn,
        string $kontoTyp,
        $enhet = null
    ) : self
    {
        $instance = new self();
        $instance->setKontoNr( $kontoNr );
        $instance->setKontoNamn( $kontoNamn );
        $instance->setKontoTyp( $kontoTyp );
        if( ! empty( $enhet )) {
            $instance->setEnhet( $enhet );
        }
        return $instance;

    }

    /**
     * Return kontoNamn
     *
     * @return null|string
     */
    public function getKontoNamn()
    {
        return $this->kontoNamn;
    }

    /**
     * Return bool true if kontoNamn is set
     *
     * @return bool
     */
    public function isKontonamnSet() : bool
    {
        return ( null !== $this->kontoNamn );
    }

    /**
     * Set kontoNamn
     *
     * @param string $kontoNamn
     * @return static
     */
    public function setKontoNamn( string $kontoNamn ) : self
    {
        $this->kontoNamn = $kontoNamn;
        return $this;
    }

    /**
     * Return kontoTyp
     *
     * @return null|string
     */
    public function getKontoTyp()
    {
        return $this->kontoTyp;
    }

    /**
     * Return bool true if kontoType is set
     *
     * @return bool
     */
    public function isKontotypSet() : bool
    {
        return ( null !== $this->kontoTyp );
    }

    /**
     * Set kontoTyp
     *
     * @param string $kontoTyp
     * @return static
     * @throws InvalidArgumentException
     */
    public function setKontoTyp( string $kontoTyp ) : self
    {
        static $FMT = 'Ogiltig kontoTyp : ';
        $kontoTyp = strtoupper( $kontoTyp );
        if( false === self::getKontoType( $kontoTyp )) {
            throw new InvalidArgumentException( $FMT . $kontoTyp );
        }
        $this->kontoTyp = $kontoTyp;
        return $this;
    }

    /**
     * Return enhet
     *
     * @return null|string
     */
    public function getEnhet()
    {
        return $this->enhet;
    }

    /**
     * Return bool true if enhet is set
     *
     * @return bool
     */
    public function isEnhetSet() : bool
    {
        return ( null !== $this->enhet );
    }

    /**
     * Set enhet
     *
     * @param string $enhet
     * @return static
     */
    public function setEnhet( string $enhet ) : AccountDto
    {
        $this->enhet = $enhet;
        return $this;
    }
}