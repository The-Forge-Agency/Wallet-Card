<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageProcessor
{
    /**
     * Redimensionne et recompresse l'image uploadée via Imagick,
     * puis la stocke sur le disque public. Retourne le chemin relatif.
     */
    public function storeCardImage(UploadedFile $file, int $maxSize = 600): string
    {
        $image = new \Imagick($file->getRealPath());
        $image->setImageFormat('png');
        $image->setImageBackgroundColor(new \ImagickPixel('transparent'));

        // On contient l'image dans un carré maxSize sans la déformer.
        $image->thumbnailImage($maxSize, $maxSize, true);
        $image->stripImage();

        $path = 'cards/'.Str::uuid()->toString().'.png';
        Storage::disk('public')->put($path, $image->getImageBlob());

        $image->clear();

        return $path;
    }

    /**
     * Génère une miniature PNG carrée pour le pass Apple (thumbnail.png : 90x90).
     * Écrit le fichier dans $destination et retourne son chemin absolu.
     */
    public function makePassThumbnail(string $publicImagePath, string $destination, int $size = 90): string
    {
        $source = Storage::disk('public')->path($publicImagePath);

        $image = new \Imagick($source);
        $image->setImageFormat('png');
        $image->thumbnailImage($size, $size, true);
        $image->stripImage();
        $image->writeImage($destination);
        $image->clear();

        return $destination;
    }
}
