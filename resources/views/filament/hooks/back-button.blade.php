{{-- Back to the previous admin screen (browser history); opened directly, it falls back to the Dashboard. Icon only. --}}
<a href="{{ $fallback }}" class="fs-back" aria-label="Back" title="Back"
   x-data
   x-on:click.prevent="document.referrer && new URL(document.referrer).origin === location.origin && history.length > 1 ? history.back() : (window.location.href = @js($fallback))">
    <x-filament::icon icon="heroicon-o-arrow-left" class="h-4 w-4" />
</a>
