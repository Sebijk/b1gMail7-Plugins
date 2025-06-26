 Ermöglicht die Nutzung der Webdisk als Webspace für Kunden zum erstellen einer eigenen Homepage.

Mit diesem Plugin kann euren Kunden ermöglicht werden auf einfache Art und Weise die Webdisk als Homepage zu nutzen. Der Kunde muss dazu lediglich das Feature bei der Webdiskfreigabe aktivieren.
Es werden weder ein eigener Server noch root-Rechte benötigt.

Features:
- Die Webdisk kann als Webspace (nur HTML) mit beliebig vielen Ordnern verwandt werden
- Für jeden E-Mail Alias kann eine andere Freigabe verwendet werden
- Homepage, bzw. Webspace-Funktion kann pro Gruppe aktiviert werden
- Pro Gruppe kann ein "Banner"-Code (z.B. Ad-Layer, Pagepeel) definiert werden (ab Version 1.1.3)
- Traffic- und Bandbreitengerenzung der Webdisk wird berücksichtigt
- Homepage kann mit einem Passwort geschützt werden
- Fehlerseiten können in den Templates angepasst werden
- Directory Listing (kann global und von jedem Benutzer einzeln deaktiviert werden)
- Sprachen: Englisch & Deutsch

Anforderungen:
- Möglichkeit der verwendung eigener 404-Fehlerseiten per ".htaccess"
- evtl. Wildcard/Catch-All Subdomain für Verzeichnis "share" (siehe unten: Homepage-URL-Varianten)


Installation:
1. Hochladen und aktivieren des Plugins über die Adminstration
2. Einrichten der 404-Fehlerseite, z.B: per ".htaccess" Datei in /share/ mit dem Inhalt je nach URL-Variante: "ErrorDocument 404 /index.php"
3. Evtl. (je nach URL-Variante): Einrichten der Wildcard/Catch-All Subdomain für das /share Verzeichnis (sofern nicht bereits geschehen für normale Freigaben)
4. Aktivieren der Option "Homepage" bei den entsprechenden Benutzer-Gruppen

Kundennutzung:
1. Zuerst muss ein Ornder auf der Webdisk erstellt und frei gegeben werden.
(Passwort optional)
2. Unter "Einstellungen" -> "Homepage" muss dieser Ordner anschließend ausgewählt werden.
3. Nun erreicht man z.B. unter http://USERNAME.DOMAIN/ (siehe "URL-Varianten") die Homepage, die im ausgewählten Ordner liegt. (Anstatt der Auflistung der Freigaben)
Wurde vorher ein Passwort bei der Freigabe gesetzt, wird auch die Homepage damit geschützt. Der Benutzername ist beliebig und kann (je nach Browser) auch leer gelassen werden.

Homepage-URL-Varianten (Wenn "/"=b1gMail-Hauptverzeichnis):
1. Wildcard/Catch-All Subdomain (Ziel: /share)
- Inhalt von "share/.htaccess": "ErrorDocument 404 /index.php"
- Beispiel-URL: http://USERNAME.DOMAIN/
2. Wildcard/Catch-All Subdomain (Ziel: /)
- Inhalt von "share/.htaccess": "ErrorDocument 404 /share/index.php"
- Beispiel-URL: http://USERNAME.DOMAIN/share/
3. Normale Subdomain(z.B. pages.DOMAIN) (Ziel: /share) (seit Version 1.1)
- Inhalt von "share/.htaccess": "ErrorDocument 404 /index.php"
- Beispiel-URL: http://pages.DOMAIN/~USERNAME/
4. Keine Subdomain (oder normale Subdomain) (Ziel: /) (seit Version 1.1)
- Inhalt von "share/.htaccess": "ErrorDocument 404 /share/index.php"
- Beispiel-URL: http://DOMAIN/share/~USERNAME/ 

Aktuelle Version: 1.1.5 von 25.04.2009:
 - Durchsuchen der Homepage-Freigabe (wie normale Freigaben) wird nun verhindert. 

ältere Versionen:


Version 1.1.4
- Kleines Bugfix wegen "Warning: Call-time pass-by-reference", Zeilen 146 und 501

Version 1.1.3
- Bugfix: Unbegrenzter Webdisk-Traffic funktionierte nicht bei Homepage
- Feature: HTML-Code für Werbung kann oberhalb oder unterhalb einer HTML-Seite ausgegeben werden (pro Gruppe definierbar)

Version 1.1.1
 - Bugfix: entfernt Fehlermeldung wenn Homepage-EInstellungen gespeichert werden und kein Alias vorhanden ist. (Meldung hatte keine Auswirkung)
 - Betrifft Zeilen 474-476

Version 1.1.0
- Getestet mit 7.0.0 PL2 und 7.1.0

- Homepage von gesperrten Benutzern wird nun nichtmehr angezeigt
- Neue Varianten für Homepage-URL (siehe Plugin-Beschreibung)
- Unterschiedliche Freigabe-Ordner für Aliase als Basis
- Unterverzeichnisse können getrennt mit Passwörtern versehen werden (Verzeichnis muss hierfür frei gegeben werden)

Version 1.0.0
(Benötigt min. Patchlevel 1, entwickelt mit PL2) 