<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;



/**

 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * @method static \Illuminate\Database\Eloquent\Builder orderBy(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder join(string $table, string $first, string $operator = '=', string $second = null, string $type = 'inner', string $where = 'and')
 * @method static \App\Models\myadmin\post|null find($id, $columns = ['*'])
 * 
 * @property mixed $id
 * @property  mixed $pagename_en
 * @property mixed $pagename_hi
 * @property mixed $target_blank
 * @property mixed $external_link
 * @property mixed $template
 * @property mixed $isactive
 * @property mixed $description_en
 * @property mixed $description_hi
 * @property mixed $pagetype
 * @property mixed $imageposition
 * @property mixed $meta_title
 * @property mixed $meta_description
 * @property mixed $meta_keyword
 * @property mixed $postdate
 * @property mixed $regpostdate
 * @property mixed $slug
 * @property mixed $you_tube_link
 * @property mixed $feature_image
 * @property mixed $pagebanner
 * @property mixed $user_id
 * @property mixed $sortorder
 * @property mixed $cif_image
 *
 */


class Post extends Model
{
    use HasFactory;
	
    use Sluggable;
	
	public function sluggable(): array{
		return [
			'slug' => [
				'source' =>'pagename'
			]
		];
	}
	public function user() {
        return $this->belongsTo(User::class);
    }
}
