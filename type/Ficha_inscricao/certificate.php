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
$x = 10;
$y = 25;
$sealx = 10;
$sealy = 15;
$sigx = 140;
$sigy = 276;
$custx = 10;
$custy = 250;
$wmarkx = 26;
$wmarky = 58;
$wmarkw = 158;
$wmarkh = 170;
$brdrx = 0;
$brdry = 0;
$brdrw = 210;
$brdrh = 297;
$codey = 250;

// Formated variables for printing
$data_nascimento = userdate($USER->profile['datanascimento'], get_string('strftimedate', 'langconfig'));
$data_admissao = userdate($USER->profile['dataadmissao'], get_string('strftimedate', 'langconfig'));
$hoje = userdate(time(), get_string('strftimedate', 'langconfig'));

// Add images and lines
certificate_print_image($pdf, $certificate, CERT_IMAGE_BORDER, $brdrx, $brdry, $brdrw, $brdrh);
certificate_draw_frame($pdf, $certificate);
// Set alpha to semi-transparency
$pdf->SetAlpha(0.2);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '');

// Add boxes
$pdf->SetLineStyle(array('width' => 0.6, 'color' => array(0, 0, 0)));
$pdf->Rect($x, $y+44, $brdrw-20, 7);
$pdf->Rect($x, $y+113, $brdrw-20, 7);
$pdf->Rect($x, $y+161, $brdrw-20, 7);

// Add text
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x+25, $y-5, 'L', 'freesans', '', 14, 'SENADO FEDERAL');
certificate_print_text($pdf, $x+25, $y+2, 'L', 'freesans', '', 12, 'Instituto Legislativo Brasileiro - ILB');
certificate_print_text($pdf, $x, $y+16, 'C', 'freesans', 'B', 14, $certificate->name);
certificate_print_text($pdf, $x, $y+30, 'C', 'freesans', 'B', 12, $course->fullname);

certificate_print_text($pdf, $x, $y+45, 'C', 'freesans', 'B', 10, 'DADOS PESSOAIS');
certificate_print_text($pdf, $x, $y+55, 'L', 'freesans', '', 10, 'NOME: '.fullname($USER));
certificate_print_text($pdf, $x, $y+62, 'L', 'freesans', '', 10, "RG: {$USER->profile['RG']}");
certificate_print_text($pdf, $x+60, $y+62, 'L', 'freesans', '',10,"ÓRGÃO EMISSOR: {$USER->profile['ORGAOEMISSOR']}");
certificate_print_text($pdf, $x+130, $y+62, 'L', 'freesans', '', 10, "CPF: {$USER->profile['cpf']}");
certificate_print_text($pdf, $x, $y+69, 'L', 'freesans', '', 10, "DATA DE NASCIMENTO: $data_nascimento");
certificate_print_text($pdf, $x+130, $y+69, 'L', 'freesans', '', 10, "SEXO: {$USER->profile['Sexo']}");
certificate_print_text($pdf, $x, $y+76, 'L', 'freesans', '', 10, "NACIONALIDADE: {$USER->profile['nacionalidade']}");
certificate_print_text($pdf, $x+60, $y+76, 'L', 'freesans', '', 10, "NATURALIDADE: {$USER->profile['naturalidade']}");
certificate_print_text($pdf, $x, $y+83, 'L', 'freesans', '', 10, "RESIDÊNCIA: {$USER->profile['endereco']}");
certificate_print_text($pdf, $x, $y+90, 'L', 'freesans', '', 10, "BAIRRO: {$USER->profile['bairro']}");
certificate_print_text($pdf, $x+60, $y+90, 'L', 'freesans', '', 10, "CIDADE: {$USER->city}");
certificate_print_text($pdf, $x+110, $y+90, 'R', 'freesans', '', 10, "UF: {$USER->profile['estado']} CEP: {$USER->profile['cep']}");
certificate_print_text($pdf, $x, $y+97, 'L', 'freesans', '', 10, "TEL.RESIDENCIAL: {$USER->profile['telefone']}");
certificate_print_text($pdf, $x+110, $y+97, 'L', 'freesans', '', 10, "CELULAR: {$USER->profile['celular']}");
certificate_print_text($pdf, $x, $y+104, 'L', 'freesans', '', 10, "E-MAIL: {$USER->email}");

certificate_print_text($pdf, $x, $y+114, 'C', 'freesans', 'B', 10, 'DADOS PROFISSIONAIS');
certificate_print_text($pdf, $x, $y+124, 'L', 'freesans', '', 10, "ÓRGÃO: {$USER->profile['orgao']}");
certificate_print_text($pdf, $x, $y+131, 'L', 'freesans', '', 10, "LOTAÇÃO: {$USER->profile['lotacao']}");
certificate_print_text($pdf, $x, $y+138, 'L', 'freesans', '', 10, "CARGO: {$USER->profile['cargo']}");
certificate_print_text($pdf, $x+100, $y+138, 'L', 'freesans', '', 10, "FUNÇÃO: {$USER->profile['funcao']}");
certificate_print_text($pdf, $x, $y+145, 'L', 'freesans', '', 10, "DATA DE ADMISSÃO: $data_admissao");
certificate_print_text($pdf, $x+100, $y+145, 'L', 'freesans', '', 10, "MATRÍCULA: {$USER->profile['matricula']}");
certificate_print_text($pdf, $x, $y+152, 'L', 'freesans', '', 10, "TELEFONE: {$USER->profile['telefonetrabalho']}");

certificate_print_text($pdf, $x, $y+162, 'C', 'freesans', 'B', 10, 'FORMAÇÃO ACADÊMICA');
certificate_print_text($pdf, $x, $y+169, 'L', 'freesans', 'BU', 10, "GRADUAÇÃO:");
certificate_print_text($pdf, $x+27, $y+169, 'L', 'freesans', 'I', 10, "(informe o curso mais recente)");
certificate_print_text($pdf, $x+10, $y+176, 'L', 'freesans', '', 10, "CURSO: {$USER->profile['graduacaocurso']}");
certificate_print_text($pdf, $x+10, $y+183, 'L', 'freesans', '', 10, "INSTITUIÇÃO: {$USER->profile['graduacaoinstituicao']}");
certificate_print_text($pdf, $x+10, $y+190, 'L', 'freesans', '', 10, "ANO DE CONCLUSÃO: {$USER->profile['graduacaoano']}");
certificate_print_text($pdf, $x, $y+197, 'L', 'freesans', 'BU', 10, "PÓS-GRADUAÇÃO:");
certificate_print_text($pdf, $x+36, $y+197, 'L', 'freesans', 'I', 10, "(informe o curso de maior grau)");
certificate_print_text($pdf, $x+10, $y+204, 'L', 'freesans', '', 10, "CURSO: {$USER->profile['poscurso']}");
certificate_print_text($pdf, $x+10, $y+211, 'L', 'freesans', '', 10, "INSTITUIÇÃO: {$USER->profile['posinstituicao']}");
certificate_print_text($pdf, $x+10, $y+218, 'L', 'freesans', '', 10, "ANO DE CONCLUSÃO: {$USER->profile['posano']}");

certificate_print_text($pdf, $custx, $custy, 'L', null, null, null, $certificate->customtext);

certificate_print_text($pdf, $x+10, $sigy-13, 'R', 'freesans', '', 10, "Brasília, $hoje");







/*
$pdf->SetTextColor(0, 0, 0);
certificate_print_text($pdf, $x, $y + 20, 'C', 'freeserif', '', 20, get_string('certify', 'certificate'));
certificate_print_text($pdf, $x, $y + 36, 'C', 'freesans', '', 30, fullname($USER));
certificate_print_text($pdf, $x, $y + 55, 'C', 'freesans', '', 20, get_string('statement', 'certificate'));
certificate_print_text($pdf, $x, $y + 72, 'C', 'freesans', '', 20, $course->fullname);
certificate_print_text($pdf, $x, $y + 92, 'C', 'freesans', '', 14,  certificate_get_date($certificate, $certrecord, $course));
certificate_print_text($pdf, $x, $y + 102, 'C', 'freeserif', '', 10, certificate_get_grade($certificate, $course));
certificate_print_text($pdf, $x, $y + 112, 'C', 'freeserif', '', 10, certificate_get_outcome($certificate, $course));
if ($certificate->printhours) {
    certificate_print_text($pdf, $x, $y + 122, 'C', 'freeserif', '', 10, get_string('credithours', 'certificate') . ': ' . $certificate->printhours);
}
certificate_print_text($pdf, $x, $codey, 'C', 'freeserif', '', 10, certificate_get_code($certificate, $certrecord));
$i = 0;
if ($certificate->printteacher) {
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    if ($teachers = get_users_by_capability($context, 'mod/certificate:printteacher', '', $sort = 'u.lastname ASC', '', '', '', '', false)) {
        foreach ($teachers as $teacher) {
            $i++;
            certificate_print_text($pdf, $sigx, $sigy + ($i * 4), 'L', 'freeserif', '', 12, fullname($teacher));
        }
    }
}

certificate_print_text($pdf, $custx, $custy, 'L', null, null, null, $certificate->customtext);*/
?>
