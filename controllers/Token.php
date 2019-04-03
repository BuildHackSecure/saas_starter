<?php namespace Controller\Core;


use Model\Core\Audit;

class Token
{

    private static $token = false;

    public static function logout(){
        $token = self::getCurrentToken();
        Audit::add( Account::getCurrentAccount(), 'user', $token->getUser()->getId(),'logout','');
        $token->disable();
        setcookie('token','',time()-3600);
        unset($_COOKIE["token"]);
        \View::redirect('/');
    }


    /**
     * @return \Model\Core\Token|bool
     */
    public static function getCurrentToken(){
        return self::$token;
    }
    
    public static function setCurrentToken( \Model\Core\Token $token ){
        self::$token = $token;
    }

    public static function logoutToReset(){
        $token = self::getCurrentToken();
        Audit::add( Account::getCurrentAccount(), 'user', $token->getUser()->getId(),'logout','');
        $token->disable();
        setcookie('token','',time()-3600);
        unset($_COOKIE["token"]);
        \View::redirect( $_SERVER["REQUEST_URI"] );
    }

    public static function logoutToInvite(){
        $token = self::getCurrentToken();
        Audit::add( Account::getCurrentAccount(), 'user', $token->getUser()->getId(),'logout','');
        $token->disable();
        setcookie('token','',time()-3600);
        unset($_COOKIE["token"]);
        \View::redirect( $_SERVER["REQUEST_URI"] );
    }
    
}