<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



	/**
 * @property mixed $isactive
 * @property mixed $title_en
 * @property mixed $title_hi
 * @property mixed $link
 * @property mixed $image_file
 * @property mixed $sponser_category
 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 */
class Sponsers extends Model
{
    use HasFactory;
}
