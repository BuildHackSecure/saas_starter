<?php namespace Model\Core;


use Controller\Core\Tool;

class Token
{

    private $token;

    private function __construct($id, $admin, User $user,$hash, $disabled ){
        $this->token = array(
            'id'    =>  $id,
            'admin' =>  $admin,
            'user'  =>  $user,
            'hash'  =>  $hash
        );
    }

    /**
     * @return int
     */
    public function getId(){
        return (int)$this->token["id"];
    }

    /**
     * @return bool
     */
    public function isAdmin(){
        return ( ($this->token["admin"]) ) ? true : false;
    }

    /**
     * @return User
     */
    public function getUser(){
        return $this->token["user"];
    }

    /**
     * @return string
     */
    public function getHash(){
        return $this->token["hash"];
    }

    public function disable(){
        $d = Db::write()->prepare('update token SET disabled = 1 where id = ? ');
        $d->execute( array($this->getId()) );
    }

    /**
     * @param $hash
     * @return bool|Token
     */
    public static function getByHash($hash){
        $resp = false;
        $d = Db::read()->prepare('select * from token where hash = ? and disabled = 0 LIMIT 1 ');
        $d->execute( array($hash) );
        if( $d->rowCount() == 1 ){
            $t = $d->fetch();
            if( $user = User::getById( $t["user_id"] ) ) {
                if( !$user->isDisabled() ) {
                    $resp = new Token($t["id"], $t["admin"], $user, $t["hash"] , false);
                    if( !$resp->isAdmin() ) {
                        $d = Db::write()->prepare('INSERT INTO user_activity (user_id,timestamp) VALUES(?,?) ON DUPLICATE KEY UPDATE timestamp = ? ');
                        $d->execute(array($user->getId(), date("U"), date("U")));
                    }
                }
            }
        }
        return $resp;
    }


    /**
     * @param User $user
     * @param bool $admin
     * @return Token
     */
    public static function create(User $user, $admin = false ){
        $hash = Tool::randomHash();
        $admin = ( intval($admin) ) ? 1 : 0;
        $d = Db::write()->prepare('insert into token (admin,user_id,hash,created_at) values (?,?,?,?)  ');
        $d->execute( array($admin,$user->getId(),$hash,date("U")) );
        return new Token( Db::write()->lastInsertId(), $admin, $user, $hash, false  );
    }


}