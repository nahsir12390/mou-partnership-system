<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAction extends Model
{
    use HasFactory;

    protected $fillable = ['agreement_id','user_id','action','from_status','to_status','comment'];

    public function agreement(): BelongsTo { return $this->belongsTo(Agreement::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
