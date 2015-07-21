<?
/******************************************************************************/
/*
                Written by:        Martin Settle
                Last Modified:        October 29, 2001
                Called by:        header.php
                Calls:                configuration.php
                                connectdb.php
                                header.php
                                footer.php
                Description:        This file prints out the trading history for
                                a user's Accounts, or in admin mode requests
                                an AccountID to show history of.

                Modification History:
                                October 29, 2001 - File Created
                                December 28, 2001 - link to MemberID removed
                                        File only links to AccountID now
                                                  - Printable version added
				January 16, 2003 - stripslashes on Description
						 - transaction fees as separate
							column

*/
/******************************************************************************/

/* get the includes out of the way */

include "configuration.php";
include "connectdb.php";

/* Deny any non-authorized user */

if(empty($MemberID))
{
        $title = "Authorization Required";
        include "header.php";
        print ("<h2>You are not Authorized to view this page</h2>
                In order to access this page, you must be an authorized member.  If you are a member of $Systemname, please ensure that you have correctly <a href=login.php>logged in</a>.<p>");
        include "footer.php";
        exit();
        }

/* This is a function to recalculate an Account's Credit Limit */

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

/* Get account information
:
   If Authorization code exists, but no account, print a form, otherwise look
   up the account information for the given AccountID

   Otherwise, look up the accounts associated with the MemberID */

if(!empty($AuthorizationCode))
{
        if(empty($AccountID))
        {
                $title = "Request Account History";
                include "header.php";
                print ("<h2>Access Account History</h2>
                        This system allows the administrative user to view the account history for any account in the LETS System.<p>
                        <form action=accounthistory.php method=post>
                        <table noborder>
                        <tr><th colspan=2 bgcolor=#D3D3D3>Account History Request</th></tr>
                        <tr><th align=left>Account Number: </th>
                        <td><input type=text name=AccountID size=5></td></tr>
                        <tr><th align=left>Begin Date:</th>\n");
                $begindate = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d"),date("Y")));
                $enddate = date("Y-m-d");
                print ("<td><input type=text name=BeginDate value='$begindate'></td></tr>
                        <tr><th align=left>End Date:</th>
                        <td><input type=text name=EndDate value='$enddate'></td></tr>
                        <tr><td colspan=2 align=right><input type=submit value='Show Statement'></td>
                        <tr><th colspan=2 bgcolor=#D3D3D3>&nbsp;</td></tr></table>");
                include "footer.php";
                exit();
                }
        }

$lookupaccounts = mysql_query("SELECT * FROM account
                                WHERE AccountID = '$AccountID'");

/* Start printing the page */

if(empty($Printable))
{
        $title = "Transaction history";
        include "header.php";
        }
else
{
        print ("<html><head>
                <title> Transaction History for Account #$AccountID</title>
                <LINK REL=StyleSheet HREF=system.css TYPE='text/css' TITLE='LETS System Stylesheet'>
                </head><body>\n");
        }
print ("<center><h2>$Systemname Account Trading History</h2>
        <p><hr width=60%><p></center>");

/* print the account details */

$account = mysql_fetch_array($lookupaccounts);
print ("<table noborder width=100% cellspacing=0>
        <tr><th colspan=7 bgcolor=#D3D3D3>Account Details</th></tr>
        <tr><th colspan=2 align=left>Account Name:</th>
        <td colspan=5>$account[AccountName]</td></tr>
        <tr><th colspan=2 align=left>Account Number:</th>
        <td colspan=5>$account[AccountID]</td></tr>");

$lookuptype = mysql_query("SELECT * FROM accounttypeoptions
                            WHERE AccountTypeID = '$account[AccountTypeID]'");

$accounttype = mysql_result($lookuptype,0,"AccountTypeName");

$creditlimit = CheckCreditLimit($account["AccountID"]);

print ("<tr><th colspan=2 align=left>Account Type:</th>
        <td colspan=5>$accounttype</td></tr>
        <tr><th colspan=2 align=left>Credit Limit: </th>
        <td colspan=5>$creditlimit</td></tr>\n");
$lookupfactor = mysql_query("SELECT UpperCreditLimitFactor
                              FROM administration");
$factor = mysql_result($lookupfactor,0,"UpperCreditLimitFactor");
$maxbalance = $creditlimit * $factor;

print ("<tr><th colspan=2 align=left>Maximum Balance:</th>
        <td colspan=5>$maxbalance</td></tr>\n");

$lookuptotals = mysql_query("SELECT (COUNT(DISTINCT(TransactionID))-1) AS Trades, SUM(ABS(Amount)) AS Volume
                              FROM transactions
                               WHERE AccountID = '$account[AccountID]'");
print mysql_error();
$total = mysql_fetch_array($lookuptotals);

print ("<tr><th colspan=2 align=left>Total Trades:</th>
        <td colspan=5>$total[Trades]</td></tr>
        <tr><th colspan=2 align=left>Total Volume:</th>
        <td colspan=5>$total[Volume]</td></tr>
        </table><table noborder width=100%>");

/* lookup and print the transaction history */

if(empty($BeginDate)) { $BeginDate = date("Y-m-d",mktime(0,0,0,date("m")-1,date("d"),date("Y"))); }
if(empty($EndDate)) { $EndDate = date("Y-m-d"); }
print ("<tr><th colspan=8 bgcolor=#D3D3D3><form action=accounthistory.php method=POST>Transaction History From <input type=text size=10 name=BeginDate value='$BeginDate'> to <input type=text size=10 name=EndDate value='$EndDate'>&nbsp;&nbsp;&nbsp;<input type=submit value='Refresh'><input type=hidden name=AccountID value='$AccountID'></form></th></tr>
        <tr><th align=left><font size='-1'>Date</th>
        <th align=left><font size='-1'>ID</th>
        <th align=left><font size='-1'>Trader</th>
        <th align=left><font size='-1'>Trade Description</th>
        <th align=left><font size='-1'>Credit</th>
        <th align=left><font size='-1'>Debit</th>
	<th align=left><font size='-1'>Fee</th>
        <th align=left><font size='-1'>Balance</th></tr>");

$colorindex = 1;

$lookuptransactions = mysql_query("SELECT * FROM transactions
                                    WHERE AccountID = '$account[AccountID]'
				    AND Description != 'Transaction Fee'
                                     AND TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                     ORDER BY TradeDate, TransactionID, ABS(Amount) DESC");

while($t = mysql_fetch_array($lookuptransactions))
{
	$FeesLookup = mysql_query("SELECT ABS(Amount) AS Amount, CurrentBalance 
					FROM transactions
					WHERE AccountID = '$account[AccountID]'
					AND TransactionID = '$t[TransactionID]'
					AND Description = 'Transaction Fee'");
	$TF = mysql_result($FeesLookup,0,'Amount');
	$Balance = mysql_result($FeesLookup,0,'CurrentBalance');
	$Description = stripslashes("$t[Description]");
        $colorindex = -$colorindex;
        if($colorindex > 0) {print "<tr>";}
        else {print "<tr bgcolor=#F0F0F0>";}
        print ("<td><font size='-1'>$t[TradeDate]</td>
                <td><font size='-1'>$t[TransactionID]</td>
                <td><font size='-1'><a href='accountinfo.php?AccountID=$t[OtherAccountID]'>$t[OtherAccountID]</a></td>
                <td><font size='-1'>$t[Description]</td>\n");
        if($t["Amount"] >= 0)
        {
                print "<td><font size='-1'>$t[Amount]</td>\n<td>&nbsp;</td>\n";
                }
        else
        {
                print "<td>&nbsp;</td>\n<td><font size='-1'>$t[Amount]</td>\n";
                }
	print "<td><font size='-1'>$TF</td>";
        if($Balance == '') print "<td><strong><font size='-1'>$t[CurrentBalance]</strong></td></tr>";
	else print "<td><strong><font size='-1'>$Balance</strong></td></tr>";
        }
print ("</font><tr><th colspan=7 bgcolor=#D3D3D3>&nbsp;</th></tr></table>
        <p><center><hr width=60%><p>");
if(empty($Printable))
{
        print("<form action=accounthistory.php method=POST>
                <input type=hidden name=AccountID value=$AccountID>
                <input type=hidden name=BeginDate value='$BeginDate'>
                <input type=hidden name=EndDate value='$EndDate'>
                <input type=hidden name=Printable value=1>
                <input type=submit value='Show a printable version of this page'>
                </form>");
        }

print ("</center>");

/* and we're done... include the footer. */

if(empty($Printable))
{
        include "footer.php";
        }
?>