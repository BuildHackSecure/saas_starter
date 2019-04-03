<?php namespace Model\Core;



class Audit
{

    public static function add( Account $account, $link, $link_id, $type, $data ){
        $created_by = 0;
        $token_id = 0;
        if( $token = \Controller\Core\Token::getCurrentToken() ){
            $created_by = $token->getUser()->getId();
            $token_id = $token->getId();
        }
        $ip = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $d = Db::write()->prepare('insert into audit (account_id,link,link_id,type,data,ip,created_at,created_by,token_id) values (?,?,?,?,?,?,?,?,?) ');
        $d->execute( array(
            $account->getId(),
            $link,
            $link_id,
            $type,
            $data,
            $ip,
            date("U"),
            $created_by,
            $token_id
        ));
    }

}