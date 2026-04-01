<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="<?= base_url('admin/visitors') ?>" class="btn btn-outline-secondary btn-sm mb-3">
                <i class="bi bi-arrow-left"></i> Retour
            </a>
            <h1 class="mb-0">
                <i class="bi bi-person"></i> <?= esc($visitor['prenom'] . ' ' . $visitor['nom']) ?>
            </h1>
            <small class="text-muted">Détails du visiteur</small>
        </div>
    </div>

    <div class="row">
        <!-- Left: Details -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-person-vcard"></i> Informations personnelles</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Prénom</label>
                            <p class="mb-0"><strong><?= esc($visitor['prenom']) ?></strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Nom</label>
                            <p class="mb-0"><strong><?= esc($visitor['nom']) ?></strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Email</label>
                            <p class="mb-0">
                                <a href="mailto:<?= esc($visitor['email']) ?>">
                                    <?= esc($visitor['email']) ?>
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Téléphone</label>
                            <p class="mb-0">
                                <a href="tel:<?= esc($visitor['telephone']) ?>">
                                    <?= esc($visitor['telephone']) ?>
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6 mb-0">
                            <label class="form-label text-muted small">Entreprise</label>
                            <p class="mb-0"><?= esc($visitor['entreprise'] ?? 'Non renseignee') ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visit Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-briefcase"></i> Informations de visite</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Motif de la visite</label>
                            <p class="mb-0"><strong><?= esc($visitor['motif']) ?></strong></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Personne à voir</label>
                            <p class="mb-0"><strong><?= esc($visitor['personne_a_voir']) ?></strong></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Date d'arrivée</label>
                            <p class="mb-0">
                                <i class="bi bi-calendar-event"></i> <?= date('d/m/Y', strtotime($visitor['date_creation'])) ?>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Heure d'arrivée</label>
                            <p class="mb-0">
                                <i class="bi bi-clock"></i> <?= substr($visitor['heure_arrivee'], 0, 5) ?>
                            </p>
                        </div>
                        <div class="col-md-4 mb-3" id="checkoutSection">
                            <label class="form-label text-muted small">Heure de départ</label>
                            <p class="mb-0">
                                <?php if ($visitor['heure_depart']): ?>
                                    <i class="bi bi-clock"></i> <?= substr($visitor['heure_depart'], 0, 5) ?>
                                <?php else: ?>
                                    <span class="text-muted">--</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if ($timeSpent): ?>
                            <div class="col-md-12 mb-0">
                                <label class="form-label text-muted small">Durée de présence</label>
                                <p class="mb-0">
                                    <strong><?= $timeSpent ?></strong>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Statut Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Statut</h6>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <?php if ($visitor['statut'] === 'present'): ?>
                                <span class="badge bg-success fs-6 py-2 px-3">
                                    <i class="bi bi-person-check"></i> Présent sur site
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary fs-6 py-2 px-3">
                                    <i class="bi bi-person-x"></i> Parti
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-end">
                            <?php if ($visitor['statut'] === 'present'): ?>
                                <button class="btn btn-sm btn-warning" onclick="checkoutVisitor(<?= $visitor['id'] ?>)">
                                    <i class="bi bi-box-arrow-right"></i> Marquer comme parti
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: QR Code Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-qr-code"></i> Badge d'accès</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <strong><?= esc($visitor['badge_id']) ?></strong>
                    </div>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= urlencode($visitor['badge_id']) ?>"
                        alt="QR Code" class="img-fluid border rounded mb-3" style="max-width: 200px;">

                    <a href="<?= base_url('admin/visitors/' . $visitor['id'] . '/badge') ?>" class="btn btn-sm btn-outline-primary w-100 mb-2">
                        <i class="bi bi-printer"></i> Imprimer
                    </a>
                    <button class="btn btn-sm btn-outline-secondary w-100" onclick="downloadQRCode('<?= esc($visitor['badge_id']) ?>')">
                        <i class="bi bi-download"></i> Télécharger
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function checkoutVisitor(visitorId) {
        if (!confirm('Confirmer la sortie de ce visiteur ?')) return;

        fetch(`<?= base_url('admin/visitors') ?>/${visitorId}/checkout`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(json => {
                if (json.success) {
                    location.reload();
                } else {
                    alert('Erreur: ' + json.message);
                }
            });
    }

    function downloadQRCode(badgeId) {
        const link = document.createElement('a');
        link.href = `https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=${encodeURIComponent(badgeId)}`;
        link.download = `badge_${badgeId}.png`;
        link.click();
    }
</script>