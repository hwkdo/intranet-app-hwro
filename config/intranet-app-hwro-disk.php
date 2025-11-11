<?php
return 
         [
            'driver' => 'local',
            'root' => storage_path('app/public/apps/hwro'),
            'url' => env('APP_URL').'/storage/apps/hwro',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
         ];