<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Add a basic where clause to the query.
 *
 * @property mixed $customname
 * @property mixed $name
 * @property mixed $email
 * @property mixed $regno
 * @property mixed $personalemail
 * @property mixed $workingsince
 * @property mixed $enddate
 * @property mixed $corembrid
 * @property mixed $sectionid
 * @property mixed $interimage
 * @property mixed $userid
 * @property mixed $presentaffiliation
 * @property mixed $isactive

 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder join(string $table, string $first, string $operator = '=', string $second = null, string $type = 'inner', string $where = 'and')
 *

 * 
 * 
 * 
 * 
 */





class Researchgroup extends Model
{
    use HasFactory;

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtoupper($value);
    }
}
