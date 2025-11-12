<?php

namespace Hwkdo\IntranetAppHwro\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class VorgangPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        return 'vorgaenge/' . $media->model_id . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive/';
    }
}

