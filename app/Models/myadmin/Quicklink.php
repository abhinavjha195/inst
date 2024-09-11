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
 * @property mixed $sortorder
 *  @property mixed $pageid
 * 
 * 
 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * @method static \App\Models\myadmin\Quicklink|null find($id, $columns = ['*'])
 */



 


class Quicklink extends Model
{
    use HasFactory;
}
