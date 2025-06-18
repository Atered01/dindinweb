<?php
// Em Dindinweb/PHP/gerar_certificado.php

require_once('config.php'); // O config.php já inicia a sessão

// Inclui as bibliotecas FPDF e FPDI
require_once('../lib/fpdf/fpdf.php');
require_once('../lib/fpdi/src/autoload.php');

use setasign\Fpdi\Fpdi;

// Guarda de segurança
if (!isset($_SESSION['usuario_id'])) {
    die("Acesso negado. Você precisa estar logado para gerar um certificado.");
}

// Pega o tipo de certificado da URL (ex: ?tipo=bronze)
$tipo_certificado = $_GET['tipo'] ?? '';
$nome_usuario = $_SESSION['usuario_nome'] ?? 'Nome do Usuário';

// Define o caminho para o arquivo PDF do template
$caminho_template = '';
switch ($tipo_certificado) {
    case 'bronze':
        // Certifique-se de que seus PDFs estão nesta pasta
        $caminho_template = '../certificados/CertificadoBronze.pdf';
        break;
    case 'prata':
        $caminho_template = '../certificados/CertificadoPrata.pdf';
        break;
    case 'ouro':
        $caminho_template = '../certificados/CertificadoOuro.pdf';
        break;
    default:
        die("Tipo de certificado inválido.");
}

if (!file_exists($caminho_template)) {
    die("Arquivo do certificado modelo não encontrado no caminho: " . $caminho_template);
}

// Inicia o FPDI
$pdf = new Fpdi();
$pdf->AddPage();
$pdf->setSourceFile($caminho_template);
$templateId = $pdf->importPage(1);
$pdf->useTemplate($templateId, ['adjustPageSize' => true]);

$pdf->SetFont('Helvetica', 'B', 20);
$pdf->SetTextColor(80, 80, 80);
// Ajuste os valores de X e Y para alinhar o nome no seu PDF
$posicao_X = 77;
$posicao_Y = 82.5; 
$pdf->SetXY($posicao_X, $posicao_Y);

$pdf->Cell(0, 10, utf8_decode($nome_usuario), 0, 0, 'C');

// Força o download do novo PDF
$pdf->Output('I', 'Certificado_DinDinVerde_' . $tipo_certificado . '.pdf');
?>