<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HouseDetail extends Model
{
    use HasFactory;

    protected $fillable = ['house_id', 'question', 'answer'];

    protected $casts = [
        'options' => 'array', // JSON → array
    ];

    public function house()
    {
        return $this->belongsTo(House::class);
    }

     protected $table = 'house_details';
    public function categories()
    {
        return $this->hasMany(HouseDetailCategory::class, 'house_detail_id','id');
    }
}
