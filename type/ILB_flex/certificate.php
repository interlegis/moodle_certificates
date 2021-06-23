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

$dataInicio = userdate($start_date, $fmt);
$dataFim = userdate($end_date, $fmt);
$cert_date = $end_date; // para fins de obtenção automática de assinatura (COTREN apenas)

$anoInicio = userdate($start_date, '%Y');
$anoFim = userdate($end_date, '%Y');
$mesInicio = userdate($start_date, '%B');
$mesFim = userdate($end_date, '%B');
$diaInicio = userdate($start_date, '%d');
$diaFim = userdate($end_date, '%d');

if($diaInicio == '1') {$diaInicio .= "º";}
if($diaFim == '1') {$diaFim .= "º";}

$verbo_acao = certificate_obtemCampoCustomizadoCurso($course->id, 'papel_acao_capacitacao');
if(empty($verbo_acao)) {
    $verbo_acao = 'participou';
};
$tipo_acao = certificate_obtemCampoCustomizadoCurso($course->id, 'tipo_acao_capacitacao');
if($tipo_acao == "") {
    $tipo_acao = 'do curso';
}
$modalidade_acao = certificate_obtemCampoCustomizadoCurso($course->id, 'modalidade_capacitacao');
$entidade_certificadora = certificate_obtemCampoCustomizadoCurso($course->id, 'entidade_certificadora');
if($entidade_certificadora == '') {
    $entidade_certificadora = 'O Instituto Legislativo Brasileiro certifica que';
}

function montaPeriodo() {
    global $anoInicio, $anoFim, $mesInicio, $mesFim, $diaInicio, $diaFim, $dataInicio, $dataFim;

    if($anoInicio != $anoFim) {
        // ano diferente
        return "realizado no período de {$dataInicio} a {$dataFim}";
    } else {
        if($mesInicio != $mesFim) {
            // mesmo ano, mês diferente
            return "realizado no período de $diaInicio de $mesInicio a $diaFim de $mesFim de $anoInicio";
        } else {
            if($diaInicio != $diaFim) {
                // mesmo mês, dia diferente
                return "realizado no período de $diaInicio a $diaFim de $mesInicio de $anoInicio";
            } else {
                // evento de um dia
                return "realizado em {$dataInicio}";
            }
        }  
    }
}

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
    $sealx = 230;
    $sealy = 150;
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
    $codey = 175;
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
$pdf->SetAlpha(0.2);
certificate_print_image($pdf, $certificate, CERT_IMAGE_WATERMARK, $wmarkx, $wmarky, $wmarkw, $wmarkh);
$pdf->SetAlpha(1);
certificate_print_image($pdf, $certificate, CERT_IMAGE_SEAL, $sealx, $sealy, '', '');
certificate_print_image($pdf, $certificate, CERT_IMAGE_SIGNATURE, $sigx, $sigy, '', '', $cert_date);

// Add text
$pdf->SetTextColor(0, 0, 0);

// $entidade_certificadora = 'O Instituto Legislativo Brasileiro (ILB), do Senado Federal, em parceria com
// as escolas de governo da Câmara dos Deputados (CEFOR) e do Tribunal de Contas da União (ISC), certifica que';
$nome_aluno = mb_strtoupper(fullname($USER), 'UTF-8');
$dados_aluno = "CPF nº $cpf";
$nome_curso = mb_strtoupper($course->fullname, 'UTF-8');
$periodo = montaPeriodo();
$carga_horaria = "com carga horária de {$certificate->printhours}";
$nota = (certificate_get_grade($certificate, $course)?certificate_get_grade($certificate, $course):'');

$texto_base_certificado = $entidade_certificadora . "<br><br>" . 
    "<b>" . $nome_aluno . "</b><br><br>" .
    $dados_aluno . ", " . $verbo_acao . ($modalidade_acao? ", na modalidade " . $modalidade_acao . ',':"") . ' ' . $tipo_acao  . ' ' . 
    "<i>" . $nome_curso . "</i>" . 
    ($certificate->printhours?", com carga horária de $certificate->printhours":'') .
    ', ' . $periodo . 
    ($nota?', ' . $nota:'') . '.';

certificate_print_text($pdf, $x, $y, 'C', 'freesans', '', 20, get_string('title', 'certificate'));
certificate_print_text($pdf, $x, $y + 15, 'C', 'freesans', '', 17, $texto_base_certificado);

// Deve ser fixo
certificate_print_text($pdf, $x, $y + 85, 'R', 'freesans', 'B', 14,  "Brasília, {$dataFim}.");


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
certificate_print_text($pdf, $x, $y + 10, 'C', 'freesans', '', 20, $nome_curso);
certificate_print_text($pdf, $custx, $custy, 'L', 'freesans', '', 10, $certificate->customtext);
certificate_print_text($pdf, $codex, $codey, 'C', 'freesans', '', 10, 'CÓDIGO DE VALIDAÇÃO');
certificate_print_text($pdf, $codex, $codey + 5, 'C', 'freesans', 'B', 12, certificate_get_code($certificate, $certrecord));
certificate_print_text($pdf, $codex, $codey + 10, 'C', 'freesans', '', 10, 'Para verificar a autenticidade deste certificado, acesse http://saberes.senado.leg.br/ e informe o código acima');

?>
