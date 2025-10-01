<?php

namespace echoQuiz;

use \app_ed_tech\pdoDb;
use \app_ed_tech\app_func;

class answer
{
    public $answerId;
    public $f_roomId;
    public $f_userId;
    public $f_questionId;
    public $answer_text;
    public $replaced_by_answerId;

    /*
        0: unset
        4: correct
        5: depends
        6: false
    */
    public $answer_grade;

    public function __construct($_answerId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare(
            'SELECT * FROM answers WHERE answerId = :answerId'
        );
        $stmt->execute(['answerId' => $_answerId]);

        $data = $stmt->fetch();
        $stmt->closeCursor();

        if (!empty($data)) {
            $this->answerId = $data['answerId'];
            $this->f_roomId = $data['f_roomId'];
            $this->f_userId = $data['f_userId'];
            $this->f_questionId = $data['f_questionId'];
            $this->answer_text = $data['answer_text'];
            $this->replaced_by_answerId = $data['replaced_by_answerId'];
            $this->answer_grade = $data['answer_grade'];
        } else {
            throw new \Exception("Answer ($_answerId) not found", 404004);
        }
    }

    public function saveToDb()
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("UPDATE answers SET
                f_roomId = :f_roomId,
                f_userId = :f_userId,
                answer_text = :answer_text,
                answer_grade = :answer_grade,
                replaced_by_answerId = :replaced_by_answerId
            WHERE answerId = :answerId");

        $stmt->execute([
            'f_roomId' => app_func::secureCharForMysql($this->f_roomId),
            'f_userId' => app_func::secureCharForMysql($this->f_userId),
            'answer_text' => app_func::secureCharForMysql($this->answer_text),
            'answer_grade' => app_func::secureCharForMysql($this->answer_grade),
            'replaced_by_answerId' => app_func::secureCharForMysql($this->replaced_by_answerId),
            'answerId' => app_func::secureCharForMysql($this->answerId),
        ]);

        $stmt->closeCursor();
    }

    public function updateFromPost()
    {
        $this->f_roomId = $_POST["f_roomId"];
        $this->f_userId = $_POST["f_userId"];
        $this->answer_text = $_POST["answer_text"];
    }

    /**
     * @return int NewId
     */
    public static function insertToDb()
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("SELECT answerId, f_questionId FROM answers WHERE f_roomId = :f_roomId AND f_userId = :f_userId AND f_questionId = :f_questionId AND replaced_by_answerId = 0");
        $stmt->execute([
            "f_roomId" => app_func::secureCharForMysql($_POST["roomId"]),
            "f_userId" => app_func::secureCharForMysql($_POST["tnId"]),
            "f_questionId" => app_func::secureCharForMysql($_POST["questionId"]),
        ]);
        $existingAnswer = $stmt->fetch();
        $stmt->closeCursor();

        $stmt = $db->prepare("INSERT INTO answers
            (f_roomId, f_userId, f_questionId, answer_text) VALUES 
            (:f_roomId, :f_userId, :f_questionId, :answer_text)");
        $stmt->execute([
            "f_roomId" => app_func::secureCharForMysql($_POST["roomId"]),
            "f_userId" => app_func::secureCharForMysql($_POST["tnId"]),
            "f_questionId" => app_func::secureCharForMysql($_POST["questionId"]),
            "answer_text" => app_func::secureCharForMysql($_POST["answer_text"]),
        ]);
        $answer = new answer($new_id = $db->lastInsertId());

        $stmt = $db->prepare("
            INSERT INTO ratings (f_questionId, f_answerId, f_tnId, rating)
            VALUES (:f_questionId, :f_answerId, :f_tnId, :rating)
        ");
        $stmt->execute([
            'f_questionId' => app_func::secureCharForMysql($_POST["questionId"]),
            'f_answerId' => $new_id,
            'f_tnId' => $_POST["tnId"],
            'rating' => -2
        ]);

        $updateExisting = [];
        if ($existingAnswer) {
            $stmt = $db->prepare("UPDATE answers SET replaced_by_answerId = :newAnswerId WHERE answerId = :oldAnswerId");
            $stmt->execute([
                "newAnswerId" => $new_id,
                "oldAnswerId" => $existingAnswer["answerId"],
            ]);
            $stmt->closeCursor();
            $updateExisting = ["oldAid" =>$existingAnswer["answerId"]];

            $stmt = $db->prepare("UPDATE ratings SET f_questionId = :newQuestionId WHERE f_answerId = :answerId");
            $stmt->execute([
                "newQuestionId" => -$existingAnswer["f_questionId"],
                "answerId" => $existingAnswer["answerId"],
            ]);
            $stmt->closeCursor();
        }

        app_func::sendWsPost("eq!!{$answer->f_roomId}", array_merge($answer->formatAnswer(), $updateExisting));


        $distroR = rating::distributeAnswersToRaters(app_func::secureCharForMysql($_POST["questionId"]));

        $tnIdsForDistro = $distroR["tn"];
        $aIdsForDistro = $distroR["aId"];
        // var_dump($answer); //todo

        foreach ($tnIdsForDistro as $tnId) {
            app_func::sendWsPost("eq!{$answer->f_roomId}", ["rTnId" => $tnId]);
        }
        
        
        app_func::sendWsPost("eq!!{$answer->f_roomId}", ["ratingAusgegebenAids" => $aIdsForDistro]);
        

        return $new_id;
    }

    public function formatAnswer () {
        $alertNum = answerAlert::getAlertsCountForAnswer($this->answerId);
        $gradeClass = "";
        if ($this->answer_grade == 4) {
            $gradeClass = "like";
        } else if ($this->answer_grade == 5) {
            $gradeClass = "openend";
        } else if ($this->answer_grade == 6) {
            $gradeClass = "dislike";
        }
        
        return array_merge(["aT" => $this->answer_text, "aId" => $this->answerId, "aQid" => $this->f_questionId, "alertMark" => $alertNum, "aGradeClass" => $gradeClass], rating::getRatingsForAnswer($this->answerId));
    }


    public static function getAnswersForRoom($roomId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("SELECT * FROM answers WHERE f_roomId = :f_roomId AND replaced_by_answerId = 0");
        $stmt->execute(['f_roomId' => app_func::secureCharForMysql($roomId)]);

        $answers = [];
        while ($data = $stmt->fetch()) {
            $answer = new answer($data['answerId']);
            $answers[] = $answer->formatAnswer();
        }
        $stmt->closeCursor();

        return $answers;
    }

    public static function getAnswerForTnAndQuestion($tnId, $f_questionId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("SELECT answerId FROM answers WHERE f_userId = :f_userId AND replaced_by_answerId = 0 and f_questionId = :f_questionId");
        $stmt->execute(['f_userId' => app_func::secureCharForMysql($tnId)
            , 'f_questionId' => app_func::secureCharForMysql($f_questionId)]);

        while ($data = $stmt->fetch()) {
            return new self($data['answerId']);
        }
        $stmt->closeCursor();

        return NULL;
    }
}
