<?php

class Route
{

    protected static $routes = array();

    private static $default_404 = '404';

    public static function change404( $page ){
        if( file_exists('../templates/'.$page.'.php')){
            self::$default_404 = $page;
        }
    }


    /**
     * @param $method - The HTTP Verb for the route
     * @param $url - The page URL segment to match
     * @param $function - The function to execute when the route is matched
     */
    public static function add( $method, $url, $function ){
        $method = ( gettype($method) == 'array' ) ? $method : array($method);
        foreach( $method as $m ) {
            if (!isset(self::$routes[$m])) self::$routes[$m] = array();
            $regexUrl = self::makeRegex($url);
            foreach( self::$routes[ $m ] as $r ){
                if( $r["regex"] == $regexUrl ){
                    echo 'Duplicate route detected for a '.$m.' request to '.$url;
                    exit();
                }
            }
            self::$routes[$m][] = array(
                'regex' => $regexUrl,
                'function' => $function
            );
        }
    }




    /**
     * @param $str String - The URL segment to make a regex for
     * @return String
     */
    private static function makeRegex($str) {
        $str = str_replace('[string]', '([0-9a-zA-Z\-]{1,})', $str);
        $str = str_replace('[int]', '([0-9]{1,})', $str);
        $str = str_replace('[hash]', '([0-9a-fA-F]{32})', $str);
        $str = str_replace('[sha-hash]', '([0-9a-zA-Z]{171}[=]{1})', $str);
        $str = '/^'.str_replace('/', '\/', $str).'[\/]?$/';
        return $str;
    }

    private static function url()
    {
        $uri_sp =  explode("?",$_SERVER["REQUEST_URI"]);
        return strval($uri_sp[0]);
    }

    public static function run()
    {
        $match = false;
        if( isset(self::$routes[$_SERVER["REQUEST_METHOD"]]) )
        {
            foreach( self::$routes[$_SERVER["REQUEST_METHOD"]] as $page )
            {
                $a = @preg_match($page["regex"], self::url(), $matches);
                if ($a) {
                    $action = $page["function"];
                    $act = explode('@', $action);
                    if( count($act) == 2 ) {
                        $controllerName = str_replace("\\","/",$act[0]);
                        $base = ( substr($controllerName,0,4) == 'App/' ) ? 'app/' : '';
                        $fileName = str_replace(array('App/','Core/'),"",$controllerName);
                        $methodName = $act[1];
                        if( file_exists('../'.$base.'controllers/'.$fileName.'.php')  ){
                            include_once('../'.$base.'controllers/'.$fileName.'.php');
                            $controllerName = str_replace("/","\\",$controllerName);
                            if (method_exists('Controller\\' . $controllerName, $methodName)) {
                                if (is_callable('Controller\\' . $controllerName, $methodName)) {
                                    call_user_func(array('Controller\\' . $controllerName, $methodName), $matches);
                                    $match = true;
                                }
                            }
                        }
                    }
                }
            }
        }
        if( !$match ){
            View::page( self::$default_404 );
        }
    }



    public static function load(){
        $host_sp = explode('.', $_SERVER["HTTP_HOST"] );
        if( Config::tldHasPeriod() ) {
            $i1 = 3;
            $i2 = 4;
        }else{
            $i1 = 2;
            $i2 = 3;
        }
        if( $host_sp[ ( count($host_sp) - 1 ) ] == 'test' ) {
            $i1 = 2;
            $i2 = 3;
        }
        if( count($host_sp) == $i1 ){
            $base = 'website';
        }else if( count($host_sp) == $i2 ){
            if( $host_sp[0] == 'www' ){
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: https://'.substr($_SERVER["HTTP_HOST"],4,1000).$_SERVER["REQUEST_URI"] );
                exit();
            }elseif( $host_sp[0] == 'api' ){
                $base = 'api';
            }elseif( $host_sp[0] == 'cron' ){
                $base = 'cron';
            }elseif( $host_sp[0] == 'signup' ){
                $base = 'signup';
            }else{
                if( $account = \Model\Core\Account::getAccountByDomain($host_sp[0]) ){
                    $base = 'app';
                    \Controller\Core\Account::setCurrentAccount( $account );
                }else{
                    View::page('app/logged_out/404');
                    exit();
                }
            }
        }else{
            View::page( self::$default_404 );
        }

        if( is_dir('../routes/' . $base) ) {
            foreach (scandir('../routes/' . $base) as $f) {
                if ($f != '.' && $f != '..') {
                    if (is_dir('../routes/' . $base . '/' . $f)) {
                        foreach (scandir('../routes/' . $base . '/' . $f) as $f2) {
                            if (substr($f2, -3, 3) == 'php') {
                                include_once('../routes/' . $base . '/' . $f . '/' . $f2);
                            }
                        }
                    } else {
                        if (substr($f, -3, 3) == 'php') {
                            include_once('../routes/' . $base . '/' . $f);
                        }
                    }
                }
            }
        }
        if( is_dir('../app/routes/' . $base) ) {
            foreach (scandir('../app/routes/' . $base) as $f) {
                if ($f != '.' && $f != '..') {
                    if (is_dir('../app/routes/' . $base . '/' . $f)) {
                        foreach (scandir('../app/routes/' . $base . '/' . $f) as $f2) {
                            if (substr($f2, -3, 3) == 'php') {
                                include_once('../app/routes/' . $base . '/' . $f . '/' . $f2);
                            }
                        }
                    } else {
                        if (substr($f, -3, 3) == 'php') {
                            include_once('../app/routes/' . $base . '/' . $f);
                        }
                    }
                }
            }
        }
    }


}



