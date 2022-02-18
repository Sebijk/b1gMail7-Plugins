# b1gMail HTTPMail Server Plugin
This plugin provides the HTTPMail server protocol for b1gMail 7.4. It was shipped until b1gMail 7.3 and was removed in 7.4, because Microsoft removed this in Outlook 2010 and later. So only Outlook 2007 and older and Outlook Express support this. b1gMail users can connect use Outlook 2007 and earlier or Outlook Express to access their emails.

## What is HTTPMail?
HTTPMail was the method used by Outlook Express to connect mainly to the free email service, Hotmail. This allowed users to access their Hotmail accounts without actually logging into the Hotmail website. To the user, it seems very similar to an IMAP email server.

Behind the scenes, Outlook Express uses WebDAV to communicate with the Hotmail servers. WebDAV is a method of communication that sends XML over HTTP. It allows a client to query and manipulate items on the server. Through this, Outlook Express can display the user's folders and messages as well as basic message manipulation (creating folders, moving messages, etc.)

HTTPMail is also supported on other servers than Hotmail.

## Attention

We NOT recommended to install this plugin on production environment, since this protocol is deprecated. Also the mentioned clients don't support TLS. I only upload this for archive purpose.

## Installation
Upload the content in upload folder to the b1gmail root and enable HTTPMail Plugin. After configure the Group settings and then its ready.
