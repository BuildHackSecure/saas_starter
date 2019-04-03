<?php View::Page('Core/app/logged_out/header',array(
    'title'=>'Signup'
)); ?>

<?php if( count($data["errors"]) > 0 ){ ?>
    <div class="alert alert-danger" role="alert">
        <p><?php echo $data["errors"][0]; ?></p>
    </div>
<?php } ?>
    <div class="panel panel-default">
        <div class="panel-heading" style="text-align: center">Signup</div>
        <div class="panel-body">
            <form method="post">
                <div><label>Domain</label></div>
                <div style="text-align: center;color:#558edb"><span class="newdomain" style="color: #1647ed;font-weight: bold"></span>.<?php echo Config::getSaasDomain(); ?></div>
                <div style="text-align: center;font-weight: bold;font-size:12px" class="domainconf">please choose a domain</div>
                <div style="margin-top: 7px;"><input class="form-control" maxlength="15" name="signup_domain" value="<?php echo $data["domain"]; ?>" placeholder="acme"></div>
                <div style="margin-top: 7px"><label>Email Address</label></div>
                <div style="position:absolute;box-shadow: 5px 10px 8px #888888;margin-top:-70px;margin-left:10px;background-color:#ffffff;margin-bottom:15px;border:2px solid #ff0000;padding:10px" class="email_rules hidden">
                    <ul style="list-style:none;margin:0;padding:0px">
                        <li><span class="email_valid glyphicon glyphicon-remove" style="color:#ff0000"></span> Email Valid?</li>
                    </ul>
                </div>
                <div><input class="form-control" name="signup_email" value="<?php echo $data["email"]; ?>" placeholder="joe.bloggs@email.com"></div>
                <div style="margin-top: 7px"><label>Password</label></div>
                <div style="position:absolute;box-shadow: 5px 10px 8px #888888;margin-top:-130px;margin-left:10px;background-color:#ffffff;margin-bottom:15px;border:2px solid #ff0000;padding:10px" class="password_rules hidden">
                    <ul style="list-style:none;margin:0;padding:0px">
                        <li><span class="pass_length glyphicon glyphicon-remove" style="color:#ff0000"></span> 8 or more characters</li>
                        <li><span class="pass_upper glyphicon glyphicon-remove" style="color:#ff0000"></span> 1 or more capital letters</li>
                        <li><span class="pass_lower glyphicon glyphicon-remove" style="color:#ff0000"></span> 1 or more lower case letters</li>
                        <li><span class="pass_number glyphicon glyphicon-remove" style="color:#ff0000"></span> 1 or more numbers</li>
                    </ul>
                </div>
                <div><input class="form-control" type="password" name="signup_password" placeholder="password"></div>
                <div style="margin-top:10px">
                    <input class="btn btn-success pull-right" type="submit" value="Create Account">
                </div>
            </form>
        </div>
    </div>
<?php View::page('Core/app/logged_out/footer'); ?>

<script>

    $('input[name="signup_domain"]').keyup( function(){
        $(this).css('border','1px solid #ff0000');
        var str = $(this).val().toLowerCase().replace(/[^a-z0-9]+/gi, "");
        $('.domainconf').html('&nbsp;');
        if( str.length > 0 ){
            if( str.length < 3 ){
                $('.domainconf').css('color','#a90000');
                $('.domainconf').html('domain must be at least 3 characters');
            }else{
                $.ajax({
                    url: "/domain",
                    async: true,
                    data: {
                        domain  :   str
                    }
                }).done(function( resp ) {
                    if( resp.success ){
                        $('.domainconf').css('color','#2ea92b');
                        $('.domainconf').html('perfect it\'s available!');
                        $('input[name="signup_domain"]').css('border','1px solid #328c33');
                    }else{
                        $('.domainconf').css('color','#a90000');
                        $('.domainconf').html('this domain name is not available');
                    }
                });
            }
        }else{
            $('.domainconf').html('please choose a domain');
        }
        $('.newdomain').html(str);
        $(this).val(str);
    });


    $('input[name="signup_password"]').keyup( function(){
        $('.password_rules').removeClass('hidden');
        var str = $(this).val();
        $('.pass_length').removeClass('glyphicon-remove');
        $('.pass_length').removeClass('glyphicon-ok');
        $('.pass_upper').removeClass('glyphicon-remove');
        $('.pass_upper').removeClass('glyphicon-ok');
        $('.pass_lower').removeClass('glyphicon-remove');
        $('.pass_lower').removeClass('glyphicon-ok');
        $('.pass_number').removeClass('glyphicon-remove');
        $('.pass_number').removeClass('glyphicon-ok');
        var count = 0;


        if( str.length > 7 ){
            count++;
            $('.pass_length').addClass('glyphicon-ok');
            $('.pass_length').css('color','#3EBA3F');
        }else{
            $('.pass_length').addClass('glyphicon-remove');
            $('.pass_length').css('color','#ff0000');
        }

        if( str.replace(/[^A-Z]/g,'').length > 0 ){
            count++;
            $('.pass_upper').addClass('glyphicon-ok');
            $('.pass_upper').css('color','#3EBA3F');
        }else{
            $('.pass_upper').addClass('glyphicon-remove');
            $('.pass_upper').css('color','#ff0000');
        }

        if( str.replace(/[^a-z]/g,'').length > 0 ){
            count++;
            $('.pass_lower').addClass('glyphicon-ok');
            $('.pass_lower').css('color','#3EBA3F');
        }else{
            $('.pass_lower').addClass('glyphicon-remove');
            $('.pass_lower').css('color','#ff0000');
        }

        if( str.replace(/[^0-9]/g,'').length > 0 ){
            count++;
            $('.pass_number').addClass('glyphicon-ok');
            $('.pass_number').css('color','#3EBA3F');
        }else{
            $('.pass_number').addClass('glyphicon-remove');
            $('.pass_number').css('color','#ff0000');
        }

        if( count > 3 ){
            $('.password_rules').css('border','2px solid #328c33');
            $(this).css('border','1px solid #328c33');
            password_check = true;
        }else{
            $('.password_rules').css('border','2px solid #FF0000');
            $(this).css('border','1px solid #ff0000');
            password_check = false;
        }

    });


    $('input[name="signup_password"]').focusin( function(){
        $('.password_rules').removeClass('hidden');
    });

    $('input[name="signup_password"]').focusout( function(){
        $('.password_rules').addClass('hidden');
    });



    function validateEmail(email) {
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }


    $('input[name="signup_email"]').keyup( function(){
        $('.email_rules').removeClass('hidden');
        var str = $(this).val();
        str = str.trim();
        $(this).val( str );
        if( validateEmail(str) ){
            $('.email_valid').removeClass('glyphicon-remove');
            $('.email_valid').addClass('glyphicon-ok');
            $('.email_valid').css('color','#3EBA3F');
            $('.email_rules').css('border','2px solid #328c33');
            $(this).css('border','1px solid #328c33');
            email_check = true;
        }else{
            $('.email_valid').removeClass('glyphicon-ok');
            $('.email_valid').addClass('glyphicon-remove');
            $('.email_valid').css('color','#ff0000');
            $('.email_rules').css('border','2px solid #FF0000');
            $(this).css('border','1px solid #ff0000');
            email_check = false;
        }
    });


    $('input[name="signup_email"]').focusin( function(){
        $('.email_rules').removeClass('hidden');
    });

    $('input[name="signup_email"]').focusout( function(){
        $('.email_rules').addClass('hidden');
    });

    $('input[name="signup_domain"]').trigger('keyup');

</script>
