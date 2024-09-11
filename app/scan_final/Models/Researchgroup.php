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
 * @property mixed $description

 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(array|string $conditions, mixed $value = null, string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder join(string $table, string $first, string $operator = '=', string $second = null, string $type = 'inner', string $where = 'and')
 *
* @method static \App\Models\Researchgroup|null find($id, $columns = ['*'])
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
