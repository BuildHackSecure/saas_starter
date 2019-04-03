<?php View::Page('Core/app/logged_out/header',array(
    'title'=>'Login'
)); ?>

            <?php if( count($data["errors"]) > 0 ){ ?>
                <div class="alert alert-danger" role="alert">
                    <p><?php echo $data["errors"][0]; ?></p>
                </div>
            <?php } ?>
            <div class="panel panel-default">
                <div class="panel-heading" style="text-align: center"><?php echo $data["company"]; ?></div>
                <div class="panel-body">
                    <form method="post">
                        <div><label>Email Address</label></div>
                        <div><input class="form-control" name="login_user"></div>
                        <div style="margin-top: 7px"><label>Password</label></div>
                        <div><input class="form-control" type="password" name="login_pass"></div>
                        <div style="margin-top:10px">
                            <a href="/forgot-password">Forgot Password?</a>

                            <input class="btn btn-success pull-right" type="submit" value="Login">
                        </div>
                    </form>
                </div>
            </div>


<?php View::page('Coreapp/logged_out/footer'); ?>