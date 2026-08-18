<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'related_type',
        'related_id',
        'ip_address',
    ];

    public static function record(
        string $action,
        string $description,
        ?string $relatedType = null,
        ?int $relatedId = null
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'ip_address' => request()->ip(),
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
