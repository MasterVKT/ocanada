<?php
$color = $color ?? 'primary';
$variantClass = 'kpi-' . preg_replace('/[^a-z]/', '', strtolower((string) $color));
?>
<div class="col-12 col-sm-6 col-xl-4 col-xxl">
    <article class="kpi-card <?= esc($variantClass) ?> h-100">
        <div class="kpi-icon-wrap">
            <i class="<?= esc($icon ?? 'bi bi-graph-up') ?>"></i>
        </div>
        <div class="kpi-body">
            <div class="kpi-value"><?= esc((string) ($value ?? '0')) ?></div>
            <p class="kpi-label mb-0"><?= esc($label ?? 'Indicateur') ?></p>
            <?php if (isset($change)): ?>
                <div class="kpi-trend text-<?= $change >= 0 ? 'success' : 'danger' ?>">
                    <i class="bi bi-arrow-<?= $change >= 0 ? 'up' : 'down' ?>"></i>
                    <span><?= abs((int) $change) ?>%</span>
                </div>
            <?php endif; ?>
        </div>
    </article>
</div>