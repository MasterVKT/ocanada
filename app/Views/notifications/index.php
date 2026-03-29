<section class="page-hero mb-4 mb-lg-5">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3">
        <div>
            <span class="page-hero-chip mb-3 d-inline-flex align-items-center gap-2">
                <i class="bi bi-bell-fill"></i>
                Centre de notifications
            </span>
            <h1 class="page-hero-title mb-2">Mes notifications</h1>
            <p class="page-hero-copy mb-0">Consultez les alertes systeme et mettez rapidement a jour leur statut de lecture.</p>
        </div>
        <form method="post" action="<?= site_url('notifications/tout-lire') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary" <?= empty($notifications) ? 'disabled' : '' ?>>
                <i class="bi bi-check2-all me-2"></i>
                Tout marquer comme lu
            </button>
        </form>
    </div>
</section>

<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <h2 class="h5 mb-0">Historique recent</h2>
        <span class="badge rounded-pill text-bg-light border text-secondary px-3 py-2"><?= esc((string) ($totalItems ?? count($notifications))) ?> elements</span>
    </div>

    <div class="card-body">
        <?php if (empty($notifications)): ?>
            <div class="realtime-placeholder">
                <i class="bi bi-inbox fs-1 text-muted"></i>
                <p class="mb-0 text-muted">Aucune notification pour le moment.</p>
            </div>
        <?php else: ?>
            <div class="notification-list d-flex flex-column gap-2">
                <?php foreach ($notifications as $n): ?>
                    <article class="notification-item <?= (int) ($n['lue'] ?? 0) === 0 ? 'is-unread' : '' ?>">
                        <div class="notification-icon">
                            <i class="bi <?= (int) ($n['lue'] ?? 0) === 0 ? 'bi-bell-fill' : 'bi-check-circle-fill' ?>"></i>
                        </div>
                        <div class="notification-main">
                            <?php if (!empty($n['lien'])): ?>
                                <a href="<?= esc($n['lien']) ?>" class="notification-title text-decoration-none stretched-link">
                                    <?= esc($n['message'] ?? '') ?>
                                </a>
                            <?php else: ?>
                                <div class="notification-title"><?= esc($n['message'] ?? '') ?></div>
                            <?php endif; ?>
                            <div class="notification-meta">
                                <span class="font-mono"><?= date('d/m/Y H:i', strtotime($n['date_creation'])) ?></span>
                                <?php if ((int) ($n['lue'] ?? 0) === 0): ?>
                                    <span class="badge rounded-pill text-bg-primary-subtle text-primary-emphasis">Non lue</span>
                                <?php else: ?>
                                    <span class="badge rounded-pill text-bg-success-subtle text-success-emphasis">Lue</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ((int) ($n['lue'] ?? 0) === 0): ?>
                            <form method="POST" action="<?= site_url('notifications/lire/' . (int) $n['id']) ?>" class="notification-action ms-2">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Marquer comme lue">
                                    <i class="bi bi-check2"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
        <div class="card-footer bg-light-subtle">
            <nav aria-label="Pagination notifications" class="d-flex justify-content-center">
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === (int) $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>
