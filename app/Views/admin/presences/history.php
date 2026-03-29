<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-clock-history"></i>
                Archive des pointages
            </span>
            <h1 class="page-hero-title mb-2">Historique des pointages</h1>
            <p class="page-hero-copy mb-0">Analysez les entrées/sorties sur une période et consultez le détail de chaque pointage.</p>
        </div>
        <a href="<?= site_url('admin/presences/index') ?>" class="btn btn-light border">
            <i class="bi bi-calendar-day me-2"></i>Retour au jour courant
        </a>
    </div>
</section>

<?php
$daysCount = max(1, (int) ((strtotime($dateFin) - strtotime($dateDebut)) / 86400) + 1);
$avgPerDay = (int) round($totalRecords / $daysCount);
?>

    <!-- Filter section -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
            <h2 class="h5 mb-1">Filtres d analyse</h2>
            <p class="text-muted small mb-0">Affinez la periode et ciblez un employe avant de consulter le detail des pointages.</p>
        </div>
        <div class="card-body p-4">
            <form method="GET" class="row g-3">
                <div class="col-12 col-md-4 col-xl-3">
                    <label for="date_debut" class="form-label fw-semibold">Date début</label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="date_debut" 
                        name="date_debut"
                        value="<?= $dateDebut ?>"
                    >
                </div>
                <div class="col-12 col-md-4 col-xl-3">
                    <label for="date_fin" class="form-label fw-semibold">Date fin</label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="date_fin" 
                        name="date_fin"
                        value="<?= $dateFin ?>"
                    >
                </div>
                <div class="col-12 col-md-4 col-xl-3">
                    <label for="employe_id" class="form-label fw-semibold">Employé</label>
                    <select class="form-select" id="employe_id" name="employe_id">
                        <option value="">-- Tous les employes --</option>
                        <?php foreach ($employes as $employe): ?>
                            <option value="<?= $employe['id'] ?>" <?= $employeId === (string)$employe['id'] ? 'selected' : '' ?>>
                                <?= esc($employe['prenom'] . ' ' . $employe['nom']) ?> (<?= esc($employe['matricule']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-xl-3 d-flex align-items-end">
                    <div class="d-grid d-sm-flex gap-2 w-100">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-search me-1"></i>Filtrer
                        </button>
                        <a href="<?= site_url('admin/presences/history') ?>" class="btn btn-outline-secondary flex-fill">
                            <i class="bi bi-arrow-clockwise me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 g-xl-4 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total pointages</h6>
                    <h2 class="mb-0 text-primary"><?= $totalRecords ?></h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Jours analyses</h6>
                    <h2 class="mb-0 text-dark"><?= $daysCount ?></h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Moyenne / jour</h6>
                    <h2 class="mb-0 text-info"><?= $avgPerDay ?></h2>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Page courante</h6>
                    <h2 class="mb-0 text-success"><?= $page ?> / <?= max(1, $pageCount) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Presences table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h2 class="h5 mb-0">Résultats</h2>
            <small class="text-muted">Période: <?= esc($dateDebut) ?> au <?= esc($dateFin) ?></small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <caption class="visually-hidden">Historique des pointages filtré par période et employé.</caption>
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Matricule</th>
                        <th scope="col">Employe</th>
                        <th scope="col">Arrivee</th>
                        <th scope="col">Depart</th>
                        <th scope="col">Statut</th>
                        <th scope="col">Retard</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($presences)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Aucun pointage pour cette période
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($presences as $presence): ?>
                            <tr>
                                <td>
                                    <small><?= (new DateTime($presence['date_pointage']))->format('d/m/Y') ?></small>
                                </td>
                                <td>
                                    <span class="badge-time"><?= esc($presence['matricule'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <strong><?= esc(($presence['prenom'] ?? '') . ' ' . ($presence['nom'] ?? 'Inconnu')) ?></strong>
                                </td>
                                <td>
                                    <?= $presence['heure_pointage'] ? '<span class="badge-time">' . substr($presence['heure_pointage'], 0, 5) . '</span>' : '—' ?>
                                </td>
                                <td>
                                    <?= $presence['heure_sortie'] ? '<span class="badge-time">' . substr($presence['heure_sortie'], 0, 5) . '</span>' : '—' ?>
                                </td>
                                <td>
                                    <?php if ($presence['statut'] === 'present'): ?>
                                        <span class="badge rounded-pill text-bg-success">À l'heure</span>
                                    <?php elseif ($presence['statut'] === 'retard'): ?>
                                        <span class="badge rounded-pill text-bg-warning">Retard</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill text-bg-danger">Absent</span>
                                    <?php endif; ?>
                                    <?php if ($presence['corrige']): ?>
                                        <span class="badge rounded-pill text-bg-info">Corrigé</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $presence['retard_minutes'] ? $presence['retard_minutes'] . ' min' : '—' ?>
                                </td>
                                <td class="text-end">
                                    <button 
                                        type="button" 
                                        class="btn btn-sm btn-outline-primary text-nowrap"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#detailModal"
                                        aria-label="Afficher le détail du pointage de <?= esc(($presence['prenom'] ?? '') . ' ' . ($presence['nom'] ?? 'Inconnu')) ?>"
                                        onclick="viewDetail(<?= $presence['id'] ?>)"
                                    >
                                        <i class="bi bi-eye me-1"></i>Détail
                                    </button>
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
                    <ul class="pagination flex-wrap justify-content-center mb-0 gap-1">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&date_debut=<?= $dateDebut ?>&date_fin=<?= $dateFin ?>&employe_id=<?= $employeId ?>">
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
                                    <a class="page-link" href="?page=<?= $p ?>&date_debut=<?= $dateDebut ?>&date_fin=<?= $dateFin ?>&employe_id=<?= $employeId ?>">
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
                                <a class="page-link" href="?page=<?= $page + 1 ?>&date_debut=<?= $dateDebut ?>&date_fin=<?= $dateFin ?>&employe_id=<?= $employeId ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Détails du pointage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function formatStatutLabel(statut) {
    if (statut === 'present') return "À l'heure";
    if (statut === 'retard') return 'Retard';
    return 'Absent';
}

function viewDetail(presenceId) {
    document.getElementById('detailContent').innerHTML = '<div class="py-3 text-center text-muted"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Chargement des détails...</div>';

    // Load presence details via AJAX
    fetch(`<?= base_url('admin/presences/correct') ?>/${presenceId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Reponse invalide du serveur');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const p = data.presence;
                const statutColor = p.statut === 'present' ? 'success' : p.statut === 'retard' ? 'warning' : 'danger';
                const content = `
                    <dl class="row">
                        <dt class="col-sm-4">Employe:</dt>
                        <dd class="col-sm-8"><strong>${escapeHtml(p.prenom)} ${escapeHtml(p.nom)}</strong></dd>
                        
                        <dt class="col-sm-4">Matricule:</dt>
                        <dd class="col-sm-8"><code>${escapeHtml(p.matricule)}</code></dd>
                        
                        <dt class="col-sm-4">Date:</dt>
                        <dd class="col-sm-8">${new Date(p.date_pointage).toLocaleDateString('fr-FR')}</dd>
                        
                        <dt class="col-sm-4">Arrivee:</dt>
                        <dd class="col-sm-8">${p.heure_pointage ? p.heure_pointage.substring(0, 5) : '—'}</dd>
                        
                        <dt class="col-sm-4">Depart:</dt>
                        <dd class="col-sm-8">${p.heure_sortie ? p.heure_sortie.substring(0, 5) : '—'}</dd>
                        
                        <dt class="col-sm-4">Statut:</dt>
                        <dd class="col-sm-8">
                            <span class="badge text-bg-${statutColor}">
                                ${formatStatutLabel(p.statut)}
                            </span>
                            ${p.corrige ? '<span class="badge text-bg-info ms-2">Corrigé</span>' : ''}
                        </dd>
                        
                        <dt class="col-sm-4">Retard:</dt>
                        <dd class="col-sm-8">${p.retard_minutes ? p.retard_minutes + ' min' : '—'}</dd>

                        <dt class="col-sm-4">Motif correction:</dt>
                        <dd class="col-sm-8">${p.motif_correction ? escapeHtml(p.motif_correction) : '—'}</dd>
                    </dl>
                `;
                document.getElementById('detailContent').innerHTML = content;
            } else {
                document.getElementById('detailContent').innerHTML = '<p class="text-danger mb-0">Impossible de charger les détails de ce pointage.</p>';
                if (typeof window.showToast === 'function') {
                    window.showToast('Impossible de récupérer les détails du pointage.', 'warning');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailContent').innerHTML = '<p class="text-danger mb-0">Erreur réseau lors du chargement des détails.</p>';
            if (typeof window.showToast === 'function') {
                window.showToast('Erreur réseau lors du chargement des détails.', 'danger');
            }
        });
}
</script>