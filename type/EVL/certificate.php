<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A4_embedded certificate type
 *
 * @package    mod
 * @subpackage certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from view.php
}

/**
 * Gets the course start date (for ILB start date is the date of enrollment)
 * and completion date from course completion framework.
 * Finally format them to print
 */
require_once("$CFG->dirroot/completion/completion_completion.php");

$start_date = $course->startdate;
$end_date   = $course->enddate;
$emissao_date   = $course->enddate;
/*
// Campos customizados do curso
$cargahoraria = obtemCampoCustomizadoCurso($course->id, 'cargahoraria');
$instrutor = obtemCampoCustomizadoCurso($course->id, 'instrutor');
$monitor = obtemCampoCustomizadoCurso($course->id, 'monitor');
$municipio = obtemCampoCustomizadoCurso($course->id, 'municipio');
$senador = obtemCampoCustomizadoCurso($course->id, 'senador');
$tipooficina = obtemCampoCustomizadoCurso($course->id, 'tipooficina');
$cargahoraria = obtemCampoCustomizadoCurso($course->id, 'cargahoraria');
*/
$fmt = '%d/%m/%Y'; // Default format
if ($certificate->datefmt == 1) {
   $fmt = '%B %d, %Y';
} else if ($certificate->datefmt == 2) {
    $suffix = certificate_get_ordinal_number_suffix(userdate($ts, '%d'));
    $fmt = '%B %d' . $suffix . ', %Y';
} else if ($certificate->datefmt == 3) {
    $fmt = '%d de %B de %Y';
} else if ($certificate->datefmt == 4) {
    $fmt = '%B de %Y';
} else if ($certificate->datefmt == 5) {
    $fmt = get_string('strftimedate', 'langconfig');
}

$start_date = userdate($start_date, $fmt);
$end_date = userdate($end_date, $fmt);
$emissao_date = userdate($emissao_date, $fmt);

//MASK para CPF
function mask($val, $mask)
{
    $maskared = '';
    $k = 0;
    for($i = 0; $i<=strlen($mask)-1; $i++){
    
        if($mask[$i] == '#'){
            if(isset($val[$k]))
            $maskared .= $val[$k++];
            }
        else
        {
            if(isset($mask[$i]))
            $maskared .= $mask[$i];
        }
    }
return $maskared;
}

$cpf = mask($USER->username, '###.###.###-##');

require_once($CFG->dirroot.'/user/profile/field/cpf/field.class.php');

$pdf = new PDF($certificate->orientation, 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetTitle($certificate->name);
$pdf->SetProtection(array('modify'));
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();


// Define variables
// Landscape
if ($certificate->orientation == 'L') {
    $x = 20;
    $y = 60;
    $sealx = 135;
    $sealy = 20;
    $sigx = 00;
    $sigy = 165;
    $custx = 15;
    $custy = $y+25;
    $wmarkx = 40;
    $wmarky = 31;
    $wmarkw = 212;
    $wmarkh = 148;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 297;
    $brdrh = 210;
    $codex = $x;
    $codey = 165;
} else { // Portrait
    $x = 10;
    $y = 90;
    $sealx = 150;
    $sealy = 220;
    $sigx = 10;
    $sigy = 235;
    $custx = 15;
    $custy = $y+25;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codex = $x;
    $codey = 245;
}

// Front page ------------------------------------------------------------------------------------------------------------
// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');


$certificador = 'A Câmara Municipal de Três Pontas - MG certifica que';
$municipio = 'Três Pontas';
$cargahoraria = '40';

// Add text
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x, $y, 'C', 'freesans', '', 20, get_string('title', 'certificate'));
//certificate_print_text($pdf, $x, $y + 15, 'C', 'freesans', '', 18, get_string('certify', 'certificate'));
certificate_print_text($pdf, $x, $y + 15, 'C', 'freesans', '', 18, $certificador);
certificate_print_text($pdf, $x, $y + 25, 'C', 'freesans', 'B', 18, mb_strtoupper(fullname($USER), 'UTF-8').", CPF nº $cpf");
certificate_print_text($pdf, $x, $y + 35, 'C', 'freesans', '', 18, "realizou o curso " . mb_strtoupper($course->fullname, 'UTF-8'))  ;
certificate_print_text($pdf, $x, $y + 45, 'C', 'freesans', '', 18, "na Câmara Municipal de $municipio");
certificate_print_text($pdf, $x, $y + 55, 'C', 'freesans', '', 18, "no período de {$start_date} a {$end_date}");
certificate_print_text($pdf, $x, $y + 65, 'C', 'freesans', '', 18, "com carga horária de $cargahoraria horas");
certificate_print_text($pdf, $x, $y + 75, 'C', 'freesans', '', 18, certificate_get_grade($certificate, $course));
certificate_print_text($pdf, $x, $y + 85, 'R', 'freesans', 'B', 14,  "$municipio, {$emissao_date}.");


$code = certificate_get_code($certificate, $certrecord);

// Verse page -----------------------------------------------------------------------------------------------------------
$pdf->AddPage();
// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.2);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');

// Add text
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x, $y, 'C', 'freesans', '', 20, 'PROGRAMA DO CURSO');
certificate_print_text($pdf, $x, $y + 10, 'C', 'freesans', '', 20, mb_strtoupper($course->fullname, 'UTF-8'));
certificate_print_text($pdf, $custx, $custy, 'L', 'freesans', '', 10, $certificate->customtext);
certificate_print_text($pdf, $codex, $codey, 'C', 'freesans', '', 10, 'CÓDIGO DE VALIDAÇÃO');
certificate_print_text($pdf, $codex, $codey + 5, 'C', 'freesans', 'B', 12, $code);
certificate_print_text($pdf, $codex, $codey + 10, 'C', 'freesans', '', 10, 'Para verificar a autenticidade deste certificado, acesse https://evl.interlegis.leg.br/ e informe o código acima');
certificate_print_text($pdf, $codex, $codey + 15, 'C', 'freesans', '', 10, 'ou utilize um leitor de QrCode para ler o código ao lado');
    
certificate_print_qrcode($pdf, $code, 260, $codey - 5);






