<div class="min-vh-100 d-flex align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow border-0">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">
                            <i class="bi bi-person-badge"></i> Enregistrement Visiteur
                        </h3>
                    </div>

                    <div class="card-body p-4">
                        <!-- Alert messages -->
                        <div id="alertContainer"></div>

                        <!-- Registration form -->
                        <form id="visitorForm" method="POST">
                            <?= csrf_field() ?>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom *</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="nom" 
                                        name="nom"
                                        required
                                        minlength="2"
                                        maxlength="50"
                                        placeholder="Dupont"
                                    >
                                    <small class="text-danger d-none" id="nom_error"></small>
                                </div>
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom *</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="prenom" 
                                        name="prenom"
                                        required
                                        minlength="2"
                                        maxlength="50"
                                        placeholder="Jean"
                                    >
                                    <small class="text-danger d-none" id="prenom_error"></small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        id="email" 
                                        name="email"
                                        required
                                        placeholder="jean.dupont@exemple.com"
                                    >
                                    <small class="text-danger d-none" id="email_error"></small>
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone *</label>
                                    <input 
                                        type="tel" 
                                        class="form-control" 
                                        id="telephone" 
                                        name="telephone"
                                        required
                                        placeholder="+237 6XX XXX XXX"
                                    >
                                    <small class="text-danger d-none" id="telephone_error"></small>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="entreprise" class="form-label">Entreprise</label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="entreprise" 
                                        name="entreprise"
                                        maxlength="100"
                                        placeholder="Entreprise (optionnel)"
                                    >
                                </div>
                                <div class="col-md-6">
                                    <label for="motif" class="form-label">Motif de visite *</label>
                                    <select class="form-select" id="motif" name="motif" required>
                                        <option value="">-- Sélectionner --</option>
                                        <option value="Reunion">Réunion</option>
                                        <option value="Livraison">Livraison</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Formation">Formation</option>
                                        <option value="Consultation">Consultation</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                    <small class="text-danger d-none" id="motif_error"></small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="personne_a_voir" class="form-label">Personne à voir *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="personne_a_voir" 
                                    name="personne_a_voir"
                                    required
                                    minlength="3"
                                    maxlength="100"
                                    placeholder="Nom de la personne à rencontrer"
                                >
                                <small class="text-danger d-none" id="personne_a_voir_error"></small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-check-circle"></i> Enregistrer l'arrivée
                            </button>
                        </form>

                        <!-- Current visitors count -->
                        <hr>
                        <p class="text-center text-muted mb-0">
                            <strong><?= count($presentVisitors) ?></strong> visiteur(s) présent(s) actuellement
                        </p>
                    </div>
                </div>
            </div>

            <!-- Current visitors sidebar -->
            <div class="col-lg-5 mt-4 mt-lg-0">
                <div class="card shadow border-0">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-building"></i> Visiteurs actuels
                        </h5>
                    </div>
                    <div class="card-body p-3" style="max-height: 500px; overflow-y: auto;">
                        <?php if (empty($presentVisitors)): ?>
                            <p class="text-muted text-center py-4">Aucun visiteur actuellement</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($presentVisitors as $visitor): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?= esc($visitor['prenom'] . ' ' . $visitor['nom']) ?></h6>
                                                <small class="text-muted">
                                                    <i class="bi bi-briefcase"></i> <?= esc($visitor['motif']) ?>
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i> <?= substr($visitor['heure_arrivee'], 0, 5) ?> - 
                                                    <span id="duration_<?= $visitor['id'] ?>">Calcul...</span>
                                                </small>
                                            </div>
                                            <button 
                                                type="button" 
                                                class="btn btn-sm btn-danger"
                                                onclick="checkoutVisitor(<?= $visitor['id'] ?>)"
                                            >
                                                <i class="bi bi-door-open"></i> Départ
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- View history button -->
                <a href="<?= base_url('visitor/history') ?>" class="btn btn-outline-secondary w-100 mt-3">
                    <i class="bi bi-clock-history"></i> Historique des visites
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Form submission
document.getElementById('visitorForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(document.getElementById('visitorForm'));
    
    try {
        const response = await fetch('<?= base_url('visitor/register') ?>', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            showAlert('success', 'Visiteur enregistré', `Badge: ${data.badge_id}`);
            document.getElementById('visitorForm').reset();
            
            // Reload visitors list after 2 seconds
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert('danger', 'Erreur', data.message || 'Veuillez vérifier les données');
            if (data.errors) {
                displayValidationErrors(data.errors);
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'Erreur', 'Une erreur s\'est produite lors de l\'enregistrement');
    }
});

// Checkout visitor
async function checkoutVisitor(visitourId) {
    if (!confirm('Confirmer le départ de ce visiteur ?')) return;

    try {
        const response = await fetch('<?= base_url('visitor/checkout') ?>/' + visitourId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json();

        if (data.success) {
            showAlert('success', 'Départ enregistré', data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert('danger', 'Erreur', data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('danger', 'Erreur', 'Une erreur s\'est produite');
    }
}

// Show alert
function showAlert(type, title, message) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        <strong>${title}</strong>: ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.getElementById('alertContainer');
    container.innerHTML = '';
    container.appendChild(alert);
}

// Display validation errors
function displayValidationErrors(errors) {
    for (const [field, error] of Object.entries(errors)) {
        const errorEl = document.getElementById(`${field}_error`);
        if (errorEl) {
            errorEl.textContent = error;
            errorEl.classList.remove('d-none');
        }
    }
}

// Calculate visit duration
function updateDurations() {
    <?php foreach ($presentVisitors as $visitor): ?>
        const arrival = new Date('2000-01-01 <?= $visitor['heure_arrivee'] ?>');
        const now = new Date('2000-01-01 ' + new Date().toLocaleTimeString('fr-FR'));
        const diff = Math.floor((now - arrival) / 60000);
        const hours = Math.floor(diff / 60);
        const minutes = diff % 60;
        
        let durationText = '';
        if (hours > 0) {
            durationText = `${hours}h ${minutes}min`;
        } else {
            durationText = `${minutes}min`;
        }
        
        const el = document.getElementById('duration_<?= $visitor['id'] ?>');
        if (el) el.textContent = durationText;
    <?php endforeach; ?>
}

// Update durations every minute
updateDurations();
setInterval(updateDurations, 60000);
</script>