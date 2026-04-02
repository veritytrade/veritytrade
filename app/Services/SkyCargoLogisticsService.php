<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SkyCargoLogisticsService
{
    protected string $skyCargoBaseUrl;
    protected string $fishBaseUrl;

    public function __construct(?string $baseUrl = null, ?string $fishBaseUrl = null)
    {
        $this->skyCargoBaseUrl = rtrim($baseUrl ?? config('services.skycargo.base_url', 'https://sapi.skycargoltd.com/sky-sys/v1'), '/');
        $this->fishBaseUrl = rtrim($fishBaseUrl ?? config('services.fishlogistics.base_url', 'https://api.fish-logistics.com/api/v1'), '/');
    }

    /**
     * Fetch logistics tracks by provider and tracking number.
     *
     * @return array{tracks: array<int, array{en: string, cn: string, at: string}>, raw: array}|null
     */
    public function fetchTracks(string $provider, string $trackingNumber): ?array
    {
        $provider = strtolower(trim($provider));

        return match ($provider) {
            'skycargo' => $this->fetchSkyCargoTracks($trackingNumber),
            'fish-logistics' => $this->fetchFishLogisticsTracks($trackingNumber),
            default => ['tracks' => [], 'raw' => []],
        };
    }

    /**
     * Fetch Sky Cargo logistics tracks for docNo.
     *
     * @return array{tracks: array<int, array{en: string, cn: string, at: string}>, raw: array}|null
     */
    protected function fetchSkyCargoTracks(string $docNo): ?array
    {
        $docNo = trim($docNo);
        if ($docNo === '') {
            return null;
        }

        try {
            $response = Http::timeout(25)
                ->acceptJson()
                ->get($this->skyCargoBaseUrl.'/waybill/logistics/list', [
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

    /**
     * Fetch Fish Logistics tracks for number.
     *
     * @return array{tracks: array<int, array{en: string, cn: string, at: string}>, raw: array}|null
     */
    protected function fetchFishLogisticsTracks(string $number): ?array
    {
        $number = trim($number);
        if ($number === '') {
            return null;
        }

        try {
            // Try Fish Express first (existing behavior), then Sea as fallback.
            $attempts = [
                ['type' => 'express', 'endpoint' => '/oms/international1'],
                ['type' => 'sea', 'endpoint' => '/oms/internal'],
            ];

            $tracks = [];
            $json = null;
            $usedType = null;

            foreach ($attempts as $attempt) {
                $response = Http::timeout(25)
                    ->acceptJson()
                    ->get($this->fishBaseUrl.$attempt['endpoint'], [
                        'number' => $number,
                    ]);

                if (! $response->successful()) {
                    Log::warning('Fish Logistics API non-success', [
                        'number' => $number,
                        'status' => $response->status(),
                        'endpoint' => $attempt['endpoint'],
                    ]);
                    continue;
                }

                $jsonCandidate = $response->json();
                if (! is_array($jsonCandidate) || (int) ($jsonCandidate['code'] ?? 0) !== 200) {
                    continue;
                }

                $rows = $jsonCandidate['data']['data'] ?? [];
                $parsed = [];
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        if (! is_array($row)) {
                            continue;
                        }
                        $context = trim((string) ($row['context'] ?? ''));
                        if ($context === '') {
                            continue;
                        }

                        $parsed[] = [
                            'en' => preg_replace('/\s+/u', ' ', $context) ?: $context,
                            'cn' => '',
                            'at' => trim((string) ($row['time'] ?? '')),
                        ];
                    }
                }

                // Use first endpoint that returns actual track rows.
                if (! empty($parsed)) {
                    $tracks = $parsed;
                    $json = $jsonCandidate;
                    $usedType = $attempt['type'];
                    break;
                }
            }

            if (empty($tracks)) {
                return null;
            }

            // Fish commonly returns newest first; normalize to chronological for timeline grouping.
            $tracks = array_reverse($tracks);

            return [
                'tracks' => $tracks,
                'raw' => array_merge((array) $json, ['resolved_type' => $usedType]),
            ];
        } catch (\Throwable $e) {
            Log::warning('Fish Logistics API exception', [
                'number' => $number,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
