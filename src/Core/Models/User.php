<?php

namespace WPWCore\Models;

use WPWCore\Auth\Authenticatable;
use WPWCore\Auth\Authorizable;
use WPWhales\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use WPWhales\Contracts\Auth\Authenticatable as AuthenticatableContract;
use WPWhales\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;



    protected $table = "users";
    protected $primaryKey = "ID";

    /**
     * Disable timestamps
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'user_registered',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'user_pass',
        'remember_token', // disabled via protected property
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_login',
        'user_pass',
        'user_nicename',
        'user_email',
        'user_url',
        'user_registered',
        'user_activation_key',
        'user_status',
        'display_name',
    ];


    /**
     * Accessor to update password via Auth scaffolding
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes[$this->getPasswordColumnKey()] = $value;
    }


    /**
     * Return the key used for email in wordpress schema
     *
     * @return string
     */
    public function getEmailColumnKey()
    {
        return "user_email";
    }

    /**
     * Return the key used for password in wordpress schema
     *
     * @return string
     */
    public function getPasswordColumnKey()
    {
        return "user_pass";
    }

    /**
     * Return password value
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->{$this->getPasswordColumnKey()};
    }



    public function meta(){

        return $this->hasMany(UserMeta::class,"user_id","ID");
    }


}
