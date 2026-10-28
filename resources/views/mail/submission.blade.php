<x-mail-layout :title="$heading">
    <h1 style="margin:0 0 12px;font-size:24px;line-height:1.2">{{ $heading }}</h1>
    <p style="margin:0 0 20px;color:#3f4b44">Your reference number</p>
    <p style="margin:0 0 24px;font-family:'Courier New',monospace;font-size:20px;font-weight:700;letter-spacing:.06em;color:#087f8c">{{ $reference }}</p>
    @foreach ($lines as $line)
        <p style="margin:0 0 14px">{{ $line }}</p>
    @endforeach
    @if ($actionUrl)
        <p style="margin:24px 0 0"><a href="{{ $actionUrl }}" style="display:inline-block;background:#eab94b;color:#071411;padding:12px 20px;font-weight:700;text-transform:uppercase;text-decoration:none;font-size:14px">{{ $actionLabel }}</a></p>
    @endif
</x-mail-layout>
