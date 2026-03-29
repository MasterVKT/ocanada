<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-warning text-dark text-center py-4">
                    <h3 class="mb-0">
                        <i class="bi bi-key me-2"></i>
                        <?= lang('Auth.forgot_title') ?>
                    </h3>
                </div>
                <div class="card-body p-5">
                    <p class="text-muted mb-4">
                        Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
                    </p>

                    <?php if (session()->has('error')): ?>
                        <div class="alert alert-danger">
                            <?= session('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->has('success')): ?>
                        <div class="alert alert-success">
                            <?= session('success') ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= site_url('auth/send-reset-link') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="bi bi-envelope me-1"></i>
                                <?= lang('Auth.forgot_email') ?>
                            </label>
                            <input type="email"
                                   class="form-control form-control-lg"
                                   id="email"
                                   name="email"
                                   value="<?= old('email') ?>"
                                   required
                                   autofocus>
                            <?php if (isset($errors['email'])): ?>
                                <div class="text-danger small mt-1">
                                    <?= $errors['email'] ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="bi bi-send me-2"></i>
                                <?= lang('Auth.forgot_button') ?>
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="<?= site_url('auth/login') ?>" class="text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>
                                Retour à la connexion
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>