<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

function exportCSV($rows) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=documents.csv');
    $out = fopen('php://output', 'w');

    if (!empty($rows)) {
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
    }
    fclose($out);
    exit;
}

function exportExcel($rows) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    if (empty($rows)) {
        $sheet->setCellValue('A1', 'No records found');
    } else {
        // Add "preview" column after filename
        $headers = array_keys($rows[0]);
        $headersWithPreview = [];
        foreach ($headers as $h) {
            $headersWithPreview[] = $h;
            if ($h === 'filename') {
                $headersWithPreview[] = 'preview';
            }
        }

        // Write headers
        foreach ($headersWithPreview as $colIndex => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cell, ucfirst($header));

            // Auto-size text columns
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Fill rows
        $rowIndex = 2;
        foreach ($rows as $row) {
            $colIndex = 1;
            foreach ($headersWithPreview as $header) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex;

                if ($header === 'preview') {
                    if (!empty($row['filename']) && file_exists('uploads/' . $row['filename'])) {
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setPath('uploads/' . $row['filename']);
                        $drawing->setHeight(60); // thumbnail size
                        $drawing->setCoordinates($cell);
                        $drawing->setWorksheet($sheet);

                        // Adjust row height for image
                        $sheet->getRowDimension($rowIndex)->setRowHeight(60);

                        // Make preview column wider
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                        $sheet->getColumnDimension($colLetter)->setWidth(20);
                    }
                } else {
                    $sheet->setCellValue($cell, $row[$header]);
                }
                $colIndex++;
            }
            $rowIndex++;
        }
    }

    // Output as Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="documents.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}