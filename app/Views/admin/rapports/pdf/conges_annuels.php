<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #333; padding: 4px; }
    th { background-color: #eee; }
    h2 { text-align: center; margin-bottom: 10px; }
    h3 { margin: 14px 0 8px; }
    .meta { margin-bottom: 8px; font-size: 11px; color: #444; }
    .kpi-grid td { width: 33.33%; text-align: center; }
</style>

<h2>Rapport des congés<br><?= date('d/m/Y', strtotime($start)) ?> → <?= date('d/m/Y', strtotime($end)) ?></h2>

<div class="meta">
    <strong>Année :</strong> <?= esc((string) ($selected_year ?? '')) ?>
    &nbsp; | &nbsp;
    <strong>Département :</strong> <?= esc(($departement ?? '') !== '' ? $departement : 'Tous') ?>
</div>

<?php $summary = $summary ?? []; ?>

<table class="kpi-grid">
    <tbody>
        <tr>
            <td>
                <strong>Demandes</strong><br>
                <?= esc((string) ($summary['total_demandes'] ?? 0)) ?>
            </td>
            <td>
                <strong>Jours demandés</strong><br>
                <?= esc(number_format((float) ($summary['total_jours_demandes'] ?? 0), 2, ',', ' ')) ?>
            </td>
            <td>
                <strong>Jours approuvés</strong><br>
                <?= esc(number_format((float) ($summary['total_jours_approuves'] ?? 0), 2, ',', ' ')) ?>
            </td>
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
            <th>Solde initial</th>
            <th>Jours pris total</th>
            <th>Solde restant</th>
            <th>Demandes</th>
            <th>Jours demandés (période)</th>
            <th>Jours approuvés (période)</th>
            <th>Détail type</th>
        </tr>
    </thead>
    <tbody>
        <?php if (! empty($by_employee ?? [])): ?>
            <?php foreach ($by_employee as $employee): ?>
                <tr>
                    <td><?= esc($employee['matricule']) ?></td>
                    <td><?= esc($employee['prenom']) ?></td>
                    <td><?= esc($employee['nom']) ?></td>
                    <td><?= esc(number_format((float) $employee['solde_initial'], 2, ',', ' ')) ?></td>
                    <td><?= esc(number_format((float) $employee['jours_pris_total'], 2, ',', ' ')) ?></td>
                    <td><?= esc(number_format((float) $employee['solde_restant'], 2, ',', ' ')) ?></td>
                    <td><?= esc((string) $employee['demandes']) ?></td>
                    <td><?= esc(number_format((float) $employee['jours_demandes_periode'], 2, ',', ' ')) ?></td>
                    <td><?= esc(number_format((float) $employee['jours_approuves_periode'], 2, ',', ' ')) ?></td>
                    <td>
                        <?php if (! empty($employee['par_type'])): ?>
                            <?php $chunks = []; ?>
                            <?php foreach ($employee['par_type'] as $type => $details): ?>
                                <?php $chunks[] = $type . ': ' . number_format((float) $details['jours'], 2, ',', ' ') . 'j / ' . (int) $details['demandes'] . ' dem'; ?>
                            <?php endforeach; ?>
                            <?= esc(implode(' | ', $chunks)) ?>
                        <?php else: ?>
                            --
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">Aucune donnée sur la période.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<h3>Détail des demandes</h3>

<table>
    <thead>
        <tr>
            <th>Date soumission</th>
            <th>Matricule</th>
            <th>Prénom</th>
            <th>Nom</th>
            <th>Type</th>
            <th>Début</th>
            <th>Fin</th>
            <th>Jours ouvrables</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php if (! empty($rows ?? [])): ?>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= esc($r['date_soumission']) ?></td>
                    <td><?= esc($r['matricule']) ?></td>
                    <td><?= esc($r['prenom']) ?></td>
                    <td><?= esc($r['nom']) ?></td>
                    <td><?= esc($r['type_conge']) ?></td>
                    <td><?= esc($r['date_debut']) ?></td>
                    <td><?= esc($r['date_fin']) ?></td>
                    <td><?= esc($r['jours_ouvrables']) ?></td>
                    <td><?= esc($r['statut']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">Aucune demande de congé sur la période.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
