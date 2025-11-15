<x-filament-panels::page
    wire:poll.5s="checkForNewRecords"
    x-data="{
        playNotificationSound() {
            const audio = new Audio('/sounds/notification.mp3');
            audio.volume = 0.5;
            audio.play().catch(error => {
                console.log('No se pudo reproducir el sonido:', error);
            });
        }
    }"
    x-on:play-notification-sound.window="playNotificationSound()"
>
    {{ $this->table }}
</x-filament-panels::page>
