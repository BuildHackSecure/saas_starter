<?php View::Page('Core/app/logged_out/header',array(
    'title'=>'Set New Password'
)); ?>

            <?php if( $data["reset"] ){ ?>
                <div class="alert alert-success" role="alert" style="text-align: center">
                    <p>If we have found a matching account we will email you with a link to reset your password. <a href="/">Back to login</a></p>
                </div>
            <?php }else{ ?>

                <?php if( $data["password_validation_error"] ){ ?>
                    <div class="alert alert-danger" role="alert" style="text-align: center">
                        <p>Your new password does not match or does not meet the minimum standards as set out below</p>
                    </div>
                <?php } ?>

                <div class="panel panel-default">
                    <div class="panel-heading" style="text-align: center"><?php echo $data["company"]; ?></div>
                    <div class="panel-body">
                        <form method="post">
                            <div style="text-align: center;margin-bottom: 2px;font-weight: 300">Please set your Password</div>
                            <div style="text-align: center;margin-bottom: 20px;font-weight: 300">Your password must be 6 or more characters and include at least one capital letter and one number.</div>
                            <div><label>Email</label></div>
                            <div><input class="form-control" disabled="" value="<?php echo $data["reset_username"]; ?>"></div>

                            <div style="margin-top:7px"><label>New Password:</label></div>
                            <div><input class="form-control" type="password" name="new_password"></div>

                            <div style="margin-top:7px"><label>Confirm Password:</label></div>
                            <div><input class="form-control" type="password" name="confirm_password"></div>

                            <div style="margin-top:10px">
                                <a href="/">Back To Login</a>
                                <input class="btn btn-success pull-right" type="submit" value="Set Password">
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>


<?php View::page('Core/app/logged_out/footer'); ?>