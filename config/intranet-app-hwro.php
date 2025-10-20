<?php

// config for Hwkdo/IntranetAppHwro
return [
    'roles' => [
        'admin' => [
            'name' => 'App-Hwro-Admin',
            'permissions' => [
                'see-app-hwro',
                'manage-app-hwro',
            ]
        ],
        'user' => [
            'name' => 'App-Hwro-Benutzer',
            'permissions' => [
                'see-app-hwro',                
            ]
        ],
    ]
];
