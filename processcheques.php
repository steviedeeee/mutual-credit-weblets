<?

/******************************************************************************


		File Name:	processcheques.php
		Written By:	Martin Settle
		Date:		February 22, 2003
		Calls:		verifytrade.php, recordtrade.php

		Modification History:
			February 22, 2003 - file created

******************************************************************************/


function PrintForm($ChequeID,$Amount,$ToAccount, $Memo)
{

print ("<form action=processcheques.php method=POST>
<table noborder>
<tr><th align=left>Cheque Number:</th>
<td><input type=text name=ChequeID value='$ChequeID' size=5></td></tr>
<tr><th align=left>Cheque Amount:</th>
<td><input type=text name=Amount value='$Amount' size=7></td></tr>
<tr><th align=left>Cheque Memo:</th>
<td><input type=text name=Memo value='$Memo' size=60></td></tr>
<tr><th align=left>Written to:</th>");

if(!empty($GLOBALS["AuthorizationCode"]))
{

	print ("<td><select name=ToAccount><option value='$ToAccount'>");
	$GetAccounts = mysql_query("SELECT AccountID, AccountName FROM account WHERE AccountRenewalDate > CURDATE() AND AccountStatus != 'Closed' ORDER BY AccountID");
	while($Acct = mysql_fetch_array($GetAccounts))
	{
		print ("<option value='$Acct[AccountID]'>$Acct[AccountID]: $Acct[AccountName]</option>\n");
		}
	print ("</select>");

	}
else
{
	$AccountsLookup = mysql_query("SELECT account.AccountID AS AccountID, AccountName
					FROM membertoaccountlink, account
					WHERE membertoaccountlink.AccountID = account.AccountID
					AND account.AccountStatus != 'Closed'
					AND MemberID = $GLOBALS[MemberID]");

	switch(mysql_num_rows($AccountsLookup))
	{
		case 1:
			$Acct = mysql_fetch_array($AccountsLookup);
			print ("<td><input type=hidden name=ToAccount value=$Acct[AccountID]>$Acct[AccountID]: $Acct[AccountName]");
			break;
		default:
			print ("<td><select name=ToAccount><option value=$ToAccount>$ToAccount\n");
			while($Acct = mysql_fetch_array($AccountsLookup))
			{
				print ("<option value=$Acct[AccountID]>$Acct[AccountID]: $Acct[AccountName]\n");
				}
			print ("</select>");
		}
	}
print ("</td></tr>
<tr><td colspan=2 align=center><input type=submit value='Record Payment'></td></tr>
</table>");

}

# start program

include "configuration.php";
include "connectdb.php";

error_reporting(255);

if(empty($MemberID))
{
	$title = "Authorization Required";
	include "header.php";
	print ("<h2>Members Only</h2>
You must be logged in as an active member of $Systemname in order to access this page.  To log in, <a href=login.php>click here</a>.<p>");
	include "footer.php";
	exit();
	}

if(empty($ChequeID) || empty($Amount) || empty($Memo) || empty($ToAccount))
{
	if(empty($ChequeID)) $ChequeID = '';
	if(empty($Amount)) $Amount = '';
	if(empty($Memo)) $Memo = '';
	if(empty($ToAccount)) $ToAccount = '';
	$title = 'Record Payment by Cheque';
	include "header.php";
	PrintForm("$ChequeID","$Amount","$ToAccount","$Memo");
	include "footer.php";
	exit();
	}

# if we have everything, process

include "verifytrade.php";

$Amount = MakeCurrency($Amount);

if(NoCredit($ToAccount))
{
	$title = 'Unable to Process';
	include "header.php";
	print ("<h2>Sale not Valid</h2>
The sale could not be processed because the seller account (Account #$ToAccount) has no trading priveleges on $Systemname.<p>
<hr><p>/n");
	PrintForm('','','','');
	include "footer.php";
	exit();
	}

# verify cheque is good

$ChequeEntry = mysql_query("SELECT * FROM cheques WHERE ChequeID = $ChequeID");

if(mysql_num_rows($ChequeEntry) != 1)
{
	$title = 'Unable to Process';
	include "header.php";
	print ("<h2>Cheque not Valid</h2>
The submitted Cheque Number does not exist in the LETSystem Database.  Please verify the cheque number and try again.<p>\n");
	PrintForm('',"$Amount","$ToAccount","$Memo");
	include "footer.php";
	exit();
	}

$Cheque = mysql_fetch_array($ChequeEntry);
$AcctLookup = mysql_query("SELECT * FROM account WHERE AccountID = $Cheque[AccountID]");
$Acct = mysql_fetch_array($AcctLookup);

if(!empty($Cheque["TransactionID"]))
{
	$title = 'Unable to Process';
	include "header.php";
	print ("<h2>Cheque Previously Processed</h2>
A trade involving the identified cheque has already been posted.  <strong>Cheque #$ChequeID</strong>, issued to <strong>Account #$Cheque[AccountID]: $Acct[AccountName]</strong> was used in Transaction $Cheque[TransactionID], transferring ");
	$TransLookup = mysql_query("SELECT * FROM transactions WHERE TransactionID = '$Cheque[TransactionID]' AND AccountID = '$Cheque[AccountID]' AND Description != 'Transaction Fee'");
	$Trans = mysql_fetch_array($TransLookup);
	print ("$Trans[Amount] on $Trans[TradeDate]. The trade description is <em>$Trans[Description]</em>.
<p><hr><p>");
	PrintForm('','','','');
	include "footer.php";
	exit();
	}

$today = date("Y-m-d");
if($Acct["AccountRenewalDate"] < $today)
{
	$title = 'Unable to Process';
	include "header.php";
	print ("<h2>Chequing Account No Longer Active</h2>
Account #$Cheque[AccountID], on which this cheque was written, has expired without renewal.  This cheque cannot be processed.
<p><hr><p>\n");
	PrintForm('','','','');
	include "footer.php";
	exit();
	}



/* Cheque is valid, involved accounts can trade....
Check that the trade is within credit limits */

if(OverLimit('$Cheque["AccountID"]',$Amount,'Buyer'))
{
	if(WasWarned("$ToAccount","$Cheque[AccountID]",""))
	{
		$title = 'Unable to Process';
		include "header.php";
		print ("<h2>Insufficient Funds</h2>
The account on which this cheque was written, Account #$Cheque[AccountID]: $Acct[AccountName], is extended beyond its credit limit, and members of Account #$ToAccount have received a previous warning about making sales to the chequing account. <p>
Re-submission of this cheque is permitted once Account #$Cheque[AccountID]: $Acct[AccountName] has returned to within acceptable credit limits.");
		include "footer.php";
		exit();
		}
	if(!empty($AdminType)) mail();
	else $SellerWarning = "<strong>WARNING: the Account from which you received this cheque, #$Cheque[AccountID]: $Acct[AccountName], is over its limit";
	}

$testseller =OverLimit($ToAccount,$Amount, 'Seller');
switch($testseller)
{
	case 1:
		$title = 'Unable to Process';
		include "header.php";
		print ("<h2>Account Balance Too High</h2>
		Your account, number $ToAccount, has a balance that exceeds the permitted maximum.  You cannot enter this cheque until your balance returns to within acceptable limits.");
		include "footer.php";
		exit();
		break;
	case 2:
		$SellerOver = "<strong>WARNING:</strong>";
		break;
	default:
	}

// seems all is good.  Now we can record the trade.

include "recordtrade.php";


// get a transaction ID

$transIDtime = time();
mysql_query("INSERT INTO transidlookup
		 VALUES ('','$transIDtime','$MemberID')");
$transIDlookup = mysql_query("SELECT TransactionID FROM transidlookup
			      WHERE Time = $transIDtime
			      AND MemberID = $MemberID");
$TransactionID = mysql_result($transIDlookup,0,'TransactionID');


// update the Cheque table to show Transaction ID

mysql_query("UPDATE cheques
		SET TransactionID = $TransactionID
		WHERE ChequeID = $ChequeID");

// enter the transaction

SubmitTrade($TransactionID,$Cheque["AccountID"],-$Amount,"Cheque $ChequeID: $Memo",$ToAccount);
SubmitTrade($TransactionID,$ToAccount,$Amount,"Cheque $ChequeID: $Memo",$Cheque["AccountID"]);
ProcessFee($TransactionID,$ToAccount,$Amount,'sell');
ProcessFee($TransactionID,$Cheque["AccountID"],$Amount,'buy');

$title = 'Cheque Registered';
include 'header.php';
print ("<h2>Cheque Registered</h2>
	Cheque $ChequeID has been registered, and the amount of $Amount Ecos has been transferred to Account $ToAccount.<p>
	To have this transaction reversed, contact the <a href=mailto:$SystemEmail>system administrator</a><p>
	<hr><p>
	<strong>To register another cheque complete the form below:</strong><p>\n");
PrintForm('','','','');
include 'footer.php';
?>