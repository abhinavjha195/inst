<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * @property mixed $subtitle
 * @property mixed $name
 * @property mixed $id
 * @property mixed $designation
 * @property mixed $description
 * @property mixed $descriptionOne
 * @property mixed $feature_img
 * @property mixed $feature_image
 * @property mixed $isactive
 * @property mixed $type
 * @property mixed $extrainfo
 * @property mixed $model
 * @property mixed $catid
 * @property mixed $user_id
 * @property mixed $postenddate
 * @property mixed $postdate
 * @property mixed $make
 * @property mixed $pdfone
 * @property mixed $pdftwo
 * @property mixed $pdfthree
 * @property mixed $order
 * 
 * 
 * 
 *
 * Add a basic where clause to the query.
 *
 * @method static \Illuminate\Database\Eloquent\Builder where($column, $operator = null, $value = null)
 *  @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 * @method static \App\Models\myadmin\Coordinator|null find($id, $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder join(string $table, string $first, string $operator = '=', string $second = null, string $type = 'inner', string $where = 'and')
 * 
 * 
 * 
 * 
 */


class Coordinator extends Model
{
    use HasFactory;
    protected $fillable = [
        
        'order','subtitle'
    ];
}
