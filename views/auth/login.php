<?php $errors = flash_errors(); ?>
<div class="auth-panel-shell">
    <section class="auth-hero-panel">
        <div class="auth-hero-content">
            <div class="hero-brand-row">
                <img src="<?= e(app_url('/SVF_ICON.jpeg')) ?>" alt="SVF Homes logo" class="hero-logo">
                <div>
                    <div class="eyebrow">Enterprise AI Platform</div>
                    <h1><span>SVF Homes</span><span>AI CRM</span></h1>
                </div>
            </div>

            <p>Manage leads, customers, bookings, projects, payments, and AI insights in one enterprise dashboard.</p>

            <div class="feature-list">
                <div class="feature-chip"><i class="bi bi-bar-chart-line"></i> Sales Pipeline</div>
                <div class="feature-chip"><i class="bi bi-signpost-2"></i> Site Visits</div>
                <div class="feature-chip"><i class="bi bi-calendar2-check"></i> Bookings</div>
                <div class="feature-chip"><i class="bi bi-cash-stack"></i> Payments</div>
                <div class="feature-chip"><i class="bi bi-graph-up-arrow"></i> Reports</div>
                <div class="feature-chip"><i class="bi bi-robot"></i> AI Assistant</div>
            </div>

            <div class="hero-media-shell">
                <img src="<?= e(app_url('/building.jpg')) ?>" alt="SVF Homes building">
                <div class="hero-media-badge">Enterprise Real Estate ERP</div>
            </div>

            <div class="hero-stat-grid">
                <div class="hero-stat-card"><i class="bi bi-building"></i><span><strong>40+</strong><small>Projects</small></span></div>
                <div class="hero-stat-card"><i class="bi bi-people-fill"></i><span><strong>400+</strong><small>Happy Customers</small></span></div>
                <div class="hero-stat-card"><i class="bi bi-house-door"></i><span><strong>400+</strong><small>Units Sold</small></span></div>
                <div class="hero-stat-card"><i class="bi bi-cash-stack"></i><span><strong>₹150 Cr+</strong><small>Revenue Generated</small></span></div>
            </div>

            <div class="hero-footer">
                <span><i class="bi bi-shield-check"></i> Secure and trusted by SVF Homes teams</span>
                <span>© 2026 SVF Homes AI CRM • All Rights Reserved</span>
            </div>
        </div>
    </section>

    <section class="auth-form-panel">
        <div class="auth-card">
            <div class="auth-brand">
                <img src="<?= e(app_url('/SVF_ICON.jpeg')) ?>" alt="SVF Homes logo" class="auth-logo">
                <div>
                    <h2>Welcome Back</h2>
                    <p>Sign in to continue to SVF AI CRM</p>
                </div>
            </div>

            <?php if ($message = flash('error')): ?>
                <div class="alert alert-danger auth-alert" role="alert"><?= e($message) ?></div>
            <?php endif; ?>
            <?php if ($message = flash('success')): ?>
                <div class="alert alert-success auth-alert" role="alert"><?= e($message) ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="alert alert-warning auth-alert" role="alert">
                    <?php foreach ($errors as $field => $messages): ?>
                        <div><strong><?= e(ucfirst((string)$field)) ?>:</strong> <?= e(implode(' ', (array)$messages)) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(app_url('/login')) ?>" class="auth-form needs-validation" novalidate>
                <?= csrf_field() ?>
                <div class="form-floating mb-3">
                    <input type="email" name="email" id="authEmail" value="<?= e(old('email')) ?>" class="form-control auth-field" placeholder="Email Address" autocomplete="email" required>
                    <label for="authEmail">Email Address</label>
                    <i class="bi bi-envelope auth-input-icon"></i>
                </div>
                <div class="form-floating mb-3 password-wrap">
                    <input type="password" name="password" id="authPassword" class="form-control auth-field" placeholder="Password" autocomplete="current-password" required>
                    <label for="authPassword">Password</label>
                    <button type="button" class="password-toggle" aria-label="Show password" data-target="authPassword">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4 auth-meta-row">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="rememberMe" name="remember">
                        <label class="form-check-label" for="rememberMe">Remember Me</label>
                    </div>
                    <a class="small" href="<?= e(app_url('/forgot-password')) ?>">Forgot Password?</a>
                </div>
                <button class="btn btn-accent btn-lg w-100 auth-submit-button" type="submit">
                    <span class="button-text">Login</span>
                    <i class="bi bi-arrow-right"></i>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>

            <div class="quick-access">
                <div class="quick-label">Quick Logins</div>
                <div class="quick-pills">
                    <button type="button" class="quick-pill" data-email="admin@svfhomes.com" data-password="admin123">Admin</button>
                    <button type="button" class="quick-pill" data-email="manager@svfhomes.com" data-password="admin123">Sales</button>
                    <button type="button" class="quick-pill" data-email="accounts@svfhomes.com" data-password="admin123">Manager</button>
                    <button type="button" class="quick-pill" data-email="admin@svfhomes.com" data-password="admin123">Demo</button>
                </div>
            </div>

            <div class="security-row" aria-label="Security highlights">
                <div><i class="bi bi-shield-lock"></i> Secure Login</div>
                <div><i class="bi bi-lock-fill"></i> Data Protected</div>
                <div><i class="bi bi-cloud-arrow-up"></i> Cloud-Based</div>
            </div>

            <div class="auth-footer">
                <div>© 2026 SVF Homes AI CRM</div>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms & Conditions</a>
                    <a href="#">Support</a>
                </div>
            </div>
        </div>
    </section>
</div>