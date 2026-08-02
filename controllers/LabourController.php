<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use PDO;

final class LabourController extends Controller
{
    private const ROLES = ['Super Admin', 'Admin', 'HR', 'Project Manager', 'Site Engineer', 'Supervisor'];

    public function dashboard(): void
    {
        $this->authorize();
        $db = Database::connection();
        $today = date('Y-m-d');
        $metrics = [
            'total' => (int)$db->query("SELECT COUNT(*) FROM labours WHERE status = 'Active'")->fetchColumn(),
            'present' => (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Present'")->fetchColumn(),
            'absent' => (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Absent'")->fetchColumn(),
            'half_day' => (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Half Day'")->fetchColumn(),
            'cost' => (float)$db->query("SELECT COALESCE(SUM(daily_wage), 0) FROM labour_attendance WHERE date = CURDATE() AND attendance_status IN ('Present', 'Half Day')")->fetchColumn(),
            'completed' => (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE date = CURDATE() AND status = 'Completed'")->fetchColumn(),
            'delayed' => (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE date = CURDATE() AND status = 'Delayed'")->fetchColumn(),
            'progress' => (float)$db->query("SELECT COALESCE(AVG(completion_percentage), 0) FROM daily_work_completed WHERE date = CURDATE()")->fetchColumn(),
        ];
        $attendanceTrend = $db->query("SELECT date, SUM(attendance_status = 'Present') AS present, SUM(attendance_status = 'Absent') AS absent FROM labour_attendance WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY date ORDER BY date")->fetchAll();
        $progressTrend = $db->query("SELECT date, COALESCE(AVG(completion_percentage), 0) AS progress FROM daily_work_completed WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY date ORDER BY date")->fetchAll();
        $recentWork = $db->query("SELECT w.*, p.name AS project_name, c.name AS contractor_name FROM daily_work_completed w LEFT JOIN projects p ON p.id = w.project_id LEFT JOIN contractors c ON c.id = w.contractor_id ORDER BY w.date DESC, w.id DESC LIMIT 8")->fetchAll();
        $this->render('labour/dashboard', compact('metrics', 'attendanceTrend', 'progressTrend', 'recentWork', 'today'));
    }

    public function attendance(): void
    {
        $this->authorize();
        $db = Database::connection();
        $filters = $this->filters();
        [$where, $params] = $this->attendanceWhere($filters);
        $statement = $db->prepare("SELECT a.*, l.name AS labour_name, l.labour_id AS labour_code, p.name AS project_name, c.name AS contractor_name FROM labour_attendance a JOIN labours l ON l.id = a.labour_id LEFT JOIN projects p ON p.id = a.project_id LEFT JOIN contractors c ON c.id = a.contractor_id $where ORDER BY a.date DESC, a.id DESC LIMIT 300");
        $statement->execute($params);
        $records = $statement->fetchAll();
        $labours = $db->query("SELECT id, labour_id, name, trade, daily_wage, contractor_id FROM labours WHERE status = 'Active' ORDER BY name")->fetchAll();
        $projects = $db->query('SELECT id, name FROM projects ORDER BY name')->fetchAll();
        $contractors = $db->query("SELECT id, name FROM contractors WHERE status = 'Active' ORDER BY name")->fetchAll();
        $this->render('labour/attendance', compact('records', 'labours', 'projects', 'contractors', 'filters'));
    }

    public function storeAttendance(): void
    {
        $this->authorize();
        verify_csrf();
        $db = Database::connection();
        $labourId = (int)($_POST['labour_id'] ?? 0);
        $date = trim((string)($_POST['date'] ?? date('Y-m-d')));
        $status = trim((string)($_POST['attendance_status'] ?? 'Present'));
        $allowed = ['Present', 'Absent', 'Half Day', 'Leave'];
        if ($labourId < 1 || !in_array($status, $allowed, true) || !$this->validDate($date)) {
            flash('error', 'Labour, date, and attendance status are required.');
            redirect('/labour/attendance');
        }
        $labour = $db->prepare('SELECT * FROM labours WHERE id = :id LIMIT 1');
        $labour->execute(['id' => $labourId]);
        $labour = $labour->fetch();
        if (!$labour) {
            flash('error', 'Selected labour was not found.');
            redirect('/labour/attendance');
        }
        $hours = $this->hours((string)($_POST['check_in'] ?? ''), (string)($_POST['check_out'] ?? ''));
        $workingHours = $status === 'Present' ? $hours : ($status === 'Half Day' ? min($hours, 4) : 0);
        $overtime = max(0, $workingHours - 8);
        $input = [
            'attendance_id' => 'ATT-' . date('YmdHis') . '-' . random_int(100, 999),
            'labour_id' => $labourId,
            'date' => $date,
            'project_id' => $this->nullableInt($_POST['project_id'] ?? null),
            'site' => $this->nullableString($_POST['site'] ?? null),
            'contractor_id' => $this->nullableInt($_POST['contractor_id'] ?? ($labour['contractor_id'] ?? null)),
            'trade' => $this->nullableString($_POST['trade'] ?? $labour['trade']),
            'check_in' => $this->nullableString($_POST['check_in'] ?? null),
            'check_out' => $this->nullableString($_POST['check_out'] ?? null),
            'working_hours' => $workingHours,
            'overtime_hours' => $overtime,
            'attendance_status' => $status,
            'daily_wage' => (float)($labour['daily_wage'] ?? 0),
            'remarks' => $this->nullableString($_POST['remarks'] ?? null),
        ];
        $columns = array_keys($input);
        $placeholders = array_map(static fn(string $column): string => ':' . $column, $columns);
        $statement = $db->prepare('INSERT INTO labour_attendance (`' . implode('`,`', $columns) . '`, created_at, updated_at) VALUES (' . implode(',', $placeholders) . ', NOW(), NOW())');
        $statement->execute($input);
        $this->queueAlerts($db, $date, $input);
        flash('success', 'Labour attendance recorded successfully.');
        redirect('/labour/attendance');
    }

    public function deleteAttendance(int $id): void
    {
        $this->authorize();
        verify_csrf();
        $statement = Database::connection()->prepare('DELETE FROM labour_attendance WHERE id = :id');
        $statement->execute(['id' => $id]);
        flash('success', 'Attendance deleted successfully.');
        redirect('/labour/attendance');
    }

    public function work(): void
    {
        $this->authorize();
        $db = Database::connection();
        $filters = $this->filters();
        [$where, $params] = $this->workWhere($filters);
        $statement = $db->prepare("SELECT w.*, p.name AS project_name, c.name AS contractor_name FROM daily_work_completed w LEFT JOIN projects p ON p.id = w.project_id LEFT JOIN contractors c ON c.id = w.contractor_id $where ORDER BY w.date DESC, w.id DESC LIMIT 300");
        $statement->execute($params);
        $records = $statement->fetchAll();
        $projects = $db->query('SELECT id, name FROM projects ORDER BY name')->fetchAll();
        $contractors = $db->query("SELECT id, name FROM contractors WHERE status = 'Active' ORDER BY name")->fetchAll();
        $this->render('labour/work', compact('records', 'projects', 'contractors', 'filters'));
    }

    public function storeWork(): void
    {
        $this->authorize();
        verify_csrf();
        $db = Database::connection();
        $date = trim((string)($_POST['date'] ?? date('Y-m-d')));
        $planned = (float)($_POST['planned_quantity'] ?? 0);
        $completed = (float)($_POST['completed_quantity'] ?? 0);
        $percentage = $planned > 0 ? min(100, max(0, round(($completed / $planned) * 100, 2))) : 0;
        $status = trim((string)($_POST['status'] ?? 'Pending'));
        if (!$this->validDate($date) || trim((string)($_POST['activity'] ?? '')) === '' || !in_array($status, ['Pending', 'In Progress', 'Completed', 'Delayed'], true)) {
            flash('error', 'Date, activity, and a valid status are required.');
            redirect('/labour/work');
        }
        $input = [
            'work_id' => 'WORK-' . date('YmdHis') . '-' . random_int(100, 999),
            'date' => $date,
            'project_id' => $this->nullableInt($_POST['project_id'] ?? null),
            'block' => $this->nullableString($_POST['block'] ?? null),
            'floor' => $this->nullableString($_POST['floor'] ?? null),
            'activity' => trim((string)$_POST['activity']),
            'description' => $this->nullableString($_POST['description'] ?? null),
            'labour_count' => max(0, (int)($_POST['labour_count'] ?? 0)),
            'contractor_id' => $this->nullableInt($_POST['contractor_id'] ?? null),
            'supervisor' => $this->nullableString($_POST['supervisor'] ?? null),
            'planned_quantity' => $planned,
            'completed_quantity' => $completed,
            'unit' => $this->nullableString($_POST['unit'] ?? null),
            'completion_percentage' => $percentage,
            'status' => $status,
            'before_image' => upload_file($_FILES['before_image'] ?? [], 'daily-work') ,
            'after_image' => upload_file($_FILES['after_image'] ?? [], 'daily-work'),
            'materials_used' => $this->nullableString($_POST['materials_used'] ?? null),
            'issues' => $this->nullableString($_POST['issues'] ?? null),
            'next_day_plan' => $this->nullableString($_POST['next_day_plan'] ?? null),
            'remarks' => $this->nullableString($_POST['remarks'] ?? null),
        ];
        $columns = array_keys($input);
        $placeholders = array_map(static fn(string $column): string => ':' . $column, $columns);
        $statement = $db->prepare('INSERT INTO daily_work_completed (`' . implode('`,`', $columns) . '`, created_at, updated_at) VALUES (' . implode(',', $placeholders) . ', NOW(), NOW())');
        $statement->execute($input);
        if ($percentage < 100 || $status === 'Delayed') {
            $this->queueNotification($db, 'Supervisor', 'Work completion below target', 'Activity "' . $input['activity'] . '" is at ' . $percentage . '% completion.');
        }
        flash('success', 'Daily work record saved successfully.');
        redirect('/labour/work');
    }

    public function deleteWork(int $id): void
    {
        $this->authorize();
        verify_csrf();
        $statement = Database::connection()->prepare('DELETE FROM daily_work_completed WHERE id = :id');
        $statement->execute(['id' => $id]);
        flash('success', 'Daily work deleted successfully.');
        redirect('/labour/work');
    }

    public function export(string $type, string $format): void
    {
        $this->authorize();
        $db = Database::connection();
        $queries = [
            'attendance' => "SELECT a.attendance_id, a.date, l.labour_id, l.name AS labour_name, a.trade, p.name AS project, c.name AS contractor, a.check_in, a.check_out, a.working_hours, a.overtime_hours, a.attendance_status, a.daily_wage, a.remarks FROM labour_attendance a JOIN labours l ON l.id = a.labour_id LEFT JOIN projects p ON p.id = a.project_id LEFT JOIN contractors c ON c.id = a.contractor_id ORDER BY a.date DESC, a.id DESC",
            'work' => "SELECT w.work_id, w.date, p.name AS project, w.block, w.floor, w.activity, w.labour_count, c.name AS contractor, w.supervisor, w.planned_quantity, w.completed_quantity, w.unit, w.completion_percentage, w.status, w.materials_used, w.issues FROM daily_work_completed w LEFT JOIN projects p ON p.id = w.project_id LEFT JOIN contractors c ON c.id = w.contractor_id ORDER BY w.date DESC, w.id DESC",
            'contractors' => 'SELECT id, name, mobile, email, trade, status, remarks FROM contractors ORDER BY name',
        ];
        if (!isset($queries[$type]) || !in_array($format, ['csv', 'excel', 'pdf'], true)) {
            http_response_code(404);
            exit('Report not found');
        }
        $rows = $db->query($queries[$type])->fetchAll(PDO::FETCH_ASSOC);
        if ($format === 'csv' || $format === 'excel') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="labour-' . $type . '.' . ($format === 'excel' ? 'xls' : 'csv') . '"');
            $output = fopen('php://output', 'wb');
            if ($rows) {
                fputcsv($output, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
            exit;
        }
        $html = '<h1>SVF Homes - Labour ' . htmlspecialchars(ucfirst($type), ENT_QUOTES, 'UTF-8') . ' Report</h1><table border="1" cellpadding="5"><tr>';
        if ($rows) {
            foreach (array_keys($rows[0]) as $heading) {
                $html .= '<th>' . htmlspecialchars((string)$heading, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            $html .= '</tr>';
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $html .= '</tr>';
            }
        }
        $html .= '</table>';
        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $dompdf->stream('labour-' . $type . '.pdf', ['Attachment' => true]);
            exit;
        }
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

    private function authorize(): void
    {
        require_auth();
        require_role(self::ROLES);
    }

    private function filters(): array
    {
        return [
            'date' => trim((string)($_GET['date'] ?? '')),
            'project_id' => (int)($_GET['project_id'] ?? 0),
            'contractor_id' => (int)($_GET['contractor_id'] ?? 0),
            'trade' => trim((string)($_GET['trade'] ?? '')),
            'attendance_status' => trim((string)($_GET['attendance_status'] ?? '')),
        ];
    }

    private function attendanceWhere(array $filters): array
    {
        $where = [];
        $params = [];
        if ($filters['date'] !== '') { $where[] = 'a.date = :date'; $params['date'] = $filters['date']; }
        if ($filters['project_id'] > 0) { $where[] = 'a.project_id = :project_id'; $params['project_id'] = $filters['project_id']; }
        if ($filters['contractor_id'] > 0) { $where[] = 'a.contractor_id = :contractor_id'; $params['contractor_id'] = $filters['contractor_id']; }
        if ($filters['trade'] !== '') { $where[] = 'a.trade = :trade'; $params['trade'] = $filters['trade']; }
        if ($filters['attendance_status'] !== '') { $where[] = 'a.attendance_status = :attendance_status'; $params['attendance_status'] = $filters['attendance_status']; }
        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private function workWhere(array $filters): array
    {
        $where = [];
        $params = [];
        if ($filters['date'] !== '') { $where[] = 'w.date = :date'; $params['date'] = $filters['date']; }
        if ($filters['project_id'] > 0) { $where[] = 'w.project_id = :project_id'; $params['project_id'] = $filters['project_id']; }
        if ($filters['contractor_id'] > 0) { $where[] = 'w.contractor_id = :contractor_id'; $params['contractor_id'] = $filters['contractor_id']; }
        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $params];
    }

    private function queueAlerts(PDO $db, string $date, array $input): void
    {
        if ($date !== date('Y-m-d')) { return; }
        $present = (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Present'")->fetchColumn();
        if ($present < 5) {
            $this->queueNotification($db, 'Project Manager', 'Low labour attendance', 'Only ' . $present . ' labourers are marked present today.');
        }
    }

    private function queueNotification(PDO $db, string $recipient, string $title, string $message): void
    {
        $statement = $db->prepare("INSERT INTO notifications (user_id, channel, title, message, status, created_at, updated_at) SELECT id, 'In-App', :title, :message, 'Queued', NOW(), NOW() FROM users WHERE role = :role AND status = 'Active'");
        $statement->execute(['title' => $title, 'message' => $message, 'role' => $recipient]);
    }

    private function hours(string $checkIn, string $checkOut): float
    {
        if ($checkIn === '' || $checkOut === '') { return 0; }
        $start = strtotime($checkIn);
        $end = strtotime($checkOut);
        return $end > $start ? round(($end - $start) / 3600, 2) : 0;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string)$value);
        return $value === '' ? null : $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = (int)$value;
        return $value > 0 ? $value : null;
    }

    private function validDate(string $date): bool
    {
        $parsed = date_create_from_format('Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
