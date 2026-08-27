@php
    $soundEnabled = \App\Models\Pengaturan::getPengaturan()->notifikasi_suara ?? true;
@endphp

@if ($soundEnabled)
<script>
    (function () {
        const defaultSoundUrl = "{{ asset('sounds/notification.mp3') }}";

        // Gunakan satu objek audio global tunggal agar suara tidak pernah tumpang tindih
        if (!window.__appSingleAudioPlayer) {
            window.__appSingleAudioPlayer = new Audio();
        }

        let isAudioUnlocked = false;
        let lastAudioPlayTime = 0;

        // Buka izin audio pada interaksi pertama pengguna (kebijakan browser)
        function unlockAudioContext() {
            if (!isAudioUnlocked) {
                window.__appSingleAudioPlayer.src = defaultSoundUrl;
                window.__appSingleAudioPlayer.volume = 0.01;
                window.__appSingleAudioPlayer.play().then(() => {
                    window.__appSingleAudioPlayer.pause();
                    window.__appSingleAudioPlayer.currentTime = 0;
                    window.__appSingleAudioPlayer.volume = 0.85;
                    isAudioUnlocked = true;
                    cleanupUnlockListeners();
                }).catch(() => {});
            }
        }

        function cleanupUnlockListeners() {
            document.removeEventListener('click', unlockAudioContext);
            document.removeEventListener('keydown', unlockAudioContext);
            document.removeEventListener('touchstart', unlockAudioContext);
        }

        document.addEventListener('click', unlockAudioContext, { passive: true });
        document.addEventListener('keydown', unlockAudioContext, { passive: true });
        document.addEventListener('touchstart', unlockAudioContext, { passive: true });

        // Fungsi pemutar audio tunggal dengan penguncian durasi (anti double/gema)
        function playSingleSound(customUrl) {
            const now = Date.now();

            // Kunci ketat: Abaikan semua pemicu baru jika audio baru saja diputar kurang dari 3.5 detik lalu
            if (now - lastAudioPlayTime < 3500) {
                return;
            }

            lastAudioPlayTime = now;

            const soundToPlay = (typeof customUrl === 'string' && customUrl.length > 0)
                ? customUrl
                : defaultSoundUrl;

            try {
                window.__appSingleAudioPlayer.pause();
                window.__appSingleAudioPlayer.currentTime = 0;
                window.__appSingleAudioPlayer.src = soundToPlay;
                window.__appSingleAudioPlayer.volume = 0.85;

                const playPromise = window.__appSingleAudioPlayer.play();
                if (playPromise !== undefined) {
                    playPromise.catch(function (err) {
                        console.log('Audio autoplay prevented:', err);
                    });
                }
            } catch (e) {
                console.error('Audio play error:', e);
            }
        }

        window.playAppNotificationSound = playSingleSound;

        // Pemicu Suara Default Notifikasi
        window.addEventListener('play-notification-sound', function () {
            playSingleSound();
        });

        // Pemicu Saat Notifikasi Filament Muncul
        window.addEventListener('open-notification', function () {
            playSingleSound();
        });

        // Dukungan Event Livewire
        document.addEventListener('livewire:init', function () {
            if (window.Livewire) {
                Livewire.on('play-notification-sound', function () {
                    playSingleSound();
                });
            }
        });

        @if (session('play_sound_on_load'))
            setTimeout(function () {
                playSingleSound();
            }, 300);
        @endif
    })();
</script>
@endif
