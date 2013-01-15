<?php
//===== globale Konstanten - editierbar =====
$more_tag = " ...[mehr]";  // Anzeige bei gekürztem Text
$show_length = 200;        // Länge des gekürzten Textes bei Listenanzeige von "eigener Text"
$max_year = 2020;          // End-Jahr bei Datums-Auswahl in Fancybox
$date_format = NULL;       // Datums-Format nach PHP-Regeln z. B. "d.m.Y" => nur bei Kommentaren
$time_format = NULL;       // Zeit-Format nach PHP-Regeln z. B. "H:i"  => nur bei Kommentaren
$avatar_size = 50;         // Größe der Avatare => nur bei Kommentaren
$use_session = true;       // Session-ID zum Speichern der CSS-Dateien nutzen (Sicherheit)
$loader = '<img src="'.$wptic_plugin_dir.'/images/loader.gif" border="0" class="loader" id="loader_'.$id.'" \/>';




/*=======================================================================================================
====================== globale Funktionen - hier nichts ändern! =========================================
=======================================================================================================*/


$sorting_arr = array();
$sorting_arr["db"] = array("Date ASC"=>"wposts.post_date ASC","Date DESC"=>"wposts.post_date DESC","Title ASC"=>"wposts.post_title ASC","Title DESC"=>"wposts.post_title DESC","Random"=>"RAND()");
$sorting_arr["own"] = array("ID ASC"=>"ID ASC","ID DESC"=>"ID DESC","Random"=>"RAND()");
$sorting_arr["rss"] = array("Standard"=>"false","Random"=>"true");
$sorting_arr["com"] = array("Date ASC"=>"comments.comment_date ASC","Date DESC"=>"comments.comment_date DESC","Title ASC"=>"posts.post_title ASC","Title DESC"=>"posts.post_title DESC","Author ASC"=>"comments.comment_author ASC","Author DESC"=>"comments.comment_author DESC","Random"=>"RAND()");


function word_substr($text, $zeichen, $kolanz=3, $punkte=3) {
    if(strlen($text) < $zeichen+$kolanz)
        return $text;
    $wort = explode(" ",$text);
    $newstr = "";
    $i = 0;
    while(strlen($newstr)<=$zeichen &&
          strlen($newstr.$wort[$i])<=($zeichen+$kolanz)) {
        $newstr .= $wort[$i]." ";
        $i++;
    }
    $newstr .= str_repeat(".",$punkte);
    return $newstr;
}

function wptic_get_loader_txt() {
  global $loader,$wptic_plugin_dir;

  $loader = '<img src="'.$wptic_plugin_dir.'/images/loader.gif" border="0" class="loader" id="loader_'.$id.'" \/>';


  return $loader;
}

?>