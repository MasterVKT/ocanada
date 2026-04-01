<?php

declare(strict_types=1);

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    private string $now;

    /** @var array<int,int> */
    private array $employeeIds = [];

    /** @var array<int,int> */
    private array $userIds = [];

    /** @var array<int,int> */
    private array $demoEmployeeIds = [];

    /** @var array<int,int> */
    private array $demoUserIds = [];

    public function run(): void
    {
        $this->now = date('Y-m-d H:i:s');

        $this->call(InitialDataSeeder::class);
        $this->cleanupAnemenaDuplicate();
        $this->seedUsersAndEmployees();
        $this->cleanupAnemenaDuplicate();
        $this->refreshEntityPools();
        $this->seedShiftsAndAssignments();
        $this->seedLeaveBalances();
        $this->seedLeaveRequests();
        $this->seedPresences();
        $this->seedVisitors();
        $this->seedNotifications();
        $this->seedDocuments();
        $this->seedAuditLogs();

        $this->printSummary();
    }

    private function seedUsersAndEmployees(): void
    {
        $passwordHash = password_hash('Demo123!', PASSWORD_BCRYPT, ['cost' => 10]);

        $adminId = $this->upsertUser('admin.demo@ocanada.local', 'admin', $passwordHash, 'Admin Demo');
        $agentId = $this->upsertUser('agent.demo@ocanada.local', 'agent', $passwordHash, 'Agent Demo');

        $this->demoUserIds = [$adminId, $agentId];

        $employees = [
            ['email' => 'pauline.ngassa@ocanada.local', 'nom' => 'Ngassa', 'prenom' => 'Pauline', 'departement' => 'Direction', 'poste' => 'Responsable RH', 'contrat' => 'CDI', 'embauche' => '2020-02-03', 'salaire_base' => 680000.00],
            ['email' => 'emmanuel.kamga@ocanada.local', 'nom' => 'Kamga', 'prenom' => 'Emmanuel', 'departement' => 'Comptabilite', 'poste' => 'Comptable', 'contrat' => 'CDI', 'embauche' => '2019-06-17', 'salaire_base' => 520000.00],
            ['email' => 'chantal.eyenga@ocanada.local', 'nom' => 'Eyenga', 'prenom' => 'Chantal', 'departement' => 'Commercial', 'poste' => 'Chargee commerciale', 'contrat' => 'CDD', 'embauche' => '2023-01-09', 'salaire_base' => 390000.00],
            ['email' => 'frederic.mboua@ocanada.local', 'nom' => 'Mboua', 'prenom' => 'Frederic', 'departement' => 'Logistique', 'poste' => 'Coordinateur logistique', 'contrat' => 'CDI', 'embauche' => '2021-03-22', 'salaire_base' => 460000.00],
            ['email' => 'alice.tchoumi@ocanada.local', 'nom' => 'Tchoumi', 'prenom' => 'Alice', 'departement' => 'Direction', 'poste' => 'Assistante de direction', 'contrat' => 'CDD', 'embauche' => '2024-04-15', 'salaire_base' => 300000.00],
            ['email' => 'vincent.fotso@ocanada.local', 'nom' => 'Fotso', 'prenom' => 'Vincent', 'departement' => 'Commercial', 'poste' => 'Commercial terrain', 'contrat' => 'CDI', 'embauche' => '2022-11-07', 'salaire_base' => 340000.00],
            ['email' => 'marie.nji@ocanada.local', 'nom' => 'Nji', 'prenom' => 'Marie', 'departement' => 'Comptabilite', 'poste' => 'Assistante comptable', 'contrat' => 'Stage', 'embauche' => '2025-09-01', 'salaire_base' => 180000.00],
            ['email' => 'didier.mbarga@ocanada.local', 'nom' => 'Mbarga', 'prenom' => 'Didier', 'departement' => 'Logistique', 'poste' => 'Magasinier', 'contrat' => 'CDI', 'embauche' => '2020-10-12', 'salaire_base' => 280000.00],
        ];

        $sequence = 1;
        foreach ($employees as $item) {
            $userId = $this->upsertUser($item['email'], 'employe', $passwordHash, $item['prenom'] . ' ' . $item['nom']);
            $this->demoUserIds[] = $userId;

            $employeeId = $this->upsertEmployee($userId, $item, $sequence);
            $sequence++;

            $this->demoEmployeeIds[] = $employeeId;

            if ($this->columnExists('utilisateurs', 'employe_id')) {
                $this->db->table('utilisateurs')->where('id', $userId)->update(['employe_id' => $employeeId]);
            }
        }
    }

    private function refreshEntityPools(): void
    {
        $this->employeeIds = array_map(
            static fn(array $row): int => (int) $row['id'],
            $this->db->table('employes')
                ->select('id')
                ->where('statut', 'actif')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray()
        );

        $this->userIds = array_map(
            static fn(array $row): int => (int) $row['id'],
            $this->db->table('utilisateurs')
                ->select('id')
                ->where('statut', 'actif')
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray()
        );
    }

    private function seedShiftsAndAssignments(): void
    {
        if (! $this->tableExists('shifts_modeles') || ! $this->tableExists('affectations_shifts')) {
            return;
        }

        $shiftDay = $this->upsertByUnique('shifts_modeles', 'nom', 'Journee Standard', [
            'nom' => 'Journee Standard',
            'heure_debut' => '08:00:00',
            'heure_fin' => '17:00:00',
            'pause_minutes' => 60,
            'jours_actifs' => '1,2,3,4,5',
            'actif' => 1,
            'date_creation' => $this->now,
            'date_modification' => $this->now,
        ]);

        $shiftMorning = $this->upsertByUnique('shifts_modeles', 'nom', 'Matin Operationnel', [
            'nom' => 'Matin Operationnel',
            'heure_debut' => '07:00:00',
            'heure_fin' => '15:00:00',
            'pause_minutes' => 45,
            'jours_actifs' => '1,2,3,4,5',
            'actif' => 1,
            'date_creation' => $this->now,
            'date_modification' => $this->now,
        ]);

        $shiftEvening = $this->upsertByUnique('shifts_modeles', 'nom', 'Soir Service', [
            'nom' => 'Soir Service',
            'heure_debut' => '10:00:00',
            'heure_fin' => '19:00:00',
            'pause_minutes' => 60,
            'jours_actifs' => '1,2,3,4,5',
            'actif' => 1,
            'date_creation' => $this->now,
            'date_modification' => $this->now,
        ]);

        $shiftIds = [$shiftDay, $shiftMorning, $shiftEvening];

        foreach ($this->employeeIds as $index => $employeeId) {
            $shiftId = $shiftIds[$index % count($shiftIds)];

            $existing = $this->db->table('affectations_shifts')
                ->where('employe_id', $employeeId)
                ->where('actif', 1)
                ->get()
                ->getRowArray();

            $data = $this->filterExistingColumns('affectations_shifts', [
                'employe_id' => $employeeId,
                'shift_id' => $shiftId,
                'date_debut' => '2026-01-01',
                'date_fin' => null,
                'actif' => 1,
                'date_creation' => $this->now,
                'date_modification' => $this->now,
            ]);

            if ($existing === null) {
                $this->db->table('affectations_shifts')->insert($data);
            }
        }
    }

    private function seedLeaveBalances(): void
    {
        if (! $this->tableExists('soldes_conges')) {
            return;
        }

        $year = (int) date('Y');

        foreach ($this->employeeIds as $index => $employeeId) {
            $annual = 25.0;
            $taken = (float) (($index % 5) + 2);
            $remaining = max(0.0, $annual - $taken);

            $data = [
                'employe_id' => $employeeId,
                'annee' => $year,
                'jours_total' => $annual,
                'jours_pris' => $taken,
                'jours_restants' => $remaining,
                'date_mise_a_jour' => $this->now,
                'solde_annuel' => $annual,
                'pris' => $taken,
                'restant' => $remaining,
                'maladie_pris' => 0,
                'maladie_restant' => 30,
                'reporte' => 0,
                'date_creation' => $this->now,
                'date_modification' => $this->now,
            ];

            $existing = $this->db->table('soldes_conges')
                ->where('employe_id', $employeeId)
                ->where('annee', $year)
                ->get()
                ->getRowArray();

            $filtered = $this->filterExistingColumns('soldes_conges', $data);

            if ($existing === null) {
                $this->db->table('soldes_conges')->insert($filtered);
            }
        }
    }

    private function seedLeaveRequests(): void
    {
        $table = $this->resolveLeaveTable();
        if ($table === null) {
            return;
        }

        $adminUserId = $this->findUserIdByEmail('admin.demo@ocanada.local');

        $entries = [
            ['offset' => 0, 'type' => 'annuel', 'start' => '2026-01-12', 'end' => '2026-01-16', 'days' => 5.0, 'status' => 'approuve'],
            ['offset' => 1, 'type' => 'maladie', 'start' => '2026-02-03', 'end' => '2026-02-04', 'days' => 2.0, 'status' => 'approuve'],
            ['offset' => 2, 'type' => 'sans_solde', 'start' => '2026-03-18', 'end' => '2026-03-20', 'days' => 3.0, 'status' => 'en_attente'],
            ['offset' => 3, 'type' => 'autre', 'start' => '2026-03-15', 'end' => '2026-03-15', 'days' => 1.0, 'status' => 'refuse'],
            ['offset' => 4, 'type' => 'maternite_paternite', 'start' => '2026-05-04', 'end' => '2026-05-15', 'days' => 10.0, 'status' => 'approuve'],
            ['offset' => 5, 'type' => 'annuel', 'start' => '2026-06-01', 'end' => '2026-06-05', 'days' => 5.0, 'status' => 'annule'],
            ['offset' => 6, 'type' => 'annuel', 'start' => '2026-07-06', 'end' => '2026-07-10', 'days' => 5.0, 'status' => 'en_attente'],
            ['offset' => 7, 'type' => 'maladie', 'start' => '2025-12-09', 'end' => '2025-12-11', 'days' => 3.0, 'status' => 'approuve'],
            ['offset' => 8, 'type' => 'autre', 'start' => '2026-02-25', 'end' => '2026-02-25', 'days' => 1.0, 'status' => 'approuve'],
        ];

        foreach ($entries as $entry) {
            if (!isset($this->employeeIds[$entry['offset']])) {
                continue;
            }

            $employeeId = $this->employeeIds[$entry['offset']];
            $data = $this->buildLeaveRow($table, $employeeId, $adminUserId, $entry);
            $filtered = $this->filterExistingColumns($table, $data);

            $existing = $this->db->table($table)
                ->where('employe_id', $employeeId)
                ->where('date_debut', $entry['start'])
                ->where('date_fin', $entry['end'])
                ->get()
                ->getRowArray();

            if ($existing === null) {
                try {
                    $this->db->table($table)->insert($filtered);
                } catch (\Throwable) {
                    // Some instances may expose a non-updatable view for legacy compatibility.
                }
            } else {
                try {
                    $this->db->table($table)->where('id', $existing['id'])->update($filtered);
                } catch (\Throwable) {
                    // Ignore update failures on non-updatable legacy views.
                }
            }
        }
    }

    /**
     * @param array{offset:int,type:string,start:string,end:string,days:float,status:string} $entry
     * @return array<string,mixed>
     */
    private function buildLeaveRow(string $table, int $employeeId, ?int $adminUserId, array $entry): array
    {
        $isApproved = $entry['status'] === 'approuve' || $entry['status'] === 'approuvee';
        $isRefused = $entry['status'] === 'refuse' || $entry['status'] === 'refusee';

        if ($table === 'conge_demandes') {
            return [
                'employe_id' => $employeeId,
                'type_conge' => $entry['type'],
                'date_debut' => $entry['start'],
                'date_fin' => $entry['end'],
                'nombre_jours' => $entry['days'],
                'motif' => 'Demande de demonstration generee automatiquement.',
                'statut' => $entry['status'],
                'approuve_par' => $isApproved || $isRefused ? $adminUserId : null,
                'refus_motif' => $isRefused ? 'Chevauchement operationnel (demo).' : null,
                'commentaire' => $entry['status'] === 'annule' ? 'Annulation employee (demo).' : null,
                'date_demande' => date('Y-m-d H:i:s', strtotime($entry['start'] . ' -7 days')),
                'date_approbation' => $isApproved || $isRefused ? date('Y-m-d H:i:s', strtotime($entry['start'] . ' -2 days')) : null,
                'date_modification' => $this->now,
            ];
        }

        return [
            'employe_id' => $employeeId,
            'type_conge' => $entry['type'],
            'type_detail' => $entry['type'] === 'autre' ? 'Demarche administrative' : null,
            'date_debut' => $entry['start'],
            'date_fin' => $entry['end'],
            'jours_ouvrables' => $entry['days'],
            'motif' => 'Demande de demonstration generee automatiquement.',
            'statut' => str_replace('approuve', 'approuvee', str_replace('refuse', 'refusee', $entry['status'])),
            'date_soumission' => date('Y-m-d H:i:s', strtotime($entry['start'] . ' -7 days')),
            'date_traitement' => $isApproved || $isRefused ? date('Y-m-d H:i:s', strtotime($entry['start'] . ' -2 days')) : null,
            'traite_par' => $isApproved || $isRefused ? $adminUserId : null,
            'commentaire_admin' => $isRefused ? 'Chevauchement operationnel (demo).' : null,
        ];
    }

    private function seedPresences(): void
    {
        if (! $this->tableExists('presences')) {
            return;
        }

        $adminUserId = $this->findUserIdByEmail('admin.demo@ocanada.local');

        $dates = [];
        $holidayDates = $this->getHolidayDates();
        $cursor = new \DateTimeImmutable(date('Y-m-01', strtotime('-5 months')));
        $end = new \DateTimeImmutable(date('Y-m-d'));

        while ($cursor <= $end) {
            $date = $cursor->format('Y-m-d');
            $weekDay = (int) $cursor->format('w');

            if ($weekDay !== 0 && $weekDay !== 6 && ! isset($holidayDates[$date])) {
                $dates[] = $date;
            }

            $cursor = $cursor->modify('+1 day');
        }

        foreach ($this->employeeIds as $employeeIndex => $employeeId) {
            foreach ($dates as $dateIndex => $date) {
                [$status, $arrival, $retard, $departure] = $this->resolvePresencePattern($employeeIndex, $dateIndex, $date);

                $row = $this->filterExistingColumns('presences', [
                    'employe_id' => $employeeId,
                    'date_pointage' => $date,
                    'heure_pointage' => $arrival,
                    'heure_sortie' => $departure,
                    'statut' => $status,
                    'retard_minutes' => $retard,
                    'corrige' => $status === 'absent' && ($employeeIndex % 2 === 0) ? 1 : 0,
                    'motif_correction' => $status === 'absent' && ($employeeIndex % 2 === 0) ? 'Absence justifiee medicalement (demo).' : null,
                    'corrige_par_utilisateur_id' => $status === 'absent' && ($employeeIndex % 2 === 0) ? $adminUserId : null,
                    'date_correction' => $status === 'absent' && ($employeeIndex % 2 === 0) ? $this->now : null,
                    'source' => 'kiosque',
                    'date_creation' => $this->now,
                    'date_modification' => $this->now,
                ]);

                $existing = $this->db->table('presences')
                    ->where('employe_id', $employeeId)
                    ->where('date_pointage', $date)
                    ->get()
                    ->getRowArray();

                if ($existing === null) {
                    $this->db->table('presences')->insert($row);
                }
            }
        }
    }

    private function seedVisitors(): void
    {
        if (! $this->tableExists('visiteurs') || count($this->employeeIds) === 0) {
            return;
        }

        $rows = [];
        $baseVisitors = [
            ['nom' => 'Nana', 'prenom' => 'Bruno', 'motif' => 'Rendez-vous partenariat', 'company' => 'Sodicam'],
            ['nom' => 'Meka', 'prenom' => 'Sabrina', 'motif' => 'Depot de dossier', 'company' => 'Particulier'],
            ['nom' => 'Kenfack', 'prenom' => 'Lionel', 'motif' => 'Maintenance informatique', 'company' => 'IT Services CM'],
            ['nom' => 'Talla', 'prenom' => 'Ariane', 'motif' => 'Suivi commercial', 'company' => 'BlueTrade'],
        ];

        for ($monthOffset = 5; $monthOffset >= 0; $monthOffset--) {
            $monthStart = new \DateTimeImmutable(date('Y-m-01', strtotime('-' . $monthOffset . ' months')));
            foreach ($baseVisitors as $index => $baseVisitor) {
                $visitDate = $monthStart->modify('+' . (3 + ($index * 4)) . ' days')->setTime(9 + $index, 10, 0);
                $status = ($monthOffset === 0 && $index % 2 === 0) ? 'present' : 'departi';

                $rows[] = [
                    'key' => 'VIS-DEMO-' . $visitDate->format('Ym') . '-' . sprintf('%02d', $index + 1),
                    'nom' => $baseVisitor['nom'],
                    'prenom' => $baseVisitor['prenom'],
                    'motif' => $baseVisitor['motif'],
                    'company' => $baseVisitor['company'],
                    'status' => $status,
                    'arrivee' => $visitDate->format('Y-m-d H:i:s'),
                    'depart' => $status === 'present' ? null : $visitDate->modify('+2 hours 15 minutes')->format('Y-m-d H:i:s'),
                ];
            }
        }

        foreach ($rows as $index => $row) {
            $employeeId = $this->employeeIds[$index % count($this->employeeIds)];
            $arriveeAt = $row['arrivee'];
            $departAt = $row['depart'];

            $payload = $this->filterExistingColumns('visiteurs', [
                'badge_id' => $row['key'],
                'numero_badge' => $row['key'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'nom_complet' => $row['prenom'] . ' ' . $row['nom'],
                'type_piece' => 'CNI',
                'numero_piece' => 'CMR-DEMO-' . sprintf('%04d', $index + 1),
                'email' => strtolower($row['prenom']) . '.' . strtolower($row['nom']) . '@example.test',
                'telephone' => '+23769910' . sprintf('%04d', $index + 11),
                'entreprise' => $row['company'],
                'motif' => $row['motif'],
                'motif_visite' => $row['motif'],
                'personne_a_voir' => 'Equipe RH',
                'personne_a_voir_id' => $employeeId,
                'service' => 'Administration',
                'heure_arrivee' => $arriveeAt,
                'heure_depart' => $departAt ? date('H:i:s', strtotime($departAt)) : null,
                'heure_sortie' => $departAt,
                'duree_visite_minutes' => $departAt ? 120 : null,
                'statut' => $this->columnExists('visiteurs', 'statut') && $this->isVisitorStatusSorti() ? str_replace('departi', 'sorti', $row['status']) : $row['status'],
                'commentaire' => 'Visite de demonstration',
                'motif_detail' => 'Seed demo',
                'date_creation' => $arriveeAt,
                'date_modification' => $departAt ?? $arriveeAt,
            ]);

            $lookupColumn = $this->columnExists('visiteurs', 'badge_id') ? 'badge_id' : 'numero_badge';
            $existing = $this->db->table('visiteurs')->where($lookupColumn, $row['key'])->get()->getRowArray();

            if ($existing === null) {
                $this->db->table('visiteurs')->insert($payload);
            } else {
                $this->db->table('visiteurs')->where('id', $existing['id'])->update($payload);
            }
        }
    }

    private function seedNotifications(): void
    {
        if (! $this->tableExists('notifications')) {
            return;
        }

        $messages = [
            ['type' => 'NOTIF_CONGE_SOUMIS', 'message' => 'Nouvelle demande de conge en attente de traitement.', 'lien' => '/admin/leaves'],
            ['type' => 'NOTIF_PRESENCE_RETARD', 'message' => 'Un retard a ete detecte ce matin.', 'lien' => '/admin/presences'],
            ['type' => 'NOTIF_DOCUMENT_DISPO', 'message' => 'Nouveau document RH disponible dans votre espace.', 'lien' => '/employe/documents'],
            ['type' => 'NOTIF_FINANCE_ALERTE', 'message' => 'Le cout d absenteisme du mois merite une revue.', 'lien' => '/admin/finance'],
            ['type' => 'NOTIF_VISITEUR_PRESENT', 'message' => 'Un visiteur est encore en attente a l accueil.', 'lien' => '/admin/visitors'],
        ];

        foreach ($this->userIds as $index => $userId) {
            $msg = $messages[$index % count($messages)];
            $created = date('Y-m-d H:i:s', strtotime($this->now . ' -' . ($index + 1) . ' hours'));

            $existing = $this->db->table('notifications')
                ->where('destinataire_id', $userId)
                ->where('type', $msg['type'])
                ->where('message', $msg['message'])
                ->get()
                ->getRowArray();

            $data = $this->filterExistingColumns('notifications', [
                'destinataire_id' => $userId,
                'type' => $msg['type'],
                'message' => $msg['message'],
                'lien' => $msg['lien'],
                'lue' => $index % 3 === 0 ? 1 : 0,
                'date_creation' => $created,
                'date_lecture' => $index % 3 === 0 ? date('Y-m-d H:i:s', strtotime($created . ' +30 minutes')) : null,
            ]);

            if ($existing === null) {
                $this->db->table('notifications')->insert($data);
            }
        }
    }

    private function seedDocuments(): void
    {
        if (! $this->tableExists('documents_rh') || count($this->employeeIds) === 0 || count($this->userIds) === 0) {
            return;
        }

        $this->ensureDocumentsDirectory();

        $adminId = $this->findUserIdByEmail('admin.demo@ocanada.local') ?? $this->userIds[0];

        $documents = [
            ['type' => 'Contrat', 'title' => 'Contrat de travail', 'file' => 'demo-contrat-rh-001.pdf', 'size' => 245760],
            ['type' => 'Attestation', 'title' => 'Attestation d emploi', 'file' => 'demo-attestation-001.pdf', 'size' => 102400],
            ['type' => 'Reglement', 'title' => 'Reglement interieur', 'file' => 'demo-reglement-001.pdf', 'size' => 307200],
            ['type' => 'Bulletin', 'title' => 'Bulletin interne RH', 'file' => 'demo-bulletin-rh-001.pdf', 'size' => 204800],
        ];

        foreach ($this->employeeIds as $index => $employeeId) {
            $doc = $documents[$index % count($documents)];
            $title = $doc['title'] . ' - ' . ($index + 1);
            $this->ensureDemoDocumentFile($doc['file'], $title, $doc['type']);

            $data = $this->filterExistingColumns('documents_rh', [
                'employe_id' => $employeeId,
                'type' => $doc['type'],
                'type_document' => $doc['type'],
                'titre' => $title,
                'fichier' => $doc['file'],
                'chemin_fichier' => 'documents/' . $doc['file'],
                'description' => 'Document de demonstration genere automatiquement.',
                'date_document' => date('Y-m-d', strtotime('2026-01-15')),
                'nom_original' => $doc['file'],
                'taille_octets' => $doc['size'],
                'uploadé_par' => $adminId,
                'uploade_par' => $adminId,
                'date_upload' => $this->now,
                'date_creation' => $this->now,
                'date_modification' => $this->now,
            ]);

            $existing = $this->db->table('documents_rh')
                ->where('employe_id', $employeeId)
                ->where('titre', $title)
                ->get()
                ->getRowArray();

            if ($existing === null) {
                $this->db->table('documents_rh')->insert($data);
            }
        }
    }

    private function ensureDocumentsDirectory(): void
    {
        $dir = $this->getDocumentsUploadDir();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function getDocumentsUploadDir(): string
    {
        return rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents';
    }

    private function ensureDemoDocumentFile(string $filename, string $title, string $type): void
    {
        $path = $this->getDocumentsUploadDir() . DIRECTORY_SEPARATOR . $filename;
        if (is_file($path)) {
            return;
        }

        $dompdfClass = 'Dompdf\\Dompdf';
        if (! class_exists($dompdfClass)) {
            return;
        }

        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeType = htmlspecialchars($type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $safeDate = date('d/m/Y H:i');

        $html = '<html><body style="font-family: DejaVu Sans, sans-serif; font-size: 12px;">'
            . '<h1 style="font-size: 20px; margin-bottom: 8px;">' . $safeTitle . '</h1>'
            . '<p><strong>Type:</strong> ' . $safeType . '</p>'
            . '<p><strong>Genere le:</strong> ' . $safeDate . '</p>'
            . '<hr>'
            . '<p>Document de demonstration genere automatiquement par DemoDataSeeder.</p>'
            . '</body></html>';

        $dompdf = new $dompdfClass(['defaultFont' => 'DejaVu Sans']);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        @file_put_contents($path, $dompdf->output());
    }

    private function seedAuditLogs(): void
    {
        if (! $this->tableExists('audit_log') || count($this->userIds) === 0) {
            return;
        }

        $rows = [
            ['event' => 'CONNEXION', 'desc' => 'Connexion compte demo admin'],
            ['event' => 'DEMANDE_CONGE', 'desc' => 'Soumission d une demande de conge demo'],
            ['event' => 'POINTAGE', 'desc' => 'Pointage kiosque de demonstration'],
            ['event' => 'VISITEUR_ENREGISTRE', 'desc' => 'Enregistrement visiteur demo'],
            ['event' => 'EXPORT_FINANCE', 'desc' => 'Generation d un export financier de demonstration'],
            ['event' => 'UPLOAD_DOCUMENT_RH', 'desc' => 'Ajout d un document RH de demonstration'],
        ];

        foreach ($rows as $index => $item) {
            $userId = $this->userIds[$index % count($this->userIds)];

            $payload = $this->filterExistingColumns('audit_log', [
                'utilisateur_id' => $userId,
                'type_evenement' => $item['event'],
                'description' => $item['desc'],
                'donnees_avant' => null,
                'donnees_apres' => json_encode(['source' => 'demo_seeder', 'index' => $index], JSON_UNESCAPED_SLASHES),
                'ip_adresse' => '127.0.0.1',
                'date_evenement' => date('Y-m-d H:i:s', strtotime($this->now . ' -' . ($index + 1) . ' day')),
                'action' => $item['event'],
                'details' => $item['desc'],
                'user_agent' => 'DemoSeeder/1.0',
                'date_creation' => date('Y-m-d H:i:s', strtotime($this->now . ' -' . ($index + 1) . ' day')),
            ]);

            $exists = $this->db->table('audit_log')
                ->where('utilisateur_id', $userId)
                ->groupStart()
                ->where('type_evenement', $item['event'])
                ->orWhere('action', $item['event'])
                ->groupEnd()
                ->groupStart()
                ->where('description', $item['desc'])
                ->orWhere('details', $item['desc'])
                ->groupEnd()
                ->get()
                ->getRowArray();

            if ($exists === null) {
                $this->db->table('audit_log')->insert($payload);
            }
        }
    }

    /**
     * @return array{0:string,1:?string,2:int,3:?string}
     */
    private function resolvePresencePattern(int $employeeIndex, int $dateIndex, string $date): array
    {
        $seed = ($employeeIndex + 1) * 37 + ($dateIndex + 3) * 11;
        $status = 'present';
        $arrival = '07:56:00';
        $retard = 0;
        $departure = '17:06:00';

        if ($date === date('Y-m-d')) {
            if ($employeeIndex % 6 === 0) {
                return ['retard', '08:18:00', 18, '17:11:00'];
            }

            if ($employeeIndex % 7 === 0) {
                return ['absent', null, 0, null];
            }

            return ['present', '07:52:00', 0, null];
        }

        if ($seed % 17 === 0) {
            $status = 'absent';
            $arrival = null;
            $departure = null;
        } elseif ($seed % 9 === 0 || $seed % 13 === 0) {
            $status = 'retard';
            $retard = 10 + ($seed % 21);
            $arrival = date('H:i:s', strtotime('08:00:00 +' . $retard . ' minutes'));
            $departure = '17:14:00';
        }

        return [$status, $arrival, $retard, $departure];
    }

    /**
     * @return array<string,true>
     */
    private function getHolidayDates(): array
    {
        if (! $this->tableExists('jours_feries')) {
            return [];
        }

        $rows = $this->db->table('jours_feries')->select('date_ferie')->get()->getResultArray();
        $dates = [];

        foreach ($rows as $row) {
            $date = (string) ($row['date_ferie'] ?? '');
            if ($date !== '') {
                $dates[$date] = true;
            }
        }

        return $dates;
    }

    private function cleanupAnemenaDuplicate(): void
    {
        if (! $this->tableExists('employes')) {
            return;
        }

        $matches = $this->db->table('employes')
            ->where('LOWER(nom)', 'anemena')
            ->where('LOWER(prenom)', 'guy')
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        if (count($matches) <= 1) {
            return;
        }

        $keep = array_shift($matches);
        $keepId = (int) $keep['id'];

        foreach ($matches as $duplicate) {
            $duplicateId = (int) $duplicate['id'];
            $this->relinkEmployeeReferences($duplicateId, $keepId);
            $this->db->table('employes')->where('id', $duplicateId)->delete();
        }
    }

    private function relinkEmployeeReferences(int $fromEmployeeId, int $toEmployeeId): void
    {
        foreach (['presences', 'soldes_conges', 'conge_demandes', 'demandes_conge', 'conges', 'documents_rh', 'affectations_shifts'] as $table) {
            if (! $this->tableExists($table) || ! $this->columnExists($table, 'employe_id')) {
                continue;
            }

            $this->db->table($table)
                ->where('employe_id', $fromEmployeeId)
                ->update(['employe_id' => $toEmployeeId]);
        }

        if ($this->tableExists('visiteurs') && $this->columnExists('visiteurs', 'personne_a_voir_id')) {
            $this->db->table('visiteurs')
                ->where('personne_a_voir_id', $fromEmployeeId)
                ->update(['personne_a_voir_id' => $toEmployeeId]);
        }

        if ($this->tableExists('utilisateurs') && $this->columnExists('utilisateurs', 'employe_id')) {
            $this->db->table('utilisateurs')
                ->where('employe_id', $fromEmployeeId)
                ->update(['employe_id' => $toEmployeeId]);
        }
    }

    private function upsertUser(string $email, string $role, string $passwordHash, string $fullName): int
    {
        $existing = $this->db->table('utilisateurs')->where('email', $email)->get()->getRowArray();

        $data = $this->filterExistingColumns('utilisateurs', [
            'email' => $email,
            'mot_de_passe' => $passwordHash,
            'role' => $role,
            'statut' => 'actif',
            'date_creation' => $this->now,
            'derniere_connexion' => null,
        ]);

        if ($existing === null) {
            $this->db->table('utilisateurs')->insert($data);
            $userId = (int) $this->db->insertID();
        } else {
            $this->db->table('utilisateurs')->where('id', $existing['id'])->update($data);
            $userId = (int) $existing['id'];
        }

        return $userId;
    }

    /**
     * @param array<string,mixed> $employee
     */
    private function upsertEmployee(int $userId, array $employee, int $sequence): int
    {
        $matricule = sprintf('EMP-DEMO-%03d', $sequence);

        $existing = $this->db->table('employes')->where('matricule', $matricule)->get()->getRowArray();

        $daily = round(((float) $employee['salaire_base']) / 22, 2);

        $data = $this->filterExistingColumns('employes', [
            'utilisateur_id' => $userId,
            'matricule' => $matricule,
            'nom' => $employee['nom'],
            'prenom' => $employee['prenom'],
            'email' => $employee['email'],
            'telephone' => '+2376' . sprintf('%08d', 12000000 + $sequence),
            'telephone_1' => '+2376' . sprintf('%08d', 12000000 + $sequence),
            'telephone_2' => '+2376' . sprintf('%08d', 22000000 + $sequence),
            'date_naissance' => date('Y-m-d', strtotime('1990-01-01 +' . $sequence . ' months')),
            'genre' => $sequence % 2 === 0 ? 'homme' : 'femme',
            'nationalite' => 'Camerounaise',
            'numero_cni' => 'CMR' . date('y') . sprintf('%08d', $sequence),
            'date_embauche' => $employee['embauche'],
            'poste' => $employee['poste'],
            'departement' => $employee['departement'],
            'type_contrat' => $this->normalizeContract((string) $employee['contrat']),
            'date_fin_contrat' => ((string) $employee['contrat'] === 'CDD') ? '2027-12-31' : null,
            'salaire_base' => $employee['salaire_base'],
            'salaire_journalier' => $daily,
            'heure_debut_travail' => '08:00:00',
            'heure_fin_travail' => '17:00:00',
            'pin_kiosque' => password_hash(sprintf('%04d', 1100 + $sequence), PASSWORD_BCRYPT, ['cost' => 10]),
            'photo' => null,
            'adresse' => 'Douala - Akwa',
            'ville' => 'Douala',
            'code_postal' => '00000',
            'pays' => 'Cameroun',
            'statut' => 'actif',
            'date_creation' => $this->now,
            'date_modification' => $this->now,
        ]);

        if ($existing === null) {
            $this->db->table('employes')->insert($data);
            return (int) $this->db->insertID();
        }

        $this->db->table('employes')->where('id', $existing['id'])->update($data);
        return (int) $existing['id'];
    }

    private function normalizeContract(string $contract): string
    {
        $upper = strtoupper($contract);

        return match ($upper) {
            'CDI' => 'CDI',
            'CDD' => 'CDD',
            'CONSULTANT' => 'consultant',
            default => 'stage',
        };
    }

    private function resolveLeaveTable(): ?string
    {
        if ($this->tableExists('conge_demandes')) {
            return 'conge_demandes';
        }

        if ($this->tableExists('demandes_conge')) {
            return 'demandes_conge';
        }

        if ($this->tableExists('conges')) {
            return 'conges';
        }

        return null;
    }

    private function isVisitorStatusSorti(): bool
    {
        // In some schemas, status values are [present, sorti].
        $fields = $this->db->getFieldData('visiteurs');
        foreach ($fields as $field) {
            if ($field->name === 'statut' && isset($field->type) && is_string($field->type) && str_contains(strtolower($field->type), 'sorti')) {
                return true;
            }
        }

        return $this->columnExists('visiteurs', 'numero_badge');
    }

    private function findUserIdByEmail(string $email): ?int
    {
        $row = $this->db->table('utilisateurs')->select('id')->where('email', $email)->get()->getRowArray();
        return $row ? (int) $row['id'] : null;
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function filterExistingColumns(string $table, array $data): array
    {
        $fields = $this->db->getFieldNames($table);

        $filtered = [];
        foreach ($data as $key => $value) {
            if (in_array($key, $fields, true)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    private function tableExists(string $table): bool
    {
        return $this->db->tableExists($table);
    }

    private function columnExists(string $table, string $column): bool
    {
        return $this->db->fieldExists($column, $table);
    }

    /**
     * @param array<string,mixed> $data
     */
    private function upsertByUnique(string $table, string $uniqueColumn, string $uniqueValue, array $data): int
    {
        $existing = $this->db->table($table)->where($uniqueColumn, $uniqueValue)->get()->getRowArray();
        $filtered = $this->filterExistingColumns($table, $data);

        if ($existing === null) {
            $this->db->table($table)->insert($filtered);
            return (int) $this->db->insertID();
        }

        $this->db->table($table)->where('id', $existing['id'])->update($filtered);
        return (int) $existing['id'];
    }

    private function printSummary(): void
    {
        $employeesCount = count($this->employeeIds);
        $usersCount = count($this->userIds);

        echo PHP_EOL;
        echo '============================================================' . PHP_EOL;
        echo 'DEMO DATA SEEDED SUCCESSFULLY' . PHP_EOL;
        echo '============================================================' . PHP_EOL;
        echo 'Users upserted      : ' . $usersCount . PHP_EOL;
        echo 'Employees upserted  : ' . $employeesCount . PHP_EOL;
        echo 'Default password    : Demo123!' . PHP_EOL;
        echo 'Demo admin login    : admin.demo@ocanada.local' . PHP_EOL;
        echo 'Demo agent login    : agent.demo@ocanada.local' . PHP_EOL;
        echo 'Demo employee login : pauline.ngassa@ocanada.local' . PHP_EOL;
        echo '============================================================' . PHP_EOL;
        echo PHP_EOL;
    }
}
