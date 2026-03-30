@props(['order'])

@php
    $stages = \App\Models\TrackingStage::orderByDesc('position')->get();
    $current = $order->effectiveStage();
    $currentPos = $current ? (int) $current->position : 0;
    $shipment = $order->shipment;
    $carrierPayload = $shipment?->carrier_tracks_json;
    $carrierTracks = is_array($carrierPayload) ? ($carrierPayload['tracks'] ?? []) : [];
    $carrierSynced = $shipment?->carrier_tracks_synced_at;

    $groupedTracks = [];
    foreach ($stages as $stage) {
        $groupedTracks[(int) $stage->position] = [];
    }

    $classifyTrackToStage = static function (string $text): array {
        $t = strtolower(trim($text));

        if ($t === '') {
            return ['stage' => 2, 'subsection' => null]; // Sent to Logistics fallback
        }

        if (str_contains($t, 'picked up goods') || str_contains($t, 'customer has picked up') || str_contains($t, 'delivered') || str_contains($t, 'signed')) {
            return ['stage' => 7, 'subsection' => null]; // Delivered
        }

        if (str_contains($t, 'package collected by verity agent') || str_contains($t, 'collected by agent')) {
            return ['stage' => 5, 'subsection' => 'Agent pickup'];
        }

        if (str_contains($t, 'lagos') || str_contains($t, 'nigeria') || str_contains($t, 'clearance') || str_contains($t, 'airport express')) {
            if (str_contains($t, 'pickup warehouse') || str_contains($t, 'warehouse')) {
                return ['stage' => 5, 'subsection' => 'At logistics warehouse'];
            }

            if (str_contains($t, 'clearance') || str_contains($t, 'clearing') || str_contains($t, 'customs')) {
                return ['stage' => 5, 'subsection' => 'Customs clearance'];
            }

            return ['stage' => 5, 'subsection' => 'At Nigeria airport'];
        }

        // Keep Guangzhou/HK security and transfer events under Arrived Logistics (not Flying).
        if (
            str_contains($t, 'truck') ||
            str_contains($t, 'guangzhou') ||
            str_contains($t, 'baiyun') ||
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
            return ['stage' => 3, 'subsection' => null]; // Arrived Logistics
        }

        if (str_contains($t, 'flying') || str_contains($t, 'addis') || str_contains($t, 'hong kong international airport')) {
            return ['stage' => 4, 'subsection' => null]; // Flying to Nigeria
        }

        return ['stage' => 2, 'subsection' => null]; // Sent to Logistics
    };

    foreach ($carrierTracks as $track) {
        $title = trim((string) ($track['en'] ?? $track['cn'] ?? ''));
        if ($title === '') {
            continue;
        }

        $classification = $classifyTrackToStage($title);
        $mappedPos = (int) ($classification['stage'] ?? 2);
        if (! array_key_exists($mappedPos, $groupedTracks)) {
            $mappedPos = 2;
        }

        $groupedTracks[$mappedPos][] = [
            'title' => $title,
            'at' => trim((string) ($track['at'] ?? '')),
            'subsection' => $track['meta']['subsection'] ?? $classification['subsection'] ?? null,
        ];
    }
@endphp

<div class="tracking-vtl mt-4 overflow-hidden rounded-2xl border border-gray-200/80 bg-gradient-to-b from-white to-gray-50/90 shadow-[0_8px_30px_rgba(0,0,0,0.06)]">
    <div class="border-b border-gray-100 bg-white/90 px-4 py-4 sm:px-5">
        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Shipment status</p>
        <p class="mt-1 text-lg font-bold tracking-tight text-gray-900 sm:text-xl">
            {{ $current?->name ?? 'Pending' }}
        </p>
        @if($current?->description)
            <p class="mt-1 text-sm text-gray-600">{{ $current->description }}</p>
        @endif
    </div>

    <div class="px-3 py-5 sm:px-5">
        <div class="relative pl-1">
            <div class="absolute left-[17px] top-2 bottom-2 w-[2px] bg-gray-200 rounded-full overflow-hidden" aria-hidden="true">
                <div class="w-full h-full bg-gradient-to-b from-emerald-500 via-blue-500 to-gray-300 tracking-line-fill"></div>
            </div>

            <ul class="space-y-0">
                @foreach($stages as $idx => $stage)
                    @php
                        $pos = (int) $stage->position;
                        $done = $currentPos > 0 && $pos < $currentPos; // Bottom-up flow: lower positions are completed.
                        $active = $currentPos > 0 && $pos === $currentPos;
                        $delayMs = $idx * 70;
                        $stageTracks = $groupedTracks[$pos] ?? [];
                    @endphp
                    <li
                        class="relative flex gap-3 mb-6 last:mb-1 tracking-vtl-step"
                        style="animation: trackingFadeSlide 0.5s ease-out {{ $delayMs }}ms both;"
                    >
                        <div class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 transition-all duration-300
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
                        <div class="min-w-0 flex-1 pt-0.5">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold leading-snug {{ $active ? 'text-blue-700' : ($done ? 'text-gray-900' : 'text-gray-500') }}">
                                    {{ $stage->name }}
                                </p>
                                @if($active)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">CURRENT</span>
                                @endif
                            </div>
                            <p class="text-xs mt-0.5 {{ $active ? 'text-blue-600' : ($done ? 'text-gray-600' : 'text-gray-400') }}">
                                {{ $stage->name }}
                            </p>
                            @if($stage->description)
                                <p class="mt-0.5 text-xs {{ $active ? 'text-blue-700' : ($done ? 'text-gray-600' : 'text-gray-400') }}">{{ $stage->description }}</p>
                            @endif

                            @if(count($stageTracks) > 0)
                                @if($pos === 5)
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
                                    @endphp
                                    <div class="mt-2 space-y-2.5">
                                        @foreach($bySubsection as $subsection => $items)
                                            @if(count($items) > 0)
                                                <div class="rounded-lg border border-gray-200 bg-white px-3 py-2.5">
                                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">{{ $subsection }}</p>
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
                            @elseif($active)
                                <p class="mt-2 text-[11px] text-gray-500">Waiting for carrier details for this stage.</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        @if($carrierSynced)
            <p class="mt-3 text-center text-[10px] text-gray-400">Logistics data last synced {{ $carrierSynced->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
        @elseif(count($carrierTracks) === 0)
            <p class="mt-3 text-center text-[10px] text-gray-400">No carrier updates yet. Timeline still works from main shipment stages.</p>
        @endif
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
