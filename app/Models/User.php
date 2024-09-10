<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;



/**
 * Add a basic where clause to the query.
 * 
 * 

 *
 * @property mixed $password
 * @property mixed $roles
 * @property mixed $email
 * @property mixed $isactive
 *  @property mixed $id
 *  @property mixed $name
 *  @property mixed $sirname
 *  @property mixed $ispasswordchange
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 *

 * Add a basic where clause to the query.
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $column, mixed $value = null)
 * 
 * 
 * 
 *  @method static \App\Models\User findOrFail(mixed $id)
 *  @method static \Illuminate\Database\Eloquent\Builder join(string $table, string $first, string $operator = null, string $second = null, string $type = 'inner', bool $where = false)
 * 
 * Finds a user by their primary key. If no user is found, an exception is thrown.
 * 
 * @param int $id The primary key of the user to find.
 * @return \App\Models\User
 * 
 * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
 * 
 *
 
 */


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'ispasswordchange',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function banners(){
        return $this->hasMany(Banner::class);
    }

    // public function setNameAttribute($value)
    // {
    //     $this->attributes['name'] = strtoupper($value);
    // }

	
	
}
