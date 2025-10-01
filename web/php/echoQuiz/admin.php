<?php

namespace echoQuiz;

use \app_ed_tech\pdoDb;
use \app_ed_tech\edTech;
use \app_ed_tech\app_func;



class admin
{

  public static function  checkLogin() {
    return;
  }

  public static function  getAdminLoginPage() {
    admin::checkLogin();


    if (isset($_POST["roomId"]) && isset($_GET["login"])) {
      try {
        $room = new room($_POST["roomId"]);
        if (isset( $_SESSION["admin_roomId"]) && in_array($room->roomId, $_SESSION["admin_roomId"])) {
          edTech::throw303SeeOther("/admin/". $_POST["roomId"]);
        }
        if ($room->roomPw == $_POST["roomPw"]) {
          $_SESSION["admin_roomId"][] = $_POST["roomId"];
          edTech::throw303SeeOther("/admin/". $_POST["roomId"]);
        } else {
          app_func::addSessionStatusMeldung("error", "Passwort falsch");
        }
      } catch (\Exception $e) {
        app_func::addSessionStatusMeldung("error", "Raum nicht gefunden");
      }
    }

    if (isset($_GET["newRoom"])) {

      $roomPw = app_func::secureCharForMysql($_POST["roomPw"]);
      $raumPasswort = "Das Passwort wurde von Ihnen bei der Erstellung gesetzt.<br>";
      if (empty($roomPw)) {
        $roomPw = base64_encode(random_bytes(6));
        $raumPasswort = "Das Passwort wurde automatisch erstellt. Es ist: " . $roomPw . "<br>";
      }

      $db = pdoDb::getConnection();
      $stmt = $db->prepare(
        'INSERT INTO rooms (roomPw, roomEmail) VALUES (:roomPw, :roomEmail)'
      );
      $stmt->execute([
        'roomPw' => app_func::secureCharForMysql($roomPw),
        'roomEmail' => app_func::secureCharForMysql($_POST["email"]),
      ]);

      $stmt->closeCursor();

      $roomId = $db->lastInsertId();

      mailer::sendAnMail(
        "echoQuiz Raum $roomId erstellt",
        "Guten Tag,<br>
ihr Raum auf echoQuiz.eu wurde erstellt. <br>
<br>
Der Raumcode ist: " . $roomId . ".<br>
$raumPasswort
<br>
<br>
Die Admin-Oberfläche ist unter https://echoquiz.eu/admin/" . $roomId . " erreichbar.<br>
<br>
Viel Spaß mit echoQuiz!",
$_POST["email"]
      );
      app_func::addSessionStatusMeldung("success", "Raum erfolgreich erstellt. E-Mail wurde gesendet.");
      $_SESSION["admin_roomId"][] = $roomId;
      edTech::throw303SeeOther("/admin/". $roomId);

    }

    echo ui::getHtmlHeader("Admin", "", 1);
    echo "<main>";

    echo app_func::getSessionStatusMeldung();

    echo "
    
    <form method='post' action='/admin?login=1'>
    <h2>Admin-Anmeldung zu bestehendem Raum</h2>
    Raumcode: <input name='roomId' required='required' /><br>
    Passwort: <input name='roomPw' type='password' required='required' /><br>
    <input type='submit' class='btn'>
    </form>
    ";


    // List all admin_roomId values
    if (isset($_SESSION["admin_roomId"])) {
      echo "<h2>Aktive Admin-Anmeldungen für Räume</h2><ul>";
      foreach ($_SESSION["admin_roomId"] as $roomId) {
        echo "<li><a href='/admin/" . htmlspecialchars($roomId) . "'>Raum " . htmlspecialchars($roomId) . "</a></li>";
      }
      echo "</ul>";
    }


    echo "<a href='/logout'>von allen Räumen abmelden</a>
    
    <h2 style='margin-top: 3em;'>oder neuen Raum eröffnen</h2>
    <a href='/admin/neu/'>neuen Raum eröffnen</a><br>
    
    </main>
    ";  
    
    echo edTech::getHtmlFooter();

    return;
  }

  public static function  getAdminRoomPage($roomId) {
    
    echo ui::getHtmlHeader("Admin", " / Raum " . $roomId, 1);
    
    echo "<main>";
    echo app_func::getSessionStatusMeldung();
    
    $room = new room($roomId);

    $currentQuestion = $room->getRoomStatusJson();

    echo room::getNchanJsForRoom($roomId);

    echo "
    <mark id='q'></mark>
    <script>
    handleEq(" . json_encode($currentQuestion) . ")
    </script>
    
    

    ";

    // <nav>
    // <span class='nav-vor'>Vorbereitung</span>
    // <span class='nav-tn'>Teilnehmer</span>
    // <span class='nav-fragen'>Fragenpool</span>
    // <span class='nav-quiz'>Quiz</span>
    // <span class='nav-echo'>Echo</span>
    // </nav>

    global $wsshost;

    echo "<script>startAdminNchan('{$wsshost}');</script>";


    echo $room->getAdminVorbereitungHtml();

    echo tn::getAdminTnHtml($roomId);

    if ($room->roomPhase == "z") {
      echo "<script>forceFeedbackList();</script>";
      echo "<script>forceTnList();</script>";

    }


    echo question::newQuestionInterface($roomId);
    

    echo "<h2>Antworten</h2>
    <mark id='answers'></mark>
    
    <script>
    
    ";

    $questionList = question::returnQuestionListForRoom($roomId);

    foreach ($questionList as $question) {

      echo "handleAdminEq(" . json_encode($question->formatQuestion(1)) . ");";
    }

    $answerList = answer::getAnswersForRoom($roomId);
    foreach ($answerList as $answer) {
      echo "handleAdminEq(" . json_encode($answer) . ");";
    }
    // echo json_encode($answer);
    echo "</script>
    </main>";

    echo edTech::getHtmlFooter();

    
  }
  
  public static function  getAdminTable($roomId) {
    
    $room = new room($roomId);
    // $a = "<h2>Abschluss-Fragen Auswertung <a target='_blank' href='/admin/{$room->roomId}/tabelle'>Tabelle</a></h2>";
    $feedbackBankId = $room->f_feedbackGroup;
    $feedbackList = feedbackBank::getFeedbackByGroup($feedbackBankId);
    $a = "
    <style>
    table, th, td {
      border: 1px solid black;
      border-collapse: collapse;
    }
    </style>
    <main class='admin'>
    <table class='echoScore'>
    <thead>
    <th>Frage für Raum $roomId</th>
    <th>Antworten</th>
    <th>Mittelwert</th>
    <th>Empirische Standardabweichung</th>
    <th>Stern 1</th>
    <th>Stern 2</th>
    <th>Stern 3</th>
    <th>Stern 4</th>
    <th>Stern 5</th>
    </thead>
    <tbody>
    ";
    foreach ($feedbackList as $feedback) {
      $a .= "<tr>";
      $a .=  "<td>" . $feedback->feedbackQ . "</td>";
      // $a =  "<mark id='feedback" . $feedback->feedbackBankId . "'></mark>";

      $statistik = feedbackBank::getFeedbackStatsForRoom($room->roomId, $feedback->feedbackKey);
      
      $a .=  "<td>" . $statistik["anzahl"] . "</td>";
      $a .= "<td>" . edTech::numberFormat($statistik["mittelwert"], 2) . "</td>";
      $a .= "<td>" . edTech::numberFormat($statistik["standardabweichung"], 2) . "</td>";
      
      $a .= "<td>" . $statistik["stars"][1] . "</td>";
      $a .= "<td>" . $statistik["stars"][2] . "</td>";
      $a .= "<td>" . $statistik["stars"][3] . "</td>";
      $a .= "<td>" . $statistik["stars"][4] . "</td>";
      $a .= "<td>" . $statistik["stars"][5] . "</td>";
      
      $a .= "</tr>";
    }

    $a .= "</tbody></table>";



    $tnList = tn::getTnList($roomId);
    $a .= "<table>
    <thead>
    <tr>
    <th>Name</th>
    <th>EchoScore Punkte</th>
    </tr>
    </thead>
    <tbody>";
    foreach ($tnList as $tn) {
      $a .= "<tr>";
      $a .= "<td>" . $tn->tnName . "</td>";
      $a .= "<td>" . $tn->score . "</td>";
      $a .= "</tr>";
    }
    $a .= "</tbody></table>";

    return $a;
    
  }

  public static function  getAdminNewRoomPage() {
    
    echo ui::getHtmlHeader("Admin", " / Neuer Raum", 1);
    
    echo "
    <main>
    <form method='post' action='/admin?newRoom=1'>
    <h2>Neuen echoQuiz Raum erstellen</h2>
    Ihre E-Mail Adresse: <input name='email' type='email' required='required' /><br>
    Passwort (optional - wählen Sie ihr Passwort jetzt, oder es wird automatisch eines erstellt): <input name='roomPw' type='password' /><br>
    
     <label for='consent' class='consent consentInfo'>
            <input type='checkbox' name='consent' id='consent' required>
            <span>
            Ich stimme zu, dass die eingegebenen Daten gemäß den <a href='/datenschutz/' target='_blank'>Datenschutzinformationen und Teilnahmebedingungen</a> verarbeitet und für wissenschaftliche Zwecke ausgewertet werden dürfen. Ich stimme zu, kein urheberrechtlich geschütztes Material, Fragen oder Aussagen zu
            Gewalt, Waffen, illegale Aktivitäten, Diskriminierung oder Hassrede oder jegliche Inhalte, die gegen geltende Gesetze oder ethische Standards verstoßen einzugeben.
            </span>
          </label>

    <input type='submit'>
    </form>
    ";
    
  }



}