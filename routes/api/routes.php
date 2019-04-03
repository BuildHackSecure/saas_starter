<?php

Route::add( array('GET','POST'),'/invoice/hash/[sha-hash]', 'Core\Account@invoiceByHash' );