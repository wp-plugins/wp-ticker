<?php
$verzeichnis = "../modules/";
$code = "";
$code .= "var show_time = new Array();".
         "var out_time = new Array();".
         "var in_time = new Array();".
         "var fade_timer = new Array();";
$dir = opendir($verzeichnis);
while($datei = readdir($dir)) {
  if (is_file($verzeichnis.$datei) && (substr($datei, -3, 3) == "php")) {
    $ini_data = parse_ini_file($verzeichnis.$datei);
    if($ini_data["base64"]==true)
      $teilcode = base64_decode($ini_data["code"]);
    else
      $teilcode = $ini_data["code"];
    $code .= stripslashes($teilcode)."\n";
  }
}
echo $code;
?>