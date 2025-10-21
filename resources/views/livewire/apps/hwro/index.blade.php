<?php

use function Livewire\Volt\{title};

title('Handwerksrolle Online');

?>  
<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">Handwerksrolle Online</flux:heading>
        <flux:subheading size="lg" class="mb-6">Verwaltung der Handwerksrolle</flux:subheading>
        <flux:separator variant="subtle" />
    </div>
    
    <x-intranet-app-hwro::hwro-layout>
        <x-slot:navigation>
            <flux:navlist.item :href="route('apps.hwro.index')" wire:navigate current>Übersicht</flux:navlist.item>
            <flux:navlist.item :href="route('apps.hwro.vorgaenge.index')" wire:navigate>Vorgänge</flux:navlist.item>
        </x-slot:navigation>

        <div class="space-y-6">
            <flux:card>
                <flux:heading size="lg" class="mb-4">Willkommen in der Handwerksrolle</flux:heading>
                <flux:text class="mb-6">
                    Hier können Sie alle Aspekte der Handwerksrolle verwalten.
                </flux:text>
                
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:card>
                        <div class="flex items-center gap-3">
                            <flux:icon name="clipboard-document-list" class="size-8 text-zinc-500 dark:text-zinc-400" />
                            <div>
                                <flux:heading size="sm">Vorgänge</flux:heading>
                                <flux:text size="sm" class="text-zinc-500">Vorgänge verwalten</flux:text>
                            </div>
                        </div>
                        <flux:button 
                            :href="route('apps.hwro.vorgaenge.index')" 
                            wire:navigate 
                            variant="primary" 
                            class="mt-4 w-full"
                        >
                            Vorgänge anzeigen
                        </flux:button>
                    </flux:card>
                </div>
            </flux:card>
        </div>
    </x-intranet-app-hwro::hwro-layout>
</section>