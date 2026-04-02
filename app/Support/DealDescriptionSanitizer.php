<?php

namespace App\Support;

/**
 * Same text normalization as admin Hot Deal create/update forms.
 */
final class DealDescriptionSanitizer
{
    public static function clean(string $text): string
    {
        $cleanDesc = preg_replace('/^Model\s*[:\-].*$/im', '', $text);
        $cleanDesc = preg_replace('/^(?:Price|Cost|Amount)\s*[:\-].*$/im', '', $cleanDesc);
        $cleanDesc = preg_replace('/^[^\S\n]*[📱💰⚠️✅]*\s*(?:Price|Cost|Amount)\s*[:\-].*$/imu', '', $cleanDesc);
        $cleanDesc = preg_replace('/^\s+|\s+$/m', '', $cleanDesc);
        $cleanDesc = preg_replace('/\n{2,}/', "\n", $cleanDesc);

        return trim($cleanDesc);
    }
}
