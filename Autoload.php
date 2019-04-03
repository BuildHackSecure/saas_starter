<?php

if(!function_exists('classAutoLoader')){
    function classAutoLoader($class){
        $parts = explode('\\', $class);
        if( count($parts) == 2 || count($parts) == 3 ){
            if( $parts[0] == 'Model' || $parts[0] == 'Controller' ){
                $dir = ( $parts[0] == 'Model' ) ? 'models' : 'controllers';


                $start_dir = ( $parts[1] == 'App' ) ? 'app/' : '';



                if( count($parts) == 4 ) {
                    if (is_file('../' .  $start_dir. $dir . '/' . $parts[2] .'/'. $parts[3]. '.php')) {
                        if (!class_exists($class)) {
                            include_once('../' .  $start_dir. $dir . '/' . $parts[2] .'/'. $parts[3]. '.php');
                        }
                    }
                }else{
                    if (is_file('../' .  $start_dir. $dir . '/' . $parts[2] . '.php')) {
                        if (!class_exists($class)) {
                            include_once('../' .  $start_dir. $dir . '/' . $parts[2] . '.php');
                        }
                    }
                }
            }
        }
    }
}

spl_autoload_register('classAutoLoader');