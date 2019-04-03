<?php namespace Model\Core;

use Controller\Core\Tool;

class InvoiceLine
{

    private $line;

    public function __construct( $data ){
        $this->line = $data;
    }

    /**
     * @return int
     */
    public function getId(){
        return (int)$this->line["id"];
    }

    /**
     * @return int
     */
    public function getInvoiceId(){
        return (int)$this->line["invoice_id"];
    }

    /**
     * @return int
     */
    public function getQty(){
        return (int)$this->line["qty"];
    }

    /**
     * @return int
     */
    public function getCost(){
        return (int)$this->line["cost"];
    }

    /**
     * @return string
     */
    public function getDescription(){
        return $this->line["description"];
    }

}


class Invoice
{


    private $invoice;

    private function __construct( $data ){
        $this->invoice = $data;
    }

    /**
     * @return int
     */
    public function getId(){
        return (int)$this->invoice["id"];
    }

    /**
     * @return int
     */
    public function getAccountId(){
        return (int)$this->invoice["account_id"];
    }

    /**
     * @return int
     */
    public function getCreatedAt(){
        return (int)$this->invoice["created_at"];
    }

    /**
     * @return int
     */
    public function getPaidAt(){
        return (int)$this->invoice["paid_at"];
    }

    /**
     * @return string
     */
    public function getPaymentRef(){
        return $this->invoice["payment_ref"];
    }

    /**
     * @return string
     */
    public function getAccessHash(){
        return $this->invoice["access_hash"];
    }

    /**
     * @return int
     */
    public function getAddressId(){
        return (int)$this->invoice["address_id"];
    }

    /**
     * @return bool|Address
     */
    public function getAddress(){
        if( !isset($this->account["address"])){
            $this->invoice["address"] = Address::get( $this->getAddressId() );
        }
        return $this->invoice["address"];
    }

    /**
     * @return bool|Card
     */
    public function getCard(){
        if( !isset($this->invoice["card"]) ){
            $this->invoice["card"] = false;
            if( $this->invoice["card_id"] > 0 ){
                if( $card = Card::get($this->invoice["card_id"]) ){
                    if( $card->getAccountId() == $this->getAccountId() ) {
                        $this->invoice["card"] = $card;
                    }
                }
            }
        }
        return $this->invoice["card"];
    }

    public function setPaid($ref, $card_id ){
        $d = Db::write()->prepare('update invoice SET paid_at=?,payment_ref = ?, card_id = ? where id = ? LIMIT 1 ');
        $d->execute( array( date("U"),$ref, $card_id, $this->getId() ) );
    }

    public function paymentLog( $success, $message, $card_id, $ref ){
        $d = Db::write()->prepare('insert into invoice_paymentlog (invoice_id,success,message,card_id,ref,created_at) VALUES (?,?,?,?,?,?) ');
        $d->execute( array($this->getId(),$success, $message, $card_id, $ref, date("U") ) );
        $this->updateLastTried();
    }

    private function updateLastTried(){
        $d = Db::write()->prepare('update invoice SET last_tried = ? where id = ? LIMIT 1 ');
        $d->execute( array( date("dmy"), $this->getId() ) );
    }



    /**
     * @return InvoiceLine[]
     */
    public function getLines(){
        if( !isset($this->invoice["lines"] ) ){
            $this->invoice["lines"] = array();
            $d = Db::read()->prepare('select * from invoice_line where invoice_id = ? ');
            $d->execute( array( $this->getId() ) );
            while( $line = $d->fetch() ){
                $this->invoice["lines"][] = new InvoiceLine( $line );
            }
        }
        return $this->invoice["lines"];
    }

    public function addLine( $description, $qty, $cost ){
        $d = Db::write()->prepare('insert into invoice_line (invoice_id,qty,description,cost) values (?,?,?,?) ');
        $d->execute( array($this->getId(),$qty,$description,$cost) );
        unset($this->invoice["lines"]);
    }

    /**
     * @param Account $account
     * @return Invoice[]
     */
    public static function getByAccount(Account $account ){
        $resp = array();
        $d = Db::read()->prepare('select * from invoice where account_id = ? order by id desc ');
        $d->execute( array($account->getId()) );
        while( $i = $d->fetch() ){
            $resp[] = new Invoice( $i );
        }
        return $resp;
    }

    /**
     * @param $hash
     * @return bool|Invoice
     */
    public static function getByHash($hash ){
        $resp = false;
        $d = Db::read()->prepare('select * from invoice where access_hash = ? LIMIT 1 ');
        $d->execute( array($hash) );
        if( $d->rowCount() == 1 ){
            $resp = new Invoice( $d->fetch() );
        }
        return $resp;
    }

    /**
     * @param $id
     * @return bool|Invoice
     */
    public static function get( $id ){
        $resp = false;
        $d = Db::read()->prepare('select * from invoice where id = ? LIMIT 1 ');
        $d->execute( array($id) );
        if( $d->rowCount() == 1 ){
            $resp = new Invoice( $d->fetch() );
        }
        return $resp;
    }

    public static function create( Account $account ){
        $hash = Tool::randomHash();
        $d = Db::write()->prepare('insert into invoice (account_id,address_id,created_at,last_tried,access_hash) values (?,?,?,?,?) ');
        $d->execute( array($account->getId(),$account->getAddressId(),date("U"),date("dmy"),$hash) );
        return new Invoice( array(
            'id'            =>  Db::write()->lastInsertId(),
            'account_id'    =>  $account->getId(),
            'address_id'    =>  $account->getAddressId(),
            'created_at'    =>  date("U"),
            'last_tried'    =>  date("dmy"),
            'paid_at'       =>  0,
            'card_id'       =>  0,
            'payment_ref'   =>  '',
            'access_hash'   =>  $hash
        ));
    }

    /**
     * @return bool|Invoice
     */
    public static function toPay(){
        $resp = false;
        $d = Db::write()->prepare('select i.* from invoice i INNER JOIN account a ON (a.id=i.account_id and a.cancelled=0) where i.last_tried != ? and i.paid_at = 0 LIMIT 1 ');
        $d->execute( array(date("dmy")) );
        if( $d->rowCount() == 1 ){
            $resp = new Invoice( $d->fetch() );
        }
        return $resp;
    }


    /**
     * @param Account $account
     * @param $days
     * @return bool|Invoice
     */
    public static function unpaidInvoice(Account $account, $days ){
        $resp = false;
        $d = Db::read()->prepare('select id from invoice where account_id = ? and created_at < ? and paid_at = 0 LIMIT 1 ');
        $d->execute( array(
            $account->getId(),
            ( date("U") - ( $days * 86400 ) )
        ) );
        if( $d->rowCount() == 1 ){
            $resp = new Invoice( $d->fetch() );
        }
        return $resp;
    }


}