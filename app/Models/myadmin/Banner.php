<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property mixed $id
 * @property  mixed $title_en
 * @property mixed $title_hi
 * @property mixed $titletwo_en
 * @property mixed $titletwo_hi
 * @property mixed $bannerimage
 * @property mixed $isactive
 * @property mixed $description_en
 * @property mixed $description_hi
 * @property mixed $user_id
 * @property mixed $journalname
 * @property mixed $agency
 * @property mixed $volumes
 * @property mixed $userid
 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 * 
 * 
 */
class Banner extends Model
{
    use HasFactory;

   

    
	
    protected $fillable = [
        'title_en',
        'title_hi',
        'titletwo_en',
        'titletwo_hi',
        'bannerimage',
        'isactive',
        'description_en',
        'description_hi',
        'user_id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
