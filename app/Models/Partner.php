<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category', 'country', 'state', 'city', 'address', 'website',
        'contact_name', 'contact_email', 'contact_phone', 'status', 'notes', 'created_by',
    ];

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function agreements(): HasMany { return $this->hasMany(Agreement::class); }
}
