<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


	/**
 * @property mixed $menuname
 * @property mixed $postid
 * @property mixed $parentid
 * @property mixed $user_id
 * 
 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 * 
 * 
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 */

class Menulevel extends Model
{
    use HasFactory;
}
