<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use FinityLabs\FinAvatar\AvatarProviders\UiAvatarsProvider;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;
use JeffersonGoncalves\Filament\RefreshSidebar\RefreshSidebarPlugin;
use Filament\Support\Icons\Heroicon;

use App\Http\Middleware\AutoLockscreen;
use Filament\View\PanelsRenderHook;

use Ipatco\FilamentProfile\FilamentProfilePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName(fn() => \App\Models\Pengaturan::getPengaturan()->nama_klinik ?? 'Myklinik')
            ->brandLogo(fn() => \App\Models\Pengaturan::getLogoUrl())
            ->darkModeBrandLogo(fn() => \App\Models\Pengaturan::getDarkModeLogoUrl())
            ->favicon(fn() => \App\Models\Pengaturan::getFaviconUrl())
            ->brandLogoHeight(fn() => \App\Models\Pengaturan::getLogoHeight())
            ->login()
            ->spa()
            ->font('Poppins')
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\Filament\Clusters')
            ->pages([
                Dashboard::class,
                \App\Filament\Pages\Auth\Lockscreen::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->modelLabel('Peran')
                    ->pluralModelLabel('Peran')
                    ->navigationLabel('Peran')
                    ->navigationGroup(null)
                    ->navigationSort(3),
                RefreshSidebarPlugin::make(),
                FilamentProfilePlugin::make()
                    ->label('Profil Saya')
                    ->icon('heroicon-o-user-circle'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->defaultAvatarProvider(UiAvatarsProvider::class)
            ->navigationGroups([
                NavigationGroup::make('Master')
                    ->icon(Heroicon::Folder),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn() => view('filament.components.auth-styles')
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn() => view('filament.components.topbar-selesaikan-pelayanan')
            )
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn() => view('filament.components.navbar-create-pasien')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn() => view('filament.components.idle-timer')
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn() => view('filament.components.notification-sound-player')
            )
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
                AutoLockscreen::class,
            ]);
    }
}
