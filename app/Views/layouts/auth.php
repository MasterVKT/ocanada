<?php
declare(strict_types=1);

/** @var string $content */
/** @var string|null $title */

$pageTitle = isset($title) ? (string) $title . ' — Ô Canada RH' : 'Connexion — Ô Canada RH';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title><?= esc($pageTitle) ?></title>

    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="/assets/css/ocanada.css" rel="stylesheet">
</head>
<body class="auth-body d-flex align-items-center justify-content-center">
<div class="auth-shell container-fluid px-3 px-md-4">
    <div class="row justify-content-center align-items-center min-vh-100 py-4 py-lg-5">
        <div class="col-12 col-lg-10 col-xl-9">
            <div class="auth-card shadow-lg border-0 overflow-hidden bg-white">
                <div class="row g-0">
                    <div class="col-lg-5 d-none d-lg-flex auth-panel text-white">
                        <div class="p-5 d-flex flex-column justify-content-between h-100">
                            <div>
                                <div class="auth-brand mb-3">
                                    <span class="auth-brand-mark"><i class="bi bi-shield-check"></i></span>
                                    <span class="fw-semibold">Ô Canada RH</span>
                                </div>
                                <h1 class="auth-title mb-3">Pilotage RH fiable et local.</h1>
                                <p class="auth-copy mb-0">Accedez aux presences, conges, visiteurs et documents depuis une interface claire, rapide et securisee.</p>
                            </div>
                            <div class="auth-meta">
                                <div class="small text-uppercase mb-2">Douala, Cameroun</div>
                                <div class="auth-meta-chip">France / Afrique Centrale</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="auth-form-wrap p-4 p-md-5">
                            <?= $content ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>

<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>

