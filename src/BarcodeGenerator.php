<?php

/**
 * General PHP Barcode Generator
 *
 * @author Casper Bakker - picqer.com
 * Based on TCPDF Barcode Generator
 */

// Copyright (C) 2002-2015 Nicola Asuni - Tecnick.com LTD
//
// This file was part of TCPDF software library.
//
// TCPDF is free software: you can redistribute it and/or modify it
// under the terms of the GNU Lesser General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// TCPDF is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// See the GNU Lesser General Public License for more details.
//
// You should have received a copy of the License
// along with TCPDF. If not, see
// <http://www.tecnick.com/pagefiles/tcpdf/LICENSE.TXT>.
//
// See LICENSE.TXT file for more information.

namespace Picqer\Barcode;

use Picqer\Barcode\Exceptions\InvalidCharacterException;
use Picqer\Barcode\Exceptions\InvalidCheckDigitException;
use Picqer\Barcode\Exceptions\InvalidFormatException;
use Picqer\Barcode\Exceptions\InvalidLengthException;
use Picqer\Barcode\Exceptions\UnknownTypeException;
use Picqer\Barcode\Helpers\BinarySequenceConverter;
use Picqer\Barcode\Helpers\OldBarcodeArrayConverter;
use Picqer\Barcode\Types\TypeCodabar;
use Picqer\Barcode\Types\TypeCode11;
use Picqer\Barcode\Types\TypeCode93;
use Picqer\Barcode\Types\TypeEan13;
use Picqer\Barcode\Types\TypeEan8;
use Picqer\Barcode\Types\TypeIntelligentMailBarcode;
use Picqer\Barcode\Types\TypeInterleaved25;
use Picqer\Barcode\Types\TypeInterleaved25Checksum;
use Picqer\Barcode\Types\TypeKix;
use Picqer\Barcode\Types\TypeMsi;
use Picqer\Barcode\Types\TypeMsiChecksum;
use Picqer\Barcode\Types\TypePharmacode;
use Picqer\Barcode\Types\TypePharmacodeTwoCode;
use Picqer\Barcode\Types\TypePlanet;
use Picqer\Barcode\Types\TypePostnet;
use Picqer\Barcode\Types\TypeRms4cc;
use Picqer\Barcode\Types\TypeUpcA;
use Picqer\Barcode\Types\TypeUpcE;

abstract class BarcodeGenerator
{
    const TYPE_CODE_39 = 'C39'; // CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
    const TYPE_CODE_39_CHECKSUM = 'C39+';  // CODE 39 with checksum
    const TYPE_CODE_39E = 'C39E'; // CODE 39 EXTENDED
    const TYPE_CODE_39E_CHECKSUM = 'C39E+'; // CODE 39 EXTENDED + CHECKSUM
    const TYPE_CODE_93 = 'C93'; // CODE 93 - USS-93
    const TYPE_STANDARD_2_5 = 'S25'; // Standard 2 of 5
    const TYPE_STANDARD_2_5_CHECKSUM = 'S25+'; // Standard 2 of 5 + CHECKSUM
    const TYPE_INTERLEAVED_2_5 = 'I25'; // Interleaved 2 of 5
    const TYPE_INTERLEAVED_2_5_CHECKSUM = 'I25+'; // Interleaved 2 of 5 + CHECKSUM
    const TYPE_CODE_128 = 'C128';
    const TYPE_CODE_128_A = 'C128A';
    const TYPE_CODE_128_B = 'C128B';
    const TYPE_CODE_128_C = 'C128C';
    const TYPE_EAN_2 = 'EAN2'; // 2-Digits UPC-Based Extention
    const TYPE_EAN_5 = 'EAN5'; // 5-Digits UPC-Based Extention
    const TYPE_EAN_8 = 'EAN8';
    const TYPE_EAN_13 = 'EAN13';
    const TYPE_UPC_A = 'UPCA';
    const TYPE_UPC_E = 'UPCE';
    const TYPE_MSI = 'MSI'; // MSI (Variation of Plessey code)
    const TYPE_MSI_CHECKSUM = 'MSI+'; // MSI + CHECKSUM (modulo 11)
    const TYPE_POSTNET = 'POSTNET';
    const TYPE_PLANET = 'PLANET';
    const TYPE_RMS4CC = 'RMS4CC'; // RMS4CC (Royal Mail 4-state Customer Code) - CBC (Customer Bar Code)
    const TYPE_KIX = 'KIX'; // KIX (Klant index - Customer index)
    const TYPE_IMB = 'IMB'; // IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200
    const TYPE_CODABAR = 'CODABAR';
    const TYPE_CODE_11 = 'CODE11';
    const TYPE_PHARMA_CODE = 'PHARMA';
    const TYPE_PHARMA_CODE_TWO_TRACKS = 'PHARMA2T';

    /**
     * Get the barcode data
     *
     * @param string $code code to print
     * @param string $type type of barcode
     * @return array barcode array
     * @public
     */
    protected function getBarcodeData($code, $type)
    {
        switch (strtoupper($type)) {
            case self::TYPE_CODE_39:
                $arrcode = $this->barcode_code39($code, false, false);
                break;

            case self::TYPE_CODE_39_CHECKSUM:
                $arrcode = $this->barcode_code39($code, false, true);
                break;

            case self::TYPE_CODE_39E:
                $arrcode = $this->barcode_code39($code, true, false);
                break;

            case self::TYPE_CODE_39E_CHECKSUM:
                $arrcode = $this->barcode_code39($code, true, true);
                break;

            case self::TYPE_CODE_93:
                $barcodeDataBuilder = new TypeCode93();
                break;

            case self::TYPE_STANDARD_2_5:
                $arrcode = $this->barcode_s25($code, false);
                break;

            case self::TYPE_STANDARD_2_5_CHECKSUM:
                $arrcode = $this->barcode_s25($code, true);
                break;

            case self::TYPE_INTERLEAVED_2_5:
                $barcodeDataBuilder = new TypeInterleaved25();
                break;

            case self::TYPE_INTERLEAVED_2_5_CHECKSUM:
                $barcodeDataBuilder = new TypeInterleaved25Checksum();
                break;

            case self::TYPE_CODE_128:
                $arrcode = $this->barcode_c128($code, '');
                break;

            case self::TYPE_CODE_128_A:
                $arrcode = $this->barcode_c128($code, 'A');
                break;

            case self::TYPE_CODE_128_B:
                $arrcode = $this->barcode_c128($code, 'B');
                break;

            case self::TYPE_CODE_128_C:
                $arrcode = $this->barcode_c128($code, 'C');
                break;

            case self::TYPE_EAN_2:
                $arrcode = $this->barcode_eanext($code, 2);
                break;

            case self::TYPE_EAN_5:
                $arrcode = $this->barcode_eanext($code, 5);
                break;

            case self::TYPE_EAN_8:
                $barcodeDataBuilder = new TypeEan8();
                break;

            case self::TYPE_EAN_13:
                $barcodeDataBuilder = new TypeEan13();
                break;

            case self::TYPE_UPC_A:
                $barcodeDataBuilder = new TypeUpcA();
                break;

            case self::TYPE_UPC_E:
                $barcodeDataBuilder = new TypeUpcE();
                break;

            case self::TYPE_MSI:
                $barcodeDataBuilder = new TypeMsi();
                break;

            case self::TYPE_MSI_CHECKSUM:
                $barcodeDataBuilder = new TypeMsiChecksum();
                break;

            case self::TYPE_POSTNET:
                $barcodeDataBuilder = new TypePostnet();
                break;

            case self::TYPE_PLANET:
                $barcodeDataBuilder = new TypePlanet();
                break;

            case self::TYPE_RMS4CC:
                $barcodeDataBuilder = new TypeRms4cc();
                break;

            case self::TYPE_KIX:
                $barcodeDataBuilder = new TypeKix();
                break;

            case self::TYPE_IMB:
                $barcodeDataBuilder = new TypeIntelligentMailBarcode();
                break;

            case self::TYPE_CODABAR:
                $barcodeDataBuilder = new TypeCodabar();
                break;

            case self::TYPE_CODE_11:
                $barcodeDataBuilder = new TypeCode11();
                break;

            case self::TYPE_PHARMA_CODE:
                $barcodeDataBuilder = new TypePharmacode();
                break;

            case self::TYPE_PHARMA_CODE_TWO_TRACKS:
                $barcodeDataBuilder = new TypePharmacodeTwoCode();
                break;

            default:
                throw new UnknownTypeException();
        }

        if (! isset($arrcode) && isset($barcodeDataBuilder)) {
            $arrcode = $barcodeDataBuilder->getBarcodeData($code);
        }

        if ( ! isset($arrcode['maxWidth'])) {
            return OldBarcodeArrayConverter::convert($arrcode);
        }

        return $arrcode;
    }

    /**
     * CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.
     * General-purpose code in very wide use world-wide
     *
     * @param $code (string) code to represent.
     * @param $extended (boolean) if true uses the extended mode.
     * @param $checksum (boolean) if true add a checksum to the code.
     * @return array barcode representation.
     * @protected
     */
    protected function barcode_code39($code, $extended = false, $checksum = false)
    {
        $chr = [];
        $chr['0'] = '111331311';
        $chr['1'] = '311311113';
        $chr['2'] = '113311113';
        $chr['3'] = '313311111';
        $chr['4'] = '111331113';
        $chr['5'] = '311331111';
        $chr['6'] = '113331111';
        $chr['7'] = '111311313';
        $chr['8'] = '311311311';
        $chr['9'] = '113311311';
        $chr['A'] = '311113113';
        $chr['B'] = '113113113';
        $chr['C'] = '313113111';
        $chr['D'] = '111133113';
        $chr['E'] = '311133111';
        $chr['F'] = '113133111';
        $chr['G'] = '111113313';
        $chr['H'] = '311113311';
        $chr['I'] = '113113311';
        $chr['J'] = '111133311';
        $chr['K'] = '311111133';
        $chr['L'] = '113111133';
        $chr['M'] = '313111131';
        $chr['N'] = '111131133';
        $chr['O'] = '311131131';
        $chr['P'] = '113131131';
        $chr['Q'] = '111111333';
        $chr['R'] = '311111331';
        $chr['S'] = '113111331';
        $chr['T'] = '111131331';
        $chr['U'] = '331111113';
        $chr['V'] = '133111113';
        $chr['W'] = '333111111';
        $chr['X'] = '131131113';
        $chr['Y'] = '331131111';
        $chr['Z'] = '133131111';
        $chr['-'] = '131111313';
        $chr['.'] = '331111311';
        $chr[' '] = '133111311';
        $chr['$'] = '131313111';
        $chr['/'] = '131311131';
        $chr['+'] = '131113131';
        $chr['%'] = '111313131';
        $chr['*'] = '131131311';

        $code = strtoupper($code);

        if ($extended) {
            // extended mode
            $code = $this->encode_code39_ext($code);
        }

        if ($checksum) {
            // checksum
            $code .= $this->checksum_code39($code);
        }

        // add start and stop codes
        $code = '*' . $code . '*';

        $bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());
        $k = 0;
        $clen = strlen($code);
        for ($i = 0; $i < $clen; ++$i) {
            $char = $code[$i];
            if ( ! isset($chr[$char])) {
                throw new InvalidCharacterException('Char ' . $char . ' is unsupported');
            }
            for ($j = 0; $j < 9; ++$j) {
                if (($j % 2) == 0) {
                    $t = true; // bar
                } else {
                    $t = false; // space
                }
                $w = $chr[$char][$j];
                $bararray['bcode'][$k] = array('t' => $t, 'w' => $w, 'h' => 1, 'p' => 0);
                $bararray['maxw'] += $w;
                ++$k;
            }
            // intercharacter gap
            $bararray['bcode'][$k] = array('t' => false, 'w' => 1, 'h' => 1, 'p' => 0);
            $bararray['maxw'] += 1;
            ++$k;
        }

        return $bararray;
    }

    /**
     * Encode a string to be used for CODE 39 Extended mode.
     *
     * @param string $code code to represent.
     * @return bool|string encoded string.
     * @protected
     */
    protected function encode_code39_ext($code)
    {
        $encode = array(
            chr(0)   => '%U',
            chr(1)   => '$A',
            chr(2)   => '$B',
            chr(3)   => '$C',
            chr(4)   => '$D',
            chr(5)   => '$E',
            chr(6)   => '$F',
            chr(7)   => '$G',
            chr(8)   => '$H',
            chr(9)   => '$I',
            chr(10)  => '$J',
            chr(11)  => '£K',
            chr(12)  => '$L',
            chr(13)  => '$M',
            chr(14)  => '$N',
            chr(15)  => '$O',
            chr(16)  => '$P',
            chr(17)  => '$Q',
            chr(18)  => '$R',
            chr(19)  => '$S',
            chr(20)  => '$T',
            chr(21)  => '$U',
            chr(22)  => '$V',
            chr(23)  => '$W',
            chr(24)  => '$X',
            chr(25)  => '$Y',
            chr(26)  => '$Z',
            chr(27)  => '%A',
            chr(28)  => '%B',
            chr(29)  => '%C',
            chr(30)  => '%D',
            chr(31)  => '%E',
            chr(32)  => ' ',
            chr(33)  => '/A',
            chr(34)  => '/B',
            chr(35)  => '/C',
            chr(36)  => '/D',
            chr(37)  => '/E',
            chr(38)  => '/F',
            chr(39)  => '/G',
            chr(40)  => '/H',
            chr(41)  => '/I',
            chr(42)  => '/J',
            chr(43)  => '/K',
            chr(44)  => '/L',
            chr(45)  => '-',
            chr(46)  => '.',
            chr(47)  => '/O',
            chr(48)  => '0',
            chr(49)  => '1',
            chr(50)  => '2',
            chr(51)  => '3',
            chr(52)  => '4',
            chr(53)  => '5',
            chr(54)  => '6',
            chr(55)  => '7',
            chr(56)  => '8',
            chr(57)  => '9',
            chr(58)  => '/Z',
            chr(59)  => '%F',
            chr(60)  => '%G',
            chr(61)  => '%H',
            chr(62)  => '%I',
            chr(63)  => '%J',
            chr(64)  => '%V',
            chr(65)  => 'A',
            chr(66)  => 'B',
            chr(67)  => 'C',
            chr(68)  => 'D',
            chr(69)  => 'E',
            chr(70)  => 'F',
            chr(71)  => 'G',
            chr(72)  => 'H',
            chr(73)  => 'I',
            chr(74)  => 'J',
            chr(75)  => 'K',
            chr(76)  => 'L',
            chr(77)  => 'M',
            chr(78)  => 'N',
            chr(79)  => 'O',
            chr(80)  => 'P',
            chr(81)  => 'Q',
            chr(82)  => 'R',
            chr(83)  => 'S',
            chr(84)  => 'T',
            chr(85)  => 'U',
            chr(86)  => 'V',
            chr(87)  => 'W',
            chr(88)  => 'X',
            chr(89)  => 'Y',
            chr(90)  => 'Z',
            chr(91)  => '%K',
            chr(92)  => '%L',
            chr(93)  => '%M',
            chr(94)  => '%N',
            chr(95)  => '%O',
            chr(96)  => '%W',
            chr(97)  => '+A',
            chr(98)  => '+B',
            chr(99)  => '+C',
            chr(100) => '+D',
            chr(101) => '+E',
            chr(102) => '+F',
            chr(103) => '+G',
            chr(104) => '+H',
            chr(105) => '+I',
            chr(106) => '+J',
            chr(107) => '+K',
            chr(108) => '+L',
            chr(109) => '+M',
            chr(110) => '+N',
            chr(111) => '+O',
            chr(112) => '+P',
            chr(113) => '+Q',
            chr(114) => '+R',
            chr(115) => '+S',
            chr(116) => '+T',
            chr(117) => '+U',
            chr(118) => '+V',
            chr(119) => '+W',
            chr(120) => '+X',
            chr(121) => '+Y',
            chr(122) => '+Z',
            chr(123) => '%P',
            chr(124) => '%Q',
            chr(125) => '%R',
            chr(126) => '%S',
            chr(127) => '%T'
        );
        $code_ext = '';
        $clen = strlen($code);
        for ($i = 0; $i < $clen; ++$i) {
            if (ord($code[$i]) > 127) {
                throw new InvalidCharacterException('Only supports till char 127');
            }
            $code_ext .= $encode[$code[$i]];
        }

        return $code_ext;
    }

    /**
     * Calculate CODE 39 checksum (modulo 43).
     *
     * @param string $code code to represent.
     * @return string char checksum.
     * @protected
     */
    protected function checksum_code39($code)
    {
        $chars = array(
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            '-',
            '.',
            ' ',
            '$',
            '/',
            '+',
            '%'
        );
        $sum = 0;
        $codelength = strlen($code);
        for ($i = 0; $i < $codelength; ++$i) {
            $k = array_keys($chars, $code[$i]);
            $sum += $k[0];
        }
        $j = ($sum % 43);

        return $chars[$j];
    }

    /**
     * Checksum for standard 2 of 5 barcodes.
     *
     * @param $code (string) code to process.
     * @return int checksum.
     * @protected
     */
    protected function checksum_s25($code)
    {
        $len = strlen($code);
        $sum = 0;
        for ($i = 0; $i < $len; $i += 2) {
            $sum += $code[$i];
        }
        $sum *= 3;
        for ($i = 1; $i < $len; $i += 2) {
            $sum += ($code[$i]);
        }
        $r = $sum % 10;
        if ($r > 0) {
            $r = (10 - $r);
        }

        return $r;
    }

    /**
     * Standard 2 of 5 barcodes.
     * Used in airline ticket marking, photofinishing
     * Contains digits (0 to 9) and encodes the data only in the width of bars.
     *
     * @param $code (string) code to represent.
     * @param $checksum (boolean) if true add a checksum to the code
     * @return array barcode representation.
     * @protected
     */
    protected function barcode_s25($code, $checksum = false)
    {
        $chr['0'] = '10101110111010';
        $chr['1'] = '11101010101110';
        $chr['2'] = '10111010101110';
        $chr['3'] = '11101110101010';
        $chr['4'] = '10101110101110';
        $chr['5'] = '11101011101010';
        $chr['6'] = '10111011101010';
        $chr['7'] = '10101011101110';
        $chr['8'] = '10101110111010';
        $chr['9'] = '10111010111010';
        if ($checksum) {
            // add checksum
            $code .= $this->checksum_s25($code);
        }
        if ((strlen($code) % 2) != 0) {
            // add leading zero if code-length is odd
            $code = '0' . $code;
        }
        $seq = '11011010';
        $clen = strlen($code);
        for ($i = 0; $i < $clen; ++$i) {
            $digit = $code[$i];
            if ( ! isset($chr[$digit])) {
                throw new InvalidCharacterException('Char ' . $digit . ' is unsupported');
            }
            $seq .= $chr[$digit];
        }
        $seq .= '1101011';
        $bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());

        return BinarySequenceConverter::convert($seq, $bararray);
    }


    /**
     * C128 barcodes.
     * Very capable code, excellent density, high reliability; in very wide use world-wide
     *
     * @param $code (string) code to represent.
     * @param $type (string) barcode type: A, B, C or empty for automatic switch (AUTO mode)
     * @return array barcode representation.
     * @protected
     */
    protected function barcode_c128($code, $type = '')
    {
        $chr = array(
            '212222', /* 00 */
            '222122', /* 01 */
            '222221', /* 02 */
            '121223', /* 03 */
            '121322', /* 04 */
            '131222', /* 05 */
            '122213', /* 06 */
            '122312', /* 07 */
            '132212', /* 08 */
            '221213', /* 09 */
            '221312', /* 10 */
            '231212', /* 11 */
            '112232', /* 12 */
            '122132', /* 13 */
            '122231', /* 14 */
            '113222', /* 15 */
            '123122', /* 16 */
            '123221', /* 17 */
            '223211', /* 18 */
            '221132', /* 19 */
            '221231', /* 20 */
            '213212', /* 21 */
            '223112', /* 22 */
            '312131', /* 23 */
            '311222', /* 24 */
            '321122', /* 25 */
            '321221', /* 26 */
            '312212', /* 27 */
            '322112', /* 28 */
            '322211', /* 29 */
            '212123', /* 30 */
            '212321', /* 31 */
            '232121', /* 32 */
            '111323', /* 33 */
            '131123', /* 34 */
            '131321', /* 35 */
            '112313', /* 36 */
            '132113', /* 37 */
            '132311', /* 38 */
            '211313', /* 39 */
            '231113', /* 40 */
            '231311', /* 41 */
            '112133', /* 42 */
            '112331', /* 43 */
            '132131', /* 44 */
            '113123', /* 45 */
            '113321', /* 46 */
            '133121', /* 47 */
            '313121', /* 48 */
            '211331', /* 49 */
            '231131', /* 50 */
            '213113', /* 51 */
            '213311', /* 52 */
            '213131', /* 53 */
            '311123', /* 54 */
            '311321', /* 55 */
            '331121', /* 56 */
            '312113', /* 57 */
            '312311', /* 58 */
            '332111', /* 59 */
            '314111', /* 60 */
            '221411', /* 61 */
            '431111', /* 62 */
            '111224', /* 63 */
            '111422', /* 64 */
            '121124', /* 65 */
            '121421', /* 66 */
            '141122', /* 67 */
            '141221', /* 68 */
            '112214', /* 69 */
            '112412', /* 70 */
            '122114', /* 71 */
            '122411', /* 72 */
            '142112', /* 73 */
            '142211', /* 74 */
            '241211', /* 75 */
            '221114', /* 76 */
            '413111', /* 77 */
            '241112', /* 78 */
            '134111', /* 79 */
            '111242', /* 80 */
            '121142', /* 81 */
            '121241', /* 82 */
            '114212', /* 83 */
            '124112', /* 84 */
            '124211', /* 85 */
            '411212', /* 86 */
            '421112', /* 87 */
            '421211', /* 88 */
            '212141', /* 89 */
            '214121', /* 90 */
            '412121', /* 91 */
            '111143', /* 92 */
            '111341', /* 93 */
            '131141', /* 94 */
            '114113', /* 95 */
            '114311', /* 96 */
            '411113', /* 97 */
            '411311', /* 98 */
            '113141', /* 99 */
            '114131', /* 100 */
            '311141', /* 101 */
            '411131', /* 102 */
            '211412', /* 103 START A */
            '211214', /* 104 START B */
            '211232', /* 105 START C */
            '233111', /* STOP */
            '200000'  /* END */
        );
        // ASCII characters for code A (ASCII 00 - 95)
        $keys_a = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_';
        $keys_a .= chr(0) . chr(1) . chr(2) . chr(3) . chr(4) . chr(5) . chr(6) . chr(7) . chr(8) . chr(9);
        $keys_a .= chr(10) . chr(11) . chr(12) . chr(13) . chr(14) . chr(15) . chr(16) . chr(17) . chr(18) . chr(19);
        $keys_a .= chr(20) . chr(21) . chr(22) . chr(23) . chr(24) . chr(25) . chr(26) . chr(27) . chr(28) . chr(29);
        $keys_a .= chr(30) . chr(31);
        // ASCII characters for code B (ASCII 32 - 127)
        $keys_b = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~' . chr(127);
        // special codes
        $fnc_a = array(241 => 102, 242 => 97, 243 => 96, 244 => 101);
        $fnc_b = array(241 => 102, 242 => 97, 243 => 96, 244 => 100);
        // array of symbols
        $code_data = array();
        // length of the code
        $len = strlen($code);
        switch (strtoupper($type)) {
            case 'A': { // MODE A
                $startid = 103;
                for ($i = 0; $i < $len; ++$i) {
                    $char = $code[$i];
                    $char_id = ord($char);
                    if (($char_id >= 241) AND ($char_id <= 244)) {
                        $code_data[] = $fnc_a[$char_id];
                    } elseif (($char_id >= 0) AND ($char_id <= 95)) {
                        $code_data[] = strpos($keys_a, $char);
                    } else {
                        throw new InvalidCharacterException('Char ' . $char . ' is unsupported');
                    }
                }
                break;
            }
            case 'B': { // MODE B
                $startid = 104;
                for ($i = 0; $i < $len; ++$i) {
                    $char = $code[$i];
                    $char_id = ord($char);
                    if (($char_id >= 241) AND ($char_id <= 244)) {
                        $code_data[] = $fnc_b[$char_id];
                    } elseif (($char_id >= 32) AND ($char_id <= 127)) {
                        $code_data[] = strpos($keys_b, $char);
                    } else {
                        throw new InvalidCharacterException('Char ' . $char . ' is unsupported');
                    }
                }
                break;
            }
            case 'C': { // MODE C
                $startid = 105;
                if (ord($code[0]) == 241) {
                    $code_data[] = 102;
                    $code = substr($code, 1);
                    --$len;
                }
                if (($len % 2) != 0) {
                    throw new InvalidLengthException('Length must be even');
                }
                for ($i = 0; $i < $len; $i += 2) {
                    $chrnum = $code[$i] . $code[$i + 1];
                    if (preg_match('/([0-9]{2})/', $chrnum) > 0) {
                        $code_data[] = intval($chrnum);
                    } else {
                        throw new InvalidCharacterException();
                    }
                }
                break;
            }
            default: { // MODE AUTO
                // split code into sequences
                $sequence = array();
                // get numeric sequences (if any)
                $numseq = array();
                preg_match_all('/([0-9]{4,})/', $code, $numseq, PREG_OFFSET_CAPTURE);
                if (isset($numseq[1]) AND ! empty($numseq[1])) {
                    $end_offset = 0;
                    foreach ($numseq[1] as $val) {
                        $offset = $val[1];
                        
                        // numeric sequence
                        $slen = strlen($val[0]);
                        if (($slen % 2) != 0) {
                            // the length must be even
                            ++$offset;
                            $val[0] = substr($val[0],1);
                        }
                        
                        if ($offset > $end_offset) {
                            // non numeric sequence
                            $sequence = array_merge($sequence,
                                $this->get128ABsequence(substr($code, $end_offset, ($offset - $end_offset))));
                        }
                        // numeric sequence fallback
                        $slen = strlen($val[0]);
                        if (($slen % 2) != 0) {
                            // the length must be even
                            --$slen;
                        }
                        $sequence[] = array('C', substr($code, $offset, $slen), $slen);
                        $end_offset = $offset + $slen;
                    }
                    if ($end_offset < $len) {
                        $sequence = array_merge($sequence, $this->get128ABsequence(substr($code, $end_offset)));
                    }
                } else {
                    // text code (non C mode)
                    $sequence = array_merge($sequence, $this->get128ABsequence($code));
                }
                // process the sequence
                foreach ($sequence as $key => $seq) {
                    switch ($seq[0]) {
                        case 'A': {
                            if ($key == 0) {
                                $startid = 103;
                            } elseif ($sequence[($key - 1)][0] != 'A') {
                                if (($seq[2] == 1) AND ($key > 0) AND ($sequence[($key - 1)][0] == 'B') AND ( ! isset($sequence[($key - 1)][3]))) {
                                    // single character shift
                                    $code_data[] = 98;
                                    // mark shift
                                    $sequence[$key][3] = true;
                                } elseif ( ! isset($sequence[($key - 1)][3])) {
                                    $code_data[] = 101;
                                }
                            }
                            for ($i = 0; $i < $seq[2]; ++$i) {
                                $char = $seq[1][$i];
                                $char_id = ord($char);
                                if (($char_id >= 241) AND ($char_id <= 244)) {
                                    $code_data[] = $fnc_a[$char_id];
                                } else {
                                    $code_data[] = strpos($keys_a, $char);
                                }
                            }
                            break;
                        }
                        case 'B': {
                            if ($key == 0) {
                                $tmpchr = ord($seq[1][0]);
                                if (($seq[2] == 1) AND ($tmpchr >= 241) AND ($tmpchr <= 244) AND isset($sequence[($key + 1)]) AND ($sequence[($key + 1)][0] != 'B')) {
                                    switch ($sequence[($key + 1)][0]) {
                                        case 'A': {
                                            $startid = 103;
                                            $sequence[$key][0] = 'A';
                                            $code_data[] = $fnc_a[$tmpchr];
                                            break;
                                        }
                                        case 'C': {
                                            $startid = 105;
                                            $sequence[$key][0] = 'C';
                                            $code_data[] = $fnc_a[$tmpchr];
                                            break;
                                        }
                                    }
                                    break;
                                } else {
                                    $startid = 104;
                                }
                            } elseif ($sequence[($key - 1)][0] != 'B') {
                                if (($seq[2] == 1) AND ($key > 0) AND ($sequence[($key - 1)][0] == 'A') AND ( ! isset($sequence[($key - 1)][3]))) {
                                    // single character shift
                                    $code_data[] = 98;
                                    // mark shift
                                    $sequence[$key][3] = true;
                                } elseif ( ! isset($sequence[($key - 1)][3])) {
                                    $code_data[] = 100;
                                }
                            }
                            for ($i = 0; $i < $seq[2]; ++$i) {
                                $char = $seq[1][$i];
                                $char_id = ord($char);
                                if (($char_id >= 241) AND ($char_id <= 244)) {
                                    $code_data[] = $fnc_b[$char_id];
                                } else {
                                    $code_data[] = strpos($keys_b, $char);
                                }
                            }
                            break;
                        }
                        case 'C': {
                            if ($key == 0) {
                                $startid = 105;
                            } elseif ($sequence[($key - 1)][0] != 'C') {
                                $code_data[] = 99;
                            }
                            for ($i = 0; $i < $seq[2]; $i += 2) {
                                $chrnum = $seq[1][$i] . $seq[1][$i + 1];
                                $code_data[] = intval($chrnum);
                            }
                            break;
                        }
                    }
                }
            }
        }
        // calculate check character
        $sum = $startid;
        foreach ($code_data as $key => $val) {
            $sum += ($val * ($key + 1));
        }
        // add check character
        $code_data[] = ($sum % 103);
        // add stop sequence
        $code_data[] = 106;
        $code_data[] = 107;
        // add start code at the beginning
        array_unshift($code_data, $startid);
        // build barcode array
        $bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());
        foreach ($code_data as $val) {
            $seq = $chr[$val];
            for ($j = 0; $j < 6; ++$j) {
                if (($j % 2) == 0) {
                    $t = true; // bar
                } else {
                    $t = false; // space
                }
                $w = $seq[$j];
                $bararray['bcode'][] = array('t' => $t, 'w' => $w, 'h' => 1, 'p' => 0);
                $bararray['maxw'] += $w;
            }
        }

        return $bararray;
    }

    /**
     * Split text code in A/B sequence for 128 code
     *
     * @param $code (string) code to split.
     * @return array sequence
     * @protected
     */
    protected function get128ABsequence($code)
    {
        $len = strlen($code);
        $sequence = array();
        // get A sequences (if any)
        $numseq = array();
        preg_match_all('/([\x00-\x1f])/', $code, $numseq, PREG_OFFSET_CAPTURE);
        if (isset($numseq[1]) AND ! empty($numseq[1])) {
            $end_offset = 0;
            foreach ($numseq[1] as $val) {
                $offset = $val[1];
                if ($offset > $end_offset) {
                    // B sequence
                    $sequence[] = array(
                        'B',
                        substr($code, $end_offset, ($offset - $end_offset)),
                        ($offset - $end_offset)
                    );
                }
                // A sequence
                $slen = strlen($val[0]);
                $sequence[] = array('A', substr($code, $offset, $slen), $slen);
                $end_offset = $offset + $slen;
            }
            if ($end_offset < $len) {
                $sequence[] = array('B', substr($code, $end_offset), ($len - $end_offset));
            }
        } else {
            // only B sequence
            $sequence[] = array('B', $code, $len);
        }

        return $sequence;
    }

    /**
     * UPC-Based Extensions
     * 2-Digit Ext.: Used to indicate magazines and newspaper issue numbers
     * 5-Digit Ext.: Used to mark suggested retail price of books
     *
     * @param $code (string) code to represent.
     * @param $len (string) barcode type: 2 = 2-Digit, 5 = 5-Digit
     * @return array barcode representation.
     * @protected
     */
    protected function barcode_eanext($code, $len = 5)
    {
        //Padding
        $code = str_pad($code, $len, '0', STR_PAD_LEFT);
        // calculate check digit
        if ($len == 2) {
            $r = $code % 4;
        } elseif ($len == 5) {
            $r = (3 * ($code[0] + $code[2] + $code[4])) + (9 * ($code[1] + $code[3]));
            $r %= 10;
        } else {
            throw new InvalidCheckDigitException();
        }
        //Convert digits to bars
        $codes = array(
            'A' => array( // left odd parity
                '0' => '0001101',
                '1' => '0011001',
                '2' => '0010011',
                '3' => '0111101',
                '4' => '0100011',
                '5' => '0110001',
                '6' => '0101111',
                '7' => '0111011',
                '8' => '0110111',
                '9' => '0001011'
            ),
            'B' => array( // left even parity
                '0' => '0100111',
                '1' => '0110011',
                '2' => '0011011',
                '3' => '0100001',
                '4' => '0011101',
                '5' => '0111001',
                '6' => '0000101',
                '7' => '0010001',
                '8' => '0001001',
                '9' => '0010111'
            )
        );
        $parities = array();
        $parities[2] = array(
            '0' => array('A', 'A'),
            '1' => array('A', 'B'),
            '2' => array('B', 'A'),
            '3' => array('B', 'B')
        );
        $parities[5] = array(
            '0' => array('B', 'B', 'A', 'A', 'A'),
            '1' => array('B', 'A', 'B', 'A', 'A'),
            '2' => array('B', 'A', 'A', 'B', 'A'),
            '3' => array('B', 'A', 'A', 'A', 'B'),
            '4' => array('A', 'B', 'B', 'A', 'A'),
            '5' => array('A', 'A', 'B', 'B', 'A'),
            '6' => array('A', 'A', 'A', 'B', 'B'),
            '7' => array('A', 'B', 'A', 'B', 'A'),
            '8' => array('A', 'B', 'A', 'A', 'B'),
            '9' => array('A', 'A', 'B', 'A', 'B')
        );
        $p = $parities[$len][$r];
        $seq = '1011'; // left guard bar
        $seq .= $codes[$p[0]][$code[0]];
        for ($i = 1; $i < $len; ++$i) {
            $seq .= '01'; // separator
            $seq .= $codes[$p[$i]][$code[$i]];
        }
        $bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 1, 'bcode' => array());

        return BinarySequenceConverter::convert($seq, $bararray);
    }

    /**
     * IMB - Intelligent Mail Barcode - Onecode - USPS-B-3200
     *
     * @param $code (string) pre-formatted IMB barcode (65 chars "FADT")
     * @return array barcode representation.
     * @protected
     */
    protected function barcode_imb_pre($code)
    {
        if ( ! preg_match('/^[fadtFADT]{65}$/', $code) == 1) {
            throw new InvalidFormatException();
        }
        $characters = str_split(strtolower($code), 1);
        // build bars
        $k = 0;
        $bararray = array('code' => $code, 'maxw' => 0, 'maxh' => 3, 'bcode' => array());
        for ($i = 0; $i < 65; ++$i) {
            switch ($characters[$i]) {
                case 'f': {
                    // full bar
                    $p = 0;
                    $h = 3;
                    break;
                }
                case 'a': {
                    // ascender
                    $p = 0;
                    $h = 2;
                    break;
                }
                case 'd': {
                    // descender
                    $p = 1;
                    $h = 2;
                    break;
                }
                case 't': {
                    // tracker (short)
                    $p = 1;
                    $h = 1;
                    break;
                }
            }
            $bararray['bcode'][$k++] = array('t' => 1, 'w' => 1, 'h' => $h, 'p' => $p);
            $bararray['bcode'][$k++] = array('t' => 0, 'w' => 1, 'h' => 2, 'p' => 0);
            $bararray['maxw'] += 2;
        }
        unset($bararray['bcode'][($k - 1)]);
        --$bararray['maxw'];

        return $bararray;
    }
}
