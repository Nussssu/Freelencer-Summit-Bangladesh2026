{{-- Always-visible sign out, pinned under the scrolling navigation (white on red ink: 6.5:1). --}}
<form method="post" action="{{ filament()->getLogoutUrl() }}" class="fs-logout">
    @csrf
    <button type="submit">
        <x-filament::icon icon="heroicon-o-arrow-left-start-on-rectangle" class="h-5 w-5" />
        Logout
    </button>
</form>
