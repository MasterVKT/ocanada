<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-bar-chart"></i>
                Analyse des présences
            </span>
            <h1 class="page-hero-title mb-2">Statistiques des pointages</h1>
            <p class="page-hero-copy mb-0">Visualisez les tendances quotidiennes et identifiez les profils avec retards répétés.</p>
        </div>
        <a href="<?= site_url('admin/presences/index') ?>" class="btn btn-light border">
            <i class="bi bi-arrow-left me-2"></i>Retour aux pointages
        </a>
    </div>
</section>

<?php
$totalRows = count($dailyStats);
$sumTotal = 0;
$sumPresent = 0;
$sumRetard = 0;
$sumAbsent = 0;

foreach ($dailyStats as $stat) {
    $sumTotal += (int) $stat['total'];
    $sumPresent += (int) $stat['presents'];
    $sumRetard += (int) $stat['retards'];
    $sumAbsent += (int) $stat['absents'];
}

$presenceRate = $sumTotal > 0 ? round((($sumPresent + $sumRetard) / $sumTotal) * 100) : 0;
$retardRate = $sumTotal > 0 ? round(($sumRetard / $sumTotal) * 100) : 0;
?>

<!-- Date range selector -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-4 col-lg-3">
                <label for="date_debut" class="form-label fw-semibold">Date début</label>
                <input
                    type="date"
                    class="form-control"
                    id="date_debut"
                    name="date_debut"
                    value="<?= $dateDebut ?>">
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <label for="date_fin" class="form-label">Date fin</label>
                <input
                    type="date"
                    class="form-control"
                    id="date_fin"
                    name="date_fin"
                    value="<?= $dateFin ?>">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= site_url('admin/presences/statistics') ?>" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-clockwise me-1"></i>Réinitialiser
                </a>
            </div>
            <div class="col-12 col-md-8 col-lg-2">
                <a href="<?= site_url('admin/presences/index') ?>" class="btn btn-outline-info w-100">
                    <i class="bi bi-calendar-today me-1"></i>Aujourd'hui
                </a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 g-xl-4 mb-4">
    <?= view('components/kpi_card', [
        'icon' => 'bi-calendar3',
        'value' => $totalRows,
        'label' => 'Jours analysés',
        'color' => 'primary',
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-check2-circle',
        'value' => $presenceRate . '%',
        'label' => 'Taux de présence',
        'color' => 'success',
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-hourglass-split',
        'value' => $sumRetard,
        'label' => 'Retards cumulés',
        'color' => 'warning',
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-person-x',
        'value' => $sumAbsent,
        'label' => 'Absences cumulées',
        'color' => 'danger',
    ]) ?>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="h6 text-uppercase text-muted mb-2">Tendance de retard</h2>
                <div class="d-flex align-items-end gap-2">
                    <span class="display-6 mb-0 fw-semibold"><?= $retardRate ?>%</span>
                    <span class="text-muted pb-2">des pointages sur la période</span>
                </div>
            </div>
            <div class="w-100" style="max-width: 420px;">
                <div class="progress" role="progressbar" aria-label="Taux de retard" aria-valuenow="<?= $retardRate ?>" aria-valuemin="0" aria-valuemax="100" style="height: 12px;">
                    <div class="progress-bar bg-warning" style="width: <?= $retardRate ?>%;"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted mt-2">
                    <span>0%</span>
                    <span>Seuil de vigilance: 15%</span>
                    <span>100%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily statistics -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header">
        <h2 class="h5 mb-0">
            <i class="bi bi-calendar-week"></i> Statistiques quotidiennes
        </h2>
    </div>
    <div class="card-body">
        <?php if (empty($dailyStats)): ?>
            <p class="text-muted text-center py-4">Aucune donnée pour cette période</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <caption class="visually-hidden">Statistiques quotidiennes des présences avec ventilation présents, retards et absents.</caption>
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">A l'heure</th>
                            <th class="text-center">Retards</th>
                            <th class="text-center">Absents</th>
                            <th scope="col">Graphique</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailyStats as $stat): ?>
                            <?php $dayTotal = max(1, (int) $stat['total']); ?>
                            <tr>
                                <td>
                                    <strong><?= (new DateTime($stat['date']))->format('d/m/Y (D)') ?></strong>
                                </td>
                                <td class="text-center">
                                    <strong><?= $stat['total'] ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= $stat['presents'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning"><?= $stat['retards'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= $stat['absents'] ?></span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 10px;" role="progressbar" aria-label="Répartition journalière de présence pour le <?= (new DateTime($stat['date']))->format('d/m/Y') ?>" aria-valuenow="<?= (int) round(($stat['presents'] / $dayTotal * 100)) ?>" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar bg-success" style="width: <?= ($stat['presents'] / $dayTotal * 100) ?>%;" title="A l'heure"></div>
                                        <div class="progress-bar bg-warning" style="width: <?= ($stat['retards'] / $dayTotal * 100) ?>%;" title="Retards"></div>
                                        <div class="progress-bar bg-danger" style="width: <?= ($stat['absents'] / $dayTotal * 100) ?>%;" title="Absents"></div>
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

<!-- Top employees with tardiness -->
<div class="card border-0 shadow-sm">
    <div class="card-header">
        <h2 class="h5 mb-0">
            <i class="bi bi-exclamation-triangle"></i> Top 20 employés avec le plus de retards
        </h2>
    </div>
    <div class="card-body">
        <?php if (empty($employeeStats)): ?>
            <p class="text-muted text-center py-4">Aucune donnée pour cette période</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm mb-0 align-middle">
                    <caption class="visually-hidden">Classement des employés selon leur taux de retard sur la période.</caption>
                    <thead>
                        <tr>
                            <th scope="col">Employé</th>
                            <th class="text-center">Matricule</th>
                            <th class="text-center">Total pointages</th>
                            <th class="text-center">À l'heure</th>
                            <th class="text-center">Retards</th>
                            <th class="text-center">Absents</th>
                            <th scope="col">Taux retard</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employeeStats as $stat): ?>
                            <?php
                            $employeeName = trim(implode(' ', array_filter([
                                (string) ($stat['prenom'] ?? ''),
                                (string) ($stat['nom'] ?? ''),
                            ])));
                            $employeeName = $employeeName !== '' ? $employeeName : 'Employe introuvable';
                            $employeeMatricule = trim((string) ($stat['matricule'] ?? ''));
                            $employeeMatricule = $employeeMatricule !== '' ? $employeeMatricule : 'Matricule indisponible';
                            ?>
                            <tr>
                                <td>
                                    <strong><?= esc($employeeName) ?></strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge-time"><?= esc($employeeMatricule) ?></span>
                                </td>
                                <td class="text-center">
                                    <?= $stat['total'] ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success"><?= $stat['presents'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning"><?= $stat['retards'] ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= $stat['absents'] ?></span>
                                </td>
                                <td>
                                    <?php
                                    $taux = $stat['total'] > 0 ? round(($stat['retards'] / $stat['total']) * 100) : 0;
                                    $color = $taux > 20 ? 'danger' : ($taux > 10 ? 'warning' : 'success');
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= $taux ?>%</span>
                                    <div class="progress" style="height: 5px; margin-top: 3px;">
                                        <div class="progress-bar bg-<?= $color ?>" style="width: <?= $taux ?>%;"></div>
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
    <a href="<?= site_url('admin/presences/index') ?>" class="btn btn-outline-primary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux pointages du jour
    </a>
</div>