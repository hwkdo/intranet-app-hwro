<?php

use function Livewire\Volt\{title};

title('Handwerksrolle Online - App-Info');

?>

<x-intranet-app-hwro::hwro-layout heading="App-Info" subheading="Installierte Version und Release-Historie">
    @livewire('intranet-app-base::app-info', ['appIdentifier' => 'hwro'])
</x-intranet-app-hwro::hwro-layout>
