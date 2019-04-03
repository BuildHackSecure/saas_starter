<?php View::Page('Core/app/logged_out/header',array(
    'title'=>'Invalid Link'
)); ?>

            <div class="alert alert-danger" role="alert" style="text-align: center">
                <p>Sorry this link seems to be invalid, please try re-requesting this again or <a href="/">click here</a> to login</p>
            </div>

<?php View::page('Core/app/logged_out/footer'); ?>