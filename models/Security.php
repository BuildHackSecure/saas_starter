<?php namespace Model\Core;


use Controller\Core\Tool;

class csrf
{

    private $csrf;

    public function __construct( $data ){
        $this->csrf = $data;
    }

    private function getId(){
        return $this->csrf["id"];
    }

    public function close(){
        $d = Db::write()->prepare('update csrf SET used = 1 where id = ? ');
        $d->execute( array($this->getId()) );
    }

}


class Security
{

    public static function checkCSRF( User $user, $token ){
        $d = Db::read()->prepare('select * from csrf where user_id = ? and token = ? and used = 0 LIMIT 1 ');
        $d->execute( array($user->getId(),$token) );
        return ( $d->rowCount() ) ? new csrf( $d->fetch() ) : false;
    }

    public static function createCSRFtoken( User $user ){
        $token = Tool::randomHash();
        $d = Db::write()->prepare('insert into csrf (token,user_id,created_at) values (?,?,?)  ');
        $d->execute( array($token,$user->getId(),date("U")) );
        return $token;
    }

    public static function clear(){
        $d = Db::write()->prepare('delete from csrf where created_at < ? ');
        $d->execute( array( ( date("U") - 86700 ) ) );
    }

}