<?php

use function Livewire\Volt\{title};

title('Meine Einstellungen - Handwerksrolle Online');

?>

<x-intranet-app-hwro::hwro-layout heading="Meine Einstellungen" subheading="Persönliche Einstellungen für die Handwerksrolle">
    @livewire('intranet-app-base::user-settings', ['appIdentifier' => 'hwro'])
</x-intranet-app-hwro::hwro-layout>

