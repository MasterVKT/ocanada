<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3"><?= esc($title) ?></h1>
        <div>
            <a href="<?= base_url('/employe/planning') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-calendar-week"></i> Vue Hebdomadaire
            </a>
        </div>
    </div>

    <!-- Month Navigation -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <a href="<?= base_url('/employe/planning/month?month=' . $prev_month) ?>"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-chevron-left"></i> Mois Précédent
                    </a>
                </div>
                <div class="col-md-4 text-center">
                    <h5 class="mb-0">
                        <?= date('F Y', strtotime($month . '-01')) == 'January 2025' ? 'January ' . date('Y', strtotime($month . '-01')) : date('F Y', strtotime($month . '-01')) ?>
                        <br>
                        <small class="text-muted"><?= strftime('%B %Y', strtotime($month . '-01')) ?></small>
                    </h5>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= base_url('/employe/planning/month?month=' . $next_month) ?>"
                        class="btn btn-outline-secondary btn-sm">
                        Mois Suivant <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-bordered mb-0" style="table-layout: fixed;">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 14.28%;">Lun</th>
                        <th class="text-center" style="width: 14.28%;">Mar</th>
                        <th class="text-center" style="width: 14.28%;">Mer</th>
                        <th class="text-center" style="width: 14.28%;">Jeu</th>
                        <th class="text-center" style="width: 14.28%;">Ven</th>
                        <th class="text-center" style="width: 14.28%;">Sam</th>
                        <th class="text-center" style="width: 14.28%;">Dim</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($calendar as $week): ?>
                        <tr style="height: 100px;">
                            <?php foreach ($week as $day): ?>
                                <td class="<?= !$day['inMonth'] ? 'bg-light' : '' ?>">
                                    <div class="p-2">
                                        <strong class="<?= !$day['inMonth'] ? 'text-muted' : '' ?>">
                                            <?= $day['day'] ?>
                                        </strong>
                                        <?php if ($day['inMonth']): ?>
                                            <div class="mt-2">
                                                <?php foreach ($day['shifts'] as $shift): ?>
                                                    <div class="badge bg-success mb-1">
                                                        <small><?= esc($shift['shift_name']) ?></small><br>
                                                        <small><?= date('H:i', strtotime($shift['heure_debut'])) ?></small>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legend -->
    <div class="mt-3">
        <small class="text-muted">
            <i class="bi bi-info-circle"></i>
            Les jours grisés ne font pas partie du mois sélectionné.
            Les shifts sont affichés en vert.
        </small>
    </div>
</div>