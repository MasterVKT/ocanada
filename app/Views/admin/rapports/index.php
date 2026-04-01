<?php
$selectedType = old('type', 'presences_mensuel');
$selectedFormat = old('format', 'pdf');
$selectedStart = old('start', date('Y-m-01'));
$selectedEnd = old('end', date('Y-m-d'));
$selectedDepartment = old('departement', '');
$hasDepartments = !empty($departements ?? []);

$reportCatalog = [
    'presences_mensuel' => [
        'title' => 'Presences (periode)',
        'icon' => 'bi-clock-history',
        'description' => 'Synthese de la ponctualite, retards, absences et taux de presence sur la periode.',
        'departmentAware' => true,
        'tone' => 'primary',
    ],
    'conges_annuels' => [
        'title' => 'Conges (periode)',
        'icon' => 'bi-calendar-check',
        'description' => 'Suivi des demandes de conge, soldes restants et consommation par type.',
        'departmentAware' => true,
        'tone' => 'success',
    ],
    'visiteurs' => [
        'title' => 'Journal visiteurs',
        'icon' => 'bi-person-badge',
        'description' => 'Liste detaillee des passages visiteurs avec statut et durees de presence.',
        'departmentAware' => false,
        'tone' => 'warning',
    ],
    'absenteisme' => [
        'title' => 'Absenteisme',
        'icon' => 'bi-graph-down-arrow',
        'description' => 'Analyse des absences, taux d absenteisme et impacts financiers estimes.',
        'departmentAware' => true,
        'tone' => 'danger',
    ],
];

$activeReport = $reportCatalog[$selectedType] ?? $reportCatalog['presences_mensuel'];
?>

<div class="reports-page">
    <section class="page-hero mb-4 mb-lg-5">
        <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
            <div>
                <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    Exports administratifs
                </span>
                <h1 class="page-hero-title mb-2">Rapports et exports</h1>
                <p class="page-hero-copy mb-0">Generez des rapports PDF ou CSV avec filtres avances, cadrage par periode et export immediate.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge rounded-pill text-bg-light border px-3 py-2"><i class="bi bi-shield-lock me-1"></i>Acces admin</span>
                <span class="badge rounded-pill text-bg-light border px-3 py-2"><i class="bi bi-clock me-1"></i>Periode personnalisable</span>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-4 reports-kpis-row">
        <div class="col-12 col-md-6 col-xl-4">
            <?= view('components/kpi_card', ['icon' => 'bi-file-earmark-text', 'value' => '4', 'label' => 'Modeles disponibles', 'color' => 'primary']) ?>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <?= view('components/kpi_card', ['icon' => 'bi-filetype-pdf', 'value' => 'PDF / CSV', 'label' => 'Formats export', 'color' => 'warning']) ?>
        </div>
        <div class="col-12 col-md-6 col-xl-4">
            <?= view('components/kpi_card', ['icon' => 'bi-sliders', 'value' => '3', 'label' => 'Filtres principaux', 'color' => 'success']) ?>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <?= esc((string) session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <?= esc((string) session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4 reports-shell align-items-start">
        <div class="col-12 col-xxl-8">
            <div class="card shadow-sm border-0 reports-builder-card">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h2 class="h5 mb-1">Generateur de rapport</h2>
                    <p class="text-muted small mb-0">Selectionnez un type, definissez votre plage de dates puis choisissez le format d export.</p>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= base_url('admin/rapports/generer') ?>" class="row g-3" id="reportsForm">
                        <?= csrf_field() ?>

                        <div class="col-12 col-lg-6 col-xxl-4">
                            <label for="type" class="form-label">Type de rapport</label>
                            <select name="type" id="type" class="form-select" required>
                                <?php foreach ($reportCatalog as $reportKey => $reportConfig): ?>
                                    <option value="<?= esc($reportKey) ?>" <?= $selectedType === $reportKey ? 'selected' : '' ?>><?= esc($reportConfig['title']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small id="reportTypeHint" class="text-muted d-block mt-2"><?= esc((string) ($activeReport['description'] ?? '')) ?></small>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 col-xxl-2">
                            <label for="start" class="form-label">Date de début</label>
                            <input type="date" name="start" id="start" class="form-control" value="<?= esc((string) $selectedStart) ?>">
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 col-xxl-2">
                            <label for="end" class="form-label">Date de fin</label>
                            <input type="date" name="end" id="end" class="form-control" value="<?= esc((string) $selectedEnd) ?>">
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 col-xxl-2">
                            <label class="form-label">Format</label>
                            <select name="format" id="format" class="form-select">
                                <option value="pdf" <?= $selectedFormat === 'pdf' ? 'selected' : '' ?>>PDF</option>
                                <option value="csv" <?= $selectedFormat === 'csv' ? 'selected' : '' ?>>CSV</option>
                            </select>
                        </div>

                        <div class="col-12 col-lg-6 col-xxl-4">
                            <label for="departement" class="form-label">Département (optionnel)</label>
                            <select name="departement" id="departement" class="form-select">
                                <option value="">Tous les départements</option>
                                <?php foreach (($departements ?? []) as $departement): ?>
                                    <option value="<?= esc($departement) ?>" <?= $selectedDepartment === $departement ? 'selected' : '' ?>><?= esc($departement) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small id="departmentHint" class="text-muted">Utilise pour les rapports Presences, Conges et Absenteisme.</small>
                            <?php if (!$hasDepartments): ?>
                                <div class="small text-warning mt-1"><i class="bi bi-exclamation-triangle me-1"></i>Aucun département disponible pour filtrage.</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <div id="dateRangeFeedback" class="invalid-feedback d-none">La date de fin doit être postérieure ou égale à la date de début.</div>
                        </div>

                        <div class="col-12 col-xl-8 d-flex flex-wrap gap-2 align-items-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="thisMonth">Mois courant</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="last30">30 derniers jours</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="quarter">Trimestre en cours</button>
                        </div>

                        <div class="col-12 col-xl-4 text-xl-end">
                            <button type="submit" class="btn btn-primary reports-generate-btn">
                                <i class="bi bi-download me-1"></i>Generer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-12 col-xxl-4">
                <div class="card shadow-sm border-0 reports-helper-card h-100">
                    <div class="card-body p-4 d-flex flex-column gap-3">
                        <div>
                            <span class="small text-uppercase fw-semibold text-muted">Type actif</span>
                            <h3 class="h5 mt-2 mb-2 d-flex align-items-center gap-2" id="reportActiveTitle">
                                <i class="bi <?= esc((string) ($activeReport['icon'] ?? 'bi-file-earmark-text')) ?> text-<?= esc((string) ($activeReport['tone'] ?? 'primary')) ?>"></i>
                                <?= esc((string) ($activeReport['title'] ?? 'Rapport')) ?>
                            </h3>
                            <p class="text-muted mb-0" id="reportActiveDescription"><?= esc((string) ($activeReport['description'] ?? '')) ?></p>
                        </div>

                        <div class="reports-helper-list">
                            <div class="reports-helper-item">
                                <i class="bi bi-1-circle"></i>
                                <span>Definissez une plage de dates courte pour des exports plus lisibles.</span>
                            </div>
                            <div class="reports-helper-item">
                                <i class="bi bi-2-circle"></i>
                                <span>CSV pour retraitement analytique, PDF pour partage ou archivage.</span>
                            </div>
                            <div class="reports-helper-item">
                                <i class="bi bi-3-circle"></i>
                                <span>Filtrez par departement pour isoler rapidement une equipe.</span>
                            </div>
                        </div>

                        <div class="alert alert-light border mb-0 small">
                            <i class="bi bi-info-circle me-1"></i>
                            Le rapport genere est telecharge immediatement apres validation des filtres.
                        </div>

                        <a href="<?= base_url('admin/finance') ?>" class="btn btn-outline-secondary btn-sm align-self-start">
                            <i class="bi bi-graph-up-arrow me-1"></i>Ouvrir le tableau financier
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-body p-4">
                <h2 class="h6 mb-3">Couverture des rapports</h2>
                <div class="row g-3">
                    <?php foreach ($reportCatalog as $reportKey => $reportConfig): ?>
                        <div class="col-12 col-lg-6 col-xxl-3">
                            <article class="report-type-card tone-<?= esc((string) ($reportConfig['tone'] ?? 'primary')) ?> <?= $selectedType === $reportKey ? 'is-selected' : '' ?>" data-report-card="<?= esc($reportKey) ?>">
                                <div class="report-type-icon">
                                    <i class="bi <?= esc((string) ($reportConfig['icon'] ?? 'bi-file-earmark-text')) ?>"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold mb-1"><?= esc((string) ($reportConfig['title'] ?? 'Rapport')) ?></div>
                                    <p class="small text-muted mb-0"><?= esc((string) ($reportConfig['description'] ?? '')) ?></p>
                                </div>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <script>
            (() => {
                const catalog = <?= json_encode($reportCatalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
                const typeSelect = document.getElementById('type');
                const typeHint = document.getElementById('reportTypeHint');
                const title = document.getElementById('reportActiveTitle');
                const description = document.getElementById('reportActiveDescription');
                const departmentSelect = document.getElementById('departement');
                const departmentHint = document.getElementById('departmentHint');
                const reportCards = document.querySelectorAll('[data-report-card]');
                const form = document.getElementById('reportsForm');
                const startInput = document.getElementById('start');
                const endInput = document.getElementById('end');
                const dateRangeFeedback = document.getElementById('dateRangeFeedback');
                const submitButton = document.querySelector('.reports-generate-btn');

                if (!typeSelect) {
                    return;
                }

                const updateTypePresentation = () => {
                    const current = catalog[typeSelect.value] || catalog.presences_mensuel;
                    if (!current) {
                        return;
                    }

                    if (typeHint) {
                        typeHint.textContent = current.description || '';
                    }

                    if (description) {
                        description.textContent = current.description || '';
                    }

                    if (title) {
                        title.innerHTML = '<i class="bi ' + (current.icon || 'bi-file-earmark-text') + ' text-' + (current.tone || 'primary') + '"></i>' +
                            '<span>' + (current.title || 'Rapport') + '</span>';
                    }

                    if (departmentSelect) {
                        departmentSelect.disabled = !current.departmentAware;
                        if (!current.departmentAware) {
                            departmentSelect.value = '';
                        }
                    }

                    if (departmentHint) {
                        departmentHint.textContent = current.departmentAware ?
                            'Utilise pour les rapports Presences, Conges et Absenteisme.' :
                            'Le filtre departement n est pas applique a ce type de rapport.';
                    }

                    reportCards.forEach((card) => {
                        card.classList.toggle('is-selected', card.getAttribute('data-report-card') === typeSelect.value);
                    });
                };

                const validateDateRange = () => {
                    if (!startInput || !endInput || !dateRangeFeedback) {
                        return true;
                    }

                    const start = startInput.value;
                    const end = endInput.value;
                    const isValid = !(start && end && end < start);

                    startInput.classList.toggle('is-invalid', !isValid);
                    endInput.classList.toggle('is-invalid', !isValid);
                    dateRangeFeedback.classList.toggle('d-none', isValid);

                    return isValid;
                };

                document.querySelectorAll('[data-preset]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (!startInput || !endInput) {
                            return;
                        }

                        const now = new Date();
                        const toIso = (date) => {
                            const year = date.getFullYear();
                            const month = String(date.getMonth() + 1).padStart(2, '0');
                            const day = String(date.getDate()).padStart(2, '0');
                            return year + '-' + month + '-' + day;
                        };

                        const preset = button.getAttribute('data-preset');
                        if (preset === 'thisMonth') {
                            const start = new Date(now.getFullYear(), now.getMonth(), 1);
                            startInput.value = toIso(start);
                            endInput.value = toIso(now);
                            validateDateRange();
                            return;
                        }

                        if (preset === 'last30') {
                            const start = new Date(now);
                            start.setDate(start.getDate() - 29);
                            startInput.value = toIso(start);
                            endInput.value = toIso(now);
                            validateDateRange();
                            return;
                        }

                        if (preset === 'quarter') {
                            const quarterStartMonth = Math.floor(now.getMonth() / 3) * 3;
                            const start = new Date(now.getFullYear(), quarterStartMonth, 1);
                            startInput.value = toIso(start);
                            endInput.value = toIso(now);
                            validateDateRange();
                        }
                    });
                });

                reportCards.forEach((card) => {
                    card.addEventListener('click', () => {
                        const reportType = card.getAttribute('data-report-card');
                        if (!reportType) {
                            return;
                        }
                        typeSelect.value = reportType;
                        updateTypePresentation();
                    });
                });

                if (startInput && endInput) {
                    startInput.addEventListener('change', validateDateRange);
                    endInput.addEventListener('change', validateDateRange);
                }

                if (form) {
                    form.addEventListener('submit', (event) => {
                        if (!validateDateRange()) {
                            event.preventDefault();
                            return;
                        }

                        if (submitButton) {
                            submitButton.disabled = true;
                            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Generation...';
                        }
                    });
                }

                typeSelect.addEventListener('change', updateTypePresentation);
                updateTypePresentation();
                validateDateRange();
            })();
        </script>
    </div>