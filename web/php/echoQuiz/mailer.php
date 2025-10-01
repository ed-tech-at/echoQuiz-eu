<?php
  
namespace echoQuiz;

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);


error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

use app_ed_tech\app_func;
use \PHPMailer\PHPMailer\PHPMailer;
use \PHPMailer\PHPMailer\Exception;


require_once __DIR__ . '/../files/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../files/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../files/PHPMailer-master/src/SMTP.php';


class mailer {


public static function sendAnMailWithCopy ($betreff, $inhalt, $toEmails, $answerToName = "", $answerToEmail = "") {

  global $enableMail;
  if ($enableMail == false) {
    $a = "<pre>E-Mail disabled: Whould have send to  ";

    foreach ($toEmails as $toEmail) {
     $a .= $toEmail . "; ";
    }
    $a .= "\nSubject: " . $betreff . "\n\n" . $inhalt;

    $a .= "</pre>";


    app_func::addSessionStatusMeldung("dark", $a);

    
    return;
  }

  
  $mail = new PHPMailer(true); 

  global $email_host;
  global $email_username;
  global $email_name;
  global $email_pw;
  global $email_port;

  try {

    $mail->SMTPDebug = 0;                                       // Enable verbose debug output
    $mail->isSMTP();                                            // Set mailer to use SMTP
    $mail->Host       = $email_host;  // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = $email_username;                     // SMTP username
    $mail->Password   = $email_pw;                               // SMTP password
    $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, ssl also accepted
    $mail->Port       = $email_port;                                    // TCP port to connect to
    $mail->CharSet = "UTF-8";                                // TCP port to connect to
    //Recipients

    $mail->setFrom($email_username, $email_name);

    foreach ($toEmails as $toEmail) {
      $mail->addAddress($toEmail);     //Add a recipient
    }
    
   
    if ($answerToName != "") {
      $mail->addReplyTo($answerToEmail, $answerToName);
    }

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $betreff;
    $mail->Body    = $inhalt;
    $mail->AltBody = strip_tags($inhalt);

    $mail->send();
//    echo 'Message has been sent';
    return "";
  } catch (Exception $e) {
    throw new \Exception($mail->ErrorInfo, 500);
    
    return "Fehler: E-Mail konnte nicht gesendet werden.";

    return "Fehler: Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }

  return "";

}

public static function sendAnMail ($betreff, $inhalt, $toEmail, $toName = "",  $answerToName = "", $answerToEmail = "") {
  
   global $enableMail;
  if ($enableMail == false) {
    $a = "<pre>E-Mail disabled: Whould have send to  ";

    $a .= $toEmail;
    
    $a .= "\nSubject: " . $betreff . "\n\n" . $inhalt;
    
    $a .= "</pre>";

    app_func::addSessionStatusMeldung("dark", $a);

    return;
  }


  $mail = new PHPMailer(true); 


  global $email_host;
  global $email_username;
  global $email_name;
  global $email_pw;
  global $email_port;

  
  try {

    $mail->SMTPDebug = 0;                                       // Enable verbose debug output
    $mail->isSMTP();                                            // Set mailer to use SMTP
    $mail->Host       = $email_host;  // Specify main and backup SMTP servers
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->Username   = $email_username;                     // SMTP username
    $mail->Password   = $email_pw;                               // SMTP password
    $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, ssl also accepted
    $mail->Port       = $email_port;                                    // TCP port to connect to
    $mail->CharSet = "UTF-8";                                // TCP port to connect to
    //Recipients
    if ($answerToName != "") {
      $mail->addReplyTo($answerToEmail, $answerToName);
    } else {
    }
    // $mail->addReplyTo($answerToEmail, $answerToName);
    $mail->setFrom($email_username, $email_name);
    if ($toName == "") {
      $mail->addAddress($toEmail);     //Add a recipient
    } else {
      $mail->addAddress($toEmail, $toName);     //Add a recipient
    }
    
    //$mail->addAddress('ellen@example.com');               //Name is optional
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //Attachments
    //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $betreff;
    $mail->Body    = $inhalt;
    $mail->AltBody = strip_tags($inhalt);

    $mail->send();
//    echo 'Message has been sent';
    return "";
  } catch (Exception $e) {
    throw new \Exception($mail->ErrorInfo, 500);
    
    return "Fehler: E-Mail konnte nicht gesendet werden.";

    return "Fehler: Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
  }

  return "";


}

}