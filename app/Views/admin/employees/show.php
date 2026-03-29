<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-person-vcard-fill"></i>
                Dossier collaborateur
            </span>
            <h1 class="page-hero-title mb-2">Detail employe</h1>
            <p class="page-hero-copy mb-0">Consultez l ensemble des informations personnelles, professionnelles et du solde de conge.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= site_url('admin/employees/' . $employe['id'] . '/edit') ?>" class="btn btn-primary">
                <i class="bi bi-pencil me-2"></i>Modifier
            </a>
            <a href="<?= site_url('admin/employees') ?>" class="btn btn-light border">
                <i class="bi bi-arrow-left me-2"></i>Retour a la liste
            </a>
        </div>
    </div>
</section>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><?= session('success') ?></div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><?= session('error') ?></div>
<?php endif; ?>

<div class="row">
    <!-- Infos générales -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Informations personnelles</h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Matricule</div>
                            <div class="profile-field-value badge-time"><?= esc($employe['matricule']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Statut</div>
                            <div class="profile-field-value">
                                <span class="badge rounded-pill <?= $employe['statut'] === 'actif' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= ucfirst($employe['statut']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Nom</div>
                            <div class="profile-field-value"><?= esc($employe['nom']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Prenom</div>
                            <div class="profile-field-value"><?= esc($employe['prenom']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Date de naissance</div>
                            <div class="profile-field-value"><?= date('d/m/Y', strtotime($employe['date_naissance'])) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Telephone</div>
                            <div class="profile-field-value"><?= esc($employe['telephone'] ?: 'Non renseigne') ?></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="profile-field">
                            <div class="profile-field-label">Email</div>
                            <div class="profile-field-value">
                                <a href="mailto:<?= esc($employe['email']) ?>"><?= esc($employe['email']) ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Infos professionnelles -->
        <div class="card mt-4">
            <div class="card-header">
                <h2 class="h5 mb-0">Informations professionnelles</h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Date d embauche</div>
                            <div class="profile-field-value"><?= date('d/m/Y', strtotime($employe['date_embauche'])) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Anciennete</div>
                            <div class="profile-field-value"><?= (int) $anciennete ?> annee(s)</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Poste</div>
                            <div class="profile-field-value"><?= esc($employe['poste']) ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Departement</div>
                            <div class="profile-field-value"><span class="badge rounded-pill text-bg-info-subtle text-info-emphasis"><?= esc($employe['departement']) ?></span></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="profile-field">
                            <div class="profile-field-label">Salaire de base</div>
                            <div class="profile-field-value"><?= number_format((float) $employe['salaire_base'], 0, ',', ' ') ?> FCFA</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Adresse -->
        <?php if (!empty($employe['adresse'])): ?>
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="h5 mb-0">Adresse</h2>
                </div>
                <div class="card-body">
                    <div class="profile-field">
                        <div class="profile-field-value">
                            <?= esc($employe['adresse']) ?><br>
                            <?= esc(($employe['code_postal'] ?? '') . ' ' . ($employe['ville'] ?? '')) ?><br>
                            <?= esc($employe['pays'] ?? '') ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Solde de congé -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-0">
                    <i class="bi bi-calendar-event me-2"></i>
                    Solde de conge
                </h2>
            </div>
            <div class="card-body">
                <?php if ($solde): ?>
                    <div class="row text-center g-3">
                        <div class="col-4">
                            <div class="fs-5 fw-bold text-primary"><?= (int) $solde['solde_annuel'] ?></div>
                            <small class="text-muted">Annuel</small>
                        </div>
                        <div class="col-4">
                            <div class="fs-5 fw-bold text-success"><?= (int) $solde['restant'] ?></div>
                            <small class="text-muted">Restant</small>
                        </div>
                        <div class="col-4">
                            <div class="fs-5 fw-bold text-warning"><?= (int) $solde['pris'] ?></div>
                            <small class="text-muted">Pris</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center g-3">
                        <div class="col-6">
                            <div class="fs-6 fw-bold text-info"><?= (int) $solde['maladie_restant'] ?></div>
                            <small class="text-muted">Maladie</small>
                        </div>
                        <div class="col-6">
                            <div class="fs-6 fw-bold text-muted"><?= (int) $solde['maladie_pris'] ?></div>
                            <small class="text-muted">Pris</small>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="realtime-placeholder">
                        <i class="bi bi-info-circle fs-1 text-muted"></i>
                        <p class="text-muted mb-0">Solde non disponible</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    Actions
                </h2>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= site_url('admin/presences/history') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-calendar-check me-1"></i>
                        Voir presences
                    </a>
                    <a href="<?= site_url('admin/leaves') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-calendar-event me-1"></i>
                        Demandes de conge
                    </a>
                    <a href="<?= site_url('admin/documents') ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-file-earmark-text me-1"></i>
                        Documents
                    </a>
                    <?php if ($employe['statut'] === 'actif'): ?>
                        <button type="button" class="btn btn-outline-danger btn-sm"
                                onclick="confirmDeactivate(<?= $employe['id'] ?>, '<?= esc($employe['prenom'] . ' ' . $employe['nom']) ?>')">
                            <i class="bi bi-person-dash me-1"></i>
                            Desactiver
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeactivate(id, name) {
    if (confirm(`Etes-vous sur de vouloir desactiver l employe "${name}" ?`)) {
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