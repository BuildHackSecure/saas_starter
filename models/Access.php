<?php namespace Model\Core;

use Controller\Core\Tool;

class Access
{

    private $id,$user,$hash;

    private function __construct( $id, User $user, $hash ){
        $this->id = $id;
        $this->user = $user;
        $this->hash = $hash;
    }

    /**
     * @return User
     */
    public function getUser(){
        return $this->user;
    }

    /**
     * @return int
     */
    public function getId(){
        return (int)$this->id;
    }

    public function getHash(){
        return $this->hash;
    }

    public function used(){
        $d = Db::write()->prepare('update access SET used = ? where id = ? ');
        $d->execute( array(date("U"),$this->getId()) );
    }

    /**
     * @param $str
     * @return bool|Access
     */
    public static function checkToken( $str ){
        $resp = false;
        $d = Db::read()->prepare('select id,user_id,hash from access where hash = ? and used = 0 LIMIT 1 ');
        $d->execute( array($str) );
        if( $d->rowCount() == 1 ){
            $a = $d->fetch();
            if( $user = User::getById( $a["user_id"] ) ){
                if( !$user->isDisabled() ){
                    $resp = new Access( $a["id"], $user, $a["hash"] );
                }
            }
        }
        return $resp;
    }

    /**
     * @param User $user
     * @return Access
     */
    public static function create(User $user ){
        $hash = Tool::randomHash();
        $d2 = Db::write()->prepare('insert into access (hash,user_id,created_at) values (?,?,?) ');
        $d2->execute(array( $hash , $user->getId(), date("U")));
        return  new Access( Db::write()->lastInsertId(), $user, $hash );
    }



}