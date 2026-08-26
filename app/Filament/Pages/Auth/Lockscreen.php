<?php

namespace App\Filament\Pages\Auth;

use App\Models\Pengaturan;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class Lockscreen extends Page implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    protected string $view = 'filament.auth.lockscreen';

    protected static string $layout = 'filament-panels::components.layout.simple';

    protected static ?string $slug = 'lockscreen';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function mount(): void
    {
        if (! Filament::auth()->check()) {
            session()->forget(['is_locked', 'locked_at', 'last_activity_time', 'url.intended']);
            $this->redirect(Filament::getLoginUrl(), navigate: false);

            return;
        }

        $timeoutMinutes = Pengaturan::getLockTimeoutMinutes();
        $timeoutSeconds = ($timeoutMinutes > 0 ? $timeoutMinutes : 5) * 60;

        $lockedAt = session('locked_at');

        // Jika waktu di lockscreen sudah melebihi 5 menit tanpa input password, otomatis logout penuh
        if ($lockedAt && (time() - $lockedAt > $timeoutSeconds)) {
            $this->logout();

            return;
        }

        if (! session('is_locked')) {
            session([
                'is_locked' => true,
                'locked_at' => time(),
            ]);
        } elseif (! session('locked_at')) {
            session(['locked_at' => time()]);
        }

        $this->form->fill();
    }

    public function getRemainingSeconds(): int
    {
        $timeoutMinutes = Pengaturan::getLockTimeoutMinutes();
        $timeoutSeconds = ($timeoutMinutes > 0 ? $timeoutMinutes : 5) * 60;

        $lockedAt = session('locked_at', time());
        $elapsed = time() - $lockedAt;
        $remaining = $timeoutSeconds - $elapsed;

        return max(0, $remaining);
    }

    public function checkTimeout(): void
    {
        $timeoutMinutes = Pengaturan::getLockTimeoutMinutes();
        $timeoutSeconds = ($timeoutMinutes > 0 ? $timeoutMinutes : 5) * 60;

        $lockedAt = session('locked_at');

        if ($lockedAt && (time() - $lockedAt >= $timeoutSeconds)) {
            $this->logout();
        }
    }

    public function getTitle(): string | Htmlable
    {
        return 'Sesi Terkunci';
    }

    public function getHeading(): string | Htmlable
    {
        return '';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('password')
                    ->label('Password Akun')
                    ->password()
                    ->revealable()
                    ->required()
                    ->autofocus()
                    ->placeholder('Masukkan password Anda'),
            ])
            ->statePath('data');
    }

    public function authenticate(): void
    {
        // Cek jika batas waktu sesi telah habis saat mencoba submit
        $timeoutMinutes = Pengaturan::getLockTimeoutMinutes();
        $timeoutSeconds = ($timeoutMinutes > 0 ? $timeoutMinutes : 5) * 60;
        $lockedAt = session('locked_at');

        if ($lockedAt && (time() - $lockedAt > $timeoutSeconds)) {
            $this->logout();

            return;
        }

        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title('Terlalu banyak percobaan')
                ->body('Silakan tunggu ' . ceil($exception->secondsUntilAvailable) . ' detik lagi.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $password = $data['password'] ?? '';
        $user = Filament::auth()->user();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'data.password' => 'Password yang Anda masukkan salah. Silakan coba lagi.',
            ]);
        }

        // Re-otentikasi dan perbarui sesi user agar selalu aktif
        Filament::auth()->login($user, remember: true);
        session()->regenerate();
        session()->forget(['is_locked', 'locked_at', 'url.intended']);
        session(['last_activity_time' => time()]);

        Notification::make()
            ->title('Sesi Dibuka')
            ->body('Selamat datang kembali, ' . ($user->name ?? 'User') . '!')
            ->success()
            ->send();

        $this->redirect(route('filament.admin.pages.dashboard'));
    }

    public function logout(): void
    {
        session()->forget(['is_locked', 'locked_at', 'last_activity_time', 'url.intended']);
        Filament::auth()->logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(Filament::getLoginUrl(), navigate: false);
    }
}
