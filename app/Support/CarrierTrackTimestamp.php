<?php

namespace App\Support;

/**
 * Derives a sortable unix timestamp from carrier `at` plus embedded datetimes in the message text.
 * Logistics lines often look like: [2026-04-02 04:01] ... or [24MAR 16:59:44] fish logistics ...
 */
final class CarrierTrackTimestamp
{
    /**
     * @param  array{at?: string, title?: string, en?: string, cn?: string}  $track
     */
    public static function extract(array $track): int
    {
        $best = 0;
        $at = trim((string) ($track['at'] ?? ''));
        if ($at !== '') {
            $t = strtotime($at);
            if ($t !== false) {
                $best = max($best, $t);
            }
        }

        $title = trim((string) ($track['title'] ?? $track['en'] ?? $track['cn'] ?? ''));
        if ($title === '') {
            return $best;
        }

        // Bracketed timestamps: [2026-04-02 04:01], [24MAR 16:59:44], etc.
        if (preg_match_all('/\[\s*([^\]]+)\s*\]/', $title, $bm)) {
            foreach ($bm[1] as $inner) {
                $inner = trim((string) $inner);
                if ($inner === '') {
                    continue;
                }
                $parsed = self::parseLooseDatetimeFragment($inner);
                if ($parsed > 0) {
                    $best = max($best, $parsed);
                }
            }
        }

        // Unbracketed ISO-style
        if (preg_match_all('/\d{4}[-/]\d{1,2}[-/]\d{1,2}(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?/', $title, $m)) {
            foreach ($m[0] as $fragment) {
                $t = strtotime(str_replace('/', '-', $fragment));
                if ($t !== false) {
                    $best = max($best, $t);
                }
            }
        }

        if (preg_match_all('/\d{1,2}\/\d{1,2}\/\d{4}(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?/', $title, $m)) {
            foreach ($m[0] as $fragment) {
                $t = strtotime($fragment);
                if ($t !== false) {
                    $best = max($best, $t);
                }
            }
        }

        // Unbracketed "24MAR 16:59:44" (no brackets)
        if (preg_match_all('/\b(\d{1,2})\s*([A-Za-z]{3})\s+(\d{1,2}:\d{2}(?::\d{2})?)\b/', $title, $rows, PREG_SET_ORDER)) {
            foreach ($rows as $row) {
                $parsed = self::dayMonthAbbrTimeToTs($row[1], $row[2], $row[3]);
                if ($parsed > 0) {
                    $best = max($best, $parsed);
                }
            }
        }

        return $best;
    }

    private static function parseLooseDatetimeFragment(string $inner): int
    {
        $t = strtotime($inner);
        if ($t !== false) {
            return $t;
        }

        // e.g. 24MAR 16:59:44 (no space between day and month)
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
        // Year omitted: if result looks far in the future, use previous year (Dec/Jan boundary).
        if ($candidate > time() + 86400 * 45) {
            $candidate2 = strtotime(sprintf('%d %s %d %s', (int) $day, $monNorm, $y - 1, $time));
            if ($candidate2 !== false) {
                return $candidate2;
            }
        }

        return $candidate;
    }
}
