<?


/******************************************************************************/
/*
                Written by:        Martin Settle
                Last Modified:     April 20, 2002
                Called by:         mailout.php
                Calls:             configuration.php
                                   connectdb.php

                Description:       creates a file called memberlist.txt that
                                   has all membership and contact information
                                   as of the current date

                Modification History:
                                April 20, 2002 - File Created

*/
/******************************************************************************/

include "configuration.php";
include "connectdb.php";
error_reporting(255);
$Date = date("Y-m-d");

//$MemberFile = fopen("memberlist.txt",'w');
$MemberFile = fopen("tmp/memberlist.txt",'w');
if($MemberFile)
fputs($MemberFile, "$Systemname Membership List\nPrinted on $Date\n\n");
fputs($MemberFile, "  ACCT  MEMBER                        PHONE                OTHER              EMAIL\n");
$AccountsLookup = mysql_query("SELECT AccountID, AccountName, account.AccountTypeID, AccountTypeName
                                FROM account,accounttypeoptions
                                WHERE account.AccountTypeID = accounttypeoptions.AccountTypeID
                                AND AccountRenewalDate >= '$Date'
                                ORDER BY AccountID");
while($Accounts = mysql_fetch_array($AccountsLookup))
{
        $MemberInfo = mysql_query("SELECT MemberFirstName,MemberLastName,HomeNumber,OtherNumber,EmailAddress,PrimaryContact
                                    FROM member,membertoaccountlink
                                    WHERE member.MemberID = membertoaccountlink.MemberID
                                    AND membertoaccountlink.AccountID = $Accounts[AccountID]
                                    ORDER BY PrimaryContact DESC, MemberLastName, MemberFirstName");
        print mysql_error();
        while($Members = mysql_fetch_array($MemberInfo))
        {
                if($Members['PrimaryContact'] == 1)
                {
                        fputs($MemberFile, "\n");
                        $extra = (6 - strlen("$Accounts[AccountID]"));
                        for($Space=1; $Space <= $extra; $Space++)
                        {
                                fputs($MemberFile, " ");
                                }
                        fputs($MemberFile, "$Accounts[AccountID]  ");
                        }
                else
                {
                        fputs($MemberFile, "        ");
                        }
                $Name = "$Members[MemberLastName], $Members[MemberFirstName]";
                fputs($MemberFile, "$Name");
                for($Space=1; $Space + strlen($Name) <= 30; $Space++)
                {
                        fputs($MemberFile, " ");
                        }
                fputs($MemberFile, "$Members[HomeNumber]");
                for($Space=1; $Space + strlen($Members["HomeNumber"]) <= 20; $Space++)
                {
                        fputs($MemberFile, " ");
                        }
                fputs($MemberFile, "$Members[OtherNumber]");
                for($Space=1; $Space + strlen($Members["OtherNumber"]) <= 20; $Space++)
                {
                        fputs($MemberFile, " ");
                        }
                fputs($MemberFile, "$Members[EmailAddress]\n");
                }
        }
fclose($MemberFile);
?>