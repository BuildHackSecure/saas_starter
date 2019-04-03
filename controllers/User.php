<?php namespace Controller\Core;


use Model\Core\Security;

class User
{

    private static $user = false;

    /**
     * @return \Model\Core\Account|boolean
     */
    public static function getCurrentUser(){
        return self::$user;
    }

    public static function setCurrentUser( \Model\Core\User $user ){
        self::$user = $user;
    }

    public static function profile(){
        $data = array(
            'errors'    =>  array(),
            'csrf'      =>  Security::createCSRFtoken( Token::getCurrentToken()->getUser() ),
            'level'     =>  Token::getCurrentToken()->getUser()->getLevel(),
            'email'     =>  Token::getCurrentToken()->getUser()->getEmail()
        );
        if( isset($_POST["new_email"],$_POST["new_cemail"]) ){
            if( isset($_POST["csrf"]) ){
                if( $csrf = Security::checkCSRF( Token::getCurrentToken()->getUser(),$_POST["csrf"] )  ){
                    $csrf->close();
                    if( filter_var($_POST["new_email"], FILTER_VALIDATE_EMAIL ) ){
                        if( $_POST["new_email"] == $_POST["new_cemail"]){
                            if( !\Model\Core\User::getByEmail( Token::getCurrentToken()->getUser()->getAccount(), $_POST["new_email"]) ){
                                Token::getCurrentToken()->getUser()->setEmail( $_POST["new_email"] );
                                \View::redirect('/profile?uemail=1');
                            }else{
                                $data["errors"][] = 'Email address already belongs to another user';
                            }
                        }else{
                            $data["errors"][] = 'New Email address and confirmation email address to not match';
                        }
                    }else{
                        $data["errors"][] = 'New Email is invalid';
                    }
                }else{
                    $data["errors"][] = 'Internal error, please try again';
                }
            }else{
                $data["errors"][] = 'Internal error, please try again';
            }
        }

        if( isset($_POST["new_pass"],$_POST["new_cpass"]) ){
            if( isset($_POST["csrf"]) ){
                if( $csrf = Security::checkCSRF( Token::getCurrentToken()->getUser(),$_POST["csrf"] )  ){
                    if( preg_replace('/[^0-9]/','',$_POST["new_pass"]) && preg_replace('/[^A-Z]/','',$_POST["new_pass"]) && preg_replace('/[^a-z]/','',$_POST["new_pass"]) && strlen($_POST["new_pass"]) > 7  ) {
                        if ($_POST["new_pass"] == $_POST["new_cpass"]) {
                            Token::getCurrentToken()->getUser()->setPassword($_POST["new_pass"]);
                            \View::redirect('/profile?upass=1');
                        } else {
                            $data["errors"][] = 'New Password and confirm password do not match';
                        }
                    }else{
                        $data["errors"][] = 'New Password must be at least 8 characters and contain one number and one capital letter';
                    }
                    $csrf->close();
                }else{
                    $data["errors"][] = 'Internal error, please try again';
                }
            }
        }



        \View::page('app/settings/profile',$data);
    }

    public static function hasPermission($str, $level){
        $resp = false;
        $binstr = str_pad( decbin($level),8,'0', STR_PAD_LEFT );
        $permissions = array();
        foreach( \Config::getPermissions() as $k=>$v ){
            $permissions[$v] = $k;
        }
        if( isset( $permissions[$str] ) ){
            $p = ( $permissions[$str] - ( ( $permissions[$str] * 2 ) + 1 ) );
            $resp = ( intval(substr($binstr,$p,1 ))) ? true : false;
        }
        return $resp;
    }

}