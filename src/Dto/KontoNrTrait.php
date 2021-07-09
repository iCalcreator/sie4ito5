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

trait KontoNrTrait
{
    /**
     * @var string
     */
    private $kontoNr = null;

    /**
     * Set kontoNr
     *
     * @return null|string
     */
    public function getKontoNr()
    {
        return $this->kontoNr;
    }

    /**
     * Return bool true if kontoNr is set
     *
     * @return bool
     */
    public function isKontoNrSet() : bool
    {
        return ( null !== $this->kontoNr );
    }

    /**
     * Set kontoNr
     *
     * @param int|string $kontoNr
     * @return static
     */
    public function setKontoNr( $kontoNr ) : self
    {
        Sie4IValidator::assertIntegerish( $kontoNr );
        $this->kontoNr = (string) $kontoNr;
        return $this;
    }
}