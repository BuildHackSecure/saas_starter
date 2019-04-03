<?php namespace Model\Core;


class Group
{

    private $group;

    private function __construct( $data ){
        $this->group = $data;
    }

    public function getId(){
        return (int)$this->group["id"];
    }

    public function getName(){
        return $this->group["name"];
    }

    public function getMailbox(){
        return $this->group["mailbox"];
    }

    /**
     * @return User[]
     */
    public function getGroupMembers(){
        if( !isset($this->group["members"]) ){
            $this->group["members"] = array();
            $d = Db::read()->prepare('select * from group_members where group_id = ? ');
            $d->execute( array($this->getId()) );
            while( $u = $d->fetch() ){
                if( $user = User::getById( $u["user_id"] ) ){
                    $this->group["members"][] = $user;
                }
            }
        }
        return $this->group["members"];
    }

    /**
     * @param Account $account
     * @param $name
     * @return bool|Group
     */
    public static function getGroupByMailbox(Account $account, $name ){
        $resp = false;
        $d = Db::read()->prepare('select * from group where account_id = ? and mailbox = ? LIMIT 1 ');
        $d->execute( array($account->getId(),$name) );
        if( $d->rowCount() == 1 ){
            $resp = new Group( $d->fetch() );
        }
        return $resp;
    }

    /**
     * @param Account $account
     * @param $id
     * @return bool|Group
     */
    public static function getGroupById(Account $account, $id ){
        $resp = false;
        $d = Db::read()->prepare('select * from `group` where account_id = ? and id = ? LIMIT 1 ');
        $d->execute( array($account->getId(),$id) );
        if( $d->rowCount() == 1 ){
            $resp = new Group( $d->fetch() );
        }
        return $resp;
    }

    /**
     * @param Account $account
     * @return Group[]
     */
    public static function getByAccount( Account $account ){
        $resp = array();
        $d = Db::read()->prepare('select * from `group` where account_id = ? order by name ');
        $d->execute( array($account->getId()) );
        while( $a = $d->fetch() ){
            $resp[] = new Group( $a );
        }
        return $resp;
    }

}