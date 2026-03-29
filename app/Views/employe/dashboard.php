<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Mon tableau de bord</h1>
        <p class="text-muted">Vue d'ensemble de votre activité</p>
    </div>
    <div class="text-end">
        <div class="text-muted small">Aujourd'hui</div>
        <div class="fw-bold"><?= date('l j F Y', strtotime('today')) ?></div>
    </div>
</div>

<!-- Statut présence aujourd'hui -->
<?php if ($stats['presence_today']): ?>
    <div class="alert alert-<?= $stats['presence_today']['statut'] === 'present' ? 'success' : ($stats['presence_today']['statut'] === 'retard' ? 'warning' : 'danger') ?> mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-<?= $stats['presence_today']['statut'] === 'present' ? 'check-circle' : ($stats['presence_today']['statut'] === 'retard' ? 'exclamation-triangle' : 'x-circle') ?> fs-4 me-3"></i>
            <div>
                <h6 class="mb-1">
                    <?php if ($stats['presence_today']['statut'] === 'present'): ?>
                        Vous êtes marqué présent aujourd'hui
                    <?php elseif ($stats['presence_today']['statut'] === 'retard'): ?>
                        Vous êtes en retard aujourd'hui
                    <?php else: ?>
                        Vous êtes absent aujourd'hui
                    <?php endif; ?>
                </h6>
                <small>
                    <?php if (!empty($stats['presence_today']['heure_pointage'])): ?>
                        Arrivée : <?= date('H:i', strtotime($stats['presence_today']['heure_pointage'])) ?>
                    <?php endif; ?>
                    <?php if (!empty($stats['presence_today']['heure_sortie'])): ?>
                        - Départ : <?= date('H:i', strtotime($stats['presence_today']['heure_sortie'])) ?>
                    <?php endif; ?>
                </small>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle fs-4 me-3"></i>
            <div>
                <h6 class="mb-1">Pointage non effectué</h6>
                <small>N'oubliez pas de pointer votre arrivée et départ via le kiosque.</small>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- KPIs -->
<div class="row mb-4">
    <?= view('components/kpi_card', [
        'icon' => 'bi-check-circle',
        'value' => $stats['presents_ce_mois'],
        'label' => 'Présents ce mois',
        'color' => 'success'
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-exclamation-triangle',
        'value' => $stats['retards_ce_mois'],
        'label' => 'Retards ce mois',
        'color' => 'warning'
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-x-circle',
        'value' => $stats['absents_ce_mois'],
        'label' => 'Absents ce mois',
        'color' => 'danger'
    ]) ?>

    <?php if ($stats['solde_conge']): ?>
        <?= view('components/kpi_card', [
            'icon' => 'bi-calendar-event',
            'value' => number_format($stats['solde_conge']['restant'], 1),
            'label' => 'Jours de congé restants',
            'color' => 'info'
        ]) ?>
    <?php endif; ?>
</div>

<div class="row">
    <!-- Solde de congé -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-event me-2"></i>
                    Solde de congé
                </h5>
            </div>
            <div class="card-body">
                <?php if ($stats['solde_conge']): ?>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fs-4 fw-bold text-primary"><?= number_format($stats['solde_conge']['solde_annuel'], 1) ?></div>
                            <small class="text-muted">Annuel</small>
                        </div>
                        <div class="col-4">
                            <div class="fs-4 fw-bold text-success"><?= number_format($stats['solde_conge']['restant'], 1) ?></div>
                            <small class="text-muted">Restant</small>
                        </div>
                        <div class="col-4">
                            <div class="fs-4 fw-bold text-warning"><?= number_format($stats['solde_conge']['pris'], 1) ?></div>
                            <small class="text-muted">Pris</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <a href="#" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>
                            Demander un congé
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-info-circle fs-1 text-muted mb-3"></i>
                        <p class="text-muted mb-0">Solde non disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Actions rapides
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= site_url('profile') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-person me-2"></i>
                        Mon profil
                    </a>
                    <a href="#" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-check me-2"></i>
                        Mes présences
                    </a>
                    <a href="#" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-event me-2"></i>
                        Mes congés
                    </a>
                    <a href="<?= site_url('employe/documents') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        Mes documents
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>