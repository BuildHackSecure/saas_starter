<?php namespace Model\Core;


class HtmlToPdf {
    
    public static function create( $url ){
        $call = new Http( 'http://pdfcrowd.com/api/pdf/convert/uri/' );
        $result = $call->postfields( array(
            'src'       =>  $url,
            'username'  =>  \Config::getPdfUsername(),
            'key'       =>  \Config::getPdfPassword()
        ))->send();
        return $result->getData();
    }

}