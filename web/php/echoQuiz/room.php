<?php

namespace echoQuiz;

use \app_ed_tech\pdoDb;
use \app_ed_tech\edTech;
use \app_ed_tech\app_func;

class room
{

  public $roomId;

  /**
   * b... break
   * z... end feedback
   * q... question
   * number... question id
   * x ... closed
   */
  public $roomPhase;
  public $roomLang;
  public $roomEmail;
  public $roomPw;
  public $roomApiKey;
  public $f_feedbackGroup;

    public function __construct($_roomId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare(
            'SELECT * FROM rooms WHERE roomId = :roomId'
        );
        $stmt->execute(['roomId' => $_roomId]);

        $data = $stmt->fetch();
        $stmt->closeCursor();

        if (!empty($data)) {
            $this->roomId = $data['roomId'];
            $this->roomPhase = $data['roomPhase'];
            $this->roomPw = $data['roomPw'];
            $this->roomApiKey = $data['roomApiKey'];
            $this->roomEmail = $data['roomEmail'];
            $this->roomLang = $data['roomLang'];
            $this->f_feedbackGroup = $data['f_feedbackGroup'];

            if ($this->roomPhase == "x") {
              throw new \Exception("Room ($_roomId) not found", 404003);
            }
        } else {
            throw new \Exception("Room ($_roomId) not found", 404003);
        }
    }

    public function saveToDb()
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("UPDATE rooms SET
                roomPhase = :roomPhase, f_feedbackGroup = :f_feedbackGroup, roomLang = :roomLang, roomApiKey = :roomApiKey, roomPw = :roomPw
            WHERE roomId = :roomId");

        $stmt->execute([
            'roomPhase' => app_func::secureCharForMysql($this->roomPhase),
            'f_feedbackGroup' => app_func::secureCharForMysql($this->f_feedbackGroup),
            'roomLang' => app_func::secureCharForMysql($this->roomLang),
            'roomApiKey' => app_func::secureCharForMysql($this->roomApiKey),
            'roomPw' => app_func::secureCharForMysql($this->roomPw),
            'roomId' => app_func::secureCharForMysql($this->roomId),
        ]);

        $stmt->closeCursor();
    }

    public function updateFromPost()
    {
        // $this->roomPhase = $_POST["roomPhase"];
        // $this->f_feedbackGroup = $_POST["f_feedbackGroup"];

        $oldLang = $this->roomLang;
        $this->roomLang = $_POST["roomLang"];

        if ($this->roomLang == "en" && $oldLang != "en" && $this->f_feedbackGroup == 1) {
          $this->f_feedbackGroup = 3;
        }
        if ($this->roomLang == "de" && $oldLang != "de" && $this->f_feedbackGroup == 3) {
          $this->f_feedbackGroup = 1;
        }
        

        if (isset($_POST["roomApiKey"]) && substr($_POST["roomApiKey"], -1) !== '*') {
          $this->roomApiKey = $_POST["roomApiKey"];
        }
        $this->roomPw = $_POST["newRoomPw"];
    }

    public function setRoomPhase($newPhase)
    {
      $this->roomPhase = app_func::secureCharForMysql($newPhase);
      $this->saveToDb();

      $return = $this->sendRoomPhaseOverWs();


      return json_encode($return);
    }

    public function sendRoomPhaseOverWs()
    {
      $return = $this->getRoomPhaseStatus();
      app_func::sendWsPost("eq!{$this->roomId}", array_merge($return));
      return $return;
    }

    /**
     * @return int NewId
     */
    public static function insertToDb()
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("INSERT INTO rooms 
            (roomPhase) VALUES 
            (:roomPhase)");
        $stmt->execute([
            "roomPhase" => app_func::secureCharForMysql($_POST["roomPhase"]),
        ]);
        $new_id = $db->lastInsertId();
        $stmt->closeCursor();
        return $new_id;
    }

    
  public function getRoomStatusJson ($forTnId = 0) {

    $tnAnswer = [];
    $q = question::returnQuestionForRoomId($this->roomId);
    if ($forTnId > 0 && $q) {
      $answer = answer::getAnswerForTnAndQuestion($forTnId, $q["qId"]);
      if ($answer) {
        $tnAnswer = ["aS" => $answer->answer_text, "ownAId" => $answer->answerId];
      }
    }

    return array_merge($q, self::getRoomPhaseStatus(), $tnAnswer);
  }

  public function getRoomPhaseStatus () {

    if (is_numeric($this->roomPhase)) {
      $answer = new answer($this->roomPhase);

      $ratings = rating::getRatingsForAnswer($this->roomPhase);     

      $class = "";
      $gradeText = "";
      if ($answer->answer_grade == 4) {
        $class = "like";
        $gradeText = "<span class='likebtn'><i class='fa fa-check' aria-hidden='true'></i></span>";
      } else if ($answer->answer_grade == 5) {
        $class = "openend";
        $gradeText = "<span class='openendbtn'><i class='fas fa-balance-scale-right' aria-hidden='true'></i></span>";
      } else if ($answer->answer_grade == 6) {
        $class = "dislike";
        $gradeText = "<span class='dislikebtn'><i class='fa fa-times' aria-hidden='true'></i></span>";
      }
      
      $a = "<article class='vis force $class' data-r0={$ratings["r0"]} data-r1={$ratings["r1"]} data-r2={$ratings["r2"]} data-r3={$ratings["r3"]} id='eyeArticle'><p>$answer->answer_text</p>$gradeText</article>";

      return ["phase" => $this->roomPhase, "eyeT" => $a];
    }
    return ["phase" => $this->roomPhase];
  }

  public static function getNchanJsForRoom ($roomId, $roomLang = "de") {
    global $wsshost;
    return "<script>
    roomId = $roomId;
    roomLang = '$roomLang';
    startNchan('{$wsshost}');
  </script>";
  }

  public static function getUserRoomId () {
    if (isset($_SESSION['roomId'])) {
      return $_SESSION['roomId'];
    } else {
      return 0;
    }
  }
  public static function setUserRoomId ($newId) {
    $_SESSION['roomId'] = $newId;
  }
  
  public static function logoutFromRoomId ($roomId) {
    $_SESSION['tn_roomId'] = array_diff($_SESSION['tn_roomId'], [$roomId]);
    // return;
  }

  public static function feRoomUiSelection () { 

    
    echo "
        <main id='main'>
        <form action='/api/setRoom'  method='POST' id='inputs'>
        <div class='section'>
        <label for='raumId'>Raum Nummer:</label>
        <input name='roomId' id='raumId' class='large' value='' />
        <button class='signup btn' id='submitAnswer'><i class='fa fa-sign-in-alt' aria-hidden='true'></i></button>
        </div>
     
        </form>
        ";
        return;
  }

  public static function feRoomUi ($roomId) {
    $roomId = app_func::secureCharForMysql($roomId);
    
    $room = new room($roomId);
    $roomStatus = $room->getRoomStatusJson();
    

    if (isset($roomStatus["phase"])) {

      if (isset($_GET["setTnName"]) && $_GET["setTnName"] == 1) {
        tn::insertToDb($roomId);
        edTech::throw303SeeOther("/raum/$roomId");
      }

      if (!in_array($roomId, tn::getSessionTnRoomIdArray())) {
        
        $tnNameText = "Name für EchoScore:";
        $consentText = "Ich stimme zu, dass meine eingegebenen Daten gemäß den <a href='/datenschutz/' target='_blank'>Datenschutzinformationen und Teilnahmebedingungen</a> verarbeitet und für wissenschaftliche Zwecke ausgewertet werden dürfen.";
        $annonymText = "Deine Antworten sind anonym, auch für Vortragende. Nur gemeldete Inhalte werden geprüft, und in Ausnahmefällen zugeordnet.";
        $pointText = "Sammle Punkte mit jeder Aktivität! Dein EchoScore wird erst zum Schluss für den Vortragenden sichtbar.";

        // TN Signup
        if ($room->roomLang == "en") {
          echo ui::getHtmlHeader("Room $roomId", "Room $roomId");
          
          $tnNameText = "Name for EchoScore:";
          $consentText = "I agree that my entered data may be processed in accordance with the <a href='/datenschutz/' target='_blank'>privacy policy and terms of participation</a> and used for scientific purposes.";
          $annonymText = "Your answers are anonymous, even for presenters. Only reported content will be checked and assigned in exceptional cases.";
          $pointText = "Collect points with every activity! Your EchoScore will only be visible to the presenter at the end.";
        } else {
          echo ui::getHtmlHeader("Raum $roomId", "Raum $roomId");

        }

        echo "
        <main id='main'>
        <form action='/raum/$roomId?setTnName=1' method='post' id='inputs'>
        <label for='tn_name'>$tnNameText</label>
        <input name='tn_name' class='large' value='' maxlength='240'  required />
        <input name='roomId' value='$roomId' type='hidden' />

          <p class='consentInfo'>
            <img src='/badges/question.png' />
            $pointText
          </p>
          <p class='consentInfo'>
            <img src='/badges/false.png' />
            
            $annonymText
          </p>
        
          <label for='consent' class='consent consentInfo'>
            <input type='checkbox' name='consent' id='consent' required>
            <span>
            $consentText
            </span>
          </label>
        

        <button class='signup btn' id='submitAnswer'><i class='fa fa-sign-in-alt' aria-hidden='true'></i></button>
        </form>
        </main>
        ";

        echo edTech::getHtmlFooter();
        // echo room::getNchanJsForRoom($roomId);

        return;
      }


      // TN ist Known
      $thisTn = tn::getTnByRoomId($roomId);

      echo ui::getHtmlHeader("Raum $roomId", "<a href='/raum/$roomId'>Raum $roomId ({$thisTn->tnName})</a><script>tnId = {$thisTn->tnId}</script>");
      
      echo room::getNchanJsForRoom($roomId, $room->roomLang);

      $roomStatus = $room->getRoomStatusJson($thisTn->tnId);

      
      echo "
      <main>
      <mark id='eye'></mark>
      <mark id='q'></mark>
      <div id='ownAnswer'>
        <mark id='qInput'></mark>
        <mark id='ownAgrade'></mark>
      </div>
      
      <mark id='rating' class='answers'></mark>
      ";

      echo "<script>handleEq(" . json_encode($roomStatus). ");getRatings();</script>
      </main>";

      echo edTech::getHtmlFooter();


    } else {
      echo edTech::header404NotFound();
      echo ui::getHtmlHeader("Raum nicht geöffnet");

      echo "<h1>Raum <i>$roomId</i> nicht geöffnet</h1>";
    }
    
    exit();
  }



  public static function feBeamerRoomUi ($roomId) {
    $roomId = app_func::secureCharForMysql($roomId);
    
    $room = new room($roomId);
    $roomStatus = $room->getRoomStatusJson();
    

    if (isset($roomStatus["phase"])) {


      global $host_shorturl;
      global $host_shorthost;

      // Beamer is always ist Known
      if ($room->roomLang == "en") {
        echo ui::getHtmlHeader("Room $roomId", "Room $roomId", 2, "<a href='{$host_shorthost}/$roomId' class='expletus'>{$host_shorturl}/$roomId</a>");
      } else {
        echo ui::getHtmlHeader("Raum $roomId", "Raum $roomId", 2, "<a href='{$host_shorthost}/$roomId' class='expletus'>{$host_shorturl}/$roomId</a>");
      }
      
      
      echo room::getNchanJsForRoom($roomId, $room->roomLang);

      $roomStatus = $room->getRoomStatusJson();

      
      echo "
      <mark id='eye'></mark>
      <mark id='q'></mark>
      <div id='ownAnswer'>
        <mark id='qInput'></mark>
        <mark id='ownAgrade'></mark>
      </div>
      
      <mark id='rating' class='answers'></mark>
      ";

      echo "<script>
      beamer=1;
      shortUrl='{$host_shorturl}';
      shortHost='{$host_shorthost}';
      handleEq(" . json_encode($roomStatus). ");getRatings();</script>";

    } else {
      echo edTech::header404NotFound();
      echo ui::getHtmlHeader("Raum nicht geöffnet");

      echo "<h1>Raum <i>$roomId</i> nicht geöffnet</h1>";
    }
    
    exit();
  }



  public function getAdminVorbereitungHtml() {

    $secureApiKey = substr($this->roomApiKey, 0, 15) . "*";
    if ($secureApiKey == "*") {
      $secureApiKey = "";
    }

    
    global $host_shorturl;
    global $host;

    $a = "<section class='vorbereitung'>
    <h2>Vorbereitung</h2>
    <form action='/api/setRoomAdminDetails' method='POST' >
    Teilnehmer Link: <code>{$host_shorturl}/{$this->roomId}</code><br>
    Beamer Link: <code>{$host}/beamer/{$this->roomId}</code><br>
    Admin Link: <code>{$host}/admin/{$this->roomId}</code><br>

    <div>
    Raum Sprache:
    <select name='roomLang'>
      <option value='de' " . ($this->roomLang == 'de' ? 'selected' : '') . ">Deutsch</option>
      <option value='en' " . ($this->roomLang == 'en' ? 'selected' : '') . ">English</option>
      
    </select>
    </div>
    <div>
        Admin Passwort: 

    <span class='btn' onclick='showAdminPassword(this)'>Anzeigen</span>
    <script>

      function showAdminPassword(sender) {
      
      const passwordElement = document.getElementById('adminPassword');
      passwordElement.style.display = 'inline-block';
      sender.style.display = 'none';
      }
    </script>
    <span id='adminPassword' style='display:none;' >
      
      <input value='{$this->roomPw}' name='newRoomPw'  class='font-mono' />
    </span>
    </div>
    <div>
    
    <div>
        Raum schließen: 

    <span class='btn' onclick='showAdminClose(this)'>Anzeigen</span>
    <script>

      function showAdminClose(sender) {
      
      const passwordElement = document.getElementById('adminClose');
      passwordElement.style.display = 'inline-block';
      sender.style.display = 'none';
      }
    </script>
    <span id='adminClose' style='display:none;' >
      Nachdem Sie das Passwort eingegeben und absenden, wird der Raum geschlossen. Auch Sie können die Auswertung danach nicht mehr ansehen.
      <div><label><input type='checkbox' name='closeroom' value='1' /> Raum schließen</label></div>
      Admin Passwortabfrage: <input name='closepw'  class='font-mono' />
    </span>
    </div>
    <div>
    
    ChatGPT API Key: 

    <span class='btn' onclick='showApiKey(this)'>Anzeigen</span>
    <script>
      function showApiKey(sender) {
      
      const apiKeyElement = document.getElementById('apiKey');
      apiKeyElement.style.display = 'inline-block';
      sender.style.display = 'none';
      }
    </script>
    <span id='apiKey' style='display:none;'>
      <input value='$secureApiKey' name='roomApiKey' placeholder='sk-'  class='font-mono' />
    </span>
    </div>

    <input type='submit' value='Speichern' class='btn' />
    <input name='roomId' value='{$this->roomId}' type='hidden' />
    <input name='roomPw' value='{$this->roomPw}' type='hidden' />

    </form>

    <p>
    
    </p>

    </section>";
    return $a;
  }

}