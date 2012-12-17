<?php
//===== globale Konstante - editierbar =====
$more_tag = " ...[mehr]";  // Anzeige bei gekürztem Text
$show_length = 200;        // Länge des gekürzten Textes bei Listenanzeige von "eigener Text"
$max_year = 2020;          // End-Jahr bei Datums-Auswahl in Fancybox
$loader = '<img src="'.$wptic_plugin_dir.'/images/loader.gif" border="0" class="loader" id="loader_'.$id.'" \/>';







/*=======================================================================================================
====================== globale Funktionen - hier nichts ändern! =========================================
=======================================================================================================*/

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