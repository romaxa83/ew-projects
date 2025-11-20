@component('mail::message')

# @lang('cms-services-orders::admin.New request with the contact form')

### @lang('cms-services-orders::admin.Form data')
@component('mail::table')
    | | |
    |-|-|
    | @lang('cms-services-orders::admin.Name'):  | {{ $serviceOrder->name }} |
    | @lang('cms-services-orders::admin.Phone'): | [{{ $serviceOrder->phone }}](tel:{{ preg_replace('/[^\d\+]/', '', $serviceOrder->phone) }}) |
    | @lang('cms-services-orders::admin.City'):  | {{ $serviceOrder->city }} |
    | @lang('cms-services-orders::admin.E-mail'): | [{{ $serviceOrder->email }}](mailto:{{ $serviceOrder->email }}) |
@endcomponent

@lang('cms-services-orders::admin.Message')
@component('mail::panel')
    {{ $serviceOrder->message }}
@endcomponent

@component('mail::button', ['url' => $urlToAdmin, 'color' => 'green'])
    @lang('cms-services-orders::admin.Go to admin panel')
@endcomponent

@endcomponent
