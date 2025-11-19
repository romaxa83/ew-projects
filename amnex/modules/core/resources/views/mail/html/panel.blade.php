<table class="panel" width="560" align="center">
<tr>
<td class="panel__body" align="center">
<table>
<tr>
@if($title ?? null)
<td>
<p class="title title--h4">{{ $title }}</p>
</td>
@endif
</tr>
@if($slot ?? null)
<tr>
<td class="panel__massage">
    {{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
@endif
</table>
</td>
</tr>
</table>
