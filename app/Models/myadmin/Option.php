<?php

namespace App\Models\myadmin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


/**

 * 
 * @property mixed $site_title
 * @property mixed $email
 * @property mixed $mobile
 * @property mixed $aboutsexcerts
 * @property mixed $aboutsexcerts_link
 * @property mixed $meta_title
 * @property mixed $meta_description
 * @property mixed $videourl
 * @property mixed $address
 * @property mixed $meta_keywords
 * @property mixed $coremembercontent
 * @property mixed $content
 * @property mixed $maplink
 * @property mixed $aboutheading
 * @property mixed $facebook
 * @property mixed $instagram
 * @property mixed $twitter
 * @property mixed $youtube
 * @property mixed $linkdin
 * @property mixed $user_id
 * @property mixed $featureimagetwo
 * @property mixed $featureimage
 * 
 * 
 * 
 * 
 * @method static \Illuminate\Database\Eloquent\Builder whereIn(string $column, mixed $values)
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 *@method static \App\Models\myadmin\Option|null find($id)
 * 
 * @property mixed $featureimage
 *
 */

class Option extends Model
{
    use HasFactory;
}
