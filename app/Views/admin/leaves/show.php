<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Détail de la Demande de Congé</h1>
        <a href="<?= base_url('/admin/leaves') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Employee Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Employé</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong><?= esc($employe['prenom'] . ' ' . $employe['nom']) ?></strong></p>
                            <p class="text-muted mb-1">Matricule</p>
                            <p class="fw-bold"><?= esc($employe['matricule']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Poste</p>
                            <p class="fw-bold"><?= esc($employe['poste']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Request Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Détails de la Demande</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Type de congé</p>
                            <p class="fw-bold">
                                <span class="badge bg-info">
                                    <?= esc(ucfirst(str_replace('_', ' ', (string) ($leave['type_conge'] ?? '')))) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Statut</p>
                            <p class="fw-bold">
                                <?php if ($leave['statut'] === 'en_attente'): ?>
                                    <span class="badge bg-warning">En attente</span>
                                <?php elseif ($leave['statut'] === 'approuve'): ?>
                                    <span class="badge bg-success">Approuvee</span>
                                <?php elseif ($leave['statut'] === 'refuse'): ?>
                                    <span class="badge bg-danger">Refusee</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Annulee</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Date de début</p>
                            <p class="fw-bold"><?= date('d/m/Y', strtotime($leave['date_debut'])) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Date de fin</p>
                            <p class="fw-bold"><?= date('d/m/Y', strtotime($leave['date_fin'])) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Nombre de jours</p>
                            <p class="fw-bold text-success">
                                <strong><?= number_format((float) ($leave['nombre_jours'] ?? 0), 1, ',', '') ?></strong> jours
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <p class="text-muted mb-2">Motif / Raison</p>
                        <div class="p-3 bg-light rounded">
                            <?= nl2br(esc($leave['motif'] ?? 'Aucun motif spécifié')) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Historique</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <p class="mb-0"><strong>Demande soumise</strong></p>
                                <small class="text-muted">
                                    <?= date('d/m/Y a H:i', strtotime((string) ($leave['date_demande'] ?? 'now'))) ?>
                                </small>
                            </div>
                        </div>

                        <?php if (! empty($leave['date_approbation'])): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?= $leave['statut'] === 'approuve' ? 'success' : 'danger' ?>"></div>
                                <div class="timeline-content">
                                    <p class="mb-0">
                                        <strong>
                                            <?= $leave['statut'] === 'approuve' ? 'Approuvee' : 'Traitee' ?>
                                        </strong>
                                    </p>
                                    <small class="text-muted">
                                        <?= date('d/m/Y a H:i', strtotime((string) $leave['date_approbation'])) ?>
                                        par <?= esc(trim(((string) ($approver['prenom'] ?? '')) . ' ' . ((string) ($approver['nom'] ?? '')))) ?: 'Admin' ?>
                                    </small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Action</h6>
                </div>
                <div class="card-body">
                    <?php if ($leave['statut'] === 'en_attente'): ?>
                        <div class="d-grid gap-2">
                            <button class="btn btn-success" onclick="showApproveModal()">
                                <i class="bi bi-check-circle"></i> Approuver
                            </button>
                            <button class="btn btn-danger" onclick="showRejectModal()">
                                <i class="bi bi-x-circle"></i> Refuser
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Cette demande a déjà été traitée.
                        </div>
                        <?php if ($leave['statut'] === 'refuse' && (! empty($leave['refus_motif']) || ! empty($leave['commentaire']))): ?>
                            <div class="mt-3 p-3 bg-light rounded">
                                <?php if (! empty($leave['refus_motif'])): ?>
                                    <p class="text-muted small mb-2"><strong>Motif du refus:</strong></p>
                                    <p class="mb-2"><?= nl2br(esc((string) $leave['refus_motif'])) ?></p>
                                <?php endif; ?>
                                <?php if (! empty($leave['commentaire'])): ?>
                                    <p class="text-muted small mb-2"><strong>Commentaire admin:</strong></p>
                                    <p class="mb-0"><?= nl2br(esc((string) $leave['commentaire'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Solde Info -->
            <?php if ($solde): ?>
                <div class="card shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Solde de Congés (<?= date('Y') ?>)</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <p class="text-muted mb-2">Solde initial</p>
                            <h4 class="mb-0"><?= number_format($solde['solde_annuel'], 1, ',', '') ?> jours</h4>
                        </div>
                        <div class="mb-3">
                            <p class="text-muted mb-2">Utilisé</p>
                            <h4 class="mb-0 text-warning">
                                <?= number_format($solde['pris'], 1, ',', '') ?> jours
                            </h4>
                        </div>
                        <div class="mb-3">
                            <p class="text-muted mb-2">Restant</p>
                            <h4 class="mb-0 text-success">
                                <?= number_format($solde['restant'], 1, ',', '') ?> jours
                            </h4>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: <?= round(($solde['pris'] / $solde['solde_annuel']) * 100) ?>%"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">
                            <?= round(($solde['pris'] / $solde['solde_annuel']) * 100) ?>% utilisé
                        </small>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modals -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approuver le Congé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="approveForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i>
                        Cette demande sera approuvée et les jours seront déduits du solde.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire (optionnel)</label>
                        <textarea name="commentary" class="form-control" rows="3" placeholder="Ex: Approuvé, congé bien mérité!"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-success" onclick="submitApprove()">Confirmer l'Approbation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Refuser le Congé</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Veuillez expliquer les raisons du refus.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motif du refus <span class="text-danger">*</span></label>
                        <textarea name="motif_refus" class="form-control" rows="3" required 
                                  placeholder="Ex: Chiffrage d'affaires critiques ce mois-ci..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Commentaire supplémentaire</label>
                        <textarea name="commentary" class="form-control" rows="2" 
                                  placeholder="Message pour l'employé..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" onclick="submitReject()">Confirmer le Refus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showApproveModal() {
    new bootstrap.Modal(document.getElementById('approveModal')).show();
}

function showRejectModal() {
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function submitApprove() {
    const commentary = document.querySelector('#approveForm textarea[name="commentary"]').value;
    const formData = new FormData();
    formData.append('commentary', commentary);
    
    fetch(`<?= base_url('/admin/leaves/' . $leave['id'] . '/approve') ?>`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Congé approuvé avec succès');
            window.location.href = '<?= base_url('/admin/leaves') ?>';
        } else {
            alert('Erreur: ' + (data.message || 'Une erreur s\'est produite'));
        }
    })
    .catch(err => {
        alert('Erreur réseau');
        console.error(err);
    });
}

function submitReject() {
    const motifRefus = document.querySelector('#rejectForm textarea[name="motif_refus"]').value;
    const commentary = document.querySelector('#rejectForm textarea[name="commentary"]').value;
    
    if (!motifRefus.trim()) {
        alert('Veuillez entrer un motif');
        return;
    }
    
    const formData = new FormData();
    formData.append('motif_refus', motifRefus);
    formData.append('commentary', commentary);

    fetch(`<?= base_url('/admin/leaves/' . $leave['id'] . '/reject') ?>`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Refus enregistré');
            window.location.href = '<?= base_url('/admin/leaves') ?>';
        } else {
            alert('Erreur: ' + (data.message || 'Une erreur s\'est produite'));
        }
    })
    .catch(err => {
        alert('Erreur réseau');
        console.error(err);
    });
}
</script>
