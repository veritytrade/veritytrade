@props(['order'])

@php
    $stages = \App\Models\TrackingStage::orderBy('position')->get();
    $current = $order->effectiveStage();
    $currentPos = $current ? (int) $current->position : 0;
@endphp

<div class="tracking-progress mt-3">
    <div class="flex justify-between items-start gap-1 relative">
        @foreach($stages as $idx => $stage)
            @php
                $pos = (int) $stage->position;
                $done = $pos < $currentPos || ($pos == $currentPos && $pos == 6);
                $active = $pos == $currentPos;
            @endphp
            <div class="flex flex-col items-center flex-1 min-w-0 z-10">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 ease-in-out
                    @if($done) bg-green-500 text-white
                    @elseif($active) bg-green-600 text-white ring-2 ring-green-400 ring-offset-2
                    @else bg-gray-200 text-gray-500 @endif">
                    {{ $pos }}
                </div>
                <span class="mt-1.5 text-[10px] sm:text-xs text-center leading-tight whitespace-nowrap overflow-hidden text-ellipsis max-w-full px-0.5
                    @if($active) font-semibold text-green-700
                    @elseif($done) text-green-600
                    @else text-gray-400 @endif" title="{{ $stage->name }}">
                    {{ $stage->short_name ?? $stage->name }}
                </span>
            </div>
        @endforeach
    </div>
    <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
        <div class="h-full bg-green-500 rounded-full transition-all duration-300 ease-in-out" style="width: {{ $currentPos ? round(($currentPos / 6) * 100) : 0 }}%"></div>
    </div>
</div>
