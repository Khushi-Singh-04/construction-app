<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseDetailCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'house_detail_id', 'category_name'
    ];

    public function houseDetail()
    {
        return $this->belongsTo(HouseDetail::class);
    }

     protected $table = 'house_detail_categories';
    public function subCategories()
    {
        return $this->hasMany(HouseDetailSubCategory::class, 'category_id', 'id');
    }
}
