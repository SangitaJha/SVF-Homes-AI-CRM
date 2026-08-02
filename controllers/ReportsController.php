<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class ReportsController extends Controller
{
    public function index(): void
    {
        require_auth();
        $db = Database::connection();
        $reports = [
            'daily' => $db->query('SELECT COUNT(*) AS total FROM leads WHERE DATE(created_at) = CURDATE()')->fetch()['total'] ?? 0,
            'weekly' => $db->query('SELECT COUNT(*) AS total FROM leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)')->fetch()['total'] ?? 0,
            'monthly' => $db->query('SELECT COUNT(*) AS total FROM leads WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)')->fetch()['total'] ?? 0,
            'labour_present' => (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Present'")->fetchColumn(),
            'labour_cost' => (float)$db->query("SELECT COALESCE(SUM(daily_wage), 0) FROM labour_attendance WHERE date = CURDATE() AND attendance_status IN ('Present', 'Half Day')")->fetchColumn(),
            'work_completed' => (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE date = CURDATE() AND status = 'Completed'")->fetchColumn(),
            'work_delayed' => (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE date = CURDATE() AND status = 'Delayed'")->fetchColumn(),
        ];
        $this->render('reports/index', ['reports' => $reports]);
    }

    public function exportPdf(): void
    {
        require_auth();
        $html = '<h1>SVF Homes Report</h1><p>Export generated at ' . date('Y-m-d H:i:s') . '</p>';

        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('svf-homes-report.pdf', ['Attachment' => true]);
            return;
        }

        header('Content-Type: text/html');
        echo $html;
    }

    public function exportExcel(): void
    {
        require_auth();
        if (class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Report');
            $sheet->setCellValue('B1', date('Y-m-d H:i:s'));

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="svf-homes-report.xlsx"');
            $writer->save('php://output');
            return;
        }

        header('Content-Type: text/plain');
        echo "Spreadsheet export requires phpoffice/phpspreadsheet";
    }
}
