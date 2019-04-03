<?php
if( !isset($_GET["no_headers"])) {
View::page('Core/app/header',array(
    'title'         =>  'Dashboard',
    'level'    =>  $data["level"]
));} ?>

<h1>Dashboard</h1>

<p>Your Project Starts Here!</p>

<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/footer');
}
?>