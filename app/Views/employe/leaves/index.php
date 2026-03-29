<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Mes Demandes de Congé</h1>
        <a href="<?= base_url('/employe/leaves/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouvelle Demande
        </a>
    </div>

    <!-- Solde Summary -->
    <?php if ($solde): ?>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Solde Initial</h6>
                        <h3 class="mb-0"><?= number_format($solde['solde_annuel'], 1, ',', '') ?> j</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Utilisé</h6>
                        <h3 class="text-warning mb-0"><?= number_format($solde['pris'], 1, ',', '') ?> j</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Restant</h6>
                        <h3 class="text-success mb-0"><?= number_format($solde['restant'], 1, ',', '') ?> j</h3>
                        <small class="text-muted">
                            (<?= round(($solde['pris'] / $solde['solde_annuel']) * 100) ?>% utilisé)
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-warning" role="progressbar" 
                         style="width: <?= round(($solde['pris'] / $solde['solde_annuel']) * 100) ?>%">
                        <?= round(($solde['pris'] / $solde['solde_annuel']) * 100) ?>%
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Filtrer par statut</label>
                    <select name="statut" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" <?= ($filter_status === 'en_attente') ? 'selected' : '' ?>>En attente</option>
                        <option value="approuve" <?= ($filter_status === 'approuve' || $filter_status === 'approuvee') ? 'selected' : '' ?>>Approuvées</option>
                        <option value="refuse" <?= ($filter_status === 'refuse' || $filter_status === 'refusee') ? 'selected' : '' ?>>Refusées</option>
                        <option value="annule" <?= ($filter_status === 'annule' || $filter_status === 'annulee') ? 'selected' : '' ?>>Annulées</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests List -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">Historique des Demandes (<?= $totalCount ?> demandes)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Période</th>
                        <th>Type</th>
                        <th class="text-center">Jours</th>
                        <th>Soumis le</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox"></i> Aucune demande
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $request): ?>
                            <tr class="<?= $request['statut'] === 'refuse' ? 'table-danger' : '' ?>">
                                <td>
                                    <strong>
                                        <?= date('d/m', strtotime($request['date_debut'])) ?> 
                                        à 
                                        <?= date('d/m/Y', strtotime($request['date_fin'])) ?>
                                    </strong>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= ucfirst(str_replace('_', ' ', $request['type_conge'])) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <strong><?= number_format($request['jours_ouvrables'], 1, ',', '') ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($request['date_soumission'])) ?>
                                    </small>
                                </td>
                                <td class="text-center">
                                    <?php if ($request['statut'] === 'en_attente'): ?>
                                        <span class="badge bg-warning">En attente</span>
                                    <?php elseif ($request['statut'] === 'approuve'): ?>
                                        <span class="badge bg-success">Approuvée</span>
                                    <?php elseif ($request['statut'] === 'refuse'): ?>
                                        <span class="badge bg-danger">Refusée</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Annulée</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('/employe/leaves/' . $request['id']) ?>" class="btn btn-sm btn-light">
                                        <i class="bi bi-eye"></i>
                                    </a>
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
                        <a class="page-link" href="<?= base_url('/employe/leaves?page=1' . ($filter_status !== '' ? '&statut=' . urlencode($filter_status) : '')) ?>">Première</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('/employe/leaves?page=' . ($page - 1) . ($filter_status !== '' ? '&statut=' . urlencode($filter_status) : '')) ?>">Précédente</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($pageCount, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= base_url('/employe/leaves?page=' . $i . ($filter_status !== '' ? '&statut=' . urlencode($filter_status) : '')) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $pageCount): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('/employe/leaves?page=' . ($page + 1) . ($filter_status !== '' ? '&statut=' . urlencode($filter_status) : '')) ?>">Suivante</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="<?= base_url('/employe/leaves?page=' . $pageCount . ($filter_status !== '' ? '&statut=' . urlencode($filter_status) : '')) ?>">Dernière</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php $this->endSection(); ?>
