<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/header',array(
        'title'         =>  'Team Details',
        'level'    =>  $data["level"]
    ));} ?>

    <h1>Team</h1>

    <div class="row">
        <div class="col-md-6 col-md-offset-3">

            <?php if( count($data["errors"]) > 0 ){ ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach( $data["errors"] as $e ){ echo '<p>'.$e.'</p>'; } ?>
            </div>
            <?php }else{ ?>
                <div class="alert alert-success" role="alert">Invite other users in your company or organisation to your account</div>
            <?php } ?>

        </div>
    </div>



    <div style="height:50px">
        <a href="#" class="btn btn-success newUser pull-right"><span class="glyphicon glyphicon-plus"></span> New User</a>
    </div>

    <table class="table">
        <tr>
            <th>Email Address</th>
            <th style="text-align: center;width: 100px">Admin</th>
            <th style="text-align: center;width: 100px">Disabled</th>
            <th>Last Active</th>
            <th style="text-align: center;width: 100px">Edit</th>
        </tr>

        <?php foreach( $data["users"] as $u ){ ?>
        <tr>
            <td><?php echo $u["email"]; ?></td>
            <td style="text-align: center"><span class="glyphicon glyphicon-<?php echo ( $u["admin"] ) ? 'ok' : 'remove' ; ?>"></span></td>
            <td style="text-align: center"><span class="glyphicon glyphicon-<?php echo ( $u["disabled"] ) ? 'ok' : 'remove' ; ?>"></span></td>
            <td><?php echo $u["last_active"]; ?></td>
            <td style="text-align: center">
                <?php if( $data["user_id"] != $u["id"] ){ ?>
                <a href="#" data-id="<?php echo $u["id"]; ?>" class="btn btn-success btn-xs editUser"> <span class="glyphicon glyphicon-pencil"></span> Update</a>
                <?php } ?>
            </td>
        </tr>

        <?php } ?>

    </table>


    <div class="modal fade" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Team Member Details</h4>
                </div>
                <form action="/admin/team" method="post">
                    <input type="hidden" name="csrf" value="<?php echo $data["csrf"]; ?>">
                    <input type="hidden" name="user_id" value="0">
                    <div class="modal-body">

                        <div style="margin-top:7px"><label><strong>Email</strong></label></div>
                        <div><input class="form-control" name="user_email" value="" disabled></div>

                        <div style="margin-top:7px"><input name="user_admin" id="admin" type="checkbox"> <label for="admin"><strong>Admin</strong></label></div>

                        <div class="disabled_row"><input type="checkbox" name="user_disabled" id="disabled"> <label for="disabled"><strong>Disabled</strong></label></div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary userfrm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/footer');
}
?>

<script>


    var team = <?php echo json_encode( $data["users"] ); ?>;

    function openModal( member ){
        $('input[name="user_id"]').val( member.id );
        $('input[name="user_email"]').val( member.email );
        $('input[name="user_admin"]').prop('checked', member.admin );
        $('input[name="user_disabled"]').prop('checked', member.disabled );
        $('#myModal').modal('show');
    }


    $('.newUser').click( function(){
        $('.userfrm').html('Create');
        $('input[name="user_email"]').removeAttr('disabled');
        $('input[name="user_admin"]').removeAttr('checked');
        $('input[name="user_disabled"]').removeAttr('checked');
        $('.disabled_row').addClass('hidden');
        openModal( {
            id          :   'new',
            email       :   '',
            admin       :   false,
            disabled    :   false
        });
        return false;
    })

    $('.editUser').click( function(){
        $('.userfrm').html('Update');
        var edit_id = $(this).attr('data-id');
        if( team.hasOwnProperty(edit_id) ){
            var member = team[ edit_id ];
            $('input[name="user_admin"]').removeAttr('checked');
            $('input[name="user_disabled"]').removeAttr('checked');
            $('input[name="user_email"]').attr('disabled','disabled');
            $('.disabled_row').removeClass('hidden');
            openModal( member );
        }else{
            alert('Invalid team member selected, please refresh the page and try again');
        }
        return false;
    })

</script>
