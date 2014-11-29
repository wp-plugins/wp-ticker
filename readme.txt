=== WP-Ticker ===
Contributors: sgt, Stephan Gaertner
Donate link: http://wp-ticker.stegasoft.de
Tags: news,ticker,newsticker,textticker,live ticker,text,fader,scroller,rss,atom,comments,multisite
Requires at least: 3.3
Tested up to: 4.0.1
Stable tag: 1.3.2.3


Auf jQuery basierender, Multisite kompatibler (Live-) Ticker, der mit verschiedenen Text-Effekten ausgestattet werden kann.

== Description ==

Wp-Ticker basiert auf jQuery und es ist moeglich, mehrere Ticker-Instanzen zu erstellen.
D. h. es koennen mehrere Ticker auf einer Seite/in einem Artikel oder z.B. in der Sidebar
und in einer Seite bzw. einem Artikel dargestellt werden.
Alle Ticker werden ueber eine zentrale Style-Datei formatiert (Groesse, Aussehen, etc.).
Die Text-Effekte koennen modular erweitert werden. Neue Module und Updates werden unter
[WP-Ticker SteGaSoft](http://wp-ticker.stegasoft.de/) veroeffentlicht.
WP-Ticker kann auch als Live-Ticker genutzt werden, da der Inhalt per Ajax geladen wird.
D. h. es ist kein Reload der Seite notwendig, um Aktualisierungen zu sehen.
Mit WP-Ticker koennen auch (die meisten) ATOM-Feeds geparst werden.

= Funktionen: =
* Datenquelle: Datenbank, eigener Text, RSS, Kommentare. Bei Datenbank als Quelle kann der Kontent ueber die Auswahl der entspr. Kategorie(n) bestimmt werden.
* Aussehen kann per CSS angepasst werden (global und/oder individuell fuer alle Ticker).
* Ein angelegter Ticker kann einfach per Widget in eine Sidebar eingebunden werden.
* Angabe von Start- / Enddatum fuer Anzeigezeitraum bei eigenem Text moeglich (inkl. autom. Loeschfunktion).
* Live-Ticker-Einsatz durch Angabe eines Reload-Intervalls moeglich.
* Sortierung (inkl. Zufall) des Kontents individuell fuer jeden Ticker moeglich

= Funktionen in Version 1.6.1 (siehe wp-ticker.stegasoft.de) : =
* zusaetzliche Datenquellen: WP-Galerie, Key-/Tag- Custom-Field - Suche 
* Basis-Layout ueber WYSIWYG-CSS-Generator einstellbar ( s. [WP-Ticker Beispiele](http://wp-ticker.stegasoft.de/beispiele/) )
* Einfaches Einbinden eines Tickers in Beitrag oder Seite ueber extra Button in TinyMCE


== Installation ==
Entpacken Sie die ZIP-Datei und laden Sie den Ordner wp-ticker in das
Plugin-Verzeichnis von WordPress hoch: *wp-content/plugins/*.


Loggen Sie sich dann als Admin unter Wordpress ein.
Unter dem Menuepunkt "Plugins" koennen Sie WP-Ticker
nun aktivieren. Sie finden dort auch den Untermenuepunkt "WP-Ticker".
Durch Klick auf diesen Link gelangen Sie zur Administration des
Plugins.

Bei Nutzung im Multisite-Betrieb bitte beachten:
Das Plugin ueber das Netzwerk installieren aber nicht(!) fuer
alle Netzwerke aktivieren!
Das Plugin fuer jeden Blog separat aktivieren!

Bitte stellen Sie noch sicher, dass die Datei "style.css" und das Verzeichnis "/styles/"
Schreibrechte besitzen.


== Frequently Asked Questions ==
FAQ unter [WP-Ticker SteGaSoft](http://wp-ticker.stegasoft.de/faq/)



== Changelog ==
= Version 1.6.1 (11/2014) =
* Kleiner Bugfix bei der Darstellung von eigenen Texten im Backend

= Version 1.6 (11/2014) =
* Uebersetzung fuer Monats- und Tages-Namen (Datum) bei RSS-Feeds
* Bug bei Image-Gallery behoben
* interne Aenderungen
* Session-Start nur bei $use_session=true
* IE 11 - Erkennung, CSS-Generator fuer IE engepasst
* Beitraege/Post ueber Tags-/Key-Wort oder Custom Fields - Suche
* neu im  CSS-Generator: font-size, line-height


= Version 1.5 (02/2014) =
* Ticker kann einfach ueber DropDown-Liste in TinyMCE (WP-Ticker - Button) in Beitrag oder Seite eingebaut werden
* Automatisches Duplizieren bei nur einem vorhanden Eintrag verhindert Anzeige-Problem z. B. bei Tendless
* sind keine Inhalte vorhanden, wird Ticker automatisch ausgeblendet
* "tic-global-custom.php" kann von Benutzer erstellt werden, um eigene Einstellungen vor Update zu sichern (Variablen entspr. aus tic-global.php uebernehmen)
* zeitlich abgelaufene Eintraege aus "eigener Text" werden farblich unterlegt; Farbe ueber tic-global.php bzw. tic-global-custom.php -> "$bg_red" anpassbar
* Start und Ende bei eigenem Text um Uhrzeit erweitert
* Ablaufdatum auch fuer Texte aus Kategorien einstellbar, dazu Benutzerdefinierte Felder nutzen:
  * Name: wpticker_enddate , Wert: Y-m-d (Beispiel 2014-01-31 fuer 31. Januar 2014); dieses Feld muss mindestens gefuellt werden, um Funktion zu aktivieren
  * Name: wpticker_endtime,  Wert: H:i (Beispiel 13:30), dieses Feld ist optional (Standard 00:00)
* Text-Kuerzung im Backend fuer eigenen Text ueberarbeitet, HTML-Tags werden nun richtig behandelt
* style-global.css neu eingefuehrt, style.css mit benutzerspezifischen Angaben sollte bei zukuenftigen Updates nicht mehr ueberschrieben werden
* Bugfix: Button-Problem [<=Heute] unter Safari
* Bugfix: interner Klassen-Name angepasst

= Version 1.4 (02.05.2013) =
* Titel ueber ID und/oder Klasse per CSS editieren
   * .ticker_head bzw. #ticker_head_TICKERID => RSS-Quelle (Ueberschrift)
   * .ticker_item_head bzw. #ticker_item_head_TICKERID_ITEMNUMBER => Ueberschriften der einzelnen Tickerbeitraege
   * .ticker_more => Link zu "...weiter"
* Shortcode auch in (Text-) Widgets nutzbar (ueber Backend aktivierbar)
* Images aus WordPress-Gallery als Datenquelle anzeigen
* WYSIWYG CSS-Generator
* interne Fixes

+++++ die neueste Version finden Sie auf [WP-Ticker.SteGaSoft.de](http://wp-ticker.stegasoft.de/downloads/) +++++

= Version 1.3.2.3 (18.12.2013) =
* spanische Uebersetzung

= Version 1.3.2.2 (04.05.2013) =
* Bugfix (Error in Zeile 102)

= Version 1.3.2.1 (03.05.2013) =
* kleine interne Aenderung

= Version 1.3.2 (20.01.2013) =
* kleine interne Aenderung

= Version 1.3.1 (19.01.2013) =
* Bugfix bei Aenderung des Verzeichnisnamens von wp-content

= Version 1.3 (15.01.2013) =
* Backend wurde uebersichtlicher gestaltet
* erweiterte Sortier-Moeglichkeiten
* auch Kommentare als Datenquelle moeglich
* fuer Multisite-Betrieb angepasst

= Version 1.2 (17.12.2012) =
* bei eigenem Text kann ueber Button das aktuelle Datum direkt eingestellt werden
* die CSS-Datei fuer Ticker kann direkt ueber die Adminseite editiert werden
* neue Effekt-Module koennen direkt ueber die Adminseite hochgeladen werden
* zufaellige Sortierung des Kontents individuell fuer jeden Ticker moeglich
* neuer Shortcode [wptictext] wurde eingefuehrt, damit laesst sich eine Historie der selbst erstellten Texte auf einer Seite/einem Beitrag anzeigen

= Version 1.1.1 (12.07.2012) =
* kleine Anpassung fuer aufwendigere Modul-Scripte

= Version 1.1 =
* Datum und Zeit als Template-Variable eingefuehrt

= Version 1.01 (11.06.2012) =
* kleine Anpassung fuer das Modul Tendless

= Version 1.0 (01/2012) =
* Kontent wir mit AJAX eingelesen, d. h. Aktualisierungen werden ohne Seiten-Reload angezeigt.
* verbesserte Verwaltung von eigenem Text mit Start-/End-Funktion der Anzeige und auto. Loeschen der Eintraege.
* WP-Ticker kann nun auch als Widget eingebunden werden.

= Version 0.131 (05.04.2011) =
* Kleiner Bugfix bei der Darstellung von Datenbank-Inhalten.


= Version 0.13 (21.03.2011) =
* Umstellung auf jQuery von WordPress (keine separate Implementierung mehr)


= Version 0.12 (04.07.2010) =
* kleiner Bugfix bei Arrayverarbeitung falls keine Kategorien selektiert wurden


= Version 0.11 (25.06.2010) =
* zwei kleine Fehler behoben:
   * Datenbankfeldgroesse erweitert,
   * Code: Funktionsaufruf-Hinweistext korrigiert


= Version 0.1 =
* Erste Version fuer Wordpress bis V3.1


== Upgrade Notice ==
Zur Zeit keine Angaben.

== Screenshots ==
Screenshots unter [WP-Ticker SteGaSoft](http://wp-ticker.stegasoft.de/screenshots/)


== Other Notes ==

= Copyright =
Wordpress - Plugin "WP-Ticker"
(c) 2010-2013 by SteGaSoft, Stephan Gaertner
Www: <http://wp-ticker.stegasoft.de>
eMail: s. website


Vielen Dank an Andrew Kurtis von [WebHostingHub](http://www.webhostinghub.com/) fuer die
spanische Uebersetzung.


= Hinweis =
Ich versuche, WP-Ticker fuer moeglichst viele Browser-Varianten zu entwickeln.
Bitte haben Sie aber Verstaendnis dafuer, dass aufgrund der teils kurzen Update-Intervalle
der Browser leider manchmal vorallem aeltere Versionen aus der Kompatibilitaetsliste rausfallen.


= Administration =
Deinstallieren:
Wenn Sie dieses Feld markieren, werden alle Daten und Tabellen nach Deaktivierung des Plugins geloescht.

Ticker-ID:
Die ID wird automatisch vergeben und kann nicht veraendert werden. Lediglich die Einstellungen zu jeder
ID sind aenderbar.

Datenquelle:
Hier koennen Sie definieren, woher der Ticker seine Daten beziehen soll.
Je nach Auswahl werden Kategorien, eine Tabelle oder ein Textfeld eingeblendet. Entsprechend muss eine
Auswahl getroffen oder die Tabelle/das Textfeld mit Daten gefuellt werden.

Dauer fuer
Anzeige:     Anzeigezeit des Kontents in Millisekunden
Einblendung: Einblendezeit (Fadein, Slidein etc.) des Kontents in Millisekunden
Ausblendung: Ausblendezeit (Fadeout, Slideout etc.) des Kontents in Millisekunden

 Beachten Sie bitte, dass die Summe aus Einblendung und Ausblendung kleiner der Anzeigezeit sein sollte, da
 es sonst zu ungewollten Effekten kommen kann (aber vielleicht gefaellt Ihnen auch der Effekt).

Reaload-Intervall: Dauer in Minuten, bis der Kontent aktualisiert wird, 0 oder leer: kein Reload.
 Tragen sie hier keine zu kurzen Reload-Zeiten ein
Reaload-Pause: Anzeige-Dauer in Sekunden des Reload-Textes bzw. Bildes  (0 oder leer: kein Reload.
 Tragen sie hier relativ kurze Zeiten ein.

Tickertyp:
Hier koennen Sie den Anzeigetyp des Tickers auswaehlen. Je nach eingebundenen Modulen wird die Auswahlliste
angepasst. Neue Module und Updates werden unter wp-ticker.stegasoft.de veroeffentlicht.

Max. Eintraege:
Hier koennen Sie angeben, wieviele Eintraege aus jeweils einer Kategorie oder eines Feeds angezeigt
werden sollen. Auf eigenen Text hat dieser Wert keinen Einfluss!


Max. Zeichen:
Hier koennen Sie angeben, wieviele Zeichen maximal angezeigt werden sollen. Es wird nur der Kontext und nicht der
Titel gekuerzt. Der More-Tag kann in der Datei global.php angepasst werden.
Auf eigenen Text hat dieser Wert keinen Einfluss!

Template:
Hier koennen Sie durch Setzten der Variablen %tic_date%, %tic_time%,%tic_title% und %tic_content% den Aufbau des Tickerkontents bestimmen.
Dies hat keinen Einfluss bei eigenem Text!

Memo:
Hier koennen Sie eine Notiz oder Info zum Ticker eingeben, z.B. auf welcher Seite/Post der Ticker verwendet wird.
Somit koennen Sie den Ticker besser identifizieren.


Start-Datum und End-Datum geben an, ab bzw. bis wann der eigene Text angezeigt werden soll.
Ist "auto. Loeschen" ausgewaehlt, werden die eigenen Text automatisch unwiderruflich geloescht.


= Ticker einbinden =
Wenn Sie einen Ticker erstellt haben, klicken Sie einfach in der unteren Tabelle in der entspr. Zeile
auf den Button [Code]. Kopieren Sie sich den Code entweder fuer die Einbindung in eine Template-Datei oder
in eine(n) Post/Seite und fuegen Sie diesen an entspr. Stelle ein.
Noch einfacher geht es, indem Sie WP-Ticker als Widget einbinden. Allerdings ist nur eine Instanz moeglich!

Beachten Sie bitte, dass nicht mehrere Ticker mit gleicher ID auf der selben Seite angezeigt werden!

Ticker koennen Sie durch Klick auf den entspr. Button wieder loeschen oder bearbeiten. In letzterem Fall
werden die Daten des gewaehlten Tickers oben in den Feldern angezeigt.


= Style anpassen =
Das Aussehen der Ticker koennen Sie in der Datei style.css bestimmen. Dazu passen Sie global fuer alle Ticker die
CSS-Klasse .ticker_content an.
Um einen Ticker individuell anzupassen, legen Sie einfach ein neues Style-Attribut mit der ID des betreffenden
Tickers an.
Beispiel: Soll der Style fuer den Ticker mit der ID=4 abweichend von der globalen Klasse bestimmt werden,
geben Sie einfach folgende Bezeichnung in der Datei ein:
#ticker_content_4 {
 ...
 Ihre Style-Angaben
 ...
}

= Historie fuer eigenen Text anzeigen =
Mit dem Shortcode [wptictext id=1 sort="ASC"] kann ganz einfach eine Liste der eigenen Texte auf einer Seite/einem Beitrag angezeigt werden.
Mit dem Parameter "id" (Standard=1) wird die Ticker-ID angegeben, fuer die die Textliste angezeigt werden soll.
Mit dem Parameter "sort" wird die Sortierreihenfolge festgelegt. Werte: ASC (Standard), DESC, RAND().

= Gewaehrleistung =
Es gibt keine Gewaehrleistung fuer die Funktionalitaet von WP-Ticker. Ausserdem uebernimmt der Autor/Programmierer
von WP-Ticker keine Garantie fuer evtl. Datenverluste oder sonstige Beeintraechtigungen, die evtl. durch die
Nutzung von WP-Ticker entstanden sind.
Die Nuzung von WP-Ticker geschieht auf eigenes Risiko des jeweiligen Nutzers.



Vergessen Sie zum Schluss das Speichern nicht!


Viel Spass mit dem Plugin wuenscht
SteGaSoft, Stephan Gaertner