<?php
namespace App\Services\Export;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use TCPDF;

function get_referentiel_name($apprenant) {
    global $model;
    $referentiel = $model['get_referentiel_by_id']($apprenant['referentiel_id']);
    return $referentiel ? $referentiel['name'] : 'Non défini';
}

function generate_pdf($apprenants) {
    // Assurez-vous qu'aucune sortie n'a été envoyée avant
    ob_clean();
    
    require_once __DIR__ . '/../../../vendor/autoload.php';
    
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
    
    // Configuration du PDF
    $pdf->SetCreator('Gestion Apprenants');
    $pdf->SetTitle('Liste des apprenants');
    $pdf->SetHeaderData('', 0, 'Liste des apprenants', '');
    
    // En-têtes des colonnes
    $header = array('Matricule', 'Nom Complet', 'Email', 'Téléphone', 'Référentiel', 'Statut');
    
    $pdf->AddPage();
    
    // Contenu du tableau
    foreach($apprenants as $apprenant) {
        // Gérer le cas où referentiel_id n'existe pas
        $referentiel = isset($apprenant['referentiel_id']) ? $apprenant['referentiel_id'] : 'Non défini';
        
        $pdf->Cell(30, 10, $apprenant['matricule'], 1);
        $pdf->Cell(50, 10, $apprenant['prenom'] . ' ' . $apprenant['nom'], 1);
        $pdf->Cell(50, 10, $apprenant['email'], 1);
        $pdf->Cell(30, 10, $apprenant['telephone'], 1);
        $pdf->Cell(30, 10, $referentiel, 1);
        $pdf->Cell(30, 10, $apprenant['status'], 1);
        $pdf->Ln();
    }
    
    // Envoi du PDF
    $pdf->Output('liste_apprenants.pdf', 'D');
    exit();
}

function generate_excel($apprenants) {
    // Assurez-vous qu'aucune sortie n'a été envoyée avant
    ob_clean();
    
    require_once __DIR__ . '/../../../vendor/autoload.php';
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // En-têtes
    $sheet->setCellValue('A1', 'Matricule');
    $sheet->setCellValue('B1', 'Nom Complet');
    $sheet->setCellValue('C1', 'Email');
    $sheet->setCellValue('D1', 'Téléphone');
    $sheet->setCellValue('E1', 'Référentiel');
    $sheet->setCellValue('F1', 'Statut');
    
    $row = 2;
    foreach($apprenants as $apprenant) {
        // Gérer le cas où referentiel_id n'existe pas
        $referentiel = isset($apprenant['referentiel_id']) ? $apprenant['referentiel_id'] : 'Non défini';
        
        $sheet->setCellValue('A' . $row, $apprenant['matricule']);
        $sheet->setCellValue('B' . $row, $apprenant['prenom'] . ' ' . $apprenant['nom']);
        $sheet->setCellValue('C' . $row, $apprenant['email']);
        $sheet->setCellValue('D' . $row, $apprenant['telephone']);
        $sheet->setCellValue('E' . $row, $referentiel);
        $sheet->setCellValue('F' . $row, $apprenant['status']);
        $row++;
    }
    
    // Configuration de l'en-tête HTTP
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="liste_apprenants.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit();
}