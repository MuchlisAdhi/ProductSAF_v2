<?php

namespace App\Models;

use App\Models\Concerns\HasStringPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasStringPrimaryKey;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'sack_color',
        'category_id',
        'image_id',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Asset, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'image_id');
    }

    /**
     * @return HasMany<Nutrition, $this>
     */
    public function nutritions(): HasMany
    {
        return $this->hasMany(Nutrition::class);
    }
}
