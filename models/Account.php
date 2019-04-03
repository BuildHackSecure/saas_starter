<?php namespace Model\Core;

use Stripe\Customer;

class Account
{

    private $account;

    private function __construct( $data ){
        $this->account = $data;
    }

    public function getId(){
        return (int)$this->account["id"];
    }

    /**
     * @return bool
     */
    public function isDemo(){
        return ( intval($this->account["demo"]) ) ? true : false;
    }

    public function getCompanyName(){
        return $this->account["company"];
    }

    public function getDomain(){
        return $this->account["domain"];
    }

    public function getStripeRef(){
        return $this->account["stripe_ref"];
    }

    public function getBillingTimestamp(){
        return $this->account["next_bill"];
    }


    public function getCancelledAt(){
        return (int)$this->account["cancelled_at"];
    }

    public function unCancelOnNextBill(){
        $d = Db::write()->prepare('update account SET cancelled_at = 0 where id = ? ');
        $d->execute( array($this->getId()) );
    }

    public function cancelOnNextBill(){
        $d = Db::write()->prepare('update account SET cancelled_at = ? where id = ? ');
        $d->execute( array(date("U"),$this->getId()) );
    }

    public function cancel(){
        $d = Db::write()->prepare('update account SET cancelled = 1 where id = ? ');
        $d->execute( array($this->getId()) );
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address ){
        $d = Db::write()->prepare('update account SET address_id = ? where id = ? ');
        $d->execute( array($address->getId(),$this->getId()) );
    }

    /**
     * @return int
     */
    public function getAddressId(){
        return (int)$this->account["address_id"];
    }

    /**
     * @return bool|Address
     */
    public function getAddress(){
        if( !isset($this->account["address"])){
            $this->account["address"] = Address::get( $this->getAddressId() );
        }
        return $this->account["address"];
    }


    public function createStripeRef(){
        if( strlen($this->getStripeRef()) == 0 ){
            $c = Customer::create(array(
                'description'   =>  \Config::getPaymentStr().' '.$this->getId()
            ));
            $d = Db::write()->prepare('update account SET stripe_ref = ? where id = ? ');
            $d->execute( array($c->id,$this->getId()) );
            $this->account["stripe_ref"] = $c->id;
        }
    }

    public function updateBillingTime(){
        $timestamp = strtotime('+1 month', $this->getBillingTimestamp());
        $d = Db::write()->prepare('update account SET next_bill = ? where id = ? LIMIT 1 ');
        $d->execute( array($timestamp,$this->getId()) );
    }

    /**
     * @param $subdomain
     * @return bool|Account
     */
    public static function getAccountByDomain($subdomain ){
        $resp = false;
        $d = Db::read()->prepare('select * from account where domain = ? and cancelled = 0 LIMIT 1 ');
        $d->execute( array($subdomain) );
        if( $d->rowCount() == 1 ){
            $resp = new Account( $d->fetch() );
        }
        return $resp;
    }

    /**
     * @param $id
     * @return bool|Account
     */
    public static function getById( $id ){
        $resp = false;
        $d = Db::read()->prepare('select * from account where id = ? and cancelled = 0 LIMIT 1 ');
        $d->execute( array($id) );
        if( $d->rowCount() == 1 ){
            $resp = new Account( $d->fetch() );
        }
        return $resp;
    }


    /**
     * @param $name
     * @param $domain
     * @return Account
     */
    public static function create($name, $domain ){
        $next_bill = self::firstBillingTimestamp();
        $d = Db::write()->prepare('insert into account (company,domain,next_bill) values (?,?,?) ');
        $d->execute( array($name,$domain,$next_bill) );
        $account = new Account( array(
            'id'            =>  Db::write()->lastInsertId(),
            'demo'          =>  0,
            'company'       =>  $name,
            'domain'        =>  $domain,
            'stripe_ref'    =>  '',
            'address_id'    =>  0,
            'next_bill'     =>  $next_bill,
            'cancelled_at'  =>  0,
            'cancelled'     =>  0
        ));
        $account->createStripeRef();
        return $account;
    }

    private static function firstBillingTimestamp(){
        $start = mktime(0,0,0, date("m"), date("d"), date("Y") );
        $finish = $start + ( \Config::getTrialDays() * 86400 );
        if( date("d",$finish) > 28 ){
            $finish = $finish + ( 5 * 86400 );
            $finish = mktime(0,0,0, date("m",$finish), 1, date("Y",$finish) );
        }
        return $finish;
    }

    /**
     * @return bool|Account
     */
    public static function getByInvoiceDue(){
        $resp = false;
        $d = Db::read()->prepare('select * from account where next_bill < ? and cancelled = 0 LIMIT 1 ');
        $d->execute( array(date("U")) );
        if( $d->rowCount() == 1 ){
            $resp = new Account( $d->fetch() );
            if( $resp->getCancelledAt() > 0 ){
                $resp->cancel();
                $resp = false;
            }
        }
        return $resp;
    }






}