<?

/******************************************************************************/
/*
        Written By:        Martin Settle
        Last Modified:        October 29, 2001
        Called By:        header.php
        Calls:                verifytrade.php
                        tradeentry.php
                        adminlogin.php
        Description:        This is the admin-level trade entry form, which
                        allow an admin user to process up to 10 trades
                        simultaneously.

        Modification History:
                        October 29, 2001 - File Created

*/
/******************************************************************************/

/* include the configuration data */

include "configuration.php";
include "connectdb.php";

/* call the adminlogin file to ensure admin level access */

include "adminlogin.php";

/* check to see if the Function variable is set to Process.  If so, verify
and record all trades. */
if(empty($Function)) {$Function = "";}
if($Function == 'Process')
{

        include "verifytrade.php";
        include "recordtrade.php";

/* Process each transaction form */

        for($trade = 0; $trade < 10; $trade++)
        {

/* Check that the submitted accounts exist, and that the Amount field is
numeric */

                $SellerID["$trade"] = ExistsAccount($SellerID["$trade"]);
                $BuyerID["$trade"] = ExistsAccount($BuyerID["$trade"]);
                $Amount["$trade"] = MakeCurrency($Amount["$trade"]);

/* Don't process if no data is entered, or if only partial data is submitted.
Set the Result[] variable to contain a response to the user, as appropriate */

                if((empty($SellerID["$trade"]))
                   && (empty($BuyerID["$trade"]))
                   && (empty($Description["$trade"]))
                   && (empty($Amount["$trade"])))
                {
                        $Result["$trade"] = "No trade data entered.";
                        }
                elseif((empty($SellerID["$trade"]))
                       || (empty($BuyerID["$trade"]))
                       || (empty($Description["$trade"]))
                       || (empty($Amount["$trade"])))
                {
                        $Result["$trade"] = "Not processed.  <em>Submitted Trade Data incomplete or contains invalid AccountID</em>.";
                        }

/* Process any other trades */

                else
                {

/* Check for expired accounts */

                        if(IsExpired($SellerID["$trade"]))
                        {
                                $Result["$trade"] = "Not Processed.  <em>The Seller account has expired</em>.";
                                }
                        elseif(IsExpired($BuyerID["$trade"]))
                        {
                                $Result["$trade"] = "Not Processed.  <em>The Buyer account has expired</em>.";
                                }

/* Check for suspended accounts */

                        elseif((AccountStatus($BuyerID["$trade"]) == 'Suspended')
                               || (AccountStatus($BuyerID["$trade"]) == 'Suspended from Buy'))
                        {
                                if(WasWarned($SellerID["$trade"], $BuyerID["$trade"], 'Suspension'))
                                {
                                        $Result["$trade"] = "Not Processed. <em>Buyer Account is under suspension and Seller has received a previous warning</em>.";
                                        }
                                }
                        elseif((AccountStatus($SellerID["$trade"]) == 'Suspended')
                               || (AccountStatus($SellerID["$trade"]) == 'Suspended from Sale'))
                        {
                                if(WasWarned($BuyerID["$trade"], $SellerID["$trade"], 'Suspension'))
                                {
                                        $Result["$trade"] = "Not Processed. <em>Seller Account is under suspension and Buyer has received a previous warning</em>.";
                                        }
                                }

/* Check account balances and warnings */

                        elseif(BuyerOverLimit($BuyerID["$trade"], $Amount["$trade"]))
                        {
                                if(WasWarned($SellerID["$trade"], $BuyerID["$trade"], 'Over Limit'))
                                {
                                        $Result["$trade"] = "Not Processed.  <em>Buyer Account is over limit and Seller has received a previous warning</em>.";
                                        }
                                }
                        elseif(SellerOverLimit($SellerID["$trade"], $Amount["$trade"]))
                        {
                                if(WasWarned($BuyerID["$trade"], $SellerID["$trade"], 'Over Limit'))
                                {
                                        $Result["$trade"] = "Not Processed.  <em>Seller Account is over limit and Buyer has received a previous warning</em>";
                                        }
                                }

/* If we made it this far, all is good... process the trade... */

                        else
                        {

/* Get a transactionID */

                                $transidtime = time();
                                if(!mysql_query("INSERT INTO transidlookup
                                                  VALUES('','$transidtime','$MemberID')"))
                                {
                                        $Result["$trade"] = "Not Processed. <em>The database was unable to lookup a transaction ID</em>.";
                                        }
                                else
                                {
                                        $lookuptransid = mysql_query("SELECT TransactionID
                                                            FROM transidlookup
                                                             WHERE Time = '$transidtime'
                                                              AND MemberID = '$MemberID'");
                                        $TransactionID = mysql_result($lookuptransid, 0, "TransactionID");


/* Submit the transactions */

                                        SubmitTrade($TransactionID,$SellerID["$trade"],$Amount["$trade"],$Description["$trade"],$BuyerID["$trade"]);
                                        SubmitTrade($TransactionID,$BuyerID["$trade"],-$Amount["$trade"],$Description["$trade"],$SellerID["$trade"]);
                                        ProcessFee($TransactionID,$SellerID["$trade"],$Amount["$trade"],'sell');
                                        ProcessFee($TransactionID,$BuyerID["$trade"],$Amount["$trade"],'buy');

/* And set the result variable */

                                        $Result["$trade"] = "Processed.  Transaction ID #$TransactionID.";


/* Delete the transidlookup record and log the trade in the AdminActions Log */

                                        mysql_query("DELETE FROM transidlookup
                                                      WHERE TransactionID = '$TransactionID'");
                                        mysql_query("INSERT INTO adminactions
                                                      VALUES(NULL,'$MemberID','Registered Transaction $TransactionID')");
                                        unset($TransactionID);

                                        }
                                }



                        }

                }

/* Print a results table */

        $title = "Administration Trade Entry Results";
        include "header.php";
        print ("<table width=100% noborder>
                <tr><th colspan=5 bgcolor=#D3D3D3>Trade Entry Results</th></tr>
                <tr><th>Trade</th><th>Seller</th><th>Buyer</th><th>Amount</th><th>Result</th></tr>\n");

        for($trade=0; $trade<10; $trade++)
        {
                $tradenumber=$trade+1;
                print("<tr><th>$tradenumber</th><td>$SellerID[$trade]</td><td>$BuyerID[$trade]</td><td>$Amount[$trade]</td><td>$Result[$trade]</td></tr>\n");
                }
        print "</table>";
        include "footer.php";
        exit();
        }

/* If there is no Function, or if for some reason it is set wrong, print the
admin trade entry form */

$title = "Administration Trade Entry Form";
include "header.php";
print "Please complete one row per trade for up to ten trades.  <strong>All fields are required</strong>.  Ensure that all data is accurate before submitting.<p>";

/* Print the form */


print ("<form action=admintradeentry.php method=POST>
        <input type=hidden name=Function value=Process>
        <table noborder width=100%>
        <tr><th colspan=5 bgcolor=#D3D3D3>Trade Entry</tr></tr>
        <tr><th><br>#</th><th>Seller<br>Account</th><th>Buyer<br>Account</th><th><br>Trade Description</th><th><br>Amount</th></tr>\n");

for($trade=0;$trade<10;$trade++)
{
        $tradenumber = $trade+1;
        print ("<tr><th>$tradenumber</th>
                <td align=center><input type=text name=SellerID[$trade] size=5></td>
                <td align=center><input type=text name=BuyerID[$trade] size=5></td>
                <td align=center><input type=text name=Description[$trade] size=40</td>
                <td align=center><input type=text name=Amount[$trade] size=8</td>
                </tr>");
        }
print ("<tr><th colspan=5 bgcolor=#D3D3D3>&nbsp;</th></tr>
        <tr><td colspan=5 align=right><input type=submit value='Record Trades'></td></tr>
        </table></form>
        <p><hr><p>
        <center>
        <form action=tradereversal.php method=post><input type=submit value='Reverse a Trade'></form>
        </center>
        <p>");
include "footer.php";