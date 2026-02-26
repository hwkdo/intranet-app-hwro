<?php

namespace Hwkdo\IntranetAppHwro\Tasks;

use Hwkdo\IntranetAppBase\Data\TaskItem;
use Hwkdo\IntranetAppBase\Interfaces\TaskProviderInterface;
use Hwkdo\IntranetAppHwro\IntranetAppHwro;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class BetriebsakteAusstehendTaskProvider implements TaskProviderInterface
{
    /**
     * Returns one TaskItem per Vorgang that has a betriebsnr but no betriebsakte yet.
     * Access is controlled by the TaskService, which only calls this provider for
     * users who have a role defined in IntranetAppHwro::roles_user() or roles_admin().
     *
     * @return Collection<int, TaskItem>
     */
    public function getTasksForUser(Authenticatable $user): Collection
    {
        return Vorgang::query()
            ->whereNotNull('betriebsnr')
            ->whereNull('betriebsakte_created_at')
            ->orderBy('vorgangsnummer')
            ->get()
            ->map(fn (Vorgang $vorgang) => new TaskItem(
                title: 'Betriebsakte erstellen',
                url: route('apps.hwro.vorgaenge.show', $vorgang),
                appIdentifier: IntranetAppHwro::identifier(),
                appName: IntranetAppHwro::app_name(),
                appIcon: IntranetAppHwro::app_icon(),
                description: 'Betriebsnr. '.$vorgang->betriebsnr.' · Vorgangsnr. '.$vorgang->vorgangsnummer,
                badge: 'Ausstehend',
                priority: 5,
            ));
    }

    public function getLabel(): string
    {
        return 'Betriebsakte ausstehend';
    }
}
