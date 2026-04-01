<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0">
                <i class="bi bi-clock-history"></i> Historique visiteurs
            </h1>
            <small class="text-muted">Enregistrements du mois</small>
        </div>
    </div>

    <!-- Month Navigation -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="month" class="form-label">Mois</label>
                    <input
                        type="month"
                        class="form-control"
                        id="month"
                        name="month"
                        value="<?= $month ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Afficher
                    </button>
                </div>
                <div class="col-md-7 text-end">
                    <a href="<?= base_url('agent/visitors/register') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus"></i> Enregistrer un visiteur
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-info h-100 py-2">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1">
                        <small><strong>Total visiteurs</strong></small>
                    </div>
                    <div class="h3 mb-0"><?= $totalVisitors ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1">
                        <small><strong>Durée moyenne</strong></small>
                    </div>
                    <div class="h3 mb-0"><?= $avgDuration ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1">
                        <small><strong>Mois sélectionné</strong></small>
                    </div>
                    <div class="h3 mb-0">
                        <?php
                        $monthFormat = strtotime($month . '-01');
                        echo date('M Y', $monthFormat) === 'Jan 99' ? 'Sélectionnez' : date('M Y', $monthFormat);
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitors Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-table"></i> Liste des visiteurs</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom / Prénom</th>
                        <th>Badge</th>
                        <th>Motif</th>
                        <th>Personne à voir</th>
                        <th>Date</th>
                        <th>Arrivée</th>
                        <th>Départ</th>
                        <th>Durée</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($visitors)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox"></i> Aucun visiteur pour cette période
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($visitors as $v): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($v['prenom'] . ' ' . $v['nom']) ?></strong>
                                </td>
                                <td>
                                    <small class="text-monospace"><?= esc($v['badge_id'] ?? '--') ?></small>
                                </td>
                                <td>
                                    <small><?= esc($v['motif']) ?></small>
                                </td>
                                <td>
                                    <small><?= esc($v['personne_a_voir']) ?></small>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($v['date_creation'])) ?></small>
                                </td>
                                <td>
                                    <small><?= substr($v['heure_arrivee'], 0, 5) ?></small>
                                </td>
                                <td>
                                    <small><?= $v['heure_depart'] ? substr($v['heure_depart'], 0, 5) : '-' ?></small>
                                </td>
                                <td>
                                    <?php
                                    if ($v['heure_arrivee'] && $v['heure_depart']) {
                                        $arrivalTs = strtotime('1970-01-01 ' . $v['heure_arrivee']);
                                        $departureTs = strtotime('1970-01-01 ' . $v['heure_depart']);

                                        if ($arrivalTs !== false && $departureTs !== false && $departureTs < $arrivalTs) {
                                            $departureTs += 86400;
                                        }

                                        if ($arrivalTs !== false && $departureTs !== false) {
                                            $durationSeconds = max(0, $departureTs - $arrivalTs);
                                            $duration = sprintf('%02d:%02d', intdiv($durationSeconds, 3600), intdiv($durationSeconds % 3600, 60));
                                        } else {
                                            $duration = '--';
                                        }
                                        echo '<small>' . $duration . '</small>';
                                    } else {
                                        echo '<small class="text-muted">--</small>';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .border-left-info {
        border-left: 4px solid #0dcaf0 !important;
    }

    .border-left-success {
        border-left: 4px solid #198754 !important;
    }

    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    .text-monospace {
        font-family: monospace;
    }
</style>