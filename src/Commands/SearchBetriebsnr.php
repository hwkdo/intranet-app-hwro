<?php

namespace Hwkdo\IntranetAppHwro\Commands;

use Hwkdo\BueLaravel\BueLaravel;
use Hwkdo\IntranetAppHwro\Events\BetriebsNrFound;
use Hwkdo\IntranetAppHwro\Events\BetriebsNrNotFound;
use Hwkdo\IntranetAppHwro\Events\SearchBetriebsNrFinished;
use Hwkdo\IntranetAppHwro\Events\SearchBetriebsNrStarted;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SearchBetriebsnr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intranet-app-hwro:search-betriebsnr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search for betriebsnr for vorgaenge which dont have a betriebsnr';

    /**
     * Execute the console command.
     */
    public function handle(BueLaravel $bueService)
    {
        Log::info('intranet-app-hwro:search-betriebsnr started');
        SearchBetriebsNrStarted::dispatch('intranet-app-hwro:search-betriebsnr started');
        $vorgaenge = Vorgang::whereNull('betriebsnr')->get();
        $found = [];
        $notFound = [];
        foreach ($vorgaenge as $vorgang) {
            $betriebsnr = $bueService->getBetriebsnrByVorgangsnummer($vorgang->vorgangsnummer);
            if ($betriebsnr) {
                $vorgang->update(['betriebsnr' => $betriebsnr]);
                BetriebsNrFound::dispatch('Vorgang '.$vorgang->vorgangsnummer.': Betriebsnr '.$betriebsnr.' found');
                $found[] = $vorgang->vorgangsnummer.' - Betriebsnr '.$betriebsnr;
            } else {
                BetriebsNrNotFound::dispatch('Vorgang '.$vorgang->vorgangsnummer.': Betriebsnr not found');
                $notFound[] = $vorgang->vorgangsnummer;
            }
        }
        $this->info('Betriebsnr for vorgaenge searched');
        $this->info('Found: '.implode(',', $found));
        $this->info('Not found: '.implode(',', $notFound));
        Log::info('intranet-app-hwro:search-betriebsnr finished');
        SearchBetriebsNrFinished::dispatch('intranet-app-hwro:search-betriebsnr finished');
    }
}
