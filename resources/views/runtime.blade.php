@php
    $payloadJson = json_encode($payload, JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_THROW_ON_ERROR);
@endphp

<div
    data-filament-tutorials-runtime
    data-payload="{{ $payloadJson }}"
></div>
