<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-speedometer2"></i>
                Pilotage administration
            </span>
            <h1 class="page-hero-title mb-2">Tableau de bord</h1>
            <p class="page-hero-copy mb-0">Vue d ensemble de l activite RH: effectif, presence quotidienne et actions prioritaires.</p>
        </div>
        <div class="realtime-status-panel text-lg-end">
            <div class="small text-uppercase text-muted">Aujourd hui</div>
            <div class="fw-semibold font-mono"><?= date('d/m/Y') ?></div>
        </div>
    </div>
</section>

<!-- KPIs -->
<div class="row g-3 g-xl-4 mb-4">
    <?= view('components/kpi_card', [
        'icon' => 'bi-people',
        'value' => number_format($stats['total_employes']),
        'label' => 'Employés actifs',
        'color' => 'primary'
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-person-plus',
        'value' => $stats['nouveaux_ce_mois'],
        'label' => 'Nouveaux ce mois',
        'color' => 'success'
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-check-circle',
        'value' => $stats['presents_aujourdhui'],
        'label' => 'Présents aujourd\'hui',
        'color' => 'success'
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-exclamation-triangle',
        'value' => $stats['retards_aujourdhui'],
        'label' => 'Retards aujourd\'hui',
        'color' => 'warning'
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-x-circle',
        'value' => $stats['absents_aujourdhui'],
        'label' => 'Absents aujourd\'hui',
        'color' => 'danger'
    ]) ?>
</div>

<div class="row g-4">
    <!-- Graphique des présences -->
    <div class="col-12 col-xl-8">
        <div class="card h-100 dashboard-panel">
            <div class="card-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="h5 mb-1"><i class="bi bi-pie-chart me-2"></i>Presences aujourd hui</h2>
                    <p class="small text-muted mb-0">Distribution presents, retards et absents sur la journee.</p>
                </div>
                <a href="<?= site_url('admin/presences/index') ?>" class="btn btn-sm btn-light border">Ouvrir les presences</a>
            </div>
            <div class="card-body">
                <div class="dashboard-chart-wrap">
                    <canvas id="presenceChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="col-12 col-xl-4">
        <div class="card h-100 dashboard-panel">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-lightning me-2"></i>Actions rapides</h2>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= site_url('admin/employees/create') ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nouvel employe
                    </a>
                    <a href="<?= site_url('admin/presences/index') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-check me-2"></i>
                        Voir presences
                    </a>
                    <a href="<?= site_url('admin/leaves') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-calendar-event me-2"></i>
                        Gerer conges
                    </a>
                    <a href="<?= site_url('admin/documents') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-folder2-open me-2"></i>
                        Documents
                    </a>
                    <a href="<?= site_url('admin/rapports') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-file-earmark-bar-graph me-2"></i>
                        Rapports
                    </a>
                    <a href="<?= site_url('shared/realtime') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-display me-2"></i>
                        Vue temps reel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notifications récentes -->
<div class="row mt-1">
    <div class="col-12">
        <div class="card dashboard-panel">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="bi bi-bell me-2"></i>Activite recente</h2>
            </div>
            <div class="card-body">
                <div class="realtime-placeholder">
                    <i class="bi bi-info-circle fs-1 text-muted"></i>
                    <p class="text-muted mb-0">Aucune activite recente a afficher.</p>
                    <small class="text-muted">Les notifications et evenements systeme apparaitront ici.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique des présences
    const ctx = document.getElementById('presenceChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($presenceData['labels']) ?>,
            datasets: [{
                data: <?= json_encode($presenceData['data']) ?>,
                backgroundColor: <?= json_encode($presenceData['colors']) ?>,
                borderWidth: 0,
                cutout: '68%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                            const value = context.raw || 0;
                            const ratio = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${context.label}: ${value} (${ratio}%)`;
                        }
                    }
                },
            }
        }
    });
});
</script>