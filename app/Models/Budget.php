<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'year', 'month', 'allocated_amount', 'used_amount'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getRemainingAttribute(): float
    {
        return (float) $this->allocated_amount - (float) $this->used_amount;
    }

    public function hasEnoughBudget(float $amount): bool
    {
        return $this->remaining >= $amount;
    }
}
