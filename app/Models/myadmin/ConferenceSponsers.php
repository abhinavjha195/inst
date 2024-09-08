<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConferenceSponsers extends Model
{
    use HasFactory;
    protected $table = 'conferences_sponser';

    public function sponser()
    {
        return $this->hasOne(Sponsers::class,'id','sponser_id');
    }
}
