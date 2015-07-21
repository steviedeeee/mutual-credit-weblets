<?
/******************************************************************************/
/*
                Written by:        Martin Settle
                Last Modified:        February 19,2002
                Called by:        various scripts
                Calls:                configuration.php
                                connectdb.php
                                header.php
                                footer.php
                Description:        This file prints out a description of an
                                account, including members, descriptions, and
                                primary contact information.

                Modification History:
                                February 19,2002 - File Created

*/
/******************************************************************************/

/* Standard includes */

include "configuration.php";
include "connectdb.php";

/* Check valid member */

if(empty($MemberID))
{
        $title = "Not logged in";
        include "header.php";
        print ("<h2>Authorization Required</h2>
                You must be logged in to a valid member account to view this page.  If you are a member, please log in using the links on the right.  For information about becoming a member, look under <a href=infopages.php>About $Systemname</a>");
        include "footer.php";
        exit();
        }

/* If there is no AccountID specified, print a lookup form */

if(empty($AccountID))
{
        $title = 'View Account Information';
        include "header.php";
        print ("<form action=accountinfo.php method=POST>
                Please enter the account number for which you wish to view information: <input type=text name=AccountID size=3> <input type=submit value='Show Info'></form><p>");
        include "footer.php";
        exit();
        }

/* and print the page with the info */

$title = "Account $AccountID Information";
include "header.php";

/* Get all the account information, and print it... */

print ("<h2><center>Account Information Page</center></h2>");

$AccountLookup = mysql_query("SELECT * FROM account
                              WHERE AccountID = '$AccountID'");

print mysql_error();

$account = mysql_fetch_array($AccountLookup);

print ("<table noborder width=100% cellspacing=0>
        <tr><th colspan=2 class=Banner><font size=+1>Account Details</th></tr>
        <tr><th align=left>Account Name:</th>
        <td width=75%>$account[AccountName]</td>
        </tr>
        <tr><th align=left>Account Number:</th>
        <td>$account[AccountID]</td></tr>");

$lookuptype = mysql_query("SELECT * FROM accounttypeoptions
                            WHERE AccountTypeID = '$account[AccountTypeID]'");
$accounttype = mysql_result($lookuptype,0,"AccountTypeName");

print ("<tr><th align=left>Account Type:</th>
        <td>$accounttype</td></tr>
        <tr><th align=left>Credit Limit: </th>
        <td>$account[AccountCreditLimit]</td></tr>\n");

$lookupfactor = mysql_query("SELECT UpperCreditLimitFactor
                              FROM administration");
$factor = mysql_result($lookupfactor,0,"UpperCreditLimitFactor");
$maxbalance = $account["AccountCreditLimit"] * $factor;

print ("<tr><th align=left>Maximum Balance:</th>
        <td>$maxbalance</td></tr>\n");

$lookuptotals = mysql_query("SELECT (COUNT(DISTINCT(TransactionID))-1) AS Trades, SUM(ABS(Amount)) AS Volume
                              FROM transactions
                               WHERE AccountID = '$account[AccountID]'");
print mysql_error();
$total = mysql_fetch_array($lookuptotals);
$lookuplastrecord = mysql_query("SELECT MAX(Reference) AS Reference
                                 FROM transactions
                                   WHERE AccountID = '$account[AccountID]'");
$lastrecord = mysql_result($lookuplastrecord, '0', "Reference");
$lookupbalance = mysql_query("SELECT CurrentBalance
                             FROM transactions
                               WHERE AccountID = '$account[AccountID]'
                                AND Reference = '$lastrecord'");
$balance = mysql_fetch_array($lookupbalance);


print ("<tr><th align=left>Total Trades:</th>
        <td>$total[Trades]</td></tr>
        <tr><th align=left>Total Volume:</th>
        <td>$total[Volume]</td></tr>
       <tr><th align=left>Current Balance:</th>
       <td>$balance[CurrentBalance]</td></tr>
       </table>");

/* now get the all membership information */

print ("<p>
        <table width=100% noborder cellspacing=0>
        <tr><th class=Banner colspan=2><font size=+1>Account Membership</th></tr>\n");

$MembersLookup = mysql_query("SELECT * FROM member,membertoaccountlink
                              WHERE AccountID = '$AccountID'
                              AND member.MemberID = membertoaccountlink.MemberID
                              ORDER BY PrimaryContact DESC, MemberLastName,MemberFirstName");
if(mysql_num_rows($MembersLookup) == 0)
{
        print ("<tr><td colspan=2>This account has no active traders.<br></td></tr></table>\n");
        }
else
{
        while($Members = mysql_fetch_array($MembersLookup))
        {
                print ("<tr><th colspan=2 class=LightBanner><center>$Members[MemberFirstName] $Members[MemberLastName]</center></td></tr>
                        <tr><th align=left>Phone:</th><td>$Members[HomeNumber]</td></tr>
                        <tr><th align=left>Email:</th><td><a href=\"mailto:$Members[EmailAddress]\">$Members[EmailAddress]</a></td></tr>
                        <tr><th align=left valign=top>Profile:</th><td>$Members[Profile]</td></tr>
                        <tr><td colspan=2>&nbsp;</td></tr>");
                }
        print ("</table>");
        }


/* And now look up and print the account advertisements */

print ("<table noborder width=100% cellspacing=0>
        <tr><th class=Banner><font size=+1>Account Advertisements</th></tr>\n");

$today = date("Y-m-d");

$query = 'SELECT * ';
$query .= 'FROM adheadings, adcategories, advertisements ';
$query .= 'WHERE (advertisements.CategoryID = adcategories.CategoryID OR advertisements.CategoryID2 = adcategories.CategoryID OR advertisements.CategoryID3 = adcategories.CategoryID)';
$query .= 'AND adcategories.HeadingID = adheadings.HeadingID ';
$query .= "AND  AdExpiryDate >= '$today' ";
$query .= "AND  AdBeginDate <= '$today' ";
$query .= "AND AccountID = '$AccountID'";
$query .= 'ORDER BY HeadingName, CategoryName, TradeType, AdName';

$AdLookup = mysql_query("$query");

if(mysql_num_rows($AdLookup) == 0)
{
        print ("<tr><td><strong>There are no active advertisements listed for this account.</strong></td></tr>");
        }
else
{
        while($Ad = mysql_fetch_array($AdLookup))
        {
                 print ("<tr><th class=LightBanner>
                         <table width=100% cellspacing=0><tr><th align=left>$Ad[AdName]</th><td align=right>");
                 if($Ad["TradeType"] == 'O')
                 {
                         print ("OFFERED");
                         }
                 else
                 {
                         print ("REQUESTED");
                         }
                 print ("</td></tr></table>
                         </th></tr>
                         <tr><td>$Ad[AdDescription]</td></tr>
                         <tr><td><table noborder cellspacing=0>
                                  <tr><th align=left><font size=-1>Category: </th><td><font size=-1>$Ad[HeadingName] - $Ad[CategoryName]</td></tr>
                                  <tr><th align=left><font size=-1>Posted on:&nbsp;&nbsp; </th><td><font size=-1>$Ad[AdBeginDate]</td></tr>
                                  </table></td></tr>
                         <tr><td>&nbsp;</td></tr>");
                 }
        }

print ("</table>");

/* and End the file */

include "footer.php";
?>