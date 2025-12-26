@extends('layouts.public')

@section('content')
    <div class="container" style="padding:2rem; text-align:center;">
        <div style="margin-bottom:1rem;">
            <img src="{{ asset('images/aurora-expressions/3-3.png') }}" alt="Aurora triste" style="width:220px;height:auto;display:block;margin:0 auto;" />
        </div>
        <h1>Confirmar baja</h1>
        <p>Hola {{ $client->client }},</p>
        <p>¿Estás seguro de que quieres darte de baja de las comunicaciones por correo de Aurora? Si te das de baja, dejarás de recibir nuestros correos de campañas.</p>

        <form method="POST" action="{{ route('unsubscribe', ['client' => $client->id, 'uuid' => $message->message_uuid]) }}" style="margin-top:1rem;">
            @csrf
            <button type="submit" class="btn" style="border: 1px solid var(--color-1);; padding:0.5rem 1rem;">Sí, darme de baja</button>
            <a href="{{ url('/') }}" class="btn btn-secondary" style="padding:0.5rem 1rem; margin-top:10px; margin-left:0.5rem; text-decoration:none;">Cancelar</a>
        </form>
    </div>
@endsection