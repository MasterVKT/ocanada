<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-folder2-open"></i>
                Dossiers RH
            </span>
            <h1 class="page-hero-title mb-2">Documents RH</h1>
            <p class="page-hero-copy mb-0">Centralisez les contrats, attestations et documents administratifs avec filtres par type et collaborateur.</p>
        </div>
        <a href="<?= base_url('admin/documents/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Nouveau document
        </a>
    </div>
</section>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
            <h2 class="h5 mb-1">Filtres</h2>
            <p class="text-muted small mb-0">Affinez la liste par intitulé, type de document ou collaborateur.</p>
        </div>
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="form-label">Recherche</label>
                    <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>" class="form-control" placeholder="Titre / type / description">
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" value="<?= esc($filters['type'] ?? '') ?>" class="form-control" placeholder="Ex: Contrat">
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label">Employé</label>
                    <select name="employe_id" class="form-select">
                        <option value="">Tous</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= ($filters['employe_id'] ?? '') == $emp['id'] ? 'selected' : '' ?>>
                                <?= esc($emp['prenom'] . ' ' . $emp['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-2 d-grid d-md-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i>Filtrer
                    </button>
                    <a href="<?= base_url('admin/documents') ?>" class="btn btn-outline-secondary flex-grow-1">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
            <h2 class="h5 mb-0">Bibliotheque documentaire</h2>
            <span class="badge rounded-pill text-bg-light border text-secondary px-3 py-2"><?= count($documents) ?> ligne(s) visibles</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Employé</th>
                        <th>Téléchargé par</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox"></i> Aucun document trouvé
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($doc['titre']) ?></div>
                                    <div class="small text-muted text-truncate"><?= esc($doc['description'] ?? '') ?></div>
                                </td>
                                <td><span class="badge rounded-pill text-bg-light border"><?= esc($doc['type']) ?></span></td>
                                <td>
                                    <?php if (! empty($doc['employe_nom'])): ?>
                                        <?= esc($doc['employe_prenom'] . ' ' . $doc['employe_nom']) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Général</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($doc['uploader_email'] ?? '---') ?></td>
                                <td><?= date('d/m/Y', strtotime($doc['date_creation'])) ?></td>
                                <td class="text-end">
                                    <div class="btn-group" role="group" aria-label="Actions document">
                                        <a href="<?= base_url('admin/documents/' . $doc['id'] . '/download') ?>" class="btn btn-sm btn-outline-primary" title="Télécharger">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <a href="<?= base_url('admin/documents/' . $doc['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="<?= base_url('admin/documents/' . $doc['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Supprimer ce document ?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pager['lastPage'] > 1): ?>
            <div class="card-footer bg-light">
                <nav aria-label="Pagination">
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $pager['lastPage']; $i++): ?>
                            <li class="page-item <?= $i === $pager['currentPage'] ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&amp;search=<?= urlencode($filters['search'] ?? '') ?>&amp;type=<?= urlencode($filters['type'] ?? '') ?>&amp;employe_id=<?= urlencode($filters['employe_id'] ?? '') ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
