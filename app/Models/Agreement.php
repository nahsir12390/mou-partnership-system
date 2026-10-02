<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number','title','partner_id','department_id','responsible_officer_id',
        'agreement_type','purpose','start_date','expiry_date','status','approval_stage',
        'renewal_status','notes','created_by',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'expiry_date' => 'date'];
    }

    public function partner(): BelongsTo { return $this->belongsTo(Partner::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function responsibleOfficer(): BelongsTo { return $this->belongsTo(User::class, 'responsible_officer_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approvalActions(): HasMany { return $this->hasMany(ApprovalAction::class)->latest(); }
    public function obligations(): HasMany { return $this->hasMany(Obligation::class)->orderByRaw('due_date IS NULL, due_date ASC'); }
    public function documents(): HasMany { return $this->hasMany(Document::class)->latest(); }
}