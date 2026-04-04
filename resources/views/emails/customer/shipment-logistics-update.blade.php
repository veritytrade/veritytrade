@php
    $brandLegal = (string) config('app.brand_legal_name', 'Verity Trade Global Limited');
@endphp
@if($kind === 'stage')
Your shipment has moved to a new stage
@else
Logistics update on your shipment
@endif

Hi {{ $recipientName }},

@if($kind === 'stage')
Current status:
{{ $newStageName }}

@if(! empty($previousStageName))
Previously: {{ $previousStageName }}

@endif
@else
Latest update:
{{ $latestLine }}

@endif
@if(! empty($orderSummary))
Order · {{ $orderSummary }}

@endif
View tracking in dashboard
{{ $trackingUrl }}

—

{{ $brandLegal }}
