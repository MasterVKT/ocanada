<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-0">
                <i class="bi bi-person-plus"></i> Enregistrement visiteur
            </h1>
            <small class="text-muted">Rapide et simple pour l'agent d'accueil</small>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <!-- Registration Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-form-check"></i> Formulaire</h6>
                </div>
                <div class="card-body">
                    <form id="visitorForm">
                        <?= csrf_field() ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">Prénom *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="prenom"
                                    name="prenom"
                                    placeholder="Jean"
                                    required
                                    autocomplete="off">
                                <div class="invalid-feedback" id="error-prenom"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="nom"
                                    name="nom"
                                    placeholder="Dupont"
                                    required
                                    autocomplete="off">
                                <div class="invalid-feedback" id="error-nom"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                placeholder="jean.dupont@example.com"
                                required
                                autocomplete="email">
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>

                        <div class="mb-3">
                            <label for="telephone" class="form-label">Téléphone *</label>
                            <input
                                type="tel"
                                class="form-control"
                                id="telephone"
                                name="telephone"
                                placeholder="+237 6 12 34 56 78"
                                required
                                autocomplete="tel">
                            <div class="invalid-feedback" id="error-telephone"></div>
                        </div>

                        <div class="mb-3">
                            <label for="motif" class="form-label">Motif de visite *</label>
                            <select class="form-select" id="motif" name="motif" required>
                                <option value="">-- Sélectionner --</option>
                                <option value="Réunion d'affaires">Réunion d'affaires</option>
                                <option value="Livraison">Livraison</option>
                                <option value="Visite officielle">Visite officielle</option>
                                <option value="Entretien">Entretien</option>
                                <option value="Formation">Formation</option>
                                <option value="Autre">Autre</option>
                            </select>
                            <div class="invalid-feedback" id="error-motif"></div>
                        </div>

                        <div class="mb-3">
                            <label for="personne_a_voir" class="form-label">Personne à voir *</label>
                            <input
                                type="text"
                                class="form-control"
                                id="personne_a_voir"
                                name="personne_a_voir"
                                placeholder="Nom du responsable"
                                required
                                autocomplete="off">
                            <div class="invalid-feedback" id="error-personne_a_voir"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-check-circle"></i> Enregistrer la visite
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: QR Code Display -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4" id="successCard" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-check-circle"></i> Enregistrement réussi</h6>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-success mb-3" id="successMessage"></div>

                    <h5 class="mb-3">
                        <span id="visitorName"></span>
                    </h5>

                    <div class="mb-4" style="padding: 20px; background-color: #f8f9fa; border-radius: 8px;">
                        <img id="qrCode" src="" alt="QR Code" class="img-fluid" style="max-width: 250px;">
                    </div>

                    <div class="badge-info mb-4 p-3 bg-light rounded">
                        <div class="mb-2">
                            <strong>Badge ID:</strong>
                            <div id="badgeId" style="font-size: 18px; font-weight: bold; letter-spacing: 2px;"></div>
                        </div>
                        <small class="text-muted">À remettre au visiteur</small>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <button class="btn btn-outline-primary w-100" onclick="printBadge()">
                                <i class="bi bi-printer"></i> Imprimer
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-outline-secondary w-100" onclick="resetForm()">
                                <i class="bi bi-plus-circle"></i> Nouveau
                            </button>
                        </div>
                    </div>

                    <hr>

                    <a href="<?= base_url('agent/visitors/current') ?>" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="bi bi-list"></i> Voir les visiteurs actuels
                    </a>
                </div>
            </div>

            <!-- How to use -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-lightbulb"></i> Conseils</h6>
                </div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li>Remplissez tous les champs obligatoires</li>
                        <li>Imprimez le badge QR pour le visiteur</li>
                        <li>Conservez le numéro de badge pour le départ</li>
                        <li>Les données sont enregistrées automatiquement</li>
                        <li>Consultez les visiteurs actuels à tout moment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('visitorForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const form = document.getElementById('visitorForm');
        const errorCards = document.querySelectorAll('.invalid-feedback');
        errorCards.forEach(el => el.textContent = '');

        const formData = new FormData(form);

        try {
            const response = await fetch('<?= base_url('agent/visitors/store') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            });

            let json = null;
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                json = await response.json();
            }

            if (response.status === 403) {
                alert('Session expirée ou token CSRF invalide. La page va être rechargée.');
                window.location.reload();
                return;
            }

            if (!response.ok && !json) {
                throw new Error(`HTTP ${response.status}`);
            }

            // Refresh CSRF token if provided (CI4 regeneration)
            if (json.csrfToken) {
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.content = json.csrfToken;
                const hidden = document.querySelector('input[name="<?= csrf_token() ?>"]');
                if (hidden) hidden.value = json.csrfToken;
            }

            if (json && json.success) {
                // Show success
                document.getElementById('successCard').style.display = 'block';
                document.getElementById('successMessage').textContent = json.message;
                document.getElementById('visitorName').textContent = formData.get('prenom') + ' ' + formData.get('nom');
                document.getElementById('badgeId').textContent = json.badgeId;
                document.getElementById('qrCode').src = json.qrCodeUrl;

                // Store for printing
                window.lastVisitorId = json.visiteurId;
                window.lastBadgeId = json.badgeId;

                // Focus on print button
                document.querySelector('#successCard button').focus();
            } else if (json && json.errors) {
                // Show validation errors
                Object.entries(json.errors).forEach(([field, message]) => {
                    const el = document.getElementById(`error-${field}`);
                    if (el) el.textContent = message;
                });
                form.classList.add('was-validated');
            } else if (json && json.message) {
                alert(json.message);
            }
        } catch (error) {
            alert('Erreur: ' + error.message);
        }
    });

    function printBadge() {
        if (!window.lastVisitorId) return;
        window.open(`<?= base_url('agent/visitors') ?>/${window.lastVisitorId}/print-badge`, '_blank');
    }

    function resetForm() {
        document.getElementById('visitorForm').reset();
        document.getElementById('visitorForm').classList.remove('was-validated');
        document.getElementById('successCard').style.display = 'none';
        document.getElementById('prenom').focus();
    }
</script>

<style>
    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>