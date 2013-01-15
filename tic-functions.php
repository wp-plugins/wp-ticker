<?php
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR. "wp-config.php");
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR."wp-includes/wp-db.php");
@include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR ."tic-global.php");

define('WPTIC_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)) );

if(!session_id())
  session_start();




$lang = get_bloginfo("language");
$lang = str_replace("-","_",$lang);

if (empty($lang) || trim($lang)=="") {
  $lang = 'de_DE';
}

if(!@include_once "lang/".$lang.".php")
  include_once "lang/en_EN.php";


$aktion = $_POST['aktion'];
$content = $_POST['content'];
$cert = $_POST['cert'];

if(!isset($_POST['aktion']))
  $aktion = $_GET['aktion'];

//===== CSS bearbeiten ===============================
if($aktion=="get_css") {
  if($use_session) {
    if($cert!=session_id()) {
      echo "<script type='text/javascript'>alert('SESS-ERROR');close_fancy();</script>";
      exit();
    }
  }


  if (!is_multisite()) {
    $css_content = file(dirname(__FILE__) . DIRECTORY_SEPARATOR ."style.css");
  }
  else {
     if(!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR."styles/".$wpdb->prefix."_style.css")) {
       $css_content = file(dirname(__FILE__) . DIRECTORY_SEPARATOR ."style.css");
       $css_content = implode("",$css_content);
       $fp = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR."styles/".$wpdb->prefix."_style.css","w+");
       fwrite($fp, $css_content);
       fclose($fp);
       $css_content = file(dirname(__FILE__) . DIRECTORY_SEPARATOR."styles/".$wpdb->prefix."_style.css");
     }
     else {
       $css_content = file(dirname(__FILE__) . DIRECTORY_SEPARATOR."styles/".$wpdb->prefix."_style.css");
     }


  }
  $css_content = implode("",$css_content);
  echo  "<textarea id='css_edit_content' style='width:590px; height:600px;'>$css_content</textarea>";
  exit();
}


if($aktion=="save_css") {
  if($use_session) {
    if($cert!=session_id()) {
      echo "<script type='text/javascript'>alert('SESS-ERROR');close_fancy();</script>";
      exit();
    }
  }

  if (!is_multisite())
    $fp = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR ."style.css","w+");
  else
    $fp = fopen(dirname(__FILE__) . DIRECTORY_SEPARATOR."styles/".$wpdb->prefix."_style.css","w+");
  fwrite($fp, $content);
  fclose($fp);
  echo "<script type='text/javascript'>close_fancy();</script>";
  exit();
}


if($aktion=="chomd_css") {
  if(chmod (dirname(__FILE__) . DIRECTORY_SEPARATOR .$content , 0777 ))
    echo "$content $css_chmod_permission";
  else
    echo $css_chmod_permission_err;

}



//===== Modul Upload ===============================
if($aktion=="get_modulform") {

  if($use_session) {
    if($cert!=session_id()) {
      echo "<script type='text/javascript'>alert('SESS-ERROR');close_fancy();</script>";
      exit();
    }
  }

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




//===== Sortier-Optionen bereitstellen ===============================
if($aktion=="get_sortoptions") {
  $content = explode("-",$content);

  $sort_options = $sorting_arr[$content[1]];

  foreach($sort_options as $sort_option) {
    $anzeige = array_search($sort_option,$sort_options);
    if ($content[0]==$anzeige)
      echo '<option value="'.$sort_option.'" selected>'.$anzeige.'</option>';
    else
      echo '<option value="'.$sort_option.'">'.$anzeige.'</option>';
  }


}


?>