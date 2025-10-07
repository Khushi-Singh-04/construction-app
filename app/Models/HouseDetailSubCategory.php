<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseDetailSubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'sub_category_name', 'description', 'images'
    ];

    protected $casts = [
        'images' => 'array', // JSON images array
    ];
     protected $table = 'house_detail_sub_categories'; 

    public function category()
    {
        return $this->belongsTo(HouseDetailCategory::class, 'category_id');
    }
}
