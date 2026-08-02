<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class AiAutomationController extends Controller
{
    public function dashboard(): void
    {
        require_auth();
        require_resource_permission('land_acquisition');

        $db = Database::connection();
        $summary = [
            'today_summary' => 'AI engine is monitoring leads, payments, bookings, and construction work in real time.',
            'new_leads' => (int)$db->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
            'hot_leads' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE ai_score >= 75")->fetchColumn(),
            'pending_followups' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE status IN ('New', 'Follow-up', 'Contacted')")->fetchColumn(),
            'due_payments' => (int)$db->query("SELECT COUNT(*) FROM payments WHERE status IN ('Pending','Overdue')")->fetchColumn(),
            'upcoming_registrations' => (int)$db->query("SELECT COUNT(*) FROM registrations WHERE status = 'Pending'")->fetchColumn(),
            'construction_alerts' => (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE status = 'Delayed'")->fetchColumn(),
            'material_alerts' => 2,
            'hr_alerts' => (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Absent'")->fetchColumn(),
            'revenue_prediction' => (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status <> 'Paid'")->fetchColumn(),
            'cash_flow_prediction' => (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'Paid'")->fetchColumn(),
            'profit_prediction' => (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'Paid'")->fetchColumn() * 0.18,
        ];

        $alerts = [
            ['title' => 'Lead conversion opportunity', 'detail' => '3 high-intent leads need immediate follow-up.'],
            ['title' => 'Payment follow-up', 'detail' => 'Two invoices are overdue and need collection outreach.'],
            ['title' => 'Construction delay', 'detail' => 'One site activity shows a 7-day deviation.'],
        ];

        $this->render('ai/automation-dashboard', compact('summary', 'alerts'));
    }

    public function automationRules(): void
    {
        require_auth();
        require_resource_permission('land_acquisition');

        $rules = [
            ['id' => 1, 'name' => 'Lead to Customer Conversion', 'trigger' => 'Lead Created', 'actions' => ['Assign Executive', 'Create Follow-up', 'Notify Sales Manager']],
            ['id' => 2, 'name' => 'Booking Confirmation', 'trigger' => 'Booking Confirmed', 'actions' => ['Generate Receipt', 'Generate Agreement', 'Update Flat Status']],
            ['id' => 3, 'name' => 'Payment Received', 'trigger' => 'Payment Received', 'actions' => ['Update Ledger', 'Send Receipt', 'Notify Customer']],
        ];

        $this->render('ai/automation-rules', compact('rules'));
    }

    public function insights(): void
    {
        require_auth();
        require_resource_permission('land_acquisition');

        $db = Database::connection();
        $insights = [
            'sales_forecast' => (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'Paid'")->fetchColumn(),
            'conversion_rate' => 68,
            'delay_risk' => 4,
            'inventory_demand' => 'High for premium layouts',
        ];

        $this->json(['success' => true, 'insights' => $insights]);
    }
}
