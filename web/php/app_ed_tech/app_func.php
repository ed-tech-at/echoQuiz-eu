<?php

namespace app_ed_tech;


class app_func
{

  public static function sendWsPost($channel, $msgArray)
  {
    global $nchan_pw;
    global $nchan_host;
    $url = "$nchan_host/pub_id/?password={$nchan_pw}&id=" . $channel;
    $data = json_encode($msgArray);
    $options = [
      'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'method' => 'POST',
        'content' => ($data),
        'timeout' => 2
      ],
    ];

    $context = stream_context_create($options);

    return file_get_contents($url, false, $context);
  }

  public static function getTimestampDb()
  {
    return date("Y-m-d H:i:s");
  }



  public static function getSessionStatusMeldung()
  {
    $statusArray = [];
    if (isset($_SESSION["status"])) {
      $statusArray = $_SESSION["status"];
    }
    $_SESSION["status"] = [];

    $a = "";
    foreach ($statusArray as $status) {
      # ["warning", "Yes"];
      $a .= "   
<div class='alert alert-dismissible alert-{$status[0]}'>
<p class='mb-0'>{$status[1]}</p>
</div>
";
// $a .= "   
// <div class='alert alert-dismissible alert-{$status[0]}'>
// <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
// <p class='mb-0'>{$status[1]}</p>
// </div>
// ";
    }
    return $a;
  }

  /**
   * @param String $typ primary, secondary, success, danger, warning, info , light, dark
   */

  public static function addSessionStatusMeldung($typ, $text)
  {
    $_SESSION["status"][] = [$typ, $text];
  }


  public static function secureCharForMysql($string)
  {
    return edTech::secureCharForMysql($string);
  }
}
