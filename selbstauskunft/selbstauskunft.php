<?php
//Last Modified: 17.12.2016
// selbstauskunft.php
require_once('./serverlib/init.inc.php');
RequestPrivileges(PRIVILEGES_USER);

/*
 * default action = start
 */

/**
 * page sidebar
 */
$tpl->assign('pageMenuFile', 'li/start.sidebar.tpl');

/**
 * dashboard
 */
if($_GET['format']=="xml") {
        $xml = new SimpleXMLElement('<emailuser/>');
        $flipUserRow = array_flip($userRow);
        array_walk_recursive($flipUserRow, array ($xml, 'addChild'));
        //print $xml->asXML();
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());
        echo $dom->saveXML();
        exit;
}
if($_GET['format']=="json") {
        echo json_encode($userRow);
        exit;
}
?>

Sehr geehrte/r <?=$userRow['vorname']; ?> <?=$userRow['nachname']; ?>,
<br /><br />
gerne erteilen wir Ihnen die gewünschte Auskünfte über Ihre personenbezogenen Daten
<br /><br />
Folgende Daten haben wir über Sie gespeichert:
<br /><br />
Anrede: <?=$userRow['anrede']; ?><br />
Vorname: <?=$userRow['vorname']; ?><br />
Nachname: <?=$userRow['nachname']; ?><br />
Adresse: <?=$userRow['strasse']; ?> <?=$userRow['hnr']; ?><br />
PLZ und Ort: <?=$userRow['plz']; ?> <?=$userRow['ort']; ?><br />
Telefon: <?=$userRow['tel']; ?><br />
Fax: <?=$userRow['fax']; ?><br />
Genutzter Webdisk-Speicher: <?=$userRow['discspace_used']; ?><br />
Genutzter E-Mail-Speicher: <?=$userRow['mailspace_used']; ?><br />
Spamfilter aktiviert: <?=$userRow['spamfilter']; ?><br />
Virusfilter aktiviert: <?=$userRow['virusfilter']; ?><br />
Gesperrt: <?=$userRow['locked']; ?><br />
Alias-Domains: <?=$userRow['saliase']; ?><br />
Antwort: <?=$userRow['re']; ?><br />
Weiterleitung: <?=$userRow['fwd']; ?><br />
Letzter Login: <?=$userRow['lastlogin']; ?><br />
Letzter POP3-Login: <?=$userRow['last_pop3']; ?><br />
Letzter SMTP-Login: <?=$userRow['last_smtp']; ?><br />
Newsletter: <?=$userRow['newsletter_optin']; ?><br />
Konversations-Ansicht: <?=$userRow['conversation_view']; ?><br />
Gruppen-ID: <?=$userRow['gruppe']; ?><br />
Datumsformat: <?=$userRow['datumsformat']; ?><br />
Absendername: <?=$userRow['absendername']; ?><br />
Interne Notizen: <?=$userRow['notes']; ?><br />
Haupt E-Mail: <?=$userRow['email']; ?><br />
Alternative E-Mail: <?=$userRow['altmail']; ?><br />
Aliase:<br />
<?php $aliases = $thisUser->GetAliases();

foreach($aliases as $alias)
{
        echo "E-Mail: ".$alias['email']. "<br />";
        echo "Typ: ".$alias['typeText']. "<br />";
}
?>
E-Mail-Weiterleitung eingeschaltet?: <?=$userRow['forward']; ?><br />
E-Mail-Weiterleitung an: <?=$userRow['forward_to']; ?><br />
E-Mail nach Weiterleitung löschen: <?=$userRow['forward_to']; ?><br />
Signaturen:<br />
<?php
$signatures = $thisUser->GetSignatures();

foreach($signatures as $signature)
{
        echo "<textarea>".$signature['text']."</textarea><br />";
}
?>
