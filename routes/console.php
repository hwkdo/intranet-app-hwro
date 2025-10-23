<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('intranet-app-hwro:search-betriebsnr')->everyFifteenMinutes();
