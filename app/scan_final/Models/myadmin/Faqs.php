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
 * 
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder join(string $table, string $first, string $operator = '=', string $second = null, string $type = 'inner', string $where = 'and')
 * @method static \App\Models\myadmin\Faqs|null find($id)
 * 
 */

class Faqs extends Model
{
    use HasFactory;
	
	public function category() {
		
		return $this->hasMany(category::class);
		
	}
}

