<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-calendar3-week"></i>
                Organisation hebdomadaire
            </span>
            <h1 class="page-hero-title mb-2"><?= esc($title) ?></h1>
            <p class="page-hero-copy mb-0">Visualisez les affectations de shifts et naviguez d une semaine a l autre sans quitter la vue calendrier.</p>
        </div>
        <a href="<?= base_url('/admin/planning/shifts') ?>" class="btn btn-primary">
            <i class="bi bi-wrench me-2"></i>Gerer les shifts
        </a>
    </div>
</section>

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-12 col-lg-4 d-grid d-lg-block">
                <a href="<?= base_url('/admin/planning?week=' . $prev_week . '&year=' . $prev_year) ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-chevron-left"></i> Semaine precedente
                </a>
            </div>
            <div class="col-12 col-lg-4 text-center">
                <h5 class="mb-0">
                    Semaine <?= str_pad($week, 2, '0', STR_PAD_LEFT) ?> / <?= $year ?>
                    <br>
                    <small class="text-muted">
                        <?= date('d/m', strtotime($week_start)) ?> - <?= date('d/m/Y', strtotime($week_end)) ?>
                    </small>
                </h5>
            </div>
            <div class="col-12 col-lg-4 text-lg-end d-grid d-lg-block">
                <a href="<?= base_url('/admin/planning?week=' . $next_week . '&year=' . $next_year) ?>" class="btn btn-outline-secondary btn-sm">
                    Semaine suivante <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h2 class="h5 mb-0">Planning hebdomadaire</h2>
        <span class="badge rounded-pill text-bg-light border text-secondary px-3 py-2"><?= count($employees) ?> employe(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 18%">Employe</th>
                    <?php foreach ($days as $date => $day): ?>
                        <th class="text-center">
                            <strong><?= esc($day['dayFr']) ?></strong><br>
                            <small class="text-muted"><?= date('d/m', strtotime($date)) ?></small>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= esc($employee['prenom'] . ' ' . $employee['nom']) ?></div>
                            <div class="small text-muted"><?= esc($employee['poste']) ?></div>
                        </td>
                        <?php foreach ($days as $date => $day): ?>
                            <td class="text-center">
                                <?php
                                $empShift = null;
                                foreach ($affectations as $aff) {
                                    if ((int) $aff['employe_id'] === (int) $employee['id']) {
                                        $empShift = $aff;
                                        break;
                                    }
                                }
                                ?>
                                <?php if ($empShift): ?>
                                    <span class="badge rounded-pill text-bg-success">
                                        <?= esc($empShift['shift_name']) ?>
                                    </span>
                                    <div class="small text-muted mt-1">
                                        <?= date('H:i', strtotime($empShift['heure_debut'])) ?> - <?= date('H:i', strtotime($empShift['heure_fin'])) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
        <h2 class="h5 mb-0">Shifts disponibles</h2>
        <span class="small text-muted"><?= count($shifts) ?> modele(s)</span>
    </div>
    <div class="row g-3">
        <?php foreach ($shifts as $shift): ?>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-2"><?= esc($shift['nom']) ?></h6>
                        <p class="card-text mb-2">
                            <i class="bi bi-clock me-1"></i>
                            <?= date('H:i', strtotime($shift['heure_debut'])) ?> - <?= date('H:i', strtotime($shift['heure_fin'])) ?>
                        </p>
                        <small class="text-muted">Pause: <?= isset($shift['pause_minutes']) ? (int) $shift['pause_minutes'] . ' min' : '-' ?></small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
