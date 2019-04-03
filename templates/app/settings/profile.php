<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/header',array(
        'title'         =>  'My User Profile',
        'level'    =>  $data["level"]
    ));} ?>

    <h1>My Profile</h1>

    <div class="row">

        <div class="col-md-6 col-md-offset-3">

            <?php if( count($data["errors"]) > 0 ){ ?>
            <div class="alert alert-danger" role="alert"><?php foreach( $data["errors"] as $e ) echo '<p>'.$e.'</p>'; ?></div>
            <?php } ?>

            <?php if( isset($_GET["uemail"]) ){ ?>
                <div class="alert alert-success" role="alert">
                    <p>Email Address has been updated</p>
                </div>
            <?php } ?>

            <?php if( isset($_GET["upass"]) ){ ?>
                <div class="alert alert-success" role="alert">
                    <p>Email Address has been updated</p>
                </div>
            <?php } ?>

            <form method="post" action="/profile">
                <input type="hidden" name="csrf" value="<?php echo $data["csrf"]; ?>">
                <div class="panel panel-default">
                    <div class="panel-heading">Email Address</div>
                    <div class="panel-body">
                        <div><label>Current Email Address</label></div>
                        <div><?php echo $data["email"]; ?></div>
                        <div style="margin-top:7px"><label for="nemail">New Email Address</label></div>
                        <div><input name="new_email" id="nemail" class="form-control"></div>
                        <div style="margin-top:7px"><label for="ncemail">Confirm New Email Address</label></div>
                        <div><input name="new_cemail" id="ncemail" class="form-control"></div>
                    </div>
                </div>
                <input type="submit" class="btn btn-success pull-right" value="Update Email">
            </form>

            <form method="post" action="/profile">
                <input type="hidden" name="csrf" value="<?php echo $data["csrf"]; ?>">
                <div class="panel panel-default" style="margin-top:75px">
                    <div class="panel-heading">Password</div>
                    <div class="panel-body">
                        <div style="margin-top:7px"><label for="npass">New Password</label></div>
                        <div><input type="password" id="npass" name="new_pass" class="form-control"></div>
                        <div style="margin-top:7px"><label for="cpass">Confirm New Password</label></div>
                        <div><input type="password" id="cpass" name="new_cpass" class="form-control"></div>
                    </div>
                </div>
                <input type="submit" class="btn btn-success pull-right" value="Update Password">
            </form>


        </div>


    </div>

<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/footer');
}
?>