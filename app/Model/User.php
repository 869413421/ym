<?php


namespace App\Model;


use Core\lib\Model;

class User extends Model
{
    public $primaryKey = 'id';

    public $table = 'users';

    public $connection = 'default';

    public $timestamps = false;

    public $fillable = [
        'name', 'score'
    ];
}