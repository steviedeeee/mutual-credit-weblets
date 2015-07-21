<?
  /*********************************************************************/
  /*
       Writen By:     Marti Settle
       Last Modified: October 26, 2001
       Called By:     All trade entry routines
       Calls:         nothing
       Description:   This is a set of functions that verify the status
                             of accounts being named in a trade.

       Modification History:
                    October 26, 2001 - file created
                    December 27, 2001 - OverLimit function added
  */
  /*********************************************************************/

/* ExistsAccount verifies that an OtherAccount actually exists, and sets the
GLOBALS["OtherAccountName"] variable if it does.  Otherwise, it returns 0 */

function ExistsAccount($AccountID)
{
        $NewAccountID = "";
        for($i=0;$i < strlen($AccountID); $i++)
        {
                $char = substr($AccountID,$i,1);
                if((ord($char) >= 48) AND (ord($char) <= 57))
                {
                        $NewAccountID .= $char;
                        }
                }

        $lookupAccount = mysql_query("SELECT AccountID, AccountName
                                        FROM account
                                        WHERE AccountID = '$NewAccountID'");
        if(mysql_num_rows($lookupAccount) == 1)
        {
                $Account = mysql_fetch_array($lookupAccount);
                $GLOBALS["AccountName"] = $Account["AccountName"];
                return($NewAccountID);
                }
        return("");
        }

/* The MakeCurrency function strips a variable of any dollar signs or text
and returns it to the same variable. */

function MakeCurrency($Amount)
{
        $NewAmount = "";
        for($i=0;$i < strlen($Amount); $i++)
        {
                $char = substr($Amount,$i,1);
                if((ord($char) >= 48) AND (ord($char) <= 57) OR (ord($char) == 46))
                {
                        $NewAmount .= $char;
                        }
                }
        return($NewAmount);
        }


/* The AccountStatus function will look up the status field on a particular
account, and return the status code.*/

function AccountStatus($AccountID)
{
        $lookup = mysql_query("SELECT AccountStatus
                                FROM account
                                 WHERE AccountID = '$AccountID'");
        $status = mysql_fetch_array($lookup);
        return($status["AccountStatus"]);
        }

/* The NoCredit function returns true if the identified account Credit LImit
is zero */

function NoCredit($AccountID)
{
        $lookuplimit = mysql_query("SELECT AccountCreditLimit
                                                   FROM account
                                     WHERE AccountID = '$AccountID'");
        $limit = mysql_fetch_array($lookuplimit);
        if($limit["AccountCreditLimit"] != '0')
        {
                return(0);
                }
        return(1);
        }

/* The next function checks the buyer's current balance (pre-trade), and if
the current trade puts the buyer over the trading limit, sends a notice by
e-mail to the system administrator, the user, or both, as appropriate.  It
also checks to see if the current trade brings the buyer back within the upper
trade limit, and if so, removes all references to the AccountID from the
badtradewarnings table. */

function BuyerOverLimit($AccountID,$TradeAmount)
{
       $lookuplimit = mysql_query("SELECT AccountCreditLimit
                                                   FROM account
                                     WHERE AccountID = '$AccountID'");
       $limit = mysql_fetch_array($lookuplimit);
       $lookuplastrecord = mysql_query("SELECT MAX(Reference) AS Reference
                                          FROM transactions
                                           WHERE AccountID = '$AccountID'");
        $lastrecord = mysql_result($lookuplastrecord, 0, "Reference");
        $lookupbalance = mysql_query("SELECT CurrentBalance
                                       FROM transactions
                                        WHERE AccountID = '$AccountID'
                                         AND Reference = '$lastrecord'");
        print mysql_error();
        $balance = mysql_fetch_array($lookupbalance);
        $difference = $balance["CurrentBalance"] + $limit["AccountCreditLimit"] ;
        if($difference < 0)
        {
                return(1);
                }
        elseif($difference < $TradeAmount)
        {
                $contacttypelookup = mysql_query("SELECT deliverymethodoptions.DeliveryMethodName, member.EmailAddress
                                                   FROM deliverymethodoptions, member, membertoaccount
                                                    WHERE deliverymethodoptions.DeliveryMethodID = member.DeliverMethodID
                                                    AND member.MemberID = membertoaccount.MemberID
                                                     AND membertoaccount.PrimaryContact = 1
                                                      AND membertoaccount.AccountID = '$AccountID'");
                $contacttype = mysql_fetch_array($contacttypelookup);
                if(($contacttype[DeliveryMethodName] <> 'Email') or (empty($contacttype[Email])))
                {
                        $MailBody = "Account #$AccountID has surpassed its credit limit.\n\n";
                        $MailBody .= "The account contact method is $contacttype[DeliveryMethodName].\n";
                        $MailBody .= "Please notify the member of this situation.\n\n";
                        if(!empty($contacttype[Email]))
                        {
                                $MailBody .= "An automated message will also be sent the the e-mail address registered with this account.  ";
                                $MailBody .= "This e-mail message should not be considered official notification, however.\n\n";
                                }
                        $MailBody .= "-------------------------------------------------------------------------";
                        $MailBody .= "This message has been generated automatically by the LETS system software";
                        mail($SystemEmail, "Account $AccountID Over Limit", $MailBody);
                        }
                if(!empty($contacttype[Email]))
                {
                        $MailBody2 = "This is an automatic message from the $Systemname ";
                        $MailBody2 .= "to inform you that your account, #$AccountID, has exceeded the spending limit.\n\n";
                        $MailBody2 .= "Please take steps to address this situation immediately.  ";
                        $MailBody2 .= "Failure to bring your account within your trading limit in a timely manner may result in your buying privileges being revoked.\n\n";
                        $MailBody2 .= "Should you require assistance in finding income possibilities, please contact the LETS System Administrator, at $SystemEmail.\n\n";
                        $MailBody2 .= "-------------------------------------------------------------------------";
                        $MailBody2 .= "This message has been generated automatically by the LETS system software";
                        mail($contacttype[Email], "Account $AccountID Over Limit", $MailBody2, "From: $SystemEmail");
                        }
                return(1);
                }
        if(($balance["CurrentBalance"] - $limit["AccountCreditLimit"] > 0) && ($balance["CurrentBalance"] - $limit["AccountCreditLimit"] < $TradeAmount))
        {
                if(!mysql_query("DELETE FROM badtradewarnings
                                  WHERE AboutAccountID = '$AccountID'"))
                {
                        mail($admin_email, 'Database Error', "The system software failed to remove bad trade records for account $AccountID.\n\nThis message is generated automatically");
                        }
                }
        return(0);
        }

/* The similar function for the seller... The difference is that there is a setup
for an upper credit limit that differs from the lower limit by a set factor, found
in the administration table.  That must be looked up and used as a multiplier.  The
other difference is that if the page is being called by a memberID associated with
the account rather than a non-associated admin user, and the current trade puts the
balance of the Seller's account over the upper limit, the function will return '2'
rather than 0 or 1, which will signal the calling program to print a warning message
to the user */

function SellerOverLimit($AccountID,$TradeAmount)
{
              $lookuplimit = mysql_query("SELECT AccountCreditLimit
                                            FROM account
                                      WHERE AccountID = '$AccountID'");
         $limit = mysql_fetch_array($lookuplimit);

          $lookupupperlimitfactor = mysql_query("SELECT UpperCreditLimitFactor
                                                 FROM administration");
         $upperlimitfactor = mysql_fetch_array($lookupupperlimitfactor);
         $lookuplastrecord = mysql_query("SELECT MAX(Reference) AS Reference
                                           FROM transactions
                                            WHERE AccountID = '$AccountID'");
         $lastrecord = mysql_result($lookuplastrecord, '0', "Reference");
         $lookupbalance = mysql_query("SELECT CurrentBalance
                                        FROM transactions
                                         WHERE AccountID = '$AccountID'
                                          AND Reference = '$lastrecord'");
         $balance = mysql_fetch_array($lookupbalance);
         $difference = ($limit["AccountCreditLimit"] * $upperlimitfactor["UpperCreditLimitFactor"]) - $balance["CurrentBalance"];
         if($difference < 0)
         {
                 return(1);
                 }
         elseif($difference < $TradeAmount)
         {
                 $contacttypelookup = mysql_query("SELECT deliverymethodoptions.DeliveryMethodName, member.EmailAddress
                                                    FROM deliverymethodoptions, member, membertoaccount
                                                     WHERE deliverymethodoptions.DeliveryMethodID = member.DeliverMethodID
                                                     AND member.MemberID = membertoaccountlink.MemberID
                                                      AND membertoaccount.PrimaryContact = 1
                                                       AND membertoaccount.AccountID = '$AccountID'");
                 $contacttype = mysql_fetch_array($contacttypelookup);
                 if(($contacttype[DeliveryMethodName] <> 'Email') or (empty($contacttype[Email])))
                 {
                         $MailBody = "Account #$AccountID has surpassed its credit limit.\n\n";
                         $MailBody .= "The account contact method is $contacttype[DeliveryMethodName].\n";
                         $MailBody .= "Please notify the member of this situation.\n\n";
                         if(!empty($contacttype[Email]))
                         {
                                 $MailBody .= "An automated message will also be sent the the e-mail address registered with this account.  ";
                                 $MailBody .= "This e-mail message should not be considered official notification, however.\n\n";
                                 }
                         $MailBody .= "-------------------------------------------------------------------------";
                         $MailBody .= "This message has been generated automatically by the LETS system software";
                         mail($SystemEmail, "Account $AccountID Over Limit", $MailBody);
                         }
                 if(!empty($contacttype[Email]))
                 {
                         $MailBody2 = "This is an automatic message from the $Systemname ";
                         $MailBody2 .= "to inform you that your account, #$AccountID, has exceeded the spending limit.\n\n";
                         $MailBody2 .= "Please take steps to address this situation immediately.  ";
                         $MailBody2 .= "Failure to bring your account within your trading limit in a timely manner may result in your buying privileges being revoked.\n\n";
                         $MailBody2 .= "Should you require assistance in finding income possibilities, please contact the LETS System Administrator, at $SystemEmail.\n\n";
                         $MailBody2 .= "-------------------------------------------------------------------------";
                         $MailBody2 .= "This message has been generated automatically by the LETS system software";
                         mail($contacttype[Email], "Account $AccountID Over Limit", $MailBody2, "From: $SystemEmail");
                         }
                 $lookupaccountmember = mysql_query("SELECT *
                                                             FROM membertoaccountlink
                                                       WHERE MemberID = '$GLOBALS[MemberID]'
                                                        AND AccountID = '$AccountID'");
                 if(mysql_num_rows($lookupaccountmember) == 1)
                 {
                         return(2);
                         }
                 return(1);
                 }
         if(($balance["CurrentBalance"] - $limit["AccountCreditLimit"] > 0) && ($balance["CurrentBalance"] - $limit["AccountCreditLimit"] < $TradeAmount))
         {
                 if(!mysql_query("DELETE FROM badtradewarnings
                                    WHERE AboutAccountID = '$AccountID'
                                    AND Cause = 'Over Limit'"))
                 {
                         mail($admin_email, 'Database Error', "The system software failed to remove bad trade records for account $AccountID.\n\nThis message is generated automatically");
                         }
                 }
         return(0);
         }

 /* The next function checks to see if an accountID has already been warned
about an account that is over its limit (from the badtradewarnings table).  If
not, it adds an entry to that table.  Returns 1 if no warning, 0 if a warning
existed.  If a warning existed it also sets the date of the warning into a global
variable entitled $WarningDate */

function WasWarned($GivenToAccountID,$AboutAccountID,$Cause)
{
        $lookupwarning = mysql_query("SELECT * FROM badtradewarnings
                                        WHERE GivenToAccountID = '$GivenToAccountID'
                                        AND AboutAccountID = '$AboutAccountID'
                                         AND Cause = '$Cause'");
        if(mysql_num_rows($lookupwarning) == 0)
        {
                $today = date("Y-m-d");
                if(!mysql_query("INSERT INTO badtradewarnings
                                                VALUES ('$GivenToAccountID','$AboutAccountID', '$today', '$Cause')"))
                {
                        mail('$admin_email', 'LETS Database Error', "The database failed to register a bad trade warning being given to Account $GivenToAccountID about Account $AboutAccountID on $today.\n\nThis is an automatically generated message.");
                        }
                $GLOBALS["newwarning"] = 1;
                return(0);
                }
        $warning = mysql_fetch_row($lookupwarning);
        $GLOBALS["WarningDate"] = $warning[2];
        return(1);
        }

/* IsExpired is a function that checks a specific AccountID to determine whether
the account has passed its expiry date.  It returns true if the account is expired,
false if still active. */

function IsExpired($AccountID)
{
        $lookupexpiry = mysql_query("SELECT UNIX_TIMESTAMP(AccountRenewalDate) AS RenewalDate
                                      FROM account
                                       WHERE AccountID = '$AccountID'");
        if(time() > mysql_result($lookupexpiry,'0',"RenewalDate"))
        {
                return(1);
                }
        return(0);
        }

/* The following function is a revised process combining the above buyer and seller
overlimit functions for the revised tradeentry routine, enabling buyer entry support.
The above functions have been left in to continue compatibility with the admin trade
entry system. */

function OverLimit($AccountID,$TradeAmount,$TraderType)
{
        switch($TraderType)
        {
                case "seller":
                 $lookuplimit = mysql_query("SELECT AccountCreditLimit
                                             FROM account
                                              WHERE AccountID = '$AccountID'");
                 $limit = mysql_fetch_array($lookuplimit);

                 $lookupupperlimitfactor = mysql_query("SELECT UpperCreditLimitFactor
                                                        FROM administration");
                 $upperlimitfactor = mysql_fetch_array($lookupupperlimitfactor);
                 $lookuplastrecord = mysql_query("SELECT MAX(Reference) AS Reference
                                                  FROM transactions
                                                    WHERE AccountID = '$AccountID'");
                 $lastrecord = mysql_result($lookuplastrecord, '0', "Reference");
                 $lookupbalance = mysql_query("SELECT CurrentBalance
                                               FROM transactions
                                                 WHERE AccountID = '$AccountID'
                                                  AND Reference = '$lastrecord'");
                 $balance = mysql_fetch_array($lookupbalance);
                 $difference = ($limit["AccountCreditLimit"] * $upperlimitfactor["UpperCreditLimitFactor"]) - $balance["CurrentBalance"];
                 if($difference < 0)
                 {
                         return(1);
                         }
                 elseif($difference < $TradeAmount)
                 {
                         $contacttypelookup = mysql_query("SELECT deliverymethodoptions.DeliveryMethodName, member.EmailAddress
                                                           FROM deliverymethodoptions, member, membertoaccountlink
                                                             WHERE deliverymethodoptions.DeliveryMethodID = member.DeliveryMethodID
                                                             AND member.MemberID = membertoaccountlink.MemberID
                                                              AND membertoaccountlink.PrimaryContact = 1
                                                               AND membertoaccountlink.AccountID = '$AccountID'");
                         $contacttype = mysql_fetch_array($contacttypelookup);
                         if(($contacttype["DeliveryMethodName"] <> 'Email') or (empty($contacttype["EmailAddress"])))
                         {
                                 $MailBody = "Account #$AccountID has surpassed its credit limit.\n\n";
                                 $MailBody .= "The account contact method is $contacttype[DeliveryMethodName].\n";
                                 $MailBody .= "Please notify the member of this situation.\n\n";
                                 if(!empty($contacttype["EmailAddress"]))
                                 {
                                         $MailBody .= "An automated message will also be sent the the e-mail address registered with this account.  ";
                                         $MailBody .= "This e-mail message should not be considered official notification, however.\n\n";
                                         }
                                 $MailBody .= "-------------------------------------------------------------------------";
                                 $MailBody .= "This message has been generated automatically by the LETS system software";
                                 mail($GLOBALS["SystemEmail"], "Account $AccountID Over Limit", $MailBody);
                                 }
                         if(!empty($contacttype["EmailAddress"]))
                         {
                                $MailBody2 = "This is an automatic message from the $GLOBALS[Systemname] ";
                                 $MailBody2 .= "to inform you that your account, #$AccountID, has exceeded the account balance limit.\n\n";
                                 $MailBody2 .= "Please take steps to address this situation immediately.  ";
                                 $MailBody2 .= "Failure to bring your account within your trading limit in a timely manner may result in your selling privileges being revoked.\n\n";
                                 $MailBody2 .= "Should you require assistance in finding expenditure possibilities, please contact the LETS System Administrator, at $GLOBALS[SystemEmail].\n\n";
                                 $MailBody2 .= "-------------------------------------------------------------------------";
                                 $MailBody2 .= "This message has been generated automatically by the LETS system software";
                                 mail($contacttype["EmailAddress"], "Account $AccountID Over Limit", $MailBody2, "From: $GLOBALS[SystemEmail]");
                                 }
                         $lookupaccountmember = mysql_query("SELECT *
                                                             FROM membertoaccountlink
                                                               WHERE MemberID = '$GLOBALS[MemberID]'
                                                                AND AccountID = '$AccountID'");
                         if(mysql_num_rows($lookupaccountmember) == 1)
                         {
                                 return(2);
                                 }
                         return(1);
                         }
                 if(($balance["CurrentBalance"] - $limit["AccountCreditLimit"] * $upperlimitfactor["UpperCreditLimitFactor"] < 0) && ($balance["CurrentBalance"] - $limit["AccountCreditLimit"] * $upperlimitfactor["UpperCreditLimitFactor"] < $TradeAmount))
                 {
                         if(!mysql_query("DELETE FROM badtradewarnings
                                           WHERE AboutAccountID = '$AccountID'
                                            AND Cause = 'Over Limit'"))
                         {
                                mail($GLOBALS["admin_email"], 'Database Error', "The system software failed to remove bad trade records for account $AccountID.\n\nThis message is generated automatically");
                                 }
                         }
                 return(0);
                 break;
          case "buyer":
                 $lookuplimit = mysql_query("SELECT AccountCreditLimit
                                             FROM account
                                              WHERE AccountID = '$AccountID'");
                 $limit = mysql_fetch_array($lookuplimit);

                 $lookuplastrecord = mysql_query("SELECT MAX(Reference) AS Reference
                                                  FROM transactions
                                                    WHERE AccountID = '$AccountID'");
                 $lastrecord = mysql_result($lookuplastrecord, '0', "Reference");
                 $lookupbalance = mysql_query("SELECT CurrentBalance
                                               FROM transactions
                                                 WHERE AccountID = '$AccountID'
                                                  AND Reference = '$lastrecord'");
                 $balance = mysql_fetch_array($lookupbalance);
                 $difference = $limit["AccountCreditLimit"] + $balance["CurrentBalance"];
                 if($difference < 0)
                 {
                         return(1);
                         }
                 elseif($difference < $TradeAmount)
                 {
                         $contacttypelookup = mysql_query("SELECT deliverymethodoptions.DeliveryMethodName, member.EmailAddress
                                                           FROM deliverymethodoptions, member, membertoaccountlink
                                                             WHERE deliverymethodoptions.DeliveryMethodID = member.DeliveryMethodID
                                                             AND member.MemberID = membertoaccountlink.MemberID
                                                              AND membertoaccountlink.PrimaryContact = 1
                                                               AND membertoaccountlink.AccountID = '$AccountID'");
                         $contacttype = mysql_fetch_array($contacttypelookup);
                         if(($contacttype["DeliveryMethodName"] <> 'Email') or (empty($contacttype["EmailAddress"])))
                         {
                                 $MailBody = "Account #$AccountID has surpassed its credit limit.\n\n";
                                 $MailBody .= "The account contact method is $contacttype[DeliveryMethodName].\n";
                                 $MailBody .= "Please notify the member of this situation.\n\n";
                                 if(!empty($contacttype["EmailAddress"]))
                                 {
                                         $MailBody .= "An automated message will also be sent the the e-mail address registered with this account.  ";
                                         $MailBody .= "This e-mail message should not be considered official notification, however.\n\n";
                                         }
                                 $MailBody .= "-------------------------------------------------------------------------";
                                 $MailBody .= "This message has been generated automatically by the LETS system software";
                                 mail($GLOBALS["SystemEmail"], "Account $AccountID Over Limit", $MailBody);
                                 }
                         if(!empty($contacttype["EmailAddress"]))
                         {
                                $MailBody2 = "This is an automatic message from the $GLOBALS[Systemname] ";
                                 $MailBody2 .= "to inform you that your account, #$AccountID, has exceeded the spending limit.\n\n";
                                 $MailBody2 .= "Please take steps to address this situation immediately.  ";
                                 $MailBody2 .= "Failure to bring your account within your trading limit in a timely manner may result in your buying privileges being revoked.\n\n";
                                 $MailBody2 .= "Should you require assistance in finding income possibilities, please contact the LETS System Administrator, at $GLOBALS[SystemEmail].\n\n";
                                 $MailBody2 .= "-------------------------------------------------------------------------";
                                 $MailBody2 .= "This message has been generated automatically by the LETS system software";
                                 mail($contacttype["EmailAddress"], "Account $AccountID Over Limit", $MailBody2, "From: $GLOBALS[SystemEmail]");
                                 }
                         $lookupaccountmember = mysql_query("SELECT *
                                                             FROM membertoaccountlink
                                                               WHERE MemberID = '$GLOBALS[MemberID]'
                                                                AND AccountID = '$AccountID'");
                         if(mysql_num_rows($lookupaccountmember) == 1)
                         {
                                 return(2);
                                 }
                         return(1);
                         }
                 if(($balance["CurrentBalance"] - $limit["AccountCreditLimit"] > 0) && ($balance["CurrentBalance"] - $limit["AccountCreditLimit"] < $TradeAmount))
                 {
                         if(!mysql_query("DELETE FROM badtradewarnings
                                           WHERE AboutAccountID = '$AccountID'
                                            AND Cause = 'Over Limit'"))
                         {
                                mail($GLOBALS["admin_email"], 'Database Error', "The system software failed to remove bad trade records for account $AccountID.\n\nThis message is generated automatically");
                                 }
                         }
                 return(0);
                 }

         }


  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: verifytrade.php */
  /*********************************************************************/
?>