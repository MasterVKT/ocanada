<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge d'accès - <?= esc($visitor['prenom'] . ' ' . $visitor['nom']) ?></title>
    <link href="<?= base_url('bootstrap-5.3.8-dist/css/bootstrap.min.css') ?>" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .badge-card {
            width: 400px;
            height: 500px;
            background: white;
            border: 2px solid #333;
            margin: 20px auto;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            page-break-after: always;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-radius: 8px;
        }
        .badge-card .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .badge-card h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .badge-card .detail {
            text-align: center;
            margin-bottom: 10px;
            font-size: 13px;
        }
        .badge-card .detail strong {
            display: block;
            font-size: 14px;
            color: #000;
            margin-bottom: 3px;
        }
        .badge-card .qr-code {
            text-align: center;
            margin: 20px 0;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .badge-card .qr-code img {
            max-width: 250px;
            max-height: 250px;
        }
        .badge-card .footer {
            text-align: center;
            border-top: 2px solid #333;
            padding-top: 10px;
            font-size: 11px;
            color: #666;
        }
        .badge-id {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 2px;
            background-color: #f0f0f0;
            padding: 8px;
            border-radius: 4px;
            margin: 10px 0;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4 text-center">
        <button class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Imprimer
        </button>
        <a href="<?= base_url('admin/visitors/' . $visitor['id']) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <!-- Badge Card -->
    <div class="badge-card">
        <!-- Header -->
        <div class="header">
            <h3>BADGE D'ACCÈS</h3>
        </div>

        <!-- Visitor Info -->
        <div class="detail">
            <strong><?= esc($visitor['prenom']) ?> <?= esc(strtoupper($visitor['nom'])) ?></strong>
            <small class="text-muted"><?= esc($visitor['entreprise'] ?? 'Visiteur') ?></small>
        </div>

        <!-- Visit Details -->
        <div class="detail">
            <strong style="font-size: 12px;">Motif:</strong>
            <small class="text-muted"><?= esc($visitor['motif']) ?></small>
        </div>

        <div class="detail">
            <strong style="font-size: 12px;">Pour voir:</strong>
            <small class="text-muted"><?= esc($visitor['personne_a_voir']) ?></small>
        </div>

        <div class="detail">
            <strong style="font-size: 12px;">Date:</strong>
            <small class="text-muted"><?= date('d/m/Y', strtotime($visitor['date_creation'])) ?></small>
        </div>

        <!-- Badge ID -->
        <div class="badge-id">
            <?= esc($visitor['badge_id']) ?>
        </div>

        <!-- QR Code -->
        <div class="qr-code">
            <img src="<?= $qrCodeUrl ?>" alt="QR Code">
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0; margin-bottom: 5px;">
                Imprimé le <?= date('d/m/Y \à H:i') ?>
            </p>
            <p style="margin: 0;">
                OCanada - Gestion RH
            </p>
        </div>
    </div>

    <!-- Multiple copies for printing -->
    <div class="no-print mt-4 text-center">
        <small class="text-muted">Imprimez cette page pour obtenir le badge d'accès du visiteur</small>
    </div>

    <script src="<?= base_url('bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
