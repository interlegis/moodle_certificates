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
 * Calcs the course start and end date to the user
 * using user enrollment timestart and timeend
 */
require_once("$CFG->dirroot/enrol/locallib.php");
$enrol_manager = new course_enrolment_manager($PAGE, $course);
$user_enrols = $enrol_manager->get_user_enrolments($USER->id);
$start_date = NULL;
$end_date = NULL;
foreach ($user_enrols as $enrol) {
    if ($enrol->timestart > 0) {
        $start_date = $enrol->timestart;
    }
    if ($enrol->timeend > 0) {
	$end_date = $enrol->timeend;
    }
}
if ($start_date > 0 and $end_date > 0) {
    $fmt = '%d/%m/%Y'; // Default format
    if ($certificate->datefmt == 1) {
       $fmt = '%B %d, %Y';
       $certificatedate = userdate($ts, '%B %d, %Y') . " a " . userdate($te, '%B %d, %Y');
    } else if ($certificate->datefmt == 2) {
        $suffix = certificate_get_ordinal_number_suffix(userdate($ts, '%d'));
        $fmt = '%B %d' . $suffix . ', %Y';
        $certificatedate = userdate($ts, '%B %d' . $suffix . ', %Y') . " a " . userdate($te, '%B %d' . $suffix . ', %Y');
    } else if ($certificate->datefmt == 3) {
        $fmt = '%d %B %Y';
        $certificatedate = userdate($ts, '%d %B %Y') . " a " . userdate($te, '%d %B %Y');
    } else if ($certificate->datefmt == 4) {
        $fmt = '%B %Y';
        $certificatedate = userdate($ts, '%B %Y') . " a " . userdate($te, '%B %Y');
    } else if ($certificate->datefmt == 5) {
        $fmt = get_string('strftimedate', 'langconfig');
        $certificatedate = userdate($ts, get_string('strftimedate', 'langconfig')) . " a " . userdate($te, get_string('strftimedate', 'langconfig'));
    }
    $start_date = userdate($start_date, $fmt);
    $end_date = userdate($end_date, $fmt);
} else {
    $start_date = '<falha>';
    $end_date = '<falha>';
}

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
    $sealx = 230;
    $sealy = 150;
    $sigx = 00;
    $sigy = 170;
    $custx = 15;
    $custy = $y+82;
    $wmarkx = 40;
    $wmarky = 31;
    $wmarkw = 212;
    $wmarkh = 148;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 297;
    $brdrh = 210;
    $codey = $y+94;
} else { // Portrait
    $x = 10;
    $y = 40;
    $sealx = 150;
    $sealy = 220;
    $sigx = 10;
    $sigy = 240;
    $custx = 15;
    $custy = $y+82;
    $wmarkx = 26;
    $wmarky = 58;
    $wmarkw = 158;
    $wmarkh = 170;
    $brdrx = 0;
    $brdry = 0;
    $brdrw = 210;
    $brdrh = 297;
    $codey = $y+94;
}

function mystrtoupper($s) {
  $s = strtoupper($s);
  return strtr($s, 'áéíóúàèìòùãẽĩõũâêîôûäëïöüç', 'ÁÉÍÓÚÀÈÌÒÙÃẼĨÕŨÂÊÎÔÛÄËÏÖÜÇ');
}

// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.2);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add text
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x, $y, 'C', 'freesans', '', 20, get_string('title', 'certificate'));
certificate_print_text($pdf, $x, $y + 15, 'C', 'freesans', '', 20, get_string('certify', 'certificate'));
certificate_print_text($pdf, $x, $y + 27, 'C', 'freesans', 'B', 20, mystrtoupper(fullname($USER)).", CPF nº {$USER->profile['cpf']}");
certificate_print_text($pdf, $x, $y + 39, 'C', 'freesans', '', 20, "realizou, no período de {$start_date} a {$end_date}, o curso sem tutoria");
certificate_print_text($pdf, $x, $y + 51, 'C', 'freesans', 'B', 20, mystrtoupper($course->fullname));
if ($certificate->printhours) {
    certificate_print_text($pdf, $x, $y + 63, 'C', 'freesans', '', 20, "com carga horária de {$certificate->printhours} na modalidade a distância.");
}
certificate_print_text($pdf, $custx, $custy, 'L', 'freesans', '', 10, $certificate->customtext);
certificate_print_text($pdf, $x, $y + 82, 'R', 'freesans', 'B', 14,  "Brasília, " . certificate_get_date($certificate, $certrecord, $course));
certificate_print_text($pdf, $x, $codey, 'R', 'freesans', '', 10, 'CÓDIGO DE VALIDAÇÃO: ' . certificate_get_code($certificate, $certrecord));
?>
