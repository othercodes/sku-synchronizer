<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class Product
 *
 * @property int $id
 * @property string $name
 * @property string $sku
 * @property string $status
 * @property string $created_at
 * @property Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'sku',
        'status',
    ];

    public function hasSKU(): bool
    {
        return ! empty($this->sku);
    }

    public function isPublished(): bool
    {
        return $this->status === 'publish';
    }
}
