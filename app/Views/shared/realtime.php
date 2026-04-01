<section class="page-hero page-hero-realtime mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-4">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-activity"></i>
                Supervision en direct
            </span>
            <h1 class="page-hero-title mb-2">Vue temps reel</h1>
            <p class="page-hero-copy mb-0">Suivez les presences du jour, les visiteurs en cours et les absences signalees sans changer d ecran.</p>
        </div>
        <div class="d-flex flex-column align-items-stretch align-items-lg-end gap-3">
            <div class="realtime-status-panel">
                <div class="d-flex align-items-center gap-2 text-success fw-semibold">
                    <i class="bi bi-circle-fill small"></i>
                    Flux actif
                </div>
                <div class="small text-muted mt-1">Derniere synchronisation: <span id="last-refresh-time" class="font-mono">--:--:--</span></div>
            </div>
            <button class="btn btn-primary" id="realtime-refresh-btn" type="button">
                <i class="bi bi-arrow-clockwise me-2"></i>
                Actualiser maintenant
            </button>
        </div>
    </div>
</section>

<section class="row g-3 g-xl-4 mb-4">
    <div class="col-12 col-md-4">
        <article class="realtime-summary-card is-success">
            <div class="realtime-summary-icon"><i class="bi bi-person-check-fill"></i></div>
            <div>
                <div class="realtime-summary-value" id="present-count">0</div>
                <div class="realtime-summary-label">Employes presents</div>
            </div>
        </article>
    </div>
    <div class="col-12 col-md-4">
        <article class="realtime-summary-card is-info">
            <div class="realtime-summary-icon"><i class="bi bi-person-badge-fill"></i></div>
            <div>
                <div class="realtime-summary-value" id="visitors-count">0</div>
                <div class="realtime-summary-label">Visiteurs en cours</div>
            </div>
        </article>
    </div>
    <div class="col-12 col-md-4">
        <article class="realtime-summary-card is-danger">
            <div class="realtime-summary-icon"><i class="bi bi-person-dash-fill"></i></div>
            <div>
                <div class="realtime-summary-value" id="absent-count">0</div>
                <div class="realtime-summary-label">Absences du jour</div>
            </div>
        </article>
    </div>
</section>

<section class="row g-4 align-items-stretch">
    <div class="col-12 col-xl-7">
        <div class="card realtime-panel h-100">
            <div class="card-header realtime-panel-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="h5 mb-1">Employes presents</h2>
                    <p class="text-muted small mb-0">Personnel ayant pointe aujourd hui.</p>
                </div>
                <span class="badge text-bg-success-subtle text-success-emphasis rounded-pill px-3 py-2" id="present-badge">0 actifs</span>
            </div>
            <div class="card-body">
                <div id="presences-list" class="row g-3">
                    <div class="col-12">
                        <div class="realtime-placeholder">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Chargement...</span>
                            </div>
                            <p class="text-muted mb-0">Chargement des presences en cours...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card realtime-panel h-100">
            <div class="card-header realtime-panel-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="h5 mb-1">Visiteurs presents</h2>
                    <p class="text-muted small mb-0">Entrees en cours sur le site.</p>
                </div>
                <span class="badge text-bg-info-subtle text-info-emphasis rounded-pill px-3 py-2" id="visitor-badge">0 visites</span>
            </div>
            <div class="card-body">
                <div id="visitors-list" class="realtime-stack"></div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card realtime-panel">
            <div class="card-header realtime-panel-header d-flex justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="h5 mb-1">Absents aujourd hui</h2>
                    <p class="text-muted small mb-0">Collaborateurs non pointes ou signales absents.</p>
                </div>
                <span class="badge text-bg-danger-subtle text-danger-emphasis rounded-pill px-3 py-2" id="absent-badge">0 absences</span>
            </div>
            <div class="card-body">
                <div id="absents-list" class="row g-3"></div>
            </div>
        </div>
    </div>
</section>

<script>
    (() => {
        'use strict';

        let refreshInterval;

        const selectors = {
            presencesList: document.getElementById('presences-list'),
            visitorsList: document.getElementById('visitors-list'),
            absentsList: document.getElementById('absents-list'),
            presentCount: document.getElementById('present-count'),
            visitorsCount: document.getElementById('visitors-count'),
            absentCount: document.getElementById('absent-count'),
            presentBadge: document.getElementById('present-badge'),
            visitorBadge: document.getElementById('visitor-badge'),
            absentBadge: document.getElementById('absent-badge'),
            lastRefreshTime: document.getElementById('last-refresh-time'),
            refreshButton: document.getElementById('realtime-refresh-btn'),
        };

        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const setLastRefresh = () => {
            if (!selectors.lastRefreshTime) {
                return;
            }

            selectors.lastRefreshTime.textContent = new Date().toLocaleTimeString('fr-FR');
        };

        const setButtonLoading = (loading) => {
            if (!selectors.refreshButton) {
                return;
            }

            selectors.refreshButton.disabled = loading;
            selectors.refreshButton.innerHTML = loading ?
                '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Actualisation...' :
                '<i class="bi bi-arrow-clockwise me-2"></i>Actualiser maintenant';
        };

        const formatIdentityName = (person, fallback) => {
            const fullName = [person.prenom, person.nom]
                .filter((value) => typeof value === 'string' && value.trim() !== '')
                .join(' ')
                .trim();

            return fullName || fallback;
        };

        const renderPresenceCard = (presence) => `
        <div class="col-12 col-md-6">
            <article class="realtime-person-card is-success h-100">
                <div class="realtime-person-icon text-success">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="realtime-person-name text-truncate">${escapeHtml(formatIdentityName(presence, 'Employe introuvable'))}</div>
                    <div class="realtime-person-meta text-truncate">${escapeHtml(presence.poste || 'Poste non renseigne')}</div>
                </div>
                <div class="realtime-person-side text-end">
                    <div class="realtime-person-status text-success">Present</div>
                    <div class="font-mono small text-muted">${escapeHtml(presence.heure_pointage || '--:--')}</div>
                </div>
            </article>
        </div>
    `;

        const renderVisitorCard = (visiteur) => `
        <article class="realtime-visitor-card">
            <div class="realtime-person-icon text-info">
                <i class="bi bi-person-badge-fill"></i>
            </div>
            <div class="flex-grow-1 min-w-0">
                <div class="realtime-person-name text-truncate">${escapeHtml(formatIdentityName(visiteur, 'Visiteur inconnu'))}</div>
                <div class="realtime-person-meta text-truncate">${escapeHtml(visiteur.motif || 'Motif non renseigne')}</div>
            </div>
            <div class="realtime-person-side text-end">
                <div class="badge-time text-info">${escapeHtml(visiteur.heure_creation || '--:--')}</div>
            </div>
        </article>
    `;

        const renderAbsentCard = (absent) => `
        <div class="col-12 col-md-6 col-xl-4">
            <article class="realtime-person-card is-danger h-100">
                <div class="realtime-person-icon text-danger">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="realtime-person-name text-truncate">${escapeHtml(formatIdentityName(absent, 'Employe introuvable'))}</div>
                    <div class="realtime-person-meta text-truncate">${escapeHtml(absent.poste || 'Poste non renseigne')}</div>
                </div>
                <div class="realtime-person-side text-end">
                    <div class="realtime-person-status text-danger">Absent</div>
                </div>
            </article>
        </div>
    `;

        const renderEmptyState = (icon, message, tone = 'muted') => `
        <div class="realtime-placeholder">
            <i class="bi ${icon} fs-1 text-${tone}"></i>
            <p class="mb-0 ${tone === 'muted' ? 'text-muted' : `text-${tone}`} ">${message}</p>
        </div>
    `;

        async function loadPresences() {
            const response = await fetch('<?= site_url('api/presences/today') ?>');
            const data = await response.json();
            const entries = Array.isArray(data.presents) ? data.presents : [];

            selectors.presentCount.textContent = String(entries.length);
            selectors.presentBadge.textContent = `${entries.length} actifs`;
            selectors.presencesList.innerHTML = entries.length > 0 ?
                entries.map(renderPresenceCard).join('') :
                `<div class="col-12">${renderEmptyState('bi-people', 'Aucun employe present pour le moment.')}</div>`;
        }

        async function loadVisitors() {
            const response = await fetch('<?= site_url('api/visiteurs/presents') ?>');
            const data = await response.json();
            const entries = Array.isArray(data.visiteurs) ? data.visiteurs : [];

            selectors.visitorsCount.textContent = String(entries.length);
            selectors.visitorBadge.textContent = `${entries.length} visites`;
            selectors.visitorsList.innerHTML = entries.length > 0 ?
                entries.map(renderVisitorCard).join('') :
                renderEmptyState('bi-person-badge', 'Aucun visiteur en cours.');
        }

        async function loadAbsents() {
            const response = await fetch('<?= site_url('api/presences/absents/today') ?>');
            const data = await response.json();
            const entries = Array.isArray(data.absents) ? data.absents : [];

            selectors.absentCount.textContent = String(entries.length);
            selectors.absentBadge.textContent = `${entries.length} absences`;
            selectors.absentsList.innerHTML = entries.length > 0 ?
                entries.map(renderAbsentCard).join('') :
                `<div class="col-12">${renderEmptyState('bi-check-circle', 'Tous les employes sont presents.', 'success')}</div>`;
        }

        async function refreshData() {
            setButtonLoading(true);

            try {
                await Promise.all([loadPresences(), loadVisitors(), loadAbsents()]);
                setLastRefresh();
            } catch (error) {
                console.error('Erreur chargement vue temps reel:', error);
                if (typeof window.showToast === 'function') {
                    window.showToast('Erreur de chargement de la vue temps reel.', 'danger');
                }
            } finally {
                setButtonLoading(false);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            refreshData();
            refreshInterval = window.setInterval(refreshData, 30000);

            selectors.refreshButton?.addEventListener('click', refreshData);
        });

        window.addEventListener('beforeunload', () => {
            if (refreshInterval) {
                window.clearInterval(refreshInterval);
            }
        });
    })();
</script>