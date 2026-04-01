<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

/**
 * Représente les documents RH (contrats, attestations, etc.)
 */
class DocumentRHModel extends Model
{
    protected $table            = 'documents_rh';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'employe_id',
        'titre',
        'type',
        'fichier',
        'description',
        'uploadé_par',
        'date_creation',
        'date_modification',
    ];

    protected $useTimestamps = false;

    /**
     * @var list<string>|null
     */
    private ?array $columnsCache = null;

    /**
     * Répertoire de stockage des fichiers (public)
     */
    public function getUploadDir(): string
    {
        return rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents';
    }

    /**
     * Chemin complet pour un fichier enregistré
     */
    public function getFilePath(string $filename): string
    {
        return $this->getUploadDir() . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * @return list<string>
     */
    public function getTableColumns(): array
    {
        if ($this->columnsCache !== null) {
            return $this->columnsCache;
        }

        try {
            $this->columnsCache = $this->db->getFieldNames($this->table);
        } catch (\Throwable) {
            $this->columnsCache = [];
        }

        return $this->columnsCache;
    }

    public function hasColumn(string $name): bool
    {
        return in_array($name, $this->getTableColumns(), true);
    }

    public function filterToExistingColumns(array $payload): array
    {
        $columns = $this->getTableColumns();
        if ($columns === []) {
            return $payload;
        }

        $allowed = array_flip($columns);
        return array_intersect_key($payload, $allowed);
    }

    public function getUploaderColumn(): ?string
    {
        foreach (['uploadé_par', 'uploade_par', 'uploaded_by'] as $column) {
            if ($this->hasColumn($column)) {
                return $column;
            }
        }

        return null;
    }

    public function getRawStoredPath(array $document): ?string
    {
        $file = trim((string) ($document['fichier'] ?? ''));
        if ($file !== '') {
            return $file;
        }

        $legacyPath = trim((string) ($document['chemin_fichier'] ?? ''));
        if ($legacyPath !== '') {
            return $legacyPath;
        }

        return null;
    }

    public function resolveExistingFilePath(array $document): ?string
    {
        $storedPath = $this->getRawStoredPath($document);
        if ($storedPath === null) {
            return null;
        }

        $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $storedPath);
        $basename = basename($normalized);

        $candidates = [];

        if (preg_match('/^[A-Za-z]:\\\\/', $normalized) === 1 || str_starts_with($normalized, DIRECTORY_SEPARATOR)) {
            $candidates[] = $normalized;
        }

        $relative = ltrim($normalized, DIRECTORY_SEPARATOR);
        $candidates[] = ROOTPATH . $relative;
        $candidates[] = FCPATH . $relative;
        $candidates[] = $this->getUploadDir() . DIRECTORY_SEPARATOR . $basename;
        $candidates[] = rtrim(ROOTPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents' . DIRECTORY_SEPARATOR . $basename;

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function ensureDownloadableFile(array $document): ?string
    {
        $existing = $this->resolveExistingFilePath($document);
        if ($existing !== null) {
            return $existing;
        }

        $rawPath = $this->getRawStoredPath($document);
        if ($rawPath === null) {
            return null;
        }

        $targetFilename = basename(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rawPath));
        if ($targetFilename === '') {
            return null;
        }

        $targetPath = $this->getUploadDir() . DIRECTORY_SEPARATOR . $targetFilename;
        $this->ensureUploadDirectory();

        $extension = strtolower((string) pathinfo($targetFilename, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return null;
        }

        $pdfBytes = $this->buildSimplePdfBytes($document);
        if ($pdfBytes === null) {
            return null;
        }

        if (@file_put_contents($targetPath, $pdfBytes) === false) {
            return null;
        }

        return is_file($targetPath) ? $targetPath : null;
    }

    public function getDownloadFilename(array $document, ?string $filePath = null): string
    {
        $original = trim((string) ($document['nom_original'] ?? ''));
        if ($original !== '') {
            return $original;
        }

        $baseName = trim((string) ($document['titre'] ?? 'document'));
        $baseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseName) ?: 'document';
        $baseName = (string) substr($baseName, 0, 200);

        $ext = '';
        if ($filePath !== null) {
            $ext = strtolower((string) pathinfo($filePath, PATHINFO_EXTENSION));
        }

        if ($ext === '') {
            $rawPath = $this->getRawStoredPath($document);
            $ext = strtolower((string) pathinfo((string) $rawPath, PATHINFO_EXTENSION));
        }

        if ($ext === '') {
            $ext = 'pdf';
        }

        return $baseName . '.' . $ext;
    }

    /**
     * Ensure the upload directory exists and is writable.
     */
    public function ensureUploadDirectory(): void
    {
        $dir = $this->getUploadDir();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    /**
     * Supprime un document et son fichier associé.
     */
    public function deleteWithFile(int $id): bool
    {
        $document = $this->find($id);
        if (! $document) {
            return false;
        }

        $filePath = $this->resolveExistingFilePath($document);
        if ($filePath !== null && is_file($filePath)) {
            @unlink($filePath);
        }

        return (bool) $this->delete($id);
    }

    private function buildSimplePdfBytes(array $document): ?string
    {
        $dompdfClass = 'Dompdf\\Dompdf';
        if (class_exists($dompdfClass)) {
            $title = htmlspecialchars((string) ($document['titre'] ?? 'Document RH'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $type = htmlspecialchars((string) ($document['type'] ?? 'Document'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $description = htmlspecialchars((string) ($document['description'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $generatedAt = date('d/m/Y H:i');

            $html = '<html><body style="font-family: DejaVu Sans, sans-serif; font-size: 12px;">'
                . '<h1 style="font-size: 20px; margin-bottom: 8px;">' . $title . '</h1>'
                . '<p><strong>Type:</strong> ' . $type . '</p>'
                . '<p><strong>Date de generation:</strong> ' . $generatedAt . '</p>'
                . '<hr>'
                . '<p>' . ($description !== '' ? $description : 'Document reconstruit automatiquement depuis les metadonnees en base de donnees.') . '</p>'
                . '</body></html>';

            $dompdf = new $dompdfClass(['defaultFont' => 'DejaVu Sans']);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return $dompdf->output();
        }

        return null;
    }
}
