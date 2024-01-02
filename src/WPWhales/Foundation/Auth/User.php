<?php

namespace WPWhales\Foundation\Auth;

use WPWhales\Auth\Authenticatable;
use WPWhales\Auth\MustVerifyEmail;
use WPWhales\Auth\Passwords\CanResetPassword;
use WPWhales\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use WPWhales\Contracts\Auth\Authenticatable as AuthenticatableContract;
use WPWhales\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use WPWhales\Database\Eloquent\Model;
use WPWhales\Foundation\Auth\Access\Authorizable;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
}
