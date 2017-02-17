<?php

namespace phalconer\user\model;

use Phalcon\Mvc\Model;

class User extends Model
{
    public $id;

    public $name;

    public $email;
    
    public $password_hash;
}
