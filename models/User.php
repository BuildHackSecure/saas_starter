<?php namespace Model\Core;


class UserAssignTo
{

    private $assign_to;

    public function __construct( $id, $type, $name ){
        $this->assign_to = array(
            'id'    =>  $id,
            'type'  =>  $type,
            'name'  =>  $name
        );
    }

    /**
     * @return int
     */
    public function getId(){
        return (int)$this->assign_to["id"];
    }

    /**
     * @return string
     */
    public function getType(){
        return $this->assign_to["type"];
    }

    /**
     * @return string
     */
    public function getName(){
        return $this->assign_to["name"];
    }

}


class User
{

    private $user;

    private function __construct( $data ){
        $this->user = $data;
    }



    public function resetPassword(){
        $resp = false;
        if( !$this->isDisabled() ) {
            if (filter_var($this->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $d = Db::write()->prepare('select * from access where user_id = ? and created_at > ? ');
                $d->execute(array($this->getId(), (date("U") - 300)));
                if ($d->rowCount() == 0) {
                    $access = Access::create( $this );
                    try{
                        $email = new Email($this->getEmail(),'noreply@'.\Config::getSaasDomain(),'password_reset',array(
                            'logo.src'          =>  \Config::getEmailHeaderURL(),
                            'logo.width'        =>  \Config::getEmailHeaderWidth(),
                            'logo.height'       =>  \Config::getEmailHeaderHeight(),
                            'account.name'      =>  $this->getAccount()->getCompanyName(),
                            'saas.domain'       =>  \Config::getSaasDomain(),
                            'account.domain'    =>  $this->getAccount()->getDomain(),
                            'reset.hash'        =>  $access->getHash(),
                            'app_name'          =>  \Config::getSaasName()
                        ));
                        $email->send();
                    }catch (\Exception $e){}
                    Audit::add( \Controller\Core\Account::getCurrentAccount(), 'user', $this->getId(), 'Password Reset Sent', '');
                    $resp = true;
                }
            }
        }
        return $resp;
    }

    /**
     * @return int
     */
    public function getId(){
        return (int)$this->user["id"];
    }

    /**
     * @return int
     */
    public function getLevel(){
        return (int)$this->user["level"];
    }

    /**
     * @return int
     */
    public function getAccountId(){
        return (int)$this->user["account_id"];
    }

    /**
     * @return Account
     */
    public function getAccount(){
        if( !isset($this->user["account"]) ){
            $this->user["account"] = Account::getById( $this->getAccountId() );
        }
        return $this->user["account"];
    }

    /**
     * @return bool
     */
    public function isDisabled(){
        return ( intval($this->user["disabled"]) ) ? true : false;
    }
    
    public function getEmail(){
        return $this->user["email"];
    }

    public function getName(){
        return $this->user["name"];
    }

    public function setDisabled( $bool ){
        $d = Db::write()->prepare('update user SET disabled = ? where id = ? ');
        $d->execute( array( $bool, $this->getId() ) );
    }

    public function setEmail( $str ){
        $d = Db::write()->prepare('update user SET email = ? where id = ? ');
        $d->execute( array( $str, $this->getId() ) );
    }


    /**
     * @param $bool
     */
    public function setLevel($bool ){
        $d = Db::write()->prepare('update user SET level = ? where id = ? ');
        $d->execute( array( $bool, $this->getId() ) );
    }

    public function setPassword($password){
        $d = Db::write()->prepare('update user SET password = ? where id = ? ');
        $d->execute( array( self::hashIt($password), $this->getId() ) );
    }

    /**
     * @return bool|int
     */
    public function getLastActivity(){
        $resp = false;
        $d = Db::read()->prepare('select timestamp from user_activity where user_id = ? LIMIT 1 ');
        $d->execute( array($this->getId()) );
        if( $d->rowCount() == 1 ){
            $resp = (int)$d->fetchColumn();
        }
        return $resp;
    }

    public static function hashIt( $str ){
        return base64_encode(hash('sha512', '16DC0CA139D6CF95'.$str.'D21BD40ACF060DA3' ));
    }

    /**
     * @param Account $account
     * @param $username
     * @param $password
     * @return bool|User
     */
    public static function getByLogin(Account $account, $username, $password ){
        $resp = false;
        $d = Db::read()->prepare('select * from user where account_id = ? and email = ? and password = ? LIMIT 1');
        $d->execute( array($account->getId(),$username,self::hashIt($password) ) );
        if( $d->rowCount() == 1 ){
            $resp = new User( $d->fetch() );
        }
        return $resp;
    }


    /**
     * @param Account $account
     * @param $email
     * @return bool|User
     */
    public static function getByEmail(Account $account, $email ){
        $resp = false;
        $d = Db::read()->prepare('select * from user where account_id = ? and email = ? LIMIT 1');
        $d->execute( array($account->getId(),$email ) );
        if( $d->rowCount() == 1 ){
            $resp = new User( $d->fetch() );
        }
        return $resp;
    }

    public static function emailExists($email){
        $d = Db::read()->prepare('select * from user where email = ? LIMIT 1 ');
        $d->execute( array($email) );
        return ( $d->rowCount() ) ? true : false;
    }
    
    /**
     * @param $id
     * @return bool|User
     */
    public static function getById($id ){
        $resp = false;
        $d = Db::read()->prepare('select * from user where id = ? LIMIT 1 ');
        $d->execute( array($id) );
        if( $d->rowCount() == 1 ){
            $resp = new User( $d->fetch() );
        }
        return $resp;
    }



    public static function checkPassword($password, $confirm_password = false){
        $resp = true;
        if( $confirm_password !== false ){
            if( $password != $confirm_password ){
                $resp = false;
            }
        }
        if( preg_replace('/[^0-9]/','',$password) && preg_replace('/[^A-Z]/','',$password) && preg_replace('/[^a-z]/','',$password) && strlen($password) > 5  ) {
        }else{
            $resp = false;
        }
        return $resp;
    }


    /**
     * @param Account $account
     * @return User[]
     */
    public static function getByAccount(Account $account ){
        $resp = array();
        $d = Db::read()->prepare('select * from user where account_id = ? ');
        $d->execute( array($account->getId()) );
        while( $u = $d->fetch() ){
            $resp[] = new User( $u );
        }
        return $resp;
    }


    /**
     * @param Account $account
     * @param $email
     * @param $password
     * @param bool $level
     * @return User
     */
    public static function create(Account $account, $email, $password, $level ){
        $d = Db::write()->prepare('insert into user (account_id,email,password,level) values (?,?,?,?) ');
        $d->execute( array($account->getId(),$email,self::hashIt($password),$level  ) );
        return new User( array(
            'id'            =>  Db::write()->lastInsertId(),
            'account_id'    =>  $account->getId(),
            'disabled'      =>  false,
            'level'         =>  $level,
            'email'         =>  $email,
            'password'      =>  $password
        ));
    }



}