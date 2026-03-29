<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Gestion des Congés</h1>
        <div>
            <span class="badge bg-primary"><?= $totalCount ?></span>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select" onchange="this.form.submit()">
                        <option value="tous" <?= ($status === 'tous') ? 'selected' : '' ?>>Tous les statuts</option>
                        <option value="en_attente" <?= ($status === 'en_attente') ? 'selected' : '' ?>>En attente</option>
                        <option value="approuvee" <?= ($status === 'approuvee') ? 'selected' : '' ?>>Approuvees</option>
                        <option value="refusee" <?= ($status === 'refusee') ? 'selected' : '' ?>>Refusees</option>
                        <option value="annulee" <?= ($status === 'annulee') ? 'selected' : '' ?>>Annulees</option>
                    </select>
                </div>
                <div class="col-md-8 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <?= view('components/kpi_card', [
                'icon' => 'bi-hourglass-split',
                'value' => $stats['pending'] ?? 0,
                'label' => 'En attente',
                'color' => 'warning',
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= view('components/kpi_card', [
                'icon' => 'bi-check-circle-fill',
                'value' => $stats['approved'] ?? 0,
                'label' => 'Approuvées',
                'color' => 'success',
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= view('components/kpi_card', [
                'icon' => 'bi-x-circle-fill',
                'value' => $stats['rejected'] ?? 0,
                'label' => 'Refusées',
                'color' => 'danger',
            ]) ?>
        </div>
        <div class="col-md-3">
            <?= view('components/kpi_card', [
                'icon' => 'bi-slash-circle',
                'value' => $stats['cancelled'] ?? 0,
                'label' => 'Annulées',
                'color' => 'secondary',
            ]) ?>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">Demandes de Congé</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 20%">Employé</th>
                        <th style="width: 15%">Type</th>
                        <th style="width: 15%">Période</th>
                        <th style="width: 10%" class="text-center">Jours</th>
                        <th style="width: 15%">Soumise le</th>
                        <th style="width: 12%" class="text-center">Statut</th>
                        <th style="width: 13%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Aucune demande trouvée
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <tr class="<?= ($request['statut'] ?? '') === 'refuse' ? 'table-danger' : '' ?>">
                                <td>
                                    <strong><?= esc($request['prenom'] . ' ' . $request['nom']) ?></strong><br>
                                    <small class="text-muted"><?= esc($request['matricule']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= esc(ucfirst(str_replace('_', ' ', (string) ($request['type_conge'] ?? '')))) ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <?= date('d/m/Y', strtotime($request['date_debut'])) ?> 
                                        <strong>à</strong> 
                                        <?= date('d/m/Y', strtotime($request['date_fin'])) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <strong><?= number_format((float) ($request['jours_ouvrables'] ?? $request['nombre_jours'] ?? 0), 1, ',', '') ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime((string) ($request['date_soumission'] ?? $request['date_demande'] ?? 'now'))) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php if ($request['statut'] === 'en_attente'): ?>
                                        <span class="badge bg-warning">En attente</span>
                                    <?php elseif ($request['statut'] === 'approuve'): ?>
                                        <span class="badge bg-success">Approuvee</span>
                                    <?php elseif ($request['statut'] === 'refuse'): ?>
                                        <span class="badge bg-danger">Refusee</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Annulee</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('/admin/leaves/' . $request['id']) ?>" class="btn btn-sm btn-light">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <?php if ($request['statut'] === 'en_attente'): ?>
                                        <button class="btn btn-sm btn-light" 
                                                onclick="approveRequest(<?= $request['id'] ?>, '<?= addslashes($request['prenom']) ?>')">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        <button class="btn btn-sm btn-light" 
                                                onclick="rejectRequest(<?= $request['id'] ?>, '<?= addslashes($request['prenom']) ?>')">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($pageCount > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('/admin/leaves?statut=' . $status . '&page=1') ?>">
                            Première
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('/admin/leaves?statut=' . $status . '&page=' . ($page - 1)) ?>">
                            Précédente
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($pageCount, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('/admin/leaves?statut=' . $status . '&page=' . $i) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $pageCount): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('/admin/leaves?statut=' . $status . '&page=' . ($page + 1)) ?>">
                            Suivante
                        </a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('/admin/leaves?statut=' . $status . '&page=' . $pageCount) ?>">
                            Dernière
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Modals -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approuver la demande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong id="approveEmployeeName"></strong> aura acces a ses jours de conge des maintenant.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire (optionnel)</label>
                        <textarea name="commentary" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-success" onclick="submitApprove()">Approuver</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Refuser la demande</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Motif du refus</label>
                        <textarea name="motif_refus" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire</label>
                        <textarea name="commentary" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" onclick="submitReject()">Refuser</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentRequestId = null;

function approveRequest(requestId, employeeName) {
    currentRequestId = requestId;
    document.getElementById('approveEmployeeName').textContent = employeeName;
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function rejectRequest(requestId, employeeName) {
    currentRequestId = requestId;
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function submitApprove() {
    const form = document.getElementById('approveForm');
    const formData = new FormData(form);
    
    fetch(`<?= base_url('/admin/leaves/') ?>${currentRequestId}/approve`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Congé approuvé');
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    });
}

function submitReject() {
    const form = document.getElementById('rejectForm');
    const formData = new FormData(form);
    
    fetch(`<?= base_url('/admin/leaves/') ?>${currentRequestId}/reject`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Congé refusé');
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    });
}
</script>
