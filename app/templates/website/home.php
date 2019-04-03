<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo Config::getSaasName(); ?></title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <?php if( Config::getGoogleAnalytics() ){ ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo Config::getGoogleAnalytics(); ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '<?php echo Config::getGoogleAnalytics(); ?>');
        </script>
    <?php } ?>
</head>
<body>
<div class="container">

    <div style="text-align:center">
        <a href="/"><img src="/assets/img/logo.png" alt="" height="200"></a>
    </div>

    <div style="text-align: center;font-size:32px;font-weight: bold">
        My New SaaS App.
    </div>

    <div style="text-align: center;font-size:18px;color: #8e8988;font-weight: bold">
        <?php echo Config::getSaasName(); ?> Is going to set the world on fire!
    </div>

    <div style="margin-top:50px;text-align: center"><?php echo Config::getSaasName(); ?> is currently under development but if you want to be one of the first to get on-board then please enter your email below*</div>

    <?php if( $data["email_wrong"] ){ ?>
        <div class="row" style="margin-top:40px">
            <div class="col-md-4 col-md-offset-4">
                <div class="alert alert-danger" role="alert">Please enter a valid email address</div>
            </div>
        </div>
    <?php } ?>

    <form method="post">
        <div class="row" style="margin-top:40px">
            <div class="col-sm-4 col-sm-offset-3">
                <input type="email" name="let_me_know" class="form-control" placeholder="joe@bloggs.com">
            </div>
            <div class="col-sm-2">
                <div class="hidden-md hidden-lg hidden-sm" style="margin-top: 15px"></div>
                <input type="submit" class="btn btn-success" style="width: 100%" value="Register">
            </div>
            <div class="col-sm-6 col-sm-offset-3" style="margin-top:20px;font-size:10px;text-align: center">
                *We will only use your email to let you know when the service is live and nothing else.
            </div>
        </div>
    </form>


</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>
