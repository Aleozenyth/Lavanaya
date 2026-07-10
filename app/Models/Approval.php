<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'sequence',
        'approver_role',
        'approver_user_id',
        'status',
        'notes',
        'acted_at',
    ];

    protected function casts(): array
    {
        return ['acted_at' => 'datetime'];
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }
}
