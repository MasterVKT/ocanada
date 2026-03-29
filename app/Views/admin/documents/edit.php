<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-pencil"></i>
                Documents RH
            </span>
            <h1 class="page-hero-title mb-2">Modifier le document</h1>
            <p class="page-hero-copy mb-0">Mettez à jour les métadonnées du document et remplacez le fichier si nécessaire.</p>
        </div>
    </div>
</section>

    <?php if (! empty(session()->getFlashdata('errors'))): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <ul class="mb-0">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="<?= base_url('admin/documents/' . $document['id'] . '/update') ?>" enctype="multipart/form-data" class="row g-3">
                <?= csrf_field() ?>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="titre">Titre</label>
                    <input type="text" id="titre" name="titre" class="form-control" required value="<?= esc(old('titre', $document['titre'])) ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="type">Type</label>
                    <input type="text" id="type" name="type" class="form-control" required value="<?= esc(old('type', $document['type'])) ?>" placeholder="Ex: Contrat, Attestation">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="employe_id">Employé (optionnel)</label>
                    <select id="employe_id" name="employe_id" class="form-select">
                        <option value="">Général</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= (old('employe_id', $document['employe_id']) == $emp['id']) ? 'selected' : '' ?>>
                                <?= esc($emp['prenom'] . ' ' . $emp['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label" for="fichier">Fichier</label>
                    <div class="input-group">
                        <input type="file" id="fichier" name="fichier" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <a href="<?= base_url('admin/documents/' . $document['id'] . '/download') ?>" class="btn btn-outline-primary" title="Télécharger le fichier actuel">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                    <small class="form-text text-muted">Laisser vide pour conserver le fichier actuel.</small>
                </div>

                <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="3" class="form-control"><?= esc(old('description', $document['description'])) ?></textarea>
                </div>

                <div class="col-12 d-grid d-md-flex justify-content-md-end gap-2">
                    <a href="<?= base_url('admin/documents') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Retour
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
