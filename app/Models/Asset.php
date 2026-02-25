<?php

namespace App\Models;

use App\Models\Concerns\HasStringPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory, HasStringPrimaryKey;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'original_file_name',
        'system_path',
        'thumbnail_path',
        'mime_type',
        'size',
    ];

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'image_id');
    }
}
