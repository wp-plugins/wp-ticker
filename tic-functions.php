<?php

@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR. "wp-config.php");
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR."wp-includes/wp-db.php");

define('WPTIC_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)) );


if (defined('WPLANG')) {
  $lang = WPLANG;
}
if (empty($lang)) {
  $lang = 'de_DE';
}

if(!@include_once "lang/".$lang.".php")
  include_once "lang/en_EN.php";


$aktion = $_POST['aktion'];
$content = $_POST['content'];


if(!isset($_POST['aktion']))
  $aktion = $_GET['aktion'];

//===== CSS bearbeiten ===============================
if($aktion=="get_css") {
  $css_content = file(dirname(__FILE__) . DIRECTORY_SEPARATOR ."style.css");
  $css_content = implode("",$css_content);
  echo  "<textarea id='css_edit_content' style='width:590px; height:600px;'>$css_content</textarea>";
  exit();
}


if($aktion=="save_css") {
  $fp = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR ."style.css","w+");
  fwrite($fp, $content);
  fclose($fp);
  echo "<script type='text/javascript'>close_fancy();</script>";
  exit();
}


//===== Modul Upload ===============================
if($aktion=="get_modulform") {

  echo "<!doctype html>\n<html>\n<head>\n<title>Modul-Formular</title>\n</head>\n<body style='font-family:Arial;font-size:10pt;'>\n";

  echo "<form action='". plugins_url() ."/wp-ticker/tic-functions.php' method='post' targe='self' enctype='multipart/form-data'>\n".
       "<input type='file' name='mudule_file' value=''>\n<br><br>\n".
       "<input type='hidden' name='aktion' value='upload'>\n".
       "<input type='submit' value='Upload'> ".
       "<input type='button' value='$abbruch_w' onclick='parent.close_fancy()'>\n";


  echo "</body>\n</html>";
}


if($aktion=="upload" && current_user_can('administrator') ) {

  $msg = "";

  $upl = move_uploaded_file($_FILES['mudule_file']['tmp_name'], dirname(__FILE__) . DIRECTORY_SEPARATOR ."modules/".$_FILES['mudule_file']['name']);
  if($upl!==FALSE)
    $msg = $import_modul_upload_ok;
  else
    $msg = $import_modul_upload_err;


  echo "<!doctype html>\n<html>\n<head>\n<title>Modul-Formular</title>\n</head>\n<body style='font-family:Arial;font-size:10pt;'>\n";

  echo $msg."<br><br>";

  echo "<input type='button' value='$schliessen_w' onclick='parent.location.reload();parent.close_fancy()'>\n";
  echo "</body>\n</html>";

}




?>