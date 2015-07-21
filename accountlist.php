<?
/******************************************************************************/
/*
                Written by:        Martin Settle
                Last Modified:        January 30,2002
                Called by:        accountlookup.php
                Calls:                configuration.php
                                connectdb.php
                                header.php
                                footer.php
                Description:        This file prints out an ordered list of all
                                existing accounts, with a link to contact info

                Modification History:
                                January 30,2002 - File Created

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

/* Look up the CreditLimitFactor */

$FactorLookup = mysql_query("SELECT UpperCreditLimitFactor
                                FROM administration");
$Factor = mysql_result($FactorLookup,0,'UpperCreditLimitFactor');
mysql_free_result($FactorLookup);

/* Start the page */

$title= 'Account Listing';
include "header.php";
print ("<h2>$Systemname Account List</h2>
        This page lists all accounts in the $Systemname system, ranked by Current Balance.  The balances of accounts that have surpassed their trading limits are listed in red.  Please do not initiate trading that would further exacerbate credit issues.<p>
        <table noborder width=100% cellspacing=0>
        <tr><th bgcolor=#D3D3D3 align=left>ID & Name</th>
        <th bgcolor=#D3D3D3 align=left>Type</th>
        <th bgcolor=#D3D3D3 align=left>Volume</th>
        <th bgcolor=#D3D3D3 align=left>Last trade</th>
        <th bgcolor=#D3D3D3 align=left>Balance</th>
        </tr>");

/* Look up the list of Balances in descending order */

$Reference = '';
$TransactionsLookup = mysql_query("SELECT Max(Reference) AS Reference
                                        FROM transactions
                                        GROUP BY AccountID");
while($References = mysql_fetch_array($TransactionsLookup))
{
        $Reference .= $References["Reference"] . ",";
        }
$Reference = substr($Reference,0,-1);
$BalanceLookup = mysql_query("SELECT AccountID,CurrentBalance,TradeDate
                                FROM transactions
                                WHERE Reference IN ($Reference)
                                ORDER BY CurrentBalance DESC");

/* and now process each one and print it... */

while($Balance = mysql_fetch_array($BalanceLookup))
{
        $AccountLookup = mysql_query("SELECT AccountName,AccountTypeName,AccountCreditLimit
                                        FROM account,accounttypeoptions
                                        WHERE account.AccountTypeID = accounttypeoptions.AccountTypeID
                                        AND AccountID = '$Balance[AccountID]'
                                        AND account.AccountRenewalDate > CURDATE()");
        if(mysql_num_rows($AccountLookup) > 0)
        {
                $Account = mysql_fetch_array($AccountLookup);
                mysql_free_result($AccountLookup);

                $TotalLookup = mysql_query("SELECT SUM(ABS(Amount)) AS Total
                                                       FROM transactions
                                                WHERE AccountID = '$Balance[AccountID]'");
                $Total = mysql_result($TotalLookup,0,'Total');

                print ("<tr><td><a href='accountinfo.php?AccountID=$Balance[AccountID]'>$Balance[AccountID]. $Account[AccountName]</a></td>
                        <td>$Account[AccountTypeName]</td>
                        <td>$Total</td>
                        <td>$Balance[TradeDate]</td>\n");
                if(($Balance["CurrentBalance"] > ($Account["AccountCreditLimit"]*$Factor)) || ($Balance["CurrentBalance"] < -$Account["AccountCreditLimit"]))
                {
                        print "<td><font color=red>$Balance[CurrentBalance]</font></td>\n";
                        }
                else
                {
                        print "<td>$Balance[CurrentBalance]</td>\n";
                        }
                print ("</tr>\n");
                }
        }

print ("<tr><th class=Banner colspan=5>&nbsp;</th></tr>
        </table>\n");
include "footer.php";

?>