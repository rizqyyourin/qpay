<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'price', 'discount', 'description', 'image', 'stock'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
