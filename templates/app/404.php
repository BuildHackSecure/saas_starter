<?php
http_response_code(404);
if( !isset($_GET["no_headers"])) {
View::page('Core/app/header',array(
    'title'    =>  'Page or Record Missing',
    'level'     =>  \Controller\Core\Token::getCurrentToken()->getUser()->getLevel(),
));} ?>


<h1>Page Not Found</h1>

<p>Sorry but we cannot find the record or page you are looking for.</p>

<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/footer');
}
?>