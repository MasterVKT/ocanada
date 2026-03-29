<?php
declare(strict_types=1);

/** @var array $currentUser */
/** @var int $unreadCount */
/** @var string|null $title */

$userName = trim((string) ($currentUser['nom_complet'] ?? 'Utilisateur'));
$role = strtolower((string) ($currentUser['role'] ?? ''));
$roleLabels = [
    'admin' => 'Administrateur',
    'agent' => 'Agent d accueil',
    'employe' => 'Employe',
];
$roleLabel = $roleLabels[$role] ?? 'Utilisateur';
?>
<header class="topbar d-flex align-items-center justify-content-between bg-white border-bottom px-3 px-md-4">
    <div class="d-flex align-items-center gap-3 min-w-0">
        <button class="btn btn-link d-lg-none p-0 text-secondary topbar-menu-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#app-sidebar-mobile" aria-controls="app-sidebar-mobile" aria-label="Basculer la navigation">
            <i class="bi bi-list fs-3"></i>
        </button>
        <div class="min-w-0">
            <div class="topbar-eyebrow">Ô Canada RH</div>
            <div class="topbar-title text-truncate"><?= esc($title ?? 'Espace de travail') ?></div>
        </div>
    </div>
    <div class="d-flex align-items-center gap-3">
        <a href="<?= site_url('notifications') ?>" class="position-relative text-secondary topbar-icon-link" aria-label="Notifications">
            <i class="bi bi-bell fs-5"></i>
            <span id="notif-badge"
                  class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger<?= $unreadCount > 0 ? '' : ' d-none' ?>">
                <?= esc((string) $unreadCount) ?>
            </span>
        </a>
        <div class="dropdown">
            <button class="btn topbar-user-menu d-flex align-items-center gap-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="topbar-avatar rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-person"></i>
                </div>
                <div class="d-none d-md-flex flex-column align-items-start text-start lh-sm">
                    <span class="fw-semibold text-dark"><?= esc($userName) ?></span>
                    <span class="small text-muted"><?= esc($roleLabel) ?></span>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="<?= site_url('profile') ?>"><i class="bi bi-person-circle me-2"></i> Mon profil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= site_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i> Deconnexion</a></li>
            </ul>
        </div>
    </div>
</header>

