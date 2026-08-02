<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

if (auth_check()) {
    redirect('/dashboard');
}

$error = null;

if (is_post()) {
    verify_csrf();
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $statement = \App\Core\Database::connection()->prepare('SELECT id, name, email, role, password FROM users WHERE email = :email AND status = "Active" LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if ($user && password_verify($password, (string)$user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        $update = \App\Core\Database::connection()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id');
        $update->execute(['id' => (int)$user['id']]);

        flash('success', 'Welcome back, ' . $user['name'] . '.');
        redirect('/dashboard');
    }

    $error = 'Invalid email or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SVF Homes AI CRM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-900">
    <div class="h-screen w-full overflow-hidden bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.16),_transparent_36%),linear-gradient(135deg,_#f8fbff_0%,_#eef4ff_100%)] p-3 sm:p-4 lg:p-5">
        <div class="mx-auto grid h-full max-w-6xl grid-cols-1 gap-4 overflow-hidden rounded-[28px] border border-slate-200/70 bg-white/90 shadow-[0_30px_80px_rgba(15,23,42,0.14)] backdrop-blur lg:grid-cols-12 lg:gap-5">
            <section class="lg:col-span-7 flex h-full flex-col justify-between bg-gradient-to-br from-slate-950 via-slate-900 to-blue-800 p-5 text-white sm:p-6 lg:p-6">
                <div class="flex h-full flex-col justify-between">
                    <div>
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-white/95 p-2 shadow-sm ring-1 ring-blue-100/80">
                                <img src="<?= e(app_url('/SVFLOGO.png')) ?>" alt="SVF Homes logo" class="h-full w-full rounded-xl object-contain" style="object-fit: contain;">
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.3em] text-blue-200">Enterprise AI Platform</p>
                                <h1 class="text-xl font-semibold tracking-tight">SVF Homes AI CRM</h1>
                            </div>
                        </div>

                        <p class="max-w-xl text-sm leading-6 text-slate-200 sm:text-[15px]">
                            Manage leads, customers, bookings, projects, payments, and AI insights in one enterprise dashboard.
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="rounded-full border border-white/15 bg-white/10 px-2.5 py-1.5 text-xs text-white/90">Sales Pipeline</span>
                            <span class="rounded-full border border-white/15 bg-white/10 px-2.5 py-1.5 text-xs text-white/90">Site Visits</span>
                            <span class="rounded-full border border-white/15 bg-white/10 px-2.5 py-1.5 text-xs text-white/90">Bookings</span>
                            <span class="rounded-full border border-white/15 bg-white/10 px-2.5 py-1.5 text-xs text-white/90">Payments</span>
                            <span class="rounded-full border border-white/15 bg-white/10 px-2.5 py-1.5 text-xs text-white/90">Reports</span>
                            <span class="rounded-full border border-white/15 bg-white/10 px-2.5 py-1.5 text-xs text-white/90">AI Assistant</span>
                        </div>

                        <div class="mt-2 h-32 overflow-hidden rounded-[20px] border border-white/10 bg-white/10 shadow-xl shadow-slate-950/30 sm:h-36 lg:h-36">
                            <img src="<?= e(app_url('/buildingyy.jpg')) ?>" alt="SVF Homes building" class="h-full w-full rounded-lg object-cover object-top">
                        </div>

                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">
                                <p class="text-lg font-semibold">40+</p>
                                <p class="text-xs text-slate-300">Projects</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">
                                <p class="text-lg font-semibold">400+</p>
                                <p class="text-xs text-slate-300">Happy Customers</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">
                                <p class="text-lg font-semibold">400+</p>
                                <p class="text-xs text-slate-300">Units Sold</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 px-3 py-2 backdrop-blur">
                                <p class="text-lg font-semibold">₹150 Cr+</p>
                                <p class="text-xs text-slate-300">Revenue Generated</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-white/10 pt-2 text-xs text-slate-300">
                        <span>Secure and trusted by SVF Homes teams</span>
                        <span>© 2026 SVF Homes AI CRM</span>
                    </div>
                </div>
            </section>

            <section class="flex w-full items-center justify-center bg-slate-50/90 px-4 py-4 sm:px-6 lg:col-span-5 lg:px-5 lg:py-5">
                <div class="w-full max-w-md rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_20px_50px_rgba(15,23,42,0.08)] sm:p-6">
                    <div class="mb-4 flex items-center gap-3">
                        <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-white/95 p-2 shadow-sm ring-1 ring-blue-100/80">
                            <img src="<?= e(app_url('/SVFLOGO.png')) ?>" alt="SVF Homes logo" class="h-full w-full rounded-xl object-contain" style="object-fit: contain;">
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-slate-900">Welcome Back</h2>
                            <p class="text-sm text-slate-500">Sign in to continue to SVF AI CRM</p>
                        </div>
                    </div>

                    <?php if ($message = flash('success')): ?>
                        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            <?= e($message) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                        <div class="mb-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <?= e($error) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($messages = flash_errors()): ?>
                        <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            <?php foreach ($messages as $field => $fieldMessages): ?>
                                <div><strong><?= e(ucfirst((string)$field)) ?>:</strong> <?= e(implode(' ', (array)$fieldMessages)) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="space-y-3">
                        <?= csrf_field() ?>
                        <div>
                            <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email Address</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l9 6 9-6" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8" /></svg>
                                </span>
                                <input type="email" id="email" name="email" value="<?= e(old('email')) ?>" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="admin@svfhomes.com">
                            </div>
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="4" y="10" width="16" height="10" rx="2" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10V7a4 4 0 118 0v3" /></svg>
                                </span>
                                <input type="password" id="password" name="password" required class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100" placeholder="Enter password">
                            </div>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 text-slate-600">
                                <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" name="remember">
                                <span>Remember Me</span>
                            </label>
                            <a href="<?= e(app_url('/forgot-password')) ?>" class="font-medium text-blue-600 hover:text-blue-700">Forgot Password?</a>
                        </div>

                        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                            <span>Login</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M20 12H4" /></svg>
                        </button>
                    </form>

                    <div class="mt-4">
                        <p class="mb-2 text-[11px] font-semibold uppercase tracking-[0.25em] text-slate-400">Quick Logins</p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="quick-login rounded-full border border-blue-100 bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 transition hover:bg-blue-100" data-email="admin@svfhomes.com" data-password="admin123">Admin</button>
                            <button type="button" class="quick-login rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100" data-email="manager@svfhomes.com" data-password="admin123">Sales</button>
                            <button type="button" class="quick-login rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100" data-email="accounts@svfhomes.com" data-password="admin123">Manager</button>
                            <button type="button" class="quick-login rounded-full border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100" data-email="admin@svfhomes.com" data-password="admin123">Demo</button>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-100 px-3 py-2 text-[11px] font-medium text-slate-600">Secure Login (256-bit)</span>
                        <span class="rounded-full bg-slate-100 px-3 py-2 text-[11px] font-medium text-slate-600">Data Protected</span>
                        <span class="rounded-full bg-slate-100 px-3 py-2 text-[11px] font-medium text-slate-600">Cloud Based</span>
                    </div>

                    <div class="mt-4 border-t border-slate-200 pt-3 text-sm text-slate-500">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span>© 2026 SVF Homes AI CRM</span>
                            <div class="flex flex-wrap gap-3">
                                <a href="#" class="hover:text-blue-600">Privacy Policy</a>
                                <a href="#" class="hover:text-blue-600">Terms & Conditions</a>
                                <a href="#" class="hover:text-blue-600">Support</a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        document.querySelectorAll('.quick-login').forEach((button) => {
            button.addEventListener('click', () => {
                const emailInput = document.getElementById('email');
                const passwordInput = document.getElementById('password');
                if (emailInput) {
                    emailInput.value = button.getAttribute('data-email') || '';
                }
                if (passwordInput) {
                    passwordInput.value = button.getAttribute('data-password') || '';
                }
            });
        });
    </script>
</body>
</html>