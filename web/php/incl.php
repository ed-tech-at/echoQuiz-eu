<?php
// namespace app_ed_tech;

setlocale(LC_TIME, 'de_AT.UTF8');

$SHOWerror = 1;
if ($SHOWerror) {
  error_reporting(E_ALL);
  ini_set('display_errors', 'on');
} else {
  error_reporting(0);
  ini_set('display_errors', 0);
}

require_once __DIR__ . '/pws.php';

if (session_status() != 2) {
  // Set session lifetime to 4 hours (4 * 60 * 60 seconds)
  $four_hours = 4 * 60 * 60;

  ini_set('session.cookie_lifetime', $four_hours);

  ini_set('session.gc_maxlifetime', $four_hours);

  ini_set('session.gc_probability', 1);
  ini_set('session.gc_divisor', 100);
  ini_set('session.cookie_secure', true);
  ini_set('session.cookie_httponly', true);

  session_start();
}

date_default_timezone_set('Europe/Vienna');


/**
 * An example of a project-specific implementation.
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
function my_psr4_autoloader($class) {
    // replace namespace separators with directory separators in the relative 
    // class name, append with .php
    $class_path = str_replace('\\', '/', $class);
    
    $file =  __DIR__ . '/' . $class_path . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
}

spl_autoload_register( 'my_psr4_autoloader' );
