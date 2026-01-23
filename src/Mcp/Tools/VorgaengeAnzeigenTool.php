<?php

namespace Hwkdo\IntranetAppHwro\Mcp\Tools;

use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsOpenWorld]
class VorgaengeAnzeigenTool extends Tool
{
    protected string $name = 'vorgaenge_anzeigen';

    /**
     * The tool's description.
     */
    protected string $description = 'Zeigt Vorgänge aus dem HWRO-System an. Unterstützt optionale Filter für Vorgangsnummer, Betriebsnummer und ob eine Betriebsakte erstellt wurde.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $query = Vorgang::query();

        // Filter nach Vorgangsnummer
        if ($request->has('vorgangsnummer')) {
            $query->where('vorgangsnummer', $request->get('vorgangsnummer'));
        }

        // Filter nach Betriebsnummer
        if ($request->has('betriebsnr')) {
            $query->where('betriebsnr', $request->get('betriebsnr'));
        }

        // Filter nach Betriebsakte erstellt
        if ($request->has('betriebsakte_erstellt')) {
            $betriebsakteErstellt = $request->get('betriebsakte_erstellt');
            if ($betriebsakteErstellt === true) {
                $query->whereNotNull('betriebsakte_created_at');
            } else {
                $query->whereNull('betriebsakte_created_at');
            }
        }

        // Lade Beziehungen
        $vorgaenge = $query->with(['dokumente.schlagwort'])
            ->orderBy('vorgangsnummer', 'desc')
            ->get();

        // Formatiere die Ergebnisse
        $result = $vorgaenge->map(function ($vorgang) {
            return [
                'id' => $vorgang->id,
                'vorgangsnummer' => $vorgang->vorgangsnummer,
                'betriebsnr' => $vorgang->betriebsnr,
                'betriebsakte_created_at' => $vorgang->betriebsakte_created_at?->toIso8601String(),
                'created_at' => $vorgang->created_at->toIso8601String(),
                'updated_at' => $vorgang->updated_at->toIso8601String(),
                'dokumente_count' => $vorgang->dokumente->count(),
            ];
        });

        return Response::text(json_encode([
            'vorgaenge' => $result,
            'total' => $result->count(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, \Illuminate\JsonSchema\Types\Type>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'vorgangsnummer' => $schema->integer()
                ->description('Filter nach Vorgangsnummer (optional)')
                ->nullable(),

            'betriebsnr' => $schema->integer()
                ->description('Filter nach Betriebsnummer (optional)')
                ->nullable(),

            'betriebsakte_erstellt' => $schema->boolean()
                ->description('Filter nach ob eine Betriebsakte erstellt wurde. true = nur mit Betriebsakte, false = nur ohne Betriebsakte (optional)')
                ->nullable(),
        ];
    }
}
