<?php namespace Controller\App;

use Model\Core\Interest;

class Website
{

    public static function home(){
        $data = array(
            'email_wrong'   =>  false
        );
        if( isset($_POST["let_me_know"]) ){
            if (filter_var($_POST["let_me_know"], FILTER_VALIDATE_EMAIL)) {
                Interest::add($_POST["let_me_know"]);
                \View::redirect('/thank-you');
            }
            $data["email_wrong"] = true;
        }
        \View::page('App/website/home',$data);
    }


    public static function thanks(){
        $data = array();
        \View::page('App/website/thanks',$data);
    }

}