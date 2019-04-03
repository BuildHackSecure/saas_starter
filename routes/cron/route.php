<?php

Route::add( array('GET','POST'),'/create-invoices', 'Core\Account@createInvoices' );

Route::add( array('GET','POST'),'/make-payments', 'Core\Account@makePayments' );

Route::add( array('GET','POST'),'/csrf', 'Core\Tool@clearCSRF' );