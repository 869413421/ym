<?php


namespace App\Model;


use Core\lib\Model;

class User extends Model
{
    public $primaryKey = 'id';

    public $table = 'users';
}