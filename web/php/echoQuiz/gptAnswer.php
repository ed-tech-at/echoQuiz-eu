<?php

namespace echoQuiz;

use \app_ed_tech\pdoDb;
use \app_ed_tech\edTech;
use \app_ed_tech\app_func;

class gptAnswer
{
  public static function getAnswer() {

    // echo "Frage: " . $_POST["frage"] . "<br>";
    if (!isset($_POST["frage"]) || !isset($_POST["antwort"])) {
      return json_encode(["error" => "Fehler: Frage und Antwort sind erforderlich."]);
    }

    if (! in_array($_POST["roomId"], $_SESSION["admin_roomId"])) {
      return json_encode(["error" => "Fehler: Raum-ID stimmt nicht mit der Sitzung überein."]);
    }

    $room = new room($_POST["roomId"]);

    $apiKey = $room->roomApiKey;

    $frage = $_POST["frage"];
    $antwort = $_POST["antwort"];
    $kontext = isset($_POST["kontext"]) ? $_POST["kontext"] : '';

    // Prepare the system message
    if (!empty($kontext)) {
      $kontext = str_replace(["'", "\n"], ["\\'", " "], $kontext);
      $systemMessage = "You are a helpful assistant. Based on the question {q} in the user input, the following answer {a} was given. The teacher wants that you know this {context}. Write a very short comment in keywords for the teacher of a live session to give {feedback} on the answer and a {grade}: 'correct', 'wrong' in JSON. Be short, answer in German!";
      $userContent = "{'q': '$frage','context': '$kontext','a': '$antwort'}";
    } else {
      $systemMessage = "You are a helpful assistant. Based on the question {q} in the user input, the following answer {a} was given. Write a very short comment in keywords for the teacher of a live session to give {feedback} on the answer and a {grade}: 'correct', 'wrong' in JSON. Be short, answer in German!";
      $userContent = "{'q': '$frage','a': '$antwort'}";
    }

    if (!isset($apiKey)) {
      return json_encode(["error" => "Fehler: API-Schlüssel ist erforderlich."]);
    }
  
    $db = pdoDb::getConnection();
    $stmt = $db->prepare(
      'SELECT * FROM openAiShortcode WHERE apiShortcode = :apiShortcode'
    );

    $stmt->execute(['apiShortcode' => $apiKey]);
    $data = $stmt->fetch();
    $stmt->closeCursor();
    if (!empty($data)) {

      // if (!isset($_POST["email"])) {
      //   return json_encode(["error" => "Fehler: E-Mail ist erforderlich."]);
      // }

      // $email = $_POST["email"];
      // $mailRegex = $data['mailRegex'];

      // if (!preg_match("/^" . $mailRegex . "$/", $email)) {
      //   return json_encode(["error" => "Fehler: E-Mail stimmt nicht mit dem erwarteten Muster überein."]);
      // }

      $apiKey = $data['apiKey'];
    } else {
      // return json_encode(["error" => "Fehler: API-Schlüssel nicht gefunden."]);
      // direct use:
      $apiKey = $apiKey;

    }
    
    if (empty($apiKey)) {
      return json_encode(["error" => "Fehler: API-Schlüssel ist erforderlich."]);
    }

    

    $url = "https://api.openai.com/v1/chat/completions";

    $data = [
      "model" => "gpt-4o-mini",
      "messages" => [
        [
          "role" => "system",
          "content" => $systemMessage
        ],
        [
          "role" => "user",
          "content" => $userContent
        ]
      ],
      "n" => 1,
      "max_tokens" => 500,
      "temperature" => 0.7,
      "response_format" => ["type" => "json_object"],
    ];

    $headers = [
      "Content-Type: application/json",
      "Authorization: Bearer $apiKey"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      return json_encode(["error" => curl_error($ch)]);
    }

    curl_close($ch);

    // Decode the response to extract the content
    $responseArray = json_decode($response, true);

    if (isset($responseArray['choices'][0]['message']['content'])) {
      $chatResponse = $responseArray['choices'][0]['message']['content'];
      return json_encode([
        "response" => $chatResponse,
        // "all" => $responseArray
      ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
      return json_encode(["error" => "Ungültige Antwort der API", "all" => $responseArray], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
  }
}
