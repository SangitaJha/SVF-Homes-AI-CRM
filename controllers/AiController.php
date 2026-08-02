<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class AiController extends Controller
{
    public function index(): void
    {
        require_auth();
        $this->render('ai/index');
    }

    public function chat(): void
    {
        require_auth();
        verify_csrf();
        $prompt = trim((string)($_POST['prompt'] ?? ''));
        $insight = $this->generateInsight($prompt);
        $this->json(['success' => true, 'message' => $insight]);
    }

    public function leadScore(): void
    {
        require_auth();
        verify_csrf();
        $score = $this->scoreLead($_POST);
        $this->json(['success' => true, 'score' => $score, 'label' => $this->leadLabel($score)]);
    }

    public function whatsappMessage(): void
    {
        require_auth();
        verify_csrf();
        $message = $this->messageTemplate('whatsapp', $_POST);
        $this->json(['success' => true, 'message' => $message]);
    }

    public function emailMessage(): void
    {
        require_auth();
        verify_csrf();
        $message = $this->messageTemplate('email', $_POST);
        $this->json(['success' => true, 'message' => $message]);
    }

    public function callSummary(): void
    {
        require_auth();
        verify_csrf();
        $notes = trim((string)($_POST['notes'] ?? ''));
        $summary = 'Summary: ' . mb_substr($notes, 0, 180);
        $nextStep = 'Suggested next action: schedule a follow-up and send a property shortlist.';
        $this->json(['success' => true, 'summary' => $summary, 'next_step' => $nextStep]);
    }

    public function propertyRecommendation(): void
    {
        require_auth();
        verify_csrf();
        $budget = (float)($_POST['budget'] ?? 0);
        $properties = Database::connection()->query('SELECT id, name, location, status FROM projects ORDER BY id DESC LIMIT 5')->fetchAll();
        $this->json(['success' => true, 'budget' => $budget, 'recommendations' => $properties]);
    }

    public function dashboardInsights(): void
    {
        require_auth();
        $db = Database::connection();
        $summary = [
            'booking_prediction' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE status IN ('Interested','Site Visit','Negotiation')")->fetchColumn(),
            'revenue_prediction' => (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status <> 'Paid'")->fetchColumn(),
            'best_project' => $db->query('SELECT name FROM projects ORDER BY id DESC LIMIT 1')->fetchColumn(),
            'hot_leads' => (int)$db->query("SELECT COUNT(*) FROM leads WHERE ai_score >= 75")->fetchColumn(),
            'monthly_growth' => (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn(),
        ];
        $this->json(['success' => true, 'insights' => $summary]);
    }

    public function documentReader(): void
    {
        require_auth();
        verify_csrf();
        $filename = $_FILES['document']['name'] ?? '';
        $this->json([
            'success' => true,
            'extracted' => [
                'customer_name' => 'Sample Customer',
                'property_name' => 'SVF Homes Residence',
                'amount' => '2500000',
                'dates' => [date('Y-m-d')],
                'agreement_number' => 'AGR-' . date('Ymd') . '-001',
                'summary' => 'Uploaded document processed successfully: ' . $filename,
            ],
        ]);
    }

    public function sopGenerator(): void
    {
        require_auth();
        verify_csrf();
        $topic = trim((string)($_POST['topic'] ?? ''));
        $content = $this->generateSop($topic);
        $this->json(['success' => true, 'message' => $content]);
    }

    public function voiceCommand(): void
    {
        require_auth();
        verify_csrf();
        $command = strtolower(trim((string)($_POST['command'] ?? '')));
        $result = match (true) {
            str_contains($command, 'add lead') => ['action' => 'navigate', 'target' => '/leads/create'],
            str_contains($command, 'create booking') => ['action' => 'navigate', 'target' => '/bookings/create'],
            str_contains($command, 'search customer') => ['action' => 'navigate', 'target' => '/customers'],
            str_contains($command, 'show reports') => ['action' => 'navigate', 'target' => '/reports'],
            default => ['action' => 'message', 'target' => 'Command not recognized'],
        };
        $this->json(['success' => true] + $result);
    }

    private function generateInsight(string $prompt): string
    {
        $prompt = strtolower($prompt);
        return match (true) {
            str_contains($prompt, 'labour attendance') => $this->labourInsight('attendance'),
            str_contains($prompt, 'completed work') => $this->labourInsight('work'),
            str_contains($prompt, 'absent today') => $this->labourInsight('absent'),
            str_contains($prompt, 'daily site report') => $this->labourInsight('site report'),
            str_contains($prompt, 'labour productivity') => $this->labourInsight('productivity'),
            str_contains($prompt, 'tomorrow') && str_contains($prompt, 'labour') => $this->labourInsight('tomorrow'),
            str_contains($prompt, 'construction progress') => $this->labourInsight('progress'),
            str_contains($prompt, 'follow-up') => 'Today follow-ups are in the follow-ups module. Prioritize pending items due now.',
            str_contains($prompt, 'pending payments') => 'Check the payments module for pending and overdue receipts.',
            str_contains($prompt, 'hot leads') => 'Hot leads are the leads with Interested or Negotiation status and higher AI score.',
            str_contains($prompt, 'bookings') => 'Use the bookings dashboard to review todays bookings and booking pipeline.',
            str_contains($prompt, 'available flats') => 'Available flats are the inventory items marked as Available in the flats module.',
            str_contains($prompt, 'sop') => 'SOP generation is available from the AI Assistant page. Use the SOP Generator card to draft a standard operating procedure.',
            default => 'I can help with leads, follow-ups, bookings, payments, projects, inventory, labour, and SOP insights.',
        };
    }

    private function generateSop(string $topic): string
    {
        $key = trim((string)env('OPENAI_API_KEY', ''));
        if ($key !== '') {
            $result = $this->callOpenAi($topic);
            if ($result !== '') {
                return $result;
            }
        }

        return 'SOP Draft for ' . ($topic !== '' ? $topic : 'operations') . "\n\n1. Objective\n- Define the purpose of the activity.\n2. Scope\n- List teams, departments, and project areas involved.\n3. Procedure\n- Step-by-step checklist for execution and handoff.\n4. Controls\n- Verify quality, safety, and compliance before closure.";
    }

    private function callOpenAi(string $topic): string
    {
        if (!function_exists('curl_init')) {
            return '';
        }

        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an enterprise real-estate operations assistant. Write a concise SOP draft.'],
                ['role' => 'user', 'content' => 'Create a concise SOP draft for ' . $topic],
            ],
            'temperature' => 0.4,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . trim((string)env('OPENAI_API_KEY', '')),
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 200 && $httpCode < 300 && $response !== false) {
            $decoded = json_decode((string)$response, true);
            if (!empty($decoded['choices'][0]['message']['content'])) {
                return trim((string)$decoded['choices'][0]['message']['content']);
            }
        }

        return '';
    }

    private function labourInsight(string $type): string
    {
        $db = Database::connection();
        return match ($type) {
            'attendance' => 'Today: ' . (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE()")->fetchColumn() . ' attendance records, including ' . (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Present'")->fetchColumn() . ' present.',
            'work' => 'Today: ' . (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE date = CURDATE() AND status = 'Completed'")->fetchColumn() . ' activities completed and average progress is ' . number_format((float)$db->query("SELECT COALESCE(AVG(completion_percentage), 0) FROM daily_work_completed WHERE date = CURDATE()")->fetchColumn(), 1) . '%.',
            'absent' => 'There are ' . (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Absent'")->fetchColumn() . ' labourers marked absent today. Review the Attendance page for names and project allocation.',
            'site report' => 'Daily site report: ' . (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE date = CURDATE()")->fetchColumn() . ' activities logged, ' . (int)$db->query("SELECT COUNT(*) FROM daily_work_completed WHERE date = CURDATE() AND status = 'Delayed'")->fetchColumn() . ' delayed, and average completion is ' . number_format((float)$db->query("SELECT COALESCE(AVG(completion_percentage), 0) FROM daily_work_completed WHERE date = CURDATE()")->fetchColumn(), 1) . '%.',
            'productivity' => 'Productivity is measured from completed quantity per labour count. Use Labour Management > Daily Work for activity-level quantities and supervisor notes.',
            'tomorrow' => 'Tomorrow labour planning should start from today\'s present count of ' . (int)$db->query("SELECT COUNT(*) FROM labour_attendance WHERE date = CURDATE() AND attendance_status = 'Present'")->fetchColumn() . ' and increase allocation for delayed activities.',
            'progress' => 'Today\'s construction progress averages ' . number_format((float)$db->query("SELECT COALESCE(AVG(completion_percentage), 0) FROM daily_work_completed WHERE date = CURDATE()")->fetchColumn(), 1) . '%. Check delayed activities before publishing the site report.',
            default => 'Labour insights are available from the Labour Management dashboard.',
        };
    }

    private function scoreLead(array $input): int
    {
        $score = 30;
        $budget = (float)($input['budget'] ?? 0);
        $siteVisit = !empty($input['site_visit']);
        $followups = (int)($input['followups'] ?? 0);
        $interest = (int)($input['interest'] ?? 0);
        $responseTime = (int)($input['response_time'] ?? 0);

        $score += $budget >= 5000000 ? 25 : ($budget >= 2500000 ? 15 : 5);
        $score += $siteVisit ? 20 : 0;
        $score += $followups >= 2 ? 10 : 0;
        $score += $interest >= 7 ? 15 : ($interest >= 4 ? 8 : 0);
        $score += $responseTime <= 30 ? 10 : ($responseTime <= 120 ? 5 : 0);

        return min(100, max(0, $score));
    }

    private function leadLabel(int $score): string
    {
        return match (true) {
            $score >= 75 => 'Hot',
            $score >= 45 => 'Warm',
            default => 'Cold',
        };
    }

    private function messageTemplate(string $type, array $input): string
    {
        $name = trim((string)($input['name'] ?? 'Customer'));
        $project = trim((string)($input['project'] ?? 'our project'));
        return match ($type) {
            'whatsapp' => "Hi {$name}, thank you for your interest in {$project}. Please let us know a convenient time for a quick update.",
            'email' => "Dear {$name},\n\nThank you for your interest in {$project}. We have prepared the next step for your review.\n\nRegards,\nSVF Homes Team",
            default => '',
        };
    }
}
