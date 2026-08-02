<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

final class AuthController extends Controller
{
    public function login(): void
    {
        if (auth_check()) {
            redirect('/dashboard');
        }

        if (!is_post()) {
            $this->render('auth/login', ['layout' => 'auth']);
            return;
        }

        verify_csrf();
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (!(new Auth())->attempt($email, $password)) {
            flash('error', 'Invalid credentials.');
            collect_old_input($_POST);
            redirect('/login');
        }

        clear_old_input();
        flash('success', 'Welcome back.');
        redirect('/dashboard');
    }

    public function logout(): void
    {
        if (is_post()) {
            verify_csrf();
        }

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        header('Location: ' . app_url('/login'));
        exit;
    }

    public function forgotPassword(): void
    {
        $this->render('auth/forgot-password', ['layout' => 'auth']);
    }

    public function changePassword(): void
    {
        require_auth();
        $this->render('auth/change-password', ['layout' => 'auth']);
    }

    public function updatePassword(): void
    {
        require_auth();
        verify_csrf();

        $current = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['password_confirmation'] ?? '');
        $user = current_user();
        $statement = \App\Core\Database::connection()->prepare('SELECT password FROM users WHERE id = :id');
        $statement->execute(['id' => $user['id']]);
        $record = $statement->fetch();

        if (!$record || !password_verify($current, $record['password']) || $newPassword !== $confirm || strlen($newPassword) < 8) {
            flash('error', 'Password update failed.');
            redirect('/change-password');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = \App\Core\Database::connection()->prepare('UPDATE users SET password = :password WHERE id = :id');
        $update->execute(['password' => $hash, 'id' => $user['id']]);

        flash('success', 'Password updated successfully.');
        redirect('/dashboard');
    }
}
