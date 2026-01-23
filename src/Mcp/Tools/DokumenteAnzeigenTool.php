<?php

namespace Hwkdo\IntranetAppHwro\Mcp\Tools;

use Hwkdo\IntranetAppHwro\Models\Dokument;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsOpenWorld;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly]
#[IsOpenWorld]
class DokumenteAnzeigenTool extends Tool
{
    protected string $name = 'dokumente_anzeigen';

    /**
     * The tool's description.
     */
    protected string $description = 'Zeigt Dokumente aus dem HWRO-System an. Unterstützt optionale Filter für Vorgang-ID und Schlagwort-ID.';

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $query = Dokument::query();

        // Filter nach Vorgang-ID
        if ($request->has('vorgang_id')) {
            $query->where('vorgang_id', $request->get('vorgang_id'));
        }

        // Filter nach Schlagwort-ID
        if ($request->has('schlagwort_id')) {
            $query->where('schlagwort_id', $request->get('schlagwort_id'));
        }

        // Lade Beziehungen
        $dokumente = $query->with(['vorgang', 'schlagwort', 'media'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Formatiere die Ergebnisse
        $result = $dokumente->map(function ($dokument) {
            $media = $dokument->getFirstMedia();

            return [
                'id' => $dokument->id,
                'vorgang_id' => $dokument->vorgang_id,
                'vorgangsnummer' => $dokument->vorgang?->vorgangsnummer,
                'schlagwort_id' => $dokument->schlagwort_id,
                'schlagwort' => $dokument->schlagwort?->schlagwort,
                'media' => $media ? [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'mime_type' => $media->mime_type,
                    'size' => $media->size,
                    'created_at' => $media->created_at->toIso8601String(),
                ] : null,
                'created_at' => $dokument->created_at->toIso8601String(),
                'updated_at' => $dokument->updated_at->toIso8601String(),
            ];
        });

        return Response::text(json_encode([
            'dokumente' => $result,
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
            'vorgang_id' => $schema->integer()
                ->description('Filter nach Vorgang-ID (optional)')
                ->nullable(),

            'schlagwort_id' => $schema->integer()
                ->description('Filter nach Schlagwort-ID (optional)')
                ->nullable(),
        ];
    }
}
