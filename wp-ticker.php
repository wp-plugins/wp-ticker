<?php
/*
Plugin Name: WP-Ticker
Plugin URI: http://www.stegasoft.de/index.php/wordpress-plugins/wp-ticker/
Description: Modularer (Live-) News Ticker auf jQuery-Basis f&uuml;r WordPress ab Version 3.3
Version: 1.3.2.4
Author: Stephan G&auml;rtner
Author URI: http://www.stegasoft.de
Min WP Version: 3.3
*/

//Datenbank fuer zukuenftige Versionen angepasst

$akt_ticker_id = $_SESSION['wp_ticker_id'];

$table_style = "border:solid 1px #606060;border-collapse:collapse;padding:2px;";

$wpticversion = "1.3.2.4";



//============= INCLUDES ==========================================================
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR. "wp-config.php");
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR."wp-includes/wp-db.php");

define('WPTIC_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)) );
$wptic_plugin_dir = WPTIC_URLPATH;


if ( !defined('WP_CONTENT_DIR') )
  define('WP_CONTENT_DIR', ABSPATH . 'wp-content');

define('MODULE_PATH',WP_CONTENT_DIR . "/plugins/".dirname(plugin_basename(__FILE__)));




@include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR ."tic-global.php");

if (is_multisite()) {
  if(isset($aus)) {
    if(is_array($aus))
      $aus = $aus[$wpdb->prefix];
    else
      $aus = false;
  }
  else
    $aus = false;

}


$version = get_bloginfo('version');


$wptic_options = get_option( "wptic_options" );


//============= Code f�r Admin-Kopf erzeugen ============================
function wpticjs2adminhead() {
  global $wptic_plugin_dir,$wptic_options,$version;

  $jscript_includes = "\n";
  $jscript_includes .= "<link rel='stylesheet' href='$wptic_plugin_dir/admin.css' type='text/css' />\n";

  wp_register_script('fancy', plugins_url().'/wp-ticker/js/fancybox/jquery.fancybox.js',array( 'jquery'),'1.3.4',true);
  wp_enqueue_script('fancy', plugins_url().'/wp-ticker/js/fancybox/jquery.fancybox.js',array( 'jquery'),'1.3.4',true);
  wp_register_style('fancystyle', plugins_url().'/wp-ticker/js/fancybox/jquery.fancybox.css');
  wp_enqueue_style('fancystyle');

  echo $jscript_includes;
}
add_action('admin_head', 'wpticjs2adminhead');


//============= Code f�r Template-Kopf erzeugen ============================
function wpticjs2head() {
  global $wptic_plugin_dir,$wptic_options,$wpdb;

  $jscript_includes = "\n\n<!-- ***** WP-Ticker ***** -->\n";
  if (!is_multisite())
    $jscript_includes .= "<link rel='stylesheet' href='$wptic_plugin_dir/style.css' type='text/css' />\n";
  else
    $jscript_includes .= "<link rel='stylesheet' href='$wptic_plugin_dir/styles/".$wpdb->prefix."_style.css' type='text/css' />\n";
  $jscript_includes .= "<script src=\"$wptic_plugin_dir/js/tic-modules.php\" type=\"text/javascript\"></script>\n";
  $jscript_includes .= "<!-- ********************* -->\n\n";

  echo $jscript_includes;
}
add_action('wp_head', 'wpticjs2head');


function wptic_init() {
  wp_enqueue_script( 'jquery' );
  
  require_once ('wp_autoupdate.php');
  $wptuts_plugin_current_version = $wpticversion;
  $wptuts_plugin_remote_path = 'http://wp-ticker.stegasoft.de/wp-autoupdate/wpticker-update.php';
  $wptuts_plugin_slug = plugin_basename(__FILE__);
  new wp_auto_update ($wptuts_plugin_current_version, $wptuts_plugin_remote_path, $wptuts_plugin_slug);
  
  if(!session_id())
    session_start();


}
add_action('init', 'wptic_init');


//============= Plugin - Button einbauen =====================================
add_action('admin_menu', 'wptic_page');
function wptic_page() {
    add_submenu_page('plugins.php', __('WP-Ticker'), __('WP-Ticker'), 10, 'wpticadmin', 'wptic_options_page');
}


//============= Ticker-Tabelle erstellen =====================================
register_activation_hook(__FILE__, 'wptic_install');
function wptic_install() {
  global $wpdb;

  $install_query = "CREATE TABLE " . $wpdb->prefix ."wp_ticker (ID bigint(20) unsigned NOT NULL auto_increment, Optionen longtext NOT NULL, Daten text NOT NULL, Typ varchar(100) NOT NULL, Template text NOT NULL, Memo text NOT NULL, PRIMARY KEY (ID))";
  // nur erstellen, wenn Tabelle noch nicht existiert
  include_once (ABSPATH."/wp-admin/upgrade-functions.php");
  @maybe_create_table($wpdb->prefix . "wp_ticker", $install_query);

  $install_query = "CREATE TABLE " . $wpdb->prefix ."wp_ticker_content (ID bigint(20) unsigned NOT NULL auto_increment, Ticker_ID INT NOT NULL, Daten text NOT NULL, Zeige_Start varchar(10) NOT NULL DEFAULT '0000-00-00', Zeige_Startzeit varchar(10) NOT NULL DEFAULT '00:00', Zeige_Ende varchar(10) NOT NULL DEFAULT '0000-00-00', Zeige_Endezeit varchar(10) NOT NULL DEFAULT '00:00', Auto_Delete varchar(2) NOT NULL, Erstell_Stamp bigint(20) NOT NULL, PRIMARY KEY (ID), INDEX ( Ticker_ID ))";
  @maybe_create_table($wpdb->prefix . "wp_ticker_content", $install_query);
}



//============= Tabellen/Optionen loeschen ===================================
if($wptic_options["deinstall"] == "yes")
  register_deactivation_hook(__FILE__, 'wptic_deinstall');
function wptic_deinstall() {
  global $wpdb,$wptic_options;
  delete_option('wptic_options');
  $wpdb->query("DROP TABLE " . $wpdb->prefix ."wp_ticker");
  $wpdb->query("DROP TABLE " . $wpdb->prefix ."wp_ticker_content");
  $wpdb->query("OPTIMIZE TABLE $wpdb->options");
}

//===== bei Deaktivierung von WP-Ticker Cronjob entfernen =====
register_deactivation_hook(__FILE__, 'wptic_end_autodelete');
function wptic_end_autodelete() {
  wp_clear_scheduled_hook('wptic_autodelete_hook');
}


//===== auto. L�schen von eigenem Text mit WP-Cron =====
if ( !wp_next_scheduled('wptic_autodelete_hook') ) {
  wp_schedule_event( mktime(1,0,0,date("n",time()),date("j",time()),date("Y",time())), 'hourly', 'wptic_autodelete_hook' ); // hourly, daily and twicedaily
}

//===== auto. L�schen von eigenem Text =====
function wptic_autodelete_own() {
  global $wpdb;
  $heute = date("Y-m-d",time());
  $befehl = "DELETE FROM ".$wpdb->prefix ."wp_ticker_content WHERE Auto_Delete='j' AND Zeige_Ende<'$heute'";
  $result = $wpdb->get_results($befehl);
}
add_action('wptic_autodelete_hook', 'wptic_autodelete_own');



//============ Funktion f�r Template =======================================
function show_wpticker($id) {
  global $wpdb,$wptic_options,$wptic_plugin_dir,$aus,$loader;

  //Daten zu Ticker-ID auslesen
  $befehl = "SELECT Optionen,Daten,Template,Typ FROM ".$wpdb->prefix ."wp_ticker WHERE ID=$id";
  $ticdaten = $wpdb->get_results($befehl);

  foreach ($ticdaten as $ticdat) {
    $optionen = unserialize($ticdat->Optionen); //Array()
    $daten = $ticdat->Daten;
    $type = $ticdat->Typ;
    $template = $ticdat->Template;
  }

  if($optionen["reloadInterval"]=="")
    $interval_faktor = 0;
  else
    $interval_faktor = $optionen["reloadInterval"];
  if($optionen["reloaderPause"]=="")
    $pause_faktor = 0;
  else
    $pause_faktor = $optionen["reloaderPause"];

  $interval_time = $interval_faktor * 60000;
  $pause_time = $pause_faktor * 1000;


  if(!is_numeric($pause_time) || $pause_time<=0)
   $loader = "";

  $template = stripslashes($template);

  $code = '<!-- WP-Ticker-Content Begin -->'."\n".
          '<div id="wptic_code_'.$id.'"></div>'."\n".
          '<script type="text/javascript">'."\n".
          'jQuery.post("'.$wptic_plugin_dir.'/get_ticker_code.php",{ ticker_id: '.$id.'}, function(data) {jQuery("#wptic_code_'.$id.'").html(data);});';
  if(is_numeric($interval_time) && $interval_time>0) {
    $code .= 'setInterval ( function () {'.
          'jQuery("#wptic_code_'.$id.'").html(\'<div class="ticker_content" id="ticker_content_'.$id.'">'.$loader.'<\/div>\');';
    if(is_numeric($pause_time) && $pause_time>0)
       $code .= 'setTimeout(\'jQuery.post("'.$wptic_plugin_dir.'/get_ticker_code.php",{ ticker_id: '.$id.'}, function(data) {jQuery("#wptic_code_'.$id.'").html(data);});\','.$pause_time.');';
    else
       $code .= 'jQuery.post("'.$wptic_plugin_dir.'/get_ticker_code.php",{ ticker_id: '.$id.'}, function(data) {jQuery("#wptic_code_'.$id.'").html(data);});';

    $code .= '},'.$interval_time.');';
  }
  $code .= '</script>'."\n<!-- WP-Ticker-Content END -->\n";


  echo $code;
}

//============ Platzhalter ersetzen =========================================
//------------ [wpticker] ----------------------------------------------
function wptic_get_params($atts) {
  global $wpdb,$wptic_options,$wptic_plugin_dir,$aus,$loader;

  extract(shortcode_atts(array('id'=>1,'sort'=>'DESC'), $atts));

  //Daten zu Ticker-ID auslesen
  $befehl = "SELECT Optionen,Daten,Template,Typ FROM ".$wpdb->prefix ."wp_ticker WHERE ID=$id";
  $ticdaten = $wpdb->get_results($befehl);

  foreach ($ticdaten as $ticdat) {
    $optionen = unserialize($ticdat->Optionen); //Array()
    $daten = $ticdat->Daten;
    $type = $ticdat->Typ;
    $template = $ticdat->Template;
  }

  if($optionen["reloadInterval"]=="")
    $interval_faktor = 0;
  else
    $interval_faktor = $optionen["reloadInterval"];
  if($optionen["reloaderPause"]=="")
    $pause_faktor = 0;
  else
    $pause_faktor = $optionen["reloaderPause"];

  $interval_time = $interval_faktor * 60000;
  $pause_time = $pause_faktor * 1000;

  if(!is_numeric($pause_time) || $pause_time<=0)
    $loader = "";


  $template = stripslashes($template);

  $code = '<!-- WP-Ticker-Content Begin -->'."\n".
          '<div id="wptic_code_'.$id.'"></div>'."\n".
          '<script type="text/javascript">'."\n".
          'jQuery.post("'.$wptic_plugin_dir.'/get_ticker_code.php",{ ticker_id: '.$id.'}, function(data) {jQuery("#wptic_code_'.$id.'").html(data);});';
  if(is_numeric($interval_time) && $interval_time>0) {
    $code .= 'setInterval ( function () {'.
          'jQuery("#wptic_code_'.$id.'").html(\'<div class="ticker_content" id="ticker_content_'.$id.'">'.$loader.'<\/div>\');';
    if(is_numeric($pause_time) && $pause_time>0)
       $code .= 'setTimeout(\'jQuery.post("'.$wptic_plugin_dir.'/get_ticker_code.php",{ ticker_id: '.$id.'}, function(data) {jQuery("#wptic_code_'.$id.'").html(data);});\','.$pause_time.');';
    else
       $code .= 'jQuery.post("'.$wptic_plugin_dir.'/get_ticker_code.php",{ ticker_id: '.$id.'}, function(data) {jQuery("#wptic_code_'.$id.'").html(data);});';

    $code .= '},'.$interval_time.');';
  }
  $code .= '</script>'."\n<!-- WP-Ticker-Content END -->\n";

  return $code;
}
add_shortcode('wpticker', 'wptic_get_params');


//------------ [wptictext] ----------------------------------------------
function wptic_sctictext($atts) {
  global $wpdb,$wptic_options,$wptic_plugin_dir,$aus,$loader;

  extract(shortcode_atts(array('id'=>1,'sort'=>'ASC'), $atts));

  $sort_values = array("ASC","DESC","RAND()");
  if(!in_array(strtoupper($sort),$sort_values))
    $sort = "ASC";

  if(strtoupper($sort)=="RAND()")
    $sort = "ORDER BY RAND()";
  else
    $sort = "ORDER BY ID $sort";

  $output = "";

  $heute = date("Y-m-d",time());

  $befehl = "SELECT ID,Ticker_ID,Daten,Zeige_Start,Zeige_Ende,Auto_Delete FROM ".$wpdb->prefix ."wp_ticker_content WHERE Ticker_ID=$id AND Zeige_Start<='$heute' ".$sort;
  $ticdaten = $wpdb->get_results($befehl);

  foreach ($ticdaten as $ticdat) {
    $anfang = '<div class="tic_owntext_item">';
    $output .= $anfang.stripslashes($ticdat->Daten).'</div>';
  }

  return $output;
}
add_shortcode('wptictext', 'wptic_sctictext');



//============= Seite f�r Plugin-Administration aufbauen ====================
function wptic_options_page() {
  global $wpdb,$wptic_plugin_dir,$wpticversion,$max_year,$aus,$wpticversion,$sorting_arr;

  $lang = get_bloginfo("language");
  $lang = str_replace("-","_",$lang);

  if (empty($lang) || trim($lang)=="") {
    $lang = 'de_DE';
  }

  if(!@include_once "lang/".$lang.".php")
    include_once "lang/en_EN.php";


  // Read in existing option value from database
  $wptic_options = get_option( "wptic_options" );
  $wptic_deinstall = $wptic_options["deinstall"];

  // See if the user has posted us some information
  // If they did, this hidden field will be set to 'Y'
  if( $_POST[ 'wptic_submit_hidden' ] == "J" ) {

    // Read their posted value
    $wptic_deinstall = $_POST[ 'wptic_deinstall' ];

    // Save the posted value in the database
    $wptic_options["deinstall"] = $wptic_deinstall;

    update_option( "wptic_options", $wptic_options );

  }
  else if( $_POST[ 'wptic_submit_hidden' ] == "Y" ) {


    //+++ gesendete Daten aufbereiten +++++

    if($_POST['wptic_src']=="db") {
      $tic_cat = $_POST['wptic_cat'];
      if(is_array($tic_cat))
        $data = implode(",",$tic_cat);
    }
    else
      $data = $_POST['wptic_data'];

    $tic_random = $_POST['wptic_random'];

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
                      "charcount"=>$_POST['wptic_charcount'],
                      "reloadInterval"=>$_POST['wptic_reloadtime'],
                      "reloaderPause"=>$_POST['wptic_reloadpausetime'],
                      "tic_random"=>$_POST['wptic_random']);

    //++++++ Ticker speichern/updaten/l�schen +++++++
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

  if(empty($template))
    $template = "%tic_date%<br />".chr(13)."%tic_title%<br />".chr(13)."%tic_content%";


  //+++++ MODULE AUSLESEN +++++++++++
  //$verzeichnis = dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR."wp-content/plugins/wp-ticker/modules/";
  $verzeichnis = MODULE_PATH . "/modules/";
  $modules = "";
  $js_script = "var ticker_hints = new Array();\n".
               "ticker_hints[0] = new Object;\n";
  $dir = opendir($verzeichnis);
  $first_modul = "";
  while($datei = readdir($dir)) {
    if (is_file($verzeichnis.$datei) && (substr($datei, -3, 3) == "php")) {
      $ini_data = parse_ini_file($verzeichnis.$datei);

      if (is_multisite() && !$aus) {
        if($ini_data["name"]=="Roller")
          $modules .= '<option value="'.$ini_data["name"].'">'.$ini_data["name"].'</option>';
      }
      else
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
     $cat_items .= '<input type="checkbox" name="wptic_cat['.$cat->term_id.']" value="'.$cat->term_id.'" /> '.$cat->name." &nbsp; &nbsp; ";

  }


  //+++++ TICKER AUSLESEN ++++++++++++++++
  $befehl = "SELECT ID,Optionen,Daten,Typ,Template,Memo FROM ".$wpdb->prefix ."wp_ticker ORDER BY ID ASC";
  $ticdaten = $wpdb->get_results($befehl);
  $ticker_tabelle = '<table id="tictable" class="widefat">';
  $ticker_tabelle .= '<thead><tr><th style="width:30px;text-align:center;">ID</th><th style="width:70px;text-align:center;">'.$tickersrc_w.'</th><th style="width:70px;text-align:center;">'.$ticker_random_w.'</th><th style="width:70px;text-align:center;">'.$tickertype_w.'</th><th style="width:300px;text-align:left;">Memo</th><th>&nbsp;</th></thead><tbody>';

  foreach ($ticdaten as $ticdat) {
    $optionen = unserialize($ticdat->Optionen); //Array()
    $daten = $ticdat->Daten;
    $daten = Str_replace("\r\n","[brn]",$daten);
    $daten = Str_replace("\r","[br]",$daten);
    $daten = Str_replace("\n","[bn]",$daten);
    if($optionen['src']=="own")
      $daten = base64_encode($daten);
    $type = $ticdat->Typ;


    $tic_random_anz = array_search($optionen['tic_random'],$sorting_arr[$optionen['src']]);

    $ticker_tabelle .= '<tr>'.
                       '<td style="text-align:center;">'.$ticdat->ID.'</td>'.
                       '<td style="text-align:center;">'.$optionen['src'].'</td>'.
                       '<td style="text-align:center;">'.$tic_random_anz.'</td>'.
                       '<td style="text-align:center;">'.$ticdat->Typ.'</td>'.
                       '<td style="text-align:left;">'.$ticdat->Memo.'</td>'.
                       '<td  style="text-align:right;">'.
                        '<input type="button" id="ticeditbtn_'.$ticdat->ID.'" name="ticeditbtn_'.$ticdat->ID.'" value="'.$editbtn_w.'" onclick="ticker_edit('.$ticdat->ID.')" /> '.
                        '<input type="button" id="ticdelbtn_'.$ticdat->ID.'" name="ticdelbtn_'.$ticdat->ID.'" value="'.$deletebtn_w.'" onclick="ticker_delete('.$ticdat->ID.')" /> '.
                        '<input type="button" id="ticcodebtn_'.$ticdat->ID.'" name="ticcodetn_'.$ticdat->ID.'" value="'.$codebtn_w.'" onclick="ticker_code('.$ticdat->ID.')"/>'.
                        '<input type="hidden" name="u_src_'.$ticdat->ID.'" value="'.$optionen['src'].'" />'.
                        '<input type="hidden" name="u_random_'.$ticdat->ID.'" value="'.$tic_random_anz.'" />'.
                        '<input type="hidden" name="u_data_'.$ticdat->ID.'" value="'.$daten.'" />'.
                        '<input type="hidden" name="u_showtime_'.$ticdat->ID.'" value="'.$optionen['showtime'].'" />'.
                        '<input type="hidden" name="u_intime_'.$ticdat->ID.'" value="'.$optionen['intime'].'" />'.
                        '<input type="hidden" name="u_outtime_'.$ticdat->ID.'" value="'.$optionen['outtime'].'" />'.
                        '<input type="hidden" name="u_reloadtime_'.$ticdat->ID.'" value="'.$optionen['reloadInterval'].'" />'.
                        '<input type="hidden" name="u_reloadpausetime_'.$ticdat->ID.'" value="'.$optionen['reloaderPause'].'" />'.
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
  $befehl = "SHOW TABLE STATUS LIKE '".$wpdb->prefix ."wp_ticker'";
  $tabledaten = $wpdb->get_results($befehl);
  foreach ($tabledaten as $tabledat) {
    $last_id = $tabledat->Auto_increment;
  }
  if($last_id=="")
    $last_id = 1;

  //============ Now display the options editing screen ===========================
  echo "<div class=\"wrap\">";

  // header
  if($aus)
    $off="aus";
  else
    $off="an";
  echo "<h2>" . __( "WP-Ticker $wpticversion Administration", "wptic_trans_domain" ) ."</h2>";

  // options form

  ?>

  <form name="form0" id="form0" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
  <input type="hidden" name="wptic_submit_hidden" value="J" />

  <table class="admintable">
   <tr><td colspan="2"><h3><?php echo $allgemeines_w; ?>:</h3></td></tr>
   <tr>
    <td style="width:140px;">
    <b><?php echo $deinstall_w; ?>:</b></td>
    <td><input type="checkbox" name="wptic_deinstall" value="yes"<?php echo $wptic_deinstall_check; ?> />
    <?php echo $deinstall_hinweis_w; ?></td>
   </tr>
   <tr>
    <td colspan="2" style="padding-top:15px;">
    <input type="submit" name="Submit" value="<?php echo $speichern_w; ?>" style="margin-right:15px;" />

    <input type="button" name="wptic_css_editbut" value="<?php echo $edit_css_button_w; ?>" onclick="edit_css()" />
    <?php if(current_user_can('administrator')) { ?>
    <input type="button" name="wptic_modul_importbut" value="<?php echo $import_modul_button_w ; ?>" onclick="import_module()" />
    <?php } ?>

    </td>
   </tr>
  </table>

  </form>

  <hr style="border:dotted 1px #E6E6E6;" />

  <form name="form1" id="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" style="display:none">
  <input type="hidden" name="wptic_submit_hidden" value="Y" />
  <table class="admintable">
   <tr><td colspan="2"><h3><?php echo $codegenerator_w; ?>:</h3></td></tr>
   <tr><td><b><?php echo $tickerid_w; ?>:</b></td><td><span id="id_span"><?php echo $last_id; ?></span> <input type="hidden" name="wptic_id" value="<?php echo $last_id; ?>" /></td></tr>
   <tr>
    <td style="vertical-align:top;"><b><?php echo $tickersrc_w; ?>:</b></td>
    <td style="vertical-align:top;">
     <select id="wptic_src" name="wptic_src" onchange="change_data_box(this)" size="1" style="width:110px;margin-right:10px;">
     <option value="db"><?php echo $tickersrc_db_w; ?></option>
     <option value="own"><?php echo $tickersrc_own_w; ?></option>
     <option value="rss"><?php echo $tickersrc_rss_w; ?></option>
     <option value="com"><?php echo $tickersrc_com_w; ?></option>
     </select>
     <?php echo $ticker_random_w; ?>
     <select name="wptic_random" id="tic_random">
     <?php echo $tick_sorting; ?>
     </select>
     <?php //<input type="checkbox" name="wptic_random" id="tic_random" value="yes" /> ?>
     <div style="padding:0;margin:0;padding-top:5px;" id="data_txt"> <b><?php echo $data_txt_db; ?>:</b></div>
     <div style="padding:0;margin:0;padding-bottom:20px;" id="data_context"> <?php echo $cat_items; ?></div>
    </td>
   </tr>

   <tr>
    <td colspan="2">
     <b><?php echo $duration_w; ?></b><br />
     <?php echo $tickershowtime_w; ?>: <input type="text" name="wptic_showtime" value="3000" style="width:60px;" /><?php echo $tickershowtime_info_w; ?> &nbsp; &nbsp;
     <?php echo $tickerintime_w; ?>: <input type="text" name="wptic_intime" value="1000" style="width:60px;" /><?php echo $tickerintime_info_w; ?> &nbsp; &nbsp;
     <?php echo $tickerouttime_w; ?>: <input type="text" name="wptic_outtime" value="1000" style="width:60px;" /><?php echo $tickerouttime_info_w; ?>
    </td>
   </tr>
   <tr>
    <td><?php echo $tickerreloadtime_w; ?>:</td><td> <input type="text" name="wptic_reloadtime" value="0" style="width:60px;" /><?php echo $tickerreloadtime_info_w; ?></td>
   </tr>
   <tr>
    <td><?php echo $tickerreloadpausetime_w; ?>: </td><td><input type="text" name="wptic_reloadpausetime" value="0" style="width:60px;" /><?php echo $tickerreloadpausetime_info_w; ?></td>
   </tr>
   <tr><td colspan="2">&nbsp;</td></tr>

   <tr>
    <td><b><?php echo $tickertype_w; ?>:</b></td>
    <td>
     <select name="wptic_type" id="wptic_type" size="1" style="width:110px;" onchange="change_modules(this)">
     <?php echo $modules; ?>
     </select>
     <span id="hint_box"><?php echo $first_modul; ?></span>
    </td>
   </tr>
   <tr><td><b><?php echo $tickermaxitems_w; ?>:</b></td><td> <input type="text" name="wptic_itemcount" value="5" style="width:60px;" /> (<?php echo $tickermaxitems_info_w; ?>)</td></tr>
   <tr><td><b><?php echo $tickermaxchars_w; ?>:</b></td><td> <input type="text" name="wptic_charcount" value="70" style="width:60px;" /> (<?php echo $tickermaxchars_info_w; ?>)</td></tr>

   <tr><td style="vertical-align: top;"><b><?php echo $template_w; ?>:</b></td><td style="vertical-align: top;"><textarea name="wptic_template" id="wptic_template" style="float:left;"><?php echo $template; ?></textarea><span id="var_masks">&nbsp;%tic_date% - &nbsp; &nbsp; &nbsp;<?php echo $template_date_w; ?><br />&nbsp;%tic_time% - &nbsp; &nbsp; &nbsp;<?php echo $template_time_w; ?><br />&nbsp;%tic_title% - &nbsp; &nbsp; &nbsp; <?php echo $template_head_w; ?><br />&nbsp;%tic_content% - <?php echo $template_content_w; ?></span></td></tr>
   <tr><td style="vertical-align: top;"><b><?php echo $memo_w; ?>:</b></td><td style="vertical-align: top;"><textarea name="wptic_memo" id="wptic_memo" style="float:left;"></textarea><?php echo $memo_hinweis_w; ?></td></tr>

  </table>

  <p class="submit">
  <input type="submit" name="Submit" value="<?php echo $speichern_w; ?>" />
  <input type="button" name="abbruch" value="<?php echo $abbruch_w; ?>" onclick="show_hide_ticker_admin('')" />
  </p>
  <input type="hidden" name="wptic_aktion" value="insert" />
  </form>


  <form name="tictableform" id="tictableform" action="#" style="margin-top:30px;">
  <div style="margin-bottom:10px;"><h3 style="display:inline;"><?php echo $ticker_head_w; ?></h3><a href="javascript:return false;" onclick="show_hide_ticker_admin('neu')" style="margin-left:10px;"><?php echo $ticker_head_neu_w; ?></a></div>

  <?php echo $ticker_tabelle; ?>
  </form>
  <br />
  <hr />

  <?php if(is_multisite()) { ?>
    <h3 style="display:inline;"><?php echo $hinweis_w; ?>:</h3><br />
    <b>MU-Prefix:</b> <input type="text" value="<?php echo $wpdb->prefix; ?>" /><br />


  <hr />
  <?php } ?>
  <br />
  <div style="margin-right:5px;float:left;"><?php echo $fu�note_w; ?></div><a href="https://twitter.com/SteGaSoft" target="_blank" style="text-decoration:none;"><img src="<?php echo $wptic_plugin_dir."/images/twitter-icon.png"; ?>" style="width:18px;height:18px;" alt="SteGaSoft Twitter" title="SteGaSoft Twitter" /></a>
  <div style="clear:left">&nbsp;</div>
  <div style="margin:7px 5px 0 0;float:left;"><?php echo $spende_w; ?></div><?php echo $spenden_button; ?>
  <div style="clear:left">&nbsp;</div>


  </div>

  <script type="text/javascript">


  //===== CSS edit funktionen =====
  function edit_css() {

   <?php
    $write_msg = "";
    if (is_multisite()) {
      if(file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR ."styles/".$wpdb->prefix."_style.css")) {
        if (!is_writable(dirname(__FILE__) . DIRECTORY_SEPARATOR ."styles/".$wpdb->prefix."_style.css")) {
          $write_msg = str_replace("%datei%",$wpdb->prefix."_style.css",$edit_css_permission);

          /*
          if(confirm("<?php echo str_replace("%datei%",$wpdb->prefix."_style.css",$css_chmod_frage); ?>")) {
            jQuery.post("<?php echo plugins_url() ."/wp-ticker/tic-functions.php"; ?>",{aktion: "chomd_css", content: "<?php echo $wpdb->prefix."_style.css"; ?>", cert: "<?php echo session_id(); ?>"}, function(data) {
               alert(data);
             });

          }
          */
        }
      }
      else if(file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR ."styles/")) {
        if (!is_writable(dirname(__FILE__) . DIRECTORY_SEPARATOR ."styles/")) {
          $write_msg = $edit_css_dir_permission;

          /*
          if(confirm("<?php echo str_replace("%datei%","styles",$css_chmod_frage); ?>")) {
            jQuery.post("<?php echo plugins_url() ."/wp-ticker/tic-functions.php"; ?>",{aktion: "chomd_css", content: "styles", cert: "<?php echo session_id(); ?>"}, function(data) {
               alert(data);
             });

          }
          */

        }
      }
    }
    else {
      if (!is_writable(dirname(__FILE__) . DIRECTORY_SEPARATOR ."style.css")) {
        $write_msg = str_replace("%datei%","style.css",$edit_css_permission);

        /*
        if(confirm("<?php echo str_replace("%datei%","style.css",$css_chmod_frage); ?>")) {
          jQuery.post("<?php echo plugins_url() ."/wp-ticker/tic-functions.php"; ?>",{aktion: "chomd_css", content: "style.css", cert: "<?php echo session_id(); ?>"}, function(data) {
             alert(data);
           });

        }
        */

      }

    }


   ?>


    var fancy_code = "<b><?php echo $edit_css_texthinweis; ?>:<\/b><br />"+
                     "<div id='css_content' style='width:590px; height:600px;'><\/div>"+
                     "<input type='button' value='<?php echo $speichern_w; ?>' onclick='save_css()' style='margin-right:10px;' />"+
                     "<input type='button' value='<?php echo $abbruch_w; ?>' onclick='close_fancy()' />"+
                     " <span style='color:#6F0000;'><?php echo $write_msg; ?></span>";


    jQuery.fancybox(
                fancy_code,
                {
                        'autoDimensions'        : false,
                        'width'                         : 600,
                        'height'                        : 'auto',
                        'transitionIn'                : 'none',
                        'transitionOut'                : 'none',
                }
    );

   jQuery.post("<?php echo plugins_url() ."/wp-ticker/tic-functions.php"; ?>",{aktion: "get_css", cert: "<?php echo session_id(); ?>"}, function(data) {
        jQuery('#css_content').html(data);
   });


  }

  function save_css() {
    jQuery.post("<?php echo plugins_url() ."/wp-ticker/tic-functions.php"; ?>",{aktion: "save_css",content: jQuery("#css_edit_content").val(), cert: "<?php echo session_id(); ?>"}, function(data) {
        jQuery('#css_content').html(data);
   });

  }

  //===== Modul-Import =====
  function import_module() {
    var fancy_code = "<b><?php echo $import_modul_texthinweis; ?>:<\/b><br />"+
                     "<iframe src='<?php echo plugins_url() ."/wp-ticker/tic-functions.php?aktion=get_modulform?cert=".session_id(); ?>' id='modulframe' style='border:none;'><\/iframe>";

    jQuery.fancybox(
                fancy_code,
                {
                        'autoDimensions'        : false,
                        'width'                         : 300,
                        'height'                        : 'auto',
                        'transitionIn'                : 'none',
                        'transitionOut'                : 'none',
                }
    );


  }



  //===== ticker edit funktion =====
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
      if(data!="") {
        var cat_arr = data.split(",");
        for (var i=0;i<cat_arr.length;i++) {
            document.form1.elements["wptic_cat["+cat_arr[i]+"]"].checked = true;
        }
      }

      document.getElementById('var_masks').innerHTML = '&nbsp;%tic_date% - &nbsp; &nbsp; &nbsp;<?php echo $template_date_w; ?><br />&nbsp;%tic_time% - &nbsp; &nbsp; &nbsp;<?php echo $template_time_w; ?><br />&nbsp;%tic_title% - &nbsp; &nbsp; &nbsp; <?php echo $template_head_w; ?><br />&nbsp;%tic_content% - <?php echo $template_content_w; ?>';

    }


    if(document.forms["tictableform"].elements[u_src].value=="own") {
      document.form1.wptic_src.selectedIndex = 1;
      document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_own; ?>:';

      document.getElementById('data_context').innerHTML = '<div id="wptic_data" class="wptic_data_basic"><\/div>'+
                                                          '<div id="wptic_datamenu" class="wptic_data_basic">'+
                                                           '<input type="button" value="<?php echo $own_ticker_neu_w; ?>" onclick="insert_own_tictext('+id+')" style="margin:4px 4px 4px 4px;" />'+
                                                          '<\/div>';
      jQuery.post("<?php echo plugins_url() ."/wp-ticker/get_own_content.php"; ?>",{ticker_id: id, aktion: "get_tickers", cert:"<?php echo session_id(); ?>"}, function(data) {
        jQuery('#wptic_data').html(data);
      });
      document.getElementById('var_masks').innerHTML = '';

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
      document.getElementById('var_masks').innerHTML = '&nbsp;%tic_date% - &nbsp; &nbsp; &nbsp;<?php echo $template_date_w; ?><br />&nbsp;%tic_time% - &nbsp; &nbsp; &nbsp;<?php echo $template_time_w; ?><br />&nbsp;%tic_title% - &nbsp; &nbsp; &nbsp; <?php echo $template_head_w; ?><br />&nbsp;%tic_content% - <?php echo $template_content_w; ?>';

    }

    if(document.forms["tictableform"].elements[u_src].value=="com") {
      document.form1.wptic_src.selectedIndex = 3;
      document.getElementById('data_txt').innerHTML ='';
      document.getElementById('data_context').innerHTML = '';
      document.getElementById('var_masks').innerHTML = '&nbsp;%author_url% - <?php echo $template_author_url_w; ?><br />&nbsp;%author% - &nbsp; &nbsp; &nbsp; &nbsp;<?php echo $template_author_w; ?><br />&nbsp;%date% - &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<?php echo $template_com_date_w; ?><br />&nbsp;%time% - &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<?php echo $template_com_time_w; ?><br />&nbsp;%post% - &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<?php echo $template_com_post_w; ?><br />&nbsp;%content% - &nbsp; &nbsp; &nbsp; <?php echo $template_comment_w; ?><br />&nbsp;%avatar% - &nbsp; &nbsp; &nbsp; &nbsp; Avatar';
    }

    show_hide_ticker_admin(document.forms["tictableform"].elements["u_random_"+id].value);

    document.form1.wptic_showtime.value = document.forms["tictableform"].elements["u_showtime_"+id].value;
    document.form1.wptic_intime.value = document.forms["tictableform"].elements["u_intime_"+id].value;
    document.form1.wptic_outtime.value = document.forms["tictableform"].elements["u_outtime_"+id].value;
    document.form1.wptic_reloadtime.value = document.forms["tictableform"].elements["u_reloadtime_"+id].value;
    document.form1.wptic_reloadpausetime.value = document.forms["tictableform"].elements["u_reloadpausetime_"+id].value;
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

    add_sorting_option("");

    switch(obj.value) {
         case "db": document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_db; ?>:';
                    document.getElementById('data_context').innerHTML ='<?php echo $cat_items; ?>';
                    document.getElementById('var_masks').innerHTML = '&nbsp;%tic_date% - &nbsp; &nbsp; &nbsp;<?php echo $template_date_w; ?><br />&nbsp;%tic_time% - &nbsp; &nbsp; &nbsp;<?php echo $template_time_w; ?><br />&nbsp;%tic_title% - &nbsp; &nbsp; &nbsp; <?php echo $template_head_w; ?><br />&nbsp;%tic_content% - <?php echo $template_content_w; ?>';
                    document.getElementById('wptic_template').value = "%tic_date%<br />\n%tic_title%<br />\n%tic_content%";
                    break;
         case "own": document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_own; ?>:';
                     document.getElementById('data_context').innerHTML = '<div id="wptic_data" class="wptic_data_basic"><\/div>'+
                                                                         '<div id="wptic_datamenu" class="wptic_data_basic">'+
                                                                          '<input type="button" value="<?php echo $own_ticker_neu_w; ?>" onclick="insert_own_tictext(<?php echo $last_id; ?>)" style="margin:4px 4px 4px 4px;" />'+
                                                                         '<\/div>';

                     jQuery.post("<?php echo plugins_url() ."/wp-ticker/get_own_content.php"; ?>",{ticker_id: <?php echo $last_id; ?>, aktion: "get_tickers", cert:"<?php echo session_id(); ?>"}, function(data) {
                       jQuery('#wptic_data').html(data);
                     });
                     document.getElementById('var_masks').innerHTML = '';
                     document.getElementById('wptic_template').value = "";
                     break;
         case "rss": document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_rss; ?>:';
                     document.getElementById('data_context').innerHTML = '<textarea name="wptic_data" style="width:400px;height:170px;"><\/textarea>';
                     document.getElementById('var_masks').innerHTML = '&nbsp;%tic_date% - &nbsp; &nbsp; &nbsp;<?php echo $template_date_w; ?><br />&nbsp;%tic_time% - &nbsp; &nbsp; &nbsp;<?php echo $template_time_w; ?><br />&nbsp;%tic_title% - &nbsp; &nbsp; &nbsp; <?php echo $template_head_w; ?><br />&nbsp;%tic_content% - <?php echo $template_content_w; ?>';
                     document.getElementById('wptic_template').value = "%tic_date%<br />\n%tic_title%<br />\n%tic_content%";
                     break;
         case "com": document.getElementById('data_txt').innerHTML ='';
                     document.getElementById('data_context').innerHTML = '';
                     document.getElementById('var_masks').innerHTML = '&nbsp;%author_url% - <?php echo $template_author_url_w; ?><br />&nbsp;%author% - &nbsp; &nbsp; &nbsp; &nbsp;<?php echo $template_author_w; ?><br />&nbsp;%date% - &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<?php echo $template_com_date_w; ?><br />&nbsp;%time% - &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<?php echo $template_com_time_w; ?><br />&nbsp;%post% - &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;<?php echo $template_com_post_w; ?><br />&nbsp;%content% - &nbsp; &nbsp; &nbsp;<?php echo $template_comment_w; ?><br />&nbsp;%avatar% - &nbsp; &nbsp; &nbsp; &nbsp; Avatar';
                     document.getElementById('wptic_template').value = "%author% am %date% um %time%<br />\n%content%";
                     break;
         default: document.getElementById('data_txt').innerHTML ='<?php echo $data_txt_cat; ?>:';
                  document.getElementById('data_context').innerHTML = '<?php echo $cat_items; ?>';
                  document.getElementById('var_masks').innerHTML = '';
                  document.getElementById('wptic_template').value = "";
                  break;
    }
  }

  function str_replace(search, replace, subject) {
    return subject.split(search).join(replace);
  }


  //===== Funktionen fuer eigenen Ticker-Text =====
  function insert_own_tictext(id) {

    <?php
      $tag = "<option value='00'>$tag_w</option>";
      for ($i=1; $i<32;$i++) {
        if($i<10)
          $tagwert = "0".$i;
        else
          $tagwert = $i;
        $tag .= "<option value='$tagwert'>$tagwert</option>";
      }

      $monat = "<option value='00'>$monat_w</option>";
      for ($i=1; $i<13;$i++) {
        if($i<10)
          $monatwert = "0".$i;
        else
          $monatwert = $i;
        $monat .= "<option value='$monatwert'>$monatwert</option>";
      }


      if( ($max_year < date("Y",time())) || (trim($max_year)=="") || (!is_numeric($max_year)))
        $max_year = date("Y",time());

      $jahr = "<option value='00'>$jahr_w</option>";
      for ($i=date("Y",time()); $i<=$max_year;$i++) {
        $jahr .= "<option value='$i'>$i</option>";
      }
    ?>

    var fancy_code = "<b><?php echo $own_ticker_texthinweis; ?>:<\/b><br />"+
                     "<textarea id='tickertext' style='width:390px; height:200px;'><\/textarea><br />"+
                     "<table class='widefat' style='width:390px;border:none;'>"+
                     "<tr><td style='width:100px;'><b><?php echo $own_ticker_startdata_w; ?>:<\/b><\/td><td><select id='startdate_d' class='fe_txt fe_date' size='1' ><?php echo $tag; ?><\/select><select id='startdate_m' class='fe_txt fe_date' size='1' ><?php echo $monat; ?><\/select><select id='startdate_j' class='fe_txt fe_date' size='1' ><?php echo $jahr; ?><\/select> <input type='button' value='<= <?php echo $heute_w; ?>' onclick='set_date_today(\"startdate\")'/><\/td><\/tr>"+
                     "<tr><td style='width:100px;'><b><?php echo $own_ticker_enddata_w; ?>:<\/b><\/td><td><select id='enddate_d' class='fe_txt fe_date' size='1' ><?php echo $tag; ?><\/select><select id='enddate_m' class='fe_txt fe_date' size='1' ><?php echo $monat; ?><\/select><select id='enddate_j' class='fe_txt fe_date' size='1' ><?php echo $jahr; ?><\/select> <input type='button' value='<= <?php echo $heute_w; ?>' onclick='set_date_today(\"enddate\")'/><\/td><\/tr>"+
                     "<tr><td style='width:100px;'><b><?php echo $own_ticker_autodel_w; ?>:<\/b><\/td><td><input type='checkbox' id='autodelete' value='j' /><\/td><\/tr>"+
                     "<\/table>"+
                     "<input type='button' value='<?php echo $speichern_w; ?>' onclick='insert_now("+id+")' style='margin-right:10px;' />"+
                     "<input type='button' value='<?php echo $abbruch_w; ?>' onclick='close_fancy()' />";


    jQuery.fancybox(
                fancy_code,
                {
                        'autoDimensions'        : false,
                        'width'                         : 400,
                        'height'                        : 'auto',
                        'transitionIn'                : 'none',
                        'transitionOut'                : 'none',
                }
    );

  }



  function close_fancy() {
     parent.jQuery.fancybox.close();
  }


  function insert_now(id) {

    var input_data = new Array()
        input_data[0] = jQuery("#tickertext").val();
        input_data[1] = jQuery("#startdate_j").val() + "-" + jQuery("#startdate_m").val() + "-" + jQuery("#startdate_d").val();
        input_data[2] = jQuery("#enddate_j").val() + "-" + jQuery("#enddate_m").val() + "-" + jQuery("#enddate_d").val();
        if(jQuery("#autodelete").attr('checked'))
          input_data[3] = "j";
        else
          input_data[3] = "n";

     close_fancy();

    jQuery.post("<?php echo plugins_url() ."/wp-ticker/get_own_content.php"; ?>",{
                                                                                   ticker_id: id,
                                                                                   aktion: "insert",
                                                                                   content: input_data[0],
                                                                                   startdate: input_data[1],
                                                                                   enddate: input_data[2],
                                                                                   autodelete: input_data[3],
                                                                                   cert: "<?php echo session_id(); ?>"
                                                                                  },
                                                                                  function(data) {
                                                                                    jQuery('#wptic_data').html(data);
                                                                                  }
    );

  }

  function set_date_today(id) {
    var sel_d = id + "_d";
    var sel_m = id + "_m";
    var sel_y = id + "_j";

    var a_day = <?php echo date("d",time()); ?>;
    var a_mon = <?php echo date("m",time()); ?>;
    var a_year = <?php echo date("Y",time()); ?>;

    var optionen;

    optionen=document.getElementById(sel_d).options;
    for(var i=0; i<optionen.length; i++) {
      if(optionen[i].value==a_day)
         optionen[i].setAttribute('selected','selected');
    }

    optionen=document.getElementById(sel_m).options;
    for(var i=0; i<optionen.length; i++) {
      if(optionen[i].value==a_mon)
         optionen[i].setAttribute('selected','selected');
    }

    optionen=document.getElementById(sel_y).options;
    for(var i=0; i<optionen.length; i++) {
      if(optionen[i].value==a_year)
         optionen[i].setAttribute('selected','selected');
    }

  }

  function edit_own_tictext(ed_id,tic_id) {
    jQuery.post("<?php echo plugins_url() ."/wp-ticker/get_own_content.php"; ?>",{ticker_id: tic_id,aktion: "edit", aktion_id: ed_id, cert: "<?php echo session_id(); ?>"}, function(data) {
      jQuery.fancybox(
                data,
                {
                        'autoDimensions'        : false,
                        'width'                         : 400,
                        'height'                        : 'auto',
                        'transitionIn'                : 'none',
                        'transitionOut'                : 'none',
                }
      );
    });

  }



  function update_own_tictext(ed_id,tic_id) {

    var input_data = new Array()
        input_data[0] = jQuery("#tickertext").val();
        input_data[1] = jQuery("#startdate_j").val() + "-" + jQuery("#startdate_m").val() + "-" + jQuery("#startdate_d").val();
        input_data[2] = jQuery("#enddate_j").val() + "-" + jQuery("#enddate_m").val() + "-" + jQuery("#enddate_d").val();
        if(jQuery("#autodelete").attr('checked'))
          input_data[3] = "j";
        else
          input_data[3] = "n";

    close_fancy();

    jQuery.post("<?php echo plugins_url() ."/wp-ticker/get_own_content.php"; ?>",{
                                                                                   ticker_id: tic_id,
                                                                                   aktion: "update",
                                                                                   content: input_data[0],
                                                                                   startdate: input_data[1],
                                                                                   enddate: input_data[2],
                                                                                   autodelete: input_data[3],
                                                                                   aktion_id: ed_id,
                                                                                   cert: "<?php echo session_id(); ?>"
                                                                                  },
                                                                                  function(data) {
                                                                                    jQuery('#wptic_data').html(data);
                                                                                  }
    );
  }



  function delete_own_tictext(del_id,tic_id) {
    if(confirm("<?php echo $own_ticker_delete_w; ?>"+del_id+"?")) {
      jQuery.post("<?php echo plugins_url() ."/wp-ticker/get_own_content.php"; ?>",{ticker_id: tic_id,aktion: "delete", aktion_id: del_id, cert: "<?php echo session_id(); ?>"}, function(data) {
        jQuery('#wptic_data').html(data);
      });
    }
  }


  function show_hide_ticker_admin(selektion) {
    jQuery("#form1").toggle();
    jQuery("#tictableform").toggle();

    if(selektion!="neu") {
      if (jQuery("#form1").is(':visible'))
        add_sorting_option(selektion);
    }
    else {
      set_new_ticker_params(<?php echo $last_id; ?>);
    }
  }


  function add_sorting_option(selektion) {
    var pre = "";

    if(selektion=="")
      pre = "<?php echo $tic_random_anz; ?>";
    else
      pre = selektion;

    jQuery.post("<?php echo plugins_url() ."/wp-ticker/tic-functions.php"; ?>",{aktion: "get_sortoptions",content: pre+"-"+jQuery('#wptic_src').val(), cert: "<?php echo session_id(); ?>"}, function(data) {
        jQuery('#tic_random').html(data);
    });

  }


  function set_new_ticker_params(neu_id) {
    document.form1.wptic_aktion.value="insert";

    document.form1.wptic_id.value=neu_id;
    document.getElementById('id_span').innerHTML=neu_id;

    jQuery("#wptic_src :selected").removeAttr("selected");
    jQuery.post("<?php echo plugins_url() ."/wp-ticker/tic-functions.php"; ?>",{aktion: "get_sortoptions",content: "-db", cert: "<?php echo session_id(); ?>"}, function(data) {
        jQuery('#tic_random').html(data);
    });

    jQuery("#data_txt").html('<?php echo $data_txt_db; ?>:');
    jQuery("#data_context").html('<?php echo $cat_items; ?>');

    document.form1.wptic_showtime.value = "3000";
    document.form1.wptic_intime.value = "1000";
    document.form1.wptic_outtime.value = "1000";
    document.form1.wptic_reloadtime.value = "0";
    document.form1.wptic_reloadpausetime.value = "0";

    jQuery("#wptic_type :selected").removeAttr("selected");

    document.form1.wptic_itemcount.value = "5";
    document.form1.wptic_charcount.value = "70";

    jQuery("#wptic_template").val("%tic_date%<br />\n%tic_title%<br />\n%tic_content%");
    jQuery("#var_masks").html('&nbsp;%tic_date% - &nbsp; &nbsp; &nbsp;<?php echo $template_date_w; ?><br />&nbsp;%tic_time% - &nbsp; &nbsp; &nbsp;<?php echo $template_time_w; ?><br />&nbsp;%tic_title% - &nbsp; &nbsp; &nbsp; <?php echo $template_head_w; ?><br />&nbsp;%tic_content% - <?php echo $template_content_w; ?>');

    jQuery("#wptic_memo").val("");

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



  <?php
}


//===== WP-Ticker-Widget =====
@include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR ."wp-ticker-widget.php");


?>