<?php

class View {

    public static function page($file, $data = array()) {
        $base = '';
        if( substr($file,0,5) === 'Core/' ){
            $file = substr($file,5,1000 );
        }elseif ( substr($file,0,4) === 'App/' ){
            $file = substr($file,4,1000 );
            $base = 'app/';
        }
        return (file_exists('../'.$base.'templates/' . $file . '.php')) ? include_once( '../'.$base.'templates/' . $file . '.php') : false;
    }
    
    public static function redirect($url) {
        header("Location: " . $url);
        exit();
    }

}