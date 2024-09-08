<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

	/**
 * @property mixed $isactive
 * @property mixed $userid
 * @property mixed $catname
 * @property mixed $parentid
 * @property mixed $type
 * 
 * 
 * 
 */

class category extends Model
{
    use HasFactory;


	
	public function faqs(){
		
		return $this->belongsTo(FAQs::class);
		
	}
}
