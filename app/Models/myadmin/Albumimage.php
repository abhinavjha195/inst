<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;





/**
 * @property mixed $albumid
 * @property  mixed $tititle
 * @property  mixed $photoname
 * @property mixed $feature_image
 * 
 * @property mixed $isactive
 * @property mixed $sortorder

 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 * @method static \App\Models\myadmin\Albumimage|null find($id)
 * 
 *
 * Add a basic where clause to the query.
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * 
 * 
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
