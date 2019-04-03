<?php namespace Model\Core;


class Interest
{


    public static function add( $email ){
        $d = Db::write()->prepare('insert into interested (email,created_at) values (?,?) ');
        $d->execute( array($email,date("U")) );
    }

    
}