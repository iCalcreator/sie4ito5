<?php
/**
 * Sie4Ito5   PHP Sie 4I to 5 conversion package
 *
 * This file is a part of Sie4Ito5
 *
 * @author    Kjell-Inge Gustafsson, kigkonsult
 * @copyright 2021 Kjell-Inge Gustafsson, kigkonsult, All rights reserved
 * @link      https://kigkonsult.se
 * @version   1.2
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

Interface Sie4IInterface
{
    /**
     * Product constants
     */
    const PRODUCTNAME              = 'Kigkonsult\Sie4Ito5';
    const PRODUCTVERSION           = '1,0';

    /**
     * Sie4 etiketter
     * Varje etikett avslutas med space
     */

    /**
     * Flaggpost
     */

     /**
      * Flaggpost som anger om filen tagits emot av mottagaren
      */
    const FLAGGA                   = '#FLAGGA';

    /**
     * Identifikationsposter
     */

     /**
      * Vilket program som genererat filen
      */
    const PROGRAM                  = '#PROGRAM';

     /**
      * Vilken teckenuppsättning som använts
      *
      * SKA vara IBM PC 8-bitars extended ASCII (Codepage 437)
      * https://en.wikipedia.org/wiki/Code_page_437
      */
    const FORMAT                   = '#FORMAT';

     /**
      * När och av vem som filen genererats
      */
    const GEN                      = '#GEN';

     /**
      * Vilken typ av SIE-formatet filen följer
      */
    const SIETYP                   = '#SIETYP';

     /**
      * Fri kommentartext kring filens innehåll
      * valfri
      */
    const PROSA                    = '#PROSA';

     /**
      * Företagstyp
      * valfri
      */
    const FTYP                     = '#FTYP';

     /**
      * Redovisningsprogrammets internkod för exporterat företag
      * valfri
      */
    const FNR                      = '#FNR';

     /**
      * Organisationsnummer för det företag som exporterats
      * valfri
      */
    const ORGNR                    = '#ORGNR';

     /**
      * Adressuppgifter för det aktuella företaget
      * valfri
      */
    const ADRESS                   = '#ADRESS';

     /**
      * Fullständigt namn för det företag som exporterats
      */
    const FNAMN                    = '#FNAMN';

     /**
      * Räkenskapsår från vilket exporterade data hämtats
      * valfri
      */
    const RAR                      = '#RAR';

     /**
      * Taxeringsår för deklarations- information (SRU-koder)
      * valfri
      */
    const TAXAR                    = '#TAXAR';

     /**
      * Kontoplanstyp
      * valfri
      */
    const KPTYP                    = '#KPTYP';

     /**
      * Redovisningsvaluta
      * valfri
      */
    const VALUTA                   = '#VALUTA';


    /**
     * Kontoplansuppgifter
     */

     /**
      * Kontouppgifter
      * valfri
      */
    const KONTO                    = '#KONTO';

     /**
      * Kontotyp
      * valfri
      */
    const KTYP                     = '#KTYP';

     /**
      * Enhet vid kvantitetsredovisning
      * valfri
      */
    const ENHET                    = '#ENHET';

     /**
      * RSV-kod för standardiserat räkenskapsutdrag
      * valfri
      */
    const SRU                      = '#SRU';

     /**
      * Dimension
      * valfri
      */
    const DIM                      = '#DIM';

     /**
      * Underdimension
      * valfri
      */
    const UNDERDIM                 = '#UNDERDIM';

     /**
      * Objekt
      * valfri
      */
    const OBJEKT                   = '#OBJEKT';

    /**
     * Saldoposter/Verifikationsposter
     */

     /**
      * Verifikationspost
      * valfri
      */
    const VER                      = '#VER';

     /**
      * Transaktionspost
      * valfri
      */
    const TRANS                    = '#TRANS';

     /**
      * Tillagd transaktionspost
      * valfri
      */
    const RTRANS                   = '#RTRANS';

     /**
      * Borttagen transaktionspost
      * valfri
      */
    const BTRANS                   = '#BTRANS';

    /**
     * Kontrollsummeposter
     */

     /**
      * Start av kontrollsummering/-summa
      * valfri
      */
    const KSUMMA                   = '#KSUMMA';
}
