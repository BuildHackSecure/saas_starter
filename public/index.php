<?php
include_once('../Autoload.php');
include_once('../Route.php');
include_once('../Output.php');
include_once('../View.php');
include_once('../Config.php');
include_once('config.php');
if( file_exists('../vendor/autoload.php') ) {
    include_once('../vendor/autoload.php');
    if( !class_exists('\Stripe\Stripe') ){
        echo "Please run composer install before proceeding";
        exit();
    }
    \Stripe\Stripe::setApiKey( Config::getStripePrivateKey() );
}else{
    echo "Please run composer install before proceeding";
    exit();
}
Route::load();
Route::run();