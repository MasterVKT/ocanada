<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-shield-check"></i>
                Traçabilite
            </span>
            <h1 class="page-hero-title mb-2">Journal d audit</h1>
            <p class="page-hero-copy mb-0">Consultez les traces systeme et les actions sensibles des utilisateurs.</p>
        </div>
    </div>
</section>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><?= esc((string) session('success')) ?></div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><?= esc((string) session('error')) ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
        <h2 class="h5 mb-1">Filtres</h2>
        <p class="text-muted small mb-0">Filtrez les evenements sensibles par type ou par contenu.</p>
    </div>
    <div class="card-body p-4">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="type" class="form-label">Type d evenement</label>
                <select id="type" name="type" class="form-select">
                    <option value="">Tous</option>
                    <?php foreach (($types ?? []) as $type): ?>
                        <option value="<?= esc((string) $type) ?>" <?= (($filters['type'] ?? '') === $type) ? 'selected' : '' ?>>
                            <?= esc((string) $type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label for="q" class="form-label">Recherche</label>
                <input id="q" type="text" class="form-control" name="q" value="<?= esc((string) ($filters['q'] ?? '')) ?>" placeholder="Type, description, email, utilisateur">
            </div>
            <div class="col-12 col-xl-2 d-grid d-md-flex d-xl-grid gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Filtrer</button>
                <a class="btn btn-outline-secondary flex-fill" href="<?= site_url('admin/audit/export-csv?type=' . urlencode((string) ($filters['type'] ?? '')) . '&q=' . urlencode((string) ($filters['q'] ?? ''))) ?>">Exporter CSV</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h2 class="h6 mb-0">Entrees d audit</h2>
        <span class="small text-muted">Total: <?= (int) ($total ?? 0) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Utilisateur</th>
                    <th>Description</th>
                    <th>IP</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucune entree.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td class="small"><?= esc((string) ($row['date_evenement'] ?? '')) ?></td>
                            <td><span class="badge text-bg-light"><?= esc((string) ($row['type_evenement'] ?? '')) ?></span></td>
                            <td>
                                <div class="fw-semibold"><?= esc(trim(((string) ($row['prenom'] ?? '')) . ' ' . ((string) ($row['nom'] ?? '')))) ?></div>
                                <div class="small text-muted"><?= esc((string) ($row['email'] ?? '')) ?></div>
                            </td>
                            <td class="small text-break"><?= esc((string) ($row['description'] ?? '')) ?></td>
                            <td class="small"><?= esc((string) ($row['ip_adresse'] ?? '')) ?></td>
                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-light border audit-detail-trigger"
                                    data-detail-url="<?= site_url('admin/audit/' . (int) ($row['id'] ?? 0)) ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#auditDetailModal"
                                >
                                    Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($lastPage ?? 1) > 1): ?>
    <nav class="mt-4" aria-label="Pagination audit">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= (int) $lastPage; $i++): ?>
                <li class="page-item <?= ($i === (int) ($page ?? 1)) ? 'active' : '' ?>">
                    <a class="page-link" href="<?= site_url('admin/audit?type=' . urlencode((string) ($filters['type'] ?? '')) . '&q=' . urlencode((string) ($filters['q'] ?? '')) . '&page=' . $i) ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5 mb-0">Detail audit</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div id="audit-detail-loading" class="text-muted">Chargement...</div>
                <div id="audit-detail-content" class="d-none">
                    <div class="mb-3">
                        <div class="small text-muted">Evenement</div>
                        <div id="audit-detail-type" class="fw-semibold"></div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted">Utilisateur</div>
                        <div id="audit-detail-user" class="fw-semibold"></div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-muted">Description</div>
                        <div id="audit-detail-description"></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-6">
                            <div class="small text-muted mb-2">Donnees avant</div>
                            <pre id="audit-detail-before" class="bg-light border rounded p-3 small mb-0 text-wrap" style="white-space: pre-wrap; word-break: break-word;"></pre>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="small text-muted mb-2">Donnees apres</div>
                            <pre id="audit-detail-after" class="bg-light border rounded p-3 small mb-0 text-wrap" style="white-space: pre-wrap; word-break: break-word;"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.audit-detail-trigger').forEach((button) => {
    button.addEventListener('click', async () => {
        const url = button.getAttribute('data-detail-url');
        const loading = document.getElementById('audit-detail-loading');
        const content = document.getElementById('audit-detail-content');

        loading.classList.remove('d-none');
        content.classList.add('d-none');
        loading.textContent = 'Chargement...';

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Erreur');
            }

            document.getElementById('audit-detail-type').textContent = data.row.type_evenement || '';
            document.getElementById('audit-detail-user').textContent = [data.row.nom_complet || '', data.row.email || ''].filter(Boolean).join(' - ');
            document.getElementById('audit-detail-description').textContent = data.row.description || '';
            document.getElementById('audit-detail-before').textContent = JSON.stringify(data.row.donnees_avant ?? null, null, 2);
            document.getElementById('audit-detail-after').textContent = JSON.stringify(data.row.donnees_apres ?? null, null, 2);

            loading.classList.add('d-none');
            content.classList.remove('d-none');
        } catch (error) {
            loading.textContent = 'Impossible de charger le detail de cet evenement.';
        }
    });
});
</script>
