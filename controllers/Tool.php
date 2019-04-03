<?php namespace Controller\Core;


use Model\Core\Security;

class Tool
{


    public static function randomHash(){
        $str = rand() . print_r($_SERVER, true) . print_r($_POST, true). print_r($_GET, true) .rand() . date("U");
        return base64_encode(hash('sha512', '16DC0CA139D6CF95'.$str.'D21BD40ACF060DA3' ));
    }

    public static function clearCSRF(){
        Security::clear();
    }

}