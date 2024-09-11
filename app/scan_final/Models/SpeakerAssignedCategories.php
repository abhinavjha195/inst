<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpeakerAssignedCategories extends Model
{
    use HasFactory;

    public function category(){
        return $this->hasOne(SpeakerCategories::class,'id','category_id');
    }

    // public function categoryord(){
    //     return $this->belongsTo(SpeakerCategories::class, 'id', 'category_id');
    // }
}
