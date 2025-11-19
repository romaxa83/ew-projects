@php
    /**
    * @var $fields array|\Illuminate\Support\Collection
    */

    $fields = collect($fields ?? []);
    $rows = $fields->chunk(2);
@endphp
<table
width="100%"
cellpadding="0"
cellspacing="0">
<tr>
<td class="data">
<table
width="100%"
cellpadding="0"
cellspacing="0">
<tr>
<td class="data__wrap">
<table
width="100%"
cellpadding="0"
cellspacing="0">
@foreach($rows as $rowsGroup)
<tr class="data__tr">
@foreach($rowsGroup as $name => $value)
<td class="data__td"
width="50%">
<table
width="100%"
cellpadding="0"
cellspacing="0">
<tr>
<td class="data__name">{{ $name }}</td>
</tr>
<tr>
<td class="data__value">
@foreach((array)$value as $key => $item)
@switch(true)
@case(is_string($key) && filter_var($key, FILTER_VALIDATE_URL))
<a class="link"
href="{{ $key }}"
download="{{ $item ?: pathinfo($key, PATHINFO_BASENAME) }}">{{ $item ?: pathinfo($key, PATHINFO_BASENAME) }}</a>
@break
@case(filter_var($item, FILTER_VALIDATE_EMAIL))
<a class="link"
href="mailto:{{ $item }}">{{ $item }}</a>
@break
@case(filter_var($item, FILTER_VALIDATE_URL))
<a class="link"
href="{{ $item }}">{{ $item }}</a>
@break
@case(preg_match('/^\+380\d{9}$/', $item))
<a
href="tel:{{ $item }}">{{ $item }}</a>
@break
@default
{{ $item ?: '----' }}
@endswitch
@endforeach
</td>
</tr>
</table>
</td>
@endforeach
</tr>
@endforeach
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>

