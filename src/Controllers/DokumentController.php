<?php

namespace Hwkdo\IntranetAppHwro\Controllers;

use Hwkdo\IntranetAppHwro\Models\Dokument;
use Hwkdo\IntranetAppHwro\Models\Vorgang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumentController
{
    public function download(Request $request, Dokument $dokument): StreamedResponse
    {
        // Prüfe Berechtigung
        Gate::authorize('see-app-hwro');

        // Hole das Media-Objekt
        $media = $dokument->getFirstMedia();

        if (! $media) {
            abort(404, 'Dokument nicht gefunden.');
        }

        // Download der Datei
        return response()->streamDownload(function () use ($media) {
            $stream = $media->stream();
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $media->file_name, [
            'Content-Type' => $media->mime_type,
        ]);
    }

    public function downloadGewan(Request $request, Vorgang $vorgang): StreamedResponse
    {
        // Prüfe Berechtigung
        Gate::authorize('see-app-hwro');

        // Hole das Media-Objekt
        $media = $vorgang->getFirstMedia('default');

        if (! $media) {
            abort(404, 'GEWAN-XML nicht gefunden.');
        }

        // Download der Datei
        return response()->streamDownload(function () use ($media) {
            $stream = $media->stream();
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, $media->file_name, [
            'Content-Type' => $media->mime_type,
        ]);
    }
}

