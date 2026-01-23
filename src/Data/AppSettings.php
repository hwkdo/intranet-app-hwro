<?php

namespace Hwkdo\IntranetAppHwro\Data;

use Hwkdo\IntranetAppBase\Data\BaseAppSettings;
use Hwkdo\IntranetAppHwro\Data\Attributes\Description;

class AppSettings extends BaseAppSettings
{
    public function __construct(
        #[Description('Aktiviert die automatische Suche nach Betriebsnummern in den Terminen')]
        public bool $scheduleSearchBetriebsnr = true,
        
        #[Description('Aktiviert die automatische Erstellung von Betriebsakten')]
        public bool $scheduleMakeBetriebsakte = true,

        #[Description('Aktiviert die automatische Löschung von Vorgängen nach einem bestimmten Zeitraum')]
        public bool $scheduleDeleteVorgaenge = true,

        #[Description('Automatische Löschung von Vorgängen nach X Wochen')]
        public int $scheduleDeleteVorgaengeAfterWeeks = 12,
        
        #[Description('Intervall in Minuten für die Suche nach Betriebsnummern (Standard: 15 Minuten)')]
        public int $scheduleSearchBetriebsnrIntervalMinutes = 15,
        
        #[Description('Intervall in Minuten für die Erstellung von Betriebsakten (Standard: 15 Minuten)')]
        public int $scheduleMakeBetriebsakteIntervalMinutes = 15,

        #[Description('Das Standard-Schlagwort für die Erstellung von Betriebsakten, wenn kein Filename passend ist')]
        public string $defaultSchlagwort = 'Antrag auf Eintragung',

        #[Description('OpenWebUi-Modell für KI Chat')]
        public string $openWebUiModel = 'intranet-app-hwro',
    ) {}

}
