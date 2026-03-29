<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #333; padding: 4px; }
    th { background-color: #eee; }
    h2 { text-align: center; margin-bottom: 10px; }
    h3 { margin: 14px 0 8px; }
    .kpi-grid td { width: 25%; text-align: center; }
</style>

<h2>Journal des visiteurs<br><?= date('d/m/Y', strtotime($start)) ?> → <?= date('d/m/Y', strtotime($end)) ?></h2>

<?php $summary = $summary ?? []; ?>
<?php $comparison = $comparison ?? []; ?>

<table class="kpi-grid">
    <tbody>
        <tr>
            <td>
                <strong>Total visites</strong><br>
                <?= esc((string) ($summary['total_visites'] ?? 0)) ?>
            </td>
            <td>
                <strong>Présents</strong><br>
                <?= esc((string) ($summary['present'] ?? 0)) ?>
            </td>
            <td>
                <strong>Sortis</strong><br>
                <?= esc((string) ($summary['departi'] ?? 0)) ?>
            </td>
            <td>
                <strong>Durée moyenne</strong><br>
                <?= esc((string) ($summary['duree_moyenne_minutes'] ?? 0)) ?> min
            </td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>Période précédente</th>
            <th>Total précédent</th>
            <th>Delta visites</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= esc(($comparison['previous_start'] ?? '') . ' → ' . ($comparison['previous_end'] ?? '')) ?></td>
            <td><?= esc((string) ($comparison['previous_total_visites'] ?? 0)) ?></td>
            <td><?= esc((string) ($comparison['delta_total_visites'] ?? 0)) ?></td>
        </tr>
    </tbody>
</table>

<h3>Détail visiteurs</h3>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Date création</th>
            <th>Date modification</th>
            <th>Badge</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Email</th>
            <th>Téléphone</th>
            <th>Entreprise</th>
            <th>Motif</th>
            <th>Personne à voir</th>
            <th>Arrivée</th>
            <th>Départ</th>
            <th>Statut</th>
            <th>Commentaire</th>
            <th>Durée (min)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (! empty($rows ?? [])): ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= esc($r['id'] ?? '') ?></td>
                    <td><?= esc($r['date_creation'] ?? '') ?></td>
                    <td><?= esc($r['date_modification'] ?? '') ?></td>
                    <td><?= esc($r['badge_id'] ?? '') ?></td>
                    <td><?= esc($r['prenom'] ?? '') ?></td>
                    <td><?= esc($r['nom'] ?? '') ?></td>
                    <td><?= esc($r['email'] ?? '') ?></td>
                    <td><?= esc($r['telephone'] ?? '') ?></td>
                    <td><?= esc($r['entreprise'] ?? '') ?></td>
                    <td><?= esc($r['motif'] ?? '') ?></td>
                    <td><?= esc($r['personne_a_voir'] ?? '') ?></td>
                    <td><?= esc($r['heure_arrivee'] ?? '') ?></td>
                    <td><?= esc($r['heure_depart'] ?? '') ?></td>
                    <td><?= esc($r['statut'] ?? '') ?></td>
                    <td><?= esc($r['commentaire'] ?? '') ?></td>
                    <td><?= esc((string) ($r['duree_visite_minutes_calculee'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="16">Aucune visite sur la période.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
