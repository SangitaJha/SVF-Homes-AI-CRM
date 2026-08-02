<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class DashboardController extends Controller
{
    public function index(): void
    {
        require_auth();
        $db = Database::connection();

        $metrics = [
            'totalLeads' => (int)$db->query('SELECT COUNT(*) FROM leads')->fetchColumn(),
            'todaysLeads' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
            'hotLeads' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE status IN ('Interested','Negotiation')")->fetchColumn(),
            'customers' => (int)$db->query('SELECT COUNT(*) FROM customers')->fetchColumn(),
            'projects' => (int)$db->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
            'bookings' => (int)$db->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
            'revenue' => (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'Paid'")->fetchColumn(),
            'pendingPayments' => (int)$db->query("SELECT COUNT(*) FROM payments WHERE status IN ('Pending','Overdue')")->fetchColumn(),
            'availableFlats' => (int)$db->query("SELECT COUNT(*) FROM flats WHERE availability = 'Available'")->fetchColumn(),
            'landLeads' => 0,
            'negotiations' => 0,
        ];

        try {
            $metrics['landLeads'] = (int)$db->query('SELECT COUNT(*) FROM land_leads')->fetchColumn();
            $metrics['negotiations'] = (int)$db->query("SELECT COUNT(*) FROM land_negotiations WHERE negotiation_status != 'Closed'")->fetchColumn();
        } catch (\PDOException) {
            $metrics['landLeads'] = 0;
            $metrics['negotiations'] = 0;
        }

        $constructionMetrics = [
            'labourToday' => 0,
            'labourPresent' => 0,
            'labourAbsent' => 0,
            'labourCost' => 0.0,
            'completedWork' => 0,
            'delayedWork' => 0,
            'projectProgress' => 0.0,
        ];
        $attendanceTrend = [];
        $progressTrend = [];
        try {
            $constructionMetrics = [
                'labourToday' => (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE()")->fetchColumn(),
                'labourPresent' => (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Present'")->fetchColumn(),
                'labourAbsent' => (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Absent'")->fetchColumn(),
                'labourCost' => (float)$db->query("SELECT COALESCE(SUM(daily_wage), 0) FROM labour_attendance WHERE date = CURDATE() AND attendance_status IN ('Present', 'Half Day')")->fetchColumn(),
                'completedWork' => (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE date = CURDATE() AND status = 'Completed'")->fetchColumn(),
                'delayedWork' => (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE date = CURDATE() AND status = 'Delayed'")->fetchColumn(),
                'projectProgress' => (float)$db->query("SELECT COALESCE(AVG(completion_percentage), 0) FROM daily_work_completed WHERE date = CURDATE()")->fetchColumn(),
            ];
            $attendanceTrend = $db->query("SELECT date, SUM(attendance_status = 'Present') AS present, SUM(attendance_status = 'Absent') AS absent FROM labour_attendance WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY date ORDER BY date")->fetchAll();
            $progressTrend = $db->query("SELECT date, COALESCE(AVG(completion_percentage), 0) AS progress FROM daily_work_completed WHERE date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY date ORDER BY date")->fetchAll();
        } catch (\PDOException) {
            // The dashboard remains usable while an existing installation is migrated.
        }

        $monthlyLeads = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS total FROM leads GROUP BY month ORDER BY month DESC LIMIT 6")->fetchAll();
        $monthlyRevenue = $db->query("SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COALESCE(SUM(amount),0) AS total FROM payments GROUP BY month ORDER BY month DESC LIMIT 6")->fetchAll();
        $followups = $db->query("SELECT * FROM followups ORDER BY followup_at ASC LIMIT 5")->fetchAll();
        $activities = $db->query('SELECT * FROM activities ORDER BY created_at DESC LIMIT 6')->fetchAll();
        $notifications = $db->query('SELECT * FROM notifications ORDER BY id DESC LIMIT 6')->fetchAll();
        $bookingStatus = $db->query("SELECT status, COUNT(*) AS total FROM bookings GROUP BY status")->fetchAll();
        $paymentCollection = $db->query("SELECT status, COUNT(*) AS total FROM payments GROUP BY status")->fetchAll();
        $employeePerformance = $db->query("SELECT u.name, COUNT(l.id) AS total FROM users u LEFT JOIN leads l ON l.assigned_to = u.name GROUP BY u.id ORDER BY total DESC LIMIT 5")->fetchAll();
        $projectSales = $db->query("SELECT p.name, COUNT(b.id) AS total FROM projects p LEFT JOIN bookings b ON b.project_id = p.id GROUP BY p.id ORDER BY total DESC LIMIT 5")->fetchAll();

        $this->render('dashboard/index', compact('metrics', 'constructionMetrics', 'attendanceTrend', 'progressTrend', 'monthlyLeads', 'monthlyRevenue', 'followups', 'activities', 'notifications', 'bookingStatus', 'paymentCollection', 'employeePerformance', 'projectSales', 'db'));
    }
}
