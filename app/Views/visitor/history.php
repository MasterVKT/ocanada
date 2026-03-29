<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0">
                <i class="bi bi-clock-history"></i> Historique des Visites
            </h1>
        </div>
    </div>

    <!-- Filter section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="date_debut" class="form-label">Date début</label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="date_debut" 
                        name="date_debut"
                        value="<?= $dateDebut ?>"
                    >
                </div>
                <div class="col-md-3">
                    <label for="date_fin" class="form-label">Date fin</label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="date_fin" 
                        name="date_fin"
                        value="<?= $dateFin ?>"
                    >
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrer
                    </button>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <a href="<?= base_url('visitor/history') ?>" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total visites</h6>
                    <h2 class="mb-0 text-primary"><?= $totalRecords ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Page actuelle</h6>
                    <h2 class="mb-0 text-info"><?= $page ?> / <?= $pageCount ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Par page</h6>
                    <h2 class="mb-0 text-warning"><?= count($visitors) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Période</h6>
                    <small class="text-dark"><?= $dateDebut ?> à <?= $dateFin ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Visitors table -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Badge</th>
                        <th>Visiteur</th>
                        <th>Entreprise</th>
                        <th>Motif</th>
                        <th>Personne à voir</th>
                        <th>Arrivée</th>
                        <th>Départ</th>
                        <th>Durée</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($visitors)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Aucune visite pour cette période
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($visitors as $visitor): ?>
                            <tr>
                                <td>
                                    <code class="bg-light px-2 py-1"><?= esc($visitor['badge_id']) ?></code>
                                </td>
                                <td>
                                    <strong><?= esc($visitor['prenom'] . ' ' . $visitor['nom']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= esc($visitor['email']) ?></small>
                                </td>
                                <td><?= esc($visitor['entreprise'] ?? '-') ?></td>
                                <td>
                                    <span class="badge bg-info"><?= esc($visitor['motif']) ?></span>
                                </td>
                                <td><?= esc($visitor['personne_a_voir']) ?></td>
                                <td><?= substr($visitor['heure_arrivee'], 0, 5) ?></td>
                                <td>
                                    <?= $visitor['heure_depart'] ? substr($visitor['heure_depart'], 0, 5) : '-' ?>
                                </td>
                                <td>
                                    <?php 
                                        if ($visitor['heure_depart']) {
                                            $arrival = DateTime::createFromFormat('H:i:s', $visitor['heure_arrivee']);
                                            $departure = DateTime::createFromFormat('H:i:s', $visitor['heure_depart']);
                                            $diff = $departure->diff($arrival);
                                            $duration = $diff->h . 'h ' . $diff->i . 'min';
                                            echo $duration;
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php if ($visitor['statut'] === 'present'): ?>
                                        <span class="badge bg-success">Présent</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Parti</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($pageCount > 1): ?>
            <div class="card-footer bg-light d-flex justify-content-center">
                <nav>
                    <ul class="pagination mb-0">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&date_debut=<?= $dateDebut ?>&date_fin=<?= $dateFin ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($p = 1; $p <= $pageCount; $p++): ?>
                            <?php if ($p === $page): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?= $p ?></span>
                                </li>
                            <?php elseif ($p === 1 || $p === $pageCount || abs($p - $page) <= 2): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $p ?>&date_debut=<?= $dateDebut ?>&date_fin=<?= $dateFin ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php elseif (abs($p - $page) === 3): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($page < $pageCount): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&date_debut=<?= $dateDebut ?>&date_fin=<?= $dateFin ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

    <!-- Back button -->
    <div class="mt-4">
        <a href="<?= base_url('visitor/index') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Retour à l'enregistrement
        </a>
    </div>
</div>