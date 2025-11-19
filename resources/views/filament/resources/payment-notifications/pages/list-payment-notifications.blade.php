<x-filament-panels::page
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
            this.audio.play().catch(error => console.log('Error:', error));
        }
    }"
    x-on:play-notification-sound.window="playNotificationSound()"
>
    <!-- Botón de sonido ARRIBA de todo -->
    <div class="mb-4 flex justify-end">
        <button
            type="button"
            @click="toggleSound()"
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg font-medium text-xs shadow-sm transition text-white"
            :class="soundEnabled ? 'bg-green-600 hover:bg-green-500' : 'bg-gray-700 hover:bg-gray-600'"
            style="display: inline-flex; align-items: center;"
        >
            <!-- Icono de volumen ON -->
            <svg x-show="soundEnabled" style="width: 14px; height: 14px;" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15.414A2 2 0 014.293 13H3a1 1 0 01-1-1V8a1 1 0 011-1h1.293a2 2 0 001.293-.414l7-7a1 1 0 011.414 0l.293.293z"/>
            </svg>
            <!-- Icono de volumen OFF -->
            <svg x-show="!soundEnabled" style="width: 14px; height: 14px;" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
            </svg>
            <span x-text="soundEnabled ? 'Sonidos Activados' : 'Activar Sonidos'"></span>
        </button>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
