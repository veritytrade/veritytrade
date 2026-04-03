@props(['order'])

@if(! isset($order) || ! $order)
    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Tracking is unavailable (no order linked).
    </div>
@else
@php
    $stages = \App\Models\TrackingStage::orderByDesc('position')->get();
    $currentPos = 0;
    $carrierSynced = null;

    if ($order instanceof \App\Models\Order) {
        try {
            $current = $order->effectiveStage();
            $currentPos = $current ? (int) $current->position : 0;
        } catch (\Throwable) {
            $currentPos = 0;
        }
        $shipment = $order->shipment;
        $carrierSynced = $shipment?->carrier_tracks_synced_at;
    } else {
        $shipment = null;
    }

    $carrierPayload = $shipment?->carrier_tracks_json;
    $carrierTracksRaw = is_array($carrierPayload) ? ($carrierPayload['tracks'] ?? []) : [];
    $carrierTracks = array_values(array_filter(
        is_array($carrierTracksRaw) ? $carrierTracksRaw : [],
        static fn ($row) => is_array($row)
    ));

    // Resolve positions by stage name so carrier rows land under the same headings after DB changes (e.g. Processing added).
    $posByName = \App\Models\TrackingStage::query()
        ->pluck('position', 'name')
        ->map(fn ($p) => (int) $p)
        ->all();
    $posArrivedNigeria = (int) ($posByName['Arrived Nigeria'] ?? 5);

    $groupedTracks = [];
    foreach ($stages as $stage) {
        $groupedTracks[(int) $stage->position] = [];
    }

    // Closure (not [Class, 'method']) avoids rare Blade/opcache issues with callables in @php.
    $sortByNewestAt = static function (mixed $a, mixed $b): int {
        return \App\Support\CarrierTrackTimestamp::compareTracksNewestFirst($a, $b);
    };

    if ($order instanceof \App\Models\Order) {
        foreach ($carrierTracks as $track) {
            if (! is_array($track)) {
                continue;
            }
            try {
                $title = trim((string) ($track['en'] ?? $track['cn'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $meta = is_array($track['meta'] ?? null) ? $track['meta'] : null;
                $classification = \App\Support\CarrierTrackClassifier::classify(
                    trim((string) ($track['en'] ?? '')),
                    trim((string) ($track['cn'] ?? '')),
                    $meta,
                    $posByName
                );
                if (($classification['ignore'] ?? false) === true) {
                    continue;
                }

                $defaultSent = (int) ($posByName['Sent to Logistics'] ?? 2);
                $mappedPos = (int) ($classification['stage'] ?? $defaultSent);
                if (! array_key_exists($mappedPos, $groupedTracks)) {
                    $mappedPos = $defaultSent;
                }

                $metaSubsection = (is_array($meta) && array_key_exists('subsection', $meta)) ? $meta['subsection'] : null;

                $rowForSort = [
                    'at' => trim((string) ($track['at'] ?? '')),
                    'title' => $title,
                    'en' => $track['en'] ?? '',
                    'cn' => $track['cn'] ?? '',
                ];

                $groupedTracks[$mappedPos][] = [
                    'title' => $title,
                    'at' => $rowForSort['at'],
                    'at_display' => \App\Support\CarrierTrackTimestamp::formatDisplayForUi($rowForSort),
                    'subsection' => $metaSubsection ?? $classification['subsection'] ?? null,
                ];
            } catch (\Throwable) {
                continue;
            }
        }
    }

    // Newest date at the top within each stage (and within each Nigeria subsection below).
    foreach ($groupedTracks as $pos => $items) {
        usort($items, $sortByNewestAt);
        $groupedTracks[$pos] = $items;
    }

    // Build Nigeria subsections here so the template does not rely on nested @php (closures are not in scope there → 500).
    $nigeriaSubsectionBlocks = [];
    $nigeriaTracks = $groupedTracks[$posArrivedNigeria] ?? [];
    if (count($nigeriaTracks) > 0) {
        $subsectionOrder = ['At Nigeria airport', 'Customs clearance', 'At logistics warehouse', 'Agent pickup'];
        $bySubsection = [];
        foreach ($subsectionOrder as $name) {
            $bySubsection[$name] = [];
        }
        foreach ($nigeriaTracks as $item) {
            $name = $item['subsection'] ?? 'At Nigeria airport';
            if (! array_key_exists($name, $bySubsection)) {
                $bySubsection[$name] = [];
            }
            $bySubsection[$name][] = $item;
        }
        foreach ($bySubsection as $subName => $subItems) {
            usort($bySubsection[$subName], $sortByNewestAt);
        }
        foreach ($bySubsection as $subsection => $items) {
            if (count($items) === 0) {
                continue;
            }
            $latestTs = 0;
            foreach ($items as $it) {
                $latestTs = max($latestTs, \App\Support\CarrierTrackTimestamp::extract($it));
            }
            $nigeriaSubsectionBlocks[] = [
                'subsection' => $subsection,
                'items' => $items,
                'latestDisplay' => $latestTs > 0 ? date('Y-m-d H:i', $latestTs) : '',
            ];
        }
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
                                    <div class="mt-2 space-y-2.5">
                                        @foreach($nigeriaSubsectionBlocks as $block)
                                            <div class="rounded-lg border border-gray-200 bg-white px-3 py-2.5">
                                                <div class="flex flex-wrap items-baseline justify-between gap-x-2 gap-y-0.5">
                                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">{{ $block['subsection'] }}</p>
                                                    @if(($block['latestDisplay'] ?? '') !== '')
                                                        <p class="text-[11px] font-semibold text-gray-900">[{{ $block['latestDisplay'] }}]</p>
                                                    @endif
                                                </div>
                                                <div class="mt-1.5 space-y-1.5">
                                                    @foreach($block['items'] as $item)
                                                        @if(is_array($item))
                                                            <p class="text-xs leading-snug text-gray-700">
                                                                @if(($item['at_display'] ?? $item['at'] ?? '') !== '')
                                                                    <span class="text-[11px] font-semibold text-gray-900">[{{ $item['at_display'] ?? $item['at'] }}]</span>
                                                                @endif
                                                                {{ $item['title'] ?? '' }}
                                                            </p>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="mt-2 space-y-2">
                                        @foreach($stageTracks as $item)
                                            @if(is_array($item))
                                                <div class="rounded-lg border border-gray-200 bg-white px-3 py-2.5">
                                                    <p class="text-xs leading-snug text-gray-700">
                                                        @if(($item['at_display'] ?? $item['at'] ?? '') !== '')
                                                            <span class="text-[11px] font-semibold text-gray-900">[{{ $item['at_display'] ?? $item['at'] }}]</span>
                                                        @endif
                                                        {{ $item['title'] ?? '' }}
                                                    </p>
                                                </div>
                                            @endif
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
@endif
