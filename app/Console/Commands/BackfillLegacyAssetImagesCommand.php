<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BackfillLegacyAssetImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:backfill-legacy-images
        {--source= : Folder gambar lama. Contoh: C:\Users\Lenovo\Downloads\uploads productsaf}
        {--limit=0 : Batasi jumlah asset yang diproses}
        {--all : Proses semua data assets, bukan hanya yang belum punya thumbnail}
        {--dry-run : Simulasi tanpa menulis file/database}
        {--overwrite : Timpa file webp target jika sudah ada}
        {--scan-only : Proses semua file gambar di folder source tanpa update tabel assets}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-time backfill untuk generate thumbnail + compress webp dari gambar lama.';

    /**
     * Execute the console command.
     */
    public function handle(ImageOptimizer $imageOptimizer): int
    {
        $sourceDirectory = trim((string) $this->option('source'));
        if ($sourceDirectory === '') {
            $sourceDirectory = public_path('uploads');
        }

        if (! is_dir($sourceDirectory)) {
            $this->error("Folder source tidak ditemukan: {$sourceDirectory}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $overwrite = (bool) $this->option('overwrite');
        $scanOnly = (bool) $this->option('scan-only');
        $limit = max(0, (int) $this->option('limit'));

        $this->line('Mode: '.($dryRun ? 'DRY-RUN (tanpa write)' : 'EXECUTE'));
        $this->line("Source folder: {$sourceDirectory}");
        $this->line('Target folder: '.public_path('uploads'));
        $this->newLine();

        if ($scanOnly) {
            return $this->processDirectoryScan($imageOptimizer, $sourceDirectory, $dryRun, $overwrite, $limit);
        }

        if (! Schema::hasTable('assets')) {
            $this->error('Tabel assets belum tersedia. Import DB + migrate dulu.');

            return self::FAILURE;
        }

        return $this->processAssets($imageOptimizer, $sourceDirectory, $dryRun, $overwrite, $limit);
    }

    /**
     * Process by reading rows from assets table.
     */
    private function processAssets(
        ImageOptimizer $imageOptimizer,
        string $sourceDirectory,
        bool $dryRun,
        bool $overwrite,
        int $limit
    ): int {
        $all = (bool) $this->option('all');

        $query = Asset::query()
            ->select(['id', 'system_path', 'thumbnail_path', 'mime_type', 'size', 'original_file_name'])
            ->orderBy('created_at');

        if (! $all) {
            $query->where(function ($q): void {
                $q->whereNull('thumbnail_path')->orWhere('thumbnail_path', '');
            });
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $assets = $query->get();
        if ($assets->isEmpty()) {
            $this->info('Tidak ada data assets yang perlu diproses.');

            return self::SUCCESS;
        }

        $this->info("Total asset akan diproses: {$assets->count()}");

        $processed = 0;
        $skipped = 0;
        $failed = 0;

        $progressBar = $this->output->createProgressBar($assets->count());
        $progressBar->start();

        foreach ($assets as $asset) {
            $sourceFile = $this->resolveSourceFile($asset->system_path, $asset->original_file_name, $sourceDirectory);
            if ($sourceFile === null) {
                $failed += 1;
                $progressBar->advance();
                continue;
            }

            $baseName = $this->buildBaseName((string) $asset->id, (string) $asset->system_path, (string) $asset->original_file_name);

            if ($dryRun) {
                $processed += 1;
                $progressBar->advance();
                continue;
            }

            $optimized = $imageOptimizer->optimizeFromPath($sourceFile, 'uploads', $baseName, $overwrite);
            if ($optimized === null) {
                $failed += 1;
                $progressBar->advance();
                continue;
            }

            $currentSystemPath = trim((string) $asset->system_path);
            $currentThumbPath = trim((string) ($asset->thumbnail_path ?? ''));
            $noDataChange = $currentSystemPath === $optimized['system_path']
                && $currentThumbPath === $optimized['thumbnail_path']
                && (string) $asset->mime_type === 'image/webp';

            if ($noDataChange) {
                $skipped += 1;
                $progressBar->advance();
                continue;
            }

            $asset->update([
                'system_path' => $optimized['system_path'],
                'thumbnail_path' => $optimized['thumbnail_path'],
                'mime_type' => $optimized['mime_type'],
                'size' => $optimized['size'],
            ]);
            $processed += 1;
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['Mode', 'Processed', 'Skipped', 'Failed'],
            [[
                $dryRun ? 'dry-run' : 'execute',
                $processed,
                $skipped,
                $failed,
            ]]
        );

        if ($failed > 0) {
            $this->warn("Ada {$failed} data yang gagal diproses. Cek path source dan format file.");
        }

        return self::SUCCESS;
    }

    /**
     * Process source directory files without using assets table.
     */
    private function processDirectoryScan(
        ImageOptimizer $imageOptimizer,
        string $sourceDirectory,
        bool $dryRun,
        bool $overwrite,
        int $limit
    ): int {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $files = collect(File::allFiles($sourceDirectory))
            ->filter(function ($file) use ($allowedExtensions): bool {
                $extension = Str::lower((string) $file->getExtension());

                return in_array($extension, $allowedExtensions, true);
            })
            ->values();

        if ($limit > 0) {
            $files = $files->take($limit)->values();
        }

        if ($files->isEmpty()) {
            $this->info('Tidak ada file gambar yang ditemukan di folder source.');

            return self::SUCCESS;
        }

        $this->info("Total file akan diproses (scan-only): {$files->count()}");

        $processed = 0;
        $failed = 0;
        $progressBar = $this->output->createProgressBar($files->count());
        $progressBar->start();

        foreach ($files as $file) {
            $sourcePath = (string) $file->getRealPath();
            if ($sourcePath === '') {
                $failed += 1;
                $progressBar->advance();
                continue;
            }

            $baseName = $this->sanitizeBaseName((string) $file->getFilenameWithoutExtension());
            if ($baseName === '') {
                $baseName = 'image-'.substr(md5($sourcePath), 0, 8);
            } else {
                $baseName .= '-'.substr(md5($sourcePath), 0, 8);
            }

            if ($dryRun) {
                $processed += 1;
                $progressBar->advance();
                continue;
            }

            $optimized = $imageOptimizer->optimizeFromPath($sourcePath, 'uploads', $baseName, $overwrite);
            if ($optimized === null) {
                $failed += 1;
            } else {
                $processed += 1;
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['Mode', 'Processed', 'Failed'],
            [[
                $dryRun ? 'dry-run' : 'execute',
                $processed,
                $failed,
            ]]
        );

        if ($failed > 0) {
            $this->warn("Ada {$failed} file yang gagal diproses saat scan-only.");
        }

        return self::SUCCESS;
    }

    /**
     * Resolve source image file path from DB fields and source folder.
     */
    private function resolveSourceFile(?string $systemPath, ?string $originalFileName, string $sourceDirectory): ?string
    {
        $candidates = [];

        $normalizedSystemPath = trim((string) $systemPath);
        if ($normalizedSystemPath !== '') {
            $publicCandidate = public_path(ltrim($normalizedSystemPath, '/\\'));
            $candidates[] = $publicCandidate;

            $basename = basename($normalizedSystemPath);
            if ($basename !== '') {
                $candidates[] = rtrim($sourceDirectory, '/\\').DIRECTORY_SEPARATOR.$basename;
            }
        }

        $normalizedOriginalFileName = trim((string) $originalFileName);
        if ($normalizedOriginalFileName !== '') {
            $candidates[] = rtrim($sourceDirectory, '/\\').DIRECTORY_SEPARATOR.$normalizedOriginalFileName;
            $candidates[] = rtrim($sourceDirectory, '/\\').DIRECTORY_SEPARATOR.basename($normalizedOriginalFileName);
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Build deterministic target base name for output files.
     */
    private function buildBaseName(string $assetId, string $systemPath, string $originalFileName): string
    {
        $systemBase = pathinfo($systemPath, PATHINFO_FILENAME);
        $originalBase = pathinfo($originalFileName, PATHINFO_FILENAME);
        $base = $this->sanitizeBaseName($systemBase !== '' ? $systemBase : $originalBase);

        if ($base === '') {
            $base = 'asset';
        }

        return $base.'-'.substr(md5($assetId), 0, 8);
    }

    /**
     * Sanitize filename base.
     */
    private function sanitizeBaseName(string $value): string
    {
        return Str::of($value)
            ->trim()
            ->replace([' ', '_'], '-')
            ->replaceMatches('/[^A-Za-z0-9\-]/', '')
            ->trim('-')
            ->lower()
            ->value();
    }
}
