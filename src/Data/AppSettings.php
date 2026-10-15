<?php

namespace Hwkdo\IntranetAppHwro\Data;

use Hwkdo\IntranetAppBase\Data\BaseAppSettings;
use Hwkdo\IntranetAppHwro\Data\Attributes\Description;

class AppSettings extends BaseAppSettings
{
    public function __construct(
        #[Description('Das Standard-Schlagwort für die Erstellung von Betriebsakten, wenn kein Filename passend ist')]
        public string $defaultSchlagwort = 'Antrag auf Eintragung',

        #[Description('Standard-Belegtyp_HR für D3-Uploads, wenn beim Schlagwort kein Belegtyp hinterlegt ist')]
        public string $defaultBelegtypHr = 'Eintragungsverfahren',

        #[Description('OpenWebUi-Modell für KI Chat')]
        public string $openWebUiModel = 'intranet-app-hwro',
    ) {}

}
