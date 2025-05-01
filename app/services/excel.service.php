<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function generate_excel_template() {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // En-têtes des colonnes
    $headers = [
        'A' => 'prenom',
        'B' => 'nom',
        'C' => 'email',
        'D' => 'telephone',
        'E' => 'adresse',
        'F' => 'date_naissance',
        'G' => 'lieu_naissance',
        'H' => 'referentiel_id'
    ];
    
    // Style pour les en-têtes
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FF7900'],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
    ];
    
    // Ajouter les en-têtes
    foreach ($headers as $column => $header) {
        $sheet->setCellValue($column . '1', ucfirst($header));
        $sheet->getStyle($column . '1')->applyFromArray($headerStyle);
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
    
    // Exemple de ligne
    $exampleData = [
        'Jean',
        'Doe',
        'jean.doe@example.com',
        '771234567',
        'Dakar',
        '1990-01-01',
        'Dakar',
        '1'
    ];
    
    // Ajouter l'exemple
    $row = 2;
    foreach ($exampleData as $col => $value) {
        $sheet->setCellValueByColumnAndRow($col + 1, $row, $value);
    }
    
    // Style pour l'exemple
    $sheet->getStyle('A2:H2')->getFont()->setItalic(true);
    $sheet->getStyle('A2:H2')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(['rgb' => '808080']));
    
    return $spreadsheet;
}