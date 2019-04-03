<?php namespace Model\Core;


class Email
{


    private $email;
    private $sent = false;

    public function  __construct( $to, $from, $type, $swap = array() ){

        if(!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid email address');
        }

        switch( $type ){


            case 'password_reset':
                $subject = \Config::getSaasName().' - Password Reset';
                $contents = file_get_contents('../data/email-templates/password_reset.html');
                break;

            case 'invoice':
                $subject = \Config::getSaasName().' - Invoice';
                $contents = file_get_contents('../data/email-templates/invoice.html');
                break;

            case 'invite':
                $subject = \Config::getSaasName().' - Invite';
                $contents = file_get_contents('../data/email-templates/invite.html');
                break;

            case 'invoice_overdue':
                $subject = \Config::getSaasName().' - Invoice Overdue';
                $contents = file_get_contents('../data/email-templates/invoice_overdue.html');
                break;

            case 'invoice_paid':
                $subject = \Config::getSaasName().' - Invoice Paid';
                $contents = file_get_contents('../data/email-templates/invoice_paid.html');
                break;

        }


        if( !isset($contents) ){
            throw new \Exception('Invalid message type');
        }

        if( gettype($swap) == 'array' ){
            foreach(  $swap as $k=>$v ){
                $contents = str_replace('{{'.$k.'}}',$v,$contents);
            }
        }


        $this->email = array(
            'to'            =>  $to,
            'from'          =>  $from,
            'subject'       =>  $subject,
            'contents'      =>  $contents,
            'attachments'   =>  array()
        );

    }


    /**
     * @param $name
     * @param $content
     * @return Email
     * @throws \Exception
     */
    public function attachment($name, $content ){
        if( $this->sent ) throw new \Exception('Email already sent');
        $this->email["attachments"][] = array(
            'content'   =>  base64_encode($content),
            'filename'  =>  $name
        );
        return $this;
    }


    /**
     * @return bool
     * @throws \Exception
     */
    public function send(){
        if( $this->sent ) throw new \Exception('Email already sent');
        $this->sent = true;
        $json = array(
            'personalizations'	=>	array(
                array(
                    'to'	=>	array(
                        array(
                            'email'	=>	$this->email["to"]
                        )
                    )
                )
            ),
            'from'	=>	array(
                'email'	=>	$this->email["from"]
            ),
            'subject'	=>	$this->email["subject"],
            'content'	=>	array(
                array(
                    'type'	=>	'text/html',
                    'value'	=>	$this->email["contents"]
                )
            )
        );
        if( $this->email["attachments"] ){
            $json["attachments"] = $this->email["attachments"];
        }

        $socket = new Http('https://api.sendgrid.com/v3/mail/send');
        $result = $socket->header('Authorization: Bearer '.\Config::getSendgridAPI() )
            ->method('POST')
            ->json( json_encode($json) )
            ->send();
        return ( $result->getHttpStatus() == 202 ) ? true : false;
    }

}