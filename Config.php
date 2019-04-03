<?php

class Config
{

    private static $settings;



    public static function setup( $data ){
        if( !isset(
            $data["permissions"],
            $data["menu"],
            $data["local_machine"],
            $data["db"]["error_ip"],
            $data["db"]["db_dev_host"],
            $data["db"]["db_dev_port"],
            $data["db"]["db_read_host"],
            $data["db"]["db_read_port"],
            $data["db"]["db_write_host"],
            $data["db"]["db_write_port"],
            $data["db"]["db_db"],
            $data["db"]["db_write_user"],
            $data["db"]["db_write_pass"],
            $data["db"]["db_read_user"],
            $data["db"]["db_read_pass"],
            $data["billing"]["method"],
            $data["billing"]["nonpayment_days"],
            $data["billing"]["tax_percent"],
            $data["billing"]["trial_days"],
            $data["billing"]["charge_line"],
            $data["billing"]["payment_str"],
            $data["billing"]["stripe_public_key"],
            $data["billing"]["stripe_private_key"],
            $data["billing"]["pdf_user"],
            $data["billing"]["pdf_password"],
            $data["email"]["sendgrid_api"],
            $data["email"]["email_header_url"],
            $data["email"]["email_header_width"],
            $data["email"]["email_header_height"],
            $data["style"]["tld_contains_period"],
            $data["style"]["name"],
            $data["style"]["domain"],
            $data["style"]["nav_logo"],
            $data["style"]["logo"],
            $data["tracking"]["google_analytics"]
        ) ){
            echo "Missing config parameters";
            exit();
        }
        if( !is_callable( $data["billing"]["method"] ) ) {
            echo "Billing Method must be callable";
            exit();
        }
        self::$settings = $data;
    }

    public static function getPermissions(){
        return self::$settings["permissions"];
    }

    public static function getMenu(){
        return self::$settings["menu"];
    }

    public static function getLocalMachine(){
        return self::$settings["local_machine"];
    }

    public static function getDbErrorIP(){
        return self::$settings["db"]["error_ip"];
    }

    public static function getDbDataBase(){
        return self::$settings["db"]["db_db"];
    }

    public static function getDbDevHost(){
        return self::$settings["db"]["db_dev_host"];
    }

    public static function getDbDevPort(){
        return self::$settings["db"]["db_dev_port"];
    }

    public static function getDbWriteHost(){
        return self::$settings["db"]["db_write_host"];
    }

    public static function getDbWritePort(){
        return self::$settings["db"]["db_write_port"];
    }

    public static function getDbWriteUser(){
        return self::$settings["db"]["db_write_user"];
    }

    public static function getDbWritePass(){
        return self::$settings["db"]["db_write_pass"];
    }



    public static function getDbReadHost(){
        return self::$settings["db"]["db_read_host"];
    }

    public static function getDbReadPort(){
        return self::$settings["db"]["db_read_port"];
    }


    public static function getDbReadUser(){
        return self::$settings["db"]["db_read_user"];
    }

    public static function getDbReadPass(){
        return self::$settings["db"]["db_read_pass"];
    }

    public static function getPdfUsername(){
        return self::$settings["billing"]["pdf_user"];
    }

    public static function getPdfPassword(){
        return self::$settings["billing"]["pdf_password"];
    }

    public static function getTrialDays(){
        return (int)self::$settings["billing"]["trial_days"];
    }
    
    public static function getChargeLine(){
        return self::$settings["billing"]["charge_line"];
    }


    public static function getTaxPercent(){
        return self::$settings["billing"]["tax_percent"];
    }

    /**
     * @return int
     */
    public static function getNonPaymentDays(){
        return (int)self::$settings["billing"]["nonpayment_days"];
    }



    /**
     * @param \Model\Core\Account $account
     * @param \Model\Core\Invoice $invoice
     * @return \Model\Core\Invoice
     */
    public static function billingMethod(\Model\Core\Account $account, \Model\Core\Invoice $invoice ){
        return self::$settings["billing"]["method"]( $account, $invoice );
    }


    /**
     * @return mixed
     */
    public static function getStripePrivateKey(){
        return self::$settings["billing"]["stripe_private_key"];
    }

    public static function getPaymentStr(){
        return self::$settings["billing"]["payment_str"];
    }

    /**
     * @return mixed
     */
    public static function getStripePublicKey(){
        return self::$settings["billing"]["stripe_public_key"];
    }


    /**
     * @return bool
     */
    public static function tldHasPeriod(){
        return ( intval(self::$settings["style"]["tld_contains_period"]) ) ? true : false;
    }

    public static function getSaasName(){
        return self::$settings["style"]["name"];
    }

    public static function getSaasDomain(){
        return self::$settings["style"]["domain"];
    }

    public static function getLogoUrl(){
        return self::$settings["style"]["logo"];
    }

    public static function getNavLogoUrl(){
        return self::$settings["style"]["nav_logo"];
    }

    public static function getSendgridAPI(){
        return self::$settings["email"]["sendgrid_api"];
    }


    public static function getEmailHeaderURL(){
        return self::$settings["email"]["email_header_url"];
    }

    public static function getEmailHeaderWidth(){
        return self::$settings["email"]["email_header_width"];
    }

    public static function getEmailHeaderHeight(){
        return self::$settings["email"]["email_header_height"];
    }


    /**
     * @return string|bool
     */
    public static function getGoogleAnalytics(){
        return self::$settings["tracking"]["google_analytics"];
    }


}