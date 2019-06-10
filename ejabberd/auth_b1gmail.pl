#!/usr/bin/perl

# Mysql external auth script
# Features: auth isUser and change password
# 2009-02-04 - by Mogilowski Sebastian - http://www.mogilowski.net
# 2010-12-26 - Home of the Sebijk.com - http://www.sebijk.com (for b1gMail)

# Settings
my $sDatabaseHost='SERVER';            # The hostname of the database server
my $sDatabaseUser="USER";            # The username to connect to mysql
my $sDatabasePass='PASS';            # The password to connect to mysql
my $sDatabaseName="DATABASE";            # The name of the database contain the user table
my $sUserTable="bm60_users";             # The name of the table containing the username and password
my $sUsernameTableField="email";         # The name of the field that holds jabber user names
my $sPasswordTableField="passwort";   # The name of the field that holds jabber passwords

# Libs
use DBI;
use DBD::mysql;
use Digest::MD5 qw(md5 md5_hex md5_base64);

while(1) {
my $sBuffer = "";
my $readBuffer = sysread STDIN,$sBuffer,2;
my $iBufferLength = unpack "n",$sBuffer;
my $readBuffer    = sysread STDIN,$sBuffer,$iBufferLength;
my ($sOperation,$sUsername,$sDomain,$sPassword) = split /:/,$sBuffer;
my $bResult;

SWITCH: {
$sOperation eq 'auth' and do {
$semailadressauth = $sUsername.'@'.$sDomain;
$cryptmd5password = md5_hex($sPassword);
$bResult   = 0;
$connect   = DBI->connect('DBI:mysql:'.$sDatabaseName, $sDatabaseUser, $sDatabasePass) || die "Could not connect to database: $DBI::errstr";
$query     = "SELECT $sPasswordTableField FROM $sUserTable WHERE $sUsernameTableField='$semailadressauth';";
$statement = $connect->prepare($query);
$statement->execute();
while ($row = $statement->fetchrow_hashref()) {
if ($row->{$sPasswordTableField} eq $cryptmd5password) {
$bResult = 1;
}
}
},last SWITCH;

$sOperation eq 'setpass' and do {
$semailadressauth = $sUsername.'@'.$sDomain;
$connect   = DBI->connect('DBI:mysql:'.$sDatabaseName, $sDatabaseUser, $sDatabasePass) || die "Could not connect to database: $DBI::errstr";
$cryptmd5password = md5_hex($sPassword);
$myquery   = "UPDATE $sUserTable SET $sPasswordTableField='$cryptmd5password' WHERE $sUsernameTableField='$semailadressauth';";
$statement = $connect->prepare($myquery);
$statement->execute();
$bResult = 1;
},last SWITCH;

$sOperation eq 'isuser' and do {
$semailadressauth = $sUsername.'@'.$sDomain;
$bResult   = 0;
$connect   = DBI->connect('DBI:mysql:'.$sDatabaseName, $sDatabaseUser, $sDatabasePass) || die "Could not connect to database: $DBI::errstr";
$myquery   = "SELECT count(*) AS iCount FROM $sUserTable WHERE $sUsernameTableField='$semailadressauth';";
$statement = $connect->prepare($myquery);
$statement->execute();
$row = $statement->fetchrow_hashref();
if($row->{'iCount'} >= 1){
$bResult = 1;
}
},last SWITCH;
};

my $sOutput = pack "nn",2,$bResult ? 1 : 0;
syswrite STDOUT,$sOutput;
}
closelog;