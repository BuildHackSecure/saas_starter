<?php

$logged_in = false;
$login_errors = array();
$password_reset = false;
$password_reset_error = false;

if( isset($_COOKIE["token"]) ){
    if( $token = \Model\Core\Token::getByHash($_COOKIE["token"]) ){
        $account = \Controller\Core\Account::getCurrentAccount();
        if( $token->getUser()->getAccountId() == $account->getId() ){
            \Controller\Core\Token::setCurrentToken( $token );
            setcookie('token', $token->getHash(), time()+3600,'/' );
            $logged_in = true;
            if( $invoice = \Model\Core\Invoice::unpaidInvoice( $account, Config::getNonPaymentDays() ) ){
                if( $_SERVER["REQUEST_URI"] != '/payment' ){
                    View::redirect('/payment');
                }
                \Controller\Core\Account::payment( $invoice, $account );
                exit();
            }
        }
    }
}

if( isset($_POST["reset_user"]) ){
    $_POST["reset_user"] = preg_replace('/([^a-zA-Z0-9@._-])/','',$_POST["reset_user"]);
    if( strlen($_POST["reset_user"]) > 4 ) {
        $password_reset = true;
        if ($user = \Model\Core\User::getByEmail( \Controller\Core\Account::getCurrentAccount() , $_POST["reset_user"] )) {
            if( $user->isDisabled() ){
                $password_reset = false;
                $password_reset_error = 'Account is disabled, please contact your administrator';
            }else if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
                if( $user->resetPassword() ){
                    $password_reset = true;
                }else{
                    $password_reset = false;
                    $password_reset_error = 'Password reset has already been sent, please wait at least 5 minutes from first request';
                }
            }else{
                $password_reset = false;
                $password_reset_error = 'Account does not have an email attached to it and needs to be reset by your administrator';
            }
        }
    }else{
        $password_reset_error = 'Username must be at least 5 characters';
    }
}

if( isset($_POST["login_user"],$_POST["login_pass"]) ){
    $_POST["login_user"] = preg_replace('/([^a-zA-Z0-9@._-])/','',$_POST["login_user"]);
    if( strlen($_POST["login_user"]) > 4 ) {
        if ($user_try = \Model\Core\User::getByEmail(\Controller\Core\Account::getCurrentAccount(), $_POST["login_user"] )) {
            if ($user = \Model\Core\User::getByLogin(\Controller\Core\Account::getCurrentAccount(), $_POST["login_user"], $_POST["login_pass"])) {
                if ($user->isDisabled()) {
                    $login_errors[] = 'Account is disabled, please contact whoever setup your account';
                    \Model\Core\Audit::add( \Controller\Core\Account::getCurrentAccount(), 'user', $user_try->getId(), 'Login Failed', 'Account Disabled');
                } else {
                    $token = \Model\Core\Token::create($user);
                    \Controller\Core\Token::setCurrentToken($token);
                    setcookie('token', $token->getHash(), time() + 3600,'/');
                    \Model\Core\Audit::add( \Controller\Core\Account::getCurrentAccount(), 'user', $user->getId(), 'login', 'Login Successful');
                    View::redirect('/');
                }
            }else {
                $login_errors[] = 'Username and password combination not recognised';
                \Model\Core\Audit::add( \Controller\Core\Account::getCurrentAccount(), 'user', $user_try->getId(), 'Login Failed', '');
            }
        }else{
            $login_errors[] = 'Username and password combination not recognised';
            \Model\Core\Audit::add( \Controller\Core\Account::getCurrentAccount(), 'user', 0, 'Login Failed', 'Tried to login as ' . $_POST["login_user"]);
        }
    }else{
        $login_errors[] = 'Please enter a valid username which is at least 5 characters';
    }
}

if( !$logged_in ){
    $data = array(
        'errors'                    =>  $login_errors,
        'company'                   =>  \Controller\Core\Account::getCurrentAccount()->getCompanyName(),
        'reset'                     =>  $password_reset,
        'reset_error'               =>  $password_reset_error,
        'password_validation_error' =>  false
    );

    switch($_SERVER["REQUEST_URI"]){
        case '/forgot-password';
            $page = 'Core/app/logged_out/forgot-password';
            break;

        case ( ( preg_match('/([\/][l][o][g][i][n][\/][0-9a-zA-Z]{171}[=]{1})/',$_SERVER["REQUEST_URI"]) ) ? true : false ):
            if( $token =  \Model\Core\Token::getByHash( substr( $_SERVER["REQUEST_URI"],-172,172 ) ) ){
                setcookie('token', $token->getHash(), time() + 3600,'/');
                View::redirect('/');
            }
            $page = 'Core/app/logged_out/login';
            break;

        case ( ( preg_match('/([\/][i][n][v][i][t][e][\/][0-9a-zA-Z]{171}[=]{1})/',$_SERVER["REQUEST_URI"]) || preg_match('/([\/][r][e][s][e][t][\/][0-9a-zA-Z]{171}[=]{1})/',$_SERVER["REQUEST_URI"]) ) ? true : false ):
            if( $access = \Model\Core\Access::checkToken( substr( $_SERVER["REQUEST_URI"],-172,172 ) ) ){
                $data["reset_username"] = $access->getUser()->getEmail();
                if( isset($_POST["new_password"],$_POST["confirm_password"]) ){
                    if( \Model\Core\User::checkPassword( $_POST["new_password"],$_POST["confirm_password"] ) ){
                        $access->getUser()->setPassword( $_POST["new_password"] );
                        $token = \Model\Core\Token::create( $access->getUser() );
                        \Controller\Core\Token::setCurrentToken($token);
                        setcookie('token', $token->getHash(), time() + 3600,'/');
                        \Model\Core\Audit::add(\Controller\Core\Account::getCurrentAccount(),'user', $access->getUser()->getId(), 'reset', 'Login Successful After Reset');
                        $access->used();
                        View::redirect('/');
                    }else{
                        $data["password_validation_error"] = true;
                    }
                }
                $page = 'Core/app/logged_out/reset-password';
            }else{
                $page = 'Core/app/logged_out/reset-invalid';
            }
            break;

        default:
            $page = 'Core/app/logged_out/login';
            break;
    }
    View::page($page,$data);
    exit();
}

Route::change404('/app/404');