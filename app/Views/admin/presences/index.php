<?php
$dt = new DateTime($date);
$dayNames = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
$dayIndex = (int) $dt->format('w');
?>

<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-clock-history"></i>
                Suivi quotidien
            </span>
            <h1 class="page-hero-title mb-2">Pointages du jour</h1>
            <p class="page-hero-copy mb-0"><?= $dt->format('d/m/Y') ?> (<?= $dayNames[$dayIndex] ?>)</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= site_url('admin/presences/history') ?>" class="btn btn-light border">
                <i class="bi bi-clock-history me-2"></i>Historique
            </a>
            <a href="<?= site_url('admin/presences/statistics') ?>" class="btn btn-light border">
                <i class="bi bi-bar-chart me-2"></i>Statistiques
            </a>
        </div>
    </div>
</section>

<?php if (session()->has('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc((string) session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

<?php if (session()->has('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc((string) session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

<!-- Date selector -->
<div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
        <h2 class="h5 mb-1">Selection de la journee</h2>
        <p class="text-muted small mb-0">Changez de date et accedez rapidement aux vues archivees ou statistiques.</p>
    </div>
    <div class="card-body p-4">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-12 col-md-4 col-lg-3">
                <label for="date" class="form-label fw-semibold">Sélectionner une date</label>
                <input
                    type="date"
                    class="form-control"
                    id="date"
                    name="date"
                    value="<?= $date ?>">
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i>Afficher
                </button>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= site_url('admin/presences/index?date=' . date('Y-m-d')) ?>" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-calendar-today me-1"></i>Aujourd'hui
                </a>
            </div>
            <div class="col-12 col-lg-5">
                <div class="d-grid d-sm-flex gap-2 justify-content-lg-end">
                    <a href="<?= site_url('admin/presences/history') ?>" class="btn btn-outline-info flex-fill flex-sm-grow-0">
                        <i class="bi bi-history me-1"></i>Historique
                    </a>
                    <a href="<?= site_url('admin/presences/statistics') ?>" class="btn btn-outline-warning flex-fill flex-sm-grow-0">
                        <i class="bi bi-bar-chart me-1"></i>Statistiques
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
$attendanceRate = $stats['total'] > 0 ? round((($stats['presents'] + $stats['retards']) / $stats['total']) * 100) : 0;
?>

<!-- Statistics cards -->
<div class="row g-3 g-xl-4 mb-4">
    <?= view('components/kpi_card', [
        'icon' => 'bi-people-fill',
        'value' => $stats['total'],
        'label' => 'Total pointages',
        'color' => 'primary',
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-check-circle-fill',
        'value' => $stats['presents'],
        'label' => "À l'heure",
        'color' => 'success',
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-clock-history',
        'value' => $stats['retards'],
        'label' => 'Retardataires',
        'color' => 'warning',
    ]) ?>

    <?= view('components/kpi_card', [
        'icon' => 'bi-x-circle-fill',
        'value' => $stats['absents'],
        'label' => 'Absents',
        'color' => 'danger',
    ]) ?>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div>
                <h2 class="h6 text-uppercase text-muted mb-2">Taux de presence du jour</h2>
                <div class="d-flex align-items-end gap-2">
                    <span class="display-6 mb-0 fw-semibold"><?= $attendanceRate ?>%</span>
                    <span class="text-muted pb-2">(présents + retards)</span>
                </div>
            </div>
            <div class="w-100" style="max-width: 420px;">
                <div class="progress" role="progressbar" aria-label="Taux de presence" aria-valuenow="<?= $attendanceRate ?>" aria-valuemin="0" aria-valuemax="100" style="height: 12px;">
                    <div class="progress-bar bg-success" style="width: <?= $attendanceRate ?>%;"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted mt-2">
                    <span>0%</span>
                    <span>Objectif interne: 95%</span>
                    <span>100%</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Presences table -->
<div class="card shadow-sm border-0">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h2 class="h5 mb-0">Liste des pointages</h2>
        <span class="badge rounded-pill text-bg-light border text-secondary px-3 py-2"><?= esc((string) count($presences)) ?> ligne(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <caption class="visually-hidden">Liste des pointages du jour avec statut, horaires et actions de correction.</caption>
            <thead>
                <tr>
                    <th scope="col">Matricule</th>
                    <th scope="col">Employe</th>
                    <th scope="col">Arrivee</th>
                    <th scope="col">Depart</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Retard</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($presences)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Aucun pointage pour cette date
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($presences as $presence): ?>
                        <?php
                        $employeeName = trim(implode(' ', array_filter([
                            (string) ($presence['prenom'] ?? ''),
                            (string) ($presence['nom'] ?? ''),
                        ])));
                        $employeeName = $employeeName !== '' ? $employeeName : 'Employe introuvable';
                        $employeeMatricule = trim((string) ($presence['matricule'] ?? ''));
                        $employeeMatricule = $employeeMatricule !== '' ? $employeeMatricule : 'Matricule indisponible';
                        ?>
                        <tr>
                            <td>
                                <span class="badge-time"><?= esc($employeeMatricule) ?></span>
                            </td>
                            <td>
                                <strong><?= esc($employeeName) ?></strong>
                            </td>
                            <td>
                                <?php if ($presence['heure_pointage']): ?>
                                    <span class="badge-time"><?= substr($presence['heure_pointage'], 0, 5) ?></span>
                                    <?php if ($presence['corrige']): ?>
                                        <span class="badge rounded-pill text-bg-info">Corrigé</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-danger">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $presence['heure_sortie'] ? '<span class="badge-time">' . substr($presence['heure_sortie'], 0, 5) . '</span>' : '—' ?>
                            </td>
                            <td>
                                <?php if ($presence['statut'] === 'present'): ?>
                                    <span class="badge rounded-pill text-bg-success">À l'heure</span>
                                <?php elseif ($presence['statut'] === 'retard'): ?>
                                    <span class="badge rounded-pill text-bg-warning">Retard</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill text-bg-danger">Absent</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if ($presence['statut'] === 'retard' && $presence['heure_pointage']) {
                                    echo $presence['retard_minutes'] ?? '0';
                                    echo ' min';
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                            <td class="text-end">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-primary text-nowrap"
                                    data-bs-toggle="modal"
                                    data-bs-target="#correctModal"
                                    aria-label="Corriger le pointage de <?= esc($employeeName) ?>"
                                    onclick="openCorrectionForm(<?= $presence['id'] ?>, '<?= esc($employeeName, 'js') ?>', '<?= esc($employeeMatricule, 'js') ?>')">
                                    <i class="bi bi-pencil"></i> Corriger
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Correction Modal -->
<div class="modal fade" id="correctModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="correctModalLabel">Corriger un pointage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="correctionForm" method="POST" aria-labelledby="correctModalLabel">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div id="correctionError" class="alert alert-danger d-none"></div>

                    <div class="alert alert-light border mb-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-2">
                            <div>
                                <div class="small text-muted">Employe</div>
                                <div id="correctionEmploye" class="fw-semibold">—</div>
                            </div>
                            <div class="text-sm-end">
                                <div class="small text-muted">Matricule</div>
                                <div id="correctionMatricule" class="badge-time">—</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="statut" class="form-label">Statut *</label>
                        <select class="form-select" id="statut" name="statut" required>
                            <option value="">-- Sélectionner --</option>
                            <option value="present">À l'heure</option>
                            <option value="retard">Retard</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="heure_pointage" class="form-label">Heure arrivée</label>
                        <input
                            type="time"
                            class="form-control"
                            id="heure_pointage"
                            name="heure_pointage"
                            placeholder="HH:MM">
                        <small class="text-muted">Laisser vide pour garder l'heure actuelle</small>
                    </div>

                    <div class="mb-3">
                        <label for="heure_sortie" class="form-label">Heure départ</label>
                        <input
                            type="time"
                            class="form-control"
                            id="heure_sortie"
                            name="heure_sortie"
                            placeholder="HH:MM">
                    </div>

                    <div class="mb-3">
                        <label for="motif_correction" class="form-label">Motif de la correction</label>
                        <textarea
                            class="form-control"
                            id="motif_correction"
                            name="motif_correction"
                            rows="3"
                            maxlength="255"
                            placeholder="Ex: Raison de la correction..."></textarea>
                        <small class="text-muted">Optionnel</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submitCorrectionBtn">Enregistrer la correction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let currentPresenceId = null;

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    async function openCorrectionForm(presenceId, employeNom, matricule) {
        currentPresenceId = presenceId;
        const errorEl = document.getElementById('correctionError');
        errorEl.classList.add('d-none');
        errorEl.textContent = '';

        const employeEl = document.getElementById('correctionEmploye');
        const matriculeEl = document.getElementById('correctionMatricule');
        employeEl.textContent = employeNom || 'Employe introuvable';
        matriculeEl.textContent = matricule || 'Matricule indisponible';

        try {
            const response = await fetch(`<?= base_url('admin/presences/correct') ?>/${presenceId}`);

            if (!response.ok) {
                throw new Error('Reponse invalide du serveur');
            }

            const data = await response.json();

            if (!data.success) {
                errorEl.textContent = data.message || 'Impossible de charger le pointage.';
                errorEl.classList.remove('d-none');
                return;
            }

            const p = data.presence;
            document.getElementById('statut').value = p.statut || '';
            document.getElementById('heure_pointage').value = p.heure_pointage ? p.heure_pointage.substring(0, 5) : '';
            document.getElementById('heure_sortie').value = p.heure_sortie ? p.heure_sortie.substring(0, 5) : '';
            document.getElementById('motif_correction').value = p.motif_correction || '';

            // Update CSRF token if provided
            if (data.csrfHash) {
                const tokenInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
                if (tokenInput) {
                    tokenInput.value = data.csrfHash;
                }
            }
        } catch (error) {
            console.error('Error:', error);
            errorEl.textContent = 'Erreur réseau lors du chargement du pointage.';
            errorEl.classList.remove('d-none');
        }
    }

    document.getElementById('correctionForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!currentPresenceId) {
            if (typeof window.showToast === 'function') {
                window.showToast('ID du pointage manquant.', 'danger');
            }
            return;
        }

        const submitBtn = document.getElementById('submitCorrectionBtn');
        const initialLabel = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Enregistrement...';

        const formData = new FormData(document.getElementById('correctionForm'));

        try {
            const response = await fetch(`<?= base_url('admin/presences/store-correction') ?>/${currentPresenceId}`, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error('Reponse invalide du serveur');
            }

            const data = await response.json();

            if (data.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast('Correction enregistree avec succes.', 'success');
                }
                const modal = bootstrap.Modal.getInstance(document.getElementById('correctModal'));
                modal.hide();
                // Reload page to show updated data
                setTimeout(() => location.reload(), 1000);
            } else {
                const errorEl = document.getElementById('correctionError');
                if (data.errors) {
                    errorEl.innerHTML = Object.values(data.errors).join('<br>');
                } else {
                    errorEl.textContent = data.message || 'Une erreur s\'est produite';
                }
                errorEl.classList.remove('d-none');

                if (typeof window.showToast === 'function') {
                    window.showToast('La correction n\'a pas pu etre enregistree.', 'warning');
                }

                if (data.csrfHash) {
                    const tokenInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
                    if (tokenInput) {
                        tokenInput.value = data.csrfHash;
                    }
                }
            }
        } catch (error) {
            console.error('Error:', error);
            const errorEl = document.getElementById('correctionError');
            errorEl.textContent = 'Une erreur s\'est produite lors de la correction';
            errorEl.classList.remove('d-none');
            if (typeof window.showToast === 'function') {
                window.showToast('Erreur reseau pendant la correction.', 'danger');
            }
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = initialLabel;
        }
    });

    document.getElementById('correctModal').addEventListener('hidden.bs.modal', () => {
        currentPresenceId = null;
        document.getElementById('correctionForm').reset();
        const errorEl = document.getElementById('correctionError');
        errorEl.classList.add('d-none');
        errorEl.textContent = '';
        document.getElementById('correctionEmploye').textContent = '—';
        document.getElementById('correctionMatricule').textContent = '—';
    });
</script>