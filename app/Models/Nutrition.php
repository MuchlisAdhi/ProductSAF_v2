<?php

namespace App\Models;

use App\Models\Concerns\HasStringPrimaryKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nutrition extends Model
{
    use HasFactory, HasStringPrimaryKey;

    public $timestamps = false;
    protected $table = 'nutritions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'product_id',
        'label',
        'value',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
