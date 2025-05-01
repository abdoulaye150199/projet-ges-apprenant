<?php
namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Import des données depuis un fichier Excel
 */
function import_excel_data($file) {
    try {
        // Vérifier l'extension du fichier
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            throw new \Exception('Format de fichier non supporté. Utilisez .xlsx ou .xls');
        }

        $reader = IOFactory::createReader($ext === 'xlsx' ? 'Xlsx' : 'Xls');
        $reader->setReadDataOnly(true);

        // Vérifier si le fichier peut être lu
        if (!$reader->canRead($file['tmp_name'])) {
            throw new \Exception('Le fichier ne peut pas être lu comme un fichier Excel');
        }

        $spreadsheet = $reader->load($file['tmp_name']);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();

        // Vérifier si le fichier n'est pas vide
        if (empty($data)) {
            throw new \Exception('Le fichier est vide');
        }

        return $data;

    } catch (\Exception $e) {
        error_log('Erreur lors de la lecture du fichier Excel: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Génération du template Excel pour l'import
 */
function generate_excel_template() {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // En-têtes
    $headers = [
        'A' => 'Prénom',
        'B' => 'Nom',
        'C' => 'Email',
        'D' => 'Téléphone',
        'E' => 'Adresse',
        'F' => 'Date de naissance',
        'G' => 'Lieu de naissance',
        'H' => 'Référentiel'
    ];
    
    // Style des en-têtes
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'FF7900']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER
        ]
    ];

    // Exemple de données
    $example = [
        'Jean',
        'Doe',
        'jean.doe@example.com',
        '771234567',
        'Dakar',
        '1990-01-01',
        'Dakar',
        'Dev Web/Mobile'
    ];

    // Ajouter les en-têtes et l'exemple
    foreach ($headers as $col => $header) {
        // En-tête
        $sheet->setCellValue($col . '1', $header);
        $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
        $sheet->getColumnDimension($col)->setAutoSize(true);
        
        // Exemple
        $index = array_search($col, array_keys($headers));
        if (isset($example[$index])) {
            $sheet->setCellValue($col . '2', $example[$index]);
            $sheet->getStyle($col . '2')->getFont()->setItalic(true);
            $sheet->getStyle($col . '2')->getFont()->setColor(new Color(['rgb' => '808080']));
        }
    }

    // Ajouter des notes explicatives
    $sheet->setCellValue('A4', 'Notes :');
    $sheet->setCellValue('A5', '- Tous les champs sont optionnels, mais plus ils sont remplis, plus vite l\'apprenant sera validé');
    $sheet->setCellValue('A6', '- Le format de téléphone doit commencer par 77, 78, 75, 70 ou 76');
    $sheet->setCellValue('A7', '- La date de naissance doit être au format AAAA-MM-JJ');
    $sheet->setCellValue('A8', '- Le référentiel doit correspondre à un référentiel existant');

    // Fusionner les cellules pour les notes
    foreach (range(5, 8) as $row) {
        $sheet->mergeCells('A'.$row.':H'.$row);
    }

    return $spreadsheet;
}