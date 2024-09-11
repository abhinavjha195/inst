<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class conferenceSpeakers extends Model
{
    use HasFactory;
    protected $table =

    'conferences_speakers';

     public function users()
     {

        return $this->hasOne(User::class,'id','sponser_id');

    }

    public function userdetail()
    {
        return $this->hasOne(Userdetail::class,'userid','conference_id');

    }

    public function speakers()
     {
        return $this->hasOne(Speakers::class,'id','sponser_id');
        
     }
}
