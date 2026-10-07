<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Cleans up uploaded images on the public disk once nothing points at them.
 */
class StoredFiles
{
    /**
     * @param  iterable<int, string|null>  $paths
     */
    public static function delete(iterable $paths): void
    {
        foreach ($paths as $path) {
            if (is_string($path) && $path !== '' && ! str_contains($path, '..')) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Wire single-image cleanup for a model attribute (e.g. a category or banner image).
     */
    public static function cleanUp(string $modelClass, string $attribute): void
    {
        $modelClass::updated(function (Model $model) use ($attribute) {
            if ($model->wasChanged($attribute) && $model->getOriginal($attribute)) {
                self::delete([$model->getOriginal($attribute)]);
            }
        });

        $modelClass::deleted(fn (Model $model) => self::delete([$model->getAttribute($attribute)]));
    }
}
