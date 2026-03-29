<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-info text-white text-center py-4">
                    <h3 class="mb-0">
                        <i class="bi bi-lock me-2"></i>
                        <?= lang('Auth.reset_title') ?>
                    </h3>
                </div>
                <div class="card-body p-5">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php elseif (!isset($valid) || !$valid): ?>
                        <div class="alert alert-danger">
                            Lien de réinitialisation invalide ou expiré.
                        </div>
                    <?php else: ?>

                        <?php if (session()->has('error')): ?>
                            <div class="alert alert-danger">
                                <?= session('error') ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?= site_url('auth/update-password') ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="token" value="<?= $token ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-key me-1"></i>
                                    <?= lang('Auth.reset_password') ?>
                                </label>
                                <input type="password"
                                       class="form-control form-control-lg"
                                       id="password"
                                       name="password"
                                       required
                                       minlength="8">
                                <?php if (isset($errors['password'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?= $errors['password'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirm" class="form-label">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?= lang('Auth.reset_confirm') ?>
                                </label>
                                <input type="password"
                                       class="form-control form-control-lg"
                                       id="password_confirm"
                                       name="password_confirm"
                                       required
                                       minlength="8">
                                <?php if (isset($errors['password_confirm'])): ?>
                                    <div class="text-danger small mt-1">
                                        <?= $errors['password_confirm'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-info btn-lg">
                                    <i class="bi bi-check2 me-2"></i>
                                    <?= lang('Auth.reset_button') ?>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <div class="text-center mt-4">
                        <a href="<?= site_url('auth/login') ?>" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>
                            Retour à la connexion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>