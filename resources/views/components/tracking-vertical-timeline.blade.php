@props(['order'])

@php
    $stages = \App\Models\TrackingStage::orderBy('position')->get();
    $current = $order->effectiveStage();
    $currentPos = $current ? (int) $current->position : 0;
    $maxPos = max(1, (int) $stages->max('position'));
    $shipment = $order->shipment;
    $carrierPayload = $shipment?->carrier_tracks_json;
    $carrierTracks = is_array($carrierPayload) ? ($carrierPayload['tracks'] ?? []) : [];
    $carrierSynced = $shipment?->carrier_tracks_synced_at;
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
            <div class="absolute left-[15px] top-2 bottom-2 w-0.5 bg-gradient-to-b from-emerald-400 via-emerald-300 to-gray-200" aria-hidden="true"></div>

            <ul class="space-y-0">
                @foreach($stages as $idx => $stage)
                    @php
                        $pos = (int) $stage->position;
                        $done = $currentPos > 0 && $pos < $currentPos;
                        $active = $currentPos > 0 && $pos === $currentPos;
                        $pending = $currentPos === 0 || $pos > $currentPos;
                        $delayMs = $idx * 55;
                    @endphp
                    <li
                        class="relative flex gap-3 pb-6 last:pb-1 tracking-vtl-step"
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
                            @if($stage->description && $active)
                                <p class="mt-0.5 text-xs text-emerald-800/90">{{ $stage->description }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @if(count($carrierTracks) > 0)
            <div class="mt-2 border-t border-gray-100 pt-4">
                <p class="mb-3 text-[11px] font-semibold uppercase tracking-wider text-gray-500">Carrier updates</p>
                <ol class="space-y-3">
                    @foreach($carrierTracks as $tidx => $row)
                        <li
                            class="rounded-xl border border-gray-100 bg-white/80 px-3 py-2.5 shadow-sm"
                            style="animation: trackingVtlIn 0.45s ease-out {{ ($stages->count() * 55) + ($tidx * 40) }}ms both;"
                        >
                            <p class="text-sm font-medium leading-snug text-gray-900">{{ $row['en'] ?: $row['cn'] }}</p>
                            @if(!empty($row['at']))
                                <p class="mt-1 text-xs text-gray-500">{{ $row['at'] }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
                @if($carrierSynced)
                    <p class="mt-3 text-center text-[10px] text-gray-400">Logistics data last synced {{ $carrierSynced->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</p>
                @endif
            </div>
        @endif
    </div>

    <div class="h-1.5 w-full overflow-hidden rounded-b-2xl bg-gray-100">
        <div
            class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-400 transition-all duration-700 ease-out"
            style="width: {{ $currentPos ? round(min(100, ($currentPos / $maxPos) * 100)) : 0 }}%;"
        ></div>
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
