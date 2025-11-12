<?php

namespace Hwkdo\IntranetAppHwro\Models;

use Hwkdo\PdfRestLaravel\Facades\PdfRestLaravel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Dokument extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'intranet_app_hwro_dokuments';

    protected $fillable = ['vorgang_id', 'schlagwort_id'];

    protected function casts(): array
    {
        return [
            'vorgang_id' => 'integer',
            'schlagwort_id' => 'integer',
        ];
    }

    public function vorgang(): BelongsTo
    {
        return $this->belongsTo(Vorgang::class);
    }

    public function schlagwort(): BelongsTo
    {
        return $this->belongsTo(Schlagwort::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->singleFile()
            ->useDisk('intranet-app-hwro');
    }

    /**
     * Add media with automatic PDF conversion for non-PDF files
     */
    public function addMediaWithPdfConversion(string $file): \Spatie\MediaLibrary\MediaCollections\FileAdder
    {
        $mimeType = mime_content_type($file);

        // If already PDF, use normal addMedia
        if ($mimeType === 'application/pdf') {
            return $this->addMedia($file);
        }

        // Convert to PDF
        $convertedPdfPath = PdfRestLaravel::convertToPdfAndSave($file);

        // Add converted PDF
        $fileAdder = $this->addMedia($convertedPdfPath);

        // Clean up temporary converted file after adding
        register_shutdown_function(function () use ($convertedPdfPath) {
            if (file_exists($convertedPdfPath)) {
                @unlink($convertedPdfPath);
            }
        });

        return $fileAdder;
    }

    /**
     * Add media from string with automatic PDF conversion for non-PDF content
     */
    public function addMediaFromStringWithPdfConversion(string $content, string $filename): \Spatie\MediaLibrary\MediaCollections\FileAdder
    {
        // Check MIME type from content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $content);
        finfo_close($finfo);

        // If already PDF, use normal addMediaFromString
        if ($mimeType === 'application/pdf' || str_ends_with($filename, '.pdf')) {
            return $this->addMediaFromString($content)->usingFileName($filename);
        }

        // Create temporary file for conversion with correct file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $tempFile = tempnam(sys_get_temp_dir(), 'dokument_').'.'.$extension;
        file_put_contents($tempFile, $content);

        try {
            // Convert to PDF
            $convertedPdfPath = PdfRestLaravel::convertToPdfAndSave($tempFile);

            // Read converted PDF content
            $pdfContent = file_get_contents($convertedPdfPath);

            // Change filename extension to .pdf
            $pdfFilename = pathinfo($filename, PATHINFO_FILENAME).'.pdf';

            // Add converted PDF
            $fileAdder = $this->addMediaFromString($pdfContent)->usingFileName($pdfFilename);

            // Clean up temporary files
            @unlink($convertedPdfPath);
            @unlink($tempFile);

            return $fileAdder;
        } catch (\Exception $e) {
            // Clean up temporary file on error
            @unlink($tempFile);
            throw $e;
        }
    }
}
