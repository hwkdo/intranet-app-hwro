<?php

use function Livewire\Volt\{title};

title('Meine Einstellungen - Handwerksrolle Online');

?>

<x-intranet-app-hwro::hwro-layout heading="Handwerksrolle Online" subheading="Meine Einstellungen">
    @livewire('intranet-app-base::user-settings', ['appIdentifier' => 'hwro'])
</x-intranet-app-hwro::hwro-layout>

