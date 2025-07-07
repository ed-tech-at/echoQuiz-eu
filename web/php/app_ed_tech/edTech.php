<?php
namespace app_ed_tech;

use echoQuiz\admin;
use echoQuiz\room;

class edTech {
  public static function getTimestampDb() {
    return date("Y-m-d H:i:s");
  }

  public static function secureCharForMysql ($_theChar) {

    if ($_theChar == "NULL") {
        $_theChar = "";
      }
    if ($_theChar === null) {
        $_theChar = "";
      }
  
    return htmlspecialchars($_theChar, ENT_QUOTES);
  }

  public static function getHtmlHeader($title)
{
  global $version;
  global $rootDir;

  $htmlTitle = $title . " | ed-tech.app";
  
  
  $a = "<!DOCTYPE html>
<html lang='de'>
<head>
  <meta charset='utf-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  
  <title>$htmlTitle</title>

<link rel='apple-touch-icon' sizes='180x180' href='/files/2024/fav1/apple-touch-icon.png'>
<link rel='icon' type='image/png' sizes='32x32' href='/files/2024/fav1/favicon-32x32.png'>
<link rel='icon' type='image/png' sizes='16x16' href='/files/2024/fav1/favicon-16x16.png'>
<link rel='manifest' href='/files/2024/fav1/site.webmanifest'>
<link rel='mask-icon' href='/files/2024/fav1/safari-pinned-tab.svg' color='#5bbad5'>
<link rel='shortcut icon' href='/files/2024/fav1/favicon.ico'>
<meta name='msapplication-TileColor' content='#da532c'>
<meta name='msapplication-config' content='/files/2024/fav1/browserconfig.xml'>
<meta name='theme-color' content='#ffffff'>
  
  <meta name='description' content='Vielen Dank für Ihr Interesse an unserem Bildungs-Chatbot. Leider ist der Chatbot gerade nicht verfügbar, wir freuen uns auf den nächsten Workshop mit Ihnen.'>

  <script src='{$rootDir}/files/jquery_3.7.1.min.js' ></script>
  <script src='{$rootDir}/files/htmx_2.0.1.min.js' ></script>
    <script src='{$rootDir}/files/2024/bg.js?$version' ></script>
    <script src='{$rootDir}/files/NchanSubscriber.js' ></script>
    <script src='{$rootDir}/files/2024/gpt-js.js?$version' ></script>
    <script src='{$rootDir}/files/chart_4.4.3.js'></script>


  <link rel='stylesheet' href='{$rootDir}/files/2024/bg.css?$version'>
  <link rel='stylesheet' href='{$rootDir}/files/fonts/fontawesome-free-5.15.3-web/css/all.min.css'>
  
  <link rel='stylesheet' href='{$rootDir}/files/fonts/fonts.css?$version'>

</head>
<body>";

$a .= "

<div id='bg'>
  <canvas></canvas>
  <canvas></canvas>
  <canvas></canvas>
</div>

  ";

  return $a;
}

public static function getHtmlFooter()
{
  
  $a = "
<footer>
<a href='/'>Home</a>
<a href='/admin'>Admin</a>
<a href='/impressum/'>Impressum / Imprint</a>
<a href='/datenschutz/'>Datenschutz</a>
</footer>
</body>
</html>
  ";

  return $a;
}

public static function getHtmlDatenschutz() {
  // $a = edTech::getHtmlHeader("Datenschutz");
  $a = \echoQuiz\ui::getHtmlHeader("Datenschutz und Teilnahmebedingungen");

$a .= '
  <main id="main" class="special">

<p>This website is created in Austria, the following Austrian laws apply:</p>

<h2 class="wp-block-heading">Datenschutz und Teilnahmebedingungen</h2>



<h4>1. Grundsatz</h4>

<p>Der Schutz Ihrer Daten ist uns ein besonderes Anliegen. Wir verarbeiten Ihre Daten daher ausschließlich auf Grundlage der gesetzlichen Bestimmungen (<a href="https://www.dsb.gv.at/recht-entscheidungen/gesetze-in-oesterreich.html">DSGVO, TKG</a>). In diesen Datenschutzinformationen informieren wir Sie über die wichtigsten Aspekte der Datenverarbeitung im Rahmen unserer Website.</p>



<h4>2. Zweck und Rechtsgrundlage der Verarbeitung</h4>


<p>Beim Besuch unserer Website wird Ihre IP-Adresse, Beginn und Ende der Sitzung für die Dauer dieser Sitzung erfasst. Dies ist aus technischem Grund erforderlich und stellt damit ein berechtigtes Interesse i.S.v. Art 6 Abs 1 lit f DSGVO dar. Soweit im Folgenden nichts anderes geregelt wird, werden diese Daten von uns nicht weiterverarbeitet.</p>

<p>Ihre Sitzung wird für die Dauer von 4 Stunden in einem lokalen Cookie gespeichert. Dies ist notwendig, um die Nutzung der Website ordnungsgemäß zu ermöglichen.</p>

<p>Sie können jederzeit Ihre Sitzung unter <a href="/logout">/logout</a> oder durch den Link <i>von allen Räumen abmelden</i> beenden.</p>

<p>Ihre Eingaben können von Vortragenden nicht Ihrem EchoScore Namen zugeordnet werden. Ihre Punkte werden erst nach der Anzeige der Abschluss-Fragen dem Lehrenden gezeigt. Nur gemeldete Inhalte werden geprüft, und in Ausnahmefällen zugeordnet.</p>



<h5>2.1 Auswertung zu wissenschaftlichen Zwecken</h5>
Die im Rahmen der Nutzung unserer Website erhobenen Daten, wie der frei gewählte Benutzername und die eingegebenen Antworten, werden pseudonymisiert zu wissenschaftlichen Zwecken ausgewertet. Diese Auswertungen dienen der Forschung und Weiterentwicklung von Bildungs- und Technologieangeboten, insbesondere im Bereich der Bildungsinformatik, Künstlichen Intelligenz und maschinellen Lernens. Die Datenverarbeitung erfolgt unter strikter Einhaltung der geltenden Datenschutzgesetze und ausschließlich zu dem Zweck, wissenschaftliche Erkenntnisse zu gewinnen, die zur Verbesserung und Weiterentwicklung unseres Angebots beitragen können.

<h5>2.2 Verwendung von Künstlicher Intelligenz (KI) in interaktiven Lernobjekten</h5>

Unsere Plattform nutzt moderne KI-Technologien zur Unterstützung interaktiver Lernobjekte. Diese kommen insbesondere bei der dynamischen Generierung von Inhalten, bei der Analyse von Eingaben sowie zur Verbesserung der Benutzererfahrung zum Einsatz. Die Verarbeitung erfolgt ausschließlich zur Bereitstellung und Wartung der Dienste. Die eingesetzten Modelle lernen nicht aus den individuellen Inhalten der Nutzer:innen. 

<h5>2.3 Hinweise für EchoQuiz-Ersteller:innen</h5>
<p>Beim Anlegen eines EchoQuiz-Raums ist die Eingabe einer gültigen E-Mail-Adresse erforderlich. Diese wird verwendet, um Sie über wichtige Ereignisse zu informieren – etwa wenn Inhalte aus Ihrem Quiz gemeldet werden. In solchen Fällen erhalten Sie automatisch eine Benachrichtigung an die angegebene Adresse. Auf Wunsch kann auch die meldende Person eine Kopie dieser Benachrichtigung erhalten und so Ihre E-Mail Adresse erfahren.</p>


<b>Unzulässige Inhalte</b>
<p>Wir bitten Sie ausdrücklich um Ihre Mithilfe: Sollten beleidigende oder unangemessene Inhalte über Ihren Raum verbreitet werden, helfen uns Ihre Rückmeldungen dabei, solche Inhalte schnellstmöglich zu prüfen und zu entfernen.</p>

<p>In EchoQuiz-Räumen dürfen keine Inhalte veröffentlicht werden, die:</p>
<ul>
  <li>Gewalt verherrlichen oder dazu aufrufen,</li>
  <li>Waffen thematisieren oder fördern,</li>
  <li>illegale Aktivitäten beschreiben oder unterstützen,</li>
  <li>diskriminierend sind oder Hassrede beinhalten,</li>
  <li>gegen geltende Gesetze oder ethische Standards verstoßen.</li>
</ul>

<p>Verstöße gegen diese Regeln können zur Sperrung des Raums oder weiterer rechtlicher Schritte führen.</p>

<h4>3. Interne Dienste und interne Datenermittlungen</h4>
<p>
Im Zuge der Verwendung der Plattform werden technisch notwendige Protokolldaten gespeichert. Diese erlauben es der TU Graz, Systemstörungen, Systemfehler, die zu einer Einschränkung der Verfügbarkeit der Online-Angebote führen sowie unerlaubte Zugriffe auf unsere Systeme zu erkennen, einzugrenzen und zu beseitigen. Die Logdaten werden nicht mit anderen personenbezogenen Daten verknüpft. Folgende Datenkategorien werden verarbeitet: Datum und Uhrzeit der Abfrage, Name und URL der abgerufenen Ressourcen, Datenmenge (in Bytes) der angefragten und/oder angerufenen Ressource, Antwort des Servers (z.B. http-Statuscode), Erkennungsdaten des verwendeten Browsers und Betriebssystems, Webseite, von der aus der Zugriff erfolgte, IP-Adresse, Username.
</p>

<h4>4. Ihre Rechte</h4>


<p>Die/Der BenutzerIn verfügt über das Recht auf Auskunft, Löschung, Berichtigung, Einschränkung der Verarbeitung, Datenübertragbarkeit und Widerspruch gegen die Datenverarbeitung. Es besteht ferner ein Beschwerderecht an die Österreichische Datenschutzbehörde, Wickenburggasse 8, 1080 Wien (<script type=\'text/javascript\'>
a=\'dsb\'; b=\'dsb.gv.at\'
        document.write(\'<A hre\'+\'f="mai\'+\'lto:\'+a+\'@\'+b+\'">\');
        document.write(a+\'@\'+b+\'<\/a>\');
        </script>).</p>



<p>Verantwortliche für die Verarbeitung Ihrer personenbezogenen Daten ist die Technische Universität Graz (nachfolgend TU Graz).
Datenschutzbeauftragte der TU Graz ist die x-tention Informationsrechnologie GmbH, Römerstraße 80A, 4600 Wels,  datenschutzbeauftragter@tugraz.at.
Bei datenschutzrechtlichen Anliegen wenden Sie sich bitte an <script type=\'text/javascript\'>
a=\'datenschutz\'; b=\'tugraz.at\'
        document.write(\'<A hre\'+\'f="mai\'+\'lto:\'+a+\'@\'+b+\'">\');
        document.write(a+\'@\'+b+\'<\/a>\');
        </script> .</p>



<p>Datenschutzhinweis mit Unterstützung der <a href="https://www.wko.at/internetrecht/datenschutzerklaerung-checkliste-infopflichten-dsgvo-tkg-we">WKO</a> erstellt.</p>


</div>
</div>

</main>


';

$a .= edTech::getHtmlFooter();

return $a;
}

public static function getHtmlImpressum() {
  // $a = edTech::getHtmlHeader("Impressum");
  $a = \echoQuiz\ui::getHtmlHeader("Impressum / Imprint");

  $a .= '

  <main id="main" class="special">

  
  <h2 class="wp-block-heading">Impressum / Imprint</h2>
  <p>This website is created in Austria, the following Austrian laws apply:</p>
  
  
  <div><h4>Für den Inhalt verantwortlich</h4> <p>Ed-Tech Research Community Graz<br> Leiter der Forschungsgruppe: Martin Ebner<br>Tel.: <a href="tel://00433168738577">+43 316 873-8577</a></p> <h4>Herausgeber:in</h4> <p>Technische Universität Graz<br>Rechbauerstrasse 12<br>8010 Graz<br>Österreich</p> <p>Tel.: <a href="tel://00433168730">+43 316 873-0</a></p> <h4>Technische Umsetzung, Webentwicklung und Webdesign</h4> <p>TU Graz Institute of Human-Centred Computing (HCC)<br> Benedikt Brünner (Future Learning, Analytics &amp; AI for Teaching)</p>  Datenschutzinformationen finden Sie auf <a href="https://echoquiz.eu/datenschutz/">https://echoquiz.eu/datenschutz/</a>. <br> Weitere Informationen finden Sie auf <a href="https://ed-tech.at/imprint/">https://ed-tech.at/imprint/</a>.
    
    </div>
  
  <h4>Haftung für Links</h4>
    
  
  <p>Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werden wir derartige Links umgehend entfernen.</p>
  
  
  
  </main>
  
  
  ';

  $a .= edTech::getHtmlFooter();
  return $a;
}
public static function getChatbotPaused() {
  $a = edTech::getHtmlHeader("EdTech Chatbot");
  $a .= "
  <main id='main' class='special'  >

    <div style='display: inline-block;'></div>
  <h1>ed-tech.app</h1>

  <div class='container clearfix'>
        <img src='/files/2024/dall-e-chatbot.webp' alt='Chatbot Illustration'>
        <h1>Unser EdTech Chatbot ist momentan nicht aktiv</h1>
        <p>Vielen Dank für Ihr Interesse an unserem Bildungs-Chatbot. Leider ist der Chatbot gerade nicht verfügbar, wir freuen uns auf den nächsten Workshop mit Ihnen.</p>
        <p>In der Zwischenzeit dürfen wir Sie auf unseren aktuellen Blog-Post unter <a href='https://education.garden/2024/prompting-techniken/'>education.garden</a> zum Thema <b>Prompting Techniken</b> aufmerksam machen.</p>
        
    </div>

    <div class='container clearfix'>
    <h2>Prompt Engineering</h2>
    <img src='/files/2024/dall-e-prompt-engineering.webp' alt='Prompt Engineering Illustration'>
<p>Prompt Engineering spielt eine entscheidende Rolle bei der Nutzung von Künstlicher Intelligenz (KI), insbesondere bei Sprachmodellen wie GPT-4. Durch das gezielte Formulieren von Eingabeaufforderungen (Prompts) kann die Qualität und Genauigkeit der generierten Antworten erheblich verbessert werden. Ein gut konzipierter Prompt ermöglicht es der KI, relevante Informationen präzise und kontextbezogen bereitzustellen, was die Effizienz und Effektivität von Anwendungen in verschiedensten Bereichen steigert. Zudem hilft Prompt Engineering dabei, unerwünschte oder fehlerhafte Ergebnisse zu minimieren, indem es klare und spezifische Anweisungen gibt, was insbesondere in sensiblen Anwendungen von großer Bedeutung ist.</p>

</div>

    <div class='container clearfix'>
    <h2>Wie KI die Bildungswelt verändert</h2>
    <img src='/files/2024/dall-e-ai-education.webp' class='large' alt='AI Tranformation of Education Illustration'>
<p>Künstliche Intelligenz revolutioniert die Bildungswelt auf vielfältige Weise. Durch den Einsatz von KI-gestützten Lernplattformen können Lehrinhalte personalisiert und an die individuellen Bedürfnisse der Lernenden angepasst werden, was zu einer effektiveren und engagierteren Lernerfahrung führt. KI-basierte Systeme können zudem administrative Aufgaben automatisieren, wie z.B. die Bewertung von Prüfungen oder die Organisation von Lehrplänen, was Lehrkräften mehr Zeit für die direkte Interaktion mit Schülern und Studenten verschafft. Darüber hinaus ermöglicht KI den Zugang zu Bildung für Menschen in abgelegenen oder benachteiligten Regionen, indem sie virtuelle Klassenzimmer und Online-Kurse anbietet, die von überall auf der Welt erreichbar sind.</p>
    
</div>

  </main>
";
$a .= edTech::getHtmlFooter();

  return $a;
}

public static function specialPages() {
  if (isset($_GET["path"])) {
    $path = explode("/", trim($_GET["path"], "/"));
    if ($path[0] == "impressum") {
      if (count($path) > 1) {
        edTech::throw303SeeOther("/impressum/");
      }
      echo edTech::getHtmlImpressum();
      die();
    }
    if ($path[0] == "datenschutz") {
      if (count($path) > 1) {
        edTech::throw303SeeOther("/datenschutz/");
      }
      echo edTech::getHtmlDatenschutz();
      die();
    }
    if ($path[0] == "admin") {
      if (count($path) > 1) {
        

        if (isset($_SESSION['admin_roomId']) && is_array($_SESSION['admin_roomId']) && in_array($path[1], $_SESSION['admin_roomId'])) {
          if (isset($path[2]) && $path[2] == "tabelle") {
            return \echoQuiz\admin::getAdminTable($path[1]);
          
          }
          echo \echoQuiz\admin::getAdminRoomPage($path[1]);
          die();
        }

        if ($path[1] == "neu") {
          // \echoQuiz\tn::tnLogout();
          
          return admin::getAdminNewRoomPage();
          // edTech::throw303SeeOther("/admin");
        }

        edTech::throw303SeeOther("/admin");
      }
      echo \echoQuiz\admin::getAdminLoginPage();
      die();
    }
    // if ($path[0] == "api_echoQuiz") {
    //   echo \echoQuiz\api_echoQuiz::doApi();
    //   die();
    // }
    if ($path[0] == "raum") {
      try {
        if (isset($path[1])) {
          return room::feRoomUi($path[1]);
        }
        throw new \Exception("Raum nicht gefunden");
      } catch (\Exception $e) {
        echo \echoQuiz\ui::getHtmlHeader();
        echo "<main><h1>Raum nicht geöffnet.</h1>Zur <a href='/'>Startseite</a></main>";
        die();
      }
    }
    
    if ($path[0] == "beamer") {
      try {
        if (isset($path[1])) {
          return room::feBeamerRoomUi($path[1]);
        }

        throw new \Exception("Raum nicht gefunden");
      } catch (\Exception $e) {
        echo \echoQuiz\ui::getHtmlHeader();
        echo "<main><h1>Raum nicht geöffnet.</h1>Zur <a href='/'>Startseite</a></main>";
        die();
      }
    }

    
    if ($path[0] == "logout") {
      
      \echoQuiz\tn::tnLogout();

      edTech::throw303SeeOther("/");
    }
    
    if (is_numeric($path[0])) {
      edTech::throw303SeeOther("/raum/" . $path[0]);
    }
    
    edTech::throw404NotFound();
  }

  echo  \echoQuiz\ui::getHtmlHeader();
  echo \echoQuiz\room::feRoomUiSelection();

  if (\echoQuiz\tn::getSessionTnRoomIdArray()) {
    echo "<h2>Aktive Anmeldungen für Räume</h2><ul>";
    foreach (\echoQuiz\tn::getSessionTnRoomIdArray() as $roomId) {
      echo "<li><a href='/raum/" . htmlspecialchars($roomId) . "'>Raum " . htmlspecialchars($roomId) . "</a></li>";
    }
    echo "</ul>";
    
    echo "<br><br><a href='/logout'>von allen Räumen abmelden</a>";
  }

  echo "</main>";  

  echo edTech::getHtmlFooter();
  
  return;
}

  static function throw301Permanent ($newLocation) {
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header("HTTP/1.1 301  Moved Permanently");
    header("Location: $newLocation", true, 301);
    die();
  }
  static function throw303SeeOther ($newLocation) {
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header("HTTP/1.1 303 See Other");
    header("Location: $newLocation", true, 303);
    die();
  }
  static function throw404NotFound () {
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header("HTTP/1.1 404 Not Found", true, 404);
    die();
  }
  static function header404NotFound () {
    header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
    header("HTTP/1.1 404 Not Found", true, 404);
  }

  static function numberFormat ($number, $digits) {
    
    return number_format($number, $digits, ',', '.');
  }

  static function formatURF8toSimpleAssci($urlTitle, $spaceAllowed = 0, $fileExtensionAllowed = 0)
  {
    $urlTitle = str_replace("@", " at ", $urlTitle);
    $urlTitle = str_replace("ö", "oe", $urlTitle);
    $urlTitle = str_replace("Ö", "oe", $urlTitle);
    $urlTitle = str_replace("ä", "ae", $urlTitle);
    $urlTitle = str_replace("Ä", "ae", $urlTitle);
    $urlTitle = str_replace("ü", "ue", $urlTitle);
    $urlTitle = str_replace("Ü", "ue", $urlTitle);
    $urlTitle = str_replace("ß", "sz", $urlTitle);
    $urlTitle = str_replace("ō", "o", $urlTitle);
    $urlTitle = str_replace("ë", "e", $urlTitle);
    $urlTitle = str_replace("œ", "oe", $urlTitle);

    $urlTitle = str_replace("'", "", $urlTitle);
    $urlTitle = str_replace("\"", "", $urlTitle);
    $urlTitle = str_replace("í", "i", $urlTitle);
    $urlTitle = str_replace("Ī", "i", $urlTitle);
    $urlTitle = str_replace("Ī", "i", $urlTitle);
    $urlTitle = str_replace("²", "2", $urlTitle);
    $urlTitle = str_replace("³", "3", $urlTitle);
    $urlTitle = str_replace("š", "s", $urlTitle);
    $urlTitle = str_replace("&", "and", $urlTitle);
    $urlTitle = str_replace("¼", "1-4", $urlTitle);
    $urlTitle = str_replace("½", "1-2", $urlTitle);
    $urlTitle = str_replace("¾", "3-4", $urlTitle);
    $urlTitle = str_replace("Š", "s", $urlTitle);
    $urlTitle = str_replace("š", "s", $urlTitle);
    $urlTitle = str_replace("ž", "z", $urlTitle);
    $urlTitle = str_replace("Ź", "z", $urlTitle);
    $urlTitle = str_replace("ź", "z", $urlTitle);
    $urlTitle = str_replace("Ż", "z", $urlTitle);
    $urlTitle = str_replace("ż", "z", $urlTitle);
    $urlTitle = str_replace("Ž", "z", $urlTitle);
    $urlTitle = str_replace("ž", "z", $urlTitle);
    $urlTitle = str_replace("Ȥ", "z", $urlTitle);
    $urlTitle = str_replace("ȥ", "z", $urlTitle);
    $urlTitle = str_replace("Ÿ", "y", $urlTitle);
    $urlTitle = str_replace("Ý", "y", $urlTitle);
    $urlTitle = str_replace("ÿ", "y", $urlTitle);
    $urlTitle = str_replace("Ŷ", "y", $urlTitle);
    $urlTitle = str_replace("ŷ", "y", $urlTitle);
    $urlTitle = str_replace("Ÿ", "y", $urlTitle);
    $urlTitle = str_replace("Ȳ", "y", $urlTitle);
    $urlTitle = str_replace("ȳ", "y", $urlTitle);
    $urlTitle = str_replace("ȳ", "y", $urlTitle);
    $urlTitle = str_replace("À", "a", $urlTitle);
    $urlTitle = str_replace("Á", "a", $urlTitle);
    $urlTitle = str_replace("Â", "a", $urlTitle);
    $urlTitle = str_replace("Ã", "a", $urlTitle);
    $urlTitle = str_replace("Ä", "a", $urlTitle);
    $urlTitle = str_replace("Å", "a", $urlTitle);
    $urlTitle = str_replace("Æ", "ae", $urlTitle);
    $urlTitle = str_replace("à", "a", $urlTitle);
    $urlTitle = str_replace("á", "a", $urlTitle);
    $urlTitle = str_replace("á", "a", $urlTitle);
    $urlTitle = str_replace("â", "a", $urlTitle);
    $urlTitle = str_replace("ä", "a", $urlTitle);
    $urlTitle = str_replace("å", "a", $urlTitle);
    $urlTitle = str_replace("æ", "ae", $urlTitle);
    $urlTitle = str_replace("Ā", "a", $urlTitle);
    $urlTitle = str_replace("ā", "a", $urlTitle);
    $urlTitle = str_replace("Ă", "a", $urlTitle);
    $urlTitle = str_replace("ă", "a", $urlTitle);
    $urlTitle = str_replace("Ą", "a", $urlTitle);
    $urlTitle = str_replace("ą", "a", $urlTitle);
    $urlTitle = str_replace("Ǎ", "a", $urlTitle);
    $urlTitle = str_replace("ǎ", "a", $urlTitle);
    $urlTitle = str_replace("Ǟ", "a", $urlTitle);
    $urlTitle = str_replace("ǟ", "a", $urlTitle);
    $urlTitle = str_replace("Ǡ", "a", $urlTitle);
    $urlTitle = str_replace("ǡ", "a", $urlTitle);
    $urlTitle = str_replace("ǡ", "a", $urlTitle);
    $urlTitle = str_replace("ǡ", "a", $urlTitle);
    $urlTitle = str_replace("Ǣ", "ae", $urlTitle);
    $urlTitle = str_replace("ǣ", "ae", $urlTitle);
    $urlTitle = str_replace("Ǽ", "ae", $urlTitle);
    $urlTitle = str_replace("ǽ", "ae", $urlTitle);
    $urlTitle = str_replace("ǣ", "a", $urlTitle);
    $urlTitle = str_replace("Ǻ", "a", $urlTitle);
    $urlTitle = str_replace("ǻ", "a", $urlTitle);
    $urlTitle = str_replace("ǻ", "a", $urlTitle);
    $urlTitle = str_replace("Ȁ", "a", $urlTitle);
    $urlTitle = str_replace("ȁ", "a", $urlTitle);
    $urlTitle = str_replace("Ȃ", "a", $urlTitle);
    $urlTitle = str_replace("ȃ", "a", $urlTitle);
    $urlTitle = str_replace("Ȧ", "a", $urlTitle);
    $urlTitle = str_replace("ȧ", "a", $urlTitle);
    $urlTitle = str_replace("Ç", "c", $urlTitle);
    $urlTitle = str_replace("ç", "c", $urlTitle);
    $urlTitle = str_replace("ć", "c", $urlTitle);
    $urlTitle = str_replace("Ć", "c", $urlTitle);
    $urlTitle = str_replace("Ĉ", "c", $urlTitle);
    $urlTitle = str_replace("ĉ", "c", $urlTitle);
    $urlTitle = str_replace("Ċ", "c", $urlTitle);
    $urlTitle = str_replace("ċ", "c", $urlTitle);
    $urlTitle = str_replace("Č", "c", $urlTitle);
    $urlTitle = str_replace("č", "c", $urlTitle);
    $urlTitle = str_replace("È", "e", $urlTitle);
    $urlTitle = str_replace("É", "e", $urlTitle);
    $urlTitle = str_replace("É", "e", $urlTitle);
    $urlTitle = str_replace("Ê", "e", $urlTitle);
    $urlTitle = str_replace("Ë", "e", $urlTitle);
    $urlTitle = str_replace("è", "e", $urlTitle);
    $urlTitle = str_replace("é", "e", $urlTitle);
    $urlTitle = str_replace("ê", "e", $urlTitle);
    $urlTitle = str_replace("ë", "e", $urlTitle);
    $urlTitle = str_replace("Ē", "e", $urlTitle);
    $urlTitle = str_replace("ē", "e", $urlTitle);
    $urlTitle = str_replace("Ĕ", "e", $urlTitle);
    $urlTitle = str_replace("ĕ", "e", $urlTitle);
    $urlTitle = str_replace("Ė", "e", $urlTitle);
    $urlTitle = str_replace("ė", "e", $urlTitle);
    $urlTitle = str_replace("Ę", "e", $urlTitle);
    $urlTitle = str_replace("ę", "e", $urlTitle);
    $urlTitle = str_replace("Ě", "e", $urlTitle);
    $urlTitle = str_replace("Ȅ", "e", $urlTitle);
    $urlTitle = str_replace("ě", "e", $urlTitle);
    $urlTitle = str_replace("ȅ", "e", $urlTitle);
    $urlTitle = str_replace("Ȇ", "e", $urlTitle);
    $urlTitle = str_replace("ȇ", "e", $urlTitle);
    $urlTitle = str_replace("ȩ", "e", $urlTitle);
    $urlTitle = str_replace("Ì", "i", $urlTitle);
    $urlTitle = str_replace("Í", "i", $urlTitle);
    $urlTitle = str_replace("Î", "i", $urlTitle);
    $urlTitle = str_replace("Ï", "i", $urlTitle);
    $urlTitle = str_replace("ì", "i", $urlTitle);
    $urlTitle = str_replace("í", "i", $urlTitle);
    $urlTitle = str_replace("î", "i", $urlTitle);
    $urlTitle = str_replace("ï", "i", $urlTitle);
    $urlTitle = str_replace("Ĩ", "i", $urlTitle);
    $urlTitle = str_replace("ĩ", "i", $urlTitle);
    $urlTitle = str_replace("Ī", "i", $urlTitle);
    $urlTitle = str_replace("ī", "i", $urlTitle);
    $urlTitle = str_replace("Ĭ", "i", $urlTitle);
    $urlTitle = str_replace("ĭ", "i", $urlTitle);
    $urlTitle = str_replace("Į", "i", $urlTitle);
    $urlTitle = str_replace("į", "i", $urlTitle);
    $urlTitle = str_replace("İ", "i", $urlTitle);
    $urlTitle = str_replace("ı", "i", $urlTitle);
    $urlTitle = str_replace("Ĳ", "i", $urlTitle);
    $urlTitle = str_replace("ĳ", "i", $urlTitle);
    $urlTitle = str_replace("Ǐ", "i", $urlTitle);
    $urlTitle = str_replace("Ȉ", "i", $urlTitle);
    $urlTitle = str_replace("ǐ", "i", $urlTitle);
    $urlTitle = str_replace("ȉ", "i", $urlTitle);
    $urlTitle = str_replace("Ȋ", "i", $urlTitle);
    $urlTitle = str_replace("ȋ", "i", $urlTitle);
    $urlTitle = str_replace("Ð", "d", $urlTitle);
    $urlTitle = str_replace("Ď", "d", $urlTitle);
    $urlTitle = str_replace("ď", "d", $urlTitle);
    $urlTitle = str_replace("đ", "d", $urlTitle);
    $urlTitle = str_replace("Ñ", "n", $urlTitle);
    $urlTitle = str_replace("ń", "n", $urlTitle);
    $urlTitle = str_replace("Ń", "n", $urlTitle);
    $urlTitle = str_replace("Ņ", "n", $urlTitle);
    $urlTitle = str_replace("ņ", "n", $urlTitle);
    $urlTitle = str_replace("Ň", "n", $urlTitle);
    $urlTitle = str_replace("ň", "n", $urlTitle);
    $urlTitle = str_replace("ŉ", "n", $urlTitle);
    $urlTitle = str_replace("ŋ", "n", $urlTitle);
    $urlTitle = str_replace("Ŋ", "n", $urlTitle);
    $urlTitle = str_replace("Ò", "o", $urlTitle);
    $urlTitle = str_replace("Ó", "o", $urlTitle);
    $urlTitle = str_replace("Ô", "o", $urlTitle);
    $urlTitle = str_replace("Õ", "o", $urlTitle);
    $urlTitle = str_replace("Ö", "o", $urlTitle);
    $urlTitle = str_replace("Ø", "o", $urlTitle);
    $urlTitle = str_replace("ò", "o", $urlTitle);
    $urlTitle = str_replace("ó", "o", $urlTitle);
    $urlTitle = str_replace("ô", "o", $urlTitle);
    $urlTitle = str_replace("õ", "o", $urlTitle);
    $urlTitle = str_replace("õ", "o", $urlTitle);
    $urlTitle = str_replace("Ō", "o", $urlTitle);
    $urlTitle = str_replace("ö", "o", $urlTitle);
    $urlTitle = str_replace("Ŏ", "o", $urlTitle);
    $urlTitle = str_replace("ō", "o", $urlTitle);
    $urlTitle = str_replace("ŏ", "o", $urlTitle);
    $urlTitle = str_replace("Ő", "o", $urlTitle);
    $urlTitle = str_replace("ő", "o", $urlTitle);
    $urlTitle = str_replace("Œ", "o", $urlTitle);
    $urlTitle = str_replace("œ", "o", $urlTitle);
    $urlTitle = str_replace("Ǒ", "o", $urlTitle);
    $urlTitle = str_replace("ǒ", "o", $urlTitle);
    $urlTitle = str_replace("Ǿ", "o", $urlTitle);
    $urlTitle = str_replace("ǿ", "o", $urlTitle);
    $urlTitle = str_replace("Ȍ", "o", $urlTitle);
    $urlTitle = str_replace("ȍ", "o", $urlTitle);
    $urlTitle = str_replace("Ȏ", "o", $urlTitle);
    $urlTitle = str_replace("ȏ", "o", $urlTitle);
    $urlTitle = str_replace("Ȫ", "o", $urlTitle);
    $urlTitle = str_replace("ȫ", "o", $urlTitle);
    $urlTitle = str_replace("Ȯ", "o", $urlTitle);
    $urlTitle = str_replace("ȭ", "o", $urlTitle);
    $urlTitle = str_replace("ȯ", "o", $urlTitle);
    $urlTitle = str_replace("ȱ", "o", $urlTitle);
    $urlTitle = str_replace("Ù", "u", $urlTitle);
    $urlTitle = str_replace("Ú", "u", $urlTitle);
    $urlTitle = str_replace("Û", "u", $urlTitle);
    $urlTitle = str_replace("Ü", "u", $urlTitle);
    $urlTitle = str_replace("ù", "u", $urlTitle);
    $urlTitle = str_replace("ú", "u", $urlTitle);
    $urlTitle = str_replace("û", "u", $urlTitle);
    $urlTitle = str_replace("ü", "u", $urlTitle);
    $urlTitle = str_replace("Ũ", "u", $urlTitle);
    $urlTitle = str_replace("ũ", "u", $urlTitle);
    $urlTitle = str_replace("Ū", "u", $urlTitle);
    $urlTitle = str_replace("ū", "u", $urlTitle);
    $urlTitle = str_replace("Ŭ", "u", $urlTitle);
    $urlTitle = str_replace("Ŭ", "u", $urlTitle);
    $urlTitle = str_replace("Ů", "u", $urlTitle);
    $urlTitle = str_replace("ů", "u", $urlTitle);
    $urlTitle = str_replace("Ű", "u", $urlTitle);
    $urlTitle = str_replace("ŭ", "u", $urlTitle);
    $urlTitle = str_replace("Ů", "u", $urlTitle);
    $urlTitle = str_replace("Ű", "u", $urlTitle);
    $urlTitle = str_replace("ų", "u", $urlTitle);
    $urlTitle = str_replace("ǔ", "u", $urlTitle);
    $urlTitle = str_replace("Ǔ", "u", $urlTitle);
    $urlTitle = str_replace("Ǖ", "u", $urlTitle);
    $urlTitle = str_replace("ǖ", "u", $urlTitle);
    $urlTitle = str_replace("Ǘ", "u", $urlTitle);
    $urlTitle = str_replace("ǘ", "u", $urlTitle);
    $urlTitle = str_replace("Ǚ", "u", $urlTitle);
    $urlTitle = str_replace("ǚ", "u", $urlTitle);
    $urlTitle = str_replace("Ȕ", "u", $urlTitle);
    $urlTitle = str_replace("ǜ", "u", $urlTitle);
    $urlTitle = str_replace("Ǜ", "u", $urlTitle);
    $urlTitle = str_replace("ȕ", "u", $urlTitle);
    $urlTitle = str_replace("ȗ", "u", $urlTitle);
    $urlTitle = str_replace("Ȗ", "u", $urlTitle);
    $urlTitle = str_replace("Ĝ", "g", $urlTitle);
    $urlTitle = str_replace("ĝ", "g", $urlTitle);
    $urlTitle = str_replace("Ğ", "g", $urlTitle);
    $urlTitle = str_replace("ğ", "g", $urlTitle);
    $urlTitle = str_replace("ġ", "g", $urlTitle);
    $urlTitle = str_replace("Ġ", "g", $urlTitle);
    $urlTitle = str_replace("Ģ", "g", $urlTitle);
    $urlTitle = str_replace("ģ", "g", $urlTitle);
    $urlTitle = str_replace("ǥ", "g", $urlTitle);
    $urlTitle = str_replace("Ǧ", "g", $urlTitle);
    $urlTitle = str_replace("Ǥ", "g", $urlTitle);
    $urlTitle = str_replace("Ǵ", "g", $urlTitle);
    $urlTitle = str_replace("ǵ", "g", $urlTitle);
    $urlTitle = str_replace("ǧ", "g", $urlTitle);
    $urlTitle = str_replace("Ĥ", "h", $urlTitle);
    $urlTitle = str_replace("ĥ", "h", $urlTitle);
    $urlTitle = str_replace("Ħ", "h", $urlTitle);
    $urlTitle = str_replace("ħ", "h", $urlTitle);
    $urlTitle = str_replace("ȟ", "h", $urlTitle);
    $urlTitle = str_replace("Ȟ", "h", $urlTitle);
    $urlTitle = str_replace("Ĵ", "j", $urlTitle);
    $urlTitle = str_replace("ĵ", "j", $urlTitle);
    $urlTitle = str_replace("Ķ", "k", $urlTitle);
    $urlTitle = str_replace("ķ", "k", $urlTitle);
    $urlTitle = str_replace("ĸ", "k", $urlTitle);
    $urlTitle = str_replace("Ǩ", "k", $urlTitle);
    $urlTitle = str_replace("ǩ", "k", $urlTitle);
    $urlTitle = str_replace("Ĺ", "l", $urlTitle);
    $urlTitle = str_replace("ĺ", "l", $urlTitle);
    $urlTitle = str_replace("Ļ", "l", $urlTitle);
    $urlTitle = str_replace("ļ", "l", $urlTitle);
    $urlTitle = str_replace("Ľ", "l", $urlTitle);
    $urlTitle = str_replace("ľ", "l", $urlTitle);
    $urlTitle = str_replace("Ŀ", "l", $urlTitle);
    $urlTitle = str_replace("ŀ", "l", $urlTitle);
    $urlTitle = str_replace("ł", "l", $urlTitle);
    $urlTitle = str_replace("Ŕ", "r", $urlTitle);
    $urlTitle = str_replace("ŕ", "r", $urlTitle);
    $urlTitle = str_replace("Ŗ", "r", $urlTitle);
    $urlTitle = str_replace("ŗ", "r", $urlTitle);
    $urlTitle = str_replace("Ř", "r", $urlTitle);
    $urlTitle = str_replace("ř", "r", $urlTitle);
    $urlTitle = str_replace("Ȑ", "r", $urlTitle);
    $urlTitle = str_replace("ȑ", "r", $urlTitle);
    $urlTitle = str_replace("Ȓ", "r", $urlTitle);
    $urlTitle = str_replace("ȓ", "r", $urlTitle);
    $urlTitle = str_replace("ś", "s", $urlTitle);
    $urlTitle = str_replace("Ś", "s", $urlTitle);
    $urlTitle = str_replace("Ŝ", "s", $urlTitle);
    $urlTitle = str_replace("ŝ", "s", $urlTitle);
    $urlTitle = str_replace("Ş", "s", $urlTitle);
    $urlTitle = str_replace("Š", "s", $urlTitle);
    $urlTitle = str_replace("ş", "s", $urlTitle);
    $urlTitle = str_replace("š", "s", $urlTitle);
    $urlTitle = str_replace("ș", "s", $urlTitle);
    $urlTitle = str_replace("Ș", "s", $urlTitle);
    $urlTitle = str_replace("Ţ", "t", $urlTitle);
    $urlTitle = str_replace("Ť", "t", $urlTitle);
    $urlTitle = str_replace("ţ", "t", $urlTitle);
    $urlTitle = str_replace("ť", "t", $urlTitle);
    $urlTitle = str_replace("Ŧ", "t", $urlTitle);
    $urlTitle = str_replace("ŧ", "t", $urlTitle);
    $urlTitle = str_replace("Ț", "t", $urlTitle);
    $urlTitle = str_replace("ț", "t", $urlTitle);
    $urlTitle = str_replace("ŵ", "w", $urlTitle);
    $urlTitle = str_replace("ŵ", "w", $urlTitle);
    $urlTitle = str_replace("ǫ", "q", $urlTitle);
    $urlTitle = str_replace("Ǭ", "q", $urlTitle);
    $urlTitle = str_replace("ǭ", "q", $urlTitle);
    $urlTitle = str_replace("ǭ", "q", $urlTitle);
    $urlTitle = str_replace("ǭ", "", $urlTitle);
    $urlTitle = str_replace("{", "", $urlTitle);
    $urlTitle = str_replace("[", "", $urlTitle);
    $urlTitle = str_replace("|", "", $urlTitle);
    $urlTitle = str_replace("}", "", $urlTitle);
    $urlTitle = str_replace("~", "", $urlTitle);
    $urlTitle = str_replace("¤", "", $urlTitle);
    $urlTitle = str_replace("¥", "", $urlTitle);
    $urlTitle = str_replace("¦", "", $urlTitle);
    $urlTitle = str_replace("§", "", $urlTitle);
    $urlTitle = str_replace("¨", "", $urlTitle);
    $urlTitle = str_replace("©", " c ", $urlTitle);
    $urlTitle = str_replace("ª", "", $urlTitle);
    $urlTitle = str_replace("«", "", $urlTitle);
    $urlTitle = str_replace("¬", "", $urlTitle);
    $urlTitle = str_replace("®", "", $urlTitle);
    $urlTitle = str_replace("¯", "", $urlTitle);
    $urlTitle = str_replace("±", "", $urlTitle);
    $urlTitle = str_replace("°", "", $urlTitle);
    $urlTitle = str_replace("´", "", $urlTitle);
    $urlTitle = str_replace("µ", "", $urlTitle);
    $urlTitle = str_replace("¶", "", $urlTitle);
    $urlTitle = str_replace("·", "", $urlTitle);
    $urlTitle = str_replace("¹", "", $urlTitle);
    $urlTitle = str_replace("¸", "", $urlTitle);
    $urlTitle = str_replace("º", "", $urlTitle);
    $urlTitle = str_replace("»", "", $urlTitle);
    $urlTitle = str_replace("¿", "", $urlTitle);



    if (!$spaceAllowed) {
      $urlTitle = preg_replace('/(  )/', ' ', $urlTitle);
      $urlTitle = preg_replace('/ /', '-', $urlTitle);
      // $urlTitle = preg_replace('/--/', '-', $urlTitle);
      // $urlTitle = str_replace("--", "-", $urlTitle);
      // $urlTitle = str_replace("--", "-", $urlTitle);
    }


    if ($fileExtensionAllowed) {
      $urlTitle = preg_replace('/[^a-zA-Z0-9\-_\.\/\']/', '', $urlTitle);
    } else {
      $urlTitle = preg_replace('/[^a-zA-Z0-9\-_\']/', '', $urlTitle);
    }

    if (!$spaceAllowed) {
      $urlTitle = preg_replace('/--/', '-', $urlTitle);
      $urlTitle = preg_replace('/--/', '-', $urlTitle);
      // $urlTitle = preg_replace('/--/', '-', $urlTitle);
      // $urlTitle = str_replace("--", "-", $urlTitle);
      // $urlTitle = str_replace("--", "-", $urlTitle);
    }

    return $urlTitle;
  }



}
