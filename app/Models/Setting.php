<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Key/value store for settings edited in Admin → Site settings.
 * Keys mirror config/shop.php paths (e.g. "contact.phone" → config('shop.contact.phone')).
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    private const CACHE_KEY = 'shop.settings';

    /** Stored encrypted at rest. */
    private const ENCRYPTED = ['razorpay.secret', 'razorpay.webhook_secret'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    /**
     * All saved settings as [key => value], cached until the next save.
     *
     * @return array<string, mixed>
     */
    public static function allCached(): array
    {
        // On a fresh install the database (and the database cache) may not exist yet:
        // fall back to config defaults instead of breaking every request and artisan command.
        try {
            $cached = Cache::get(self::CACHE_KEY);

            if (is_array($cached)) {
                return $cached;
            }

            if (! Schema::hasTable('settings')) {
                return [];
            }

            $settings = static::query()->pluck('value', 'key')
                ->map(fn ($value, $key) => in_array($key, self::ENCRYPTED, true) && filled($value) ? self::decrypt($value) : $value)
                ->all();

            Cache::forever(self::CACHE_KEY, $settings);

            return $settings;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function saveMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, self::ENCRYPTED, true) && filled($value)) {
                $value = Crypt::encryptString($value);
            }

            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Copy saved settings over config('shop.*'). Empty values keep the config default.
     */
    public static function applyToConfig(): void
    {
        foreach (static::allCached() as $key => $value) {
            if ($value !== null && $value !== '' && $value !== []) {
                config(['shop.'.$key => $value]);
            }
        }
    }

    private static function decrypt(string $value): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return null;
        }
    }
}
