<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

final class NotificationsController extends Controller
{
    public function index(): void
    {
        require_auth();
        $records = Database::connection()->query('SELECT * FROM notifications ORDER BY id DESC')->fetchAll();
        $this->render('notifications/index', ['records' => $records]);
    }

    public function sendReminder(): void
    {
        require_auth();
        verify_csrf();
        $payload = [
            'channel' => $_POST['channel'] ?? 'In-App',
            'title' => $_POST['title'] ?? 'Reminder',
            'message' => $_POST['message'] ?? '',
            'status' => 'Queued',
        ];
        $db = Database::connection();
        $statement = $db->prepare('INSERT INTO notifications (user_id, channel, title, message, status, created_at, updated_at) VALUES (:user_id, :channel, :title, :message, :status, NOW(), NOW())');
        $statement->execute([
            'user_id' => current_user()['id'],
            'channel' => $payload['channel'],
            'title' => $payload['title'],
            'message' => $payload['message'],
            'status' => $payload['status'],
        ]);

        $this->json(['success' => true, 'message' => 'Notification queued successfully.']);
    }
}
