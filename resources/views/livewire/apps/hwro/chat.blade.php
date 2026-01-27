<?php

use App\Data\UserSettings;
use Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings;
use Illuminate\Support\Facades\Auth;


use function Livewire\Volt\{computed, title};

title('HWRO - Chat');

$appSettings = computed(function () {
    $settings = IntranetAppHwroSettings::current();
    
    return $settings?->settings;
});

$apiKey = computed(function () {
    $user = Auth::user();
    
    if (! $user) {
        return '';
    }
    
    $settings = UserSettings::from($user->settings);
    
    return $settings->ai->openWebUiApiToken ?? '';
});

$model = computed(function () {
    return $this->appSettings?->openWebUiModel ?? config('openwebui-api-laravel.default_model', 'gpt-oss:20b');
});

$baseUrl = computed(function () {
    return config('openwebui-api-laravel.base_api_url_ollama', 'https://chat.ai.hwk-do.com/api');
});

$hasApiKey = computed(function () {
    return ! empty($this->apiKey);
});

?>

<x-intranet-app-hwro::hwro-layout heading="Chat" subheading="KI-Chat für HWRO-Vorgänge und Dokumente">
    @if ($this->hasApiKey)
        @livewire('prism-chat', [
            'appIdentifier' => 'hwro',
            'model' => $this->model,
            'apiKey' => $this->apiKey,
            'baseUrl' => $this->baseUrl,
            'useMcpTools' => true, // Temporär deaktiviert zum Testen
        ])
    @else
        <flux:card>
            <flux:callout variant="warning" class="mb-4">
                <flux:heading size="sm">API-Token fehlt</flux:heading>
                <flux:text>
                    Um den Chat zu nutzen, müssen Sie einen OpenWebUI API-Token in Ihren globalen Einstellungen konfigurieren.
                </flux:text>
            </flux:callout>

            <flux:button
                variant="primary"
                href="{{ route('settings.all') }}"
            >
                Zu den Einstellungen
            </flux:button>
        </flux:card>
    @endif
</x-intranet-app-hwro::hwro-layout>
