<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-person-badge"></i>
                Accueil visiteurs
            </span>
            <h1 class="page-hero-title mb-2">Statistiques des visiteurs</h1>
            <p class="page-hero-copy mb-0">Analysez le trafic visiteurs, les motifs les plus frequents et les collaborateurs les plus sollicites.</p>
        </div>
        <span class="badge rounded-pill text-bg-light border px-3 py-2"><i class="bi bi-calendar-week me-1"></i>Suivi hebdomadaire</span>
    </div>
</section>

<div class="row g-3 g-xl-4 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <?= view('components/kpi_card', ['icon' => 'bi-people', 'value' => (string) $todayVisitors, 'label' => 'Visiteurs aujourd hui', 'color' => 'primary']) ?>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <?= view('components/kpi_card', ['icon' => 'bi-person-check-fill', 'value' => (string) $todayPresent, 'label' => 'Presents maintenant', 'color' => 'success']) ?>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <?= view('components/kpi_card', ['icon' => 'bi-box-arrow-right', 'value' => (string) $todayDeparted, 'label' => 'Partis aujourd hui', 'color' => 'warning']) ?>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <?= view('components/kpi_card', ['icon' => 'bi-calendar-week', 'value' => (string) $weekVisitors, 'label' => 'Cette semaine', 'color' => 'primary']) ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent">
                <h2 class="h6 mb-0"><i class="bi bi-hourglass-split me-2"></i>Duree moyenne de visite</h2>
            </div>
            <div class="card-body d-flex flex-column justify-content-center text-center">
                <div class="display-6 fw-bold text-primary mb-2"><?= esc($avgDuration) ?></div>
                <div class="small text-muted">Moyenne calculee sur la semaine en cours.</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent">
                <h2 class="h6 mb-0"><i class="bi bi-list-ul me-2"></i>Motifs principaux</h2>
            </div>
            <div class="card-body">
                <?php if (empty($topMotifs)): ?>
                    <div class="realtime-placeholder py-4">
                        <i class="bi bi-inbox fs-2 text-muted"></i>
                        <p class="text-muted mb-0">Aucune donnee disponible.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($topMotifs as $motif): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-3 px-0 py-3">
                                <span class="small text-break"><?= esc($motif['motif']) ?></span>
                                <span class="badge rounded-pill text-bg-light border text-primary"><?= (int) $motif['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-transparent">
                <h2 class="h6 mb-0"><i class="bi bi-people me-2"></i>Employes les plus visites</h2>
            </div>
            <div class="card-body">
                <?php if (empty($topEmployees)): ?>
                    <div class="realtime-placeholder py-4">
                        <i class="bi bi-inbox fs-2 text-muted"></i>
                        <p class="text-muted mb-0">Aucune donnee disponible.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($topEmployees as $emp): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center gap-3 px-0 py-3">
                                <span class="small text-break"><?= esc($emp['personne_a_voir']) ?></span>
                                <span class="badge rounded-pill text-bg-light border text-success"><?= (int) $emp['count'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h2 class="h6 mb-1"><i class="bi bi-download me-2"></i>Exporter les donnees</h2>
            <div class="small text-muted">Choisissez une plage de dates pour extraire l historique au format CSV.</div>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= base_url('admin/visitors/export-csv') ?>" class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl-3">
                <label for="startDate" class="form-label">Date de debut</label>
                <input type="date" class="form-control" id="startDate" name="start" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <label for="endDate" class="form-label">Date de fin</label>
                <input type="date" class="form-control" id="endDate" name="end" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-12 col-xl-6 d-grid d-md-flex justify-content-xl-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-download me-1"></i>Exporter en CSV
                </button>
            </div>
        </form>
    </div>
</div>
