<?php

namespace App\Models\myadmin;

use App\Models\conferenceSpeakers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


	/**
     * 
     * 
     *  @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 *
 * 
 * 
* @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null)
 * 
 * 

 *
 * 

 * 
 *
 */

class Conferences extends Model
{
    use HasFactory;
    protected $table = 

        'conferences';

        public function conferencespeakers()
        {
            return $this->hasMany(conferenceSpeakers::class,'conference_id','id');
        }
    
        public function conferencesponsers()
        {
            return $this->hasMany(ConferenceSponsers::class,'conference_id','id');
    
        }
    
}
