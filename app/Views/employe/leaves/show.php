<?php $this->extend('layouts/main'); ?>

<?php $this->section('content'); ?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Détail de ma Demande</h1>
        <a href="<?= base_url('/employe/leaves') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Status Banner -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="text-muted mb-1">Statut actuel</p>
                            <div>
                                <?php if ($leave['statut'] === 'en_attente'): ?>
                                    <span class="badge bg-warning" style="padding: 8px 12px; font-size: 14px;">
                                        <i class="bi bi-hourglass-split"></i> En attente d'approbation
                                    </span>
                                <?php elseif ($leave['statut'] === 'approuve'): ?>
                                    <span class="badge bg-success" style="padding: 8px 12px; font-size: 14px;">
                                        <i class="bi bi-check-circle"></i> Approuvée
                                    </span>
                                <?php elseif ($leave['statut'] === 'refuse'): ?>
                                    <span class="badge bg-danger" style="padding: 8px 12px; font-size: 14px;">
                                        <i class="bi bi-x-circle"></i> Refusée
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary" style="padding: 8px 12px; font-size: 14px;">
                                        <i class="bi bi-slash-circle"></i> Annulée
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <p class="text-muted mb-1">Demande soumise le</p>
                            <p class="fw-bold"><?= date('d/m/Y à H:i', strtotime($leave['date_soumission'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Détails de la Demande</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Type de congé</p>
                            <p class="fw-bold">
                                <span class="badge bg-info">
                                    <?= ucfirst(str_replace('_', ' ', $leave['type_conge'])) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Du</p>
                            <p class="fw-bold"><?= date('d/m/Y', strtotime($leave['date_debut'])) ?></p>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-1">Au</p>
                            <p class="fw-bold"><?= date('d/m/Y', strtotime($leave['date_fin'])) ?></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <p class="text-muted mb-2">Jours ouvrables demandés</p>
                            <h4 class="text-success">
                                <strong><?= number_format($leave['jours_ouvrables'], 1, ',', '') ?> jours</strong>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Motif -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Motif / Raison</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <?= nl2br(esc($leave['motif'])) ?>
                    </p>
                </div>
            </div>

            <!-- Timeline -->
            <?php if ($leave['statut'] !== 'en_attente'): ?>
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
                                        <?= date('d/m/Y à H:i', strtotime($leave['date_soumission'])) ?>
                                    </small>
                                </div>
                            </div>

                            <div class="timeline-item">
                                <div class="timeline-marker bg-<?= $leave['statut'] === 'approuve' ? 'success' : 'danger' ?>"></div>
                                <div class="timeline-content">
                                    <p class="mb-0">
                                        <strong>
                                            <?= $leave['statut'] === 'approuve' ? 'Approuvée' : 'Refusée' ?>
                                        </strong>
                                    </p>
                                    <small class="text-muted">
                                        <?= $leave['date_traitement'] ? date('d/m/Y à H:i', strtotime($leave['date_traitement'])) : 'Traitement en attente' ?>
                                        par <?= esc($leave['approuve_par_nom'] ?? 'L\'administrateur') ?>
                                    </small>

                                    <?php if ($leave['statut'] === 'refuse' && $leave['commentaire_admin']): ?>
                                        <div class="mt-2 p-2 bg-light border-start border-danger">
                                            <p class="text-muted small mb-0"><strong>Motif du refus:</strong></p>
                                            <p class="mb-0 small"><?= esc($leave['commentaire_admin']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Solde Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Mon Solde</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="text-muted mb-2">Solde initial (<?= date('Y') ?>)</p>
                        <h5 class="mb-0"><?= number_format($solde['solde_annuel'], 1, ',', '') ?> jours</h5>
                    </div>
                    <div class="mb-3">
                        <p class="text-muted mb-2">Selon cette demande</p>
                        <p class="mb-0">
                            <span class="text-danger">- <?= number_format($leave['jours_ouvrables'], 1, ',', '') ?> jours</span>
                        </p>
                    </div>
                    <hr>
                    <div>
                        <p class="text-muted mb-2">Solde après approbation</p>
                        <h5 class="text-success mb-0">
                            <?php 
                                $solve_after = $solde['restant'] - $leave['jours_ouvrables'];
                                echo number_format(max(0, $solve_after), 1, ',', '');
                            ?> jours
                        </h5>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <?php if ($leave['statut'] === 'en_attente'): ?>
                <div class="card shadow-sm mb-4 border-warning">
                    <div class="card-header bg-warning bg-opacity-10">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> En Attente
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Votre demande est actuellement en attente d'approbation par un administrateur.
                            Ce processus prend généralement 1 à 2 jours ouvrables.
                        </p>
                        <button class="btn btn-sm btn-outline-danger w-100" onclick="cancelRequest()">
                            <i class="bi bi-x-circle"></i> Annuler cette demande
                        </button>
                    </div>
                </div>
            <?php elseif ($leave['statut'] === 'approuve'): ?>
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-header bg-success bg-opacity-10">
                        <h6 class="mb-0">
                            <i class="bi bi-check-circle"></i> Approuvée
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">
                            Votre congé est approuvé. Les jours ont été déduits de votre solde.
                            Veuillez vous assurer d'informer votre supérieur des détails.
                        </p>
                    </div>
                </div>
            <?php elseif ($leave['statut'] === 'refuse'): ?>
                <div class="card shadow-sm mb-4 border-danger">
                    <div class="card-header bg-danger bg-opacity-10">
                        <h6 class="mb-0">
                            <i class="bi bi-x-circle"></i> Refusée
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Malheureusement, votre demande a été refusée. 
                            Vous pouvez soumettre une nouvelle demande pour une période différente.
                        </p>
                        <a href="<?= base_url('/employe/leaves/create') ?>" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Nouvelle Demande
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- FAQ Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-question-circle"></i> Questions?
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        <strong>Comment fonctionne l'approbation?</strong><br>
                        Les demandes en attente plus de 48h reçoivent une notification automatique à l'administrateur.
                    </p>
                    <p class="mb-0">
                        <strong>Puis-je modifier ma demande?</strong><br>
                        Non, vous devez l'annuler et en créer une nouvelle.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelRequest() {
    if (confirm('Êtes-vous sûr de vouloir annuler cette demande?')) {
        fetch(`<?= base_url('/employe/leaves/' . $leave['id'] . '/cancel') ?>`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Demande annulée');
                window.location.href = '<?= base_url('/employe/leaves') ?>';
            } else {
                alert('Erreur: ' + data.message);
            }
        });
    }
}
</script>

<?php $this->endSection(); ?>
