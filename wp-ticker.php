<?php
/*
Plugin Name: WP-Ticker
Plugin URI: http://www.stegasoft.de/
Description: News Ticker auf jQuery-Basis, RSS-Reader basiert auf dem Script von Sebastian Gollus: http://www.web-spirit.de
Version: 0.12
Author: Stephan G&auml;rtner
Author URI: http://www.stegasoft.de
*/

$table_style = "border:solid 1px #606060;border-collapse:collapse;padding:2px;";

$wpticversion = "0.12";


//============= INCLUDES ==========================================================
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR. "wp-config.php");
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR."wp-includes/wp-db.php");
@include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR ."global.php");

$version = get_bloginfo('version');


define('WPTIC_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)) );
$wptic_plugin_dir = WPTIC_URLPATH;


$wptic_options = get_option( "wptic_options" );


//============= Code für Admin-Kopf erzeugen ============================
/*
function wpticjs2adminhead() {
  global $wptic_plugin_dir,$wptic_options;

  $jscript_includes = "\n";
  $jscript_includes .= "<style type='text/css'><!-- .fe_txt { border:solid 1px #5F5F5F; --></style>\n";
  $jscript_includes .= "<script language='JavaScript' src=\"$qwq_plugin_dir/global/jscolor/jscolor.js\" type=\"text/javascript\"></script>\n\n";

  echo $jscript_includes;
}
add_action('admin_head', 'wpticjs2adminhead');
*/


//============= Code für Template-Kopf erzeugen ============================
function wpticjs2head() {
  global $wptic_plugin_dir,$wptic_options;

  $jscript_includes = "\n\n<!-- ***** WP-Ticker ***** -->\n";
  $jscript_includes .= "<link rel='stylesheet' href='$wptic_plugin_dir/style.css' type='text/css' />\n";
  $jscript_includes .= "<script src=\"$wptic_plugin_dir/js/jquery.js\" type=\"text/javascript\"></script>\n";
  $jscript_includes .= "<script src=\"$wptic_plugin_dir/js/modules.php\" type=\"text/javascript\"></script>\n";
  $jscript_includes .= "<!-- ********************* -->\n\n";

  echo $jscript_includes;
}
add_action('wp_head', 'wpticjs2head');



//============= Plugin - Button einbauen =====================================
add_action('admin_menu', 'wptic_page');
function wptic_page() {
    add_submenu_page('plugins.php', __('WP-Ticker'), __('WP-Ticker'), 10, 'wpticadmin', 'wptic_options_page');
}


//============= Ticker-Tabelle erstellen =====================================
register_activation_hook(__FILE__, 'wptic_install');
function wptic_install() {
  global $wpdb;

  $install_query = "CREATE TABLE " . $wpdb->prefix ."wp_ticker (ID bigint(20) unsigned NOT NULL auto_increment, Optionen longtext NOT NULL, Daten text NOT NULL, Typ varchar(100) NOT NULL, Template text NOT NULL, Memo text NOT NULL, PRIMARY KEY  (ID))";

  // nur erstellen, wenn Tabelle noch nicht existiert
  include_once (ABSPATH."/wp-admin/upgrade-functions.php");
  @maybe_create_table($wpdb->prefix . "wp_ticker", $install_query);

}



//============= Tabellen/Optionen loeschen ===================================
if($wptic_options["deinstall"] == "yes")
  register_deactivation_hook(__FILE__, 'wptic_deinstall');
function wptic_deinstall() {
  global $wpdb,$wptic_options;
  delete_option('wptic_options');
  $wpdb->query("DROP TABLE " . $wpdb->prefix ."wp_ticker");
  $wpdb->query("OPTIMIZE TABLE $wpdb->options");
}


//============ Funktion für Template =======================================
function show_wpticker($id) {
  global $wpdb,$wptic_options,$wptic_plugin_dir,$tcpr;

  //Daten zu Ticker-ID auslesen
  $befehl = "SELECT Optionen,Daten,Template,Typ FROM ".$wpdb->prefix ."wp_ticker WHERE ID=$id";
  $ticdaten = $wpdb->get_results($befehl);

  foreach ($ticdaten as $ticdat) {
    $optionen = unserialize($ticdat->Optionen); //Array()
    $daten = $ticdat->Daten;
    $type = $ticdat->Typ;
    $template = $ticdat->Template;
  }

  /*
  $param_array = Array("id"=>$id,
                       "src"=>$optionen['src'],
                       "showtime"=>$optionen['showtime'],
                       "intime"=>$optionen['intime'],
                       "outtime"=>$optionen['outtime'],
                       "data"=>$daten,
                       "type"=>$type,
                       "items"=>$optionen['itemcount'],
                       "chars"=>$optionen['charcount']);
  */
  $template = stripslashes($template);

  $code = '<!-- WP-Ticker-Content Begin -->'."\n".'<div class="ticker_content" id="ticker_content_'.$id.'" onmouseover="jTickerEnd'.$type.'('.$id.')" onmouseout="jTickerStart'.$type.'('.$id.')">'."\n";

  if($optionen['src']=="db")
    $code .= wptic_get_dbdata($optionen['itemcount'],$daten,$optionen['charcount'],$template);
  else if($optionen['src']=="own")
    $code .= wptic_get_owndata($daten);
  else if($optionen['src']=="rss")
    $code .= wptic_get_rssdata($optionen['itemcount'],$daten,$optionen['charcount'],$template);


  $code .= '</div>'."\n".base64_decode($tcpr).
           '<script type="text/javascript">'.
           'show_time['.$id.'] = '.$optionen['showtime'].';'.
           'out_time['.$id.'] = '.$optionen['outtime'].';'.
           'in_time['.$id.'] = '.$optionen['intime'].';'.
           'fade_timer['.$id.'];'.
           'jTickerStart'.$type.'('.$id.');'.
           '</script>'."\n<!-- WP-Ticker-Content END -->\n";


  echo $code;
}

//============ Platzhalter ersetzen =========================================
//------------ [wpticker] ----------------------------------------------
function wptic_get_params($atts) {
  global $wpdb,$wptic_options,$wptic_plugin_dir,$tcpr;

  extract(shortcode_atts(array('id'=>1), $atts));

  //Daten zu Ticker-ID auslesen
  $befehl = "SELECT Optionen,Daten,Template,Typ FROM ".$wpdb->prefix ."wp_ticker WHERE ID=$id";
  $ticdaten = $wpdb->get_results($befehl);

  foreach ($ticdaten as $ticdat) {
    $optionen = unserialize($ticdat->Optionen); //Array()
    $daten = $ticdat->Daten;
    $type = $ticdat->Typ;
    $template = $ticdat->Template;
  }

  $template = stripslashes($template);

  $code = '<!-- WP-Ticker-Content Begin -->'."\n".'<div class="ticker_content" id="ticker_content_'.$id.'" onmouseover="jTickerEnd'.$type.'('.$id.')" onmouseout="jTickerStart'.$type.'('.$id.')">'."\n";


  if($optionen['src']=="db")
    $code .= wptic_get_dbdata($optionen['itemcount'],$daten,$optionen['charcount'],$template);
  else if($optionen['src']=="own")
    $code .= wptic_get_owndata($daten);
  else if($optionen['src']=="rss") {
    $code .= "<!-- RSS Feed Script von Sebastian Gollus: http://www.web-spirit.de/webdesign-tutorial/7/RSS-Feed-auslesen-mit-PHP -->\n";
    $code .= wptic_get_rssdata($optionen['itemcount'],$daten,$optionen['charcount'],$template);
  }

  $code .= '</div>'."\n".base64_decode($tcpr)."\n".
           '<script type="text/javascript">'."\n".
           'show_time['.$id.'] = '.$optionen['showtime'].';'."\n".
           'out_time['.$id.'] = '.$optionen['outtime'].';'."\n".
           'in_time['.$id.'] = '.$optionen['intime'].';'."\n".
           'fade_timer['.$id.'];'."\n".
           'jTickerStart'.$type.'('.$id.');'."\n".
           '</script>'."\n<!-- WP-Ticker-Content END -->\n";


  return $code;
}
add_shortcode('wpticker', 'wptic_get_params');



//============= Seite für Plugin-Administration aufbauen ====================
function wptic_options_page() {
  global $wpdb,$wptic_plugin_dir,$wpticversion;

  if (defined('WPLANG')) {
    $lang = WPLANG;
  }
  if (empty($lang)) {
    $lang = 'de_DE';
  }

  if(!@include_once "lang/".$lang.".php")
    include_once "lang/en_EN.php";


  // Read in existing option value from database
  $wptic_options = get_option( "wptic_options" );
  $wptic_deinstall = $wptic_options["deinstall"];

  // See if the user has posted us some information
  // If they did, this hidden field will be set to 'Y'
  if( $_POST[ 'wptic_submit_hidden' ] == "Y" ) {

    // Read their posted value
    $wptic_deinstall = $_POST[ 'wptic_deinstall' ];

    // Save the posted value in the database
    $wptic_options["deinstall"] = $wptic_deinstall;

    update_option( "wptic_options", $wptic_options );


    //+++ gesendete Daten aufbereiten +++++

    if($_POST['wptic_src']=="db") {
      $tic_cat = $_POST['wptic_cat'];
      if(is_array($tic_cat))
        $data = implode(",",$tic_cat);
    }
    else
      $data = $_POST['wptic_data'];

    $typ = $_POST['wptic_type'];
    $template = $_POST['wptic_template'];
    $memo = $_POST['wptic_memo'];

    if(get_magic_quotes_gpc==0) {
      $template = addslashes($template);
      $memo = addslashes($memo);
    }

    $optionen = Array("showtime"=>$_POST['wptic_showtime'],
                      "intime"=>$_POST['wptic_intime'],
                      "outtime"=>$_POST['wptic_outtime'],
                      "src"=>$_POST['wptic_src'],
                      "itemcount"=>$_POST['wptic_itemcount'],
                      "charcount"=>$_POST['wptic_charcount']);

    //++++++ Ticker speichern/updaten/löschen +++++++
    if($_POST[ 'wptic_aktion' ]=="insert")
      $befehl = "INSERT INTO ".$wpdb->prefix ."wp_ticker (Optionen,Daten,Typ,Template,Memo) VALUES ('".serialize($optionen)."','$data','$typ','$template','$memo')";
    else if($_POST[ 'wptic_aktion' ]=="update")
      $befehl = "UPDATE ".$wpdb->prefix ."wp_ticker SET Optionen='".serialize($optionen)."',Daten='$data',Typ='$typ',Template='$template',Memo='$memo' WHERE ID=".$_POST[ 'wptic_id' ];
    else if($_POST[ 'wptic_aktion' ]=="delete")
      $befehl = "DELETE FROM ".$wpdb->prefix ."wp_ticker WHERE ID=".$_POST[ 'wptic_id' ];
    else
      $befehl = "INSERT INTO ".$wpdb->prefix ."wp_ticker (Optionen,Daten,Typ,Template,Memo) VALUES ('".serialize($optionen)."','$data','$typ','$template','$memo')";

    $wpdb->query($befehl);



    // Put an options updated message on the screen
    ?>
    <div class="updated"><p><strong><?php echo $istgespeichert_w; ?></strong></p></div>
    <?php

  } //bei Formularversand


  if($wptic_deinstall=="yes")
    $wptic_deinstall_check = " checked";
  else
    $wptic_deinstall_check = "";


  //+++++ MODULE AUSLESEN +++++++++++
  $verzeichnis = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR."wp-content/plugins/wp-ticker/modules/";
  $modules = "";
  $js_script = "var ticker_hints = new Array();\n".
               "ticker_hints[0] = new Object;\n";
  $dir = opendir($verzeichnis);
  $first_modul = "";
  while($datei = readdir($dir)) {
    if (is_file($verzeichnis.$datei) && (substr($datei, -3, 3) == "tic")) {
      $ini_data = parse_ini_file($verzeichnis.$datei);
      $modules .= '<option value="'.$ini_data["name"].'">'.$ini_data["name"].'</option>';
      $js_script .= 'ticker_hints[0]["'.$ini_data["name"].'"] = "'.$ini_data["hint"].'";'."\n";
      if($first_modul=="")
        $first_modul = $ini_data["hint"];
    }
  }


  //+++++ KATEGORIEN AUSLESEN +++++++++++
  $cat_items = "";
  $cats = get_categories('');
  foreach ($cats as $cat) {
     //$cat_items .= '<option value="'.$cat->term_id.'">'.$cat->name.'</option>';
     $cat_items .= '<input type="checkbox" name="wptic_cat['.$cat->term_id.']" value="'.$cat->term_id.'" />'.$cat->name." &nbsp; &nbsp; ";

  }


  //+++++ TICKER AUSLESEN ++++++++++++++++
  $befehl = "SELECT ID,Optionen,Daten,Typ,Template,Memo FROM ".$wpdb->prefix ."wp_ticker ORDER BY ID ASC";
  $ticdaten = $wpdb->get_results($befehl);
  $ticker_tabelle = '<table id="tictable" class="widefat">';
  $ticker_tabelle .= '<thead><tr><th align="center" style="width:30px;">ID</th><th align="center" style="width:70px;">'.$tickersrc_w.'</th><th align="center" style="width:70px;">'.$tickertype_w.'</th><th style="width:300px;" align="left">Memo</th><th>&nbsp;</th></thead><tbody>';

  foreach ($ticdaten as $ticdat) {
    $optionen = unserialize($ticdat->Optionen); //Array()
    $daten = $ticdat->Daten;
    $daten = Str_replace("\r\n","[brn]",$daten);
    $daten = Str_replace("\r","[br]",$daten);
    $daten = Str_replace("\n","[bn]",$daten);
    if($optionen['src']=="own")
      $daten = base64_encode($daten);
    $type = $ticdat->Typ;

    $ticker_tabelle .= '<tr>'.
                       '<td align="center">'.$ticdat->ID.'</td>'.
                       '<td align="center">'.$optionen['src'].'</td>'.
                       '<td align="center">'.$ticdat->Typ.'</td>'.
                       '<td align="left">'.$ticdat->Memo.'</td>'.
                       '<td align="right">'.
                        '<input type="button" id="ticeditbtn_'.$ticdat->ID.'" name="ticeditbtn_'.$ticdat->ID.'" value="'.$editbtn_w.'" onclick="ticker_edit('.$ticdat->ID.')" /> '.
                        '<input type="button" id="ticdelbtn_'.$ticdat->ID.'" name="ticdelbtn_'.$ticdat->ID.'" value="'.$deletebtn_w.'" onclick="ticker_delete('.$ticdat->ID.')" /> '.
                        '<input type="button" id="ticcodebtn_'.$ticdat->ID.'" name="ticcodetn_'.$ticdat->ID.'" value="'.$codebtn_w.'" onclick="ticker_code('.$ticdat->ID.')"/>'.
                        '<input type="hidden" name="u_src_'.$ticdat->ID.'" value="'.$optionen['src'].'" />'.
                        '<input type="hidden" name="u_data_'.$ticdat->ID.'" value="'.$daten.'" />'.
                        '<input type="hidden" name="u_showtime_'.$ticdat->ID.'" value="'.$optionen['showtime'].'" />'.
                        '<input type="hidden" name="u_intime_'.$ticdat->ID.'" value="'.$optionen['intime'].'" />'.
                        '<input type="hidden" name="u_outtime_'.$ticdat->ID.'" value="'.$optionen['outtime'].'" />'.
                        '<input type="hidden" name="u_typ_'.$ticdat->ID.'" value="'.$ticdat->Typ.'" />'.
                        '<input type="hidden" name="u_itemcount_'.$ticdat->ID.'" value="'.$optionen['itemcount'].'" />'.
                        '<input type="hidden" name="u_charcount_'.$ticdat->ID.'" value="'.$optionen['charcount'].'" />'.
                        '<input type="hidden" name="u_template_'.$ticdat->ID.'" value="'.$ticdat->Template.'" />'.
                        '<input type="hidden" name="u_memo_'.$ticdat->ID.'" value="'.$ticdat->Memo.'" />'.

                       '</td>'.
                       '</tr>';
  }
  $ticker_tabelle .= '</tbody></table>';


  //+++++ AKTUELLE ID AUSLESEN +++++++++++++++++++++++++++++
  $befehl = "SHOW TABLE STATUS FROM ".DB_NAME." LIKE '".$wpdb->prefix ."wp_ticker'";
  $tabledaten = $wpdb->get_results($befehl);
  foreach ($tabledaten as $tabledat) {
    $last_id = $tabledat->Auto_increment;
  }


  //============ Now display the options editing screen ===========================
  echo "<div class=\"wrap\">";

  // header
  echo "<h2>" . __( "WP-Ticker $wpticversion Administration", "wptic_trans_domain" ) ."</h2>";

  // options form

  ?>

  <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
  <input type="hidden" name="wptic_submit_hidden" value="Y" />

  <table border="0" cellpadding="3" cellspacing="0">
   <tr><td colspan="3"><br /><b><?php echo $allgemeines_w; ?>:</b><br />&nbsp;</td></tr>
   <tr>
    <td style="width:140px;">
    <?php echo $deinstall_w; ?>:</td>
    <td><input type="checkbox" name="wptic_deinstall" value="yes"<?php echo $wptic_deinstall_check; ?> />
    <?php echo $deinstall_hinweis_w; ?></td>
   </tr>
  </table>
  <br />
  <table border="0" cellpadding="3" cellspacing="0" >
   <tr><td colspan="2"><b><?php echo $codegenerator_w; ?>:</b></td></tr>
   <tr><td><?php echo $tickerid_w; ?>:</td><td><span id="id_span"><?php echo $last_id; ?></span> <input type="hidden" name="wptic_id" value="<?php echo $last_id; ?>" /></td></tr>
   <tr>
    <td valign="top"><?php echo $tickersrc_w; ?>:</td>
    <td>
     <select id="wptic_src" name="wptic_src" onchange="change_data_box(this)" size="1" style="width:110px;">
     <option value="db"><?php echo $tickersrc_db_w; ?></option>
     <option value="own"><?php echo $tickersrc_own_w; ?></option>
     <option value="rss"><?php echo $tickersrc_rss_w; ?></option>
     </select>
     <div style="padding:0;margin:0;padding-top:5px;" id="data_txt"> <?php echo $data_txt_db; ?>:</div>
     <div style="padding:0;margin:0;padding-bottom:20px;" id="data_context"> <?php echo $cat_items; ?></div>
    </td>
   </tr>

   <tr>
    <td colspan="2">
     <?php echo $duration_w; ?><br />
     <?php echo $tickershowtime_w; ?>: <input type="text" name="wptic_showtime" value="3000" style="width:60px;" /><?php echo $tickershowtime_info_w; ?> &nbsp; &nbsp;
     <?php echo $tickerintime_w; ?>: <input type="text" name="wptic_intime" value="1000" style="width:60px;" /><?php echo $tickerintime_info_w; ?> &nbsp; &nbsp;
     <?php echo $tickerouttime_w; ?>: <input type="text" name="wptic_outtime" value="1000" style="width:60px;" /><?php echo $tickerouttime_info_w; ?><br />&nbsp;
    </td>
   </tr>
   <tr>
    <td><?php echo $tickertype_w; ?>:</td>
    <td>
     <select name="wptic_type" size="1" style="width:110px;" onchange="change_modules(this)">
     <?php echo $modules; ?>
     </select>
     <span id="hint_box"><?php echo $first_modul; ?></span>
    </td>
   </tr>
   <tr><td><?php echo $tickermaxitems_w; ?>:</td><td> <input type="text" name="wptic_itemcount" value="5" style="width:60px;" /> (<?php echo $tickermaxitems_info_w; ?>)</td></tr>
   <tr><td><?php echo $tickermaxchars_w; ?>:</td><td> <input type="text" name="wptic_charcount" value="70" style="width:60px;" /> (<?php echo $tickermaxchars_info_w; ?>)</td></tr>

   <tr><td valign="top"><?php echo $template_w; ?>:</td><td valign="top"><textarea name="wptic_template" style="width:250px;height:80px;float:left;">%tic_title%<br /><?php echo chr(13); ?>%tic_content%</textarea> %tic_title% - &nbsp; &nbsp; &nbsp;<?php echo $template_head_w; ?><br /> %tic_content% - <?php echo $template_content_w; ?></tr>

   <tr><td valign="top"><?php echo $memo_w; ?>:</td><td><textarea name="wptic_memo" style="width:250px;height:80px;"></textarea></tr>

  </table>

  <p class="submit">
  <input type="submit" name="Submit" value="<?php echo $speichern_w; ?>" />
  </p>
  <input type="hidden" name="wptic_aktion" value="insert" />
  </form>

  <hr />

  <div style="margin-bottom:10px;"><b><?php echo $ticker_head_w; ?></b></div>
  <form name="tictableform" action="#">
  <?php echo $ticker_tabelle; ?>
  </form>
  <br />
  <hr />





  <br />
  <?php echo $fußnote_w; ?>


  </div>

  <script type="text/javascript">

  function ticker_edit(id) {
    document.form1.wptic_aktion.value="update";
    document.form1.wptic_id.value=id;
    document.getElementById('id_span').innerHTML=id;

    var u_src = "u_src_"+id;
    if(document.forms["tictableform"].elements[u_src].value=="db") {
      document.form1.wptic_src.selectedIndex = 0;
      document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_db; ?>:';
      document.getElementById('data_context').innerHTML ='<?php echo $cat_items; ?>';

      var data = document.forms["tictableform"].elements["u_data_"+id].value;
      var cat_arr = data.split(",");
      for (var i=0;i<cat_arr.length;i++) {
          document.form1.elements["wptic_cat["+cat_arr[i]+"]"].checked = true;
      }
    }


    if(document.forms["tictableform"].elements[u_src].value=="own") {
      document.form1.wptic_src.selectedIndex = 1;
      document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_own; ?>:';
      document.getElementById('data_context').innerHTML = '<textarea name="wptic_data" style="width:400px;height:170px;"><\/textarea>';
      var u_daten = base64_decode (document.forms["tictableform"].elements["u_data_"+id].value);
      u_daten = str_replace("[brn]", "\r\n", u_daten);
      u_daten = str_replace("[br]", "\r", u_daten);
      u_daten = str_replace("[bn]", "\n", u_daten);

      document.form1.wptic_data.value = u_daten;
    }


    if(document.forms["tictableform"].elements[u_src].value=="rss") {
      document.form1.wptic_src.selectedIndex = 2;
      document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_rss; ?>:';
      document.getElementById('data_context').innerHTML = '<textarea name="wptic_data" style="width:400px;height:170px;"><\/textarea>';
      var u_daten = document.forms["tictableform"].elements["u_data_"+id].value;
      u_daten = str_replace("[brn]", "\r\n", u_daten);
      u_daten = str_replace("[br]", "\r", u_daten);
      u_daten = str_replace("[bn]", "\n", u_daten);
      document.form1.wptic_data.value = u_daten;
      //document.form1.wptic_data.value = document.forms["tictableform"].elements["u_data_"+id].value;
    }

    document.form1.wptic_showtime.value = document.forms["tictableform"].elements["u_showtime_"+id].value;
    document.form1.wptic_intime.value = document.forms["tictableform"].elements["u_intime_"+id].value;
    document.form1.wptic_outtime.value = document.forms["tictableform"].elements["u_outtime_"+id].value;
    document.form1.wptic_itemcount.value = document.forms["tictableform"].elements["u_itemcount_"+id].value;
    document.form1.wptic_charcount.value = document.forms["tictableform"].elements["u_charcount_"+id].value;
    document.form1.wptic_template.value = document.forms["tictableform"].elements["u_template_"+id].value;
    document.form1.wptic_memo.value = document.forms["tictableform"].elements["u_memo_"+id].value;


    for (var i=0; i<document.form1.wptic_type.length; i++) {
      if(document.form1.wptic_type[i].value==document.forms["tictableform"].elements["u_typ_"+id].value){
        document.form1.wptic_type.selectedIndex = i;
        document.getElementById("hint_box").innerHTML=ticker_hints[0][document.form1.wptic_type[i].value];
        break;
      }
    }

    //document.form1.wptic_id.style.visibility = "hidden";
    document.form1.wptic_id.disabled = false;
  }

  function ticker_delete(id) {
    if(confirm("<?php echo $delete_quest_w; ?>"+id+" ?")) {
      document.form1.wptic_aktion.value="delete";
      document.form1.wptic_id.value=id;
      document.form1.wptic_id.disabled = false;
      document.form1.submit();
    }

  }

  function ticker_code(id) {
    alert("<?php echo $code_info_w; ?>Post: [wpticker id="+id+"]\nTemplate: <"+"?php show_wpticker("+id+") ?>");
  }


  function change_data_box(obj) {
    switch(obj.value) {
         case "db": document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_db; ?>:';
                    document.getElementById('data_context').innerHTML ='<?php echo $cat_items; ?>';
                    break;
         case "own": document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_own; ?>:';
                     document.getElementById('data_context').innerHTML = '<textarea name="wptic_data" style="width:400px;height:170px;"><\/textarea>';
                     break;
         case "rss": document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_rss; ?>:';
                     document.getElementById('data_context').innerHTML = '<textarea name="wptic_data" style="width:400px;height:170px;"><\/textarea>';
                     break;
         default: document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_cat; ?>:';
                  document.getElementById('data_context').innerHTML = '<?php echo $cat_items; ?>';
                  break;
    }
  }

  function str_replace(search, replace, subject) {
    return subject.split(search).join(replace);
  }


function base64_decode (data) {
    // Decodes string using MIME base64 algorithm
    //
    // version: 1004.2314
    // discuss at: http://phpjs.org/functions/base64_decode    // +   original by: Tyler Akins (http://rumkin.com)
    // +   improved by: Thunder.m
    // +      input by: Aman Gupta
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman    // +   bugfixed by: Pellentesque Malesuada
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // -    depends on: utf8_decode    // *     example 1: base64_decode('S2V2aW4gdmFuIFpvbm5ldmVsZA==');
    // *     returns 1: 'Kevin van Zonneveld'
    // mozilla has this native
    // - but breaks in 2.0.0.12!
    //if (typeof this.window['btoa'] == 'function') {    //    return btoa(data);
    //}

    var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, dec = "", tmp_arr = [];
    if (!data) {
        return data;
    }
     data += '';

    do {  // unpack four hexets into three octets using index points in b64
        h1 = b64.indexOf(data.charAt(i++));
        h2 = b64.indexOf(data.charAt(i++));        h3 = b64.indexOf(data.charAt(i++));
        h4 = b64.indexOf(data.charAt(i++));

        bits = h1<<18 | h2<<12 | h3<<6 | h4;
         o1 = bits>>16 & 0xff;
        o2 = bits>>8 & 0xff;
        o3 = bits & 0xff;

        if (h3 == 64) {            tmp_arr[ac++] = String.fromCharCode(o1);
        } else if (h4 == 64) {
            tmp_arr[ac++] = String.fromCharCode(o1, o2);
        } else {
            tmp_arr[ac++] = String.fromCharCode(o1, o2, o3);        }
    } while (i < data.length);

    dec = tmp_arr.join('');
    dec = this.utf8_decode(dec);
    return dec;
}


function utf8_decode ( str_data ) {
    // Converts a UTF-8 encoded string to ISO-8859-1
    //
    // version: 1004.2314
    // discuss at: http://phpjs.org/functions/utf8_decode    // +   original by: Webtoolkit.info (http://www.webtoolkit.info/)
    // +      input by: Aman Gupta
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Norman "zEh" Fuchs
    // +   bugfixed by: hitwork    // +   bugfixed by: Onno Marsman
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: utf8_decode('Kevin van Zonneveld');
    // *     returns 1: 'Kevin van Zonneveld'

    var tmp_arr = [], i = 0, ac = 0, c1 = 0, c2 = 0, c3 = 0;

    str_data += '';

    while ( i < str_data.length ) {        c1 = str_data.charCodeAt(i);
        if (c1 < 128) {
            tmp_arr[ac++] = String.fromCharCode(c1);
            i++;
        } else if ((c1 > 191) && (c1 < 224)) {            c2 = str_data.charCodeAt(i+1);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 31) << 6) | (c2 & 63));
            i += 2;
        } else {
            c2 = str_data.charCodeAt(i+1);            c3 = str_data.charCodeAt(i+2);
            tmp_arr[ac++] = String.fromCharCode(((c1 & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }
    }
    return tmp_arr.join('');
}

  <?php echo $js_script; ?>


  function change_modules(obj) {
    document.getElementById("hint_box").innerHTML=ticker_hints[0][obj.value];
  }

  </script>



  <?
}


//===== DATEN AUS EIGENEM TEXT ================================
function wptic_get_owndata($content) {
  $content_array = explode(";;",$content);
  $output = "";
  $k=0;
  foreach ($content_array as $content) {
    if($k==0)
      $anfang = '<div>';
    else
      $anfang = '<div style="display:none;">';
    $output .= $anfang.$content.'</div>';
    $k++;
  }
  return $output;
}


//===== DATEN AUS RSS_FEEDS ====================================
function wptic_get_rssdata($no_posts, $urls, $maxchar,$template) {
  global $more_tag;

  $url_array = explode("\r\n",$urls);

  $output = "";
  $headline = "";
  $item_head = "";

  $k=0;
  foreach($url_array as $url) {

    $url_elem = explode(";",$url);

    $data_array = wptic_getRssfeed($url_elem[0], $url_elem[1], $no_posts, 3);

    $item_array = $data_array[2];

    $link = trim($data_array[1]);
    if($link=="")
      $link = "#";

    $headline = '<a href="'.$link.'" target="_blank"><b>'.$data_array[0].'</b></a><br />';

    foreach($item_array as $items) {
      if($k==0)
        $anfang = '<div>';
      else
        $anfang = '<div style="display:none;">';

      $link = trim($items[1]);
      if($link=="")
        $link = "#";
      $item_head = '<a href="'.$link.'" target="_blank">'.$items[0].'</a>';

      $content = wptic_shrink_data($items[2],$maxchar);

      $template_stack = str_replace("%tic_title%",$headline.$item_head,$template);
      if(trim($content)!="")
        $template_stack = str_replace("%tic_content%",$content,$template_stack);
      else
        $template_stack = str_replace("%tic_content%","",$template_stack);

      $template_stack = str_replace("<-ticend->",'<a href="'.$link.'" target="_blank">'.$more_tag.'</a>',$template_stack);

      $template_stack = trim($template_stack);

      $output .= $anfang.$template_stack.'</div>';
      $k++;
    }


  }



  return $output;
}



//===== DATEN AUS DB ============================================
function wptic_get_dbdata($no_posts, $catids = 1, $maxchar,$template) {
  global $wpdb,$more_tag;

  if(trim($no_posts)!="")
    $limit = " LIMIT $no_posts";
  else
    $limit = "";

  $output = '';

  $catid_arr = explode(",",$catids);

  $k=0;
  foreach($catid_arr as $catid) {

    $request = "SELECT DISTINCT wposts.* FROM $wpdb->posts wposts LEFT JOIN $wpdb->postmeta wpostmeta ON wposts.ID = wpostmeta.post_id LEFT JOIN $wpdb->term_relationships ON (wposts.ID = $wpdb->term_relationships.object_id) LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id ='$catid' AND wposts.post_status='publish' AND wposts.post_type='post' ORDER BY wposts.post_date DESC$limit";
    $posts = $wpdb->get_results($request);

    if($posts) {

      foreach ($posts as $post) {
        $post_title = stripslashes($post->post_title);
        $permalink = get_permalink($post->ID);
        $post_content = stripslashes($post->post_content);

        if($maxchar!="")
          $post_content = wptic_shrink_data($post_content,$maxchar);

        $post_content = str_replace("<-ticend->", '<a href="' . $permalink . '" rel="bookmark" title="Permanent Link: ' . htmlspecialchars($post_title, ENT_COMPAT) . '">'.$more_tag.'</a>',$post_content);

        if($k==0)
          $anfang = '<div>';
        else
          $anfang = '<div style="display:none;">';

        $template_stack = str_replace("%tic_title%",'<a href="' . $permalink . '" rel="bookmark" title="Permanent Link: ' . htmlspecialchars($post_title, ENT_COMPAT) . '">' . $post_title . '</a>',$template);
        $template_stack = str_replace("%tic_content%",$post_content,$template_stack);

        $output .= $anfang.$template_stack.'</div';
        $template_stack = "";
        $k++;
      }
    }
    else {
      if($k==0)
        $anfang = '<div>';
      else
        $anfang = '<div style="display:none;">';
      $output .= $anfang.'NO POST FOR CAT-ID '. $catid.'</div>';
      $k++;
    }
  }
  return $output;
}


//===== DATEN KÜRZEN ================================
function wptic_shrink_data($content,$maxchar) {

  if(trim($maxchar) != "") {
    if($maxchar<1)
      $maxchar = 1;
    $last_blank = 0;
    $tag_is_open = false;
    $open_tag_pos = 0;
    if(strlen($content)>$maxchar) {
      for ($i=0; $i<$maxchar; $i++) {
        if ($content[$i] == " ")
          $last_blank = $i;
        if ($content[$i] == "<") {
          $tag_is_open = true;
          $open_tag_pos = $i;
        }
        if ($content[$i] == ">")
          $tag_is_open = false;
      }//for
      if($tag_is_open) {
        $close_tag_pos = strpos($content,">",$open_tag_pos);
        $content = substr($content,0,$close_tag_pos+1)."<-ticend->";
      }
      else
        $content = substr($content,0,$last_blank)."<-ticend->";
    }//if
  }

 return $content;
}

$tcpr = "PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTo4cHQ7Ij5XUC1UaWNrZXIgcG93ZXJlZCBieSA8YSBocmVmPSJodHRwOi8vd3d3LnN0ZWdhc29mdC5kZSIgdGFyZ2V0PSJfYmxhbmsiPlN0ZUdhU29mdDwvYT48L3NwYW4+";


/*
Nutzung dieses Scripts nur gestattet, wenn Kommentare (PHP und HTML)
nicht gelöscht werden, oder ein Link zu folgender Adresse gesetzt wird:
URL: http://www.web-spirit.de/webdesign-tutorial/7/RSS-Feed-auslesen-mit-PHP
Beschreibung: RSS Feed auslesen mit PHP
Autor: Sebastian Gollus
Internet: http://www.web-spirit.de
Version: 1.0.200905
*/

// Funktionsaufruf z.B.: getRssfeed("http://www.web-spirit.de/web-spirit.xml","web-spirit","auto",3,3);

function wptic_getRssfeed($rssfeed, $encode="auto", $anzahl, $mode=0) {
  // $encode e[".*"; "no"; "auto"]

  // $mode e[0; 1; 2; 3]:
  // 0 = nur Titel und Link der Items weden ausgegeben
  // 1 = Titel und Link zum Channel werden ausgegeben
  // 2 = Titel, Link und Beschreibung der Items werden ausgegeben
  // 3 = 1 & 2

  if(trim($anzahl)=="")
    $anzahl = 1000;   // hohen (imaginären) Wert Setzen


  $rss_data_array = Array();
  $rss_item_array = Array();

  // Zugriff auf den RSS Feed
  $data = @file($rssfeed);
  $data = @implode ("", $data);
  if(strpos($data,"</item>") > 0) {
    preg_match_all("/<item.*>(.+)<\/item>/Uism", $data, $items);
    $atom = 0;
  }
  else if(strpos($data,"</entry>") > 0) {
    preg_match_all("/<entry.*>(.+)<\/entry>/Uism", $data, $items);
    $atom = 1;
  }

  // Encodierung
  if($encode == "auto") {
    preg_match("/<?xml.*encoding=\"(.+)\".*?>/Uism", $data, $encodingarray);
    $encoding = $encodingarray[1];
  }
  else {
    $encoding = $encode;
  }

  //echo "<!-- RSS Feed Script von Sebastian Gollus: http://www.web-spirit.de/webdesign-tutorial/7/RSS-Feed-auslesen-mit-PHP -->\n";
  //echo "<div class=\"rssfeed_".$cssclass."\">\n";

  // Titel und Link zum Channel
  if($mode == 1 || $mode == 3) {
    if(strpos($data,"</item>") > 0) {
      $data = preg_replace("/<item.*>(.+)<\/item>/Uism", '', $data);
    }
    else {
      $data = preg_replace("/<entry.*>(.+)<\/entry>/Uism", '', $data);
    }
    preg_match("/<title.*>(.+)<\/title>/Uism", $data, $channeltitle);
    if($atom == 0) {
      preg_match("/<link>(.+)<\/link>/Uism", $data, $channellink);
    }
    else if($atom == 1) {
      preg_match("/<link.*alternate.*text\/html.*href=[\"\'](.+)[\"\'].*\/>/Uism", $data, $channellink);
    }

    $channeltitle = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $channeltitle);
    $channellink = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $channellink);

    //echo "<h1><a href=\"".$channellink[1]."\" title=\"";
    //$channel_headline .= '<a href="'.$channellink[1].'" title='";

    $rss_data_array[1] = $channellink[1];

    if($encode != "no") {
      //echo htmlentities($channeltitle[1],ENT_QUOTES,$encoding);
      $rss_data_array[0] = htmlentities($channeltitle[1],ENT_QUOTES,$encoding);
    }
    else {
      //echo $channeltitle[1];
      $rss_data_array[0] = $channeltitle[1];
    }
    //echo "\">";
    /*
    if($encode != "no") {
      echo htmlentities($channeltitle[1],ENT_QUOTES,$encoding);
    }
    else {
      echo $channeltitle[1];
    }
    echo "</a></h1>\n";
    */
  }

  // Titel, Link und Beschreibung der Items
  $k=0;
  if(is_array($items[1])) {
  foreach ($items[1] as $item) {
    preg_match("/<title.*>(.+)<\/title>/Uism", $item, $title);
    if($atom == 0) {
      preg_match("/<link>(.+)<\/link>/Uism", $item, $link);
    }
    else if($atom == 1) {
      preg_match("/<link.*alternate.*text\/html.*href=[\"\'](.+)[\"\'].*\/>/Uism", $item, $link);
    }
    if($atom == 0) {
      preg_match("/<description>(.*)<\/description>/Uism", $item, $description);
    }
    elseif($atom == 1) {
      preg_match("/<summary.*>(.*)<\/summary>/Uism", $item, $description);
    }

    $title = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $title);
    $description = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $description);
    $link = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $link);

    //echo "<p class=\"link\">\n";
    //echo "<a href=\"".$link[1]."\" title=\"";

    $rss_item_array[$k][1] = $link[1];

    if($encode != "no") {
      //echo htmlentities($title[1],ENT_QUOTES,$encoding);
      $rss_item_array[$k][0] = htmlentities($title[1],ENT_QUOTES,$encoding);
    }
    else {
      //echo $title[1];
      $rss_item_array[$k][0] = $title[1];
    }
    //echo "\">";
    /*
    if($encode != "no") {
      echo htmlentities($title[1],ENT_QUOTES,$encoding)."</a>\n";
    }
    else {
      echo $title[1]."</a>\n";
    }
    echo "</p>\n";
    */


    if($mode == 2 || $mode == 3 && ($description[1]!="" && $description[1]!=" ")) {
      //echo "<p class=\"description\">\n";
      if($encode != "no") {
        //echo htmlentities($description[1],ENT_QUOTES,$encoding)."\n";
        $rss_item_array[$k][2] = htmlentities($description[1],ENT_QUOTES,$encoding);
      }
      else {
        //echo $description[1];
        $rss_item_array[$k][2] = $description[1];
      }
      //echo "</p>\n";
    }
    if ($anzahl-- <= 1) break;
    $k++;
  }
  //echo "</div>\n\n";
  }
  $rss_data_array[2] = $rss_item_array;

  return $rss_data_array;
}




?>