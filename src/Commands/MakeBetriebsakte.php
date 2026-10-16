<?php

namespace Hwkdo\IntranetAppHwro\Commands;

use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MakeBetriebsakte extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intranet-app-hwro:make-betriebsakte';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search for betriebsakten for vorgaenge which dont have a betriebsakte';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('intranet-app-hwro:make-betriebsakte started');

        $vorgaenge = Vorgang::whereNull('betriebsakte_created_at')->whereNotNull('betriebsnr')->get();
        $canMakeBetriebsakte = $vorgaenge->filter(function ($vorgang) {
            return $vorgang->canMakeBetriebsakte;
        });
        $total = $canMakeBetriebsakte->count();
        $success = 0;
        $errors = [];
        foreach ($canMakeBetriebsakte as $vorgang) {
            $result = $vorgang->makeD3BetriebsakteFromLocal();
            if ($result['success']) {
                $success++;
            } else {
                $errors[] = $result['message'];
            }
        }
        if ($total === 0) {
            $message = 'Betriebsakten-Erstellung abgeschlossen: Keine Vorgänge zum Erstellen gefunden';
        } else {
            $message = sprintf(
                'Betriebsakten-Erstellung abgeschlossen: %d von %d erfolgreich erstellt%s',
                $success,
                $total,
                count($errors) > 0 ? ' ('.count($errors).' Fehler)' : ''
            );
        }

        Log::info($message);
    }
}
