<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**
 * Add a basic where clause to the query.
 *

 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 *

 * 
 * 
 * 
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
