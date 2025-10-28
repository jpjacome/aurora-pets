@component('mail::message')

Hola{{ !empty($payload['owner_name']) ? ' ' . $payload['owner_name'] : '' }},

Te compartimos el resultado del PlantScan:


@if(!empty($payload['image_contents']) && !empty($payload['image_mime']))
<div style="text-align:center;margin:32px 0 16px 0;padding-top:16px;">
    <img src="{{ $message->embedData($payload['image_contents'], 'resultado-plantscan.png', $payload['image_mime']) }}" alt="Plant image" style="max-width:100%;height:auto;border-radius:6px;">
</div>
@elseif(!empty($payload['og_image']))
<div style="text-align:center;margin:32px 0 16px 0;padding-top:16px;">
    <img src="{{ $payload['og_image'] }}" alt="Plant image" style="max-width:100%;height:auto;border-radius:6px;">
</div>
@elseif(!empty($payload['image']))
<div style="text-align:center;margin:32px 0 16px 0;padding-top:16px;">
    <img src="{{ $payload['image'] }}" alt="Plant image" style="max-width:100%;height:auto;border-radius:6px;">
</div>
@endif

Gracias por usar PlantScan.

Saludos,

Equipo Aurora

@endcomponent
