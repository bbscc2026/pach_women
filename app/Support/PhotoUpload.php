<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PhotoUpload
{
    public static function save(TemporaryUploadedFile $file, string $directory): string
    {
        if (! in_array(strtolower($file->getClientOriginalExtension()), ['heic', 'heif'])) {
            return $file->store($directory, 'public');
        }

        $image = null;
        try {
            $image = new \Imagick;
            $image->pingImage($file->getRealPath());
            if ($image->getImageWidth() * $image->getImageHeight() > 50000000) {
                throw new \RuntimeException('Photo too large');
            }
            $image->clear();
            $image->readImage($file->getRealPath().'[0]');
            if (method_exists($image, 'autoOrientImage')) {
                $image->autoOrientImage();
            } elseif (method_exists($image, 'autoOrient')) {
                $image->autoOrient();
            }
            if ($image->getImageWidth() > 2000 || $image->getImageHeight() > 2000) {
                $image->thumbnailImage(2000, 2000, true);
            }
            $image->setImageFormat('jpeg');
            $image->setImageCompressionQuality(85);
            $image->stripImage();
            $path = $directory.'/'.Str::uuid().'.jpg';
            if (! Storage::disk('public')->put($path, $image->getImageBlob())) {
                throw new \RuntimeException('Storage failed');
            }

            return $path;
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages(['data.images' => 'This iPhone photo could not be processed. Please choose a JPG photo or a smaller image.']);
        } finally {
            $image?->clear();
        }
    }
}
