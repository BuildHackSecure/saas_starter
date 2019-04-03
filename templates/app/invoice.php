<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?php echo str_pad($data["invoice"]["id"],6,'0',STR_PAD_LEFT); ?></title>
    <link href="/assets/css/<?php echo ( $data["pdf"] ) ? 'invoice-pdf.css' : 'invoice.css'; ?>" rel="stylesheet">
</head>
<body>

<center><img src="https://api.<?php echo Config::getSaasDomain(); ?><?php echo Config::getLogoUrl(); ?>" height="200"></center>

<table class="data" width="100%">

    <tr>
        <td valign="top" class="first-col" width="50%">
            <b>From</b><br><br>
            <strong>Umbrella Systems And Services Ltd</strong><br>
            <i>trading as</i> <strong><?php echo Config::getSaasName(); ?></strong><br>
            The Generator, Quay House<br>
            Exeter, Devon. EX2 4AN<br>
            United Kingdom<br><br>
            Company # 2342323424<br>
            VAT # GB297118082<br>
            <br>
            <b>To</b><br><br>
            <strong><?php echo $data["invoice"]["address"]["company"]; ?></strong><br>
            <?php if( strlen( $data["invoice"]["address"]["line1"] ) > 0 ) echo $data["invoice"]["address"]["line1"]."<br>"; ?>
            <?php if( strlen( $data["invoice"]["address"]["line2"] ) > 0 ) echo $data["invoice"]["address"]["line2"]."<br>"; ?>
            <?php if( strlen( $data["invoice"]["address"]["line3"] ) > 0 ) echo $data["invoice"]["address"]["line3"]."<br>"; ?>
            <?php if( strlen( $data["invoice"]["address"]["town"] ) > 0 ) echo $data["invoice"]["address"]["town"]."<br>"; ?>
            <?php if( strlen( $data["invoice"]["address"]["county"] ) > 0 ) echo $data["invoice"]["address"]["county"]."<br>"; ?>
            <?php if( strlen( $data["invoice"]["address"]["postcode"] ) > 0 ) echo $data["invoice"]["address"]["postcode"]."<br>"; ?>
            <?php if( strlen( $data["invoice"]["address"]["country"] ) > 0 ) echo $data["invoice"]["address"]["country"]; ?>
        </td>
        <td valign="top" class="second-col" width="50%">
            <b>Invoice Number</b><br>
            <?php echo str_pad($data["invoice"]["id"],6,'0',STR_PAD_LEFT); ?><br><br>
            <b>Invoice Date</b><br>
            <?php echo date("d/m/Y",$data["invoice"]["created"]); ?><br><br>
            <b>Payment Status</b><br>
            <?php echo ( $data["invoice"]["paid"] ) ? 'PAID ( card ending '.$data["invoice"]["last4"].' )' : 'UNPAID'; ?>
        </td>
    </tr>

</table>


<table class="basket">
    <tr>
        <th class="left">Description</th>
        <th class="center">Quantity</th>
        <th class="center">Cost</th>
        <th class="right">Total</th>
    </tr>

    <?php foreach( $data["invoice"]["lines"] as $line  ){ ?>
        <tr>
            <td class="left">
                <b><?php echo $line["description"]; ?></b><br>
            </td>
            <td class="center"><?php echo $line["qty"]; ?></td>
            <td class="center"><?php echo $line["cost"]; ?></td>
            <td class="right"><?php echo $line["total"]; ?></td>
        </tr>
    <?php } ?>

    <tr>
        <td class="noborder"></td>
        <td class="left"><strong>Sub Total</strong></td>
        <td></td>
        <td class="right"><?php echo $data["invoice"]["subtotal"]; ?></td>
    </tr>
    <?php if( Config::getTaxPercent() > 0 ){ ?>
    <tr>
        <td class="noborder"></td>
        <td class="left"><strong>VAT @ <?php echo Config::getTaxPercent(); ?>%</strong></td>
        <td></td>
        <td class="right"><?php echo $data["invoice"]["vat"]; ?></td>
    </tr>
    <?php } ?>
    <tr>
        <td class="noborder"></td>
        <td class="left"><strong>Total</strong></td>
        <td></td>
        <td class="right">GBP &pound;<?php echo $data["invoice"]["total"]; ?></td>
    </tr>

</table>
</body>
</html>