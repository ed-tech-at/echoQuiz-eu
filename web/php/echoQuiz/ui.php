<?php

namespace echoQuiz;

class ui
{

  public static function getHtmlHeader ($titel = "echoQuiz.eu", $headerLeft = "",  $type = 0, $headerRight = "<div class='badge expletus' id='badgeCount'></div>") {
    global $rootDir;
    global $version;
    $bodyClass = "";
    $headHtml = "";
    $logoTag = "a";
    if ($type == 1) {
      // admin
      $bodyClass = "admin";
      $headerLeft = "<a href='/admin'>Admin</a>" . $headerLeft;
      $headHtml = "<script src='{$rootDir}/files/eq-admin-js.js?$version'></script><script>adminView=1;</script>";
      $logoTag = "div";
    } elseif ($type == 2) {
      // beamer
      $bodyClass = "beamer";
      $headHtml = "<script src='{$rootDir}/files/qrcode.min.js?$version'></script>";
    }

    return "<html>
  <head>
    <meta charset='UTF-8' />
    <meta http-equiv='Content-Language' content='de'>
    <meta name='viewport' content='width=device-width, initial-scale=1' />
    <link href='{$rootDir}/files/fonts/fonts.css' rel='stylesheet'>
    <link href='{$rootDir}/files/echoquiz-style.css?$version' rel='stylesheet'>
    <link rel='icon' type='image/png' href='/files/eq_v1.png'>
    <title>$titel</title>
      <script src='{$rootDir}/files/htmx_2.0.1.min.js' ></script>
      <script src='{$rootDir}/files/jquery_3.7.1.min.js' ></script>
      <script src='{$rootDir}/files/NchanSubscriber.js' ></script>
      <script src='{$rootDir}/files/eq-js.js?$version' ></script>
  
      <link rel='stylesheet' href='{$rootDir}/files/fonts/fontawesome-free-5.15.3-web/css/all.min.css'>
    $headHtml
  </head>


  <body class='inter $bodyClass'>

    <header>
      <div class='headerLeft'>
        $headerLeft
      </div>
      
      <$logoTag href='/' class='eqlogo' id='eqlogo'>        
        <span class='a expletus'>Echo</span>
        <span class='b expletus' >Quiz</span>
      </$logoTag>

      <div class='headerRight'>
        $headerRight
         
        
      </div>
    </header>
    <div class='headerSpacer'></div>
      ";
    
  }
}