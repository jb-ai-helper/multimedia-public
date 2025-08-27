<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>Send Request</title>
</head>

<body>
<?php

//Include Common Functions
require '../../../src/php/commontools.php';
    
function sendEmails()
{
    $ref = $_POST["ref"];
    $sender = $_POST["sender"];

    //Create EmailList
    $EmailList = array(
        "multimedia.enpjj-roubaix@justice.fr"
        //"jean-baptiste.wattiaux@justice.fr",
        //"benjamin.desmalines@justice.fr"
        );

    //Setup Subject
    $SUBJECT = 'Demande de prestation multimédia n°'.$ref;
    $subject = utf8_decode($SUBJECT);
    $subject = mb_encode_mimeheader($subject, "UTF-8");
    //Setup reply and title (reply subject)
    $reply_subject = rawurlencode($SUBJECT);
    $reply_body = rawurlencode("Bonjour,");
    $reply_body.= "%0D%0A"."%0D%0A";//Double Line Break
    $reply_body.= rawurlencode("Merci pour votre demande de prestation multimédia : https://multimedia.enpjj.fr/fiche/?ref=".$ref);
    
    //Setup message
    $message = "Vous avez reçu une nouvelle demande de prestation multimédia&nbsp;:"."<br />";
    $message.= "https://multimedia.enpjj.fr/fiche/?ref=".$ref."<br /><br />";
    $message.= "Une fois vérifiée, vous pouvez la convertir en collection pour l'application <i>Pilotage</i>, en cliquant sur ce lien&nbsp;:"."<br />";
    $message.= "https://multimedia.enpjj.fr/fiche/?ref=".$ref."&action=convert<br /><br />";
    $message.= "Cette demande a été envoyée par (cliquer pour répondre)&nbsp;:&nbsp;<a href='mailto:".$sender."?subject=".$reply_subject."&body=".$reply_body."'>".$sender."</a>";

    //Set Header
    $headers = 'MIME-Version: 1.0'."\r\n";
    $headers.= 'From: Multimedia Service Request<multimedia.enpjj-roubaix@justice.fr>'."\r\n";
    $headers.= 'Content-type: text/html; charset=utf-8'."\r\n";
    $headers.= 'X-Mailer: PHP/'.phpversion()."\r\n";
    
    //Preparing error system
    $error_nb = 0; $error_msg = 'Une erreur s\'est produite lors de l\'envoie de votre demande.\r\nMerci de recommencer...';
    
    //Preparing success message
    $success_msg = 'Votre demande a bien été envoyée au service Multimédia.';

    foreach($EmailList as $email)
    { if(!mail($email, $subject, $message, $headers)) { ++$error_nb;
    } $error_msg.= $email."\n"; 
    }
    
    if($error_nb>0) { return($error_msg);
    } else{
        $success_email = "Bonjour,"."<br />".$success_msg;
        $success_email.= "<br />"."N'hésitez pas à l'imprimer pour en garder une trace&nbsp;:&nbsp;https://multimedia.enpjj.fr/fiche/?ref=".$ref."<br /><br />";
        $success_email.= "NB : Pour imprimer la fiche, cliquez sur le lien ci-dessus, afin d'ouvrir la fiche dans Microsoft Edge puis, appuyez simultanément sur les touches \"CTRL\" et \"P\". ";
        $success_email.= "Bien vérifiez que la case \"graphisme de l'arrière-plan\" est cochée et que les marges sont réglées sur \"par défaut\" dans les options."."<br /><br />";
        $success_email.= "Le service reviendra bientôt vers vous pour valider ou amander la prestation."."<br /><br />";
        $success_email.= "Cordialement,"."<br /><br /><br /><br />";
        $success_email.= "Service Multimédia de l'ENPJJ";
        if(!mail($sender, $subject, $success_email, $headers)) { ++$error_nb; $error_msg.= $email."\n"; 
        }
        else{ $success_msg.= '\r\nUn email récapitulatif vous a également été adressé.'; 
        }
        return($success_msg);
    }
}

$results = sendEmails();
echo '<script type="text/javascript">';
echo 'parent.alert("'.$results.'");';
echo '</script>';
    
?>
</body>
</html>
