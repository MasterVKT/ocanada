<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #333; padding: 4px; }
    th { background-color: #eee; }
    h2 { text-align: center; margin-bottom: 10px; }
    h3 { margin: 14px 0 8px; }
    .meta { margin-bottom: 8px; font-size: 11px; color: #444; }
    .kpi-grid td { width: 25%; text-align: center; }
</style>

<h2>Rapport des présences<br><?= date('d/m/Y', strtotime($start)) ?> → <?= date('d/m/Y', strtotime($end)) ?></h2>

<div class="meta">
    <strong>Département :</strong>
    <?= esc(($departement ?? '') !== '' ? $departement : 'Tous') ?>
</div>

<?php $summary = $summary ?? []; ?>
<?php $comparison = $comparison ?? []; ?>

<table class="kpi-grid">
    <tbody>
        <tr>
            <td>
                <strong>Taux présence global</strong><br>
                <?= esc(number_format((float) ($summary['taux_presence_global'] ?? 0), 2, ',', ' ')) ?> %
            </td>
            <td>
                <strong>Pointages</strong><br>
                <?= esc((string) ($summary['total_pointages'] ?? 0)) ?>
            </td>
            <td>
                <strong>Retards</strong><br>
                <?= esc((string) ($summary['total_retards'] ?? 0)) ?>
            </td>
            <td>
                <strong>Heures travaillées</strong><br>
                <?= esc(number_format((float) ($summary['total_heures_travaillees'] ?? 0), 2, ',', ' ')) ?> h
            </td>
        </tr>
    </tbody>
</table>

<table>
    <thead>
        <tr>
            <th>Période précédente</th>
            <th>Taux précédent (%)</th>
            <th>Delta taux (pts)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= esc(($comparison['previous_start'] ?? '') . ' → ' . ($comparison['previous_end'] ?? '')) ?></td>
            <td><?= esc(number_format((float) ($comparison['previous_taux_presence_global'] ?? 0), 2, ',', ' ')) ?></td>
            <td><?= esc(number_format((float) ($comparison['delta_taux_presence_global'] ?? 0), 2, ',', ' ')) ?></td>
        </tr>
    </tbody>
</table>

<h3>Récapitulatif par employé</h3>
<table>
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Département</th>
            <th>Jours total</th>
            <th>Présents</th>
            <th>Retards</th>
            <th>Absences</th>
            <th>Retard (min)</th>
            <th>Heures</th>
            <th>Taux présence (%)</th>
        </tr>
    </thead>
    <tbody>
        <?php if (! empty($by_employee ?? [])): ?>
            <?php foreach ($by_employee as $row): ?>
                <tr>
                    <td><?= esc($row['matricule']) ?></td>
                    <td><?= esc($row['prenom']) ?></td>
                    <td><?= esc($row['nom']) ?></td>
                    <td><?= esc($row['departement']) ?></td>
                    <td><?= esc((string) $row['jours_total']) ?></td>
                    <td><?= esc((string) $row['jours_present']) ?></td>
                    <td><?= esc((string) $row['jours_retard']) ?></td>
                    <td><?= esc((string) $row['jours_absence']) ?></td>
                    <td><?= esc((string) $row['retard_minutes']) ?></td>
                    <td><?= esc(number_format((float) $row['heures_travaillees'], 2, ',', ' ')) ?></td>
                    <td><?= esc(number_format((float) $row['taux_presence'], 1, ',', ' ')) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="11">Aucune donnée sur la période.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<h3>Détails des pointages</h3>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Matricule</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Département</th>
            <th>Statut</th>
            <th>Arrivée</th>
            <th>Départ</th>
            <th>Retard (min)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= esc($r['date_pointage']) ?></td>
                <td><?= esc($r['matricule']) ?></td>
                <td><?= esc($r['prenom']) ?></td>
                <td><?= esc($r['nom']) ?></td>
                <td><?= esc($r['departement'] ?? 'Non renseigné') ?></td>
                <td><?= esc($r['statut']) ?></td>
                <td><?= esc($r['heure_pointage'] ?? '') ?></td>
                <td><?= esc($r['heure_sortie'] ?? '') ?></td>
                <td><?= esc($r['retard_minutes'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
