<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0"><i class="bi bi-folder2-open"></i> Mes documents</h1>
            <small class="text-muted">Documents RH disponibles pour vous.</small>
        </div>
    </div>

    <?php if (empty($documents)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="mt-3 mb-0 text-muted">Aucun document disponible pour le moment.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Titre</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td><?= esc($doc['titre']) ?></td>
                                <td><?= esc($doc['type']) ?></td>
                                <td class="text-truncate" style="max-width: 300px;"><?= esc($doc['description']) ?></td>
                                <td><?= date('d/m/Y', strtotime($doc['date_creation'])) ?></td>
                                <td class="text-end">
                                    <a href="<?= base_url('employe/documents/' . $doc['id'] . '/download') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i> Télécharger
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
