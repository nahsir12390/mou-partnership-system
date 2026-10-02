<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Obligation extends Model
{
    use HasFactory;

    protected $fillable = [
        'agreement_id','title','description','type','department_id','responsible_officer_id',
        'due_date','priority','status','progress','completion_notes','completed_at','created_by',
    ];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'completed_at' => 'datetime', 'progress' => 'integer'];
    }

    public function agreement(): BelongsTo { return $this->belongsTo(Agreement::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function responsibleOfficer(): BelongsTo { return $this->belongsTo(User::class, 'responsible_officer_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && ! in_array($this->status, ['completed','cancelled'], true);
    }
}