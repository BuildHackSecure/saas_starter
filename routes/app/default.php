<?php
Route::add( array('GET','POST'),'/profile', 'Core\User@profile' );
Route::add( array('GET','POST'),'/admin/company', 'Core\Account@company' );
Route::add( array('GET','POST'),'/admin/billing', 'Core\Account@billing' );
Route::add( array('GET','POST'),'/admin/billing/invoice/[int]', 'Core\Account@invoice' );
Route::add( array('GET','POST'),'/admin/team', 'Core\Account@team' );
Route::add( array('GET','POST'),'/admin/team/[int]', 'Core\Account@teamMember' );
Route::add('GET','/logout','Core\Token@logout');
Route::add('GET','/reset/[sha-hash]','Core\Token@logoutToReset');
Route::add('GET','/invite/[sha-hash]','Core\Token@logoutToInvite');