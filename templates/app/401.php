<?php
if( !isset($_GET["no_headers"])) {
View::page('Core/app/header',array(
    'title'    =>  'Access not allowed',
    'level'     =>  \Controller\Core\Token::getCurrentToken()->getUser()->getLevel(),
));}
?>

<h1>No Access</h1>

<p>Sorry but your account does not allow you access to this area</p>

<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/footer');
}
?>
