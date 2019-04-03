<?php

Route::add( array('GET','POST') ,'/', 'App\Website@home' );

Route::add( 'GET' ,'/thank-you', 'App\Website@thanks' );
