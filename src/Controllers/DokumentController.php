<?php

namespace Hwkdo\IntranetAppHwro\Controllers;

use Hwkdo\IntranetAppHwro\Models\Dokument;
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
            echo $media->stream();
        }, $media->file_name, [
            'Content-Type' => $media->mime_type,
        ]);
    }
}

