<?php

namespace App\Support;

/**
 * Single sortable unix timestamp per carrier row: prefer API "at", else first datetime in message text.
 * Using max() across all dates in text caused wrong order (newest not at top).
 */
final class CarrierTrackTimestamp
{
    /**
     * @param  array{at?: string, title?: string, en?: string, cn?: string}  $track
     */
    /**
     * @param  mixed  $track  Row from carrier_tracks_json; non-arrays return 0 (avoids TypeError in usort).
     */
    public static function extract(mixed $track): int
    {
        if (! is_array($track)) {
            return 0;
        }

        try {
            return self::doExtract($track);
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Sort callback: newest event first (compare timestamps desc), then by raw `at` string desc, then message text.
     */
    public static function compareTracksNewestFirst(mixed $a, mixed $b): int
    {
        $a = is_array($a) ? $a : [];
        $b = is_array($b) ? $b : [];

        $ta = self::extract($a);
        $tb = self::extract($b);
        if ($tb !== $ta) {
            return $tb <=> $ta;
        }

        $atA = trim((string) ($a['at'] ?? ''));
        $atB = trim((string) ($b['at'] ?? ''));
        if ($atB !== $atA) {
            return strcmp($atB, $atA);
        }

        return strcmp(
            (string) ($a['title'] ?? $a['en'] ?? $a['cn'] ?? ''),
            (string) ($b['title'] ?? $b['en'] ?? $b['cn'] ?? '')
        );
    }

    /**
     * @param  array{at?: string, title?: string, en?: string, cn?: string}  $track
     */
    private static function doExtract(array $track): int
    {
        $at = self::scrubUtf8(trim((string) ($track['at'] ?? '')));
        if (strlen($at) > 500) {
            $at = substr($at, 0, 500);
        }
        if ($at !== '') {
            $fromAt = self::parseAtField($at);
            if ($fromAt > 0) {
                // Carrier-supplied time is authoritative when it parses.
                return $fromAt;
            }
        }

        $title = self::scrubUtf8(trim((string) ($track['title'] ?? $track['en'] ?? $track['cn'] ?? '')));
        if ($title === '') {
            return 0;
        }
        if (strlen($title) > 20000) {
            $title = substr($title, 0, 20000);
        }

        // First leading bracket only — event time is almost always there.
        if (preg_match('/\[\s*([^\]]+)\s*\]/', $title, $bm)) {
            $inner = trim((string) ($bm[1]);
            if ($inner !== '') {
                $parsed = self::parseLooseDatetimeFragment($inner);
                if ($parsed > 0) {
                    return $parsed;
                }
            }
        }

        // First ISO-like date in text (left-to-right), not max of all matches.
        if (preg_match('/\d{4}[-/]\d{1,2}[-/]\d{1,2}(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?/', $title, $m)) {
            $t = strtotime(str_replace('/', '-', $m[0]));
            if ($t !== false && self::isPlausibleUnix($t)) {
                return $t;
            }
        }

        if (preg_match('/\d{1,2}\/\d{1,2}\/\d{4}(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?/', $title, $m)) {
            $t = strtotime($m[0]);
            if ($t !== false && self::isPlausibleUnix($t)) {
                return $t;
            }
        }

        // First "24MAR 16:59:44" style in text.
        if (preg_match('/\b(\d{1,2})\s*([A-Za-z]{3})\s+(\d{1,2}:\d{2}(?::\d{2})?)\b/', $title, $row)) {
            $parsed = self::dayMonthAbbrTimeToTs($row[1], $row[2], $row[3]);
            if ($parsed > 0) {
                return $parsed;
            }
        }

        return 0;
    }

    private static function parseAtField(string $at): int
    {
        $t = strtotime($at);
        if ($t !== false && self::isPlausibleUnix($t)) {
            return $t;
        }

        // ISO 8601 with T — sometimes strtotime is picky.
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $at)) {
            $t = strtotime(str_replace('T', ' ', substr($at, 0, 19)));
            if ($t !== false && self::isPlausibleUnix($t)) {
                return $t;
            }
        }

        return 0;
    }

    private static function isPlausibleUnix(int $t): bool
    {
        // ~2000–2100 — rejects garbage strtotime hits.
        return $t > 946684800 && $t < 4102444800;
    }

    private static function parseLooseDatetimeFragment(string $inner): int
    {
        $t = strtotime($inner);
        if ($t !== false && self::isPlausibleUnix($t)) {
            return $t;
        }

        if (preg_match('/^(\d{1,2})\s*([A-Za-z]{3})\s+(\d{1,2}:\d{2}(?::\d{2})?)$/i', $inner, $m)) {
            return self::dayMonthAbbrTimeToTs($m[1], $m[2], $m[3]);
        }

        return 0;
    }

    private static function dayMonthAbbrTimeToTs(string $day, string $mon, string $time): int
    {
        $monNorm = ucfirst(strtolower($mon));
        $y = (int) date('Y');
        $candidate = strtotime(sprintf('%d %s %d %s', (int) $day, $monNorm, $y, $time));
        if ($candidate === false) {
            return 0;
        }
        if (! self::isPlausibleUnix($candidate)) {
            return 0;
        }
        if ($candidate > time() + 86400 * 45) {
            $candidate2 = strtotime(sprintf('%d %s %d %s', (int) $day, $monNorm, $y - 1, $time));
            if ($candidate2 !== false && self::isPlausibleUnix($candidate2)) {
                return $candidate2;
            }
        }

        return $candidate;
    }

    /**
     * Safe on hosts without mbstring (calling mb_check_encoding without the ext fatals).
     */
    private static function scrubUtf8(string $s): string
    {
        if ($s === '') {
            return '';
        }
        if (function_exists('mb_scrub')) {
            return mb_scrub($s, 'UTF-8');
        }
        if (function_exists('mb_check_encoding') && function_exists('mb_convert_encoding')) {
            if (mb_check_encoding($s, 'UTF-8')) {
                return $s;
            }

            return mb_convert_encoding($s, 'UTF-8', 'UTF-8');
        }
        if (function_exists('iconv')) {
            $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $s);
            if ($clean !== false && $clean !== '') {
                return $clean;
            }
        }

        return $s;
    }
}
