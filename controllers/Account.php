<?php namespace Controller\Core;

use Model\Core\Access;
use Model\Core\Address;
use Model\Core\Card;
use Model\Core\Email;
use Model\Core\HtmlToPdf;
use Model\Core\Invoice;
use Model\Core\Security;
use Stripe\Customer;

class Account
{
    private static $account = false;

    /**
     * @return \Model\Core\Account|boolean
     */
    public static function getCurrentAccount(){
        return self::$account;
    }

    public static function setCurrentAccount( \Model\Core\Account $account ){
        self::$account = $account;
    }

    public static function company(){
        $data = array(
            'csrf'      =>  Security::createCSRFtoken( Token::getCurrentToken()->getUser() ),
            'level'     =>  Token::getCurrentToken()->getUser()->getLevel(),
            'errors'   =>  array()
        );
        if( User::hasPermission('admin', Token::getCurrentToken()->getUser()->getLevel()) ) {
            $account = Token::getCurrentToken()->getUser()->getAccount();

            if( isset($_POST["company_name"],$_POST["address_line1"],$_POST["address_line2"],$_POST["address_line3"],$_POST["address_town"],$_POST["address_county"],$_POST["address_postcode"],$_POST["address_country"]) ){
                if( isset($_POST["csrf"]) ) {
                    if( $csrf = Security::checkCSRF( Token::getCurrentToken()->getUser(),$_POST["csrf"] )  ) {
                        $csrf->close();

                        foreach ($_POST as $k => $v) {
                            $_POST[$k] = preg_replace('/([^a-zA-Z0-9 \-\_\'])/', '', $_POST[$k]);
                            $_POST[$k] = trim(preg_replace('/\s+/', ' ', $_POST[$k]));
                        }


                        if (strlen($_POST["company_name"]) < 3) {
                            $data["errors"][] = 'Company name must be at least 3 characters long';
                        }

                        if (strlen($_POST["address_line1"]) < 3) {
                            $data["errors"][] = 'Address Line 1 must be at least 3 characters long';
                        }

                        if (strlen($_POST["address_county"]) < 3) {
                            $data["errors"][] = 'County/State must be at least 3 characters long';
                        }

                        if (strlen($_POST["address_postcode"]) < 5) {
                            $data["errors"][] = 'Post/Zip Code must be at least 5 characters long';
                        }

                        if (strlen($_POST["address_country"]) < 2) {
                            $data["errors"][] = 'Country name must be at least 2 characters long';
                        }

                        if (count($data["errors"]) == 0) {
                            $address = Address::create($account, 'company', $_POST["company_name"], $_POST["address_line1"], $_POST["address_line2"], $_POST["address_line3"], $_POST["address_town"], $_POST["address_county"], $_POST["address_postcode"], $_POST["address_country"]);
                            $account->setAddress($address);
                            \View::redirect('/admin/company');
                        }

                    }else{
                        $data["errors"][] = 'Internal error, please try again';
                    }
                }else{
                    $data["errors"][] = 'Internal error, please try again';
                }
            }
            $address = array(
                'company'   => $account->getCompanyName(),
                'line1'     => '',
                'line2'     => '',
                'line3'     => '',
                'town'      => '',
                'county'    => '',
                'postcode'  => '',
                'country'   => '',
            );
            if( $a = $account->getAddress() ) {
                $address = array(
                    'company'   => $a->getCompany(),
                    'line1'     => $a->getLine1(),
                    'line2'     => $a->getLine2(),
                    'line3'     => $a->getLine3(),
                    'town'      => $a->getTown(),
                    'county'    => $a->getCounty(),
                    'postcode'  => $a->getPostcode(),
                    'country'   => $a->getCountry(),
                );
            }
            $data["address"] = $address;
            \View::page('Core/app/admin/company', $data);
        }else{
            \View::page('Core/app/401', $data);
        }
    }

    public static function invoice($arg){
        $account = Token::getCurrentToken()->getUser()->getAccount();
        $data = array(
            'level'     =>  Token::getCurrentToken()->getUser()->getLevel(),
        );
        if( User::hasPermission('admin', Token::getCurrentToken()->getUser()->getLevel()) ) {
            if( $invoice = Invoice::get($arg[1]) ){
                if( $invoice->getAccountId() == $account->getId() ){
                    $last4 = '';
                    if( $card = $invoice->getCard() ){
                        $last4 = $card->getLastFour();
                    }
                    $address = array(
                        'company'   => '',
                        'line1'     => '',
                        'line2'     => '',
                        'line3'     => '',
                        'town'      => '',
                        'county'    => '',
                        'postcode'  => '',
                        'country'   => '',
                    );
                    if( $a = $invoice->getAddress() ) {
                        $address = array(
                            'company'   => $a->getCompany(),
                            'line1'     => $a->getLine1(),
                            'line2'     => $a->getLine2(),
                            'line3'     => $a->getLine3(),
                            'town'      => $a->getTown(),
                            'county'    => $a->getCounty(),
                            'postcode'  => $a->getPostcode(),
                            'country'   => $a->getCountry(),
                        );
                    }
                    $data["invoice"] = array(
                        'address'   =>  $address,
                        'paid'      =>  ( $invoice->getPaidAt() ? true : false  ),
                        'last4'     =>  $last4,
                        'created'   =>  $invoice->getCreatedAt(),
                        'id'        =>  $invoice->getId(),
                        'subtotal'  =>  0,
                        'vat'       =>  0,
                        'total'     =>  0,
                        'lines'     =>  array()
                    );
                    foreach( $invoice->getLines() as $line ){
                        $data["invoice"]["subtotal"] = $data["invoice"]["subtotal"] + ( $line->getCost() * $line->getQty() );
                        $data["invoice"]["lines"][] = array(
                            'qty'           =>  $line->getQty(),
                            'description'   =>  $line->getDescription(),
                            'cost'          =>  number_format( (  $line->getCost() / 100), 2, '.', ''),
                            'total'         =>  number_format( ( ( $line->getCost() * $line->getQty() ) / 100), 2, '.', '')
                        );
                    }
                    $data["invoice"]["vat"] = ($data["invoice"]["subtotal"] * ( \Config::getTaxPercent() / 100 ) );
                    $data["invoice"]["total"] = $data["invoice"]["subtotal"] + $data["invoice"]["vat"];
                    $data["invoice"]["subtotal"] = number_format( (  $data["invoice"]["subtotal"]  / 100), 2, '.', '');
                    $data["invoice"]["vat"] = number_format( (  $data["invoice"]["vat"]  / 100), 2, '.', '');
                    $data["invoice"]["total"] = number_format( (  $data["invoice"]["total"]  / 100), 2, '.', '');
                    $data["pdf"] = false;
                    \View::page('Core/app/invoice',$data);
                }else{
                    \View::page('Core/app/404',$data);
                }
            }else{
                \View::page('Core/app/404',$data);
            }
        }else{
            \View::page('Core/app/401',$data);
        }
    }

    public static function billing(){
        $account = Token::getCurrentToken()->getUser()->getAccount();
        $data = array(
            'csrf'      =>  Security::createCSRFtoken( Token::getCurrentToken()->getUser() ),
            'level'     =>  Token::getCurrentToken()->getUser()->getLevel(),
            'next_payment'  =>  $account->getBillingTimestamp(),
            'cancelled'     =>  $account->getCancelledAt(),
            'card'          =>  false,
            'invoices'      =>  array(),
            'error'         =>  array()
        );

        if( User::hasPermission('admin', Token::getCurrentToken()->getUser()->getLevel()) ) {
            if( isset($_POST["c_password"],$_POST["uncancel"]) ) {
                if( isset($_POST["csrf"]) ) {
                    if( $csrf = Security::checkCSRF( Token::getCurrentToken()->getUser(),$_POST["csrf"] )  ) {
                        $csrf->close();
                        if( \Model\Core\User::getByLogin( $account, Token::getCurrentToken()->getUser()->getEmail(), $_POST["c_password"]  ) ){
                            $account->unCancelOnNextBill();
                            \View::redirect('/admin/billing');
                        }else {
                            $data["error"][] = 'Invalid password entered';
                        }

                    }else{
                        $data["error"][] = 'Internal error, please try again';
                    }
                }else{
                    $data["error"][] = 'Internal error, please try again';
                }
            }


            if( isset($_POST["c_password"],$_POST["cancel"]) ) {
                if( isset($_POST["csrf"]) ) {
                    if( $csrf = Security::checkCSRF( Token::getCurrentToken()->getUser(),$_POST["csrf"] )  ) {
                        $csrf->close();
                        if( \Model\Core\User::getByLogin( $account, Token::getCurrentToken()->getUser()->getEmail(), $_POST["c_password"]  ) ){
                            $account->cancelOnNextBill();
                            \View::redirect('/admin/billing');
                        }else {
                            $data["error"][] = 'Invalid password entered';
                        }

                    }else{
                        $data["error"][] = 'Internal error, please try again';
                    }
                }else{
                    $data["error"][] = 'Internal error, please try again';
                }
            }



            if( isset($_POST["stripeToken"]) ) {
                if( isset($_POST["csrf"]) ) {
                    if( $csrf = Security::checkCSRF( Token::getCurrentToken()->getUser(),$_POST["csrf"] )  ) {
                        $csrf->close();
                        try {
                            $cu = Customer::update($account->getStripeRef(), array(
                                'source' => $_POST["stripeToken"]
                            ));
                            Card::create(
                                $account,
                                $cu->sources->data[0]["brand"],
                                $cu->sources->data[0]["last4"],
                                str_pad($cu->sources->data[0]["exp_month"], 2, '0', STR_PAD_LEFT) . substr($cu->sources->data[0]["exp_year"], 2, 2),
                                $cu->default_source
                            );
                            \View::redirect('/admin/billing?new_card=1');
                        } catch (\Exception $e) {
                            $data["error"][] = 'Failed to add new card, please try again';
                        }
                    }else{
                        $data["error"][] = 'Internal error, please try again';
                    }
                }else{
                    $data["error"][] = 'Internal error, please try again';
                }
            }


            if( $get_card = Card::getByAccount( Token::getCurrentToken()->getUser()->getAccount() ) ){
                $data["card"] = array(
                    'type'          =>  $get_card->getType(),
                    'ending'     =>  $get_card->getLastFour(),
                    'expiry'        =>  $get_card->getExpiry()
                );
            }

            foreach( Invoice::getByAccount( Token::getCurrentToken()->getUser()->getAccount() ) as $invoice ) {
                $amount = 0;
                foreach( $invoice->getLines() as $line ){
                    $amount = $amount + ( $line->getCost() * $line->getQty() );
                }
                $amount = number_format( ($amount / 100), 2, '.', '');
                $data["invoices"][] = array(
                    'id'        => $invoice->getId(),
                    'date'      => date("d/m/Y", $invoice->getCreatedAt() ),
                    'amount'    => '&pound;'.$amount,
                    'status'    => (  ( $invoice->getPaidAt() ) ? 'PAID' : 'UNPAID' )
                );
            }

            \View::page('Core/app/admin/billing', $data);
        }else{
            \View::page('Core/app/401', $data);
        }
    }


    public static function team(){
        $data = array(
            'csrf'      =>  Security::createCSRFtoken( Token::getCurrentToken()->getUser() ),
            'level'     =>  Token::getCurrentToken()->getUser()->getLevel(),
            'errors'    =>  array()
        );
        if( User::hasPermission('admin',Token::getCurrentToken()->getUser()->getLevel()) ) {
            $account = Token::getCurrentToken()->getUser()->getAccount();
            $data["users"] = array();
            foreach( \Model\Core\User::getByAccount( $account ) as $user ){
                $data["users"][ $user->getId() ] = array(
                    'id'            =>  $user->getId(),
                    'email'         =>  $user->getEmail(),
                    'admin'         =>  User::hasPermission('admin', $user->getLevel() ),
                    'disabled'      =>  ( ( $user->isDisabled() ) ? true : false ),
                    'last_active'   =>  date("d/m/y H:i",$user->getLastActivity())
                );
            }
            if( isset($_POST["user_id"]) ){
                if( isset($_POST["csrf"]) ) {
                    if( $csrf = Security::checkCSRF( Token::getCurrentToken()->getUser(),$_POST["csrf"] )  ){
                        $csrf->close();
                        $level = (isset($_POST["user_admin"])) ? 193 : 65;
                        if ($_POST["user_id"] == 'new') {
                            if (isset($_POST["user_email"])) {
                                if (filter_var($_POST["user_email"], FILTER_VALIDATE_EMAIL)) {
                                    if (!\Model\Core\User::getByEmail($account, $_POST["user_email"])) {
                                        $user = \Model\Core\User::create($account, $_POST["user_email"], Tool::randomHash(), $level );
                                        $access = Access::create($user);
                                        try {
                                            $email = new Email($user->getEmail(), 'noreply@' . \Config::getSaasDomain(), 'invite', array(
                                                'logo.src' => \Config::getEmailHeaderURL(),
                                                'logo.width' => \Config::getEmailHeaderWidth(),
                                                'logo.height' => \Config::getEmailHeaderHeight(),
                                                'account.name' => $user->getAccount()->getCompanyName(),
                                                'saas.domain' => \Config::getSaasDomain(),
                                                'account.domain' => $user->getAccount()->getDomain(),
                                                'reset.hash' => $access->getHash(),
                                                'app_name' => \Config::getSaasName()
                                            ));
                                            $email->send();
                                        } catch (\Exception $e) {}
                                    } else {
                                        $data["errors"][] = 'Email Address already exists for another user';
                                    }
                                } else {
                                    $data["errors"][] = 'Email address entered is invalid';
                                }
                            } else {
                                $data["errors"][] = 'Invalid input detected';
                            }
                        } else {
                            $user_id = intval($_POST["user_id"]);
                            if (isset($data["users"][$user_id])) {
                                $disabled = (isset($_POST["user_disabled"])) ? true : false;
                                $user = \Model\Core\User::getById($user_id);
                                $user->setDisabled($disabled);
                                $user->setLevel($level);
                            } else {
                                $data["errors"][] = 'Invalid input detected';
                            }
                        }
                        if (count($data["errors"]) == 0) {
                            \View::redirect('/admin/team');
                        }
                    }else{
                        $data["errors"][] = 'Internal error, please try again';
                    }
                }else{
                    $data["errors"][] = 'Internal error, please try again';
                }
            }
            $data["user_id"] = Token::getCurrentToken()->getUser()->getId();
            \View::page('Core/app/admin/team', $data);
        }else{
            \View::page('Core/app/401', $data);
        }
    }


    public static function makePayments(){
        if( $invoice = Invoice::toPay() ){
            if( $account = \Model\Core\Account::getById($invoice->getAccountId()) ) {
                $paid = false;
                $days_over = ceil((date("U") - $invoice->getCreatedAt()) / 86400);
                $total = 0;
                foreach( $invoice->getLines() as $line ){
                    $total = $total + $line->getQty() * $line->getCost();
                }
                $total = intval( $total * ( ( \Config::getTaxPercent() / 100 ) +  1) );
                $card = Card::getByAccount($account);
                if( $card ) {
                    try {
                        $c = \Stripe\Charge::create(array(
                            "amount" => $total,
                            "currency" => "gbp",
                            'customer' => $account->getStripeRef(),
                            "source" => $card->getToken(),
                            "description" => \Config::getPaymentStr()." INV " . $invoice->getId(),
                            'statement_descriptor' => \Config::getPaymentStr().' '. str_pad($account->getId(), 6, '0', STR_PAD_LEFT)
                        ));
                        $invoice->setPaid( $c->id ,$card->getId()  );
                        $invoice->paymentLog(true, '', $card->getId(), $c->id);
                        $paid = true;
                    } catch (\Exception $e) {
                        $invoice->paymentLog(false, $e->getMessage(), $card->getId(), '');
                    }
                }
                $total = number_format( (  $total  / 100), 2, '.', '');
                if( !$paid ) {
                    if (gettype($days_over / 5) == 'integer') {
                        $pdf = HtmlToPdf::create('https://api.'.\Config::getSaasDomain().'/invoice/hash/'.$invoice->getAccessHash() );
                        if( strstr($pdf,'Cannot resolve hostname') ){
                            $pdf = false;
                        }
                        foreach(  \Model\Core\User::getByAccount( $account ) as $user ){
                            if( User::hasPermission('admin', $user->getLevel()) ){
                                try {
                                    $msg = new Email( $user->getEmail() , 'noreply@'. \Config::getSaasDomain(), 'invoice_overdue', array(
                                        'total'         => $total,
                                        'days'          =>  $days_over,
                                        'app_name'      =>  \Config::getSaasName(),
                                        'card_status'   =>  ( ( $card )
                                            ?   'We seem to have a problem taking the funds from the card we have on file ending '.$card->getLastFour().'. Please make sure you have funds available or change your billing details online at <a href="https://'.$account->getDomain().'.'.\Config::getSaasDomain().'">https://'.$account->getDomain().'.'.\Config::getSaasDomain().'</a>'
                                            :   'You do not currently have a payment method saved on file. Please login to your account at <a href="https://'.$account->getDomain().'.'.\Config::getSaasDomain().'">https://'.$account->getDomain().'.'.\Config::getSaasDomain().'</a>  and enter your billing information.' ),
                                        'logo.src'          =>  \Config::getEmailHeaderURL(),
                                        'logo.width'        =>  \Config::getEmailHeaderWidth(),
                                        'logo.height'       =>  \Config::getEmailHeaderHeight(),
                                        'invoice_status'    =>  ( ($pdf)
                                            ? 'Please find attached your outstanding invoice'
                                            : 'To view details of the outstanding invoice '.str_pad( $invoice->getId(),6,'0',STR_PAD_LEFT).' please login to your account at <a href="https://'.$account->getDomain().'.'.\Config::getSaasDomain().'">https://'.$account->getDomain().'.'.\Config::getSaasDomain().'</a>' )
                                    ));
                                    if( $pdf ) {
                                        $msg->attachment('Invoice '.str_pad( $invoice->getId(),6,'0',STR_PAD_LEFT).'.pdf', $pdf);
                                    }
                                    $msg->send();
                                }catch (\Exception $e ){
                                    echo $e->getMessage();
                                }
                            }
                        }
                    }
                }else{
                    $pdf = HtmlToPdf::create('https://api.'.\Config::getSaasDomain().'/invoice/hash'.$invoice->getAccessHash() );
                    if( strstr($pdf,'Cannot resolve hostname') ){
                        $pdf = false;
                    }
                    foreach(  \Model\Core\User::getByAccount( $account ) as $user ){
                        if( User::hasPermission('admin', $user->getLevel()) ){
                            try {
                                $msg = new Email( $user->getEmail() , 'noreply@'. \Config::getSaasDomain(), 'invoice_paid', array(
                                    'total'         => $total,
                                    'app_name'      =>  \Config::getSaasName(),
                                    'card_status'   =>  'The payment was taken from the carding ending '.$card->getLastFour(),
                                    'logo.src'          =>  \Config::getEmailHeaderURL(),
                                    'logo.width'        =>  \Config::getEmailHeaderWidth(),
                                    'logo.height'       =>  \Config::getEmailHeaderHeight(),
                                    'invoice_status'    =>  ( ($pdf)
                                        ? 'Please find attached your paid invoice for your records'
                                        : 'To view details of the your invoice '.str_pad( $invoice->getId(),6,'0',STR_PAD_LEFT).' please login to your account at <a href="https://'.$account->getDomain().'.'.\Config::getSaasDomain().'">https://'.$account->getDomain().'.'.\Config::getSaasDomain().'</a>' )
                                ));
                                if( $pdf ) {
                                    $msg->attachment('Invoice '.str_pad( $invoice->getId(),6,'0',STR_PAD_LEFT).'.pdf', $pdf);
                                }
                                $msg->send();
                            }catch (\Exception $e ){
                                echo $e->getMessage();
                            }
                        }
                    }
                }
            }
        }
    }

    public static function createInvoices(){
        if( $account = \Model\Core\Account::getByInvoiceDue() ){
            $invoice = Invoice::create( $account );
            $invoice = \Config::billingMethod( $account, $invoice );
            sleep(5);
            $total = 0;
            foreach( $invoice->getLines() as $line ){
                $total = $total + $line->getQty() * $line->getCost();
            }
            $total = $total * ( ( \Config::getTaxPercent() / 100 ) +  1);
            $total = number_format( (  $total  / 100), 2, '.', '');
            $pdf = HtmlToPdf::create('https://api.'.\Config::getSaasDomain().'/invoice/hash/'.$invoice->getAccessHash() );
            if( strstr($pdf,'Cannot resolve hostname') ){
                $pdf = false;
            }
            if( $account->isDemo() ){
                $invoice->setPaid('DEMO',0);
            }else {
                $card = Card::getByAccount($account);
                foreach (\Model\Core\User::getByAccount($account) as $user) {
                    if ( User::hasPermission('admin', $user->getLevel()) ) {
                        try {
                            $msg = new Email($user->getEmail(), 'noreply@' . \Config::getSaasDomain(), 'invoice', array(
                                'total' => $total,
                                'app_name' => \Config::getSaasName(),
                                'card_status' => (($card)
                                    ? 'As we already have a card on file for you we will attempt to take the funds from the card ending ' . $card->getLastFour() . ' tomorrow.'
                                    : 'You do not currently have a payment method saved on file. Please login to your account at <a href="https://' . $account->getDomain() . '.' . \Config::getSaasDomain() . '">https://' . $account->getDomain() . '.' . \Config::getSaasDomain() . '</a>  and enter your billing information.'),
                                'logo.src' => \Config::getEmailHeaderURL(),
                                'logo.width' => \Config::getEmailHeaderWidth(),
                                'logo.height' => \Config::getEmailHeaderHeight(),
                                'invoice_status' => (($pdf)
                                    ? 'Please find attached your invoice'
                                    : 'To view details of invoice ' . str_pad($invoice->getId(), 6, '0', STR_PAD_LEFT) . ' please login to your account at <a href="https://' . $account->getDomain() . '.' . \Config::getSaasDomain() . '">https://' . $account->getDomain() . '.' . \Config::getSaasDomain() . '</a>')
                            ));
                            if ($pdf) {
                                $msg->attachment('Invoice ' . str_pad($invoice->getId(), 6, '0', STR_PAD_LEFT) . '.pdf', $pdf);
                            }
                            $msg->send();
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    }
                }
            }
            $account->updateBillingTime();
        }
        echo "OK";
    }



    public static function invoiceByHash($arg){
        if( $invoice = Invoice::getByHash( $arg[1] ) ){
            $last4 = '';
            if( $card = $invoice->getCard() ){
                $last4 = $card->getLastFour();
            }
            $address = array(
                'company'   => '',
                'line1'     => '',
                'line2'     => '',
                'line3'     => '',
                'town'      => '',
                'county'    => '',
                'postcode'  => '',
                'country'   => '',
            );
            if( $a = $invoice->getAddress() ) {
                $address = array(
                    'company'   => $a->getCompany(),
                    'line1'     => $a->getLine1(),
                    'line2'     => $a->getLine2(),
                    'line3'     => $a->getLine3(),
                    'town'      => $a->getTown(),
                    'county'    => $a->getCounty(),
                    'postcode'  => $a->getPostcode(),
                    'country'   => $a->getCountry(),
                );
            }
            $data["invoice"] = array(
                'address'   =>  $address,
                'paid'      =>  ( $invoice->getPaidAt() ? true : false  ),
                'last4'     =>  $last4,
                'created'   =>  $invoice->getCreatedAt(),
                'id'        =>  $invoice->getId(),
                'subtotal'  =>  0,
                'vat'       =>  0,
                'total'     =>  0,
                'lines'     =>  array()
            );
            foreach( $invoice->getLines() as $line ){
                $data["invoice"]["subtotal"] = $data["invoice"]["subtotal"] + ( $line->getCost() * $line->getQty() );
                $data["invoice"]["lines"][] = array(
                    'qty'           =>  $line->getQty(),
                    'description'   =>  $line->getDescription(),
                    'cost'          =>  number_format( (  $line->getCost() / 100), 2, '.', ''),
                    'total'         =>  number_format( ( ( $line->getCost() * $line->getQty() ) / 100), 2, '.', '')
                );
            }
            $data["invoice"]["vat"] = ($data["invoice"]["subtotal"] * ( \Config::getTaxPercent() / 100 ) );
            $data["invoice"]["total"] = $data["invoice"]["subtotal"] + $data["invoice"]["vat"];
            $data["invoice"]["subtotal"] = number_format( (  $data["invoice"]["subtotal"]  / 100), 2, '.', '');
            $data["invoice"]["vat"] = number_format( (  $data["invoice"]["vat"]  / 100), 2, '.', '');
            $data["invoice"]["total"] = number_format( (  $data["invoice"]["total"]  / 100), 2, '.', '');
            $data["pdf"] = true;
            \View::page('Core/app/invoice',$data);
        }else{
            \Output::error('Invoice missing',404);
        }
    }


    public static function signUp(){
        $domain_str = '';
        $email_str = '';
        $errors = array();
        if( isset($_POST["signup_domain"],$_POST["signup_email"],$_POST["signup_password"]) ){
            $domain = preg_replace('/([^0-9a-z])/','', strtolower($_POST["signup_domain"]) );
            $domain_str = $domain;
            $domain_valid = false;
            if( strlen($domain) > 2 ) $domain_valid = self::domainAvailable( $domain );
            if( !$domain_valid ) $errors[] = 'Domain name chosen is invalid';
            if ( filter_var($_POST["signup_email"], FILTER_VALIDATE_EMAIL)) {
                $email_str = $_POST["signup_email"];
            }else{
                $errors[] = 'Email address entered is invalid';
            }
            $password = $_POST["signup_password"];
            if( preg_replace('/[^0-9]/','',$password) && preg_replace('/[^A-Z]/','',$password) && preg_replace('/[^a-z]/','',$password) && strlen($password) > 7  ) {
            }else{
                $errors[] = 'Password must have at least 8 characters, 1 number and 1 capital letter';
            }
            if( count($errors) == 0 ){
                $account = \Model\Core\Account::create( $domain, $domain );
                $user = \Model\Core\User::create( $account, $_POST["signup_email"], $password, 193 );
                $token = \Model\Core\Token::create( $user, true );
                \View::redirect('https://'.$domain.'.'.\Config::getSaasDomain().'/login/'.$token->getHash() );
            }
        }
        $data = array(
            'errors'    =>  $errors,
            'domain'    =>  $domain_str,
            'email'     =>  $email_str
        );
        \View::page('Core/app/logged_out/signup',$data);
    }


    private static function domainAvailable( $domain ){
        $resp = false;
        if( $domain != 'app' && $domain != 'api' && $domain != 'cron'  && $domain != 'signup' ) {
            if( !\Model\Core\Account::getAccountByDomain( $domain ) ) {
                $resp = true;
            }
        }
        return $resp;
    }

    public static function checkDomain(){
        $resp = false;
        if( isset($_GET["domain"]) ){
            $domain = preg_replace('/([^0-9a-z])/','', strtolower($_GET["domain"]) );
            if( strlen($domain) > 2 ){
                $resp = self::domainAvailable( $domain );
            }
        }
        \Output::success( array(
           'success'    =>  $resp
        ));
    }

    public static function payment( Invoice $invoice, \Model\Core\Account $account ){
        $subtotal = 0;
        $errors = array();
        foreach( $invoice->getLines() as $line ){
            $subtotal = $subtotal + ( $line->getCost() * $line->getQty() );
        }
        $vat = ( $subtotal * ( \Config::getTaxPercent() / 100 ) );
        $charge_total = intval($subtotal + $vat);
        $total = number_format( (  ( $subtotal + $vat )  / 100), 2, '.', '');
        if( isset($_POST["stripeToken"]) ) {
            if( isset($_POST["csrf"]) ) {
                if( $csrf = Security::checkCSRF( Token::getCurrentToken()->getUser(),$_POST["csrf"] )  ) {
                    $csrf->close();
                    try {
                        $cu = Customer::update($account->getStripeRef(), array(
                            'source' => $_POST["stripeToken"]
                        ));
                        $c = \Stripe\Charge::create(array(
                            "amount"                => $charge_total,
                            "currency"              => "gbp",
                            'customer'              => $account->getStripeRef(),
                            "source"                => $cu->default_source,
                            "description"           => \Config::getPaymentStr()." INV " . $invoice->getId(),
                            'statement_descriptor'  => \Config::getPaymentStr().' '. str_pad($account->getId(), 6, '0', STR_PAD_LEFT)
                        ));
                        $card_id = Card::create(
                            $account,
                            $cu->sources->data[0]["brand"],
                            $cu->sources->data[0]["last4"],
                            str_pad($cu->sources->data[0]["exp_month"], 2, '0', STR_PAD_LEFT) . substr($cu->sources->data[0]["exp_year"], 2, 2),
                            $cu->default_source
                        );
                        $invoice->setPaid( $c->id ,$card_id  );
                        $invoice->paymentLog(true, '', $card_id, $c->id );
                        $pdf = HtmlToPdf::create('https://api.'.\Config::getSaasDomain().'/invoice/hash/'.$invoice->getAccessHash() );
                        if( strstr($pdf,'Cannot resolve hostname') ){
                            $pdf = false;
                        }
                        foreach(  \Model\Core\User::getByAccount( $account ) as $user ){
                            if( User::hasPermission('admin', $user->getLevel()) ){
                                try {
                                    $msg = new Email( $user->getEmail() , 'noreply@'. \Config::getSaasDomain(), 'invoice_paid', array(
                                        'total'         => $total,
                                        'app_name'      =>  \Config::getSaasName(),
                                        'card_status'   =>  'The payment was taken from the carding ending '.$cu->sources->data[0]["last4"],
                                        'logo.src'          =>  \Config::getEmailHeaderURL(),
                                        'logo.width'        =>  \Config::getEmailHeaderWidth(),
                                        'logo.height'       =>  \Config::getEmailHeaderHeight(),
                                        'invoice_status'    =>  ( ($pdf)
                                            ? 'Please find attached your paid invoice for your records'
                                            : 'To view details of the your invoice '.str_pad( $invoice->getId(),6,'0',STR_PAD_LEFT).' please login to your account at <a href="https://'.$account->getDomain().'.'.\Config::getSaasDomain().'">https://'.$account->getDomain().'.'.\Config::getSaasDomain().'</a>' )
                                    ));
                                    if( $pdf ) {
                                        $msg->attachment('Invoice '.str_pad( $invoice->getId(),6,'0',STR_PAD_LEFT).'.pdf', $pdf);
                                    }
                                    $msg->send();
                                }catch (\Exception $e ){
                                    echo $e->getMessage();
                                }
                            }
                        }
                        \View::redirect('/');
                    } catch (\Exception $e) {
                        $errors[] = 'Failed to add new card, please try again';
                    }
                }else{
                    $errors[] = 'Internal error, please try again';
                }
            }else{
                $errors[] = 'Internal error, please try again';
            }
        }
        $data = array(
            'errors'    =>  $errors,
            'csrf'      =>  Security::createCSRFtoken( Token::getCurrentToken()->getUser() ),
            'total'     =>  $total
        );
        \View::page('Core/app/admin/overdue',$data);
    }

    
}