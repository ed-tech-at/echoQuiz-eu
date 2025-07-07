<?php

namespace echoQuiz;

use \app_ed_tech\pdoDb;
use \app_ed_tech\app_func;

class rating
{
    public $ratingId;
    public $f_questionId;
    public $f_answerId;
    public $f_tnId;
    
    /*
    -3: user alerted
    -2: self
    -1: not yet

    0: unselected after previous selection
    1: like
    2: question
    3: dislike


    */
    public $rating;


    public function __construct($_ratingId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare(
            'SELECT * FROM ratings WHERE ratingId = :ratingId'
        );
        $stmt->execute(['ratingId' => $_ratingId]);

        $data = $stmt->fetch();
        $stmt->closeCursor();

        if (!empty($data)) {
            $this->ratingId = $data['ratingId'];
            $this->f_questionId = $data['f_questionId'];
            $this->f_answerId = $data['f_answerId'];
            $this->f_tnId = $data['f_tnId'];
            $this->rating = $data['rating'];
        } else {
            throw new \Exception("Rating ($_ratingId) not found", 404004);
        }
    }

    public function saveToDb()
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("UPDATE ratings SET
                f_questionId = :f_questionId,
                f_answerId = :f_answerId,
                f_tnId = :f_tnId,
                rating = :rating
            WHERE ratingId = :ratingId");

        $stmt->execute([
            'f_questionId' => app_func::secureCharForMysql($this->f_questionId),
            'f_answerId' => app_func::secureCharForMysql($this->f_answerId),
            'f_tnId' => app_func::secureCharForMysql($this->f_tnId),
            'rating' => app_func::secureCharForMysql($this->rating),
            'ratingId' => app_func::secureCharForMysql($this->ratingId),
        ]);

        $stmt->closeCursor();
    }

    public function updateFromPost()
    {
        $this->f_questionId = $_POST["f_questionId"];
        $this->f_answerId = $_POST["f_answerId"];
        $this->f_tnId = $_POST["f_tnId"];
        $this->rating = $_POST["rating"];
    }

    /**
     * @return int NewId
     */
    public static function insertToDb()
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("INSERT INTO ratings
            (f_questionId, f_answerId, f_tnId, rating) VALUES 
            (:f_questionId, :f_answerId, :f_tnId, :rating)");
        $stmt->execute([
            "f_questionId" => app_func::secureCharForMysql($_POST["f_questionId"]),
            "f_answerId" => app_func::secureCharForMysql($_POST["f_answerId"]),
            "f_tnId" => app_func::secureCharForMysql($_POST["f_tnId"]),
            "rating" => app_func::secureCharForMysql($_POST["rating"]),
        ]);

        return $db->lastInsertId();
    }

    // public static function getRatingsForAnswer($answerId)
    // {
    //     $db = pdoDb::getConnection();

    //     $stmt = $db->prepare("SELECT * FROM ratings WHERE f_answerId = :f_answerId");
    //     $stmt->execute(['f_answerId' => app_func::secureCharForMysql($answerId)]);

    //     $ratings = [];
    //     while ($data = $stmt->fetch()) {
    //         $ratings[] = [
    //             "rId" => $data['ratingId'],
    //             "uId" => $data['f_tnId'],
    //             "rating" => $data['rating'],
    //         ];
    //     }
    //     $stmt->closeCursor();

    //     return $ratings;
    // }

    public static function getRatingsForAnswer($answerId)
    {
        $db = pdoDb::getConnection();

        $stmt = $db->prepare("SELECT rating FROM ratings WHERE f_answerId = :f_answerId and f_questionId > 0");
        $stmt->execute(['f_answerId' => app_func::secureCharForMysql($answerId)]);

        $ratings = [
            'r0' => 0,
            'r1' => 0,
            'r2' => 0,
            'r3' => 0
        ];

        while ($data = $stmt->fetch()) {
            switch ($data['rating']) {
                case 0:
                case -1:
                    $ratings['r0']++;
                    break;
                case 1:
                    $ratings['r1']++;
                    break;
                case 2:
                    $ratings['r2']++;
                    break;
                case 3:
                    $ratings['r3']++;
                    break;
            }
        }
        $stmt->closeCursor();

        return $ratings;
    }

public static function distributeAnswersToRatelessEchoPhase($f_questionId, $neue_verteilung = 3)
{
    $db = pdoDb::getConnection();
    $returnTn = [];
    $returnAid = [];

    $distributed = 0;

    $question = new question($f_questionId);
    $room = new room($question->f_roomId);

    $stmt = $db->prepare("
        SELECT tnId
        FROM tn
        WHERE f_roomId = :f_roomId
          AND logoutTime = '0000-00-00 00:00:00'
          AND tnId NOT IN (
              SELECT f_tnId
              FROM ratings
              WHERE f_questionId = :f_questionId
          )
    ");
    $stmt->execute(['f_roomId' => $room->roomId, 'f_questionId' => $f_questionId]);
    $ratelessTns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $stmt->closeCursor();


    if (count($ratelessTns) == 0) {
        return ["tn" => $returnTn, "aId" => $returnAid];
    }
        
    // Suche nach Antworten mit wenig ratings
    $stmt = $db->prepare("
        SELECT f_answerId, COUNT(ratingId) AS rating_count
        FROM ratings
        WHERE f_questionId = :f_questionId
        GROUP BY f_answerId
        ORDER BY rating_count ASC
    ");
    $stmt->execute([":f_questionId" => $f_questionId]);
    $answers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    

    $answerI = 0;

    if (count($answers) == 0) {
        return ["tn" => $returnTn, "aId" => $returnAid];
    }

    foreach ($ratelessTns as $tn) {
        $tnId = $tn["tnId"];
        
        $startAnswerI = $answerI;

        for ($i = 0; $i < $neue_verteilung; $i++) {
            $answer = $answers[$answerI];
            $f_answerId = $answer['f_answerId'];
            // Bewertung einfügen

            $stmt = $db->prepare("
                INSERT INTO ratings (f_questionId, f_answerId, f_tnId, rating)
                VALUES (:f_questionId, :f_answerId, :f_tnId, :rating)
            ");
            $stmt->execute([
                'f_questionId' => $f_questionId,
                'f_answerId' => $f_answerId,
                'f_tnId' => $tnId,
                'rating' => -1
            ]);
            $stmt->closeCursor();

            if (!in_array($tnId, $returnTn)) {
                $returnTn[] = $tnId;
            }
            $returnAid[] = $f_answerId;

            $distributed++;
            $answerI++;
            if ($answerI == count($answers)) {
                $answerI = 0;
            }

            if ($startAnswerI == $answerI) {
                break;
            }

        }

    }
    
        


    

    
    return ["tn" => $returnTn, "aId" => $returnAid];

}

public static function distributeAnswersToRaters($f_questionId, $max_verteilung = 5)
{
    $db = pdoDb::getConnection();
    $returnTn = [];
    $returnAid = [];

    // Suche nach Antworten mit weniger als $max_verteilung Bewertungen
    $stmt = $db->prepare("
        SELECT f_answerId, COUNT(ratingId) AS rating_count
        FROM ratings
        WHERE f_questionId = :f_questionId
        GROUP BY f_answerId
        HAVING rating_count < :max_verteilung
        ORDER BY rating_count ASC
        LIMIT $max_verteilung
    ");
    $stmt->execute([':max_verteilung' => $max_verteilung, ":f_questionId" => $f_questionId]);
    $answers = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $distributed = 0;

    // Iteriere über die Antworten und füge Bewertungen hinzu
    foreach ($answers as $answer) {
        $f_answerId = $answer['f_answerId'];

        for ($i = 0; $i < $max_verteilung; $i++) {
            // Finde eine f_tnId, die diese Antwort noch nicht bewertet hat
            $stmt = $db->prepare("
                SELECT f_tnId, rating_count
                FROM (
                    SELECT f_tnId,
                          (SELECT COUNT(*)
                            FROM ratings r_sub
                            WHERE r_sub.f_tnId = r.f_tnId
                              AND r_sub.f_questionId = :f_questionId) AS rating_count
                    FROM ratings r
                    WHERE r.f_questionId = :f_questionId
                      AND r.f_tnId NOT IN (
                          SELECT f_tnId
                          FROM ratings
                          WHERE f_answerId = :f_answerId
                      )
                    GROUP BY f_tnId
                ) AS subquery
                WHERE rating_count < :max_verteilung
                ORDER BY rating_count ASC
                LIMIT 1;
            ");
            $stmt->execute(['f_questionId' => $f_questionId, "f_answerId" => $f_answerId, "max_verteilung" => $max_verteilung]);
            $tn = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($tn) {

                // Bewertung einfügen
                $stmt = $db->prepare("
                    INSERT INTO ratings (f_questionId, f_answerId, f_tnId, rating)
                    VALUES (:f_questionId, :f_answerId, :f_tnId, :rating)
                ");
                $stmt->execute([
                    'f_questionId' => $f_questionId,
                    'f_answerId' => $f_answerId,
                    'f_tnId' => $tn['f_tnId'],
                    'rating' => -1
                ]);
                $stmt->closeCursor();

                $returnTn[] = $tn['f_tnId'];
                $returnAid[] = $f_answerId;

                $distributed++;
                if ($distributed == $max_verteilung) {
                    return ["tn" => $returnTn, "aId" => $returnAid];
                }

            } else {
                break; // Keine weiteren Benutzer verfügbar, gehe zur nächsten Antwort
            }
        }
    }

    return ["tn" => $returnTn, "aId" => $returnAid];

  }


public static function adminAlertForAnswer($answerId)
{
    $db = pdoDb::getConnection();

    $stmt = $db->prepare("SELECT * FROM ratings WHERE f_answerId = :f_answerId");
    $stmt->execute(['f_answerId' => app_func::secureCharForMysql($answerId)]);

    $ratings = [];
    while ($data = $stmt->fetch()) {
        $ratings[] = [
            "ratingId" => $data['ratingId'],
            "tnId" => $data['f_tnId'],
            "f_questionId" => $data['f_questionId'],
            "rating" => $data['rating'],
        ];
    }
    $stmt->closeCursor();

    // Update the database with the modified f_questionId values
    foreach ($ratings as $rating) {
        $stmt = $db->prepare("UPDATE ratings SET f_questionId = :f_questionId WHERE ratingId = :ratingId");
        $stmt->execute([
            'f_questionId' => -(abs($rating['f_questionId'])),
            'ratingId' => $rating['ratingId']
        ]);
        $stmt->closeCursor();
    }
   
    return $ratings;
}

    
}
