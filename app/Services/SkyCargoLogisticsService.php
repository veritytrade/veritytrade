<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SkyCargoLogisticsService
{
    protected string $baseUrl;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? config('services.skycargo.base_url', 'https://sapi.skycargoltd.com/sky-sys/v1'), '/');
    }

    /**
     * Fetch logistics tracks for a doc number (same as customer-facing tracking code).
     *
     * @return array{tracks: array<int, array{en: string, cn: string, at: string}>, raw: array}|null
     */
    public function fetchTracksByDocNo(string $docNo): ?array
    {
        $docNo = trim($docNo);
        if ($docNo === '') {
            return null;
        }

        try {
            $response = Http::timeout(25)
                ->acceptJson()
                ->get($this->baseUrl.'/waybill/logistics/list', [
                    'docNo' => $docNo,
                ]);

            if (! $response->successful()) {
                Log::warning('SkyCargo API non-success', [
                    'docNo' => $docNo,
                    'status' => $response->status(),
                ]);

                return null;
            }

            $json = $response->json();
            if (! is_array($json) || (int) ($json['code'] ?? 0) !== 200) {
                return null;
            }

            $data = $json['data'] ?? [];
            $tracks = [];
            if (isset($data[0]['waybillItems'][0]['logisticsTracks']) && is_array($data[0]['waybillItems'][0]['logisticsTracks'])) {
                $rawTracks = $data[0]['waybillItems'][0]['logisticsTracks'];
                foreach ($rawTracks as $t) {
                    if (! is_array($t)) {
                        continue;
                    }
                    $tracks[] = [
                        'en' => trim((string) ($t['nameEn'] ?? '')),
                        'cn' => trim((string) ($t['nameCn'] ?? '')),
                        'at' => trim((string) ($t['createdTimeStr'] ?? '')),
                    ];
                }
            }

            // API returns newest first; display chronological (oldest first) in vertical timeline
            $tracks = array_reverse($tracks);

            return [
                'tracks' => $tracks,
                'raw' => $json,
            ];
        } catch (\Throwable $e) {
            Log::warning('SkyCargo API exception', [
                'docNo' => $docNo,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
