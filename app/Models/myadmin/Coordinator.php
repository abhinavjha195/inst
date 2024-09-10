<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * 
 * @property mixed $name
 * @property mixed $designation
 * @property mixed $description
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
 * 
 * 
 *
 * Add a basic where clause to the query.
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * 
 * 
 * 
 * 
 */


class Coordinator extends Model
{
    use HasFactory;
    protected $fillable = [
        
        'order'
    ];
}
