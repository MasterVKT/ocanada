<?php
declare(strict_types=1);

/** @var string $content */

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <title>Pointage du personnel — Ô Canada</title>

    <link href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="/assets/css/ocanada.css" rel="stylesheet">
</head>
<body class="kiosque-mode d-flex flex-column">
<div class="kiosque-shell flex-grow-1 d-flex flex-column">
    <?= $content ?>
</div>

<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<script src="/assets/js/kiosque.js"></script>
</body>
</html>

