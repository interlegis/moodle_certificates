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

$pdf = new PDF($certificate->orientation, 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetTitle($certificate->name);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(false, 0);
$pdf->AddPage();

// Define variables
// This certificate prints only in Landscape format
$x = 10;
$y = 12;
$sealx = 10;
$sealy = 10;
$sigx = 17;
$sigy = 112;
$custx = 10;
$custy = 40;
$wmarkx = 40;
$wmarky = 31;
$wmarkw = 212;
$wmarkh = 148;
$brdrx = 0;
$brdry = 0;
$brdrw = 297;
$brdrh = 210;
$codey = 185;

// Add images and lines to border
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency (watermark)
$pdf->SetAlpha(0.2);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
// Add seal & signature images
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_text($pdf, $sealx-1, $sealy+21, 'L', 'freesans', 'B', 6, 'SENADO FEDERAL');
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add text
$pdf->SetTextColor(0, 0, 128);
certificate_print_text($pdf, $x, $y, 'C', 'freesans', 'B', 30, get_string('title', 'certificate'));
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x, $y + 14, 'C', 'freesans', 'B', 18, 'SECRETARIA ESPECIAL DO INTERLEGIS');
certificate_print_text($pdf, $x, $y + 26, 'C', 'freeserif', 'B', 16, get_string('certify', 'certificate'));
certificate_print_text($pdf, $x, $y + 38, 'C', 'freesans', '', 24, mb_strtoupper(fullname($USER), 'UTF-8'));
$statement = get_string('statement', 'certificate')." '$course->fullname'";
certificate_print_text($pdf, $x, $y + 55, 'C', 'freesans', '', 18, $statement);
certificate_print_text($pdf, $x, $y + 65, 'C', 'freesans', '', 18, 'realizado no período de '.strip_tags($course->summary));
if ($certificate->printhours) {
    certificate_print_text($pdf, $x, $y + 85, 'C', 'freeserif', '', 14, get_string('credithours', 'certificate') . ': ' . $certificate->printhours);
}
certificate_print_text($pdf, $x, $codey, 'L', 'freesans', '', 10, 'Código de validação: '.certificate_get_code($certificate, $certrecord));
certificate_print_text($pdf, $x, $codey + 6, 'L', 'freesans', '', 10, 'Emitido em: ' . certificate_get_date($certificate, $certrecord, $course));

// Reverse page
$pdf->AddPage();

// Add images and lines to border
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency (watermark)
$pdf->SetAlpha(0.2);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
// Add seal image
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_text($pdf, $sealx-1, $sealy+21, 'L', 'freesans', 'B', 6, 'SENADO FEDERAL');

// Add verse signature image - hack: uses 'verse-{imagename}' to get a different image ;)
$type = CERT_IMAGE_SIGNATURE;
$path = "$CFG->dirroot/mod/certificate/pix/$type/verse-$certificate->printsignature";
$uploadpath = "$CFG->dataroot/mod/certificate/pix/$type/verse-$certificate->printsignature";
if (file_exists($path)) {
	$pdf->Image($path, $sigx+170, $sigy+45, '', '');
} elseif (file_exists($uploadpath)) {
	$pdf->Image($uploadpath, $sigx+170, $sigy+45, '', '');
}

// Add text
$pdf->SetTextColor(0, 0, 128);
certificate_print_text($pdf, $x+20, $y+5, 'C', 'freesans', 'B', 24, $course->fullname);
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $custx, $custy, 'L', 'freesans', 'B', 12, 'CONTEÚDO PROGRAMÁTICO');
certificate_print_text($pdf, $custx+10, $custy+10, 'L', 'freesans', '', 12, $certificate->customtext);
?>
