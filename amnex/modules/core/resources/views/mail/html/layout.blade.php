<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
@include('core::mail.html.themes.media')
</style>
<!--[if (mso)|(mso 16)]>
<style type="text/css">
@include('core::mail.html.themes.mso')
</style>
<![endif]-->
</head>
<body>
<div>
<!--[if gte mso 9]>
<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
<v:fill type="tile" color="#ffffff"></v:fill>
</v:background>
<![endif]-->
<table class="wrapper" width="100%" cellspacing="0" cellpadding="0" border="0" background="#ffffff" bgcolor="#ffffff">
<tr>
<td valign="top">

<table class="wrapper__content" width="560" cellpadding="0" cellspacing="0" border="0" background="#ffffff" bgcolor="#ffffff" align="center">
<tr>
<td align="center">

<table width="560" cellpadding="0" cellspacing="0" border="0" align="center">
<tr>
<td align="center" width="560">

{{-- Header --}}
{{ $header ?? '' }}
{{-- Email Body --}}
<table>
<tr>
<td class="wrapper__body" width="560">

{{ Illuminate\Mail\Markdown::parse($slot) }}
{{-- Subcopy --}}
{{ $subcopy ?? '' }}

</td>
</tr>
</table>

</td>
</tr>
</table>

</td>
</tr>
</table>

</td>
</tr>
</table>

</div>
</body>
</html>
