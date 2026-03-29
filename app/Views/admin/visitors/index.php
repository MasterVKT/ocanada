<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0">
                <i class="bi bi-people"></i> Gestion des visiteurs
            </h1>
            <small class="text-muted">Suivi en temps réel des visiteurs sur site</small>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <!-- Filter tabs -->
                <div class="col-12 mb-3">
                    <div class="btn-group" role="group">
                        <a href="?filter=present" class="btn btn-sm <?= $filter === 'present' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="bi bi-person-check"></i> Présents
                        </a>
                        <a href="?filter=today" class="btn btn-sm <?= $filter === 'today' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="bi bi-calendar-event"></i> Aujourd'hui
                        </a>
                        <a href="?filter=week" class="btn btn-sm <?= $filter === 'week' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="bi bi-calendar-range"></i> Cette semaine
                        </a>
                        <a href="?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="bi bi-list"></i> Tous
                        </a>
                    </div>
                </div>

                <!-- Search -->
                <div class="col-md-6">
                    <input 
                        type="text" 
                        class="form-control" 
                        name="search"
                        placeholder="Rechercher par nom, email, motif..."
                        value="<?= esc($searchTerm) ?>"
                    >
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Rechercher
                    </button>
                </div>
                <div class="col-md-4 text-end">
                    <a href="<?= base_url('admin/visitors/export-csv') ?>" class="btn btn-outline-success">
                        <i class="bi bi-download"></i> Exporter CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1">
                        <small><strong>Présents maintenant</strong></small>
                    </div>
                    <div class="h3 mb-0">
                        <?php
                        $presentCount = count(array_filter($visitors, fn($v) => $v['statut'] === 'present'));
                        echo $presentCount;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1">
                        <small><strong>Aujourd'hui</strong></small>
                    </div>
                    <div class="h3 mb-0"><?= $total ?></div>
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
                        <th style="width: 15%">Nom / Prénom</th>
                        <th style="width: 12%">Email</th>
                        <th style="width: 12%">Téléphone</th>
                        <th style="width: 15%">Motif</th>
                        <th style="width: 12%">Personne à voir</th>
                        <th style="width: 10%">Arrivée</th>
                        <th style="width: 10%">Statut</th>
                        <th style="width: 14%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($visitors)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox"></i> Aucun visiteur trouvé
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($visitors as $v): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($v['prenom'] . ' ' . $v['nom']) ?></strong>
                                    <?php if ($v['badge_id']): ?>
                                        <br><small class="text-muted">Badge: <?= esc($v['badge_id']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small><?= esc($v['email']) ?></small>
                                </td>
                                <td>
                                    <small><?= esc($v['telephone']) ?></small>
                                </td>
                                <td>
                                    <small><?= esc($v['motif']) ?></small>
                                </td>
                                <td>
                                    <small><?= esc($v['personne_a_voir']) ?></small>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d/m H:i', strtotime($v['date_creation'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <?php 
                                    if ($v['statut'] === 'present'):
                                        ?>
                                        <span class="badge bg-success">Présent</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Parti</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('admin/visitors/' . $v['id']) ?>" class="btn btn-sm btn-outline-primary" title="Détails">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url('admin/visitors/' . $v['id'] . '/badge') ?>" class="btn btn-sm btn-outline-info" title="Badge QR">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
                                    <?php if ($v['statut'] === 'present'): ?>
                                        <button class="btn btn-sm btn-outline-warning" onclick="checkoutVisitor(<?= $v['id'] ?>)" title="Marquer comme parti">
                                            <i class="bi bi-box-arrow-right"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-light">
                <nav aria-label="Page navigation" class="d-flex justify-content-center">
                    <ul class="pagination pagination-sm">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="?filter=<?= $filter ?>&search=<?= urlencode($searchTerm) ?>&page=<?= $i ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function checkoutVisitor(visitorId) {
    if (!confirm('Confirmer la sortie de ce visiteur ?')) return;

    fetch(`<?= base_url('admin/visitors') ?>/${visitorId}/checkout`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(json => {
        if (json.success) {
            location.reload();
        } else {
            alert('Erreur: ' + json.message);
        }
    });
}
</script>
