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
namespace Kigkonsult\Sie4Ito5;

Interface Sie4IInterface
{
    /**
     * Product constants
     */
    const PRODUCTNAME              = 'Kigkonsult\Sie4Ito5';
    const PRODUCTVERSION           = '1.2';

    /**
     * Constants for Sie4 labels and sub-labels values
     * as defined in 'SIE filformat - Utgåva 4B'.
     * ALL constant values in upper case.
     * If lable has only one sub-field, NO sub-label defined
     * For skipped lables, no sub-lables defined
     */

    /**
     * Flaggpost
     */

     /**
      * Flaggpost som anger om filen tagits emot av mottagaren
      * obligatorisk i Sie4I
      */
    const FLAGGA                   = '#FLAGGA';

    /**
     * Identifikationsposter
     */

     /**
      * Vilket program som genererat filen
      * #PROGRAM programnamn version
      * obligatorisk
      */
    const PROGRAM                  = '#PROGRAM';
    const PROGRAMNAMN              = 'PROGRAMNAMN';
    const PROGRAMVERSION           = 'PROGRAMVERSION';

     /**
      * Vilken teckenuppsättning som använts
      *
      * SKA vara IBM PC 8-bitars extended ASCII (Codepage 437)
      * https://en.wikipedia.org/wiki/Code_page_437
      * obligatorisk i Sie4I, auto
      */
    const FORMAT                   = '#FORMAT';

     /**
      * När och av vem som filen genererats
      * #GEN datum sign
      * Obligatorisk (sign opt) Sie4I, båda obl. Sie5 SieEntry
      */
    const GEN                      = '#GEN';
    const GENDATUM                 = 'GENDATUM';
    const GENSIGN                  = 'GENSIGN';

     /**
      * Vilken typ av SIE-formatet filen följer
      * obligatorisk, auto i Sie4I : 4
      */
    const SIETYP                   = '#SIETYP';

     /**
      * Fri kommentartext kring filens innehåll
      * valfri, ignoreras
      */
    const PROSA                    = '#PROSA';

     /**
      * Företagstyp
      * valfri, ignoreras
      */
    const FTYP                     = '#FTYP';

     /**
      * Redovisningsprogrammets internkod för exporterat företag
      * #FNR företagsid
      * valfri
      */
    const FNR                      = '#FNR';
    const FNRID                    = 'FNRID';

     /**
      * Organisationsnummer för det företag som exporterats
      * #ORGNR orgnr förvnr verknr
      * förvnr : anv då ensk. person driver flera ensk. firmor (ordningsnr)
      * verknr : anv ej
      * valfri i sie4IDto, obl (orgnr) i SirEntry
      */
    const ORGNR                    = '#ORGNR';
    const ORGNRORGNR               = 'ORGNRORGNR';
    const ORGNRFORNVR              = 'ORGNRFORNVR';

     /**
      * Adressuppgifter för det aktuella företaget
      * valfri, ignoreras
      */
    const ADRESS                   = '#ADRESS';

     /**
      * Fullständigt namn för det företag som exporterats
      * #FNAMN företagsnamn
      * Obligatorisk i Sie4I, valfri i SieEntry
      */
    const FNAMN                    = '#FNAMN';
    const FTGNAMN                  = 'FNAMN';

     /**
      * Räkenskapsår från vilket exporterade data hämtats
      * valfri, ignoreras
      */
    const RAR                      = '#RAR';

     /**
      * Taxeringsår för deklarations- information (SRU-koder)
      * valfri, ignoreras
      */
    const TAXAR                    = '#TAXAR';

     /**
      * Kontoplanstyp
      * valfri, ignoreras
      */
    const KPTYP                    = '#KPTYP';

     /**
      * Redovisningsvaluta
      * #VALUTA valutakod
      * valfri
      */
    const VALUTA                   = '#VALUTA';
    const VALUTAKOD                = 'VALUTAKOD';


    /**
     * Kontoplansuppgifter
     */

     /**
      * Kontouppgifter
      * #KONTO kontonr kontonamn
      * valfri
      */
    const KONTO                    = '#KONTO';
    const KONTONR                  = 'KONTONR';
    const KONTONAMN                = 'KONTONAMN';

     /**
      * Kontotyp
      * #KTYP kontonr  kontoTyp
      * valfri
      */
    const KTYP                     = '#KTYP';
    const KONTOTYP                 = 'KONTOTYP';

     /**
      * Enhet vid kvantitetsredovisning
      * #ENHET kontonr enhet
      * valfri
      */
    const ENHET                    = '#ENHET';
    const KONTOENHET               = 'KONTOENHET';

     /**
      * RSV-kod för standardiserat räkenskapsutdrag
      * valfri, ignoreras
      */
    const SRU                      = '#SRU';

     /**
      * Dimension
      * #DIM dimensionsnr namn
      * valfri
      */
    const DIM                      = '#DIM';
    const DIMENSIONNR              = 'DIMENSIONNR';
    const DIMENSIONNAMN            = 'DIMENSIONNAMN';

     /**
      * Underdimension
      * valfri, ignoreras
      */
    const UNDERDIM                 = '#UNDERDIM';

     /**
      * Objekt
      * #OBJEKT dimensionsnr objektnr objektnamn
      * valfri
      */
    const OBJEKT                   = '#OBJEKT';
    const OBJEKTDIMENSIONNR        = 'OBJEKTDIMENSIONNR';
    const OBJEKTNR                 = 'OBJEKTNR';
    const OBJEKTNAMN               = 'OBJEKTNAMN';

    /**
     * Saldoposter/Verifikationsposter
     */

     /**
      * Verifikationspost
      * #VER serie vernr verdatum vertext regdatum sign
      * Obligatorisk
      * Enbart verdatum obligatoriskt
      * auto-gen (now) om det saknas i Sie4I
      */
    const VER                      = '#VER';
    const VERSERIE                 = 'VERSERIE';
    const VERNR                    = 'VERNR';
    const VERDATUM                 = 'VERDATUM';
    const VERTEXT                  = 'VERTEXT';
    const REGDATUM                 = 'REGDATUM';
    const VERSIGN                  = 'VERSIGN';

     /**
      * Transaktionspost
      * valfri enl Sie4I-pdf, obl i importfil
      * #TRANS kontonr {objektlista} belopp transdat(opt) transtext(opt) kvantitet sign
      * Obligatoriskt : kontonr/belopp
      */
    const TRANS                    = '#TRANS';
    const TRANSKONTONR             = 'TRANSKONTONR';
    const TRANSDIMENSIONNR         = 'TRANSDIMENSIONNR';
    const TRANSOBJEKTNR            = 'TRANSOBJEKTNR';
    const BELOPP                   = 'BELOPP';
    const TRANSDAT                 = 'TRANSDAT';
    const TRANSTEXT                = 'TRANSTEXT';
    const KVANTITET                = 'KVANTITET';
    const TRANSSIGN                = 'TRANSSIGN';

     /**
      * Tillagd transaktionspost
      * valfri, ignoreras
      */
    const RTRANS                   = '#RTRANS';

     /**
      * Borttagen transaktionspost
      * valfri, ignoreras
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
