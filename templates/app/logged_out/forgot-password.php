<?php View::Page('Core/app/logged_out/header',array(
    'title'=>'Forgot Password'
)); ?>

            <?php if( $data["reset"] ){ ?>
                <div class="alert alert-success" role="alert" style="text-align: center">
                    <p>If we have found a matching account we will email you with a link to reset your password. <a href="/">Back to login</a></p>
                </div>
            <?php }else{ ?>

            <?php if( $data["reset_error"] ){ ?>
                <div class="alert alert-danger" role="alert" style="text-align: center">
                    <p><?php echo $data["reset_error"]; ?></p>
                </div>
            <?php } ?>

            <div class="panel panel-default">
                <div class="panel-heading" style="text-align: center"><?php echo $data["company"]; ?></div>
                <div class="panel-body">
                    <form method="post">
                        <div style="text-align: center;margin-bottom: 20px;font-weight: 300">Please enter your email address</div>
                        <div><label>Email Address</label></div>
                        <div><input class="form-control" name="reset_user"></div>
                        <div style="margin-top:10px">
                            <a href="/">Back To Login</a>
                            <input class="btn btn-success pull-right" type="submit" value="Lookup">
                        </div>
                    </form>
                </div>
            </div>
            <?php } ?>


<?php View::page('Core/app/logged_out/footer'); ?>