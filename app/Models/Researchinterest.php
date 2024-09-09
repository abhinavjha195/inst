<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Add a basic where clause to the query.
 * 
 * 
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder join(string $table, string $first, string $operator = '=', string $second = null, string $type = 'inner', string $where = 'and')
 *
 * 
 * 
 * 
 * 
 * 
 * @property mixed $title
 * @property mixed $description
 * @property mixed $type
 * @property mixed $pi
 * @property mixed $journalname
 * @property mixed $journalcoference
 * @property mixed $bookpublisher
 * @property mixed $copi
 * @property mixed $amount
 * @property mixed $tenure
 * @property mixed $agency
 * @property mixed $isactive
 * @property mixed $sortorder
 * @property mixed $volumes
 * @property mixed $enddate
 * @property mixed $sectionid
 * @property mixed $userid
 * @property mixed $id
 * @property mixed $name
 * @property mixed $surname
 * @property mixed $sortorder
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 *
 */


class Researchinterest extends Model
{
    use HasFactory;
    // protected $fillable = [
        
    //     'order'
    // ];


    protected $fillable = [
        'order',
        
    ];

    
}
