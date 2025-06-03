@component('mail::message')
# Hi, {{ $user->name }}

Silakan verifikasi email kamu dengan menekan tombol di bawah ini:

@component('mail::button', ['url' => url('verify/'.$user->remember_token)])
Verifikasi Email
@endcomponent

Terima kasih,
{{ config('app.name') }}
@endcomponent