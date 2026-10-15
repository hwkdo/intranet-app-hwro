<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppHwro\Background;

use Hwkdo\IntranetAppBase\Data\BackgroundActivityDefinition;
use Hwkdo\IntranetAppBase\Data\BackgroundActivityFrequency;
use Hwkdo\IntranetAppBase\Data\BackgroundActivityParameter;
use Hwkdo\IntranetAppBase\Enums\BackgroundActivityCadence;
use Hwkdo\IntranetAppHwro\IntranetAppHwro;

final class HwroBackgroundActivities
{
    /**
     * @return list<BackgroundActivityDefinition>
     */
    public static function definitions(): array
    {
        $id = IntranetAppHwro::identifier();
        $name = IntranetAppHwro::app_name();
        $all = BackgroundActivityCadence::cases();

        return [
            BackgroundActivityDefinition::scheduled(
                key: 'hwro.search-betriebsnr',
                label: 'Betriebsnummern suchen',
                appIdentifier: $id,
                appName: $name,
                command: 'intranet-app-hwro:search-betriebsnr',
                frequency: BackgroundActivityFrequency::everyMinutes(15),
                description: 'Sucht Betriebsnummern in den Terminen.',
                allowedCadences: $all,
                minEvery: 1,
                maxEvery: 60,
                sort: 10,
            ),
            BackgroundActivityDefinition::scheduled(
                key: 'hwro.make-betriebsakte',
                label: 'Betriebsakten erstellen',
                appIdentifier: $id,
                appName: $name,
                command: 'intranet-app-hwro:make-betriebsakte',
                frequency: BackgroundActivityFrequency::everyMinutes(15),
                description: 'Erstellt fehlende Betriebsakten.',
                allowedCadences: $all,
                minEvery: 1,
                maxEvery: 60,
                sort: 20,
            ),
            BackgroundActivityDefinition::scheduled(
                key: 'hwro.clean-vorgaenge',
                label: 'Alte Vorgänge löschen',
                appIdentifier: $id,
                appName: $name,
                command: 'intranet-app-hwro:clean-vorgaenge',
                frequency: BackgroundActivityFrequency::dailyAt('00:01'),
                description: 'Löscht Vorgänge mit Betriebsakte nach der Karenz.',
                allowedCadences: $all,
                parameters: [
                    new BackgroundActivityParameter(
                        key: 'after_weeks',
                        label: 'Löschen nach Wochen',
                        type: 'int',
                        default: 12,
                        min: 1,
                        max: 520,
                    ),
                ],
                sort: 30,
            ),
        ];
    }
}
