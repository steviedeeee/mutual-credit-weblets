<?  #trade reversal
/******************************************************************************/
/*
                Written by:        Martin Settle
                Last Modified:     August 14, 2002
                Called by:
                Calls:             configuration.php
                                   connectdb.php
                                   header.php
                                   footer.php
                                   adminlogin.php
                Description:       Looks up the information about a particular
                                trade, and inputs the exact data in reverse
                                to "reverse" the trade

                Modification History:
                        June 11, 2002 - File Created
			Jan 16, 2003 - fix INSERT SQL to allow table structure
					modifications

*/
/******************************************************************************/


# required includes

include "configuration.php";
include "connectdb.php";
include "adminlogin.php";

# TransactionID selection function

function TransID($MemberID)
{
        $transIDtime = time();
        if(!mysql_query("INSERT INTO transidlookup
                          VALUES ('','$transIDtime','$MemberID')"))
        {
                print ("WARNING: Unable to look up transaction ID.  Current Balance Transfer will fail.\n");
                print mysql_error() . "\n";
                }
        $transIDlookup = mysql_query("SELECT TransactionID
                                        FROM transidlookup
                                        WHERE Time = $transIDtime
                                         AND MemberID = '$MemberID'");
        $Transaction = mysql_fetch_array($transIDlookup);
        mysql_query("DELETE FROM transidlookup WHERE Time = '$transIDtime' AND MemberID = '$MemberID'");
        return($Transaction["TransactionID"]);
        }


$title = "Trade Reversal";

# request transactionID

if(empty($TransactionID))
{
        include "header.php";
        print ("<h2>Trade Reversal</h2>
Please input the TransactionID of the trade you wish to have reversed.<p>
<form action='tradereversal.php' method=post>
<center>
<strong>TransactionID: </strong>
<input type=text name=TransactionID size=8><br>
<input type=submit value='Reverse Trade'><p>
</form>");
        include "footer.php";
        exit();
        }

# Confirm the reversal

if(empty($Confirm))
{
        $DetailsLookup = mysql_query("SELECT * FROM transactions WHERE TransactionID = '$TransactionID' AND Amount < 0 AND Description != 'Transaction Fee'");
        $Details = mysql_fetch_array($DetailsLookup);
        $BuyerLookup = mysql_query("SELECT AccountName FROM account WHERE AccountID = $Details[AccountID]");
        $Buyer = mysql_result($BuyerLookup, 0, 'AccountName');
        $SellerLookup = mysql_query("SELECT AccountName FROM account WHERE AccountID = $Details[OtherAccountID]");
        $Seller = mysql_result($SellerLookup, 0, 'AccountName');
        include "header.php";
        print ("<h2>Trade Reversal</h2>
Please confirm the following details:<p>
<table noborder>
<tr class=Banner><th colspan=2>Reverse Transaction #$TransactionID</th></tr>
<tr><th class=Data>Buyer Account:</th><td>$Details[AccountID] ($Buyer)</td></tr>
<tr><th class=Data>Seller Account:</th><td>$Details[OtherAccountID] ($Seller)</td></tr>
<tr><th class=Data>Description:</th><td>$Details[Description]</td></tr>
<tr><th class=Data>Amount:</th><td>$Details[Amount]</td></tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td colspan=2 align=center><form action=tradereversal.php method=post>
<input type=hidden name=TransactionID value=$TransactionID>
<input type=hidden name=Confirm value=1>
<input type=submit value='Confirm Reversal'>
</form></td></tr>
</table>");
        include "footer.php";
        exit();
        }

# Get a TransactionID for the Reversal

include "verifytrade.php";
$TransID = TransID($MemberID);

# Process the reversal

$TransLookup = mysql_query("SELECT * FROM transactions WHERE TransactionID = $TransactionID ORDER BY ABS(Amount) DESC");

while($Trans = mysql_fetch_array($TransLookup))
{
        $BalanceLookup = mysql_query("SELECT CurrentBalance FROM transactions WHERE AccountID=$Trans[AccountID] ORDER BY Reference DESC LIMIT 1");
        $Balance = mysql_result($BalanceLookup,0,'CurrentBalance');
        $NewBalance = $Balance - $Trans[Amount];
	$Date = date("Y-m-d");
        if($Trans["Description"] == 'Transaction Fee')
        {
                $Description = "Transaction $TransactionID fee reversal";
                }
        else
        {
                $Description = "Reversal of Transaction $TransactionID";
                }

        if($Trans["Amount"] < 0)
        {
                $type = 'credit';
                }
        else
        {
                $type = 'debit';
                }
	$Amount = -($Trans["Amount"]);
        if(!mysql_query("INSERT INTO transactions SET TransactionID = '$TransID',TradeDate = '$Date',AccountID = '$Trans[AccountID]',Amount = '$Amount',Description = '$Description',CurrentBalance = '$NewBalance',OtherAccountID = '$Trans[OtherAccountID]'"))
        {
                $ErrorMessage[] = "The system failed to $type account $Trans[AccountID] $Trans[Amount].";
                $SQLMessage[] = mysql_error();
                }
        else
        {
                $Success[] = "A $type of $Trans[Amount] was registered to Account $Trans[AccountID].";
                }
        }

include "header.php";
print ("<h2>Trade Reversal</h2>
The system has processed your request.  The following results were received:
<ul>");

if(!empty($ErrorMessage))
{
	while(list($key,$var) = each($ErrorMessage))
	{
		print ("<li><strong>$var</strong> The database returned the following error: <i>$SQLMessage[$key]</i>\n");
		}
	}

if(!empty($Success))
{
	while(list($key,$var) = each($Success))
	{
		print ("<li><strong>$var</strong>\n");
		}
	}

print ("</ul>\n");
include "footer.php";

?>
