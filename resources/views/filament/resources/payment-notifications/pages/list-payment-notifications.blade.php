<div
    wire:poll.5s="checkForNewRecords"
    x-data="{
        soundEnabled: false,
        audio: null,

        init() {
            this.soundEnabled = localStorage.getItem('payment_sounds_enabled') === 'true';
        },

        toggleSound() {
            if (!this.soundEnabled) {
                this.audio = new Audio('/sounds/notification.mp3');
                this.audio.volume = 0.5;

                this.audio.play().then(() => {
                    this.soundEnabled = true;
                    localStorage.setItem('payment_sounds_enabled', 'true');
                    console.log('✓ Sonidos activados');
                }).catch(error => {
                    console.error('Error al activar sonidos:', error);
                    alert('No se pudieron activar los sonidos. Verifica que el archivo notification.mp3 exista en public/sounds/');
                });
            } else {
                this.soundEnabled = false;
                localStorage.removeItem('payment_sounds_enabled');
                console.log('✓ Sonidos desactivados');
            }
        },

        playNotificationSound() {
            if (!this.soundEnabled) return;

            if (!this.audio) {
                this.audio = new Audio('/sounds/notification.mp3');
                this.audio.volume = 0.5;
            }

            this.audio.currentTime = 0;
            this.audio.play().catch(error => {
                console.log('Error al reproducir:', error);
            });
        }
    }"
    x-on:play-notification-sound.window="playNotificationSound()"
>
    <x-filament-panels::page>
        <x-slot name="headerActions">
            <button
                type="button"
                @click="toggleSound()"
                class="fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-primary fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm"
                :class="soundEnabled
                    ? 'bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50'
                    : 'bg-gray-800 text-white hover:bg-gray-700 focus-visible:ring-gray-700/50'"
                :style="soundEnabled ? '--c-400:var(--success-400);--c-500:var(--success-500);--c-600:var(--success-600);' : ''"
            >
                <svg
                    class="fi-btn-icon h-5 w-5"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="1.5"
                    stroke="currentColor"
                >
                    <template x-if="soundEnabled">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.114 5.636a9 9 0 010 12.728M16.463 8.288a5.25 5.25 0 010 7.424M6.75 8.25l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" />
                    </template>
                    <template x-if="!soundEnabled">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 9.75L19.5 12m0 0l2.25 2.25M19.5 12l2.25-2.25M19.5 12l-2.25 2.25m-10.5-6l4.72-4.72a.75.75 0 011.28.53v15.88a.75.75 0 01-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.01 9.01 0 012.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75z" />
                    </template>
                </svg>
                <span class="fi-btn-label" x-text="soundEnabled ? 'Sonidos On' : 'Sonidos Off'"></span>
            </button>
        </x-slot>

        {{ $this->table }}
    </x-filament-panels::page>
</div>
