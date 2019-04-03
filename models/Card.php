<?php namespace Model\Core;


class Card
{


    private $card;

    private function  __construct( $data ){
        $this->card = $data;
    }


    /**
     * @return int
     */
    public function getId(){
        return (int)$this->card["id"];
    }

    /**
     * @return int
     */
    public function getAccountId(){
        return (int)$this->card["account_id"];
    }

    /**
     * @return string
     */
    public function getType(){
        return $this->card["type"];
    }

    /**
     * @return string
     */
    public function getLastFour(){
        return $this->card["ending"];
    }

    /**
     * @return string
     */
    public function getExpiry(){
        return $this->card["expiry"];
    }

    /**
     * @return string
     */
    public function getToken(){
        return $this->card["token"];
    }


    /**
     * @param Account $account
     * @return bool|Card
     */
    public static function getByAccount(Account $account ){
        $resp = false;
        $d = Db::read()->prepare('select * from account_card where account_id = ? order by id DESC LIMIT 1 ');
        $d->execute( array($account->getId()) );
        if( $d->rowCount() == 1 ){
            $resp = new Card( $d->fetch() );
        }
        return $resp;
    }


    /**
     * @param Account $account
     * @param $type
     * @param $last4
     * @param $expiry
     * @param $token
     * @return int
     */
    public static function create(Account $account, $type, $last4, $expiry, $token ){
        $d = Db::write()->prepare('insert into account_card (account_id,type,ending,expiry,token) values (?,?,?,?,?) ');
        $d->execute( array($account->getId(), strtolower($type) ,$last4,$expiry,$token) );
        return (int)Db::write()->lastInsertId();
    }

    /**
     * @param $id
     * @return bool|Card
     */
    public static function get($id ){
        $resp = false;
        $d = Db::read()->prepare('select * from account_card where id = ? LIMIT 1 ');
        $d->execute( array($id) );
        if( $d->rowCount() == 1 ){
            $resp = new Card( $d->fetch() );
        }
        return $resp;
    }


}