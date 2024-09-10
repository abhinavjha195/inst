<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



	/**
     * 
     * 
     *  @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null, string $operator = '=')
 * @method static int count()
 * @method static \App\Models\myadmin\Sponsers|null find($id)
 * 
 * @property mixed $isactive
 * @property mixed $title_en
 * @property mixed $title_hi
 * @property mixed $link
 * @property mixed $image_file
 * @property mixed $sponser_category
 * @property mixed $sortorder
 * 
 *
 */
class Sponsers extends Model
{
    use HasFactory;
}
