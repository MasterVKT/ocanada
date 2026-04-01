<?php
$pdo = new PDO('mysql:host=localhost;dbname=ocanada_db;charset=utf8mb4', 'root', '');
$users = $pdo->query('SELECT id, email, role, statut, employe_id FROM utilisateurs')->fetchAll(PDO::FETCH_ASSOC);
echo 'UTILISATEURS:' . PHP_EOL;
foreach ($users as $u) {
    echo implode(' | ', array_map(fn($v) => $v ?? 'NULL', $u)) . PHP_EOL;
}

$cols = $pdo->query('SHOW COLUMNS FROM employes')->fetchAll(PDO::FETCH_COLUMN);
echo PHP_EOL . 'EMPLOYES COLUMNS: ' . implode(', ', $cols) . PHP_EOL;

$emps = $pdo->query('SELECT id, matricule, nom, prenom, poste, departement, statut FROM employes')->fetchAll(PDO::FETCH_ASSOC);
echo PHP_EOL . 'EMPLOYES:' . PHP_EOL;
foreach ($emps as $e) {
    echo implode(' | ', array_map(fn($v) => $v ?? 'NULL', $e)) . PHP_EOL;
}
