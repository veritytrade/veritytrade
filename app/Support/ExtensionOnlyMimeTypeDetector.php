<?php

declare(strict_types=1);

namespace App\Support;

use League\MimeTypeDetection\GeneratedExtensionToMimeTypeMap;
use League\MimeTypeDetection\MimeTypeDetector;

/**
 * MIME type detector using file extension only (no PHP finfo).
 * Use when the server does not have the fileinfo extension enabled.
 */
class ExtensionOnlyMimeTypeDetector implements MimeTypeDetector
{
    private GeneratedExtensionToMimeTypeMap $map;

    public function __construct(?GeneratedExtensionToMimeTypeMap $map = null)
    {
        $this->map = $map ?? new GeneratedExtensionToMimeTypeMap();
    }

    public function detectMimeType(string $path, $contents): ?string
    {
        return $this->detectMimeTypeFromPath($path);
    }

    public function detectMimeTypeFromBuffer(string $contents): ?string
    {
        return null;
    }

    public function detectMimeTypeFromPath(string $path): ?string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($extension === '') {
            return null;
        }

        return $this->map->lookupMimeType($extension);
    }

    public function detectMimeTypeFromFile(string $path): ?string
    {
        return $this->detectMimeTypeFromPath($path);
    }
}
