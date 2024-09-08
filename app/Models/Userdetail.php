<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Userdetail extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'userid',
        'designation',
        'profilepic',
        'sectionid',
        'googlelink',
        'personalgroupinfo',
        'aboutme'
    ]; 
}
