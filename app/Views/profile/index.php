<?php
$roleLabels = [
    'admin' => 'Administrateur',
    'agent' => 'Agent d accueil',
    'employe' => 'Employe',
];
$roleLabel = $roleLabels[strtolower((string) ($user['role'] ?? ''))] ?? 'Utilisateur';
$dashboardRole = strtolower((string) ($user['role'] ?? ''));
if (!in_array($dashboardRole, ['admin', 'agent', 'employe'], true)) {
    $dashboardRole = 'employe';
}
$errors = session('errors') ?? [];
?>

<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-person-vcard"></i>
                Espace personnel
            </span>
            <h1 class="page-hero-title mb-2">Mon profil</h1>
            <p class="page-hero-copy mb-0">Consultez vos informations personnelles et mettez a jour vos identifiants de securite.</p>
        </div>
        <a href="<?= site_url($dashboardRole . '/dashboard') ?>" class="btn btn-light border">
            <i class="bi bi-arrow-left me-2"></i>
            Retour au tableau de bord
        </a>
    </div>
</section>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4">
        <?= session('success') ?>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4">
        <?= session('error') ?>
    </div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="fw-semibold mb-1">Certains champs sont invalides.</div>
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row g-4 align-items-start">
    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <h2 class="h5 mb-0"><i class="bi bi-person me-2"></i>Informations personnelles</h2>
                <span class="badge rounded-pill text-bg-primary-subtle text-primary-emphasis px-3 py-2"><?= esc($roleLabel) ?></span>
            </div>
            <div class="card-body">
                <?php if ($employe): ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="profile-field">
                                <div class="profile-field-label">Matricule</div>
                                <div class="profile-field-value font-mono"><?= esc($employe['matricule']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-field">
                                <div class="profile-field-label">Email</div>
                                <div class="profile-field-value"><?= esc($user['email']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-field">
                                <div class="profile-field-label">Nom</div>
                                <div class="profile-field-value"><?= esc($employe['nom']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-field">
                                <div class="profile-field-label">Prenom</div>
                                <div class="profile-field-value"><?= esc($employe['prenom']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-field">
                                <div class="profile-field-label">Telephone</div>
                                <div class="profile-field-value"><?= esc($employe['telephone'] ?: 'Non renseigne') ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-field">
                                <div class="profile-field-label">Date de naissance</div>
                                <div class="profile-field-value"><?= date('d/m/Y', strtotime($employe['date_naissance'])) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-field">
                                <div class="profile-field-label">Date d embauche</div>
                                <div class="profile-field-value"><?= date('d/m/Y', strtotime($employe['date_embauche'])) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-field">
                                <div class="profile-field-label">Poste</div>
                                <div class="profile-field-value"><?= esc($employe['poste']) ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="profile-field">
                                <div class="profile-field-label">Departement</div>
                                <div class="profile-field-value"><?= esc($employe['departement']) ?></div>
                            </div>
                        </div>
                        <?php if (!empty($employe['adresse'])): ?>
                            <div class="col-12">
                                <div class="profile-field">
                                    <div class="profile-field-label">Adresse</div>
                                    <div class="profile-field-value">
                                        <?= esc($employe['adresse']) ?><br>
                                        <?= esc(($employe['ville'] ?? '') . ' ' . ($employe['code_postal'] ?? '') . ', ' . ($employe['pays'] ?? '')) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="realtime-placeholder">
                        <i class="bi bi-info-circle fs-1 text-muted"></i>
                        <p class="mb-0 text-muted">Informations employe non disponibles.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-shield-lock me-2"></i>Securite du compte</h2>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Mettez a jour vos identifiants regulierement pour proteger l acces a vos donnees RH.</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="bi bi-key me-2"></i>
                        Changer le mot de passe
                    </button>

                    <?php if ($employe): ?>
                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changePinModal">
                            <i class="bi bi-lock me-2"></i>
                            Changer le PIN kiosque
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal changement mot de passe -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Changer le mot de passe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('profile/update-password') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Mot de passe actuel</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nouveau mot de passe</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8" autocomplete="new-password">
                        <div class="form-text">Minimum 8 caractères.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Changer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal changement PIN -->
<?php if ($employe): ?>
<div class="modal fade" id="changePinModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Changer le PIN kiosque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('profile/update-pin') ?>" method="post">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="pin" class="form-label">Nouveau PIN (4 chiffres)</label>
                        <input type="text" class="form-control" id="pin" name="pin" pattern="[0-9]{4}" maxlength="4" required inputmode="numeric" autocomplete="off">
                        <div class="form-text">Le PIN doit contenir exactement 4 chiffres.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Changer</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>