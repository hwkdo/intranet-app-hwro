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
                    
                    <flux:card>
                        <div class="flex items-center gap-3">
                            <flux:icon name="cog-6-tooth" class="size-8 text-zinc-500 dark:text-zinc-400" />
                            <div>
                                <flux:heading size="sm">Meine Einstellungen</flux:heading>
                                <flux:text size="sm" class="text-zinc-500">Persönliche Einstellungen anpassen</flux:text>
                            </div>
                        </div>
                        <flux:button 
                            :href="route('apps.hwro.settings.user')" 
                            wire:navigate 
                            variant="primary" 
                            class="mt-4 w-full"
                        >
                            Einstellungen öffnen
                        </flux:button>
                    </flux:card>
                    
                    @can('manage-app-hwro')
                        <flux:card>
                            <div class="flex items-center gap-3">
                                <flux:icon name="shield-check" class="size-8 text-zinc-500 dark:text-zinc-400" />
                                <div>
                                    <flux:heading size="sm">Admin</flux:heading>
                                    <flux:text size="sm" class="text-zinc-500">Administrationsbereich verwalten</flux:text>
                                </div>
                            </div>
                            <flux:button 
                                :href="route('apps.hwro.admin.index')" 
                                wire:navigate 
                                variant="primary" 
                                class="mt-4 w-full"
                            >
                                Admin öffnen
                            </flux:button>
                        </flux:card>
                    @endcan
                </div>
            </flux:card>
        </div>
    </x-intranet-app-hwro::hwro-layout>
</section>