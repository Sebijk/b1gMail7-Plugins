/****
Google Rechtschreibpr�fung f�r b1gMail
****/

var googie = new GoogieSpell("googiespell/", "sendReq.php?lang=");
googie.setLanguages({'de': 'Deutsch', 'en': 'English'});
googie.dontUseCloseButtons();
googie.decorateTextarea("emailText");