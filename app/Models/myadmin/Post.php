<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

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
