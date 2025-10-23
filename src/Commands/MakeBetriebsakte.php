<?php

namespace Hwkdo\IntranetAppHwro\Commands;

use Hwkdo\IntranetAppHwro\Events\MakeBetriebsakteStarted;
use Hwkdo\IntranetAppHwro\Events\MakeBetriebsakteFinished;
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
        MakeBetriebsakteStarted::dispatch('intranet-app-hwro:make-betriebsakte started');
        Log::info('intranet-app-hwro:make-betriebsakte finished');
        MakeBetriebsakteFinished::dispatch('intranet-app-hwro:make-betriebsakte finished');
    }
}
