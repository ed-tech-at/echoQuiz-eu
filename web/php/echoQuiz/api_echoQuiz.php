<?php
namespace echoQuiz;
use \app_ed_tech\pdoDb;
use \app_ed_tech\edTech;
use \app_ed_tech\app_func;

class api_echoQuiz {

  public static function doApi ($path) {
    
    if ($path =="newQ") {
      return json_encode(question::newQuestionInterface(0));
    }

    if ($path =="setTnName") {
      $newId = tn::insertToDb($_POST["roomId"]);

      $room = new room($_POST["roomId"]);

      $roomStatus = json_encode($room->getRoomStatusJson($newId));
      return $roomStatus ;
    }
    
    if ($path =="forceTnList") {
      
      
      $tnList = tn::getTnJson($_POST["roomId"]);
      return $tnList;
    }

    if ($path =="forceFeedbackList") {
      
      
      $room = new room($_POST["roomId"]);
      $a = "<h2>Abschluss-Fragen Auswertung <a target='_blank' href='/admin/{$room->roomId}/tabelle'>Tabelle</a></h2>";
      $feedbackBankId = $room->f_feedbackGroup;
      $feedbackList = feedbackBank::getFeedbackByGroup($feedbackBankId);
      foreach ($feedbackList as $feedback) {
        $a .=  "<p><b>" . $feedback->feedbackQ . "</b><br>";
        // $a =  "<mark id='feedback" . $feedback->feedbackBankId . "'></mark>";

        $statistik = feedbackBank::getFeedbackStatsForRoom($room->roomId, $feedback->feedbackKey);
        
        $a .=  $statistik["anzahl"] . " Rückmeldungen: ";
        $a .=  "Mittelwert: " . $statistik["mittelwert"] . " &plusmn; " . $statistik["standardabweichung"] . "<br></p>";
        
      }
      
      return json_encode(["feedbackList" => $a]);
    }

    
    


    if ($path =="answer") {
      $newId = answer::insertToDb();

      return json_encode(["aS" => app_func::secureCharForMysql($_POST["answer_text"]), "ownAId" => $newId]);
    }
    if ($path =="setRoom") {
      edTech::throw303SeeOther("/raum/" . $_POST["roomId"]);
    }
     if ($path =="setRoomAdminDetails") {
      if (isset($_POST["roomId"])) {
        if (!in_array($_POST["roomId"], $_SESSION["admin_roomId"])) {
          app_func::addSessionStatusMeldung("error", "Sie sind nicht als Admin angemeldet");
          edTech::throw303SeeOther("/admin");
        }

        
                  
        $room = new room($_POST["roomId"]);

        if (isset($_POST["closeroom"]) && $_POST["closeroom"] == "1") {
          if ($_POST["closepw"] != $room->roomPw) {
            app_func::addSessionStatusMeldung("error", "Passwort falsch");
            edTech::throw303SeeOther("/admin/" . $_POST["roomId"]);

            return;
          }
          
          $room->setRoomPhase("x");
          $_SESSION["admin_roomId"] = array_diff($_SESSION["admin_roomId"], [$_POST["roomId"]]);

          edTech::throw303SeeOther("/admin/");
        }
        $room->updateFromPost();
        $room->saveToDb();
        
        edTech::throw303SeeOther("/admin/" . $_POST["roomId"]);



      }
    }


    if ($path == "setRoomPhase") {
      $room = new room($_POST["roomId"]);

      if ($room->roomPhase == "b" && $_POST["phase"] == "q") {
        return json_encode(["error" => "Fehler: Frage fehlt."]);
      }
      if ($room->roomPhase == "b" && $_POST["phase"] == "e") {
        return json_encode(["error" => "Fehler: Frage fehlt."]);
      }
      if ($room->roomPhase == "z" && $_POST["phase"] == "q") {
        return json_encode(["error" => "Fehler: Frage fehlt."]);
      }
      if ($room->roomPhase == "z" && $_POST["phase"] == "e") {
        return json_encode(["error" => "Fehler: Frage fehlt."]);
      }

      if ($_POST["phase"] == "e") {

        $questionId = question::returnQuestionForRoomId($room->roomId)["qId"];

        $distroR = rating::distributeAnswersToRatelessEchoPhase($questionId);
        
        $tnIdsForDistro = $distroR["tn"];
        // var_dump($answer); //todo
        
        foreach ($tnIdsForDistro as $tnId) {
          app_func::sendWsPost("eq!{$room->roomId}", ["rTnId" => $tnId]);
        }
        
        
        $aIdsForDistro = $distroR["aId"];
        app_func::sendWsPost("eq!!{$room->roomId}", ["ratingAusgegebenAids" => $aIdsForDistro]);
        

      }

      return $room->setRoomPhase($_POST["phase"]);
    }

    if ($path == "setEye") {
      $room = new room($_POST["roomId"]);
      if ($_POST["showEye"] == "-1") {
        return $room->setRoomPhase("e");
      }
        
      return $room->setRoomPhase($_POST["aId"]);
    }

    if ($path == "getTnRatings") {
      
      $room = new room($_POST["roomId"]);
      if ($room->roomPhase == "b" || $room->roomPhase == "z") {
        return json_encode([]);
      }

      $tnId = app_func::secureCharForMysql($_POST["tnId"]);     

      $questionId = app_func::secureCharForMysql($_POST["questionId"]);

      $db = pdoDb::getConnection();
      $stmt = $db->prepare(
        'SELECT ratingId, rating, a.answer_text FROM ratings r JOIN answers a ON r.f_answerId = a.answerId WHERE r.f_tnId = :tnId AND r.f_questionId = :questionId AND rating > -2'
      );
      $stmt->execute(['tnId' => $tnId, 'questionId' => $questionId]);

      $ratings = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      $stmt->closeCursor();

      return json_encode(["ratings" => $ratings]);
      
    }

    if ($path == "setRating") {
      $rating = new rating($_POST["ratingId"]);
      $oldRating = $rating->rating;
      $rating->rating = $_POST["rating"];
      $rating->saveToDb();
      $answer = new answer($rating->f_answerId);
      $room = new room($answer->f_roomId);
      if ($room->roomPhase == "e") {
        // echo phase, tell all students
        app_func::sendWsPost("eq!{$answer->f_roomId}", ["answerRating" => $rating->f_answerId, "rating" => $_POST["rating"], "oldRating" => $oldRating]);
      } else {
        // question phase or display of one answer
        app_func::sendWsPost("eq!!{$answer->f_roomId}", ["answerRating" => $rating->f_answerId, "rating" => $_POST["rating"], "oldRating" => $oldRating]);
      }

      // return 1;
      return json_encode(["ratedId" => $_POST["ratingId"], "rating" => $_POST["rating"]]);
    }

    if ($path == "getRoomPhase") {
      $room = new room($_GET["roomId"]);
      return json_encode($room->getRoomPhaseStatus());
    }

    if ($path == "logoutFromRoomId") {
      
      return room::logoutFromRoomId($_POST["roomId"]);
    }

    if ($path == "getBadges") {
      $badgeCount = 0;
      $tnId = app_func::secureCharForMysql($_POST["tnId"]);

      if ($tnId == 0) {
        return json_encode([]);
      }

      $tn = new tn($tnId);
      
      
      $tn->calcScore();

      return json_encode(["badgeCount" => $tn->score]);
      
    }
    
    if ($path == "setAnswerGrade") {
      $answer = new answer($_POST["aId"]);
      $answer->answer_grade = $_POST["grade"];
      $answer->saveToDb();

      $room = new room($answer->f_roomId);
      if (is_numeric($room->roomPhase) || $room->roomPhase == "e") {
        if ($room->roomPhase == $answer->answerId) {
          // echo phase showing this, tell all students
          $room->sendRoomPhaseOverWs();
        } else {
          // tell this student
          app_func::sendWsPost("eq!{$answer->f_roomId}", ["updateGradeTn" => $answer->f_userId]);
        }
      }

      if ($room->roomPhase == "e" || is_numeric($room->roomPhase)) {
        // echo phase or showing detail - tell the student
        app_func::sendWsPost("eq!{$answer->f_roomId}", ["answerGrade" => $answer->answerId]);
      }


      // return 1;
      return json_encode(["gradedAnswerId" => $_POST["aId"], "grade" => $_POST["grade"]]);
    }

    if ($path == "getOwnAgrade") {

      if ($_POST["questionId"] == -1) {

        $room = new room($_POST["roomId"]);
        $feedbackHtml = feedbackBank::renderFeedbackQuestions($_POST["tnId"], $room->f_feedbackGroup);
        return $feedbackHtml;



      }

      $answer = answer::getAnswerForTnAndQuestion($_POST["tnId"], $_POST["questionId"]);
      $class = "";
      $gradeText = "";
      
      if ($answer) {

        if ($answer->answer_grade == 4) {
          $class = "like";
          $gradeText = "<span class='likebtn largeBtn  frontend-grade'><i class='fa fa-check' aria-hidden='true'></i></span>";
        } else if ($answer->answer_grade == 5) {
          $class = "openend";
          $gradeText = "<span class='openendbtn largeBtn  frontend-grade'><i class='fas fa-balance-scale-right' aria-hidden='true'></i></span>";
        } else if ($answer->answer_grade == 6) {
          $class = "dislike";
          $gradeText = "<span class='dislikebtn largeBtn  frontend-grade'><i class='fa fa-times' aria-hidden='true'></i></span>";
        }

        $ratings = rating::getRatingsForAnswer($answer->answerId);
        return "<div class='answers gradient'><article class='vis force $class aId-{$answer->answerId} ' data-r0={$ratings["r0"]} data-r1={$ratings["r1"]} data-r2={$ratings["r2"]} data-r3={$ratings["r3"]} id='ownGrade'>$answer->answer_text</article>$gradeText</div>";
      }
      return "";
      
      
    }

    if ($path == "setFeedback") {
      $db = pdoDb::getConnection();
      $stmt = $db->prepare(
        'INSERT INTO feedback (stars, f_feedbackKey, f_tnId, f_roomId) 
VALUES (:stars, :f_feedbackKey, :f_tnId, :f_roomId) 
ON DUPLICATE KEY UPDATE 
    stars = VALUES(stars);'
      );
      $stmt->execute([
        'stars' => app_func::secureCharForMysql($_POST["stars"]), 
        'f_feedbackKey' => app_func::secureCharForMysql($_POST["fqk"]), 
        'f_tnId' => app_func::secureCharForMysql($_POST["tnId"]), 
        'f_roomId' => app_func::secureCharForMysql($_POST["roomId"])
      ]);
      $feedbackId = $db->lastInsertId();
      $stmt->closeCursor();

      return json_encode(["feedbackId" => $feedbackId]);
    }
    

    if ($path == "answerAlert") {
      $rating = new rating($_POST["ratingId"]);
      $rating->rating = -3;
      $rating->saveToDb();
      $answer = new answer($rating->f_answerId);
      answerAlert::insertToDb($answer, $_POST["alert_user_text"], $_POST["alert_sender_user_email"], 0);
      
      app_func::sendWsPost("eq!!{$answer->f_roomId}", ["answerAlert" => $rating->f_answerId]);
      // return 1;
      return json_encode(["hideRating" => $_POST["ratingId"]]);
    }

    if ($path == "answerAlertAdmin") {

      $answer = new answer($_POST["answerId"]);

      $answer->replaced_by_answerId = -4;
      $answer->saveToDb();

      // $room = new room($answer->f_roomId);
      $alertUserText = $_POST["alert_user_text"] ?? "";

      answerAlert::insertToDb($answer, $alertUserText, "", 1);
      
      app_func::sendWsPost("eq!!{$answer->f_roomId}", ["answerAlertAdmin" => $answer->answerId]);

      $adminAlert = rating::adminAlertForAnswer($answer->answerId);

      foreach ($adminAlert as $alert) {
        app_func::sendWsPost("eq!{$answer->f_roomId}", ["hideRating" => $alert["ratingId"]]);
      }

      return json_encode(["answerAlertAdmin" => $answer->answerId]);
    }




    if ($path == "gptAnswer") {
      return gptAnswer::getAnswer();
    }

    
  }

}