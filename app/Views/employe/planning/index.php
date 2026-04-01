<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= esc($title) ?></h1>
        <div>
            <a href="<?= base_url('/employe/planning/month') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-calendar"></i> Vue Mensuelle
            </a>
        </div>
    </div>

    <!-- Week Navigation -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <a href="<?= base_url('/employe/planning?week=' . $prev_week . '&year=' . $prev_year) ?>"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-chevron-left"></i> Semaine Précédente
                    </a>
                </div>
                <div class="col-md-4 text-center">
                    <h5 class="mb-0">
                        Semaine <?= str_pad($week, 2, '0', STR_PAD_LEFT) ?> / <?= $year ?>
                        <br>
                        <small class="text-muted">
                            <?= date('d/m', strtotime($week_start)) ?> - <?= date('d/m/Y', strtotime($week_end)) ?>
                        </small>
                    </h5>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= base_url('/employe/planning?week=' . $next_week . '&year=' . $next_year) ?>"
                        class="btn btn-outline-secondary btn-sm">
                        Semaine Suivante <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule -->
    <div class="row">
        <?php foreach ($days as $date => $day): ?>
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm <?= $day['shift'] ? 'border-success' : 'border-secondary' ?>">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <strong><?= $day['dayFr'] ?></strong>
                            <br>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($date)) ?></small>
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if ($day['shift']): ?>
                            <div>
                                <p class="mb-2">
                                    <strong class="text-success"><?= esc($day['shift']['name']) ?></strong>
                                </p>
                                <div class="alert alert-info mb-0">
                                    <i class="bi bi-clock"></i>
                                    <strong>
                                        <?= date('H:i', strtotime($day['shift']['heure_debut'])) ?> -
                                        <?= date('H:i', strtotime($day['shift']['heure_fin'])) ?>
                                    </strong>
                                    <br>
                                    <small>
                                        <?php
                                        $start = strtotime($day['shift']['heure_debut']);
                                        $end = strtotime($day['shift']['heure_fin']);
                                        if ($end < $start) $end += 86400;
                                        $hours = (int)(($end - $start) / 3600);
                                        $minutes = (int)((($end - $start) % 3600) / 60);
                                        echo $hours . 'h' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
                                        ?>
                                    </small>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center mb-0">
                                <i class="bi bi-dash-circle"></i><br>
                                Pas de shift assigné
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Week Summary -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Résumé de la Semaine</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h6 class="text-muted">Jours assignés</h6>
                            <h3>
                                <?php
                                $assignedDays = count(array_filter($days, fn($d) => $d['shift']));
                                echo $assignedDays;
                                ?>
                            </h3>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Heures totales</h6>
                            <h3>
                                <?php
                                $totalMinutes = 0;
                                foreach ($days as $day) {
                                    if ($day['shift']) {
                                        $start = strtotime($day['shift']['heure_debut']);
                                        $end = strtotime($day['shift']['heure_fin']);
                                        if ($end < $start) $end += 86400;
                                        $totalMinutes += ($end - $start) / 60;
                                    }
                                }
                                $hours = (int)($totalMinutes / 60);
                                $minutes = (int)($totalMinutes % 60);
                                echo $hours . 'h' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
                                ?>
                            </h3>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted">Jours sans assignment</h6>
                            <h3 class="text-warning">
                                <?= 7 - $assignedDays ?>
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>