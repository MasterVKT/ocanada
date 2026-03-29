<div class="auth-form-header mb-4">
    <span class="auth-form-chip mb-3 d-inline-flex align-items-center gap-2">
        <i class="bi bi-shield-lock"></i>
        Connexion securisee
    </span>
    <h2 class="h3 mb-2"><?= lang('Auth.login_title') ?></h2>
    <p class="text-muted mb-0">Connectez-vous pour acceder a votre espace RH, suivre vos activites et executer vos actions en toute securite.</p>
</div>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm">
        <?= session('error') ?>
    </div>
<?php endif; ?>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success border-0 shadow-sm">
        <?= session('success') ?>
    </div>
<?php endif; ?>

<form action="<?= site_url('auth/attempt-login') ?>" method="post" class="auth-form-stack">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label for="email" class="form-label fw-semibold text-secondary">
            <i class="bi bi-envelope me-1"></i>
            <?= lang('Auth.login_email') ?>
        </label>
        <input type="email"
               class="form-control form-control-lg"
               id="email"
               name="email"
               value="<?= old('email') ?>"
               required
               autofocus
               autocomplete="email">
        <?php if (isset($errors['email'])): ?>
            <div class="text-danger small mt-1">
                <?= $errors['email'] ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
            <label for="password" class="form-label fw-semibold text-secondary mb-0">
                <i class="bi bi-key me-1"></i>
                <?= lang('Auth.login_password') ?>
            </label>
            <a href="<?= site_url('auth/forgot-password') ?>" class="small text-decoration-none">Mot de passe oublie ?</a>
        </div>
        <input type="password"
               class="form-control form-control-lg"
               id="password"
               name="password"
               required
               autocomplete="current-password">
        <?php if (isset($errors['password'])): ?>
            <div class="text-danger small mt-1">
                <?= $errors['password'] ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="d-grid gap-3">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-box-arrow-in-right me-2"></i>
            <?= lang('Auth.login_button') ?>
        </button>
        <div class="small text-muted">Acces reserve aux utilisateurs autorises. Les tentatives d acces sont journalisees.</div>
    </div>
</form>