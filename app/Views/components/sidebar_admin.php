<?php
declare(strict_types=1);

/** @var array $currentUser */

$currentPath = trim(service('uri')->getPath(), '/');
$items = [
    ['label' => 'Tableau de bord', 'icon' => 'bi-speedometer2', 'href' => site_url('admin/dashboard'), 'match' => 'admin/dashboard'],
    ['label' => 'Vue temps reel', 'icon' => 'bi-display', 'href' => site_url('shared/realtime'), 'match' => 'shared/realtime'],
    ['label' => 'Employes', 'icon' => 'bi-people-fill', 'href' => site_url('admin/employees'), 'match' => 'admin/employees'],
    ['label' => 'Presences', 'icon' => 'bi-calendar-check', 'href' => site_url('admin/presences/index'), 'match' => 'admin/presences'],
    ['label' => 'Conges', 'icon' => 'bi-calendar-heart', 'href' => site_url('admin/leaves'), 'match' => 'admin/leaves'],
    ['label' => 'Visiteurs', 'icon' => 'bi-person-badge', 'href' => site_url('admin/visitors'), 'match' => 'admin/visitors'],
    ['label' => 'Planning', 'icon' => 'bi-calendar3-week', 'href' => site_url('admin/planning'), 'match' => 'admin/planning'],
    ['label' => 'Documents RH', 'icon' => 'bi-folder2-open', 'href' => site_url('admin/documents'), 'match' => 'admin/documents'],
    ['label' => 'Rapports', 'icon' => 'bi-file-earmark-bar-graph', 'href' => site_url('admin/rapports'), 'match' => 'admin/rapports'],
    ['label' => 'Tableau financier', 'icon' => 'bi-graph-up-arrow', 'href' => site_url('admin/finance'), 'match' => 'admin/finance'],
    ['label' => 'Journal d audit', 'icon' => 'bi-shield-check', 'href' => site_url('admin/audit'), 'match' => 'admin/audit'],
    ['label' => 'Configuration', 'icon' => 'bi-gear-fill', 'href' => site_url('admin/configuration'), 'match' => 'admin/configuration'],
];

$renderNav = static function (array $items, string $currentPath): string {
    $html = '<nav class="sidebar-nav px-3 py-3">';
    $html .= '<div class="sidebar-section-label">Administration</div>';

    foreach ($items as $item) {
        $isDisabled = (bool) ($item['disabled'] ?? false);
        $isActive = !$isDisabled && isset($item['match']) && ($currentPath === $item['match'] || str_starts_with($currentPath, $item['match'] . '/'));
        $classes = 'sidebar-link' . ($isActive ? ' is-active' : '') . ($isDisabled ? ' is-disabled' : '');
        $label = esc($item['label']);
        $icon = esc($item['icon']);

        if ($isDisabled) {
            $html .= '<span class="' . $classes . '"><span><i class="bi ' . $icon . ' me-2"></i>' . $label . '</span><span class="sidebar-chip">Bientot</span></span>';
            continue;
        }

        $href = esc((string) $item['href']);
        $html .= '<a href="' . $href . '" class="' . $classes . '"><span><i class="bi ' . $icon . ' me-2"></i>' . $label . '</span></a>';
    }

    $html .= '</nav>';
    return $html;
};
?>
<aside class="sidebar d-none d-lg-flex flex-column">
    <div class="sidebar-brand px-4 py-4">
        <div class="sidebar-brand-mark"><i class="bi bi-shield-check"></i></div>
        <div>
            <div class="sidebar-brand-title">Ô Canada RH</div>
            <div class="sidebar-brand-subtitle">Espace administration</div>
        </div>
    </div>
    <?= $renderNav($items, $currentPath) ?>
</aside>

<div class="offcanvas offcanvas-start sidebar-offcanvas" tabindex="-1" id="app-sidebar-mobile" aria-labelledby="app-sidebar-mobile-label">
    <div class="offcanvas-header sidebar-brand px-4 py-4 border-bottom border-white border-opacity-10">
        <div>
            <div class="sidebar-brand-title" id="app-sidebar-mobile-label">Ô Canada RH</div>
            <div class="sidebar-brand-subtitle">Espace administration</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?= $renderNav($items, $currentPath) ?>
    </div>
</div>

