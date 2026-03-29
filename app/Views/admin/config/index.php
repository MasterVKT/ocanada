<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-gear-fill"></i>
                Parametrage
            </span>
            <h1 class="page-hero-title mb-2">Configuration systeme</h1>
            <p class="page-hero-copy mb-0">Centralisez les regles globales de l application et les parametres d exploitation.</p>
        </div>
    </div>
</section>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><?= esc((string) session('success')) ?></div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><?= esc((string) session('error')) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card">
            <div class="card-header"><h2 class="h6 mb-0">Parametres generaux</h2></div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/configuration') ?>" class="row g-3">
                    <?= csrf_field() ?>
                    <div class="col-12">
                        <label for="ip_kiosque_autorisees" class="form-label">IPs kiosque autorisees (separees par virgule)</label>
                        <textarea id="ip_kiosque_autorisees" class="form-control" name="ip_kiosque_autorisees" rows="2"><?= esc((string) (($configMap['ip_kiosque_autorisees']['valeur'] ?? ''))) ?></textarea>
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="heure_debut_travail" class="form-label">Heure debut travail (HH:MM)</label>
                        <input id="heure_debut_travail" type="text" class="form-control" name="heure_debut_travail" value="<?= esc((string) (($configMap['heure_debut_travail']['valeur'] ?? '08:00'))) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="heure_fin_travail" class="form-label">Heure fin travail (HH:MM)</label>
                        <input id="heure_fin_travail" type="text" class="form-control" name="heure_fin_travail" value="<?= esc((string) (($configMap['heure_fin_travail']['valeur'] ?? '17:00'))) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="jours_ouvrables" class="form-label">Jours ouvrables (1,2,3,4,5)</label>
                        <input id="jours_ouvrables" type="text" class="form-control" name="jours_ouvrables" value="<?= esc((string) (($configMap['jours_ouvrables']['valeur'] ?? '1,2,3,4,5'))) ?>">
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="anthropic_api_key" class="form-label">Cle API Anthropic (laisser vide pour conserver)</label>
                        <input id="anthropic_api_key" type="password" class="form-control" name="anthropic_api_key" value="">
                    </div>
                    <div class="col-12 d-grid d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary">Enregistrer les parametres</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card">
            <div class="card-header"><h2 class="h6 mb-0">Ajouter un jour ferie</h2></div>
            <div class="card-body">
                <form method="post" action="<?= site_url('admin/configuration/jours-feries') ?>" class="row g-3">
                    <?= csrf_field() ?>
                    <div class="col-12">
                        <label for="date_ferie" class="form-label">Date</label>
                        <input id="date_ferie" type="date" name="date_ferie" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label for="designation" class="form-label">Designation</label>
                        <input id="designation" type="text" name="designation" class="form-control" maxlength="100" required>
                    </div>
                    <div class="col-12">
                        <label for="type" class="form-label">Type</label>
                        <select id="type" name="type" class="form-select" required>
                            <option value="fixe">Fixe</option>
                            <option value="variable">Variable</option>
                        </select>
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-outline-primary">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h2 class="h6 mb-0">Jours feries - annee <?= (int) ($year ?? date('Y')) ?></h2>
        <form method="get" class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center w-100" style="max-width: 22rem;">
            <input type="number" class="form-control" name="annee" min="2000" max="2100" value="<?= (int) ($year ?? date('Y')) ?>">
            <button type="submit" class="btn btn-light border">Changer</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Designation</th>
                    <th>Type</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($holidays)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun jour ferie pour cette annee.</td></tr>
                <?php else: ?>
                    <?php foreach ($holidays as $holiday): ?>
                        <tr>
                            <td><?= esc(format_date_fr((string) $holiday['date_ferie'])) ?></td>
                            <td><?= esc((string) $holiday['designation']) ?></td>
                            <td><span class="badge text-bg-light"><?= esc((string) $holiday['type']) ?></span></td>
                            <td class="text-end">
                                <div class="d-inline-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary holiday-edit-trigger"
                                        data-id="<?= (int) $holiday['id'] ?>"
                                        data-date="<?= esc((string) $holiday['date_ferie']) ?>"
                                        data-designation="<?= esc((string) $holiday['designation']) ?>"
                                        data-type="<?= esc((string) $holiday['type']) ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#holidayEditModal"
                                    >
                                        Modifier
                                    </button>
                                    <form method="post" action="<?= site_url('admin/configuration/jours-feries/' . (int) $holiday['id'] . '/delete') ?>" onsubmit="return confirm('Supprimer ce jour ferie ?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="holidayEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5 mb-0">Modifier un jour ferie</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form id="holidayEditForm" method="post">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="holiday-edit-date" class="form-label">Date</label>
                        <input id="holiday-edit-date" type="date" name="date_ferie" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="holiday-edit-designation" class="form-label">Designation</label>
                        <input id="holiday-edit-designation" type="text" name="designation" class="form-control" maxlength="100" required>
                    </div>
                    <div class="mb-0">
                        <label for="holiday-edit-type" class="form-label">Type</label>
                        <select id="holiday-edit-type" name="type" class="form-select" required>
                            <option value="fixe">Fixe</option>
                            <option value="variable">Variable</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.holiday-edit-trigger').forEach((button) => {
    button.addEventListener('click', () => {
        const form = document.getElementById('holidayEditForm');
        form.action = `<?= site_url('admin/configuration/jours-feries') ?>/${button.dataset.id}`;
        document.getElementById('holiday-edit-date').value = button.dataset.date || '';
        document.getElementById('holiday-edit-designation').value = button.dataset.designation || '';
        document.getElementById('holiday-edit-type').value = button.dataset.type || 'fixe';
    });
});
</script>

<div class="card mt-4">
    <div class="card-header"><h2 class="h6 mb-0">Cles de configuration existantes</h2></div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Cle</th>
                    <th>Valeur</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($configs)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-3">Aucune configuration en base.</td></tr>
                <?php else: ?>
                    <?php foreach ($configs as $config): ?>
                        <tr>
                            <td class="fw-semibold"><?= esc((string) $config['cle']) ?></td>
                            <td class="small text-muted"><?= esc((string) ($config['valeur'] ?? '')) ?></td>
                            <td class="small text-muted"><?= esc((string) ($config['description'] ?? '')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
