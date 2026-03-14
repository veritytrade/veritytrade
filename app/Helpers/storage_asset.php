<?php

if (!function_exists('storage_asset')) {
    /**
     * URL for a file in storage/app/public (works without symlink when using the /_f/ route).
     * Use this instead of asset('storage/'.$path) so images work on hosts where public_html has no storage symlink.
     */
    function storage_asset(?string $path): string
    {
        if ($path === null || trim($path) === '') {
            return '';
        }
        $path = ltrim(str_replace(['\\', '..'], ['/', ''], $path), '/');
        return url('_f/' . $path);
    }
}
