@slot('header')
    @component('mail::header', ['url' => config('app.url')])
        <!-- header here -->
        {{ config('app.name') }}
    @endcomponent
@endslot
@component('mail::message')
# Olá, Desenvolvedor

{{ $alert }}

Atenciosamente,<br>
{{ config('app.name') }}
@endcomponent
