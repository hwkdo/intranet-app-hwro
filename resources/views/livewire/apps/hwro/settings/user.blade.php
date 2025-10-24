<?php

use function Livewire\Volt\{title};

title('Meine Einstellungen - Handwerksrolle Online');

?>

<x-intranet-app-hwro::hwro-layout heading="Handwerksrolle Online" subheading="Meine Einstellungen">
    <x-intranet-app-base::user-settings app-identifier="hwro" />
</x-intranet-app-hwro::hwro-layout>

