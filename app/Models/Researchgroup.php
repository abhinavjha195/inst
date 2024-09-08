<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Add a basic where clause to the query.
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * 
 * 
 */


 /**
 * Find a record by its primary key.
 *
 * @method static \Illuminate\Database\Eloquent\Model|null find(mixed $id, array $columns = ['*'])
 *
 * @param mixed $id The primary key of the record you want to retrieve. This can be an integer or a string.
 * @param array $columns The columns to select from the database. Defaults to `['*']`, which means all columns.
 * @return \Illuminate\Database\Eloquent\Model|null Returns the model instance if found, or `null` if no record is found.
 */


class Researchgroup extends Model
{
    use HasFactory;

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }
}
