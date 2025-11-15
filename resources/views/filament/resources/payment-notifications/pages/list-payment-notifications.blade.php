<x-filament-panels::page
    wire:poll.5s="checkForNewRecords"
    x-data="{
        soundEnabled: false,
        audio: null,

        init() {
            // Verificar si el usuario ya habilitó los sonidos previamente
            this.soundEnabled = localStorage.getItem('payment_sounds_enabled') === 'true';
        },

        toggleSound() {
            if (!this.soundEnabled) {
                // Inicializar audio con interacción del usuario
                this.audio = new Audio('/sounds/notification.mp3');
                this.audio.volume = 0.5;

                // Intentar reproducir para desbloquear
                this.audio.play().then(() => {
                    this.soundEnabled = true;
                    localStorage.setItem('payment_sounds_enabled', 'true');
                    console.log('✓ Sonidos activados');
                }).catch(error => {
                    console.error('Error al activar sonidos:', error);
                    alert('No se pudieron activar los sonidos. Por favor, verifica que el archivo de audio exista en public/sounds/notification.mp3');
                });
            } else {
                // Desactivar sonidos
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

            // Reiniciar el audio si ya está reproduciéndose
            this.audio.currentTime = 0;
            this.audio.play().catch(error => {
                console.log('Error al reproducir:', error);
            });
        }
    }"
    x-on:play-notification-sound.window="playNotificationSound()"
>
    <x-slot name="headerActions">
        <button
            type="button"
            @click="toggleSound()"
            class="filament-button filament-button-size-md inline-flex items-center justify-center gap-1 font-medium rounded-lg border transition-colors focus:outline-none focus:ring-offset-2 focus:ring-2 focus:ring-inset"
            :class="soundEnabled
                ? 'bg-success-600 hover:bg-success-500 focus:bg-success-700 focus:ring-offset-success-700 border-transparent text-white'
                : 'bg-gray-600 hover:bg-gray-500 focus:bg-gray-700 focus:ring-offset-gray-700 border-transparent text-white dark:bg-gray-700 dark:hover:bg-gray-600'"
            style="padding: 0.5rem 1rem;"
        >
            <svg
                class="filament-button-icon w-5 h-5"
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
            <span x-text="soundEnabled ? 'Sonidos Activados' : 'Activar Sonidos'"></span>
        </button>
    </x-slot>

    {{ $this->table }}
</x-filament-panels::page>
