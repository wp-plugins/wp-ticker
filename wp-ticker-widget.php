<?php


function db_hilfsfunktion() {
  global $wpdb;
  return $wpdb;
}




function wp_tickerwid_init() {

  // check for the required WP functions, die silently for pre-2.2 WP.
  if ( !function_exists('wp_register_sidebar_widget') )
    return;


  //===== Ticker Frontend =====
  function wp_tickerwid_fe($args) {       //wet_bmicalc($args)

    $wpdb = db_hilfsfunktion();
    $loader =  wptic_get_loader_txt();

    extract($args);

    $wptic_options = get_option( "wptic_options" );
    $title = $wptic_options['wptic_wid-title'];
    $ticker_id = $wptic_options['wptic_wid-ticid'];



    $befehl = "SELECT Optionen,Daten,Template,Typ FROM ".$wpdb->prefix ."wp_ticker WHERE ID=$ticker_id";
    $ticdaten = $wpdb->get_results($befehl);

    foreach ($ticdaten as $ticdat) {
      $optionen = unserialize($ticdat->Optionen); //Array()
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

    $plugin_url = plugins_url();


    echo $before_widget . $before_title . $title . $after_title;
    echo '<!-- WP-Ticker-Content Begin -->'."\n".
         '<div id="wptic_code_'.$ticker_id.'" ></div>'."\n";

    $code = "";
    $code = 'jQuery.post("'.$plugin_url.'/wp-ticker/get_ticker_code.php",{ ticker_id: '.$ticker_id.'}, function(data) {jQuery("#wptic_code_'.$ticker_id.'").html(data);});';
    if(is_numeric($interval_time) && $interval_time>0) {
      $code .= 'setInterval ( function () {'.
               'jQuery("#wptic_code_'.$ticker_id.'").html(\'<div class="ticker_content" id="ticker_content_'.$ticker_id.'">'.$loader.'<\/div>'.decode_tcpr_wp($aus).'\');';
      if(is_numeric($pause_time) && $pause_time>0)
         $code .= 'setTimeout(\'jQuery.post("'.$plugin_url.'/wp-ticker/get_ticker_code.php",{ ticker_id: '.$ticker_id.'}, function(data) {jQuery("#wptic_code_'.$ticker_id.'").html(data);});\','.$pause_time.');';
      else
         $code .= 'jQuery.post("'.$plugin_url.'/wp-ticker/get_ticker_code.php",{ ticker_id: '.$ticker_id.'}, function(data) {jQuery("#wptic_code_'.$ticker_id.'").html(data);});';

      $code .= '},'.$interval_time.');';
    }



echo <<<JS
    <script type="text/javascript">
    $code;
    </script>
    <!-- WP-Ticker-Content END -->
JS;


    echo $after_widget;
  }



  //===== Ticker Backend =====
  function wp_tickerwid_be() {          //wet_bmicalc_control

    $wpdb = db_hilfsfunktion();

    $wptic_options = get_option( "wptic_options" );
    if ( !is_array($wptic_options) )
      $wptic_options = array('wptic_wid-title'=>'Ticker');

    if ( $_POST['wp_tic_wid-submit'] ) {
      $wptic_options['wptic_wid-title'] = strip_tags(stripslashes($_POST['wp_tic_wid-title']));
      $wptic_options['wptic_wid-ticid'] = $_POST['wptic_wid-ticid'];
      update_option( "wptic_options", $wptic_options );
    }

    $title = htmlspecialchars($wptic_options['wptic_wid-title'], ENT_QUOTES);
    $sel_ticid = $wptic_options['wptic_wid-ticid'];

    $ticker_liste = "";

    $befehl = "SELECT ID,Memo FROM ".$wpdb->prefix ."wp_ticker ORDER BY ID ASC";
    $ticdaten = $wpdb->get_results($befehl);

    foreach ($ticdaten as $ticdat) {

      $memo = word_substr($ticdat->Memo, 30, 3, 3);

      if($sel_ticid==$ticdat->ID)
        $ticker_liste .= '<option value="'.$ticdat->ID.'" selected>'.$ticdat->ID.': '.$memo.'</option>';
      else
        $ticker_liste .= '<option value="'.$ticdat->ID.'">'.$ticdat->ID.': '.$memo.'</option>';
    } //foreach


    echo '<p style="text-align:right;">'.
         '<label for="wp_tic_wid-title">'.get_widget_txt("ticker_widget_title_w").':'.
         ' <input style="width: 200px;" id="wp_tic_wid-title" name="wp_tic_wid-title" type="text" value="'.$title.'" /></label></p>';
    echo '<p style="text-align:right;">'.
         '<label for="wptic_wid-ticid">Ticker:'.
         ' <select style="width: 200px;" size="1" id="wptic_wid-ticid" name="wptic_wid-ticid" >'.
         $ticker_liste.
         '</select></label></p>';

    echo '<input type="hidden" name="wp_tic_wid-submit" id="wp_tic_wid-submit" value="1" />';

  }


  //===== Widget in WP einbinden =====
  wp_register_sidebar_widget('wp_tickerwid_fe',
                             'WP-Ticker',
                             'wp_tickerwid_fe',
                              array( 'classname' => 'wp_tickerwid_fe',
                                      'description' =>get_widget_txt("ticker_widget_describe_w")

                              )
  );



  //===== Widget im WP Backend einbinden =====
  wp_register_widget_control('wp_tickerwid_fe',
                             'WP-Ticker',
                             'wp_tickerwid_be',
                             array('width' => 300)
  );
}
add_action('widgets_init', 'wp_tickerwid_init');


function get_widget_txt($txtitem) {

  $lang = get_bloginfo("language");
  $lang = str_replace("-","_",$lang);


  if (empty($lang) || trim($lang)=="") {
    $lang = 'de_DE';
  }

  switch($lang) {
    case "de_DE": $text_array = array(
                  "ticker_widget_title_w"=>"Titel",
                  "ticker_widget_describe_w"=>"WP-Ticker f&uuml;r die Sidebar."
                  );
                  break;

    case "en_EN": $text_array = array(
                  "ticker_widget_title_w"=>"Title",
                  "ticker_widget_describe_w"=>"Easy including WP-Ticker into your sidebar."
                  );
                  break;
    default     : $text_array = array(
                  "ticker_widget_title_w"=>"Title",
                  "ticker_widget_describe_w"=>"Easy including WP-Ticker into your sidebar."
                  );
                  break;

  }//switch

  return $text_array[$txtitem];

}


?>