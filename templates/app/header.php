<?php
$uri = explode("?",$_SERVER["REQUEST_URI"]);
$uri = $uri[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo Config::getSaasName(); ?><?php if( isset($data["title"]) ) echo ' - '.$data["title"]; ?></title>
    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="/assets/css/dropzone.css" rel="stylesheet">
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
    <style>
        h1 {
            margin: 1em 0 0.5em 0;
            color: #343434;
            font-weight: normal;
            font-size: 42px;
            line-height: 42px;
            font-family: 'Orienta', sans-serif;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/" style="padding: 6px 10px 0px 10px;"><img src="<?php echo Config::getNavLogoUrl(); ?>" height="40" alt="SaaS Ltd - Tag Line"></a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <?php if( gettype(Config::getMenu()) == 'array' ){ ?>
                    <?php foreach( Config::getMenu() as $m ){ ?>
                        <?php if( gettype($m) == 'array' && isset($m["name"],$m["link"],$m["permission"],$m["sublinks"]) ){ ?>
                            <?php if( \Controller\Core\User::hasPermission( $m["permission"], $data["level"] ) ){ ?>
                                <?php if( gettype($m["sublinks"]) == 'array' && count($m["sublinks"]) > 0 ){ ?>
                                    <li class="dropdown<?php if ( $uri == $m["link"] ) echo ' active'; ?>">
                                        <a href="<?php echo $m["link"]; ?>" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo $m["name"]; ?> <span class="caret"></span></a>
                                        <ul class="dropdown-menu">
                                            <?php foreach( $m["sublinks"] as $sl ){ ?>
                                                <?php if( gettype($m) == 'array' && isset($sl["name"],$sl["link"],$sl["permission"]) ){ ?>
                                                    <?php if( \Controller\Core\User::hasPermission( $sl["permission"], $data["level"] ) ){ ?>
                                                    <li<?php if ( $uri == $sl["link"] ) echo ' class="active"'; ?>><a href="<?php echo $sl["link"]; ?>"><?php echo $sl["name"]; ?></a></li>
                                                    <?php } ?>
                                                <?php } ?>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php }else{ ?>
                                    <li<?php if ( $uri == $m["link"] ) echo ' class="active"'; ?>><a href="<?php echo $m["link"]; ?>"><?php echo $m["name"]; ?></a></li>
                                <?php } ?>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                <?php } ?>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>


<div class="container sitemainpage" style="margin-top:40px">