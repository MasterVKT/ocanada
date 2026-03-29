<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge - <?= esc($visitor['prenom']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f5f5f5;
            padding: 20px;
            font-family: Arial, sans-serif;
        }

        .badge-card {
            width: 400px;
            height: 500px;
            background: white;
            border: 3px solid #333;
            margin: 20px auto;
            padding: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-after: always;
        }

        .badge-card .header {
            text-align: center;
            border-bottom: 3px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .badge-card .header h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 5px;
        }

        .badge-card .header .subtext {
            color: #666;
            font-size: 11px;
        }

        .badge-card .visitor-name {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #000;
            margin-bottom: 15px;
        }

        .badge-card .detail-row {
            margin-bottom: 12px;
            font-size: 12px;
        }

        .badge-card .detail-row .label {
            color: #666;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }

        .badge-card .detail-row .value {
            color: #000;
            font-size: 13px;
        }

        .badge-card .badge-id-box {
            background-color: #f0f0f0;
            border: 2px solid #333;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
            margin: 15px 0;
        }

        .badge-card .badge-id-box .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .badge-card .badge-id-box .id {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            font-family: 'Courier New', monospace;
            color: #000;
        }

        .badge-card .qr-section {
            text-align: center;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
        }

        .badge-card .qr-section img {
            max-width: 180px;
            max-height: 180px;
        }

        .badge-card .footer {
            text-align: center;
            border-top: 2px dashed #333;
            padding-top: 10px;
            font-size: 10px;
            color: #666;
        }

        .print-controls {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-controls button {
            padding: 10px 20px;
            margin: 0 5px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-controls button:hover {
            background-color: #0056b3;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            .print-controls {
                display: none;
            }
            .badge-card {
                margin: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button onclick="window.print();">🖨️ Imprimer</button>
        <button onclick="window.close();">✕ Fermer</button>
    </div>

    <!-- Badge Card -->
    <div class="badge-card">
        <!-- Header -->
        <div class="header">
            <h2>🏢 OCanada</h2>
            <div class="subtext">Badge d'accès visiteur</div>
        </div>

        <!-- Visitor Name -->
        <div class="visitor-name">
            <?= esc($visitor['prenom']) ?> <?= strtoupper(esc($visitor['nom'])) ?>
        </div>

        <!-- Details -->
        <div class="detail-row">
            <span class="label">Entreprise:</span>
            <span class="value"><?= esc($visitor['entreprise'] ?? 'Visiteur') ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Motif:</span>
            <span class="value"><?= esc($visitor['motif']) ?></span>
        </div>

        <div class="detail-row">
            <span class="label">Pour voir:</span>
            <span class="value"><?= esc($visitor['personne_a_voir']) ?></span>
        </div>

        <!-- Badge ID -->
        <div class="badge-id-box">
            <div class="label">No de badge</div>
            <div class="id"><?= esc($visitor['badge_id']) ?></div>
        </div>

        <!-- QR Code -->
        <div class="qr-section">
            <img src="<?= $qrCodeUrl ?>" alt="QR Code">
        </div>

        <!-- Footer -->
        <div class="footer">
            <div><?= date('d/m/Y à H:i') ?></div>
            <div style="margin-top: 3px;">Conservez ce badge pendant votre visite</div>
        </div>
    </div>

    <script>
        // Auto print on load if needed
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
