<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchTranslation extends Model
{
    protected $fillable = [
        'merch_id',
        'language_id',
        'name',
    ];

    public function merch(): BelongsTo
    {
        return $this->belongsTo(Merch::class);
    }
}
