<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class ImageOptimizer
{
    /**
     * Store image and optional thumbnail with optimization.
     *
     * @return array{system_path: string, thumbnail_path: ?string, mime_type: string, size: int, original_file_name: string}
     */
    public function store(UploadedFile $file, string $directory = 'uploads'): array
    {
        $normalizedDirectory = trim($directory, '/');
        $baseName = now()->timestamp.'-'.Str::uuid();
        $originalFileName = (string) $file->getClientOriginalName();

        $realPath = (string) $file->getRealPath();
        $detectedMime = (string) ($file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream');

        $optimized = $this->optimizeFromPath($realPath, $normalizedDirectory, $baseName);
        if ($optimized !== null) {
            return [
                ...$optimized,
                'original_file_name' => $originalFileName !== '' ? $originalFileName : basename($optimized['system_path']),
            ];
        }

        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: 'jpg'));
        $fallbackExtension = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) ? $extension : 'jpg';
        $filename = $baseName.'.'.$fallbackExtension;
        $baseDirectory = public_path($normalizedDirectory);
        if (! is_dir($baseDirectory)) {
            mkdir($baseDirectory, 0755, true);
        }
        $file->move($baseDirectory, $filename);

        $absoluteFile = $baseDirectory.DIRECTORY_SEPARATOR.$filename;
        $size = (int) (@filesize($absoluteFile) ?: 0);

        return [
            'system_path' => '/'.$normalizedDirectory.'/'.$filename,
            'thumbnail_path' => null,
            'mime_type' => $detectedMime,
            'size' => $size,
            'original_file_name' => $originalFileName !== '' ? $originalFileName : $filename,
        ];
    }

    /**
     * Optimize a legacy image file into main + thumbnail webp files.
     *
     * @return array{system_path: string, thumbnail_path: string, mime_type: string, size: int}|null
     */
    public function optimizeFromPath(string $sourcePath, string $directory, string $baseName, bool $overwrite = true): ?array
    {
        if (! is_file($sourcePath) || ! function_exists('imagewebp')) {
            return null;
        }

        $normalizedDirectory = trim($directory, '/');
        $sanitizedBaseName = trim($baseName);
        if ($sanitizedBaseName === '') {
            return null;
        }

        $baseDirectory = public_path($normalizedDirectory);
        $thumbDirectory = $baseDirectory.DIRECTORY_SEPARATOR.'thumbs';

        if (! is_dir($baseDirectory)) {
            mkdir($baseDirectory, 0755, true);
        }

        if (! is_dir($thumbDirectory)) {
            mkdir($thumbDirectory, 0755, true);
        }

        $filename = $sanitizedBaseName.'.webp';
        $mainAbsolute = $baseDirectory.DIRECTORY_SEPARATOR.$filename;
        $thumbAbsolute = $thumbDirectory.DIRECTORY_SEPARATOR.$filename;

        if (! $overwrite && is_file($mainAbsolute) && is_file($thumbAbsolute)) {
            return [
                'system_path' => '/'.$normalizedDirectory.'/'.$filename,
                'thumbnail_path' => '/'.$normalizedDirectory.'/thumbs/'.$filename,
                'mime_type' => 'image/webp',
                'size' => (int) (@filesize($mainAbsolute) ?: 0),
            ];
        }

        $detectedMime = (string) (mime_content_type($sourcePath) ?: 'application/octet-stream');
        $resource = $this->createImageResource($sourcePath, $detectedMime);

        if ($resource === null || $resource === false) {
            return null;
        }

        $mainCanvas = $this->resizeToBounds($resource, 1600, 1600);
        $thumbCanvas = $this->resizeToBounds($resource, 460, 460);

        $mainSaved = @imagewebp($mainCanvas, $mainAbsolute, 82);
        $thumbSaved = @imagewebp($thumbCanvas, $thumbAbsolute, 78);

        imagedestroy($resource);
        imagedestroy($mainCanvas);
        imagedestroy($thumbCanvas);

        if (! $mainSaved || ! $thumbSaved) {
            return null;
        }

        return [
            'system_path' => '/'.$normalizedDirectory.'/'.$filename,
            'thumbnail_path' => '/'.$normalizedDirectory.'/thumbs/'.$filename,
            'mime_type' => 'image/webp',
            'size' => (int) (@filesize($mainAbsolute) ?: 0),
        ];
    }

    /**
     * Create a GD image resource from uploaded image.
     */
    private function createImageResource(string $realPath, string $mimeType): mixed
    {
        if (! is_file($realPath)) {
            return null;
        }

        $resource = match (strtolower($mimeType)) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($realPath),
            'image/png' => @imagecreatefrompng($realPath),
            'image/gif' => @imagecreatefromgif($realPath),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($realPath) : null,
            default => null,
        };

        if ($resource !== null) {
            return $resource;
        }

        $binary = @file_get_contents($realPath);
        if ($binary === false) {
            return null;
        }

        $fromString = @imagecreatefromstring($binary);

        return $fromString !== false ? $fromString : null;
    }

    /**
     * Resize while preserving ratio inside a max width/height box.
     */
    private function resizeToBounds(mixed $source, int $maxWidth, int $maxHeight): mixed
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $ratio = min($maxWidth / max($sourceWidth, 1), $maxHeight / max($sourceHeight, 1), 1);
        $newWidth = (int) max(1, floor($sourceWidth * $ratio));
        $newHeight = (int) max(1, floor($sourceHeight * $ratio));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $newWidth, $newHeight, $transparent);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

        return $canvas;
    }
}
