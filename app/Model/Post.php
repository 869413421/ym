<?php


namespace App\Model;


use Core\lib\Model;

class Post extends Model
{
    public $primaryKey = 'id';

    public $table = 'posts';

    public $connection = 'db2';
}