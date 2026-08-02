<div class="auth-card card shadow-lg border-0 mx-auto">
    <div class="card-body p-4 p-md-5">
        <h2 class="h3 mb-2 text-white">Change password</h2>
        <form method="post" action="<?= e(app_url('/change-password')) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label text-white-50">Current password</label>
                <input type="password" name="current_password" class="form-control auth-input" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-white-50">New password</label>
                <input type="password" name="password" class="form-control auth-input" required>
            </div>
            <div class="mb-4">
                <label class="form-label text-white-50">Confirm password</label>
                <input type="password" name="password_confirmation" class="form-control auth-input" required>
            </div>
            <button class="btn btn-accent w-100" type="submit">Update password</button>
        </form>
    </div>
</div>