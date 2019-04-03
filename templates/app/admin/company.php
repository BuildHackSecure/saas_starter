<?php
if( !isset($_GET["no_headers"])) {
    View::page('Core/app/header',array(
        'title'         =>  'Company Details',
        'level'    =>  $data["level"]
    ));} ?>

    <h1>Company</h1>


    <div class="row">

        <div class="col-md-6 col-md-offset-3">

            <?php if( count($data["errors"])  > 0 ){ ?>
                <div class="alert alert-danger" role="alert">
                    <?php foreach( $data["errors"] as $e  ) echo '<p>'.$e.'</p>'; ?>
                </div>
            <?php } ?>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <a href="#" class="btn btn-success btn-xs pull-right addressbox"><span class="glyphicon glyphicon-pencil"></span> Update</a>
                    Company Name &amp; Address</div>
                <div class="panel-body">
                    <strong><?php echo $data["address"]["company"]; ?></strong><br>
                    <?php if( strlen( $data["address"]["line1"] ) > 0 ) echo $data["address"]["line1"]."<br>"; ?>
                    <?php if( strlen( $data["address"]["line2"] ) > 0 ) echo $data["address"]["line2"]."<br>"; ?>
                    <?php if( strlen( $data["address"]["line3"] ) > 0 ) echo $data["address"]["line3"]."<br>"; ?>
                    <?php if( strlen( $data["address"]["town"] ) > 0 ) echo $data["address"]["town"]."<br>"; ?>
                    <?php if( strlen( $data["address"]["county"] ) > 0 ) echo $data["address"]["county"]."<br>"; ?>
                    <?php if( strlen( $data["address"]["postcode"] ) > 0 ) echo $data["address"]["postcode"]."<br>"; ?>
                    <?php if( strlen( $data["address"]["country"] ) > 0 ) echo $data["address"]["country"]; ?>
                </div>
            </div>

        </div>

    </div>



<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Company Name &amp; Address</h4>
            </div>
            <form action="/admin/company" method="post" id="payment-form">
                <input type="hidden" name="csrf" value="<?php echo $data["csrf"]; ?>">
                <div class="modal-body">

                    <div><strong style="color:#F00">*</strong> <label>Company/Organisation Name</label></div>
                    <div><input name="company_name" class="form-control" value="<?php echo $data["address"]["company"]; ?>"></div>

                    <div style="margin-top:7px"><strong style="color:#F00">*</strong> <label>Address Line 1</label></div>
                    <div><input name="address_line1" class="form-control" value="<?php echo $data["address"]["line1"]; ?>"></div>

                    <div style="margin-top:7px"><label>Address Line 2</label></div>
                    <div><input name="address_line2" class="form-control" value="<?php echo $data["address"]["line2"]; ?>"></div>

                    <div style="margin-top:7px"><label>Address Line 3</label></div>
                    <div><input name="address_line3" class="form-control" value="<?php echo $data["address"]["line3"]; ?>"></div>

                    <div style="margin-top:7px"><label>Town</label></div>
                    <div><input name="address_town" class="form-control" value="<?php echo $data["address"]["town"]; ?>"></div>

                    <div style="margin-top:7px"><strong style="color:#F00">*</strong> <label>County/State</label></div>
                    <div><input name="address_county" class="form-control" value="<?php echo $data["address"]["county"]; ?>"></div>

                    <div style="margin-top:7px"><strong style="color:#F00">*</strong> <label>Post/Zip Code</label></div>
                    <div><input name="address_postcode" class="form-control" value="<?php echo $data["address"]["postcode"]; ?>"></div>

                    <div style="margin-top:7px"><strong style="color:#F00">*</strong> <label>Country</label></div>
                    <div><input name="address_country" class="form-control" value="<?php echo $data["address"]["country"]; ?>"></div>

                    <div style="margin-top:7px"><strong style="color:#F00">*</strong> <label>Fields marked with a red star must be completed</label></div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
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

    $('.addressbox').click( function(){

        $('#myModal').modal('show');

       return false;
    });

</script>
