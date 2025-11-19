@php
    /**
     * @var $url string|null
     * @var $displayableActionUrl string|null
     * @var $slot \Illuminate\Support\HtmlString
     */
@endphp

<table cellpadding="0" cellspacing="0" width="100%">
<tr>
<td class="clicking-trouble" width="560" align="center" valign="top">
<table cellpadding="0" cellspacing="0" width="100%" role="presentation">
<tr>
<td class="clicking-trouble__text" align="center">
<p>{{ $slot->isNotEmpty() ? Illuminate\Mail\Markdown::parse($slot) : __('core::messages.mail.clicking_trouble_notice') }}</p>
</td>
</tr>
<tr>
<td class="clicking-trouble__action" align="center">
<p>
<a href="{{ $url }}" class="clicking-trouble__action-link" target="_blank">
{{ $displayableActionUrl ?? $url }}
</a>
</p>
</td>
</tr>
</table>
</td>
</tr>
</table>
