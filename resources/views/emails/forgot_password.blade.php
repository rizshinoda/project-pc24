@component('mail::message')
Hi, {{$user->name}}. Forgot Password?
<p> it Happens.</p>

@component('mail::button', ['url' => url ('reset/'.$user->remember_token)])
Reset your password

@endcomponent

Thanks, <br>
{{config('app.name')}}
@endcomponent