<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'phone', 'line1', 'line2', 'city', 'state', 'pincode', 'is_default'])]
class Address extends Model
{
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function oneLine(): string
    {
        return collect([$this->line1, $this->line2, $this->city, $this->state, $this->pincode])
            ->filter()
            ->implode(', ');
    }
}
