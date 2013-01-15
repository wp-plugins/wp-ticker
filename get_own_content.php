<?php

@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR. "wp-config.php");
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR."wp-includes/wp-db.php");
@include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR ."tic-global.php");

define('WPTIC_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)) );
$wptic_plugin_dir = WPTIC_URLPATH;

if(!session_id())
  session_start();



$lang = get_bloginfo("language");
$lang = str_replace("-","_",$lang);

if (empty($lang) || trim($lang)=="") {
  $lang = 'en_EN';
}

if(!@include_once dirname(__FILE__) . DIRECTORY_SEPARATOR ."lang/".$lang.".php")
  include_once dirname(__FILE__) . DIRECTORY_SEPARATOR ."lang/en_EN.php";



$ticker_id = $_POST['ticker_id'];
$aktion_id = $_POST['aktion_id'];
$content = array($_POST['content'],$_POST['startdate'],$_POST['enddate'],$_POST['autodelete']);
$aktion = $_POST['aktion'];
$cert = $_POST['cert'];




//===== eigenen Tickertext einfügen =====
if($aktion == "insert") {

  if($use_session) {
    if($cert!=session_id()) {
      echo "<script type='text/javascript'>alert('SESS-ERROR');close_fancy();</script>";
      exit();
    }
  }

  if($content[3]!="j")
    $autodelete = "n";
  else
    $autodelete = $content[3];
  $befehl = "INSERT INTO ".$wpdb->prefix ."wp_ticker_content (Ticker_ID,Daten,Zeige_Start,Zeige_Ende,Auto_Delete) VALUES ($ticker_id,'".addslashes($content[0])."','$content[1]','$content[2]','$autodelete')";
  $ticdaten = $wpdb->get_results($befehl);

  echo get_tickers($ticker_id);
}


//===== Ticker-Daten zu ID ausliefern =====
if($aktion == "edit") {
  if($use_session) {
    if($cert!=session_id()) {
      echo "<script type='text/javascript'>alert('SESS-ERROR');close_fancy();</script>";
      exit();
    }
  }

  $befehl = "SELECT ID,Ticker_ID,Daten,Zeige_Start,Zeige_Ende,Auto_Delete FROM ".$wpdb->prefix ."wp_ticker_content WHERE ID=$aktion_id ORDER BY ID";
  $ticdaten = $wpdb->get_results($befehl);
  foreach ($ticdaten as $ticdat) {
    $text = $ticdat->Daten;
    $start = explode("-",$ticdat->Zeige_Start);
    $ende = explode("-",$ticdat->Zeige_Ende);

    if($ticdat->Auto_Delete=="j")
      $autodel = " checked";
    else
      $autodel = "";
  }



     $start_tag = "<option value='00'>$tag_w</option>";
     $end_tag = "<option value='00'>$tag_w</option>";
      for ($i=1; $i<32;$i++) {
        if($i<10)
          $tagwert = "0".$i;
        else
          $tagwert = $i;

        if($tagwert==$start[2])
          $start_tag .= "<option value='$tagwert' selected>$tagwert</option>";
        else
          $start_tag .= "<option value='$tagwert'>$tagwert</option>";

        if($tagwert==$ende[2])
          $end_tag .= "<option value='$tagwert' selected>$tagwert</option>";
        else
          $end_tag .= "<option value='$tagwert'>$tagwert</option>";

      }

      $start_monat = "<option value='00'>$monat_w</option>";
      $end_monat = "<option value='00'>$monat_w</option>";
      for ($i=1; $i<13;$i++) {
        if($i<10)
          $monatwert = "0".$i;
        else
          $monatwert = $i;

        if($monatwert==$start[1])
          $start_monat .= "<option value='$monatwert' selected>$monatwert</option>";
        else
          $start_monat .= "<option value='$monatwert'>$monatwert</option>";

        if($monatwert==$ende[1])
          $end_monat .= "<option value='$monatwert' selected>$monatwert</option>";
        else
          $end_monat .= "<option value='$monatwert'>$monatwert</option>";


      }


      if( ($max_year < date("Y",time())) || (trim($max_year)=="") || (!is_numeric($max_year)))
        $max_year = date("Y",time());

      $start_jahr = "<option value='00'>$jahr_w</option>";
      $end_jahr = "<option value='00'>$jahr_w</option>";
      for ($i=date("Y",time()); $i<=$max_year;$i++) {
        if($i==$start[0])
          $start_jahr .= "<option value='$i' selected>$i</option>";
        else
          $start_jahr .= "<option value='$i'>$i</option>";
        if($i==$ende[0])
          $end_jahr .= "<option value='$i' selected>$i</option>";
        else
          $end_jahr .= "<option value='$i'>$i</option>";
      }


  $fancy_code = "<b>$own_ticker_texthinweis</b><br />".
                "<textarea id='tickertext' style='width:390px; height:200px;'>".stripslashes($text)."</textarea><br />".
                "<table border='0' class='widefat' style='width:390px;'>".
                "<tr><td style='width:100px;'><b>$own_ticker_startdata_w:</b></td><td><select id='startdate_d' class='fe_txt fe_date' size='1' >$start_tag</select><select id='startdate_m' class='fe_txt fe_date' size='1' >$start_monat</select><select id='startdate_j' class='fe_txt fe_date' size='1' >$start_jahr</select> <input type='button' value='<= $heute_w' onclick='set_date_today(\"startdate\")' /></td></tr>".
                "<tr><td style='width:100px;'><b>$own_ticker_enddata_w:</b></td><td><select id='enddate_d' class='fe_txt fe_date' size='1' >$end_tag</select><select id='enddate_m' class='fe_txt fe_date' size='1' >$end_monat</select><select id='enddate_j' class='fe_txt fe_date' size='1' >$end_jahr</select> <input type='button' value='<= $heute_w' onclick='set_date_today(\"enddate\")' /></td></tr>".
                "<tr><td style='width:100px;'><b>$own_ticker_autodel_w:</b></td><td><input type='checkbox' id='autodelete' value='j'$autodel /></td></tr>".
                "</table>".
                "<input type='button' value='$speichern_w' onclick='update_own_tictext($aktion_id,$ticker_id)' style='margin-right:10px;' />".
                "<input type='button' value='$abbruch_w' onclick='close_fancy()' />";


  echo $fancy_code;

  exit();

}


//===== Ticker aktualisieren =====
if($aktion == "update") {
  if($use_session) {
    if($cert!=session_id()) {
      echo "<script type='text/javascript'>alert('SESS-ERROR');close_fancy();</script>";
      exit();
    }
  }

  $befehl = "UPDATE ".$wpdb->prefix ."wp_ticker_content SET Daten='".addslashes($content[0])."',Zeige_Start='$content[1]',Zeige_Ende='$content[2]',Auto_Delete='$content[3]' WHERE ID=$aktion_id";
  $ticdaten = $wpdb->get_results($befehl);

  echo get_tickers($ticker_id);
}


//===== Ticker löschen =====
if($aktion == "delete") {
  if($use_session) {
    if($cert!=session_id()) {
      echo "<script type='text/javascript'>alert('SESS-ERROR');close_fancy();</script>";
      exit();
    }
  }


  $befehl = "DELETE FROM ".$wpdb->prefix ."wp_ticker_content WHERE ID=$aktion_id";
  $wpdb->get_results($befehl);

  echo get_tickers($ticker_id);
}


//===== Eigen-Text-Tabelle ausgeben =====
if($aktion == "get_tickers") {
  if($use_session) {
    if($cert!=session_id()) {
      echo '<table border="1" class="widefat">'.
           '<tr>'.
           '<td>SESS-ERROR</td>'.
           '</tr>'.
           '</table>';

      exit();
    }
  }

  echo get_tickers($ticker_id);

}



//===== Daten zu Ticker-ID auslesen und anzeigen =====
function get_tickers($ticker_id) {
  global $wpdb,$own_ticker_startdata_w,$own_ticker_enddata_w,$own_ticker_autodel_w,$wptic_plugin_dir,$editbtn_w,$deletebtn_w,$show_length;

  $befehl = "SELECT ID,Ticker_ID,Daten,Zeige_Start,Zeige_Ende,Auto_Delete FROM ".$wpdb->prefix ."wp_ticker_content WHERE Ticker_ID=$ticker_id ORDER BY ID";
  $ticdaten = $wpdb->get_results($befehl);

  //echo $befehl;

  $code = "";

  $code = '<table border="1" class="widefat">'.
          '<thead>'.
          '<tr>'.
           '<th style="width:50px;">Text-ID</th>'.
           '<th>Text</th>'.
           '<th align="center" style="width:50px;">'.$own_ticker_startdata_w.'</th>'.
           '<th align="center" style="width:50px;">'.$own_ticker_enddata_w.'</th>'.
           '<th style="width:50px;">'.$own_ticker_autodel_w.'</th>'.
           '<th style="width:50px;">&nbsp;</th>'.
          '</tr>'.
          '</thead>'.
          '<tbody>';

  foreach ($ticdaten as $ticdat) {
    $code .= '<tr>'.
              '<td>'.$ticdat->ID.'</td>'.
              '<td>'.stripslashes(word_substr($ticdat->Daten, $show_length, 3, 3)).'</td>'.
              '<td>'.$ticdat->Zeige_Start.'</td>'.
              '<td>'.$ticdat->Zeige_Ende.'</td>'.
              '<td>'.$ticdat->Auto_Delete.'</td>'.
              '<td>'.
               '<input type="button" style="background-color: transparent; background-image: url(\''.$wptic_plugin_dir.'/images/edit.png\'); background-repeat: no-repeat; width: 18px; height: 18px; margin-right:5px;" title="'.$editbtn_w.'" onclick="edit_own_tictext('.$ticdat->ID.','.$ticker_id.')" />'.
               '<input type="button" style="background-color: transparent; background-image: url(\''.$wptic_plugin_dir.'/images/cross.png\'); background-repeat: no-repeat; width: 18px; height: 18px;" title="'.$deletebtn_w.'" onclick="delete_own_tictext('.$ticdat->ID.','.$ticker_id.')" />'.
              '</td>'.
             '</tr>';
  }

  $code .= '</tbody>'.
           '</table>';

  return $code;
}


?>