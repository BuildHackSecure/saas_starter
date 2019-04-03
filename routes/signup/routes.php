<?php

Route::add( array('GET','POST'),'/domain', 'Core\Account@checkDomain' );
Route::add( array('GET','POST'),'/', 'Core\Account@signUp' );