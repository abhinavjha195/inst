<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Add a basic where clause to the query.
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 *
 */


class Researchinterest extends Model
{
    use HasFactory;
    protected $fillable = [
        
        'order'
    ];
    
}
