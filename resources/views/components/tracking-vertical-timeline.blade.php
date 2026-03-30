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

        if (str_contains($t, 'flying') || str_contains($t, 'addis') || str_contains($t, 'hong kong international airport') || str_contains($t, 'take-off')) {
            return ['stage' => 4, 'subsection' => null]; // Flying to Nigeria
        }

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
            str_contains($t, 'transferred to hong kong airport')
        ) {
            return ['stage' => 3, 'subsection' => null]; // Arrived Logistics
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

    <div class="px-2 py-4 sm:px-4">
        <div class="relative pl-2">
            <div class="absolute left-[15px] top-2 bottom-2 w-0.5 bg-gradient-to-b from-emerald-300 via-emerald-300 to-emerald-100" aria-hidden="true"></div>

            <ul class="space-y-0">
                @foreach($stages as $idx => $stage)
                    @php
                        $pos = (int) $stage->position;
                        $done = $currentPos > 0 && $pos < $currentPos; // Bottom-up flow: lower positions are completed.
                        $active = $currentPos > 0 && $pos === $currentPos;
                        $delayMs = $idx * 55;
                        $stageTracks = $groupedTracks[$pos] ?? [];
                    @endphp
                    <li
                        class="relative flex gap-3 pb-6 last:pb-2 tracking-vtl-step"
                        style="animation: trackingVtlIn 0.5s ease-out {{ $delayMs }}ms both;"
                    >
                        <div class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 transition-all duration-300
                            @if($done) border-emerald-500 bg-emerald-500 text-white shadow-[0_0_0_4px_rgba(16,185,129,0.2)]
                            @elseif($active) border-emerald-600 bg-white text-emerald-700 shadow-[0_0_0_4px_rgba(16,185,129,0.35)] ring-2 ring-emerald-400/50
                            @else border-gray-200 bg-white text-gray-400 @endif">
                            @if($done)
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <span class="text-[11px] font-bold">{{ $pos }}</span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1 pt-0.5">
                            <p class="text-sm font-semibold leading-snug text-gray-900">
                                {{ $stage->name }}
                            </p>
                            @if($stage->description)
                                <p class="mt-0.5 text-xs {{ $active ? 'text-emerald-800/90' : 'text-gray-600' }}">{{ $stage->description }}</p>
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
                                                <div class="rounded-lg border border-gray-200 bg-white px-2.5 py-2">
                                                    <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-600">{{ $subsection }}</p>
                                                    <div class="mt-1.5 space-y-1.5">
                                                        @foreach($items as $item)
                                                            <p class="text-xs leading-snug text-gray-700">
                                                                @if($item['at'] !== '')
                                                                    <span class="font-semibold text-gray-900">[{{ $item['at'] }}]</span>
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
                                            <div class="rounded-lg border border-gray-200 bg-white px-2.5 py-2">
                                                <p class="text-xs leading-snug text-gray-700">
                                                    @if($item['at'] !== '')
                                                        <span class="font-semibold text-gray-900">[{{ $item['at'] }}]</span>
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
    @keyframes trackingVtlIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
