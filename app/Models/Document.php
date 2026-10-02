<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = ['agreement_id','title','type','version','original_name','stored_name','path','mime_type','size','description','is_final','uploaded_by'];

    protected function casts(): array
    {
        return ['is_final' => 'boolean', 'version' => 'integer', 'size' => 'integer'];
    }

    public function agreement(): BelongsTo { return $this->belongsTo(Agreement::class); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function getHumanSizeAttribute(): string
    {
        if ($this->size < 1024) return $this->size.' B';
        if ($this->size < 1048576) return number_format($this->size / 1024, 1).' KB';
        return number_format($this->size / 1048576, 1).' MB';
    }
}