<?php $errors = session('errors') ?? []; ?>

<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-person-plus-fill"></i>
                Nouveau collaborateur
            </span>
            <h1 class="page-hero-title mb-2">Creer un employe</h1>
            <p class="page-hero-copy mb-0">Renseignez les informations essentielles pour creer le dossier RH et le compte utilisateur associe.</p>
        </div>
        <a href="<?= site_url('admin/employees') ?>" class="btn btn-light border">
            <i class="bi bi-arrow-left me-2"></i>Retour a la liste
        </a>
    </div>
</section>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm mb-4"><?= session('error') ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-warning border-0 shadow-sm mb-4">
        <div class="fw-semibold mb-1">Le formulaire contient des erreurs.</div>
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form action="<?= site_url('admin/employees/store') ?>" method="post">
            <?= csrf_field() ?>

            <h2 class="h5 mb-3">Informations personnelles</h2>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="nom" class="form-label">Nom *</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= old('nom') ?>" required>
                    <?php if (isset($errors['nom'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['nom'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="prenom" class="form-label">Prenom *</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?= old('prenom') ?>" required>
                    <?php if (isset($errors['prenom'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['prenom'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?= old('email') ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="telephone" class="form-label">Telephone</label>
                    <input type="tel" class="form-control" id="telephone" name="telephone" value="<?= old('telephone') ?>">
                    <?php if (isset($errors['telephone'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['telephone'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="date_naissance" class="form-label">Date de naissance *</label>
                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?= old('date_naissance') ?>" required>
                    <?php if (isset($errors['date_naissance'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['date_naissance'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="date_embauche" class="form-label">Date d embauche *</label>
                    <input type="date" class="form-control" id="date_embauche" name="date_embauche" value="<?= old('date_embauche') ?>" required>
                    <?php if (isset($errors['date_embauche'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['date_embauche'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <h2 class="h5 mb-3">Informations professionnelles</h2>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="poste" class="form-label">Poste *</label>
                    <input type="text" class="form-control" id="poste" name="poste" value="<?= old('poste') ?>" required>
                    <?php if (isset($errors['poste'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['poste'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="departement" class="form-label">Departement *</label>
                    <select class="form-select" id="departement" name="departement" required>
                        <option value="">Choisir un departement</option>
                        <option value="Direction" <?= old('departement') === 'Direction' ? 'selected' : '' ?>>Direction</option>
                        <option value="Comptabilité" <?= old('departement') === 'Comptabilité' ? 'selected' : '' ?>>Comptabilite</option>
                        <option value="Commercial" <?= old('departement') === 'Commercial' ? 'selected' : '' ?>>Commercial</option>
                        <option value="Logistique" <?= old('departement') === 'Logistique' ? 'selected' : '' ?>>Logistique</option>
                        <option value="Ressources Humaines" <?= old('departement') === 'Ressources Humaines' ? 'selected' : '' ?>>Ressources Humaines</option>
                        <option value="Technique" <?= old('departement') === 'Technique' ? 'selected' : '' ?>>Technique</option>
                    </select>
                    <?php if (isset($errors['departement'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['departement'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label for="salaire_base" class="form-label">Salaire de base (FCFA) *</label>
                    <input type="number" class="form-control" id="salaire_base" name="salaire_base" value="<?= old('salaire_base') ?>" min="0" step="1000" required>
                    <?php if (isset($errors['salaire_base'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['salaire_base'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <h5 class="mb-3">Adresse</h5>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label for="adresse" class="form-label">Adresse</label>
                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?= old('adresse') ?>">
                    <?php if (isset($errors['adresse'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['adresse'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="ville" class="form-label">Ville</label>
                    <input type="text" class="form-control" id="ville" name="ville" value="<?= old('ville') ?>">
                    <?php if (isset($errors['ville'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['ville'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="code_postal" class="form-label">Code postal</label>
                    <input type="text" class="form-control" id="code_postal" name="code_postal" value="<?= old('code_postal') ?>">
                    <?php if (isset($errors['code_postal'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['code_postal'] ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="pays" class="form-label">Pays</label>
                    <input type="text" class="form-control" id="pays" name="pays"
                           value="<?= old('pays', 'Cameroun') ?>">
                    <?php if (isset($errors['pays'])): ?>
                        <div class="text-danger small mt-1"><?= $errors['pays'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-md-end gap-2">
                <a href="<?= site_url('admin/employees') ?>" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-2"></i>Creer l employe
                </button>
            </div>
        </form>
    </div>
</div>