<?php

namespace App\Filesystem;

use App\Support\ExtensionOnlyMimeTypeDetector;
use Illuminate\Filesystem\FilesystemManager as BaseFilesystemManager;
use League\Flysystem\Local\LocalFilesystemAdapter as LocalAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;

/**
 * Extends Laravel's FilesystemManager to use extension-only MIME detection
 * when the PHP fileinfo extension is not available (avoids "Class finfo not found").
 */
class FilesystemManager extends BaseFilesystemManager
{
    /**
     * Create an instance of the local driver with a fallback MIME detector
     * that does not require the fileinfo extension.
     */
    public function createLocalDriver(array $config, string $name = 'local')
    {
        $visibility = PortableVisibilityConverter::fromArray(
            $config['permissions'] ?? [],
            $config['directory_visibility'] ?? $config['visibility'] ?? Visibility::PRIVATE
        );

        $links = ($config['links'] ?? null) === 'skip'
            ? LocalAdapter::SKIP_LINKS
            : LocalAdapter::DISALLOW_LINKS;

        $mimeDetector = new ExtensionOnlyMimeTypeDetector();

        $adapter = new LocalAdapter(
            $config['root'],
            $visibility,
            $config['lock'] ?? LOCK_EX,
            $links,
            $mimeDetector
        );

        return (new \Illuminate\Filesystem\FilesystemAdapter(
            $this->createFlysystem($adapter, $config),
            $adapter,
            $config
        ))->diskName($name)->shouldServeSignedUrls(
            $config['serve'] ?? false,
            fn () => $this->app['url'],
        );
    }
}
