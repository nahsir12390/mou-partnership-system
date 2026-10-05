<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property CarbonImmutable|null $previous_expiry_date
 * @property CarbonImmutable|null $new_expiry_date
 * @property CarbonImmutable $decision_date
 */
class RenewalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'agreement_id', 'previous_expiry_date', 'new_expiry_date', 'decision',
        'decision_date', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'previous_expiry_date' => 'date',
            'new_expiry_date' => 'date',
            'decision_date' => 'date',
        ];
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
