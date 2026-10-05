<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id', 'agreement_id', 'event', 'subject_type', 'subject_id',
        'description', 'metadata', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }
}
