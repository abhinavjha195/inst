<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;





/**
 * @property mixed $albumid
 * @property  mixed $tititle
 * @property mixed $feature_image
 * 
 * @property mixed $isactive

 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 * 
 * 
 */



class Albumimage extends Model
{
    use HasFactory;
	
	public function user() {
        return $this->belongsTo(User::class);
    }
	protected $fillable = ['albumid'];
}
