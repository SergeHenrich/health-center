<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_request_id',
        'lab_exam_id',
        'status',
    ];

    public function labRequest(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class);
    }

    public function labExam(): BelongsTo
    {
        return $this->belongsTo(LabExam::class);
    }

    public function result(): BelongsTo
    {
        return $this->belongsTo(LabResult::class);
    }
}
