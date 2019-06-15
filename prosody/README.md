# b1gMail Prosody Authentification script

This script allows b1gMail users to log in and use prosody servers. This script is written exclusively for the XMPP (Jabber) server prosody.

## Installation

Download the auth_prosody.php and edit the file with an editor to customize the path of your b1gMail installation, there you will also find the corresponding configuration entries for the prosody server.   In addition, the prosody user must also be in the same group as the b1gMail, since this script uses the b1gMail PHP files for authentication.

Also note any notes about the prosody module mod_auth_external: https://modules.prosody.im/mod_auth_external.html

The prosody.plugin.php integrate the delete function of a user. But it only works, if you set MySQL/MariaDB as storage on prosody and it is on the same database like b1gMail.  This file you put in your b1gmail plugin folder and activate this from administration center.
