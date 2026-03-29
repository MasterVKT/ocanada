<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0">
                <i class="bi bi-people"></i> Visiteurs actuels
            </h1>
            <small class="text-muted">Personnes présentes sur le site</small>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="<?= base_url('agent/visitors/register') ?>" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Enregistrer un visiteur
            </a>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?= base_url('agent/visitors/history') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history"></i> Historique
            </a>
        </div>
    </div>

    <!-- Stats Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-0">
                <i class="bi bi-person-check"></i>
                <?= count($visitors) ?> visiteur<?= count($visitors) > 1 ? 's' : '' ?> présent<?= count($visitors) > 1 ? 's' : '' ?>
            </h5>
        </div>
    </div>

    <!-- Visitors Grid -->
    <?php if (empty($visitors)): ?>
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
            <p class="mb-0 mt-3">Aucun visiteur présent actuellement</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($visitors as $v): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <?= esc($v['prenom'] . ' ' . $v['nom']) ?>
                            </h5>
                            <small class="text-muted">
                                <i class="bi bi-badge-qr"></i> <?= esc($v['badge_id']) ?>
                            </small>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong class="text-muted">Motif:</strong>
                                <p class="mb-0"><?= esc($v['motif']) ?></p>
                            </div>
                            <div class="mb-2">
                                <strong class="text-muted">Pour voir:</strong>
                                <p class="mb-0"><?= esc($v['personne_a_voir']) ?></p>
                            </div>
                            <div class="mb-0">
                                <strong class="text-muted">Entreprise:</strong>
                                <p class="mb-0"><?= esc($v['entreprise'] ?? 'N/A') ?></p>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="small mb-2">
                                <i class="bi bi-clock"></i> Arrivée: <?= substr($v['heure_arrivee'], 0, 5) ?>
                            </div>
                            <button 
                                class="btn btn-sm btn-warning w-100" 
                                onclick="checkoutVisitor(<?= $v['id'] ?>, '<?= esc($v['prenom']) ?>')">
                                <i class="bi bi-box-arrow-right"></i> Marquer comme parti
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function checkoutVisitor(visitorId, visitorName) {
    if (!confirm(`Confirmer le départ de ${visitorName} ?`)) return;

    fetch(`<?= base_url('agent/visitors') ?>/${visitorId}/checkout`, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
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
</script>
