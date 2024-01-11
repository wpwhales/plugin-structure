<?php

namespace WPWCore\Auth;

use WPWhales\Database\Eloquent\Model;

class User extends Model
{


    protected $table = "users";
    protected $primaryKey = "ID";
}
