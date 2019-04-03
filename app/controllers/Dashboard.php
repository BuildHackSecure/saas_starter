<?php namespace Controller\App;


use Controller\Core\Token;

class Dashboard
{

    public static function view(){

        $data = array(
            'level'         =>  Token::getCurrentToken()->getUser()->getLevel(),
            'errors'        =>  array(),
        );
        \View::page('Core/app/dashboard',$data);
    }


}