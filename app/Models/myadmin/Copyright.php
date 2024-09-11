<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;




	/**
     * 
     * @property mixed $copyright
 * 
 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * 
 * @method static \App\Models\myadmin\Copyright|null find($id, $columns = ['*'])
 */




class Copyright extends Model
{
    use HasFactory;
    protected $table='copyright'; 
}
