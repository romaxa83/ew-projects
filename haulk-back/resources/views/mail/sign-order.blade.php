@component('mail::message')

# Hello,

You need to familiarize yourself with the completed inspections on the order and sign that everything is recorded correctly.
Click on the button below to go to sign.

@component('mail::button', ['url' => $url, 'color' => 'primary'])
    Sign BOL
@endcomponent

<strong style="color: red">Attention, do not pass this link to third parties!</strong> Follow the link now, as in 3 hours it will no longer be available.

@endcomponent
