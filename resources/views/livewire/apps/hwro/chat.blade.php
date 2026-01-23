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

$endpoint = computed(function () {
    $baseUrl = config('openwebui-api-laravel.base_api_url', 'https://chat.ai.hwk-do.com/api');
    
    return rtrim($baseUrl, '/').'/chat/completions';
});

$hasApiKey = computed(function () {
    return ! empty($this->apiKey);
});

?>

<x-intranet-app-hwro::hwro-layout heading="Chat" subheading="KI-Chat für HWRO-Vorgänge und Dokumente">
    @if ($this->hasApiKey)
        <div class="h-[800px]">
            @livewire('open-web-ui-chat', [
                'model' => $this->model,
                'apiKey' => $this->apiKey,
                'endpoint' => $this->endpoint,
            ])
        </div>
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
