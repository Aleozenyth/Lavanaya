<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    /**
     * Kategori PO Produk memakai jalur approval khusus (langsung Direktur).
     */
    public function isPoProduk(): bool
    {
        return $this->name === 'PO Produk';
    }
}
