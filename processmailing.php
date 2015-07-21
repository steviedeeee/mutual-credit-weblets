<?
/******************************************************************************/
/*
                Written by:        Martin Settle
                Last Modified:        January 25, 2002
                Called by:        header.php
                Calls:                configuration.php
                                connectdb.php
                                header.php
                                footer.php
                Description:        processes a LETS member mailing, sending a
                                submitted e-mail text and attachments to all
                                members, and printing to file a .csv file
                                with data for those accounts not receiving
                                e-mails.

                Modification History:
                                January 25, 2002 - File Created
				August 21, 2002 - Printing of non-email account
					statements to text directory added.
				January 16, 2003 - no fee charged to accounts
					with 0 credit limit
						- account statements altered
					to show fees on same line as trade
						- INSERT SQL altered for 
					flexibility in structure
				January 28, 2003 - stop e-mail to closed accounts

*/
/******************************************************************************/

/* get the includes out of the way */

include "configuration.php";
include "connectdb.php";

/* make sure the user is admin authorized */

include "adminlogin.php";

error_reporting(255);
/* This is the creditlimit function called to check whether the limit has
changed */

function CheckCreditLimit($AccountID)
{
        $lookupvolume = mysql_query("SELECT SUM(ABS(Amount)) as Volume
                                      FROM transactions
                                       WHERE AccountID = '$AccountID'
                                       AND Description !='Transaction Fee'");
        $volume = mysql_result($lookupvolume,0,"Volume");

        $lookupcurrentlimit = mysql_query("SELECT AccountCreditLimit
                                            FROM account
                                             WHERE AccountID = '$AccountID'");
        $currentlimit = mysql_result($lookupcurrentlimit,0,"AccountCreditLimit");
        $newlimit = $currentlimit;

        $lookuplimits = mysql_query("SELECT creditlimits.*
                                     FROM creditlimits,account
                                      WHERE creditlimits.AccountTypeID = account.AccountTypeID
                                       AND account.AccountID = '$AccountID'
                                        AND CreditLimit > '$currentlimit'
                                         ORDER BY TradeVolume");
        while($limits = mysql_fetch_array($lookuplimits))
        {
                if($limits["TradeVolume"] <= $volume)
                {
                        $newlimit = $limits["CreditLimit"];
                        }
                }

        if($newlimit != $currentlimit)
        {
                mysql_query("UPDATE account
                             SET AccountCreditLimit = '$newlimit'
                             WHERE AccountID = '$AccountID'");
                }
        return($newlimit);
        }

/* if this is loaded directly rather than from mailing.php, refer the user
back to mailing.php

$file = substr($HTTP_REFERRER,strrpos($HTTP_REFERRER,'/'),-1);

if($file != '/mailing.php')
{
        header("Location: mailing.php");
        }

/* print the confirmation page */

$title = "Message Request Received";
include "header.php";

print ("<h2>Message Request Confirmation</h2>
        The system has received your message request.<p>
        When the mailing is complete, the mailing addresses for the contacts of accounts not receiving this e-mail, as well as all information needed for a mailing to those accounts will be printed to <a href=tmp/>the temp directory</a>.  <strong>Please remember to delete these files when you have completed using them</strong><p>
        The information being processed is as follows:<p>
        ");
#include "footer.php";
if(empty($HTTP_POST_VARS))
{
        print "No Data Received";
        }

while(list($Key,$var) = each($HTTP_POST_VARS))
{
        print "$Key = $var<br>\n";
        }

/* Now process the mailing */

/* Lookup the start date and end date values (start date should be looked up
from the adminactions table to ensure that nothing gets missed...)*/

$BeginDateLookup = mysql_query("SELECT DATE_FORMAT(Time,'%Y-%m-%d') AS Time
                                 FROM adminactions
                                 WHERE Action = 'Account Statements Mailed'
                                 ORDER BY Time DESC
                                 LIMIT 1");
if(mysql_num_rows($BeginDateLookup) == 0)
{
        $BeginDate = '2001-09-01';
        }
else
{
        $BeginDate = mysql_result($BeginDateLookup, 0, 'Time');
        }
$EndDate = date("Y-m-d");
$Date = $EndDate;

/* Create the necessary global attachments (membership list, ad directory) */

/* Membership List Creation */

if(!empty($MemberList))
{
        include "txtmemberlist.php";
        }

/* Ad Directory Creation */

if(!empty($Directory))
{
        include "txtdirectory.php";
        }

/* Prepare the message (without Account Statement) */

$headers = '';

$boundary = "====webLETSoftware." . md5(uniqid(time())) . "====";
//$headers = "Followup: $Priority\r\n";
$headers .= "From: $SystemEmail\r\n";
$headers .= "MIME-Version:1.0\r\n";
$headers .= "Content-Type: multipart/mixed;\r\n\tboundary=\"$boundary\"\r\n\r\n";

/* Here's the text that was submitted */

$Text = stripslashes($Text);

$body = "--" . $boundary . "\r\n";
$body .= "Content-Type: text/plain;\r\n\tcharset=\"us-ascii\"\r\n";
$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$body .= "$Text\r\n\r\n";

/* And each submitted attachment */

for($i=0;$i<count($Attachment);$i++)
{
        if(!empty($Attachment["$i"]) && $Attachment["$i"] != 'none')
        {
                $fp = fopen($Attachment["$i"],"rb");
                $data = fread($fp, filesize($Attachment["$i"]));
                $data = chunk_split(base64_encode($data));
                fclose($fp);

                $body .= "--" . $boundary . "\r\n";
                $body .= "Content-Type: " . $Attachment_type["$i"] . ";\r\n\tname=\"" . $Attachment_name["$i"] . "\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n";
                $body .= "Content-Disposition: attachment; \r\n\tfilename=\"" . $Attachment_name["$i"] . "\"\r\n\r\n";
                $body .= $data . "\r\n\r\n";
                }
        }

/* Check to see if a mailing list is required */

if(!empty($MemberList))
{
        //$attachlist = file("memberlist.txt");
        $attachlist = file("tmp/memberlist.txt");
        $attachlist = implode($attachlist, "\n");
        $body .= "--" . $boundary . "\r\n";
        $body .= "Content-Type: text;\r\n\tcharset=\"us-ascii\"\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n";
        $body .= "Content-Disposition: attachment; \r\n\tfilename=\"PhoneList.$Date.txt\"\r\n\r\n";
        $body .= $attachlist . "\r\n\r\n";
        }


/* Check to see if an ad directory is required */

if(!empty($Directory))
{
        $attachads = file("tmp/directory.txt");
        $attachads = implode($attachads, "\n");
        $body .= "--" . $boundary . "\r\n";
        $body .= "Content-Type: text;\r\n\tcharset=\"us-ascii\"\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n";
        $body .= "Content-Disposition: attachment; \r\n\tfilename=\"Directory.$Date.txt\"\r\n\r\n";
        $body .= $attachads . "\r\n\r\n";
        }

/* if there is a charge, look up a transaction ID, and set up the system
account transaction record */

if(empty($NoFee))
{
        $now = time();
        mysql_query("INSERT INTO transidlookup
                     VALUES('','$now','$MemberID')");
        $TransIDLookup = mysql_query("SELECT TransactionID
                                        FROM transidlookup
                                        WHERE Time = '$now'
                                        AND MemberID = '$MemberID'");
        print mysql_error();
        $TransID = mysql_result($TransIDLookup,0,'TransactionID');
        mysql_query("DELETE FROM transidlookup
                      WHERE TransactionID = '$TransID'");
        $now = date("Y-m-d");
        $SystemBalanceLookup = mysql_query("SELECT CurrentBalance
                                             FROM transactions
                                              WHERE AccountID = '$SystemAccountID'
                                              ORDER BY Reference DESC
                                              LIMIT 1");
        print mysql_error();
        $SystemBalance = mysql_result($SystemBalanceLookup,0,'CurrentBalance');
        mysql_query("INSERT INTO transactions
                      SET TransactionID = '$TransID',
			TradeDate = '$now',
			AccountID = '$SystemAccountID',
			Amount = '0',
			Description = 'System Mailing',
			CurrentBalance = '$SystemBalance',
			OtherAccountID = '0'");
        print mysql_error();
        $MailingValue = '0';
        }



/* Now look up all the members that wish to receive a mailing by e-mail */

if(empty($Everyone))
{
         $EmailLookup = mysql_query("SELECT member.MemberID, EmailAddress, DeliveryMethodCost
                            FROM member, deliverymethodoptions,membertoaccountlink,account
                            WHERE member.DeliveryMethodID = deliverymethodoptions.DeliveryMethodID
                            AND member.MemberID = membertoaccountlink.MemberID
			    AND membertoaccountlink.AccountID = account.AccountID
			    AND AccountStatus != 'Closed'
			    AND DeliveryMethodName = 'Email'");
         }
else
{
         $EmailLookup = mysql_query("SELECT member.MemberID, EmailAddress, DeliveryMethodCost
                            FROM member,membertoaccountlink,account, deliverymethodoptions
			    WHERE member.MemberID = membertoaccountlink.MemberID
			    AND membertoaccountlink.AccountID = account.AccountID
			    AND member.DeliveryMethodID = deliverymethodoptions.DeliveryMethodID
			    AND AccountStatus != 'Closed'
                            AND EmailAddress is NOT NULL");

         }

/* Process the e-mail for each member found */

while($Email = mysql_fetch_array($EmailLookup))
{
        $mailbody = $body;
/* Look up the accounts (for creating account statements and processing
charges if contact is named as primary) */
        $Accounts = mysql_query("SELECT account.AccountID,AccountCreditLimit,AccountName,AccountTypeID,PrimaryContact
                                 FROM membertoaccountlink,account
                                 WHERE membertoaccountlink.AccountID = account.AccountID
                                 AND MemberID = '$Email[MemberID]'
                                 AND AccountRenewalDate > CURDATE()");
        print mysql_error();

        while($Acct = mysql_fetch_array($Accounts))
        {

// Process the fee first...

                if((empty($NoFee)) && ($Acct["PrimaryContact"] == 1) && ($Acct["AccountCreditLimit"] != '0.00'))
                {
                        $SystemBalance = $SystemBalance + $Email["DeliveryMethodCost"];
                        $MailingValue = $MailingValue + $Email["DeliveryMethodCost"];

                        $AcctBalanceLookup=mysql_query("SELECT CurrentBalance
                                                         FROM transactions
                                                         WHERE AccountID = '$Acct[AccountID]'
                                                         ORDER BY Reference DESC
                                                         LIMIT 1");
                        $AcctBalance = mysql_result($AcctBalanceLookup,0,'CurrentBalance');
                        $AcctBalance = $AcctBalance - $Email["DeliveryMethodCost"];

                        mysql_query("INSERT INTO transactions
                                      SET TransactionID = '$TransID',
					TradeDate = '$EndDate',
					AccountID = '$Acct[AccountID]',
					Amount = '-$Email[DeliveryMethodCost]',
					Description = 'System Mailing',
					CurrentBalance = '$AcctBalance',
					OtherAccountID = '$SystemAccountID',
					SystemFee = '1'");
                        mysql_query("UPDATE transactions
                                     SET CurrentBalance='$SystemBalance',Amount='$MailingValue'
                                     WHERE AccountID = '$SystemAccountID'
                                     AND TransactionID = '$TransID'");
                        }

/* Create the account statement, if required */

                if(!empty($AccountStatement))
                {
                        $statement = "";
                        $statement .= "<html><head>\n<title> Transaction History for Account #$Acct[AccountID]</title>\n";
                        $statement .= "<LINK REL=StyleSheet HREF=system.css TYPE='text/css' TITLE='LETS System Stylesheet'>\n";
                        $statement .= "</head><body>\n<center><h2>$Systemname Account Trading History</h2>\n<p><hr width=60%><p></center>\n";

                        /* print the account details */

                        $statement .= "<table noborder width=100% cellspacing=0>\n";
                        $statement .= "<tr><th colspan=7 bgcolor=#D3D3D3>Account Details</th></tr>\n";
                        $statement .= "<tr><th colspan=2 align=left>Account Name:</th>\n";
                        $statement .= "<td colspan=5>$Acct[AccountName]</td></tr>\n";
                        $statement .= "<tr><th colspan=2 align=left>Account Number:</th>\n";
                        $statement .= "<td colspan=5>$Acct[AccountID]</td></tr>\n";

                        $lookuptype = mysql_query("SELECT * FROM accounttypeoptions
                                                    WHERE AccountTypeID = '$Acct[AccountTypeID]'");

                        $accounttype = mysql_result($lookuptype,0,"AccountTypeName");

                        $creditlimit = CheckCreditLimit($Acct["AccountID"]);

                        $statement .= "<tr><th colspan=2 align=left>Account Type:</th>\n";
                        $statement .= "<td colspan=5>$accounttype</td></tr>\n";
                        $statement .= "<tr><th colspan=2 align=left>Credit Limit: </th>\n";
                        $statement .= "<td colspan=5>$creditlimit</td></tr>\n";

                        $lookupfactor = mysql_query("SELECT UpperCreditLimitFactor
                                                      FROM administration");
                        $factor = mysql_result($lookupfactor,0,"UpperCreditLimitFactor");
                        $maxbalance = $creditlimit * $factor;

                        $statement .= "<tr><th colspan=2 align=left>Maximum Balance:</th>\n";
                        $statement .= "<td colspan=5>$maxbalance</td></tr>\n";

                        $lookuptotals = mysql_query("SELECT (COUNT(DISTINCT(TransactionID))-1) AS Trades, SUM(ABS(Amount)) AS Volume
                                                      FROM transactions
                                                       WHERE AccountID = '$Acct[AccountID]'");
                        $total = mysql_fetch_array($lookuptotals);

                        $statement .= "<tr><th colspan=2 align=left>Total Trades:</th>\n";
                        $statement .= "<td colspan=5>$total[Trades]</td></tr>\n";
                        $statement .= "<tr><th colspan=2 align=left>Total Volume:</th>\n";
                        $statement .= "<td colspan=5>$total[Volume]</td></tr>\n";
                        $statement .= "</table><table noborder width=100%>\n";

                        /* lookup and print the transaction history */

                        $statement .= "<tr><th colspan=8 bgcolor=#D3D3D3>Transaction History From $BeginDate to $EndDate</th></tr>\n";
                        $statement .= "<tr><th align=left><font size='-1'>Date</th>\n";
                        $statement .= "<th align=left><font size='-1'>ID</th>\n";
                        $statement .= "<th align=left><font size='-1'>Trader</th>\n";
                        $statement .= "<th align=left><font size='-1'>Trade Description</th>\n";
                        $statement .= "<th align=left><font size='-1'>Credit</th>\n";
                        $statement .= "<th align=left><font size='-1'>Debit</th>\n";
                        $statement .= "<th align=left><font size='-1'>Fee</th>\n";
			$statement .= "<th align=left><font size='-1'>Balance</th></tr>\n";

                        $colorindex = 1;

                        $lookuptransactions = mysql_query("SELECT * FROM transactions
                                                            WHERE AccountID = '$Acct[AccountID]'
                                                             AND TradeDate BETWEEN '$BeginDate' AND '$EndDate'
							     AND Description != 'Transaction Fee'
                                                             ORDER BY TradeDate, TransactionID, ABS(Amount) DESC");

                        while($t = mysql_fetch_array($lookuptransactions))
                        {

				$FeeLookup = mysql_query("SELECT ABS(Amount) AS Amount,CurrentBalance
								FROM transactions
								WHERE AccountID = '$Acct[AccountID]'
								AND Description = 'Transaction Fee'
								AND TransactionID = '$t[TransactionID]'");
				$TF = mysql_result($FeeLookup,0,'Amount');
				$Balance = mysql_result($FeeLookup,0,'CurrentBalance');

                                $Description = stripslashes("$t[Description]");
				
				$colorindex = -$colorindex;
                                if($colorindex > 0) {$statement .= "<tr>";}
                                else {$statement .= "<tr bgcolor=#F0F0F0>";}

                                $statement .= "<td><font size='-1'>$t[TradeDate]</td>\n";
                                $statement .= "<td><font size='-1'>$t[TransactionID]</td>\n";
                                $statement .= "<td><font size='-1'>$t[OtherAccountID]</td>\n";
                                $statement .= "<td><font size='-1'>$Description</td>\n";

                                if($t["Amount"] >= 0)
                                {
                                        $statement .= "<td><font size='-1'>$t[Amount]</td>\n<td>&nbsp;</td>\n";
                                        }
                                else
                                {
                                        $statement .= "<td>&nbsp;</td>\n<td><font size='-1'>$t[Amount]</td>\n";
                                        }
				$statement .= "<td><font size='-1'>$TF</td>";
				if($Balance == '') $statement .= "<td><strong><font size='-1'>$t[CurrentBalance]</strong></td></tr>\n";
                                else $statement .= "<td><strong><font size='-1'>$Balance</td></tr>";
				}
                        $statement .= "</font><tr><th colspan=7 bgcolor=#D3D3D3>&nbsp;</th></tr></table>\n</body></html>\n";

                        $mailbody .= "--" . $boundary . "\r\n";
                        $mailbody .= "Content-Type: text/html;\r\n\tcharset=\"us-ascii\"\r\n";
                        $mailbody .= "Content-Transfer-Encoding: 7bit\r\n";
                        $mailbody .= "Content-Disposition: attachment; \r\n\tfilename=\"Account.$Acct[AccountID].$EndDate.html\"\r\n\r\n";
                        $mailbody .= $statement . "\r\n\r\n";
                        }

                }


        $mailbody .= "--" . $boundary . "\r\n";
        mail("$Email[EmailAddress]","$Subject","$mailbody","$headers");
        }


// look up all other accounts, and create a csv address file

$CSV = fopen("tmp/mailing.csv",'w');
$MailLookup = mysql_query("SELECT membertoaccountlink.AccountID,MemberFirstName,MemberLastName, MailingAddress1,MailingAddress2, MailingCity,MailingProvince,MailingPostalCode,DeliveryMethodCost
                            FROM member, deliverymethodoptions, membertoaccountlink, account
                            WHERE member.DeliveryMethodID = deliverymethodoptions.DeliveryMethodID
                            AND member.MemberID = membertoaccountlink.MemberID
                            AND membertoaccountlink.AccountID = account.AccountID
                            AND PrimaryContact = '1'
                            AND DeliveryMethodName = 'Direct Mail'
                            AND AccountRenewalDate > CURDATE()
                            ORDER BY membertoaccountlink.AccountID");
while($Mail = mysql_fetch_array($MailLookup))
{
        $CSVData = "\"$Mail[AccountID]\",\"$Mail[MemberFirstName] $Mail[MemberLastName]\",\"$Mail[MailingAddress1]\",\"$Mail[MailingAddress2]\",\"$Mail[MailingCity]\",\"$Mail[MailingProvince]\",\"$Mail[MailingPostalCode]\"\n";
        fputs($CSV, "$CSVData");

// process the charge for the land mail...

        if(empty($NoFee))
        {
                $SystemBalance = $SystemBalance + $Mail["DeliveryMethodCost"];
                $MailingValue = $MailingValue + $Mail["DeliveryMethodCost"];

                $AcctBalanceLookup=mysql_query("SELECT CurrentBalance
                                                 FROM transactions
                                                 WHERE AccountID = '$Mail[AccountID]'
                                                 ORDER BY Reference DESC
                                                 LIMIT 1");
                $AcctBalance = mysql_result($AcctBalanceLookup,0,'CurrentBalance');
                $AcctBalance = $AcctBalance - $Mail["DeliveryMethodCost"];

                mysql_query("INSERT INTO transactions
                              SET TransactionID = '$TransID',
				TradeDate = '$EndDate',
				AccountID = '$Mail[AccountID]',
				Amount = '-$Mail[DeliveryMethodCost]',
				Description = 'System Mailing',
				CurrentBalance = '$AcctBalance',
				OtherAccountID = '$SystemAccountID',
				SystemFee = '1'");
                mysql_query("UPDATE transactions
                             SET CurrentBalance='$SystemBalance',Amount='$MailingValue'
                             WHERE AccountID = '$SystemAccountID'
                             AND TransactionID = '$TransID'");
                }

// create (and save) the accountstatement for each person

       if(!empty($AccountStatement))
       {
               $Account = mysql_query("SELECT account.AccountID,AccountName,AccountTypeID,PrimaryContact
                                 FROM membertoaccountlink,account
                                 WHERE membertoaccountlink.AccountID = account.AccountID
                                 AND account.AccountID = '$Mail[AccountID]'");
               print mysql_error();

               $Acct = mysql_fetch_array($Account);

               $statement = "";
               $statement .= "<html><head>\n<title> Transaction History for Account #$Acct[AccountID]</title>\n";
               $statement .= "<LINK REL=StyleSheet HREF=system.css TYPE='text/css' TITLE='LETS System Stylesheet'>\n";
               $statement .= "</head><body>\n<center><h2>$Systemname Account Trading History</h2>\n<p><hr width=60%><p></center>\n";

               /* print the account details */

               $statement .= "<table noborder width=100% cellspacing=0>\n";
               $statement .= "<tr><th colspan=7 bgcolor=#D3D3D3>Account Details</th></tr>\n";
               $statement .= "<tr><th colspan=2 align=left>Account Name:</th>\n";
               $statement .= "<td colspan=5>$Acct[AccountName]</td></tr>\n";
               $statement .= "<tr><th colspan=2 align=left>Account Number:</th>\n";
               $statement .= "<td colspan=5>$Acct[AccountID]</td></tr>\n";

               $lookuptype = mysql_query("SELECT * FROM accounttypeoptions
                                           WHERE AccountTypeID = '$Acct[AccountTypeID]'");

               $accounttype = mysql_result($lookuptype,0,"AccountTypeName");

               $creditlimit = CheckCreditLimit($Acct["AccountID"]);

               $statement .= "<tr><th colspan=2 align=left>Account Type:</th>\n";
               $statement .= "<td colspan=5>$accounttype</td></tr>\n";
               $statement .= "<tr><th colspan=2 align=left>Credit Limit: </th>\n";
               $statement .= "<td colspan=5>$creditlimit</td></tr>\n";

               $lookupfactor = mysql_query("SELECT UpperCreditLimitFactor
                                             FROM administration");
               $factor = mysql_result($lookupfactor,0,"UpperCreditLimitFactor");
               $maxbalance = $creditlimit * $factor;

               $statement .= "<tr><th colspan=2 align=left>Maximum Balance:</th>\n";
               $statement .= "<td colspan=5>$maxbalance</td></tr>\n";

               $lookuptotals = mysql_query("SELECT (COUNT(DISTINCT(TransactionID))-1) AS Trades, SUM(ABS(Amount)) AS Volume
                                             FROM transactions
                                              WHERE AccountID = '$Acct[AccountID]'");
               $total = mysql_fetch_array($lookuptotals);

               $statement .= "<tr><th colspan=2 align=left>Total Trades:</th>\n";
               $statement .= "<td colspan=5>$total[Trades]</td></tr>\n";
               $statement .= "<tr><th colspan=2 align=left>Total Volume:</th>\n";
               $statement .= "<td colspan=5>$total[Volume]</td></tr>\n";
               $statement .= "</table><table noborder width=100%>\n";

               /* lookup and print the transaction history */

               $statement .= "<tr><th colspan=8 bgcolor=#D3D3D3>Transaction History From $BeginDate to $EndDate</th></tr>\n";
               $statement .= "<tr><th align=left><font size='-1'>Date</th>\n";
               $statement .= "<th align=left><font size='-1'>ID</th>\n";
               $statement .= "<th align=left><font size='-1'>Trader</th>\n";
               $statement .= "<th align=left><font size='-1'>Trade Description</th>\n";
               $statement .= "<th align=left><font size='-1'>Credit</th>\n";
               $statement .= "<th align=left><font size='-1'>Debit</th>\n";
               $statement .= "<th align=left><font size='-1'>Fee</th>\n";
	       $statement .= "<th align=left><font size='-1'>Balance</th></tr>\n";

               $colorindex = 1;

               $lookuptransactions = mysql_query("SELECT * FROM transactions
                                                   WHERE AccountID = '$Acct[AccountID]'
						   AND Description != 'Transaction Fee'
                                                    AND TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                                    ORDER BY TradeDate, TransactionID, ABS(Amount) DESC");

               while($t = mysql_fetch_array($lookuptransactions))
               {

		       $FeeLookup = mysql_query("SELECT ABS(Amount) AS Amount,CurrentBalance
						       FROM transactions
						       WHERE AccountID = '$Acct[AccountID]'
						       AND Description = 'Transaction Fee'
						       AND TransactionID = '$t[TransactionID]'");				$TF = mysql_result($FeeLookup,0,'Amount');
		       $Balance = mysql_result($FeeLookup,0,'CurrentBalance');
		       $Fee = mysql_result($FeeLookup,0,'Amount');

		       $Description = stripslashes("$t[Description]");
			
                       $colorindex = -$colorindex;
                       if($colorindex > 0) {$statement .= "<tr>";}
                       else {$statement .= "<tr bgcolor=#F0F0F0>";}

                       $statement .= "<td><font size='-1'>$t[TradeDate]</td>\n";
                       $statement .= "<td><font size='-1'>$t[TransactionID]</td>\n";
                       $statement .= "<td><font size='-1'>$t[OtherAccountID]</td>\n";
                       $statement .= "<td><font size='-1'>$Description</td>\n";

                       if($t["Amount"] >= 0)
                       {
                               $statement .= "<td><font size='-1'>$t[Amount]</td>\n<td>&nbsp;</td>\n";
                               }
                       else
                       {
                               $statement .= "<td>&nbsp;</td>\n<td><font size='-1'>$t[Amount]</td>\n";
                               }
		       $statement .= "<td><font size='-1'>$Fee</td>";
                       if($Balance == '') $statement .= "<td><strong><font size='-1'>$t[CurrentBalance]</strong></td></tr>\n";
                       else $statement .= "<td><strong><font size='-1'>$Balance</strong></td></tr>\n";
		       }
               $statement .= "</font><tr><th colspan=7 bgcolor=#D3D3D3>&nbsp;</th></tr></table>\n</body></html>\n";

               $statementfile = fopen("tmp/Account_$Acct[AccountID].htm",'w');
               fputs($statementfile, "$statement");
               fclose($statementfile);
               }


        }
fclose($CSV);

// log the mailing in the adminactions table.


$log = 'System Mailing';
if(empty($NoFee))
{
        $log .= ' with processing fee';
        }
mysql_query("INSERT INTO adminactions
              VALUES (NULL,'$MemberID','$log')");
if(!empty($AccountStatement))
{
        mysql_query("INSERT INTO adminactions
                      VALUES (NULL,$MemberID,'Account Statements Mailed')");
        }


exit();

// Don't forget to add the stylesheet information
// create the monitor file

?>