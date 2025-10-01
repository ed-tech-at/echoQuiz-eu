<?php

namespace echoQuiz;

use \app_ed_tech\pdoDb;
use \app_ed_tech\app_func;

class feedbackBank
{
    public $feedbackBankId;
    public $feedbackGroup;
    public $feedbackQ;
    public $feedbackKey;
    public $feedbackOrder;

    public function __construct($_feedbackBankId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare(
            'SELECT * FROM feedbackBank WHERE feedbackBankId = :feedbackBankId'
        );
        $stmt->execute(['feedbackBankId' => $_feedbackBankId]);

        $data = $stmt->fetch();
        $stmt->closeCursor();

        if (!empty($data)) {
            $this->feedbackBankId = $data['feedbackBankId'];
            $this->feedbackGroup = $data['feedbackGroup'];
            $this->feedbackQ = $data['feedbackQ'];
            $this->feedbackKey = $data['feedbackKey'];
            $this->feedbackOrder = $data['feedbackOrder'];
        } else {
            throw new \Exception("FeedbackBank ($_feedbackBankId) not found", 404002);
        }
    }

    public function saveToDb()
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("UPDATE feedbackBank SET
                feedbackGroup = :feedbackGroup,
                feedbackQ = :feedbackQ,
                feedbackKey = :feedbackKey,
                feedbackOrder = :feedbackOrder
            WHERE feedbackBankId = :feedbackBankId");

        $stmt->execute([
            'feedbackGroup' => app_func::secureCharForMysql($this->feedbackGroup),
            'feedbackQ' => app_func::secureCharForMysql($this->feedbackQ),
            'feedbackKey' => app_func::secureCharForMysql($this->feedbackKey),
            'feedbackOrder' => app_func::secureCharForMysql($this->feedbackOrder),
            'feedbackBankId' => app_func::secureCharForMysql($this->feedbackBankId),
        ]);

        $stmt->closeCursor();
    }

    public function updateFromPost()
    {
        $this->feedbackGroup = $_POST["feedbackGroup"] ?? $this->feedbackGroup;
        $this->feedbackQ = $_POST["feedbackQ"] ?? $this->feedbackQ;
        $this->feedbackKey = $_POST["feedbackKey"] ?? $this->feedbackKey;
        $this->feedbackOrder = $_POST["feedbackOrder"] ?? $this->feedbackOrder;
    }

    /**
     * @return int NewId
     */
    public static function insertToDb($feedbackGroup, $feedbackQ, $feedbackKey, $feedbackOrder = 0)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("INSERT INTO feedbackBank 
            (feedbackGroup, feedbackQ, feedbackKey, feedbackOrder) VALUES 
            (:feedbackGroup, :feedbackQ, :feedbackKey, :feedbackOrder)");
        $stmt->execute([
            "feedbackGroup" => app_func::secureCharForMysql($feedbackGroup),
            "feedbackQ" => app_func::secureCharForMysql($feedbackQ),
            "feedbackKey" => app_func::secureCharForMysql($feedbackKey),
            "feedbackOrder" => app_func::secureCharForMysql($feedbackOrder),
        ]);
        $new_id = $db->lastInsertId();
        $stmt->closeCursor();

        return $new_id;
    }

    /**
     * @param int $feedbackGroup The feedback group to retrieve records for.
     * @return FeedbackBank[] An array of FeedbackBank objects.
     */
    public static function getFeedbackByGroup($feedbackGroup)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare(
            'SELECT * FROM feedbackBank WHERE feedbackGroup = :feedbackGroup ORDER BY feedbackOrder ASC'
        );
        $stmt->execute(['feedbackGroup' => app_func::secureCharForMysql($feedbackGroup)]);

        $data = $stmt->fetchAll();
        $stmt->closeCursor();

        $feedbackList = [];
        foreach ($data as $row) {
            $feedback = new self($row['feedbackBankId']);
            $feedbackList[] = $feedback;
        }
        return $feedbackList;
    }

    public static function getFeedbackJson($feedbackGroup)
    {
        $feedbackList = self::getFeedbackByGroup($feedbackGroup);
        $result = [];

        foreach ($feedbackList as $feedback) {
            $result[] = [
                'feedbackBankId' => $feedback->feedbackBankId,
                'feedbackQ' => $feedback->feedbackQ,
                'feedbackKey' => $feedback->feedbackKey,
                'feedbackOrder' => $feedback->feedbackOrder,
            ];
        }

        return json_encode($result);
    }

    public static function deleteById($feedbackBankId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare('DELETE FROM feedbackBank WHERE feedbackBankId = :feedbackBankId');
        $stmt->execute(['feedbackBankId' => app_func::secureCharForMysql($feedbackBankId)]);
        $stmt->closeCursor();
    }

    public static function renderFeedbackQuestions($tnId, $feedbackGroup)
    {
        $db = pdoDb::getConnection();

        // Fetch existing feedback data for the given tnId
        $stmt = $db->prepare(
            'SELECT f_feedbackKey FROM feedback WHERE f_tnId = :f_tnId'
        );
        $stmt->execute(['f_tnId' => app_func::secureCharForMysql($tnId)]);
        $feedbackData = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $stmt->closeCursor();

        // Fetch all questions from the feedback group
        $questions = FeedbackBank::getFeedbackByGroup($feedbackGroup);

        // Start building the HTML output
        $a = "<div class='questionFinalWrapper'>";

        foreach ($questions as $question) {
            // If the question is not already in the feedback data, render it
            if (!in_array($question->feedbackKey, $feedbackData)) {
                $a .= "
                <div class='questionFinal' data-fqk='{$question->feedbackKey}'>
                    <div class='questionRatingText expletus'>{$question->feedbackQ}</div>
                    <div class='stars'>
                        <i class='far fa-star'></i>
                        <i class='far fa-star'></i>
                        <i class='far fa-star'></i>
                        <i class='far fa-star'></i>
                        <i class='far fa-star'></i>
                    </div>
                </div>";
            }
        }

        // If all feedback is already given, show a thank-you message
        if (count($feedbackData) == count($questions)) {
            $a .= "<div class='questionFinal'>Vielen Dank für Ihr Feedback!</div>";
        }

        $a .= "
        </div>
        <script>
            parseStars();
        </script>";

        return $a;
    }

    public static function getFeedbackStatsForRoom ($f_roomId, $f_feedbackKey) {
        $db = pdoDb::getConnection();

        
        $stmt = $db->prepare(
            'SELECT * FROM feedback WHERE f_roomId = :f_roomId AND f_feedbackKey = :f_feedbackKey'
        );
        $stmt->execute([
            'f_roomId' => app_func::secureCharForMysql($f_roomId),
            'f_feedbackKey' => app_func::secureCharForMysql($f_feedbackKey)
        ]);

        $dataArray = $stmt->fetchAll();
        $stmt->closeCursor();

        $mittelwert = 0;
        $standardabweichung = 0;
        $anzahl = count($dataArray);
        $summe = 0;
        $stars = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];
        foreach ($dataArray as $data) {
            $summe += $data['stars'];

            $stars[$data['stars']]++;
        }
        if ($anzahl > 0) {
            $mittelwert = $summe / $anzahl;
        }
        if ($anzahl > 1) {
            foreach ($dataArray as $data) {
                $standardabweichung += pow($data['stars'] - $mittelwert, 2);
            }
            $standardabweichung = round(sqrt($standardabweichung / ($anzahl - 1)), 2);
        }

        return [
            'anzahl' => $anzahl,
            'mittelwert' => $mittelwert,
            'standardabweichung' => $standardabweichung,
            'stars' => $stars
        ];
    }

}
