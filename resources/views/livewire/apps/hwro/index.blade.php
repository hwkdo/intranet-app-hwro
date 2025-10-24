<?php

use function Livewire\Volt\{title};

title('Handwerksrolle Online');

?>

<x-intranet-app-hwro::hwro-layout heading="Handwerksrolle Online" subheading="Verwaltung der Handwerksrolle">
    <x-intranet-app-base::app-index-auto 
        app-identifier="hwro"
        app-name="Handwerksrolle Online"
        app-description="Verwaltung der Handwerksrolle"
        welcome-title="Willkommen in der Handwerksrolle"
        welcome-description="Hier können Sie alle Aspekte der Handwerksrolle verwalten."
    />
</x-intranet-app-hwro::hwro-layout>