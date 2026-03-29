<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #333; padding: 4px; }
    th { background-color: #eee; }
    h2 { text-align: center; margin-bottom: 10px; }
    h3 { margin: 14px 0 8px; }
    .meta { margin-bottom: 8px; font-size: 11px; color: #444; }
    .kpi-grid { margin: 8px 0 10px; }
    .kpi-grid td { width: 25%; text-align: center; }
</style>

<h2>Rapport d'absentéisme<br><?= date('d/m/Y', strtotime($start)) ?> → <?= date('d/m/Y', strtotime($end)) ?></h2>

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
                <strong>Taux global</strong><br>
                <?= esc(number_format((float) ($summary['taux_absenteisme_global'] ?? 0), 2, ',', ' ')) ?> %
            </td>
            <td>
                <strong>Absences</strong><br>
                <?= esc((string) ($summary['total_absences'] ?? 0)) ?>
            </td>
            <td>
                <strong>Retards</strong><br>
                <?= esc((string) ($summary['total_retard_minutes'] ?? 0)) ?> min
            </td>
            <td>
                <strong>Coût total estimé</strong><br>
                <?= esc(number_format((float) ($summary['cout_total'] ?? 0), 0, ',', ' ')) ?> XAF
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
            <th>Coût précédent (XAF)</th>
            <th>Delta coût (XAF)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?= esc(($comparison['previous_start'] ?? '') . ' → ' . ($comparison['previous_end'] ?? '')) ?></td>
            <td><?= esc(number_format((float) ($comparison['previous_taux_absenteisme_global'] ?? 0), 2, ',', ' ')) ?></td>
            <td><?= esc(number_format((float) ($comparison['delta_taux_absenteisme_global'] ?? 0), 2, ',', ' ')) ?></td>
            <td><?= esc(number_format((float) ($comparison['previous_cout_total'] ?? 0), 0, ',', ' ')) ?></td>
            <td><?= esc(number_format((float) ($comparison['delta_cout_total'] ?? 0), 0, ',', ' ')) ?></td>
        </tr>
    </tbody>
</table>

<h3>Classement par taux d'absentéisme</h3>
<table>
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Département</th>
            <th>Jours total</th>
            <th>Absences</th>
            <th>Retards (min)</th>
            <th>Taux abs. (%)</th>
            <th>Coût total (XAF)</th>
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
                    <td><?= esc((string) $row['jours_absence']) ?></td>
                    <td><?= esc((string) $row['retard_minutes']) ?></td>
                    <td><?= esc(number_format((float) $row['taux_absenteisme'], 1, ',', ' ')) ?></td>
                    <td><?= esc(number_format((float) $row['cout_total'], 0, ',', ' ')) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">Aucune donnée sur la période.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<h3>Détails des absences</h3>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Matricule</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Département</th>
        </tr>
    </thead>
    <tbody>
        <?php if (! empty($rows ?? [])): ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= esc($r['date_pointage']) ?></td>
                    <td><?= esc($r['matricule']) ?></td>
                    <td><?= esc($r['prenom']) ?></td>
                    <td><?= esc($r['nom']) ?></td>
                    <td><?= esc($r['departement'] ?? 'Non renseigné') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">Aucune absence détectée sur la période.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
