@props([
    'heading' => '',
    'subheading' => '',
    'navItems' => []
])

@php
    $defaultNavItems = [
        ['label' => 'Übersicht', 'href' => route('apps.hwro.index'), 'icon' => 'home', 'description' => 'Zurück zur Übersicht', 'buttonText' => 'Übersicht anzeigen'],
        ['label' => 'Vorgänge', 'href' => route('apps.hwro.vorgaenge.index'), 'icon' => 'clipboard-document-list', 'description' => 'Vorgänge verwalten', 'buttonText' => 'Vorgänge anzeigen'],
        ['label' => 'Meine Einstellungen', 'href' => route('apps.hwro.settings.user'), 'icon' => 'cog-6-tooth', 'description' => 'Persönliche Einstellungen anpassen', 'buttonText' => 'Einstellungen öffnen'],
        ['label' => 'Admin', 'href' => route('apps.hwro.admin.index'), 'icon' => 'shield-check', 'description' => 'Administrationsbereich verwalten', 'buttonText' => 'Admin öffnen', 'permission' => 'manage-app-hwro']
    ];
    
    $navItems = !empty($navItems) ? $navItems : $defaultNavItems;
@endphp

<x-intranet-app-base::app-layout 
    app-identifier="hwro"
    :heading="$heading"
    :subheading="$subheading"
    :nav-items="$navItems"
>
    @if(request()->routeIs('apps.hwro.index'))
        <x-intranet-app-base::app-index-auto 
            app-identifier="hwro"
            app-name="Handwerksrolle Online"
            app-description="Verwaltung der Handwerksrolle"
            :nav-items="$navItems"
            welcome-title="Willkommen in der Handwerksrolle"
            welcome-description="Hier können Sie alle Aspekte der Handwerksrolle verwalten."
        />
    @else
        {{ $slot }}
    @endif
</x-intranet-app-base::app-layout>

