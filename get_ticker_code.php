<?php

@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR. "wp-config.php");
@include_once (dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR."wp-includes/wp-db.php");
@include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR ."tic-global.php");

define('WPTIC_URLPATH', WP_CONTENT_URL.'/plugins/'.plugin_basename( dirname(__FILE__)) );
$wptic_plugin_dir = WPTIC_URLPATH;


$lang = get_bloginfo("language");
$lang = str_replace("-","_",$lang);

if (empty($lang) || trim($lang)=="") {
  $lang = 'en_EN';
}

if(!@include_once dirname(__FILE__) . DIRECTORY_SEPARATOR ."lang/".$lang.".php")
  include_once dirname(__FILE__) . DIRECTORY_SEPARATOR ."lang/en_EN.php";

$id = $_POST['ticker_id'];

if(!is_numeric($id))
  exit;



//===== Daten zu Ticker-ID auslesen =====
$befehl = "SELECT Optionen,Daten,Template,Typ FROM ".$wpdb->prefix ."wp_ticker WHERE ID=$id";
$ticdaten = $wpdb->get_results($befehl);

foreach ($ticdaten as $ticdat) {
  $optionen = unserialize($ticdat->Optionen); //Array()
  $daten = $ticdat->Daten;
  $type = $ticdat->Typ;
  $template = $ticdat->Template;
}


$template = stripslashes($template);

$code = "\n".'<div class="ticker_content" id="ticker_content_'.$id.'" onmouseover="jTickerEnd'.$type.'('.$id.')" onmouseout="jTickerStart'.$type.'('.$id.')">'."\n";

if($optionen['src']=="db")
  $code .= wptic_get_dbdata($optionen['itemcount'],$daten,$optionen['charcount'],$template,$optionen['tic_random'],$id);
else if($optionen['src']=="own")
  $code .= wptic_get_owndata($id,$optionen['tic_random']);
else if($optionen['src']=="rss")
  $code .= wptic_get_rssdata($optionen['itemcount'],$daten,$optionen['charcount'],$template,$optionen['tic_random'],$id);
else if($optionen['src']=="com")
  $code .= wptic_get_coments($optionen['itemcount'],$optionen['charcount'],$optionen['tic_random'],$id);

$tcpr = decode_tcpr($aus);

$code .= '</div>'."\n".$tcpr.
         '<script type="text/javascript">'.
         'show_time['.$id.'] = '.$optionen['showtime'].';'.
         'out_time['.$id.'] = '.$optionen['outtime'].';'.
         'in_time['.$id.'] = '.$optionen['intime'].';'.
         'fade_timer['.$id.'];'.
         'jTickerStart'.$type.'('.$id.');'.
         '</script>'."\n";


//===== DATEN AUS EIGENEM TEXT ================================
function wptic_get_owndata($ticker_id,$random_sort='ID ASC') {
  global $wpdb;


  $heute = date("Y-m-d",time());

  $zusatz = "";

  if(trim($random_sort)=="")
    $zusatz = " ORDER BY ID ASC";
  else
    $zusatz = " ORDER BY $random_sort";

  $befehl = "SELECT ID,Ticker_ID,Daten,Zeige_Start,Zeige_Ende,Auto_Delete FROM ".$wpdb->prefix ."wp_ticker_content WHERE Ticker_ID=$ticker_id AND Zeige_Start<='$heute' AND (Zeige_Ende>'$heute' OR Zeige_Ende='0000-00-00')".$zusatz;
  $ticdaten = $wpdb->get_results($befehl);

  $output = "";
  $k=0;
  foreach ($ticdaten as $ticdat) {
    if($k==0)
      $anfang = '<div class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';
    else
      $anfang = '<div style="display:none;" class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';
    $output .= $anfang.stripslashes($ticdat->Daten).'</div>';
    $k++;
  }

  return $output;
}



//===== DATEN AUS RSS_FEEDS ====================================
function wptic_get_rssdata($no_posts, $urls, $maxchar,$template,$random_sort=false, $ticker_id) {
  global $more_tag;

  $url_array = explode("\r\n",$urls);

  if($random_sort)
    shuffle($url_array);

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
        $anfang = '<div class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';
      else
        $anfang = '<div style="display:none;" class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';

      $link = trim($items[1]);
      if($link=="")
        $link = "#";
      $item_head = '<a href="'.$link.'" target="_blank">'.$items[0].'</a>';

      $content = wptic_shrink_data($items[2],$maxchar);

      $datum_zeit = $items[3];
      $datum = explode("-",$datum_zeit[0]);
      $zeit = explode(":",$datum_zeit[1]);
      $itemtimestamp = mktime((int)$zeit[0],(int)$zeit[1],(int)$zeit[2],(int)$datum[1],(int)$datum[2],(int)$datum[0]);


      $datum = date(get_option('date_format'),$itemtimestamp);
      $zeit = date("H:i",$itemtimestamp);

      $template_stack = str_replace("%tic_title%",$headline.$item_head,$template);
      if(trim($content)!="")
        $template_stack = str_replace("%tic_content%",$content,$template_stack);
      else
        $template_stack = str_replace("%tic_content%","",$template_stack);

      $template_stack = str_replace("%tic_date%",$datum,$template_stack);
      $template_stack = str_replace("%tic_time%",$zeit,$template_stack);

      $template_stack = str_replace("<-ticend->",'<a href="'.$link.'" target="_blank">'.$more_tag.'</a>',$template_stack);

      $template_stack = trim($template_stack);

      $output .= $anfang.$template_stack.'</div>';
      $k++;
    }


  }

  return $output;
}



//===== DATEN AUS DB ============================================
function wptic_get_dbdata($no_posts, $catids = 1, $maxchar,$template,$random_sort="wposts.post_date DESC", $ticker_id) {
  global $wpdb,$more_tag;

  if(trim($no_posts)!="")
    $limit = " LIMIT $no_posts";
  else
    $limit = "";

  $output = '';

  $catid_arr = explode(",",$catids);

  $k=0;
  foreach($catid_arr as $catid) {

    if(trim($random_sort)=="")
      $sort_zusatz = "wposts.post_date DESC";
    else
      $sort_zusatz = $random_sort;

    $request = "SELECT DISTINCT wposts.* FROM $wpdb->posts wposts LEFT JOIN $wpdb->postmeta wpostmeta ON wposts.ID = wpostmeta.post_id LEFT JOIN $wpdb->term_relationships ON (wposts.ID = $wpdb->term_relationships.object_id) LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) WHERE $wpdb->term_taxonomy.taxonomy = 'category' AND $wpdb->term_taxonomy.term_id ='$catid' AND wposts.post_status='publish' AND wposts.post_type='post' ORDER BY ".$sort_zusatz.$limit;
    $posts = $wpdb->get_results($request);

    if($posts) {

      foreach ($posts as $post) {
        $post_title = stripslashes($post->post_title);
        $post_time = get_the_time("", $post->ID );
        $post_date = get_the_time(get_option('date_format'), $post->ID );
        $permalink = get_permalink($post->ID);
        $post_content = stripslashes($post->post_content);

        if($maxchar!="")
          $post_content = wptic_shrink_data($post_content,$maxchar);

        $post_content = str_replace("<-ticend->", '<a href="' . $permalink . '" rel="bookmark" title="Permanent Link: ' . htmlspecialchars($post_title, ENT_COMPAT) . '">'.$more_tag.'</a>',$post_content);

        if($k==0)
          $anfang = '<div class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';
        else
          $anfang = '<div style="display:none;" class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';

        $template_stack = str_replace("%tic_title%",'<a href="' . $permalink . '" rel="bookmark" title="Permanent Link: ' . htmlspecialchars($post_title, ENT_COMPAT) . '">' . $post_title . '</a>',$template);
        $template_stack = str_replace("%tic_content%",$post_content,$template_stack);
        $template_stack = str_replace("%tic_date%",$post_date,$template_stack);
        $template_stack = str_replace("%tic_time%",$post_time,$template_stack);

        $output .= $anfang.$template_stack.'</div>';
        $template_stack = "";
        $k++;
      }
    }
    else {
      if($k==0)
        $anfang = '<div class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';
      else
        $anfang = '<div style="display:none;" class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';
      $output .= $anfang.'NO POST FOR CAT-ID '. $catid.'</div>';
      $k++;
    }
  }


  return $output;
}


//===== DATEN AUS KOMMENTAREN ================================
function wptic_get_coments($no_posts, $maxchar,$random_sort='comments.comment_date DESC', $ticker_id) {
  global $wpdb,$date_format,$time_format,$template,$avatar_size;

  $output = "";

  if(trim($no_posts)!="")
    $limit = " LIMIT $no_posts";
  else
    $limit = "";

  if(trim($random_sort)=="") {
    $sort_zusatz = "$wpdb->comments.comment_date DESC";
  }
  else if($random_sort=="RAND()") {
    $sort_zusatz = "RAND()";
  }
  else
    $sort_zusatz = "{$wpdb->prefix}".$random_sort;

  $comments_query="SELECT $wpdb->comments.comment_author, $wpdb->comments.comment_author_url,$wpdb->comments.comment_author_email,$wpdb->comments.comment_date,$wpdb->comments.comment_content,$wpdb->posts.post_title,$wpdb->posts.guid FROM $wpdb->comments,$wpdb->posts
                         WHERE $wpdb->comments.comment_post_ID=$wpdb->posts.ID
                         AND $wpdb->comments.comment_approved='1'
                         AND $wpdb->comments.comment_author_url NOT LIKE '".get_bloginfo( 'url', 'raw' )."%'
                         AND comment_author !=''
                         AND $wpdb->comments.comment_type NOT LIKE 'pingback' OR 'trackback'
                         ORDER BY ".$sort_zusatz.$limit;


  $comments_result = $wpdb->get_results($comments_query);

  $k=0;
  foreach ($comments_result as $comment) {

    $kontent = "";
    $c_autor = $comment->comment_author;
    $c_autorurl = $comment->comment_author_url;
    $c_autormail = $comment->comment_author_email;
    $c_date_time = $comment->comment_date;
    $c_content = $comment->comment_content;
    $c_post = $comment->post_title;
    $c_posturl = $comment->guid;

    $c_date_time = explode(" ",$c_date_time);
    $c_date = explode("-",$c_date_time[0]);
    $c_time = explode(":",$c_date_time[1]);

    $date_time_stamp = mktime($c_time[0], $c_time[1], $c_time[2], $c_date[1], $c_date[2], $c_date[0]);
    if(isset($date_format))
      $c_date = date($date_format,$date_time_stamp);
    else
      $c_date = date(get_option('date_format'),$date_time_stamp);
    if(isset($time_format))
      $c_time = date($time_format,$date_time_stamp);
    else
      $c_time = date(get_option('time_format'),$date_time_stamp);

    if($maxchar>0 && $maxchar!="") {
      $c_content = wptic_shrink_data($c_content,$maxchar);
      $c_content = str_replace("<-ticend->"," ...",$c_content);
    }

    if(trim($avatar_size)=="" || !is_numeric($avatar_size))
      $avatar_size = 60;
    $kom_avatar = get_avatar($c_autormail,$avatar_size);

    $kontent = $template;
    $kontent = str_replace("%author%",$c_autor,$kontent);
    $kontent = str_replace("%author_url%",$c_autorurl,$kontent);
    $kontent = str_replace("%avatar%",$kom_avatar,$kontent);
    $kontent = str_replace("%date%",$c_date,$kontent);
    $kontent = str_replace("%time%",$c_time,$kontent);
    $kontent = str_replace("%content%",$c_content,$kontent);
    $kontent = str_replace("%post%",$c_post,$kontent);
    $kontent = str_replace("%post_url%",$c_posturl,$kontent);

    if($k==0)
      $anfang = '<div class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';
    else
      $anfang = '<div style="display:none;" class="ticker_item" id="ticker_item_'.$ticker_id.'_'.($k+1).'">';

    $output .= $anfang.$kontent."</div>";

    $k++;

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

function decode_tcpr($do=true) {
  if($do)
    $out = "";
  else
    $out = "";
  return $out;
}


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

    $rss_data_array[1] = $channellink[1];

    if($encode != "no") {
      $rss_data_array[0] = htmlentities($channeltitle[1],ENT_QUOTES,$encoding);
    }
    else {
      $rss_data_array[0] = $channeltitle[1];
    }

  }

  // Titel, Link und Beschreibung der Items
  $k=0;
  if(is_array($items[1])) {
  foreach ($items[1] as $item) {
    preg_match("/<title.*>(.+)<\/title>/Uism", $item, $title);
    if($atom == 0) {
      preg_match("/<link>(.+)<\/link>/Uism", $item, $link);
      preg_match("/<pubDate>(.+)<\/pubDate>/Uism", $item, $datum);
      preg_match("/<description>(.*)<\/description>/Uism", $item, $description);
      $datum = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $datum);
      $datum = parse_feed_date("", $datum[0]);
    }
    else if($atom == 1) {
      preg_match("/<link.*alternate.*text\/html.*href=[\"\'](.+)[\"\'].*\/>/Uism", $item, $link);
      preg_match("/<updated>(.+)<\/updated>/Uism", $item, $datum);
      preg_match("/<summary.*>(.*)<\/summary>/Uism", $item, $description);
      $datum = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $datum);
      $datum = parse_feed_date("atom", $datum[0]);
    }


    $title = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $title);
    $description = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $description);
    $link = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $link);


    $rss_item_array[$k][1] = $link[1];

    if($encode != "no") {
      $rss_item_array[$k][0] = htmlentities($title[1],ENT_QUOTES,$encoding);
    }
    else {
      $rss_item_array[$k][0] = $title[1];
    }


    if($mode == 2 || $mode == 3 && ($description[1]!="" && $description[1]!=" ")) {
      if($encode != "no") {
        $rss_item_array[$k][2] = htmlentities($description[1],ENT_QUOTES,$encoding);
      }
      else {
        $rss_item_array[$k][2] = $description[1];
      }
      $rss_item_array[$k][3] = $datum;
    }
    if ($anzahl-- <= 1) break;
    $k++;
  }
  }

  $rss_data_array[2] = $rss_item_array;

  return $rss_data_array;
}


function parse_feed_date($type="", $datum) {
  $parsed_date = "";
  $stack = "";
  $stack_datum = "";
  $stack_zeit = "";
  $mon_array = array("Jan"=>"01","Feb"=>"02","Mar"=>"03","Apr"=>"04","May"=>"05","Jun"=>"06","Jul"=>"07","Aug"=>"08","Sep"=>"09","Oct"=>"10","Nov"=>"11","Dec"=>"12");

  if($type=="atom") {
    $parsed_date = explode ("T",$datum);
    $stack_datum = $parsed_date[0];
    $stack = explode("+",$parsed_date[1]);
    $stack_zeit = $stack[0];
    $parsed_date = array("$stack_datum","$stack_zeit");
  }
  else {
    $parsed_date = explode(" ",$datum);
    $stack_datum = $parsed_date[3]."-".$mon_array[$parsed_date[2]]."-".$parsed_date[1];
    $stack_zeit = $parsed_date[4];
    $parsed_date = array("$stack_datum","$stack_zeit");
  }
  return $parsed_date;
}



  echo $code;

?>