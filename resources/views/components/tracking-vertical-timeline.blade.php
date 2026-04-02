@props(['order'])

@php
    $stages = \App\Models\TrackingStage::orderByDesc('position')->get();
    $current = $order->effectiveStage();
    $currentPos = $current ? (int) $current->position : 0;
    $shipment = $order->shipment;
    $carrierPayload = $shipment?->carrier_tracks_json;
    $carrierTracks = is_array($carrierPayload) ? ($carrierPayload['tracks'] ?? []) : [];
    $carrierSynced = $shipment?->carrier_tracks_synced_at;

    // Resolve positions by stage name so carrier rows land under the same headings after DB changes (e.g. Processing added).
    $posByName = \App\Models\TrackingStage::query()
        ->pluck('position', 'name')
        ->map(fn ($p) => (int) $p)
        ->all();
    $posSent = (int) ($posByName['Sent to Logistics'] ?? 2);
    $posArrivedLogistics = (int) ($posByName['Arrived Logistics'] ?? 3);
    $posFlying = (int) ($posByName['Flying to Nigeria'] ?? 4);
    $posArrivedNigeria = (int) ($posByName['Arrived Nigeria'] ?? 5);
    $posDelivered = (int) ($posByName['Delivered'] ?? 7);

    $groupedTracks = [];
    foreach ($stages as $stage) {
        $groupedTracks[(int) $stage->position] = [];
    }

    $classifyTrackToStage = static function (string $text) use ($posSent, $posArrivedLogistics, $posFlying, $posArrivedNigeria, $posDelivered): array {
        $t = strtolower(trim($text));

        if ($t === '') {
            return ['stage' => $posSent, 'subsection' => null];
        }

        if (str_contains($t, 'picked up goods') || str_contains($t, 'customer has picked up') || str_contains($t, 'delivered') || str_contains($t, 'signed')) {
            return ['stage' => $posDelivered, 'subsection' => null];
        }

        if (str_contains($t, 'package collected by verity agent') || str_contains($t, 'collected by agent')) {
            return ['stage' => $posArrivedNigeria, 'subsection' => 'Agent pickup'];
        }

        if (str_contains($t, 'lagos') || str_contains($t, 'nigeria') || str_contains($t, 'clearance') || str_contains($t, 'airport express')) {
            if (str_contains($t, 'pickup warehouse') || str_contains($t, 'warehouse')) {
                return ['stage' => $posArrivedNigeria, 'subsection' => 'At logistics warehouse'];
            }

            if (str_contains($t, 'clearance') || str_contains($t, 'clearing') || str_contains($t, 'customs')) {
                return ['stage' => $posArrivedNigeria, 'subsection' => 'Customs clearance'];
            }

            return ['stage' => $posArrivedNigeria, 'subsection' => 'At Nigeria airport'];
        }

        // Keep Guangzhou/HK security and transfer events under Arrived Logistics (not Flying).
        if (
            str_contains($t, 'truck') ||
            str_contains($t, 'guangzhou') ||
            str_contains($t, 'baiyun') ||
            str_contains($t, 'hong kong') ||
            str_contains($t, 'hongkong') ||
            str_contains($t, 'received express goods') ||
            str_contains($t, 'collected') ||
            str_contains($t, 'custom declaration') ||
            str_contains($t, 'inspection') ||
            str_contains($t, 'cannot pass') ||
            str_contains($t, 'security check') ||
            str_contains($t, 'contraband') ||
            str_contains($t, 'returned by the airport') ||
            str_contains($t, 'transferred to hong kong airport') ||
            str_contains($t, 'take-off')
        ) {
            return ['stage' => $posArrivedLogistics, 'subsection' => null];
        }

        if (str_contains($t, 'flying') || str_contains($t, 'addis')) {
            return ['stage' => $posFlying, 'subsection' => null];
        }

        return ['stage' => $posSent, 'subsection' => null];
    };

    foreach ($carrierTracks as $track) {
        $title = trim((string) ($track['en'] ?? $track['cn'] ?? ''));
        if ($title === '') {
            continue;
        }

        $classification = $classifyTrackToStage($title);
        $mappedPos = (int) ($classification['stage'] ?? $posSent);
        if (! array_key_exists($mappedPos, $groupedTracks)) {
            $mappedPos = $posSent;
        }

        $groupedTracks[$mappedPos][] = [
            'title' => $title,
            'at' => trim((string) ($track['at'] ?? '')),
            'subsection' => $track['meta']['subsection'] ?? $classification['subsection'] ?? null,
        ];
    }

    // Prefer carrier `at`; also scan title for embedded datetimes so ordering matches visible dates.
    $extractBestTimestamp = static function (array $item): int {
        $best = 0;
        $at = trim((string) ($item['at'] ?? ''));
        if ($at !== '') {
            $t = strtotime($at);
            if ($t !== false) {
                $best = max($best, $t);
            }
        }
        $title = (string) ($item['title'] ?? '');
        if ($title !== '' && preg_match_all('/\b\d{4}[-/]\d{1,2}[-/]\d{1,2}(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?\b/u', $title, $m)) {
            foreach ($m[0] as $fragment) {
                $t = strtotime(str_replace('/', '-', $fragment));
                if ($t !== false) {
                    $best = max($best, $t);
                }
            }
        }
        if ($title !== '' && preg_match_all('/\b\d{1,2}\/\d{1,2}\/\d{4}(?:[ T]\d{1,2}:\d{2}(?::\d{2})?)?\b/u', $title, $m)) {
            foreach ($m[0] as $fragment) {
                $t = strtotime($fragment);
                if ($t !== false) {
                    $best = max($best, $t);
                }
            }
        }

        return $best;
    };

    $sortByNewestAt = static function (array $a, array $b) use ($extractBestTimestamp): int {
        $ta = $extractBestTimestamp($a);
        $tb = $extractBestTimestamp($b);
        if ($tb !== $ta) {
            return $tb <=> $ta;
        }

        return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
    };

    // Newest date at the top within each stage (and within each Nigeria subsection below).
    foreach ($groupedTracks as $pos => $items) {
        usort($items, $sortByNewestAt);
        $groupedTracks[$pos] = $items;
    }
@endphp

<div class="tracking-vtl mt-4 overflow-hidden rounded-2xl border border-gray-200/80 bg-gradient-to-b from-white to-gray-50/90 shadow-[0_8px_30px_rgba(0,0,0,0.06)]">
    <div class="border-b border-gray-100 bg-white/90 px-4 py-4 sm:px-5">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Shipment timeline</p>
    </div>

    <div class="px-3 py-5 sm:px-5">
        <div class="relative pl-1">
            <div class="pointer-events-none absolute left-[16px] top-4 bottom-4 w-[3px] rounded-full bg-gray-300/90" aria-hidden="true"></div>

            <ul class="space-y-0">
                @foreach($stages as $idx => $stage)
                    @php
                        $pos = (int) $stage->position;
                        $done = $currentPos > 0 && $pos < $currentPos; // Bottom-up flow: lower positions are completed.
                        $active = $currentPos > 0 && $pos === $currentPos;
                        $delayMs = $idx * 70;
                        $stageTracks = $groupedTracks[$pos] ?? [];
                        $isLast = $idx === $stages->count() - 1;
                        $connectorColor = $done ? '#22c55e' : ($active ? '#3b82f6' : '#d1d5db');
                    @endphp
                    <li
                        class="relative flex items-start gap-3 mb-7 last:mb-1 tracking-vtl-step"
                        style="animation: trackingFadeSlide 0.5s ease-out {{ $delayMs }}ms both;"
                    >
                        @unless($isLast)
                            <div class="absolute" style="left:17px;top:36px;bottom:-28px;width:3px;border-radius:9999px;background:{{ $connectorColor }};opacity:0.95;" aria-hidden="true"></div>
                        @endunless
                        <div class="relative z-10 mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 transition-all duration-300
                            @if($done) border-green-600 bg-green-600 text-white shadow-[0_0_0_4px_rgba(22,163,74,0.20)]
                            @elseif($active) border-blue-600 bg-white text-blue-700 shadow-[0_0_0_4px_rgba(37,99,235,0.25)]
                            @else border-gray-300 bg-white text-gray-400 @endif">
                            @if($done)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @elseif($active)
                                <span class="tracking-active-pulse inline-flex h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                            @else
                                <span class="text-[11px] font-bold">{{ $pos }}</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1 pt-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold leading-snug {{ $active ? 'text-blue-700' : ($done ? 'text-gray-900' : 'text-gray-500') }}">
                                    {{ $stage->name }}
                                </p>
                                @if($active)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">CURRENT</span>
                                @endif
                            </div>

                            @if(count($stageTracks) > 0)
                                @if($pos === $posArrivedNigeria)
                                    @php
                                        $subsectionOrder = ['At Nigeria airport', 'Customs clearance', 'At logistics warehouse', 'Agent pickup'];
                                        $bySubsection = [];
                                        foreach ($subsectionOrder as $name) {
                                            $bySubsection[$name] = [];
                                        }
                                        foreach ($stageTracks as $item) {
                                            $name = $item['subsection'] ?? 'At Nigeria airport';
                                            if (! array_key_exists($name, $bySubsection)) {
                                                $bySubsection[$name] = [];
                                            }
                                            $bySubsection[$name][] = $item;
                                        }
                                        foreach ($bySubsection as $subName => $subItems) {
                                            usort($bySubsection[$subName], $sortByNewestAt);
                                        }
                                    @endphp
                                    <div class="mt-2 space-y-2.5">
                                        @foreach($bySubsection as $subsection => $items)
                                            @if(count($items) > 0)
                                                @php
                                                    $latestTs = 0;
                                                    foreach ($items as $it) {
                                                        $latestTs = max($latestTs, $extractBestTimestamp($it));
                                                    }
                                                    $latestDisplay = $latestTs > 0 ? date('Y-m-d H:i', $latestTs) : '';
                                                @endphp
                                                <div class="rounded-lg border border-gray-200 bg-white px-3 py-2.5">
                                                    <div class="flex flex-wrap items-baseline justify-between gap-x-2 gap-y-0.5">
                                                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">{{ $subsection }}</p>
                                                        @if($latestDisplay !== '')
                                                            <p class="text-[11px] font-semibold text-gray-900">[{{ $latestDisplay }}]</p>
                                                        @endif
                                                    </div>
                                                    <div class="mt-1.5 space-y-1.5">
                                                        @foreach($items as $item)
                                                            <p class="text-xs leading-snug text-gray-700">
                                                                @if($item['at'] !== '')
                                                                    <span class="text-[11px] font-semibold text-gray-900">[{{ $item['at'] }}]</span>
                                                                @endif
                                                                {{ $item['title'] }}
                                                            </p>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-2 space-y-2">
                                        @foreach($stageTracks as $item)
                                            <div class="rounded-lg border border-gray-200 bg-white px-3 py-2.5">
                                                <p class="text-xs leading-snug text-gray-700">
                                                    @if($item['at'] !== '')
                                                        <span class="text-[11px] font-semibold text-gray-900">[{{ $item['at'] }}]</span>
                                                    @endif
                                                    {{ $item['title'] }}
                                                </p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

<style>
    @keyframes trackingFadeSlide {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes trackingPulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.3); opacity: 0.6; }
        100% { transform: scale(1); opacity: 1; }
    }

    @keyframes trackingLineGrow {
        from { transform: scaleY(0); transform-origin: top; }
        to { transform: scaleY(1); transform-origin: top; }
    }

    .tracking-active-pulse {
        animation: trackingPulse 1.2s ease-in-out infinite;
    }

    .tracking-line-fill {
        animation: trackingLineGrow 0.9s ease-out both;
    }
</style>
