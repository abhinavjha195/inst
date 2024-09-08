<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class conferenceSpeakers extends Model
{
    use HasFactory;
    protected $table =

    'conferences_speakers';

     public function speakers()
     {
        return $this->hasOne(Speakers::class,'id','sponser_id');
        
     }

}
