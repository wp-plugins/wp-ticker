=== WP-Ticker ===
Contributors: Stephan Gaertner
Donate link: http://www.stegasoft.de
Tags: news,ticker,newsticker,textticker,live ticker,text,fader,scroller,rss,atom
Requires at least: 2.6
Tested up to: 3.3.1
Stable tag: 1.1


== Description ==
Wp-Ticker ist eigentlich die Weiterentwicklung des beliebten WordPress-Plugins Ticker.
Er basiert auf jQuery und es ist nun moeglich, mehrere Ticker-Instanzen zu erstellen.
D. h. es koennen mehrere Ticker auf einer Seite/in einem Artikel dargestellt werden.
Ausserdem wurde der CSS-Style erheblich vereinfacht. Alle Ticker werden
ueber eine zentrale Style-Datei formatiert (Groesse, Aussehen, etc.).
Der Kontent wird nicht mehr in einem IFrame angezeigt, sondern direkt in Div-Elementen.
Die Scripte koennen modular erweitert werden. Neue Module und Updates werden unter
www.stegasoft.de veroeffentlicht.
Ab Version 1.0 kann WP-Ticker auch als Live-Ticker genutzt werden, da der Inhalt per
Ajax geladen wird. D. h. es ist kein Reload der Seite notwendig, um Aktualisierungen
zu sehen.

Im Gegensatz zu Ticker koennen leider (noch) keine Klicks bei eigenem Text gezaehlt werden.

Die Datenbankabfragen wurden ueberarbeitet. Somit sollte die Kompatibilitaet zu
zukuenftigen WordPress-Versionen gewaehrleistet sein.

Mit WP-Ticker koennen (die meisten) ATOM-Feeds geparst werden.


== Copyright ==
Wordpress - Plugin "Wp-Ticker"
(c) 2010-2012 by SteGaSoft, Stephan Gaertner
Www: http://www.stegasoft.de
eMail: s. website
Der Copyright-Hinweis muss sichtbar am Ticker erhalten bleiben!
Weitere Infos dazu finden Sie unter http://www.stegasoft.de/wordpress-plugin-wp-ticker/
im Abschnitt "Lizenz".


== Historie ==
Version 1.1
 - Datum und Zeit als Template-Variable eingefuehrt

Version 1.01 (11.06.2012)
 - kleine Anpassung für das Modul Tendless

Version 1.0 (01/2012)
 - Kontent wir mit AJAX eingelesen, d. h. Aktualisierungen werden ohne
   Seiten-Reload angezeigt.
 - verbesserte Verwaltung von eigenem Text mit Start-/End-Funktion der Anzeige
   und auto. Loeschen der Eintraege.
 - WP-Ticker kann nun auch als Widget eingebunden werden

Version 0.131 (05.04.2011)
 - Kleiner Bugfix bei der Darstellung von Datenbank-Inhalten.


Version 0.13 (21.03.2011)
 - Umstellung auf jQuery von WordPress (keine separate Implementierung mehr)


Version 0.12 (04.07.2010)
 - kleiner Bugfix bei Arrayverarbeitung falls keine
   Kategorien selektiert wurden


Version 0.11 (25.06.2010)
 - zwei kleine Fehler behoben:
   - Datenbankfeldgroesse erweitert
   - Code: Funktionsaufruf-Hinweistext korrigiert


Version 0.1
 - Erste Version fuer Wordpress bis V3.1




== Installation ==
Entpacken Sie die ZIP-Datei und laden Sie den Ordner wp-ticker in das
Plugin-Verzeichnis von WordPress hoch: wp-content/plugins/.


Loggen Sie sich dann als Admin unter Wordpress ein.
Unter dem Menuepunkt "Plugins" koennen Sie WP-Ticker
nun aktivieren. Sie finden dort auch den Untermenuepunkt "WP-Ticker".
Durch Klick auf diesen Link gelangen Sie zur Administration des
Plugins.




== Administration ==
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
angepasst. Neue Module und Updates werden unter www.stegasoft.de veroeffentlicht.

Max. Eintraege:
Hier koennen Sie angeben, wieviele Eintraege aus jeweils einer Kategorie oder eines Feeds angezeigt
werden sollen. Auf eigenen Text hat dieser Wert keinen Einfluss!


Max. Zeichen:
Hier koennen Sie angeben, wieviele Zeichen maximal angezeigt werden sollen. Es wird nur der Kontext und nicht der
Titel gekuerzt. Der More-Tag kann in der Datei global.php angepasst werden.
Auf eigenen Text hat dieser Wert keinen Einfluss!

Template:
Hier koennen Sie durch Setzten der Variablen %tic_title% und %tic_content% den Aufbau des Tickerkontents bestimmen.
Dies hat keinen Einfluss bei eigenem Text!

Memo:
Hier koennen Sie eine Notiz oder Info zum Ticker eingeben, z.B. auf welcher Seite/Post der Ticker verwendet wird.
Somit koennen Sie den Ticker besser identifizieren.


Start-Datum und End-Datum geben an, ab bzw. bis wann der eigene Text angezeigt werden soll.
Ist "auto. Loeschen" ausgewählt, werden die eigenen Text automatisch unwiderruflich geloescht.


== Ticker einbinden ==
Wenn Sie einen Ticker erstellt haben, klicken Sie einfach in der unteren Tabelle in der entspr. Zeile
auf den Button [Code]. Kopieren Sie sich den Code entweder fuer die Einbindung in eine Template-Datei oder
in eine(n) Post/Seite und fuegen Sie diesen an entspr. Stelle ein.
Noch einfacher geht es, indem Sie WP-Ticker als Widget einbinden. Allerdings ist nur eine Instanz moeglich!

Beachten Sie bitte, dass nicht mehrere Ticker mit gleicher ID auf der selben Seite angezeigt werden!

Ticker koennen Sie durch Klick auf den entspr. Button wieder loeschen oder bearbeiten. In letzterem Fall
werden die Daten des gewaehlten Tickers oben in den Feldern angezeigt.


== Style anpassen ==
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


== Gewaehrleistung ==
Es gibt keine Gewaehrleistung fuer die Funktionalitaet von WP-Ticker. Ausserdem uebernimmt der Autor/Programmierer
von WP-Ticker keine Garantie fuer evtl. Datenverluste oder sonstige Beeintraechtigungen, die evtl. durch die
Nutzung von WP-Ticker entstanden sind.
Die Nuzung von WP-Ticker geschieht auf eigenes Risiko des jeweiligen Nutzers.



Vergessen Sie zum Schluss das Speichern nicht!


Viel Spass mit dem Plugin wuenscht
SteGaSoft, Stephan Gaertner