<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i>
                Ajustement de pointage
            </span>
            <h1 class="page-hero-title mb-2">Correction du pointage</h1>
            <p class="page-hero-copy mb-0">Mettez à jour le statut et les horaires en conservant une trace motivée de la correction.</p>
        </div>
        <a href="<?= site_url('admin/presences/index?date=' . ($presence['date_pointage'] ?? date('Y-m-d'))) ?>" class="btn btn-light border">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
</section>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc((string) session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc((string) session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

<?php $validationErrors = session('errors'); ?>
<?php if (is_array($validationErrors) && !empty($validationErrors)): ?>
    <div class="alert alert-warning" role="alert">
        <h2 class="h6 mb-2">Veuillez corriger les champs suivants:</h2>
        <ul class="mb-0 ps-3">
            <?php foreach ($validationErrors as $error): ?>
                <li><?= esc((string) $error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php
$presenceDate = $presence['date_pointage'] ?? date('Y-m-d');
$employeeName = trim(implode(' ', array_filter([
    (string) ($presence['prenom'] ?? ''),
    (string) ($presence['nom'] ?? ''),
])));
$employeeName = $employeeName !== '' ? $employeeName : 'Employe introuvable';
$employeeMatricule = trim((string) ($presence['matricule'] ?? ''));
$employeeMatricule = $employeeMatricule !== '' ? $employeeMatricule : 'Matricule indisponible';
$statusLabel = isset($presence['statut'])
    ? ($presence['statut'] === 'present' ? "A l'heure" : ($presence['statut'] === 'retard' ? 'Retard' : 'Absent'))
    : 'Non renseigne';
$statusBadge = isset($presence['statut'])
    ? ($presence['statut'] === 'present' ? 'success' : ($presence['statut'] === 'retard' ? 'warning' : 'danger'))
    : 'secondary';
?>

<div class="row g-4">
    <div class="col-lg-7 col-xl-8">
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 card-title mb-3">Contexte employé</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="profile-field">
                            <div class="profile-field-label">Nom</div>
                            <div class="profile-field-value"><?= esc($employeeName) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="profile-field">
                            <div class="profile-field-label">Matricule</div>
                            <div class="profile-field-value badge-time"><?= esc($employeeMatricule) ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="profile-field">
                            <div class="profile-field-label">Date</div>
                            <div class="profile-field-value"><?= isset($presence['date_pointage']) ? (new DateTime($presence['date_pointage']))->format('d/m/Y') : 'Non renseignee' ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 card-title mb-3">Appliquer la correction</h2>

                <div class="alert alert-warning d-flex align-items-start gap-2" role="alert">
                    <i class="bi bi-shield-check mt-1"></i>
                    <div>
                        Toute correction est tracée dans le journal d'audit. Renseignez un motif clair pour faciliter le suivi RH.
                    </div>
                </div>

                <form method="POST" action="<?= site_url('admin/presences/store-correction/' . ($presence['id'] ?? '')) ?>" class="mt-3" aria-label="Formulaire de correction de pointage">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="statut" class="form-label fw-semibold">Statut *</label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="present" <?= (isset($presence['statut']) && $presence['statut'] === 'present') ? 'selected' : '' ?>>À l'heure</option>
                                <option value="retard" <?= (isset($presence['statut']) && $presence['statut'] === 'retard') ? 'selected' : '' ?>>Retard</option>
                                <option value="absent" <?= (isset($presence['statut']) && $presence['statut'] === 'absent') ? 'selected' : '' ?>>Absent</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="heure_pointage" class="form-label fw-semibold">Heure d'arrivée</label>
                            <input type="time" class="form-control" id="heure_pointage" name="heure_pointage" value="<?= isset($presence['heure_pointage']) ? substr($presence['heure_pointage'], 0, 5) : '' ?>">
                            <small class="text-muted">Laissez vide pour conserver la valeur existante.</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="heure_sortie" class="form-label fw-semibold">Heure de départ</label>
                            <input type="time" class="form-control" id="heure_sortie" name="heure_sortie" value="<?= isset($presence['heure_sortie']) ? substr($presence['heure_sortie'], 0, 5) : '' ?>">
                        </div>
                        <div class="col-12">
                            <label for="motif_correction" class="form-label fw-semibold">Motif de correction</label>
                            <textarea class="form-control" id="motif_correction" name="motif_correction" rows="4" maxlength="255" placeholder="Ex: erreur de saisie sur la borne, justificatif valide..."><?= esc($presence['motif_correction'] ?? '') ?></textarea>
                            <small class="text-muted">Optionnel, mais recommandé.</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                        <a href="<?= site_url('admin/presences/index?date=' . $presenceDate) ?>" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i>Enregistrer la correction
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5 col-xl-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 card-title">État actuel</h2>

                <div class="profile-field mb-3">
                    <div class="profile-field-label">Statut actuel</div>
                    <div class="profile-field-value">
                        <span class="badge text-bg-<?= $statusBadge ?>"><?= $statusLabel ?></span>
                    </div>
                </div>

                <div class="profile-field mb-3">
                    <div class="profile-field-label">Arrivée</div>
                    <div class="profile-field-value badge-time"><?= isset($presence['heure_pointage']) ? substr($presence['heure_pointage'], 0, 5) : '—' ?></div>
                </div>

                <div class="profile-field mb-3">
                    <div class="profile-field-label">Départ</div>
                    <div class="profile-field-value badge-time"><?= isset($presence['heure_sortie']) ? substr($presence['heure_sortie'], 0, 5) : '—' ?></div>
                </div>

                <div class="profile-field">
                    <div class="profile-field-label">Dernière modification</div>
                    <div class="profile-field-value"><?= isset($presence['date_modification']) ? (new DateTime($presence['date_modification']))->format('d/m/Y H:i') : '—' ?></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Aide rapide</h2>
                <ul class="mb-0 ps-3 small text-muted">
                    <li>Utilisez le statut "Retard" uniquement si une arrivée existe.</li>
                    <li>Renseignez l'heure de départ pour des rapports de durée fiables.</li>
                    <li>Documentez les cas sensibles via le motif de correction.</li>
                </ul>
            </div>
        </div>
    </div>
</div>