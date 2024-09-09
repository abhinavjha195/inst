<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

	/**
	 * 
	 * 
	 * * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * 
 * 
 * 
 * @property mixed $isactive
 * @property mixed $user_id
 * @property mixed $catname
 * @property mixed $parentid
 * @property mixed $type
 * 
 * 
 * 
 */

class category extends Model
{
    use HasFactory;


	
	public function faqs(){
		
		return $this->belongsTo(FAQs::class);
		
	}
}
