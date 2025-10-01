<?php

namespace echoQuiz;

use \app_ed_tech\pdoDb;
use \app_ed_tech\app_func;

class answerAlert
{
    public $alertId;
    public $f_answerId;
    public $alert_user_text;
    public $alert_sender_user_email;
    public $alert_from_admin;

    public function __construct($_alertId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare(
            'SELECT * FROM answerAlerts WHERE alertId = :alertId'
        );
        $stmt->execute(['alertId' => $_alertId]);

        $data = $stmt->fetch();
        $stmt->closeCursor();

        if (!empty($data)) {
            $this->alertId = $data['alertId'];
            $this->f_answerId = $data['f_answerId'];
            $this->alert_user_text = $data['alert_user_text'];
            $this->alert_sender_user_email = $data['alert_sender_user_email'];
            $this->alert_from_admin = $data['alert_from_admin'];
        } else {
            throw new \Exception("AnswerAlert ($_alertId) not found", 404005);
        }
    }

    public function saveToDb()
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("UPDATE answerAlerts SET
                f_answerId = :f_answerId,
                alert_user_text = :alert_user_text,
                alert_sender_user_email = :alert_sender_user_email,
                alert_from_admin = :alert_from_admin
            WHERE alertId = :alertId");

        $stmt->execute([
            'f_answerId' => app_func::secureCharForMysql($this->f_answerId),
            'alert_user_text' => app_func::secureCharForMysql($this->alert_user_text),
            'alert_sender_user_email' => app_func::secureCharForMysql($this->alert_sender_user_email),
            'alert_from_admin' => app_func::secureCharForMysql($this->alert_from_admin),
            'alertId' => app_func::secureCharForMysql($this->alertId),
        ]);

        $stmt->closeCursor();
    }

    public function updateFromPost()
    {
        $this->f_answerId = $_POST["f_answerId"];
        $this->alert_user_text = $_POST["alert_user_text"];
        $this->alert_sender_user_email = $_POST["alert_sender_user_email"];
    }

    /**
     * @param answer $answer
     * @return int NewId
     */
    public static function insertToDb($answer, $alert_user_text, $alert_sender_user_email, $alert_from_admin)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("INSERT INTO answerAlerts
            (f_answerId, alert_user_text, alert_sender_user_email, alert_from_admin) VALUES 
            (:f_answerId, :alert_user_text, :alert_sender_user_email, :alert_from_admin)");
        $stmt->execute([
            "f_answerId" => app_func::secureCharForMysql($answer->answerId),
            "alert_user_text" => app_func::secureCharForMysql($alert_user_text),
            "alert_sender_user_email" => app_func::secureCharForMysql($alert_sender_user_email),
            "alert_from_admin" => app_func::secureCharForMysql($alert_from_admin),
        ]);
        $new_id = $db->lastInsertId();
        $stmt->closeCursor();


        $room = new room($answer->f_roomId);
        $emails = ["abuse@echoquiz.eu", $room->roomEmail];

        $optional = "";
        if ($alert_from_admin) {
            $optional = "<b>Meldung wurde abgesendet vom Raum-Admin:</b> $alert_sender_user_email
<br>";
        } else if (!empty($alert_sender_user_email)) {
            $optional = "<b>E-Mail für Rückmeldungen:</b> $alert_sender_user_email
<br>";
            $emails[] = $alert_sender_user_email;

        }


        mailer::sendAnMailWithCopy(
            "echoQuiz Meldung in Raum ". $answer->f_roomId,
            "Auf echoQuiz.eu wurde in Raum ". $answer->f_roomId . " eine Meldung für beleidigenden Inhalt abgesendet:<br>
<b>Meldegrund:</b> $alert_user_text<br> $optional
<b>Gemeldete Antwort:</b> " . $answer->answer_text . "<br>
<br>
Unser Moderationsteam wird sich um die Meldung kümmern, wir bitten, falls möglich, um zusätzliche Informationen als Antwort auf diese E-Mail.<br>
<br>
Moderationsteam echoQuiz.eu ( abuse@echoquiz.eu )",

            $emails
        );

        return $new_id;
    }

    public static function getAlertsCountForAnswer($answerId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("SELECT count(alertId) as CNT FROM answerAlerts WHERE f_answerId = :f_answerId");
        $stmt->execute(['f_answerId' => app_func::secureCharForMysql($answerId)]);

        $data = $stmt->fetch();
        $stmt->closeCursor();

        return $data['CNT'];
    }

    public function formatAlert()
    {
        return [
            "alertId" => $this->alertId,
            "f_answerId" => $this->f_answerId,
            "alert_user_text" => $this->alert_user_text,
            "alert_sender_user_email" => $this->alert_sender_user_email
        ];
    }
}