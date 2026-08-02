<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;

final class ApiController
{
    public function metrics(): void
    {
        $db = Database::connection();
        json_response([
            'success' => true,
            'data' => [
                'leads' => (int)$db->query('SELECT COUNT(*) FROM leads')->fetchColumn(),
                'customers' => (int)$db->query('SELECT COUNT(*) FROM customers')->fetchColumn(),
                'bookings' => (int)$db->query('SELECT COUNT(*) FROM bookings')->fetchColumn(),
                'revenue' => (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status = 'Paid'")->fetchColumn(),
            ],
        ]);
    }

    public function leads(): void
    {
        json_response(['success' => true, 'data' => Database::connection()->query('SELECT * FROM leads ORDER BY id DESC LIMIT 50')->fetchAll()]);
    }

    public function customers(): void
    {
        json_response(['success' => true, 'data' => Database::connection()->query('SELECT * FROM customers ORDER BY id DESC LIMIT 50')->fetchAll()]);
    }

    public function projects(): void
    {
        json_response(['success' => true, 'data' => Database::connection()->query('SELECT * FROM projects ORDER BY id DESC LIMIT 50')->fetchAll()]);
    }

    public function bookings(): void
    {
        json_response(['success' => true, 'data' => Database::connection()->query('SELECT * FROM bookings ORDER BY id DESC LIMIT 50')->fetchAll()]);
    }

    public function payments(): void
    {
        json_response(['success' => true, 'data' => Database::connection()->query('SELECT * FROM payments ORDER BY id DESC LIMIT 50')->fetchAll()]);
    }

    public function notifications(): void
    {
        json_response(['success' => true, 'data' => Database::connection()->query('SELECT * FROM notifications ORDER BY id DESC LIMIT 50')->fetchAll()]);
    }

    public function aiScore(): void
    {
        require_auth();
        (new AiController())->leadScore();
    }

    public function aiChat(): void
    {
        require_auth();
        (new AiController())->chat();
    }
}
