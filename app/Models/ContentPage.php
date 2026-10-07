<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Info/policy page edited in Admin → Pages. Content is HTML from the rich editor and may
 * contain placeholders such as {phone} that are filled from the current settings.
 */
#[Fillable(['slug', 'title', 'content', 'sort_order', 'is_active'])]
class ContentPage extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('title');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return array<string, string>
     */
    public static function placeholders(): array
    {
        return [
            '{shop_name}' => (string) config('shop.name'),
            '{phone}' => (string) config('shop.contact.phone'),
            '{whatsapp}' => (string) config('shop.contact.whatsapp'),
            '{email}' => (string) config('shop.contact.email'),
            '{store}' => (string) config('shop.contact.store'),
            '{shipping_fee}' => inr(config('shop.shipping_fee')),
            '{free_shipping_over}' => inr(config('shop.free_shipping_over')),
        ];
    }

    public function renderedContent(): string
    {
        return strtr((string) $this->content, self::placeholders());
    }
}
