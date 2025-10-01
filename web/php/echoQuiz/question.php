<?php

namespace echoQuiz;

use \app_ed_tech\pdoDb;
use \app_ed_tech\edTech;
use \app_ed_tech\app_func;

class question
{
  public $questionId;
  public $f_roomId;
  public $questionText;

  public function __construct($_questionId)
  {
    $db = pdoDb::getConnection();

    $stmt = $db->prepare(
      'SELECT * FROM questions WHERE questionId = :questionId'
    );
    $stmt->execute(['questionId' => $_questionId]);

    $data = $stmt->fetch();
    $stmt->closeCursor();

    if (!empty($data)) {
      $this->questionId = $data['questionId'];
      $this->f_roomId = $data['f_roomId'];
      $this->questionText = $data['questionText'];
    } else {
      throw new \Exception("Question ($_questionId) not found", 404001);
    }
  }

  public function saveToDb()
  {
    $db = pdoDb::getConnection();

    $stmt = $db->prepare("UPDATE questions SET
            f_roomId = :f_roomId,
            questionText = :questionText
        WHERE questionId = :questionId");

    $stmt->execute([
      'f_roomId' => app_func::secureCharForMysql($this->f_roomId),
      'questionText' => app_func::secureCharForMysql($this->questionText),
      'questionId' => app_func::secureCharForMysql($this->questionId),
    ]);

    $stmt->closeCursor();
  }

  public function updateFromPost()
  {
    $this->f_roomId = $_POST["f_roomId"];
    $this->questionText = $_POST["questionText"];
  }

  /**
   * @return int NewId
   */
  public static function insertToDb()
  {
    $db = pdoDb::getConnection();

    $stmt = $db->prepare("INSERT INTO questions 
        (f_roomId, questionText) VALUES 
        (:f_roomId, :questionText)");
    $stmt->execute([
      "f_roomId" => app_func::secureCharForMysql($_POST["f_roomId"]),
      "questionText" => app_func::secureCharForMysql($_POST["questionText"]),
    ]);
    $new_id = $db->lastInsertId();
    $stmt->closeCursor();
    return $new_id;
  }



  public static function newQuestionInterface ($room_id) {
    if (isset($_POST["questionText"])) {
      if (empty($_POST["questionText"])) {
        return ["error" => "Kein Fragetext"];
      }
      $newQ = new question(question::insertToDb());

      $room = new room(app_func::secureCharForMysql($_POST["f_roomId"]));
      $room->setRoomPhase("q");

      app_func::sendWsPost("eq!{$newQ->f_roomId}", array_merge($newQ->formatQuestion()));
      return ["admin" => ["placeholderQuestion" => $newQ->questionText]];
      
    }

    
    echo "
    
    <h2>Neue Quizfrage</h2>

    <form action='/api/newQ' class='formjs' id='question-ui'>
    <input name='f_roomId' value='$room_id' type='hidden'>
    <div class='newQuestion'>
      <input name='questionText' class='questionTextInput' id='questionTextInput' autcomplese='off'>
      <button class='questionbtn btn' id='submitAnswer'><i class='fas fa-satellite-dish' aria-hidden='true'></i></button>
    </div>
    </div>
    </form>
    <div class='answers'></div>
    ";
    ;
  }
  
  public static function returnQuestionForRoomId ($f_roomId) {
      $db = pdoDb::getConnection();
      $stmt = $db->prepare(
        'SELECT questionId FROM questions WHERE f_roomId = :f_roomId ORDER BY questionId DESC LIMIT 1'
      );
      $stmt->execute(['f_roomId' => $f_roomId]);
  
      $data = $stmt->fetch();
      $stmt->closeCursor();
  
      if (!empty($data)) {
        $question = new question($data["questionId"]);
        return $question->formatQuestion();
      }
      return [];
  }

  public function formatQuestion ($history = 0) {
    
    if ($history) {
      return ["qHistory" => $this->questionText, "qId" => $this->questionId];
    }
    return ["q" => $this->questionText, "qId" => $this->questionId];
    
  }
  
  
  /**
   * @return question[]
   */
  public static function returnQuestionListForRoom($f_roomId) {
    $db = pdoDb::getConnection();
    $stmt = $db->prepare(
      'SELECT questionId FROM questions WHERE f_roomId = :f_roomId ORDER BY questionId ASC'
    );
    $stmt->execute(['f_roomId' => $f_roomId]);

    $questions = [];
    while ($data = $stmt->fetch()) {
      $questions[] = new question($data['questionId']);
    }
    $stmt->closeCursor();

    return $questions;
  }
  
}
