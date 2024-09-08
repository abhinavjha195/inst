<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

	/**
 * @property mixed $isactive
 * @property mixed $question
 * @property mixed $answer
 * @property mixed $catid
 * @property mixed $user_id
 * 
 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 */

class Faqs extends Model
{
    use HasFactory;
	
	public function category() {
		
		return $this->hasMany(category::class);
		
	}
}

