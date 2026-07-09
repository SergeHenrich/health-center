<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PharmacyDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'reference',
        'description',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'version',
        'uploaded_by',
        'medicine_id',
        'supplier_id',
        'expiry_date',
        'is_active',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'published_at' => 'datetime',
            'is_active' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
