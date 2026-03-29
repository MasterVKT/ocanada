<?php
declare(strict_types=1);

/** @var array $currentUser */

$currentPath = trim(service('uri')->getPath(), '/');
$items = [
    ['label' => 'Mon tableau de bord', 'icon' => 'bi-speedometer2', 'href' => site_url('employe/dashboard'), 'match' => 'employe/dashboard'],
    ['label' => 'Mes conges', 'icon' => 'bi-calendar-heart', 'href' => site_url('employe/leaves'), 'match' => 'employe/leaves'],
    ['label' => 'Mon planning', 'icon' => 'bi-calendar3-week', 'href' => site_url('employe/planning'), 'match' => 'employe/planning'],
    ['label' => 'Mes documents', 'icon' => 'bi-folder2-open', 'href' => site_url('employe/documents'), 'match' => 'employe/documents'],
    ['label' => 'Notifications', 'icon' => 'bi-bell-fill', 'href' => site_url('notifications'), 'match' => 'notifications'],
    ['label' => 'Mon profil', 'icon' => 'bi-person-circle', 'href' => site_url('profile'), 'match' => 'profile'],
];

$renderNav = static function (array $items, string $currentPath): string {
    $html = '<nav class="sidebar-nav px-3 py-3">';
    $html .= '<div class="sidebar-section-label">Mon espace</div>';

    foreach ($items as $item) {
        $isActive = isset($item['match']) && ($currentPath === $item['match'] || str_starts_with($currentPath, $item['match'] . '/'));
        $classes = 'sidebar-link' . ($isActive ? ' is-active' : '');
        $html .= '<a href="' . esc((string) $item['href']) . '" class="' . $classes . '"><span><i class="bi ' . esc($item['icon']) . ' me-2"></i>' . esc($item['label']) . '</span></a>';
    }

    $html .= '</nav>';
    return $html;
};
?>
<aside class="sidebar d-none d-lg-flex flex-column">
    <div class="sidebar-brand px-4 py-4">
        <div class="sidebar-brand-mark"><i class="bi bi-person-badge"></i></div>
        <div>
            <div class="sidebar-brand-title">Ô Canada RH</div>
            <div class="sidebar-brand-subtitle">Espace employe</div>
        </div>
    </div>
    <?= $renderNav($items, $currentPath) ?>
</aside>

<div class="offcanvas offcanvas-start sidebar-offcanvas" tabindex="-1" id="app-sidebar-mobile" aria-labelledby="app-sidebar-mobile-label">
    <div class="offcanvas-header sidebar-brand px-4 py-4 border-bottom border-white border-opacity-10">
        <div>
            <div class="sidebar-brand-title" id="app-sidebar-mobile-label">Ô Canada RH</div>
            <div class="sidebar-brand-subtitle">Espace employe</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Fermer"></button>
    </div>
    <div class="offcanvas-body p-0">
        <?= $renderNav($items, $currentPath) ?>
    </div>
</div>

