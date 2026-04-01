<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Nouvelle Demande de Congé</h1>
        <a href="<?= base_url('/employe/leaves') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-chevron-left"></i> Retour
        </a>
    </div>

    <div class="row">
        <!-- Form -->
        <div class="col-lg-8">
            <form method="post" action="<?= base_url('/employe/leaves') ?>" class="card shadow-sm">
                <?= csrf_field() ?>

                <!-- Solde Info Alert -->
                <?php if ($solde): ?>
                    <div class="alert alert-info m-4 mb-0">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Solde actuel:</strong>
                                <span class="text-success"><?= number_format($solde['restant'], 1, ',', '') ?> jours</span>
                            </div>
                            <div class="col-md-6 text-end">
                                <small class="text-muted">
                                    <?= number_format($solde['pris'], 1, ',', '') ?> jours utilisés sur
                                    <?= number_format($solde['solde_annuel'], 1, ',', '') ?> jours
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card-body">
                    <!-- Type de Congé -->
                    <div class="mb-4">
                        <label class="form-label">Type de Congé <span class="text-danger">*</span></label>
                        <select name="type_conge" id="typeConge" class="form-select" required>
                            <option value="" disabled selected>-- Sélectionner un type --</option>
                            <option value="annuel">Congé Annuel</option>
                            <option value="maladie">Congé Maladie</option>
                            <option value="maternite_paternite">Congé Maternité/Paternité</option>
                            <option value="sans_solde">Congé Sans Solde</option>
                            <option value="autre">Autre Raison</option>
                        </select>
                        <small class="form-text text-muted">Sélectionnez le type de congé demandé</small>
                    </div>

                    <!-- Dates -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Date de Début <span class="text-danger">*</span></label>
                            <input type="date" name="date_debut" id="dateDebut" class="form-control" required
                                min="<?= date('Y-m-d') ?>">
                            <small class="form-text text-muted">À partir d'aujourd'hui</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de Fin <span class="text-danger">*</span></label>
                            <input type="date" name="date_fin" id="dateFin" class="form-control" required
                                min="<?= date('Y-m-d') ?>">
                            <small class="form-text text-muted">Inclus</small>
                        </div>
                    </div>

                    <!-- Working Days Display -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Jours Ouvrables</label>
                                <input type="text" id="joursMoyen" class="form-control" value="0" readonly>
                                <input type="hidden" name="nombre_jours" id="nombreJours">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="calculateDays()">
                                    <i class="bi bi-calculator"></i> Calculer
                                </button>
                            </div>
                        </div>
                        <div id="calculationResult" class="mt-2"></div>
                    </div>

                    <!-- Motif -->
                    <div class="mb-4">
                        <label class="form-label">Motif / Raison <span class="text-danger">*</span></label>
                        <textarea name="motif" id="motif" class="form-control" rows="4" required
                            placeholder="Veuillez expliquer brièvement les raisons de votre demande..."></textarea>
                        <small class="form-text text-muted">Min. 10 caractères</small>
                    </div>

                    <!-- AI Assistant Button (optional) -->
                    <div class="mb-4">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="showAIAssistant()">
                            <i class="bi bi-lightbulb"></i> Aide à la rédaction (IA)
                        </button>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="card-footer bg-light">
                    <div class="d-flex gap-2">
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Soumettre la Demande
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Rules Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-info-circle"></i> À Savoir
                    </h6>
                </div>
                <div class="card-body small">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Les congés sont comptés en <strong>jours ouvrables</strong> (lundi-vendredi)</li>
                        <li class="mb-2">Les <strong>jours fériés</strong> ne sont pas comptés</li>
                        <li class="mb-2">Vous ne pouvez demander que le solde disponible</li>
                        <li class="mb-2">Demande à soumettre au moins <strong>3 jours avant</strong></li>
                        <li>Les congés maternité ne déduisent pas du solde</li>
                    </ul>
                </div>
            </div>

            <!-- Calendar Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="bi bi-calendar"></i> Jours Fériés <?= date('Y') ?>
                    </h6>
                </div>
                <div class="card-body small">
                    <p class="text-muted mb-2">Jours non-ouvrables à considérer:</p>
                    <ul class="mb-0 ps-3" id="holidaysList">
                        <li>Chargement...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AI Assistant Modal -->
<div class="modal fade" id="aiAssistantModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-sparkles"></i> Assistant de Rédaction (IA)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Claude pourra vous aider à rédiger un motif plus formel et convaincant.</p>
                <div class="mb-3">
                    <label class="form-label">Votre brouillon (optionnel)</label>
                    <textarea id="aiInput" class="form-control" rows="3"
                        placeholder="Ou laissez vide pour une suggestion générale..."></textarea>
                </div>
                <div id="aiResult" class="mb-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-primary" onclick="callAIAssistant()">
                    <i class="bi bi-lightning"></i> Générer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize date calculations
    document.getElementById('dateDebut').addEventListener('change', calculateDays);
    document.getElementById('dateFin').addEventListener('change', calculateDays);
    document.getElementById('typeConge').addEventListener('change', calculateDays);

    function calculateDays() {
        const dateDebut = document.getElementById('dateDebut').value;
        const dateFin = document.getElementById('dateFin').value;

        if (!dateDebut || !dateFin) {
            document.getElementById('joursMoyen').value = '0';
            return;
        }

        const url = '<?= base_url('/employe/leaves/calculate-working-days') ?>' +
            '?date_debut=' + encodeURIComponent(dateDebut) +
            '&date_fin=' + encodeURIComponent(dateFin);

        fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
            .then(async (res) => {
                const data = await res.json();
                if (!res.ok) {
                    throw new Error(data.message || 'Calcul echoue');
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('joursMoyen').value = data.working_days;
                    document.getElementById('nombreJours').value = data.working_days;

                    // Check solde
                    const soldeRestant = <?= $solde['restant'] ?? 0 ?>;
                    const result = document.getElementById('calculationResult');

                    if (data.working_days > soldeRestant) {
                        result.innerHTML = `
                    <div class="alert alert-danger small">
                        <i class="bi bi-exclamation-triangle"></i>
                        Solde insuffisant! Vous demandez ${data.working_days} jours mais n'avez que ${soldeRestant} jours disponibles.
                    </div>
                `;
                    } else {
                        result.innerHTML = `
                    <div class="alert alert-success small">
                        <i class="bi bi-check-circle"></i>
                        ${data.working_days} jours ouvrables. Solde: ${soldeRestant - data.working_days} jours restants après.
                    </div>
                `;
                    }
                } else {
                    alert('Erreur: ' + (data.message || 'Calcul échoué'));
                }
            })
            .catch((error) => {
                alert('Erreur: ' + error.message);
            });
    }

    function showAIAssistant() {
        new bootstrap.Modal(document.getElementById('aiAssistantModal')).show();
    }

    function callAIAssistant() {
        const userInput = document.getElementById('aiInput').value;
        const typeConge = document.getElementById('typeConge').value;
        const result = document.getElementById('aiResult');

        result.innerHTML = '<div class="text-muted"><i class="bi bi-hourglass-split"></i> Génération...</div>';

        window.securePost('<?= base_url('/ia/assistant-conge') ?>', {
                user_input: userInput,
                type_conge: typeConge
            })
            .then(data => {
                if (data.success) {
                    result.innerHTML = `
                <div class="alert alert-info">
                    <p class="mb-2"><strong>Suggestion:</strong></p>
                    <p class="mb-0">${data.suggestion}</p>
                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-primary" 
                                onclick="useSuggestion('${data.suggestion.replace(/'/g, "\\'")}')">
                            Utiliser cette suggestion
                        </button>
                    </div>
                </div>
            `;
                } else {
                    result.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            });
    }

    function useSuggestion(text) {
        document.getElementById('motif').value = text;
        bootstrap.Modal.getInstance(document.getElementById('aiAssistantModal')).hide();
    }

    // Load holidays
    fetch('<?= base_url('/api/holidays') ?>')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.holidays) {
                const list = document.getElementById('holidaysList');
                list.innerHTML = data.holidays
                    .map(h => `<li>${new Date(h.date).toLocaleDateString('fr-FR')} - ${h.name}</li>`)
                    .join('');
            }
        });
</script>