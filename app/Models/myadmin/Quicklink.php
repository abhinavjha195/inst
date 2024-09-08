<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


	/**
 * @property mixed $isactive
 * @property mixed $name
 * @property mixed $url
 * @property mixed $type
 * @property mixed $user_id
 * @property mixed $page_id
 * 
 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 */

class Quicklink extends Model
{
    use HasFactory;
}
