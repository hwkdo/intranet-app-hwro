<?php

declare(strict_types=1);

namespace Hwkdo\IntranetAppHwro\Services;

use Hwkdo\BueLaravel\BueLaravel;
use Hwkdo\D3RestLaravel\Client;
use Hwkdo\D3RestLaravel\DTO\NewObjectDTO;
use Hwkdo\D3RestLaravel\Enums\DocTypeEnum;
use Hwkdo\D3RestLaravel\models\Handwerksrolle;
use Hwkdo\IntranetAppHwro\Models\Schlagwort;

class d3Service
{
    public function FormwerkEintragungToD3(int $nr, string $file, Schlagwort|string $schlagwort): NewObjectDTO
    {
        $bueBetrieb = app(BueLaravel::class)->getBetriebByBetriebsnr($nr);

        $schlagwortName = $schlagwort instanceof Schlagwort
            ? $schlagwort->schlagwort
            : $schlagwort;

        $belegtypHr = $schlagwort instanceof Schlagwort
            ? $schlagwort->resolveBelegtypHr()
            : Schlagwort::resolveBelegtypHrForName($schlagwort);

        $dok = new Handwerksrolle([
            'BetriebsNr' => $nr,
            'Straße' => $bueBetrieb->strasse.' '.$bueBetrieb->hausnummer,
            'PLZ' => $bueBetrieb->betr_plz,
            'Ort' => $bueBetrieb->betr_ort,
            'Name' => $bueBetrieb->name,
            'Belegtyp_HR' => $belegtypHr,
            'Belegdatum' => now()->format('d.m.Y'),
            'Erfassungsdatum' => now()->format('d.m.Y'),
            'Schlagwort' => $schlagwortName,
            'Matchcode' => [explode(' ', $bueBetrieb->name)[0]],
            'filename' => 'Antrag auf Eintragung_'.$nr.'.pdf',
        ]);

        return $dok->save(filepath: $file);
    }

    public function getD3OnlineEintragungByVorgangsnummer($vorgangsnummer)
    {
        return app(Client::class)->SearchResult(
            fulltext: $vorgangsnummer,
            doc_type: DocTypeEnum::HandwerksrolleOnline,
            raw: false
        );
    }

    public function getD3BetriebsakteByBetriebsnr($betriebsnr)
    {
        return app(Client::class)->SearchResult(
            fulltext: $betriebsnr,
            doc_type: DocTypeEnum::Handwerksrolle,
            raw: false
        );
    }
}
