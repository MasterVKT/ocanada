<?php
declare(strict_types=1);

/** @var array<string,mixed>|null $currentUser */
/** @var string|null $content */
/** @var string|null $title */
/** @var int|null $unreadCount */

$currentUser = isset($currentUser) && is_array($currentUser) ? $currentUser : [];
$unreadCount = isset($unreadCount) ? (int) $unreadCount : 0;
$content = isset($content) ? (string) $content : '';

$role = (string) ($currentUser['role'] ?? 'employe');
if (! in_array($role, ['admin', 'agent', 'employe'], true)) {
    $role = 'employe';
}

$pageTitle = isset($title) ? (string) $title . ' — Ô Canada RH' : 'Ô Canada RH';
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
<body class="app-shell">

<?= view('components/sidebar_' . $role, ['currentUser' => $currentUser]) ?>

<div class="main-wrapper flex-grow-1 d-flex flex-column">
    <?= view('components/topbar', ['currentUser' => $currentUser, 'unreadCount' => $unreadCount, 'title' => $title ?? null]) ?>

    <main class="content-area flex-grow-1" id="main-content">
        <div class="content-container">
            <?= $content ?>
        </div>
    </main>

    <footer class="app-footer small px-4 px-lg-5 py-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
            <span>&copy; <?= date('Y') ?> Ô Canada — Douala, Cameroun</span>
            <span class="text-uppercase app-footer-meta">Gestion RH centralisee</span>
        </div>
    </footer>
</div>

<?= view('components/chatbot') ?>

<div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 1080;"></div>

<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="/assets/js/app.js"></script>
</body>
</html>

