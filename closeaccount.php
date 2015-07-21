<?

/* This script simply requests an account number, and then processes the actions necessary to close the account with a zero balance */

include "configuration.php";
include "connectdb.php";
include "adminlogin.php";

if(empty($AccountID))
{
	$title = 'Close Account';
	include "header.php";
	print ("<center>Please input the Account Number for the account you wish to close:<p>
			<form action=closeaccount.php method=POST>
			<input type=text name=AccountID size=4><p>
			<input type=submit value='Close Account'>
			</form></center>\n");
	include "footer.php";
	exit();
	}

if(empty($Confirm))
{
	$AccountLookup = mysql_query("SELECT * FROM account WHERE AccountID = '$AccountID' AND AccountStatus != 'Closed'");
	if(mysql_num_rows($AccountLookup)==0)
	{
		$title = 'Account Not Found';
		include "header.php";
		print ("<center>The submitted Account Number, $AccountID, is not a listed account.<p><hr><p>
				Please input the Account Number for the account you wish to close:<p>
				<form action=closeaccount.php method=POST>
				<input type=text name=AccountID size=4><p>
				<input type=submit value='Close Account'>
				</form></center>\n");
		include "footer.php";
		exit();
		}
	$Account = mysql_fetch_array($AccountLookup);

	$title = 'Confirm Account Closure';
	include "header.php";
	print ("<center>Please confirm that you wish to close<br>
			<strong>Account #$AccountID $Account[AccountName]</strong><p>
			<form action=closeaccount.php method=POST>
			<input type=hidden name=AccountID value=$AccountID>
			<input type=hidden name=Confirm value=1>
			<input type=submit value='Close Account $AccountID'>
			</form></center>\n");
	include "footer.php";
	exit();
	}

// get the current balance

$BalanceLookup = mysql_query("SELECT CurrentBalance FROM transactions
							WHERE AccountID = $AccountID
							ORDER BY Reference DESC
							LIMIT 1");
$Balance = mysql_result($BalanceLookup,0,'CurrentBalance');

// get the system balance

$SystemBalanceLookup =mysql_query("SELECT CurrentBalance FROM transactions
							WHERE AccountID = $SystemAccountID
							ORDER BY Reference DESC
							LIMIT 1");
$SystemBalance = mysql_result($SystemBalanceLookup,0,'CurrentBalance');

// get a transaction ID

$transIDtime = time();
mysql_query("INSERT INTO transidlookup VALUES ('','$transIDtime','$MemberID')");
$transIDlookup = mysql_query("SELECT TransactionID
        		 	       FROM transidlookup
                                        WHERE Time = $transIDtime
                                         AND MemberID = $MemberID");
$TransactionID = mysql_result($transIDlookup,0,'TransactionID');
mysql_query("DELETE FROM transidlookup WHERE TransactionID = $TransactionID");

// transfer the remainder balance to the system account

$NewSystemBalance = $SystemBalance + $Balance;

mysql_query("INSERT INTO transactions
			SET TransactionID = '$TransactionID',
			AccountID = '$SystemAccountID',
			Description = 'Closure of Account $AccountID',
			Amount = '$Balance',
			CurrentBalance = '$NewSystemBalance',
			OtherAccountID = '$AccountID',
			TradeDate = CURDATE()");
mysql_query("INSERT INTO transactions 
			SET TransactionID = '$TransactionID',
			AccountID = '$AccountID',
			Description = 'Closure of Account $AccountID',
			Amount = EVAL(0 - $Balance),
			CurrentBalance = 0,
			OtherAccountID = '$SystemAccountID',
			TradeDate = CURDATE()");

// Now expire the Account and set the AccountStatus to Closed

mysql_query("UPDATE account
			SET AccountRenewalDate = CURDATE(),
			AccountStatus = 'Closed'
			WHERE AccountID = $AccountID");

// And print the confirmation.

$title = "Account $AccountID Closed";
include "header.php";
print ("<center>Account #$AccountID has been closed.<p>
		<hr><p>
		Please input the Account Number for the account you wish to close:<p>
		<form action=closeaccount.php method=POST>
		<input type=text name=AccountID size=4><p>
		<input type=submit value='Close Account'>
		</form></center>\n");
include "footer.php";
exit();
?>