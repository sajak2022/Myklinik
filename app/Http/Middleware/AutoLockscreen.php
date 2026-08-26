<?php

namespace App\Http\Middleware;

use App\Models\Pengaturan;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AutoLockscreen
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Jika tidak sedang login, bersihkan session flag lockscreen
        if (! Filament::auth()->check()) {
            session()->forget(['is_locked', 'locked_at', 'last_activity_time', 'lock_intended_url']);

            return $next($request);
        }

        $timeoutMinutes = Pengaturan::getLockTimeoutMinutes();
        $timeoutSeconds = ($timeoutMinutes > 0 ? $timeoutMinutes : 5) * 60;

        $isLockscreenRoute = $request->route()?->getName() === 'filament.admin.pages.lockscreen' 
            || $request->is('admin/lockscreen*');

        // 1. Jika sesi dalam status terkunci
        if (session('is_locked') === true) {
            // Izinkan akses ke halaman lockscreen dan request Livewire lockscreen
            if ($isLockscreenRoute) {
                return $next($request);
            }

            if ($request->header('X-Livewire')) {
                $payload = json_encode($request->all());
                if (stripos($payload, 'lockscreen') !== false) {
                    return $next($request);
                }

                return response()->json(['redirect' => route('filament.admin.pages.lockscreen')], 423);
            }

            if ($request->expectsJson()) {
                return response()->json(['redirect' => route('filament.admin.pages.lockscreen')], 423);
            }

            // Simpan intended URL agar setelah unlock bisa kembali ke halaman sebelumnya
            if (! $request->is('admin/login*') && ! $request->is('admin/logout*')) {
                session(['lock_intended_url' => $request->fullUrl()]);
            }

            return redirect()->route('filament.admin.pages.lockscreen');
        }

        // Rute yang dikecualikan dari penguncian
        $exemptRouteNames = [
            'filament.admin.pages.lockscreen',
            'filament.admin.auth.login',
            'filament.admin.auth.logout',
        ];

        $routeName = $request->route()?->getName();
        if (in_array($routeName, $exemptRouteNames, true) || $request->is('admin/login*') || $request->is('admin/logout*')) {
            return $next($request);
        }

        // 2. Cek inaktivitas dari sesi normal
        $lastActivity = session('last_activity_time');

        if ($lastActivity && (time() - $lastActivity > $timeoutSeconds)) {
            session([
                'is_locked' => true,
                'locked_at' => time(),
                'lock_intended_url' => $request->fullUrl(),
            ]);

            if ($request->expectsJson() || $request->header('X-Livewire')) {
                return response()->json(['redirect' => route('filament.admin.pages.lockscreen')], 423);
            }

            return redirect()->route('filament.admin.pages.lockscreen');
        }

        // Perbarui waktu aktivitas terakhir jika belum terkunci
        session(['last_activity_time' => time()]);

        return $next($request);
    }
}
