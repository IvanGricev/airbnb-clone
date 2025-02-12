@component('mail::message')
# Подтверждение email адреса

Уважаемый {{ $user->name }},

Спасибо за регистрацию на нашем сайте!

Для подтверждения вашего email адреса, пожалуйста, перейдите по следующей ссылке:

@component('mail::button', ['url' => $verificationUrl])
Подтвердить email
@endcomponent

Если вы не регистрировались на нашем сайте, просто проигнорируйте это письмо.

С уважением,
{{ config('app.name') }}

@endcomponent