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

use DateTime;
use InvalidArgumentException;
use Kigkonsult\Asit\It;
use Kigkonsult\Sie4Ito5\Dto\AccountDto;
use Kigkonsult\Sie4Ito5\Dto\DimDto;
use Kigkonsult\Sie4Ito5\Dto\DimObjektDto;
use Kigkonsult\Sie4Ito5\Dto\Sie4IDto;
use Kigkonsult\Sie4Ito5\Dto\TransDto;
use Kigkonsult\Sie4Ito5\Dto\VerDto;
use Kigkonsult\Sie4Ito5\Util\ArrayUtil;
use Kigkonsult\Sie4Ito5\Util\FileUtil;
use Kigkonsult\Sie4Ito5\Util\StringUtil;
use Kigkonsult\Sie5Sdk\Impl\CommonFactory;

use function crc32;
use function implode;
use function rtrim;
use function sprintf;

class Sie4IWriter implements Sie4IInterface
{
    private static $SIEENTRYFMT1 = '%s %s';
    private static $SIEENTRYFMT2 = '%s %s %s';
    private static $SIEENTRYFMT3 = '%s %s %s %s';
    private static $SIEENTRYFMT6 = '%s %s %s %s %s %s %s';
    private static $SIEENTRYFMT7 = '%s %s %s %s %s %s %s %s';
    public  static $YYYYMMDD     = 'Ymd';

    /**
     * Output file rows, managed by Asit\It
     *
     * Rows without eol
     *
     * @var It
     */
    private $output = null;

    /**
     * @var Sie4IDto
     */
    private $sie4IDto = null;

    /**
     * If true, write #KSUMMA in Sie4I output
     *
     * @var bool
     */
    private $writeKsumma = false;

    /**
     * String to base #KSUMMA crc-32 value on
     * @var string
     */
    private $ksummaBase  = null;

    /**
     * @param mixed ...$args
     */
    private function appendKsumma( ...$args )
    {
        if( $this->writeKsumma ) {
            $this->ksummaBase .= implode( $args );
        }
    }

    /**
     * @return null|string
     */
    public function getKsummaBase()
    {
        return $this->ksummaBase;
    }

    /**
     * Return instance
     *
     * @param null|Sie4IDto  $sie4IDto
     * @return static
     * @throws InvalidArgumentException
     */
    public static function factory( $sie4IDto = null ) : self
    {
        $instance = new self();
        if( ! empty( $sie4IDto )) {
            $instance->setSie4IDto( $sie4IDto );
        }
        return $instance;
    }

    /**
     * @param null|Sie4IDto $sie4IDto
     * @param null|string   $outputfile
     * @param null|bool     $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     * @deprecated
     */
    public function write4I(
        $sie4IDto = null,
        $outputfile = null,
        $writeKsumma = false
    ) : string
    {
        return $this->process( $sie4IDto, $outputfile, $writeKsumma );
    }

    /**
     * @param null|Sie4IDto $sie4IDto
     * @param null|string   $outputfile
     * @param null|bool     $writeKsumma
     * @return string
     * @throws InvalidArgumentException
     */
    public function process(
        $sie4IDto = null,
        $outputfile = null,
        $writeKsumma = false
    ) : string
    {
        if( ! empty( $sie4IDto )) {
            $this->setSie4IDto( $sie4IDto );
        }
        if( ! empty( $outputfile )) {
            FileUtil::assertWriteFile( $outputfile, 5201 );
        }
        $this->output      = new It();
        $this->writeKsumma = (bool) $writeKsumma;

        $this->output->append(
            sprintf( self::$SIEENTRYFMT1, self::FLAGGA, StringUtil::$ZERO )
        );
        if( $this->writeKsumma ) {
            $this->output->append( self::KSUMMA );
        }
        $this->writeIdDto();
        $this->writeAccounts();
        $this->writeVerDtos();
        if( $this->writeKsumma ) {
            $this->computeAndWriteKsumma();
        }
        $output = ArrayUtil::eolEndElements( $this->output->get());
        if( ! empty( $outputfile )) {
            FileUtil::writeFile( $outputfile, $output, 5205 );
        }
        return implode( StringUtil::$SP0, $output );
    }

    /**
     * Manage Sie4I 'Identifikationsposter'
     */
    private function writeIdDto()
    {
        static $FORMATPC8 = 'PC8';
        static $SIETYP4   = '4';
        $idDto = $this->sie4IDto->getIdDto();

        // #PROGRAM programnamn version
        $programnamn = StringUtil::utf8toCP437( $idDto->getProgramnamn());
        $version     = StringUtil::utf8toCP437( $idDto->getVersion());
        $this->appendKsumma( self::PROGRAM, $programnamn, $version );

        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::PROGRAM,
                StringUtil::quoteString( $programnamn ),
                StringUtil::quoteString( $version )
            )
        );

        // #FORMAT PC8
        $this->appendKsumma( self::FORMAT, $FORMATPC8 );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT1,
                self::FORMAT,
                $FORMATPC8
            )
        );

        // #GEN datum sign
        $datum = $idDto->getGenDate()->format( self::$YYYYMMDD );
        $this->appendKsumma( self::GEN, $datum );
        $sign  = StringUtil::$SP0;
        if( $idDto->isGenSignSet() && ( self::PRODUCTNAME != $idDto->getGenSign())) {
            $sign = StringUtil::utf8toCP437( $idDto->getGenSign());
            $this->appendKsumma( $sign );
            $sign = StringUtil::quoteString( $sign );
        }
        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEENTRYFMT2,
                    self::GEN,
                    $datum,
                    $sign
                )
            )
        );

        // #SIETYP typnr
        $this->appendKsumma( self::SIETYP, $SIETYP4 );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT1,
                self::SIETYP,
                $SIETYP4
            )
        );

        // #FNR företagsid
        if( $idDto->isFnrIdSet()) {
            $companyClientId = StringUtil::utf8toCP437( $idDto->getFnrId());
            $this->appendKsumma( self::FNR, $companyClientId );
            $this->output->append(
                sprintf( self::$SIEENTRYFMT1, self::FNR, $companyClientId )
            );
        }
        // #ORGNR orgnr förvnr verknr (förvnr = multiple if not is null|1)
        $orgnr    = $idDto->getOrgnr();
        $multiple = ( $idDto->getMultiple() ?: StringUtil::$SP0 );
        $this->appendKsumma( self::ORGNR, $orgnr, $multiple );
        $this->output->append(
            rtrim(
                sprintf(
                    self::$SIEENTRYFMT2,
                    self::ORGNR,
                    $orgnr,
                    $multiple
                )
            )
        );
        // #FNAMN företagsnamn
        if( $idDto->isFnamnSet()) {
            $companyName = StringUtil::utf8toCP437( $idDto->getFnamn());
            $this->appendKsumma( self::FNAMN, $companyName );
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT1,
                    self::FNAMN,
                    StringUtil::quoteString( $companyName )
                )
            );
        }

        // #VALUTA valutakod
        if( $idDto->isValutakodSet()) {
            $valutakod = StringUtil::utf8toCP437( $idDto->getValutakod());
            $this->appendKsumma( self::VALUTA, $valutakod );
            $this->output->append(
                sprintf( self::$SIEENTRYFMT1, self::VALUTA, $valutakod )
            );
        }
    }

    /**
     * Manage Sie4I  'Kontoplansuppgifter'
     *
     * #SRU and #UNDERDIM are skipped
     */
    private function writeAccounts()
    {
        if( 0 < $this->sie4IDto->countAccountDtos()) {
            foreach( $this->sie4IDto->getAccountDtos() as $accountDto ) {
                // empty row before each #KONTO
                $this->output->append( StringUtil::$SP0 );
                $this->writeKontoData( $accountDto );
            } // end foreach
        }

        if( 0 < $this->sie4IDto->countDimDtos()) {
            foreach( $this->sie4IDto->getDimDtos() as $dimDto ) {
                // empty row before each #DIM
                $this->output->append( StringUtil::$SP0 );
                $this->writeDimData( $dimDto );
            } // end foreach
        }

        if( 0 < $this->sie4IDto->countDimObjektDtos()) {
            foreach( $this->sie4IDto->getDimObjektDtos() as $dimObjektDto ) {
                // empty row before each #DIM
                $this->output->append( StringUtil::$SP0 );
                $this->writeDimObjectData( $dimObjektDto );
            } // end foreach
        }
    }

    /**
     * #KONTO kontonr kontoNamn
     * #KTYP kontonr  kontoTyp
     * #ENHET kontonr enhet
     *
     * @param AccountDto $accountDto
     */
    private function writeKontoData( AccountDto $accountDto )
    {
        $kontoNr   = $accountDto->getKontoNr();
        $kontonamn = StringUtil::utf8toCP437( $accountDto->getKontoNamn());
        $this->appendKsumma( self::KONTO, $kontoNr, $kontonamn );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::KONTO,
                $kontoNr,
                StringUtil::quoteString( $kontonamn )
            )
        );
        $kontotyp = StringUtil::utf8toCP437( $accountDto->getKontoTyp() );
        $this->appendKsumma( self::KTYP, $kontoNr, $kontotyp );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::KTYP,
                $kontoNr,
                $kontotyp
            )
        );
        if( $accountDto->isEnhetSet()) {
            $enhet = StringUtil::utf8toCP437( $accountDto->getEnhet());
            $this->appendKsumma( self::ENHET, $kontoNr, $enhet );
            $this->output->append(
                sprintf(
                    self::$SIEENTRYFMT2,
                    self::ENHET,
                    $kontoNr,
                    $enhet
                )
            );
        } // end if
    }

    /**
     * #DIM dimensionsnr namn
     *
     * @param DimDto $dimDto
     */
    private function writeDimData( DimDto $dimDto )
    {
        $dimId = $dimDto->getDimensionsNr();
        $namn  = StringUtil::utf8toCP437( $dimDto->getDimensionsNamn());
        $this->appendKsumma( self::DIM, $dimId, $namn );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT2,
                self::DIM,
                $dimId,
                StringUtil::quoteString( $namn )
            )
        );
    }

    /**
     * #OBJEKT dimensionsnr objektnr objektnamn
     *
     * @param DimObjektDto $dimObjektDto
     */
    private function writeDimObjectData( DimObjektDto $dimObjektDto )
    {
        $dimId      = $dimObjektDto->getDimensionsNr();
        $objektnr   = StringUtil::utf8toCP437( $dimObjektDto->getObjektNr());
        $objektnamn = StringUtil::utf8toCP437( $dimObjektDto->getObjektNamn());
        $this->appendKsumma( self::OBJEKT, $dimId, $objektnr, $objektnamn );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT3,
                self::OBJEKT,
                $dimId,
                StringUtil::quoteString( $objektnr ),
                StringUtil::quoteString( $objektnamn )
            )
        );
    }

    /**
     * Managing writing of #VER and #TRANS
     */
    private function writeVerDtos()
    {
        if( empty( $this->sie4IDto->countVerDtos())) {
            return;
        }
        foreach( $this->sie4IDto->getVerDtos() as $verDto ) {
            // empty row before each #VER
            $this->output->append( StringUtil::$SP0 );
            $this->writeVerDto( $verDto );
        } // end foreach
    }

    /**
     * Writes #VER and #TRANS
     *
     * #VER serie vernr verdatum vertext regdatum sign
     *
     * @param VerDto $verDto
     */
    private function writeVerDto( VerDto $verDto )
    {
        $this->appendKsumma( self::VER );
        if( $verDto->isSerieSet()) {
            $serie = $verDto->getSerie();
            $this->appendKsumma( $serie );
        }
        else {
            $serie = StringUtil::$DOUBLEQUOTE;
        }

        if( $verDto->isVernrSet()) {
            $vernr = $verDto->getVernr();
            $this->appendKsumma( $vernr );
        }
        else {
            $vernr = StringUtil::$DOUBLEQUOTE;
        }

        $datum     = $verDto->isVerdatumSet()
            ? $verDto->getVerdatum()
            : new DateTime();
        $verdatum = $datum->format( self::$YYYYMMDD );
        $this->appendKsumma( $verdatum );

        if( $verDto->isVertextSet()) {
            $vertext = $verDto->getVertext();
            $vertext = StringUtil::utf8toCP437( $vertext );
            $this->appendKsumma( $vertext );
            $vertext = StringUtil::quoteString( $vertext );
        }
        else {
            $vertext = StringUtil::$DOUBLEQUOTE;
        }

        if( ! $verDto->isRegdatumSet()) {
            $regdatum = StringUtil::$DOUBLEQUOTE;
        }
        else {
            $regdatum = $verDto->getRegdatum()->format( self::$YYYYMMDD );
            if( $verdatum == $regdatum ) {
                // skip if equal
                $regdatum = StringUtil::$DOUBLEQUOTE;
            }
            else {
                $this->appendKsumma( $regdatum );
            }
        }

        if( $verDto->isSignSet()) {
            $sign = $verDto->getSign();
            $sign = StringUtil::utf8toCP437( $sign );
            $this->appendKsumma( $sign );
            $sign = StringUtil::quoteString( $sign );
        }
        else {
            $sign = StringUtil::$SP0;
        }

        $row = rtrim(
            sprintf(
                self::$SIEENTRYFMT6,
                self::VER,
                $serie,
                (string) $vernr,
                $verdatum,
                $vertext,
                $regdatum,
                $sign
            )
        );

        $this->output->append( StringUtil::d2qRtrim( $row ));

        $this->output->append( StringUtil::$CURLYBRACKETS[0] );
        foreach( $verDto->getTransDtos() as $transDto ) {
            $this->writeTransDto( $transDto, $verdatum );
        }
        $this->output->append( StringUtil::$CURLYBRACKETS[1] );
    }

    /**
     * Write #TRANS
     *
     * #TRANS kontonr {objektlista} belopp transdat(opt) transtext(opt) kvantitet sign
     * ex  #TRANS 7010 {"1" "456" "7" "47"} 13200.00
     * Note, sign is skipped
     *
     * @param TransDto $transDto
     * @param string $verdatum
     */
    private function writeTransDto( TransDto $transDto, string $verdatum )
    {
        $kontonr = StringUtil::utf8toCP437( $transDto->getKontoNr());
        $this->appendKsumma( self::TRANS, $kontonr );

        if( 0 < $transDto->countObjektlista()) {
            list( $objektlista, $ksummaPart ) = self::getObjektLista(
                $transDto->getObjektlista()
            );
            if( ! empty( $objektlista ) ) {
                $this->appendKsumma( $ksummaPart );
            }
        }
        else {
            $objektlista = StringUtil::curlyBacketsString( StringUtil::$SP0 );
        }

        $belopp = CommonFactory::formatAmount( $transDto->getBelopp());
        $this->appendKsumma( $belopp );

        if( $transDto->isTransdatSet()) {
            $transdat = $transDto->getTransdat()->format( self::$YYYYMMDD );
            if( $transdat == $verdatum ) {
                // skip if equal
                $transdat = StringUtil::$DOUBLEQUOTE;
            }
            else {
                $this->appendKsumma( $transdat );
            }
        }
        else {
            $transdat = StringUtil::$DOUBLEQUOTE;
        }

        if( $transDto->isTranstextSet()) {
            $transtext = StringUtil::utf8toCP437( $transDto->getTranstext());
            $this->appendKsumma( $transtext );
            $transtext = StringUtil::quoteString( $transtext );
        }
        else {
            $transtext = StringUtil::$DOUBLEQUOTE;
        }

        if( $transDto->isKvantitetSet()) {
            $kvantitet = $transDto->getKvantitet();
            $this->appendKsumma( $kvantitet );
        }
        else {
            $kvantitet = StringUtil::$DOUBLEQUOTE;
        }

        if( $transDto->isSignSet()) {
            $sign = StringUtil::utf8toCP437( $transDto->getSign());
            $this->appendKsumma( $sign );
            $sign = StringUtil::quoteString( $sign );
        }
        else {
            $sign = StringUtil::$SP0;
        }

        $row = rtrim(
            sprintf(
                self::$SIEENTRYFMT7,
                self::TRANS,
                $kontonr,
                $objektlista,
                $belopp,
                $transdat,
                $transtext,
                $kvantitet,
                $sign
            )
        );
        $this->output->append( StringUtil::d2qRtrim( $row ));
    }

    /**
     * Return string with (quoted) dimId and objectId pairs (if set)
     *
     * @param DimObjektDto[] $dimObjektDtos
     * @return array
     */
    private static function getObjektLista( array $dimObjektDtos ) : array
    {
        $objektlista = [];
        $ksummaPart  = StringUtil::$SP0;
        foreach( $dimObjektDtos as $dimObjektDto ) {
            $dimId         = $dimObjektDto->getDimensionsNr();
            $objektlista[] = StringUtil::quoteString((string) $dimId );
            $objektId      = StringUtil::utf8toCP437( $dimObjektDto->getObjektNr());
            $objektlista[] = StringUtil::quoteString( $objektId );
            $ksummaPart   .= $dimId . $objektId;
        } // end foreach
        return [
            StringUtil::curlyBacketsString( implode( StringUtil::$SP1, $objektlista )),
            $ksummaPart
        ];
    }

    /**
     * Computes and writes trailing Ksumma
     */
    private function computeAndWriteKsumma()
    {
        // empty row before
        $this->output->append( StringUtil::$SP0 );
        $this->output->append(
            sprintf(
                self::$SIEENTRYFMT1,
                self::KSUMMA,
                (string) crc32( $this->getKsummaBase())
            )
        );
    }

    /**
     * @param Sie4IDto $sie4IDto
     * @return static
     * @throws InvalidArgumentException
     */
    public function setSie4IDto( Sie4IDto $sie4IDto ) : self
    {
        Sie4IValidator::assertSie4IDto( $sie4IDto );
        $this->sie4IDto = $sie4IDto;
        return $this;
    }
}
