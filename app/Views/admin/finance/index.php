<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-graph-up-arrow"></i>
                Pilotage RH
            </span>
            <h1 class="page-hero-title mb-2">Tableau financier</h1>
            <p class="page-hero-copy mb-0">Suivi du cout de l absenteeisme et des retards sur la periode selectionnee.</p>
        </div>
        <a href="<?= base_url('/admin/finance/export-csv?' . http_build_query([
            'periode' => $filters['periode'] ?? 'mois_courant',
            'mois' => $filters['anchorMonth'] ?? date('Y-m'),
            'date_debut' => $filters['periodStart'] ?? date('Y-m-01'),
            'date_fin' => $filters['periodEnd'] ?? date('Y-m-t'),
            'departement' => $filters['departement'] ?? '',
        ])) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-download"></i> Export CSV
        </a>
    </div>
</section>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="get">
            <div class="col-12 col-lg-3">
                <label for="periode" class="form-label">Période</label>
                <select id="periode" name="periode" class="form-select">
                    <option value="mois_courant" <?= (($filters['periode'] ?? '') === 'mois_courant') ? 'selected' : '' ?>>Mois courant</option>
                    <option value="mois_precedent" <?= (($filters['periode'] ?? '') === 'mois_precedent') ? 'selected' : '' ?>>Mois précédent</option>
                    <option value="personnalise" <?= (($filters['periode'] ?? '') === 'personnalise') ? 'selected' : '' ?>>Plage personnalisée</option>
                </select>
            </div>
            <div class="col-12 col-lg-3">
                <label for="mois" class="form-label">Mois de référence</label>
                <input id="mois" type="month" name="mois" class="form-control" value="<?= esc($filters['anchorMonth'] ?? date('Y-m')) ?>">
            </div>
            <div class="col-12 col-lg-2">
                <label for="date_debut" class="form-label">Date début</label>
                <input id="date_debut" type="date" name="date_debut" class="form-control" value="<?= esc($filters['periodStart'] ?? date('Y-m-01')) ?>">
            </div>
            <div class="col-12 col-lg-2">
                <label for="date_fin" class="form-label">Date fin</label>
                <input id="date_fin" type="date" name="date_fin" class="form-control" value="<?= esc($filters['periodEnd'] ?? date('Y-m-t')) ?>">
            </div>
            <div class="col-12 col-lg-2">
                <label for="departement" class="form-label">Département</label>
                <select id="departement" name="departement" class="form-select">
                    <option value="">Tous</option>
                    <?php foreach (($departements ?? []) as $departement): ?>
                        <option value="<?= esc($departement) ?>" <?= (($filters['departement'] ?? '') === $departement) ? 'selected' : '' ?>><?= esc($departement) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 d-flex gap-2 justify-content-end">
                <button type="button" class="btn btn-outline-secondary" data-finance-preset="thisMonth">Mois courant</button>
                <button type="button" class="btn btn-outline-secondary" data-finance-preset="last30">30 derniers jours</button>
                <button type="button" class="btn btn-outline-secondary" data-finance-preset="quarter">Trimestre</button>
                <button type="submit" class="btn btn-primary">Appliquer</button>
                <a href="<?= base_url('/admin/finance') ?>" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
            <div class="col-12">
                <div id="financeDateFeedback" class="invalid-feedback d-none">La date de fin doit être postérieure ou égale à la date de début.</div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <?= view('components/kpi_card', ['icon' => 'bi-cash-stack', 'value' => format_xaf((float) ($summary['cout_absenteisme'] ?? 0)), 'label' => 'Cout absenteeisme', 'color' => 'danger']) ?>
    </div>
    <div class="col-12 col-md-4">
        <?= view('components/kpi_card', ['icon' => 'bi-alarm', 'value' => format_xaf((float) ($summary['cout_retards'] ?? 0)), 'label' => 'Impact retards', 'color' => 'warning']) ?>
    </div>
    <div class="col-12 col-md-4">
        <?= view('components/kpi_card', ['icon' => 'bi-activity', 'value' => format_xaf((float) ($summary['cout_total'] ?? 0)), 'label' => 'Impact total', 'color' => 'primary']) ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <?= view('components/kpi_card', ['icon' => 'bi-calendar-x', 'value' => (int) ($summary['total_absences'] ?? 0), 'label' => 'Absences injustifiées', 'color' => 'danger']) ?>
    </div>
    <div class="col-12 col-md-4">
        <?= view('components/kpi_card', ['icon' => 'bi-stopwatch', 'value' => (int) ($summary['total_retard_minutes'] ?? 0) . ' min', 'label' => 'Retards cumulés', 'color' => 'warning']) ?>
    </div>
    <div class="col-12 col-md-4">
        <?= view('components/kpi_card', ['icon' => 'bi-hourglass-split', 'value' => number_format((float) ($summary['retard_equivalent_jours'] ?? 0), 2, ',', ' ') . ' j', 'label' => 'Équivalent jours retard', 'color' => 'primary']) ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="small text-muted">Periode</div>
                <div class="fw-semibold"><?= esc(format_date_fr($periodStart ?? date('Y-m-01'))) ?> - <?= esc(format_date_fr($periodEnd ?? date('Y-m-t'))) ?></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="small text-muted">Total absences</div>
                <div class="fw-semibold"><?= (int) ($summary['total_absences'] ?? 0) ?></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="small text-muted">Minutes de retard</div>
                <div class="fw-semibold"><?= (int) ($summary['total_retard_minutes'] ?? 0) ?></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="small text-muted">Département</div>
                <div class="fw-semibold"><?= esc(($filters['departement'] ?? '') !== '' ? $filters['departement'] : 'Tous') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-7">
        <div class="card h-100 shadow-sm">
            <div class="card-header">
                <h2 class="h6 mb-1">Comparaison mensuelle</h2>
                <div class="small text-muted">6 derniers mois, coûts agrégés uniquement</div>
            </div>
            <div class="card-body">
                <div style="height: 320px;">
                    <canvas id="financeTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card h-100 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h6 mb-0">Ventilation par département</h2>
                <span class="text-muted small"><?= count($departmentBreakdown ?? []) ?> ligne(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Département</th>
                            <th class="text-center">Abs.</th>
                            <th class="text-center">Retards</th>
                            <th class="text-end">Coût</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($departmentBreakdown)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Aucune donnée sur la période.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($departmentBreakdown as $row): ?>
                                <tr>
                                    <td><?= esc((string) ($row['departement'] ?? 'Non renseigné')) ?></td>
                                    <td class="text-center"><?= (int) ($row['absences'] ?? 0) ?></td>
                                    <td class="text-center"><?= (int) ($row['retard_minutes'] ?? 0) ?></td>
                                    <td class="text-end"><?= esc(format_xaf((float) ($row['cout_total'] ?? 0))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4 shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="h6 mb-1">Classement des employés par taux de présence</h2>
            <div class="small text-muted">Du meilleur au plus fragile sur la période choisie</div>
        </div>
        <span class="text-muted small"><?= count($employeeRanking ?? []) ?> employé(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Employé</th>
                    <th>Département</th>
                    <th class="text-center">Présences</th>
                    <th class="text-center">Absences</th>
                    <th class="text-center">Retard (min)</th>
                    <th class="text-center">Taux présence</th>
                    <th class="text-end">Coût total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($employeeRanking)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucune donnée sur la période.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($employeeRanking as $row): ?>
                        <?php $rate = (float) ($row['taux_presence'] ?? 0); ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= esc(trim(((string) ($row['prenom'] ?? '')) . ' ' . ((string) ($row['nom'] ?? '')))) ?></div>
                                <div class="small text-muted"><?= esc((string) ($row['matricule'] ?? '')) ?></div>
                            </td>
                            <td><?= esc((string) ($row['departement'] ?? 'Non renseigné')) ?></td>
                            <td class="text-center"><?= (int) ($row['jours_presence'] ?? 0) ?></td>
                            <td class="text-center"><?= (int) ($row['jours_absence'] ?? 0) ?></td>
                            <td class="text-center"><?= (int) ($row['retard_minutes'] ?? 0) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $rate >= 95 ? 'bg-success' : ($rate >= 85 ? 'bg-warning text-dark' : 'bg-danger') ?>">
                                    <?= number_format($rate, 1, ',', ' ') ?> %
                                </span>
                            </td>
                            <td class="text-end"><?= esc(format_xaf((float) ($row['cout_total'] ?? 0))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const canvas = document.getElementById('financeTrendChart');
    if (!canvas) {
        return;
    }

    const comparison = <?= json_encode($monthlyComparison ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    const labels = comparison.map(item => item.label);
    const absenceCosts = comparison.map(item => Number(item.cout_absences || 0));
    const retardCosts = comparison.map(item => Number(item.cout_retards || 0));

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Absences',
                    data: absenceCosts,
                    backgroundColor: 'rgba(196, 18, 48, 0.75)',
                    borderColor: 'rgba(196, 18, 48, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Retards',
                    data: retardCosts,
                    backgroundColor: 'rgba(217, 119, 6, 0.75)',
                    borderColor: 'rgba(217, 119, 6, 1)',
                    borderWidth: 1,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback(value) {
                            return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(value) + ' XAF';
                        }
                    }
                }
            }
        }
    });

    const startInput = document.getElementById('date_debut');
    const endInput = document.getElementById('date_fin');
    const feedback = document.getElementById('financeDateFeedback');
    const financeForm = document.querySelector('form[method="get"]');

    const validateRange = () => {
        if (!startInput || !endInput || !feedback) {
            return true;
        }

        const start = startInput.value;
        const end = endInput.value;
        const isValid = !(start && end && end < start);

        startInput.classList.toggle('is-invalid', !isValid);
        endInput.classList.toggle('is-invalid', !isValid);
        feedback.classList.toggle('d-none', isValid);

        return isValid;
    };

    const toIso = (date) => {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    };

    document.querySelectorAll('[data-finance-preset]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!startInput || !endInput) {
                return;
            }

            const now = new Date();
            const preset = button.getAttribute('data-finance-preset');

            if (preset === 'thisMonth') {
                const start = new Date(now.getFullYear(), now.getMonth(), 1);
                startInput.value = toIso(start);
                endInput.value = toIso(now);
            }

            if (preset === 'last30') {
                const start = new Date(now);
                start.setDate(start.getDate() - 29);
                startInput.value = toIso(start);
                endInput.value = toIso(now);
            }

            if (preset === 'quarter') {
                const quarterStartMonth = Math.floor(now.getMonth() / 3) * 3;
                const start = new Date(now.getFullYear(), quarterStartMonth, 1);
                startInput.value = toIso(start);
                endInput.value = toIso(now);
            }

            validateRange();
        });
    });

    if (startInput && endInput) {
        startInput.addEventListener('change', validateRange);
        endInput.addEventListener('change', validateRange);
    }

    if (financeForm) {
        financeForm.addEventListener('submit', (event) => {
            if (!validateRange()) {
                event.preventDefault();
            }
        });
    }

    validateRange();
})();
</script>
