<?
/******************************************************************************/
/*
                Written by:     Martin Settle
                Last Modified:  January 13, 2002
                Called by:      Reportrequest.php
                Calls:          reports.inc
                                connectdb.php
                Description:    This file processes all request for system
                                reports

                Modification History:
                                January 19, 2002 - file created

*/
/******************************************************************************/

/* Here are all the includes */

include 'configuration.php';
include 'connectdb.php';
include 'reports.inc';

/* If no date has been specified, assume the request is for the previous
month */

if(empty($BeginDate))
{
        if(date("m") < 2)
        {
                $BeginDate = (date('Y') - 1) . '-12-01';
                }
        else
        {
                $BeginDate = date("Y-") . (date('m')-1) . '-01';
                }
        }

/* Now calculate the EndDate (if not specified) as the last day of the previous
month */

if(empty($EndDate))
{
        $Month = substr($BeginDate,5,-3);
        $Day = substr($BeginDate,-2,2);
        $Year = substr($BeginDate,0,4);
        $EndDate = date("Y-m-d",mktime(0,0,0,$Month+1,$Day-1,$Year));
        }

/* the variable $HTML will be used to evaluate whether any selections were
printed.  If not, the system will print a message asking the user to return
to the previous page to select report options */

$HTML = 0;

/* this little function will check to see if a section title has been printed.
If not, it prints one using the TH.Banner class */

function PrintBanner($Section)
{
        static $printed;
        if(empty($printed["$Section"]))
        {
                print ("<tr><th colspan=2 class=Banner>$Section</th></tr>\n");
                $printed["$Section"] = 1;
                }
        }

/* include the header file, if there is no command that the page be in
printable format */

if(empty($printable))
{
        $title = 'System Report';
        include "header.php";
        print "<form action=showreports.php method=POST target=Print>";
        }
else
{
        print ("<html>
                <head><title>$Systemname: System Report</title>
                <LINK REL=StyleSheet HREF=system.css TYPE='text/css' TITLE='LETS System Stylesheet'>
                </head>
                <body>\n");
        }
print ("<center><H1>$Systemname Trading Report</h1>
	<h3>For the period $BeginDate to $EndDate</h3></center>
	<table noborder width=100%>\n");

/* Now run queries, as applicable */

if(!empty($SystemBalance))
{
        PrintBanner('System and Trades');
        print ("<tr><th class=FormLabel>System Balance:</th><td>");
        print SystemBalance();
        print ("</td></tr>
                <input type=hidden name=SystemBalance value=yes>\n");
        $HTML++;
        }
if(!empty($TotalTrades))
{
        PrintBanner('System and Trades');
        print ("<tr><th class=FormLabel>Total Trades:</th><td>");
        print TotalTrades("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=TotalTrades value=yes>\n");
        $HTML++;
        }
if(!empty($TotalValue))
{
        PrintBanner('System and Trades');
        print ("<tr><th class=FormLabel>Volume transferred:</th><td>");
        print TotalValue("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=TotalValue value=yes>\n");
        $HTML++;
        }
if(!empty($TotalValueGST))
{
        PrintBanner('System and Trades');
        print ("<tr><th class=FormLabel>Value of Goods and Services:</th><td>");
        print TotalValueGST("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=TotalValueGST value=yes>\n");
        $HTML++;
        }
if(!empty($AverageTrade))
{
        PrintBanner('System and Trades');
        print ("<tr><th class=FormLabel>Average Trade Value:</th><td>");
        print AverageTrade("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=AverageTrade value=yes>\n");
        $HTML++;
        }
if(!empty($LargestTransaction))
{
        PrintBanner('System and Trades');
        print ("<tr><th class=FormLabel>Largest Transaction:</th><td>");
        print LargestTransaction("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=LargestTransaction value=yes>\n");
        $HTML++;
        }
if(!empty($TotalTransactionFee))
{
        PrintBanner('System and Trades');
        print ("<tr><th class=FormLabel>Total Transaction Fees Processed:</th><td>");
        print TotalTransactionFee("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=TotalTransactionFee value=yes>\n");
        $HTML++;
        }
if(!empty($NumberAccounts))
{
        PrintBanner('Accounts and Activity');
        print ("<tr><th class=FormLabel>Number of live Accounts:</th><td>");
        print NumberAccounts("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=NumberAccounts value=yes>\n");
        $HTML++;
        }
if(!empty($ActiveNumber))
{
        PrintBanner('Accounts and Activity');
        print ("<tr><th class=FormLabel>Number of Active Traders:</th><td>");
        print ActiveNumber("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=ActiveNumber value=yes>\n");
        $HTML++;
        }
if(!empty($ActiveTraders))
{
        PrintBanner('Accounts and Activity');
        print ("<tr><th class=FormLabel valign=top>Active Traders:</th><td>");
        print ActiveTraders("$BeginDate","$EndDate",'',"<br>\n");
        print ("</td></tr>
                <input type=hidden name=ActiveTraders value=yes>\n");
        $HTML++;
        }
if(!empty($InactiveNumber))
{
        PrintBanner('Accounts and Activity');
        print ("<tr><th class=FormLabel>Number of Inactive Traders:</th><td>");
        print InactiveNumber("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=InactiveNumber value=yes>\n");
        $HTML++;
        }
if(!empty($InactiveTraders))
{
        PrintBanner('Accounts and Activity');
        print ("<tr><th class=FormLabel valign=top>Inactive Traders:</th><td>");
        print InactiveTraders("$BeginDate","$EndDate","","<br>\n");
        print ("</td></tr>
                <input type=hidden name=InactiveTraders value=yes>\n");
        }
if(!empty($ValueTopTen))
{
        PrintBanner('Accounts and Activity');
        print ("<tr><th class=FormLabel valign=top>Top Ten Traders<br> by Trade Value:</th>
                <td><table noborder><tr><th>Account</th><th>Amount</th>\n");
        print ValueTopTen("$BeginDate","$EndDate","<tr><td>","</td><td>"," Ecos</td></tr>\n");
        print ("</table></td></tr>
                <input type=hidden name=ValueTopTen value=yes>\n");
        $HTML++;
        }
if(!empty($NumberTopTen))
{
        PrintBanner('Accounts and Activity');
        print ("<tr><th class=FormLabel valign=top>Top Ten Traders<br> by Number of Trades: </th>
                <td><table noborder><tr><th>Account</th><th>Amount</th>\n");
                print NumberTopTen("$BeginDate","$EndDate","<tr><th align=left>","</th><td>"," trades<td></tr>\n");
        print ("</table></td></tr>
                <input type=hidden name=NumberTopTen value=yes>\n");
        $HTML++;
        }
if(!empty($TradePartners))
{
        PrintBanner('Accounts and Activity');
        print ("<tr><th class=FormLabel valign=top>Top Ten Traders<br> by Number of Distinct Trading Partners: </th>
                <td><table noborder>
                <tr><th>Account Name and ID Number</th><th>Number of Trade Partners</th</tr>\n");
        print TradePartners("$BeginDate","$EndDate","<tr><td>","</td><td>","<td></tr>\n");
        print ("</table></td></tr>
                <input type=hidden name=TradePartners value=yes>\n");
        $HTML++;
        }
if(!empty($NumberAds))
{
        PrintBanner('Advertisements');
        print ("<tr><th class=FormLabel>Number of Advertisements:</th><td>");
        print NumberAds("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=NumberAds value=yes>\n");
        $HTML++;
        }
if(!empty($AccountsAds))
{
        PrintBanner('Advertisements');
        print ("<tr><th class=FormLabel>Number of Accounts with Ads:</th><td>");
        print AccountsAds("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=AccountsAds value=yes>\n");
        $HTML++;
        }
if(!empty($PercentAds))
{
        PrintBanner('Advertisements');
        print ("<tr><th class=FormLabel>Percentage of Accounts with Ads<br>recording trades:</th><td>");
        print PercentAds("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=PercentAds value=yes>\n");
        $HTML++;
        }
if(!empty($PercentNoAds))
{
        PrintBanner('Advertisements');
        print ("<tr><th class=FormLabel>Percentage of Accounts without Ads<br>recording trades:</th><td>");
        print PercentNoAds("$BeginDate","$EndDate");
        print ("</td></tr>
                <input type=hidden name=PercentNoAds value=yes>\n");
        $HTML++;
        }

/* check $HTML to see if we've done anything.  If not, print a message. */

if($HTML == 0)
{
        print ("</table><strong>OOPS!</strong><br>
                It appears that you forgot to select what options you want this report to show.<p>Please return to the <a href=reports.php>Report Request Form</a> and select the options you wish to have included on your report.<p>");
                include 'footer.php';
                exit();
        }

/* Wrap up the page */

print ("</table>");

if(empty($printable))
{
        print ("<p><center>
                <form action=showreports.php method=POST>
                <input type=hidden name=BeginDate value='$BeginDate'>
                <input type=hidden name=EndDate value='$EndDate'>
                <input type=hidden name=printable value=yes>
                <input type=submit value='Make this page printable'>
                </form></center><p>\n");
        include 'footer.php';
        }
else
{
        print "</html>";
        }

?>