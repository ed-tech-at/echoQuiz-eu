<?php

namespace echoQuiz;

use \app_ed_tech\pdoDb;
use \app_ed_tech\app_func;
use app_ed_tech\edTech;

class tn
{
  public $tnId;
  public $f_roomId;
  public $tnName;
  public $phpSessionId;
  public $logoutTime;
  public $loginTime;
  public $score;

  public function __construct($_tnId)
  {
    $db = pdoDb::getConnection();

    $stmt = $db->prepare(
      'SELECT * FROM tn WHERE tnId = :tnId'
    );
    $stmt->execute(['tnId' => $_tnId]);

    $data = $stmt->fetch();
    $stmt->closeCursor();

    if (!empty($data)) {
      $this->tnId = $data['tnId'];
      $this->f_roomId = $data['f_roomId'];
      $this->tnName = $data['tnName'];
      $this->phpSessionId = $data['phpSessionId'];
      $this->logoutTime = $data['logoutTime'];
      $this->loginTime = $data['loginTime'];
      $this->score = 0;
    } else {
      throw new \Exception("TN ($_tnId) not found", 404002);
    }
  }

  public function saveToDb()
  {
    $db = pdoDb::getConnection();

    $stmt = $db->prepare("UPDATE tn SET
            f_roomId = :f_roomId,
            tnName = :tnName,
            phpSessionId = :phpSessionId,
            logoutTime = :logoutTime
        WHERE tnId = :tnId");

    $stmt->execute([
      'f_roomId' => app_func::secureCharForMysql($this->f_roomId),
      'tnName' => app_func::secureCharForMysql($this->tnName),
      'phpSessionId' => app_func::secureCharForMysql($this->phpSessionId),
      'logoutTime' => app_func::secureCharForMysql($this->logoutTime),
      'tnId' => app_func::secureCharForMysql($this->tnId),
    ]);

    $stmt->closeCursor();
  }

  public function updateFromPost()
  {
    $this->tnName = $_POST["tnName"];
    $this->phpSessionId = $_POST["phpSessionId"];
  }

  public function calcScore()
  {
    
    $badgeCount = 0;

    $db = pdoDb::getConnection();
    $stmt = $db->prepare(
      'SELECT COUNT(*) as ratingCount FROM ratings WHERE f_tnId = :tnId AND rating > 0'
    );
    $stmt->execute(['tnId' => $this->tnId]);
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if ($result) {
      $badgeCount += $result['ratingCount'];
    }
    
    $stmt = $db->prepare(
      'SELECT COUNT(f_tnId) as feedbackCount FROM feedback WHERE f_tnId = :tnId'
    );
    $stmt->execute(['tnId' => $this->tnId]);
    $result = $stmt->fetch(\PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    if ($result) {
      $badgeCount += $result['feedbackCount'];
    }

    $stmt = $db->prepare(
      'SELECT COUNT(*) as answerCount, answer_grade FROM answers WHERE f_userId = :tnId GROUP BY answer_grade'
    );
    $stmt->execute(['tnId' => $this->tnId]);
    $results = $stmt->fetchAll();
    $stmt->closeCursor();
    
    foreach ($results as $result) {

      if ($result) {
        $multiplier = 5;
        if ($result['answer_grade'] == 4) {
          //correct
          $multiplier = 8;
        }
        if ($result['answer_grade'] == 5) {
          //openend
          $multiplier = 7;
        }
        $badgeCount += $result['answerCount'] * $multiplier;
      }
    }
    $this->score = $badgeCount;
    return $badgeCount;
  }

  /**
   * @return int NewId
   */
  public static function insertToDb($f_roomId)
  {
    $db = pdoDb::getConnection();

    $stmt = $db->prepare("INSERT INTO tn 
        (f_roomId, tnName, phpSessionId, loginTime) VALUES 
        (:f_roomId, :tnName, :phpSessionId, :loginTime)");
    $stmt->execute([
      "f_roomId" => app_func::secureCharForMysql($f_roomId),
      "tnName" => app_func::secureCharForMysql($_POST["tn_name"]),
      "phpSessionId" => app_func::secureCharForMysql(session_id()),
      "loginTime" => app_func::secureCharForMysql(edTech::getTimestampDb()),
    ]);
    $new_id = $db->lastInsertId();
    $stmt->closeCursor();

    $tn = new self($new_id);

    self::addToSessionTnRoomIdArray($f_roomId);

    app_func::sendWsPost("eq!!{$f_roomId}", array_merge(['tnId' => $tn->tnId, 'tnName' => $tn->tnName]));

    return $new_id;
  }

  public static function getTnByRoomId($f_roomId)
  {
    $db = pdoDb::getConnection();

    $stmt = $db->prepare(
      'SELECT * FROM tn WHERE f_roomId = :f_roomId AND phpSessionId = :phpSessionId'
    );
    $stmt->execute([
      'f_roomId' => app_func::secureCharForMysql($f_roomId),
      'phpSessionId' => app_func::secureCharForMysql(session_id()),
    ]);

    $data = $stmt->fetch();
    $stmt->closeCursor();

    if (!empty($data)) {
      return new self($data['tnId']);
    } else {
      throw new \Exception("TN not found for the given room and session ID", 404003);
    }
  }

  /**
   * @param int $f_roomId The ID of the room to retrieve tn objects for.
   * @return tn[] An array of tn objects.
   */
  public static function getTnList($f_roomId)
  {
    $db = pdoDb::getConnection();

    $room = new room($f_roomId);
    if ($room->roomPhase == "z") {
      $stmt = $db->prepare(
        'SELECT * FROM tn WHERE f_roomId = :f_roomId '
      );
    } else {
      $stmt = $db->prepare(
        'SELECT * FROM tn WHERE f_roomId = :f_roomId AND (logoutTime = "0000-00-00 00:00:00")'
      );
    }
    
    $stmt->execute(['f_roomId' => $f_roomId]);

    $data = $stmt->fetchAll();
    $stmt->closeCursor();

    $tnList = [];
    foreach ($data as $row) {
      $tn = new self($row['tnId']);
      
      if ($room->roomPhase == "z") {
        $tn->calcScore();
      }

      $tnList[] = $tn;

    }
    return $tnList;

  }

  public static function getTnJson($f_roomId)
  {
    $tnList = self::getTnList($f_roomId);
    $result = [];

    foreach ($tnList as $tn) {
      $result[] = [
        'tnId' => $tn->tnId,
        'tnName' => $tn->tnName,
        'score' => $tn->score,
      ];
    }

    return json_encode($result);
  }

  /**
   * @return array The session array containing tn_roomId.
   */
  public static function getSessionTnRoomIdArray()
  {
    if (!isset($_SESSION['tn_roomId'])) {
      $_SESSION['tn_roomId'] = [];
    }
    return $_SESSION['tn_roomId'];
  }

  /**
   * @param int $id The ID to add to the session array tn_roomId.
   */
  public static function addToSessionTnRoomIdArray($id)
  {
    if (!isset($_SESSION['tn_roomId'])) {
      $_SESSION['tn_roomId'] = [];
    }
    if (!in_array($id, $_SESSION['tn_roomId'])) {
      $_SESSION['tn_roomId'][] = $id;
    }
  }

  public static function getAdminTnHtml($f_roomId) {
    // echo tn::getTnJson($f_roomId);

    $a = "<h2>Teilnehmer</h2>
    <div>
    <button class='btn' onclick='setRoomPhaseBtn(\"b\")'>Pause starten</button>
    <button class='btn' onclick='setRoomPhaseBtn(\"z\")'>Abschlussfrage starten</button>
    </div>
    <mark id='tn'></mark>
    <mark id='feedbackList'></mark>
    <script>
    tnList = " . tn::getTnJson($f_roomId) . ";
    parseTnList()</script>"
    ;
    return $a;
  }
  
  public static function tnLogout()
  {
    $db = pdoDb::getConnection();

    $stmt = $db->prepare("UPDATE tn SET logoutTime = :logoutTime WHERE phpSessionId = :phpSessionId");
    $stmt->execute([
      'logoutTime' => edTech::getTimestampDb(),
      'phpSessionId' => app_func::secureCharForMysql(session_id()),
    ]);

    $stmt->closeCursor();
    session_regenerate_id(true);
    session_destroy();
  }

}