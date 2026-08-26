@auth
    @if (! session('is_locked') && request()->route()?->getName() !== 'filament.admin.pages.lockscreen')
        @php
            $timeoutMinutes = \App\Models\Pengaturan::getLockTimeoutMinutes();
            $timeoutMs = ($timeoutMinutes > 0 ? $timeoutMinutes : 5) * 60 * 1000;
        @endphp
        <script>
            (function() {
                const idleTimeout = {{ $timeoutMs }};
                const lockscreenUrl = "{{ route('filament.admin.pages.lockscreen') }}";
                let lastActivity = Date.now();
                let checkTimer = null;

                function resetActivity() {
                    lastActivity = Date.now();
                }

                function checkIdle() {
                    if (Date.now() - lastActivity >= idleTimeout) {
                        if (checkTimer) {
                            clearInterval(checkTimer);
                        }
                        window.location.href = lockscreenUrl;
                    }
                }

                const activityEvents = [
                    'mousemove', 'mousedown', 'keydown', 'keyup', 
                    'scroll', 'touchstart', 'touchmove', 'click', 'input', 'change'
                ];

                activityEvents.forEach(function(evt) {
                    window.addEventListener(evt, resetActivity, { passive: true });
                });

                document.addEventListener('livewire:navigated', resetActivity);
                document.addEventListener('livewire:init', resetActivity);

                // Reset waktu aktivitas setiap kali Livewire mengirim/menerima request
                document.addEventListener('DOMContentLoaded', function() {
                    if (window.Livewire) {
                        Livewire.hook('request', () => {
                            resetActivity();
                        });
                    }
                });

                document.addEventListener('livewire:initialized', function() {
                    if (window.Livewire) {
                        Livewire.hook('request', () => {
                            resetActivity();
                        });
                    }
                });

                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        checkIdle();
                    }
                });

                if (checkTimer) {
                    clearInterval(checkTimer);
                }
                checkTimer = setInterval(checkIdle, 5000);
            })();
        </script>
    @endif
@endauth
