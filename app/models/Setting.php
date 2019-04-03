<?php namespace Model\App;

use Model\Core\Db;

class Test
{

    public static function test( ){
        $d = Db::read()->prepare('SQL STATEMENT HERE ');
        $d->execute( array() );
    }



}