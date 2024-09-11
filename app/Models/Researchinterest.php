<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Add a basic where clause to the query.
 * 
 * 
 * @method static \Illuminate\Database\Eloquent\Builder where(array|string $conditions, mixed $value = null, string $operator = '=')
 * @method static \Illuminate\Database\Eloquent\Builder join(string $table, string $first, string $operator = '=', string $second = null, string $type = 'inner', string $where = 'and')
 *@method static \App\Models\Researchinterest|null find($id, $columns = ['*'])
 * @method static mixed max(string $column) Get the maximum value of a specified column.
 * 
 * 
 * 
 *  
 * @property mixed $title
 * @property mixed $description
 * @property mixed $type
 * @property mixed $pi
 * @property mixed $journalname
 * @property mixed $journalconference
 * @property mixed $bookpublisher
 * @property mixed $copi
 * @property mixed $sirname
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
 * @property mixed $postdate
 * 
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
