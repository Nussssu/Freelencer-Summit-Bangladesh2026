<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Support\Settings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset() // also the "set your password" page for invited users
            ->brandName('Freelancer Summit Bangladesh')
            ->brandLogo(fn () => ($logo = Settings::identity()['logo']) ? url($logo) : null)
            ->favicon(fn () => Settings::identity()['favicon'] ?? '/favicon.svg')
            ->colors([
                'primary' => Color::hex('#087f8c'), // site teal (--green)
            ])
            // Nine collapsible groups, in the order the team works through them.
            ->navigationGroups(array_map(fn (string $label) => NavigationGroup::make($label)->collapsible(), [
                'Overview', 'Leads & CRM', 'Registrations', 'Awards Management', 'Messages',
                'Event Management', 'Content Management', 'Configuration', 'Access Control',
            ]))
            ->databaseNotifications() // new submissions notify the team in the panel (bell, top right)
            ->databaseNotificationsPolling('60s')
            ->sidebarWidth('16rem') // default 20rem
            ->renderHook(PanelsRenderHook::STYLES_AFTER, fn () => view('filament.hooks.theme'))
            ->renderHook(PanelsRenderHook::SIDEBAR_FOOTER, fn () => view('filament.hooks.sidebar-view-website'))
            ->renderHook(PanelsRenderHook::SIDEBAR_FOOTER, fn () => view('filament.hooks.sidebar-logout'))
            ->renderHook(PanelsRenderHook::SIDEBAR_LOGO_AFTER, fn () => view('filament.hooks.brand-caption'))
            ->renderHook(PanelsRenderHook::GLOBAL_SEARCH_AFTER, fn () => view('filament.hooks.visit-website'))
            // A Back button above every page heading except the Dashboard (the panel's start page).
            ->renderHook(PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE, fn (array $scopes) => collect($scopes)->contains(fn ($s) => is_string($s) && is_a($s, \Filament\Pages\Dashboard::class, true)) ? '' : view('filament.hooks.back-button', ['fallback' => Filament::getUrl()]))
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->maxContentWidth(Width::Full) // tables and dashboard use the whole content area
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class, // App\Filament\Pages\Dashboard: three-column layout
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
