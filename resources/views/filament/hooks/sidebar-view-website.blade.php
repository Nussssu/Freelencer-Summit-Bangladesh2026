{{-- Opens the public site in a new tab, pinned above the logout button. --}}
<div class="fs-view-website">
    <a href="{{ url('/') }}" target="_blank" rel="noopener">
        <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="h-5 w-5" />
        View Website
    </a>
</div>
