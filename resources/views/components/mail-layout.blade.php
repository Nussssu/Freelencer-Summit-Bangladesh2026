<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>{{ $title ?? config('summit.name') }}</title></head>
<body style="margin:0;background:#f5f2e9;color:#071411;font-family:Helvetica,Arial,sans-serif;font-size:16px;line-height:1.55">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f2e9">
<tr><td align="center" style="padding:32px 16px">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid rgba(7,20,17,.1)">
        <tr><td style="background:#071411;color:#f5f2e9;padding:20px 28px;font-weight:800;letter-spacing:.02em;text-transform:uppercase">
            {{ config('summit.name') }} <span style="color:#eab94b">{{ config('summit.edition') }}</span>
        </td></tr>
        <tr><td style="height:4px;background:#087f8c"></td></tr>
        <tr><td style="padding:28px">{{ $slot }}</td></tr>
        <tr><td style="padding:18px 28px;border-top:1px solid rgba(7,20,17,.1);color:#3f4b44;font-size:13px">
            Jointly organised by BACCO and DoICT. You are receiving this because you used a form on {{ parse_url(config('app.url'), PHP_URL_HOST) }}.
        </td></tr>
    </table>
</td></tr>
</table>
</body>
</html>
