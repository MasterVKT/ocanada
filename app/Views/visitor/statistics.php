<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0">
                <i class="bi bi-bar-chart"></i> Statistiques des Visites
            </h1>
        </div>
    </div>

    <!-- Date selector -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date" class="form-label">Sélectionner une date</label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="date" 
                        name="date"
                        value="<?= $date ?>"
                    >
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Afficher
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="?date=<?= date('Y-m-d') ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-calendar-today"></i> Aujourd'hui
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Total visites</h6>
                            <h2 class="mb-0 text-primary"><?= $stats['total'] ?></h2>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-people-fill" style="font-size: 2rem; color: #0d6efd;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Actuellement présents</h6>
                            <h2 class="mb-0 text-success"><?= $stats['presents'] ?></h2>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-check-circle-fill" style="font-size: 2rem; color: #198754;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Partis</h6>
                            <h2 class="mb-0 text-info"><?= $stats['partis'] ?></h2>
                        </div>
                        <div class="ms-auto">
                            <i class="bi bi-door-open" style="font-size: 2rem; color: #0dcaf0;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitors by motif -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-list"></i> Visites par motif
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($summary)): ?>
                <p class="text-muted text-center py-4">Aucune donnée pour cette date</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Motif</th>
                                <th>Nombre de visites</th>
                                <th>Pourcentage</th>
                                <th>Barre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summary as $item): ?>
                                <tr>
                                    <td>
                                        <strong><?= esc($item['motif']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= $item['count'] ?></span>
                                    </td>
                                    <td>
                                        <?php $percentage = ($stats['total'] > 0) ? round(($item['count'] / $stats['total']) * 100) : 0; ?>
                                        <?= $percentage ?>%
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div 
                                                class="progress-bar" 
                                                role="progressbar" 
                                                style="width: <?= $percentage ?>%;"
                                                aria-valuenow="<?= $percentage ?>" 
                                                aria-valuemin="0" 
                                                aria-valuemax="100"
                                            >
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back button -->
    <div class="mt-4">
        <a href="<?= base_url('visitor/index') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Retour à l'enregistrement
        </a>
    </div>
</div>