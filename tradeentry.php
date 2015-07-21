<?

  /*********************************************************************/
  /*
       Writen By:     Martin Settle
       Last Modified: October 1, 2001
       Called By:     Nothing
       Calls:         Nothing
       Description:   This is the transaction entry program.

       Modification History:
                    September 13, 2001 - File created.
                    December 21, 2001 - support for buyer entry added
  */
  /*********************************************************************/

/* Get the includes out of the way, right off the bat */

   include "configuration.php";
   include "connectdb.php";

/* The form used to enter the trade is printed by calling this subroutine.  If
some variables are given, they are used as default */

function tradeentryform()
{

/* First we have to look up whether the buyer or the seller enters the trades */

         $Entrylookup = mysql_query("SELECT TradeEntryBy FROM administration");
         $Entrybyarray = mysql_fetch_array($Entrylookup);
         $Entryby = $Entrybyarray["TradeEntryBy"];

/* Now begin printing the form */

         print ("<form action=\"tradeentry.php\" method=post>
                 <input type=hidden name=EnteredBy value='$Entryby'>
                 <table noborder>
                 <tr><th align=center colspan=2 bgcolor=#D3D3D3>Trade Data</th></tr>");

         switch($Entryby)
         {
                case 'seller':
                      print("<tr><th align=left>Your Account:</th>");

                      $selleraccounts = mysql_query("SELECT AccountID
                                         FROM membertoaccountlink
                                         WHERE MemberID = $GLOBALS[MemberID]");
                      switch(mysql_num_rows($selleraccounts))
                      {
                              case 1:
                                  $AccountID = mysql_fetch_array($selleraccounts);
                                  print ("<td><strong>$AccountID[AccountID]</strong>
                                          <input type=hidden name=SellerID value=$AccountID[AccountID]
                                          </td></tr>");
                                  break;
                              default:
                                  print ("<td><select name=SellerID><option>");
                                  if(!empty($GLOBALS["SellerID"]))
                                  {
                                          print $GLOBALS["SellerID"];
                                          }
                                  while($row = mysql_fetch_array($selleraccounts))
                                  {
                                          print "<option>$row[AccountID]\n";
                                          }
                                  print ("</select></td></tr>\n");
                                  break;
                              }
                      print ("<tr><th align=left>Buyer's Account:</th>
                              <td><input type=text name=BuyerID ");
                      if(!empty($GLOBALS["BuyerID"]))
                      {
                              print "value='$GLOBALS[BuyerID]'";
                              }
                      print ("></td></tr>\n");
                      break;

                case 'buyer':
                      print("<tr><th align=left>Your Account:</th>");

                      $buyeraccounts = mysql_query("SELECT AccountID
                                         FROM membertoaccountlink
                                         WHERE MemberID = $GLOBALS[MemberID]");
                      switch(mysql_num_rows($buyeraccounts))
                      {
                              case 1:
                                  $AccountID = mysql_fetch_array($buyeraccounts);
                                  print ("<td><strong>$AccountID[AccountID]</strong>
                                          <input type=hidden name=BuyerID value=$AccountID[AccountID]
                                          </td></tr>");
                                  break;
                              default:
                                  print ("<td><select name=BuyerID><option>");
                                  if(!empty($GLOBALS["BuyerID"]))
                                  {
                                          print $GLOBALS["BuyerID"];
                                          }
                                  while($row = mysql_fetch_array($buyeraccounts))
                                  {
                                          print "<option>$row[AccountID]\n";
                                          }
                                  print ("</select></td></tr>\n");
                                  break;
                              }
                      print ("<tr><th align=left>Seller's Account:</th>
                              <td><input type=text name=SellerID ");
                      if(!empty($GLOBALS["SellerID"]))
                      {
                              print "value='$GLOBALS[SellerID]'";
                              }
                      print ("></td></tr>\n");
                      break;
                default:
                        print ("there is something wrong here...");
                }
        if(empty($GLOBALS["Description"]))
        {
                $GLOBALS["Description"] = "";
                }
        if(empty($GLOBALS["Amount"]))
        {
                $GLOBALS["Amount"] = "";
                }
        print ("</td></tr>
                <tr><th align=left>Item(s) Traded:</th>
                <td><input type=text name=Description size=40 value='$GLOBALS[Description]'></td></tr>
                <tr><th align=left>Eco Amount:</th>
                <td><input type=text name=Amount size=8 value='$GLOBALS[Amount]'></td></tr>
                <tr><td colspan=2 align=middle>
                <input type=hidden name=Function value=VerifyTrade>
                <input type=Submit value='Record Trade'></td></tr>
                </table>
		</form>
                <p>");
        }

/* MAIN PROGRAM BEGINS HERE
Start by checking that the user is logged in as a member.  If not, print
a message and exit. */

if(empty($MemberID))
{
        $title = "Not Authorized";
        include "header.php";
        print ("<h1>Not Authorized</h1>
                You are not authorized to enter trades.  If you are a LETS member, please ensure that you have <a href=\"login.php\">logged in</a>.");
        include "footer.php";
        exit();
        }

/* Now check the $Function variable.  If it is not present, this is the first
time the page has been called.*/

if(empty($Function))
{

        /* Call the form subroutine and exit */

        $title = 'Trade Entry';
        include "header.php";
        print ("<h1>Trade Entry</h1>");
        tradeentryform();
	print ("<hr><p><center><form action=processcheques.php method=GET><input type=submit value='Submit a Cheque Transaction'></form></center>\n");
        include "footer.php";
        exit();
         }

/* Otherwise, $Function exists, and we can do a switch on it to call the appropriate
subroutines to verify the trade or to enter it into the database. */

switch($Function)
{

/* The first step is the VerifyTrade system */

	case 'VerifyTrade':

/* Check that all fields are complete.  If they aren't, call the form subroutine
and exit */

        if(empty($BuyerID) || empty($SellerID) || empty($Description) || empty($Amount))
        {
               	$title = "Trade Submission Incomplete";
                include "header.php";
                print ("<h1>Incomplete Trade Data</h1>
                	<h4>The data that you have submitted for the trade is incomplete.  Please ensure that all requested information is complete, and resubmit</h4>\n<p>");
                tradeentryform();
		include "footer.php";
                exit();
		}

/* Open a confirmation warning variable, which will be used to store any warnings
generated by the verification system */

	$ConfirmationWarning = "";

/* Include the verifytrade.php file, which includes the required functions for the
trade verification system */

	include "verifytrade.php";

/* Clean up the amount field to ensure that it is a number, and clean up the
description */

	$Amount = MakeCurrency($Amount);
        $Description = stripslashes($Description);

/* Check that the accounts exist */

	if($EnteredBy == 'buyer')
        {
        	$SellerID = ExistsAccount($SellerID);
                $SellerAccountName = $AccountName;
                }
        else
        {
        	$BuyerID = ExistsAccount($BuyerID);
                $BuyerAccountName = $AccountName;
                }

/* Check that the accounts have trading priveleges 
(Credit Limit is greater than zero) */

	if($EnteredBy == 'buyer')
	{
		if(NoCredit($BuyerID))
		{
			$title = 'Trading not authorized';
			include 'header.php';
       		        print ("<h1>Trading Not Authorized</h1>
               			<strong>Your account has no trading priveleges on $Systemname.</strong><p>
				if you think that your account should be authorized to change, or wish to make changes to your account status, please contact the <a href='mailto://$SystemEmail'>$Systemname Administrator</a>,");
			include 'footer.php';
			exit();
			}
		if(NoCredit($SellerID))
		{
			$title = 'Seller Account not authorized';
			include 'header.php';
			print ("<h1>Trading Not Authorized</h1>
				<strong>The Seller Account identified in this trade has no trading privileges on $Systemname.</strong><p>
				This trade will not be processed.  Please address this with the member in question.  If you have concerns about the behaviour of any member of this system, please contact the <a href='mailto://$SystemEmail'>$Systemname Administrator</a>,");
			include 'footer.php';
			exit();
			}
		}
	else
	{
		if(NoCredit($SellerID))
		{
			$title = 'Trading not authorized';
			include 'header.php';
       		        print ("<h1>Trading Not Authorized</h1>
               			<strong>Your account has no trading priveleges on $Systemname.</strong><p>
				if you think that your account should be authorized to change, or wish to make changes to your account status, please contact the <a href='mailto://$SystemEmail'>$Systemname Administrator</a>,");
			include 'footer.php';
			exit();
			}
		if(NoCredit($BuyerID))
		{
			$title = 'Buyer Account not authorized';
			include 'header.php';
			print ("<h1>Trading Not Authorized</h1>
				<strong>The Buyer Account identified in this trade has no trading privileges on $Systemname.</strong><p>
				This trade will not be processed.  Please address this with the member in question.  If you have concerns about the behaviour of any member of this system, please contact the <a href='mailto://$SystemEmail'>$Systemname Administrator</a>,");
			include 'footer.php';
			exit();
			}
		}

/* Check that the "other account" has not expired */

	switch($EnteredBy)
        {
        	case 'buyer':
                	if(IsExpired($SellerID))
                        {
	                	$title = "Seller Account Expired";
		                include "header.php";
        		        print ("<h1>Unable to Process Trade</h1>
                			<h4>Sorry.</h4>The system is unable to process the inputted trade, because: <p><h2>the identified seller account has expired.</h2><p>");
		                include "footer.php";
        		        exit();
                        	}
                        break;
        	case 'seller':
                	if(IsExpired($BuyerID))
                        {
	                	$title = "Buyer Account Expired";
		                include "header.php";
        		        print ("<h1>Unable to Process Trade</h1>
                			<h4>Sorry.</h4>The system is unable to process the inputted trade, because: <p><h2>the identified buyer account has expired.</h2><p>");
		                include "footer.php";
        		        exit();
                                }
                }

/* Check to ensure that neither account is restricted.  If the entering account is
suspended, deny the trade.  Otherwise, look for a warning, and deny or allow the
trade as appropriate */

	switch($EnteredBy)
        {
        	case 'buyer':
                	$BuyerStatus = AccountStatus($BuyerID);
                        if(($BuyerStatus == 'Suspended') || ($BuyerStatus == 'Suspended from Purchase'))
                        {
                                $title = 'Account Restricted';
		                include "header.php";
                		print ("<h1>Account #$BuyerID is $BuyerStatus</h1>\n
		                	The account you have attempted to register a purchase for is currently under suspension.
		                        <strong>This trade is not permitted</strong>.<p>
		                        For information about this suspension please contact the <a href=\"mailto:$SystemEmail\">$Systemname Administrator</a>");
		                include "footer.php";
		                exit();
		        	}
		        $SellerStatus = AccountStatus($SellerID);
		        if(($SellerStatus == 'Suspended') || ($SellerStatus == 'Suspended from Sale'))
		        {
      		                if(WasWarned($BuyerID, $SellerID, 'Suspension'))
		                {
      		                	$title = 'Trade not authorized';
		                        include "header.php";
		                        print ("<h1>Trade not authorized</h1>
		                               Account #$SellerID is $SellerStatus.  Database records show that <em>your account (#$BuyerID) received a warning to this effect on <strong>$WarningDate.</strong></em><p>
		                               As you have received a previous warning about the current status of this account, entry of this trade is forbidden.<p>
		                               If you have cause to believe that this information is incorrect or invalid, please contact the <a href=\"mailto:$SystemEmail\">$Systemname Administrator</a>.");
		                        include "footer.php";
		                        exit();
		                        }
		                if(!empty($newwarning))
		                {
		                        $ConfirmationWarning .= "<strong>WARNING:</strong> The seller account named is this transaction (Account #$SellerID) is currently under suspension.<strong>  Future sales to this account will not be permitted until such time as the suspension is lifted.</strong><p>";
		                        $newwarning = "";
		                        }
	        	        }
                        break;

                case 'seller':
                	$SellerStatus = AccountStatus($SellerID);
                        if(($SellerStatus == 'Suspended') || ($SellerStatus == 'Suspended from Sale'))
                        {
                                $title = 'Account Restricted';
		                include "header.php";
                		print ("<h1>Account #$SellerID is $SellerStatus</h1>\n
		                	The account you have attempted to register a sale for is currently under suspension.
		                        <strong>This trade is not permitted</strong>.<p>
		                        For information about this suspension please contact the <a href=\"mailto:$SystemEmail\">$Systemname Administrator</a>");
		                include "footer.php";
		                exit();
		        	}
		        $BuyerStatus = AccountStatus($BuyerID);
		        if(($BuyerStatus == 'Suspended') || ($BuyerStatus == 'Suspended from Sale'))
		        {
      		                if(WasWarned($SellerID, $BuyerID, 'Suspension'))
		                {
      		                	$title = 'Trade not authorized';
		                        include "header.php";
		                        print ("<h1>Trade not authorized</h1>
		                               Account #$BuyerID is $BuyerStatus.  Database records show that <em>your account (#$BuyerID) received a warning to this effect on <strong>$WarningDate.</strong></em><p>
		                               As you have received a previous warning about the current status of this account, entry of this trade is forbidden.<p>
		                               If you have cause to believe that this information is incorrect or invalid, please contact the <a href=\"mailto:$SystemEmail\">$Systemname Administrator</a>.");
		                        include "footer.php";
		                        exit();
		                        }
		                if(!empty($newwarning))
		                {
		                        $ConfirmationWarning .= "<strong>WARNING:</strong> The buyer account named in this transaction (Account #$BuyertID) is currently under suspension.<strong>  Future sales to this account will not be permitted until such time as the suspension is lifted.</strong><p>";
		                        $newwarning = "";
		                        }
	        	        }
          	}

/* Check that the accounts are not over their limits.  If the entering account is
over the limit, deny the trade.  If the other account is over the limit, check for
warnings and approve or deny as appropriate */

	switch($EnteredBy)
        {
        	case 'buyer':
                	switch(OverLimit($BuyerID,$Amount,'buyer'))
                        {
                                case 1:
		                	$title = "Account Balance Too Low";
		                        include "header.php";
		                        print ("<h1>Trade not authorized</h1>
		                              Your account, <strong>(Account #$BuyerID)</strong>, has a balance that exceeds the maximum allowable credit.<p>
		                              <em>Until you bring your balance within your permitted range, further purchase by this account are not permitted.</em>
		                              For information or advice on how to use the system to sell goods and services, contact the <a href=\"mailto:$SystemEmail\">$Systemname Administrator</a>.<p>
		                              You may attempt to re-enter this trade when your balance has been brought to within acceptable limits.");
		                        include "footer.php";
		                        exit();
		                case 2:
		                	$ConfirmationWarning .= "<strong>WARNING: </strong> This trade puts your account balance below the maximum allowable credit.  <strong>Your account will be restricted from further purchases until your balance is brought to within acceptable levels.</strong><p>";
                                default:
                                }
                        if(OverLimit($SellerID,$Amount,'seller'))
                        {
                               	if(WasWarned($BuyerID,$SellerID,'Over Limit'))
                                {
	                                $title = "Trade not authorized";
		                        include "header.php";
		                        print ("<h1>Trade not authorized</h1>
		                               Account #$SellerID has surpassed the maximum allowable balance.  Database records show that <em>your account (#$BuyerID) received a warning to this effect on <strong>$WarningDate.</strong></em><p>
		                               As you have received a previous warning about the current status of this account, entry of this trade is forbidden.<p>
		                               If you have cause to believe that this information is incorrect or invalid, please contact the <a href=\"mailto:$SystemEmail\">$Systemname Administrator</a>.");
		                        include "footer.php";
		                        exit();
                                        }
                                $ConfirmationWarning .= "<strong>WARNING: </strong>The seller account named in this transaction (Account #$SellerID) is currently over the credit limit.<strong>  Future purchases from this account will not be permitted until such time as the account's balance is within acceptable levels.</strong><p>";
                                }
                        break;

                case 'seller':
                	switch(OverLimit($SellerID,$Amount,'seller'))
                        {
                                case 1:
		                	$title = "Account Balance Too High";
		                        include "header.php";
		                        print ("<h1>Trade not authorized</h1>
		                              Your account, <strong>(Account #$SellerID)</strong>, has a balance that exceeds the maximum allowable level.<p>
		                              <em>Until you bring your balance within your permitted range, further purchase by this account are not permitted.</em>
		                              For information or advice on how to use the system to purchase goods and services, contact the <a href=\"mailto:$SystemEmail\">$Systemname Administrator</a>.<p>
		                              You may attempt to re-enter this trade when your balance has been brought to within acceptable limits.");
		                        include "footer.php";
		                        exit();
		                case 2:
		                	$ConfirmationWarning .= "<strong>WARNING: </strong> This trade puts your account balance above the maximum allowable level.  <strong>Your account will be restricted from further sales until your balance is brought to within acceptable levels.</strong><p>";
                                default:
                                }
                        if(OverLimit($BuyerID,$Amount,'buyer'))
                        {
                               	if(WasWarned($SellerID,$BuyerID,'Over Limit'))
                                {
	                                $title = "Trade not authorized";
		                        include "header.php";
		                        print ("<h1>Trade not authorized</h1>
		                               Account #$BuyerID has surpassed the maximum allowable credit.  Database records show that <em>your account (#$SellerID) received a warning to this effect on <strong>$WarningDate.</strong></em><p>
		                               As you have received a previous warning about the current status of this account, entry of this trade is forbidden.<p>
		                               If you have cause to believe that this information is incorrect or invalid, please contact the <a href=\"mailto:$SystemEmail\">$Systemname Administrator</a>.");
		                        include "footer.php";
		                        exit();
                                        }
                                $ConfirmationWarning .= "<strong>WARNING: </strong>The buyer account named in this transaction (Account #$BuyerID) is currently over the credit limit.<strong>  Future sales to this account will not be permitted until such time as the account's balance is within acceptable levels.</strong><p>";
                                }
        	}
/* Look up a Transaction ID.  This is done by inserting a timestamp and
the memberID into the transactionlookup table, then querying the table for
transactionID based on those same details */

        $transIDtime = time();
        if(!mysql_query("INSERT INTO transidlookup
        		  VALUES ('','$transIDtime','$MemberID')"))
        {
        	$title = "Database Error";
                include "header.php";
                print ("<h1>Unable to get TransactionID</h2>
                	The system was unable to create a transaction identification number.<p>
                        The following error was returned: " . mysql_error());
                include "footer.php";
                exit();
                }
        $transIDlookup = mysql_query("SELECT TransactionID
        		 	       FROM transidlookup
                                        WHERE Time = $transIDtime
                                         AND MemberID = $MemberID");
        $Transaction = mysql_fetch_array($transIDlookup);
        $TransactionID = $Transaction["TransactionID"];

/* Print a confirmation page, with hidden form */

        $title = "Trade Confirmation";
        include "header.php";
        print ("<h1>Trade Confirmation</h1>
        	<br><br>
                $ConfirmationWarning
                <form action=\"tradeentry.php\" method=POST>
                <input type=hidden name=Function value=ProcessTrade>
		<input type=hidden name=EnteredBy value=$EnteredBy>
                <input type=hidden name=BuyerID value=$BuyerID>
                <input type=hidden name=SellerID value=$SellerID>
                <input type=hidden name=Description value=\"$Description\">
                <input type=hidden name=Amount value=\"$Amount\">
                <input type=hidden name=Time value=\"$transIDtime\">
                <input type=hidden name=TransactionID value=$TransactionID>
                <table border=0>
                <tr><th align=center colspan=2 bgcolor=#D3D3D3>Please confirm the following data:</th></tr>");
        if($EnteredBy == 'seller')
        {
        	print ("<tr><th align=left>Your (seller) Account: </th><td>$SellerID</td></tr>
                	<tr><th align=left>Buyer Account: </th><td>$BuyerID ($BuyerAccountName)</td></tr>");
                }
        else
        {
        	print ("<tr><th align=left>Your (buyer) Account: </th><td>$BuyerID</td></tr>
                	<tr><th align=left>Seller Account: </th><td>$SellerID ($SellerAccountName)</td></tr>");
        	}
        print ("<tr><th align=left>Description: </th><td>$Description</td></tr>
                <tr><th align=left>Amount: </th><td>$Amount<br></td></tr>
                <tr><td align=right><input type=submit value=Confirm></form></td>
                <td valign=top><form action=\"tradeentry.php\" method=POST>
                <input type=hidden name=Function value=CancelTrade>
                <input type=hidden name=TransactionID value=$TransactionID>
                <input type=submit value=\"Cancel\"></form></td></tr></table>
                <p>");
        include "footer.php";
        exit();

/* Cancel Trade */

   case 'CancelTrade':

/* Delete the transidtable entry.  If the entry fails to delete, e-mail
a notice to the system administrator e-mail address in configuration.php */

        if(!mysql_query("DELETE FROM transidlookup
        		  WHERE TransactionID = $TransactionID"))
        {
        	mail("$admin_email", "$Systemname Database Error", "\nTransaction number $TransactionID was cancelled, but the system failed to remove the entry in the transidlookup table.\n\nThis is an automated message");
                }
        $title = "Trade Cancelled";
        include "header.php";
        print ("<h1>Trade Cancelled</h1>
        	Your trade entry has been cancelled.<p>
                All reference to the submitted data has been removed from the system.<p>
                To continue, choose an option from the menu on the left.<p>");
   	include "footer.php";
        exit();

/* Process Trade */
   case 'ProcessTrade':

/* Include the recordtrade.php file, which contains the functions related
to this recording trades */

   	include "recordtrade.php";

/* Confirm the appropriate tranactionID data has been submitted.  If not,
exit.  If the data is correct, delete the transactionID table entry */

        $lookuptransid = mysql_query("SELECT * FROM transidlookup
         		  	       WHERE TransactionID = '$TransactionID'
                                        AND Time = '$Time'
                                        AND MemberID = '$MemberID'");
         if(mysql_num_rows($lookuptransid) != 1)
         {
         	$title = "Data Submission Error";
                 include "header.php";
                 print ("<h1>Trade Entry Data Error</h1>
                 	<h3>There was an error in the submission of your trading data.</h3>
                         Please ensure that you have submitted this data using the <a href=tradeentry.php>Trade Entry form</a>.");
                 include "footer.php";
                 exit();
         	}
         if(!mysql_query("DELETE FROM transidlookup WHERE TransactionID = '$TransactionID'"))
         {
         	mail($admin_email, "LETS DATABASE ERROR", "The LETS System was unable to delete Transaction $TransactionID from the transidlookup table.\n\nThe error reported was:\n\n" . mysql_error() . "\n\nThis message is generated automatically");
                 }

         /* clean up the description */

         $Description = stripslashes($Description);

         /* Submit the transaction for the seller account */

         SubmitTrade($TransactionID,$SellerID,$Amount,$Description,$BuyerID);

         /* Submit the transaction for the buyer account */

         SubmitTrade($TransactionID,$BuyerID,-$Amount,$Description,$SellerID);

         /* Process the transaction fees */

         ProcessFee($TransactionID, $SellerID, $Amount, 'sell');
         ProcessFee($TransactionID, $BuyerID, $Amount, 'buy');

         /* Print a receipt */

         if($EnteredBy == 'seller')
         {
         	$AccountID = $SellerID;
                 }
         else
         {
         	$AccountID = $BuyerID;
                 }
         $title = "Transaction Recorded";
         include "header.php";
         print ("<h1>Transaction Recorded</h1>
         	<h3>This page is your record of transaction for this trade.</h3>
                Please print this page if you wish to keep a copy for your records.<p>
                <table noborder>
                <tr><th align=center bgcolor=#D3D3D3 colspan=2>Transaction Record</th></tr>
                <tr><th align=left>Transaction Number: </th><td>$TransactionID</td></tr>
                <tr><th align=left>Member:</th><td>$MemberID: $MemberFirstName</td></tr>
                <tr><th align=left>Credited to Account: </th><td>$SellerID</td></tr>
                <tr><th align=left>Debited to Account: </th><td>$BuyerID</td></tr>
                <tr><th align=left>Trade Amount: </th><td>$Amount</td></tr>
                <tr><th align=left>Description: </th><td>$Description</td></tr>
                <tr><th align=left valign=top>Service Fees: </td><td><table noborder>");
         if(!empty($SellerFee))
         {
                 print "<tr><td>To Seller: $SellerFee</td></tr>";
                 }
         if(!empty($BuyerFee))
         {
                 print "<tr><td>To Buyer: $BuyerFee</td></tr>";
                 }
         print ("</table></td></tr>
         	<tr><th align=left>Your Current Balance: </th><td>");
         $lookuplastreference = mysql_query("SELECT MAX(Reference) AS Reference FROM transactions WHERE AccountID = '$AccountID'");
         $lastreference = mysql_result($lookuplastreference, '0', "Reference");
         $lookupbalance = mysql_query("SELECT CurrentBalance
         		 	       FROM transactions
                                         WHERE AccountID = '$AccountID'
                                          AND Reference = '$lastreference'");
         $CurrentBalance = mysql_result($lookupbalance, '0', "CurrentBalance");
         print ("$CurrentBalance</td></tr>
         	<tr><th colspan=2 bgcolor=#D3D3D3>&nbsp;</th></tr>
                <tr><td colspan=2 align=center>To have this transaction reversed, contact the <a href=\"mailto:$SystemEmail\">LETS System Administrator</a></td></tr></table>");
                include "footer.php";
         exit();

/* Default */

	/* This suggests that the page has been called with $Function as something
        other than the above options.  This shouldn't happen, but if it does, print
        an error message */

        print "There is an error in the switch function";
        }

?>