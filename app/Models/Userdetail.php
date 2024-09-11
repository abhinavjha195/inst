<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Add a basic where clause to the query.
 *

 *

 * 
 * @property mixed $aboutme
 * @property mixed $designation
 * @property mixed $personalgroupinfo
 * @property mixed $googlelink
 * @property mixed $profilepic
 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 *
* @method static \App\Models\Userdetail|null find($id, $columns = ['*'])
 
 * @method static \App\Models\Userdetail create(array $attributes = [])
 * 
 */

class Userdetail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'userid',
        'designation',
        'profilepic',
        'sectionid',
        'googlelink',
        'personalgroupinfo',
        'aboutme'
    ]; 
}
