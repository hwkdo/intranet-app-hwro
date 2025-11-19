<?php

namespace Hwkdo\IntranetAppHwro\Commands;

use Hwkdo\IntranetAppHwro\Models\IntranetAppHwroSettings;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanVorgaenge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'intranet-app-hwro:clean-vorgaenge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean vorgaenge which are older than X weeks and have a betriebsakte';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('intranet-app-hwro:clean-vorgaenge started');
        $settings = IntranetAppHwroSettings::current()?->settings;
        $target = today()->subWeeks($settings->scheduleDeleteVorgaengeAfterWeeks);
        
        $vorgaenge = Vorgang::whereNotNull('betriebsakte_created_at')->where('betriebsakte_created_at','<=', $target)->get();
        $vorgaenge->each(function ($vorgang) {
            $vorgang->delete();
        });
        $message = 'intranet-app-hwro:clean-vorgaenge - Vorgänge gelöscht: '.$vorgaenge->count();
        Log::info($message);
        Log::info('intranet-app-hwro:clean-vorgaenge finished');
    }
}
