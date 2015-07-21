<? 
  /*********************************************************************/
  /*
       Writen By:     Marti Settle
       Last Modified: October 28, 2001
       Called By:     All trade entry systems
       Calls:         Nothing
       Description:   This is the set of routines required by the trade
                             entry processing systems, including the actual
                      trade entry function

       Modification History:
                    October 28, 2001 - File Created
                    May 30, 2002 - Fixed errors in processing System
                                account transactions
		    Jan 16, 2003 - fixed INSERT language to allow changes
				to table structure
  */
  /*********************************************************************/

/* SubmitTrade literally submits the trade into the transactions table, after
looking up the new balance */

function SubmitTrade($TransactionID,$AccountID,$Amount,$Description,$OtherAccountID)
{
        $today = date("Y-m-d");
	$Description = addslashes("$Description");
        $lookuplastreference = mysql_query("SELECT MAX(Reference) AS Reference FROM transactions WHERE AccountID = '$AccountID'");
        $lastreference = mysql_result($lookuplastreference, '0', "Reference");
        $lookupbalance = mysql_query("SELECT CurrentBalance
                                        FROM transactions
                                        WHERE AccountID = '$AccountID'
                                         AND Reference = '$lastreference'");
        $oldbalance = mysql_result($lookupbalance,'0',"CurrentBalance");
        $newbalance = $oldbalance + $Amount;
        if(!mysql_query("INSERT INTO transactions
                          SET TransactionID = '$TransactionID',TradeDate = '$today', AccountID = '$AccountID',Amount = '$Amount', Description = '$Description', CurrentBalance = '$newbalance', OtherAccountID = '$OtherAccountID'"))
        {
                print mysql_error();
                $message = "There was an error entering data into the transaction table\n\n";
                $message .= "TransactionID: $TransactionID\nTradeDate: $today\nAccountID: $AccountID\nAmount: $Amount\nDescription: $Description\n Current Balance: $newbalance\n, Other AccountID: $OtherAccountID\n\n";
                $message .= "The database returned the following error: \n\n" . mysql_error();
                $message .= "\n\nThis is an automatically generated message.";
                //mail($GLOBALS["admin_email"], "LETS DATABASE ERROR", $message);
                }
        return(1);
        }

/* The second function in this file checks an account to see if it is fee-exempt.
If not, it looks up the appropriate fee to charge, and calculates the amount, and
enters the two trades. */

function ProcessFee($TransactionID,$AccountID,$Amount,$sellorbuy)
{
        $lookupfeeexempt = mysql_query("SELECT AccountIsFeeExempt
                                         FROM account
                                          WHERE AccountID = '$AccountID'");
        if(mysql_result($lookupfeeexempt, '0', "AccountIsFeeExempt") == 0)
        {
                if($sellorbuy == 'sell')
                {
                        $lookuptransactionfee = mysql_query("SELECT AccountTypeSaleTransactionFee AS Fee
                                                              FROM account, accounttypeoptions
                                                               WHERE account.AccountTypeID = accounttypeoptions.AccountTypeID
                                                                AND account.AccountID = '$AccountID'");
                        }
                else
                {
                        $lookuptransactionfee = mysql_query("SELECT AccountTypeBuyTransactionFee AS Fee
                                                              FROM account, accounttypeoptions
                                                               WHERE account.AccountTypeID = accounttypeoptions.AccountTypeID
                                                                AND account.AccountID = '$AccountID'");
                        }
                $transactionfee = mysql_result($lookuptransactionfee, '0', "Fee");
                $FeeAmount = $transactionfee * $Amount;
                if($FeeAmount > 0)
                {
                        if($sellorbuy == 'sell')
                        {
                                $GLOBALS["SellerFee"] = $FeeAmount;
                                }
                        else
                        {
                                $GLOBALS["BuyerFee"] = $FeeAmount;
                                }
                        SubmitTrade($TransactionID,$AccountID,-$FeeAmount,"Transaction Fee",$GLOBALS["SystemAccountID"]);

                        $lookupSystemtransactions = mysql_query("SELECT * FROM transactions
                                                                  WHERE TransactionID = '$TransactionID'
                                                                   AND AccountID = '$GLOBALS[SystemAccountID]'
                                                                   AND Description = 'Transaction Fee'");
                        if(mysql_num_rows($lookupSystemtransactions) == 0)
                        {
                        SubmitTrade($TransactionID,$GLOBALS["SystemAccountID"],$FeeAmount, "Transaction Fee",$AccountID);
                                }
                        else
                        {
                                $previous = mysql_fetch_array($lookupSystemtransactions);
                                $CurrentBalance = $previous["CurrentBalance"] + $FeeAmount;
                                $NewAmount = $previous["Amount"] + $FeeAmount;
                                $update = mysql_query("UPDATE transactions
                                              SET Amount = '$NewAmount', CurrentBalance = '$CurrentBalance', OtherAccountID = ''
                                              WHERE AccountID = '$GLOBALS[SystemAccountID]'
                                              AND Description = 'Transaction Fee'
                                              AND TransactionID = '$TransactionID'");
                                }
                        }
                }
        }

?>