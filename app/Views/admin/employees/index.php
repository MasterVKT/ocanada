<?php
$filters = $filters ?? [];
$search = trim((string) ($filters['search'] ?? ''));
$deptFilter = (string) ($filters['departement'] ?? '');
$posteFilter = (string) ($filters['poste'] ?? '');
$statutFilter = (string) ($filters['statut'] ?? '');
$currentPage = (int) ($pager['currentPage'] ?? 1);
$lastPage = max(1, (int) ($pager['lastPage'] ?? 1));
$total = (int) ($pager['total'] ?? count($employes));
?>

<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-people-fill"></i>
                Administration RH
            </span>
            <h1 class="page-hero-title mb-2">Gestion des employes</h1>
            <p class="page-hero-copy mb-0">Pilotez les profils, statuts et informations contractuelles de tous les collaborateurs.</p>
        </div>
        <a href="<?= site_url('admin/employees/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>
            Nouvel employe
        </a>
    </div>
</section>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><?= session('success') ?></div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><?= session('error') ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h2 class="h5 mb-0"><i class="bi bi-funnel me-2"></i>Filtres</h2>
    </div>
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-12 col-md-6 col-xl-4">
                <label for="search" class="form-label">Recherche</label>
                <input type="text" class="form-control" id="search" name="search" value="<?= esc($search) ?>" placeholder="Nom, prenom, matricule...">
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label for="departement" class="form-label">Departement</label>
                <select class="form-select" id="departement" name="departement">
                    <option value="">Tous</option>
                    <?php foreach ($departements as $dept): ?>
                        <option value="<?= esc($dept) ?>" <?= $deptFilter === $dept ? 'selected' : '' ?>><?= esc($dept) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label for="poste" class="form-label">Poste</label>
                <select class="form-select" id="poste" name="poste">
                    <option value="">Tous</option>
                    <?php foreach ($postes as $p): ?>
                        <option value="<?= esc($p) ?>" <?= $posteFilter === $p ? 'selected' : '' ?>><?= esc($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label for="statut" class="form-label">Statut</label>
                <select class="form-select" id="statut" name="statut">
                    <option value="">Tous</option>
                    <option value="actif" <?= $statutFilter === 'actif' ? 'selected' : '' ?>>Actif</option>
                    <option value="inactif" <?= $statutFilter === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                </select>
            </div>
            <div class="col-12 col-md-9 col-xl-2 d-grid d-md-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">
                    <i class="bi bi-search me-1"></i>Filtrer
                </button>
                <a href="<?= site_url('admin/employees') ?>" class="btn btn-outline-secondary flex-grow-1">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reinitialiser
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h2 class="h5 mb-0">Liste des employes</h2>
        <span class="badge rounded-pill text-bg-light border text-secondary px-3 py-2"><?= esc((string) $total) ?> resultat(s)</span>
    </div>
    <div class="card-body">
        <?php if (empty($employes)): ?>
            <div class="realtime-placeholder">
                <i class="bi bi-people fs-1 text-muted"></i>
                <p class="text-muted mb-0">Aucun employe trouve pour ces filtres.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle employees-table">
                    <thead>
                        <tr>
                            <th>Matricule</th>
                            <th>Nom et prenom</th>
                            <th>Poste</th>
                            <th>Departement</th>
                            <th>Email</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employes as $employe): ?>
                            <?php
                            $initials = strtoupper(substr((string) ($employe['prenom'] ?? ''), 0, 1) . substr((string) ($employe['nom'] ?? ''), 0, 1));
                            $fullName = trim((string) (($employe['prenom'] ?? '') . ' ' . ($employe['nom'] ?? '')));
                            ?>
                            <tr>
                                <td><span class="badge-time"><?= esc($employe['matricule'] ?? '') ?></span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="employees-avatar" aria-hidden="true"><?= esc($initials) ?></div>
                                        <div>
                                            <div class="fw-semibold"><?= esc($fullName) ?></div>
                                            <small class="text-muted">Depuis le <?= date('d/m/Y', strtotime((string) ($employe['date_embauche'] ?? 'now'))) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= esc($employe['poste'] ?? '') ?></td>
                                <td><span class="badge rounded-pill text-bg-light border"><?= esc($employe['departement'] ?? '') ?></span></td>
                                <td>
                                    <a href="mailto:<?= esc($employe['email'] ?? '') ?>" class="text-decoration-none">
                                        <?= esc($employe['email'] ?? '') ?>
                                    </a>
                                </td>
                                <td>
                                    <span class="badge rounded-pill <?= ($employe['statut'] ?? '') === 'actif' ? 'text-bg-success' : 'text-bg-danger' ?>">
                                        <?= ($employe['statut'] ?? '') === 'actif' ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group float-md-end" role="group" aria-label="Actions employe">
                                        <a href="<?= site_url('admin/employees/' . (int) $employe['id']) ?>" class="btn btn-sm btn-outline-primary" title="Voir">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= site_url('admin/employees/' . (int) $employe['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if (($employe['statut'] ?? '') === 'actif'): ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeactivate(<?= (int) $employe['id'] ?>, '<?= esc($fullName) ?>')" title="Desactiver">
                                                <i class="bi bi-person-dash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($lastPage > 1): ?>
                <nav class="mt-3" aria-label="Pagination employes">
                    <ul class="pagination justify-content-center mb-0">
                        <?php
                        $query = $_GET;
                        $buildLink = static function (int $page) use ($query): string {
                            $query['page'] = $page;
                            return '?' . http_build_query($query);
                        };
                        ?>
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $currentPage <= 1 ? '#' : esc($buildLink($currentPage - 1)) ?>" aria-label="Precedent">&laquo;</a>
                        </li>
                        <?php for ($i = 1; $i <= $lastPage; $i++): ?>
                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="<?= esc($buildLink($i)) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $currentPage >= $lastPage ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= $currentPage >= $lastPage ? '#' : esc($buildLink($currentPage + 1)) ?>" aria-label="Suivant">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmDeactivate(id, name) {
    if (confirm(`Etes-vous sur de vouloir desactiver l employe "${name}" ?`)) {
        // Créer un formulaire temporaire pour POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `<?= site_url('admin/employees/') ?>${id}/deactivate`;

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '<?= csrf_token() ?>';
        csrfInput.value = '<?= csrf_hash() ?>';
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>