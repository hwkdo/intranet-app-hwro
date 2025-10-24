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
    {{ $slot }}
</x-intranet-app-base::app-layout>

