<?php
/*********************************************************************/
/*
       Writen By:     Shawn Keown
       Last Modified: July 9, 2001
       Called By:     Login.php
       Calls:         Announcement.php

       Description:   This is the page that processes the login request

       Modification History:
                    July 9, 2001 - ProcessLogin Page Created
                    April 25, 2001 - Admin Fee subroutine added
		    Jan 16, 2003 - INSERT SQL modified for flexibility
*/
/*********************************************************************/
/* BECAUSE PHP IS LIMITED THERE CAN BE NO WHITESPACE PRIOR TO THE */
/* SET COOKIE COMMAND WHICH IS WHY FOR THIS ONE FILE THE INCLUDES */
/* ARE AFTER THE MAJORITY OF THE CODE */

#This is the function to set up the transaction ID for the current balance transfer

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

include "configuration.php";
include "connectdb.php";
$result = mysql_query("SELECT MemberID,MemberFirstName,PriorLogin
                               FROM member
                               WHERE LoginID = '$LoginID'
                                 AND Password = '$Password'");
if ($result)
{
      $numberOfRows = mysql_num_rows($result);
      switch($numberOfRows)
      {
          case 1:
               $currentRow = mysql_fetch_row($result);
               if (!$currentRow)
               {
                   mail($admin_email, "Inconceivable Error","I don't think that word means what you think it means.  processLogin.php Line 46 loginid: $LoginID");
                   ?>
                   A Database error has occured the administrators have been notified.  Please try again later.
                   <?
                   exit();
               }
               if($currentRow[2] == 0)
               {
                       if(empty($ApproveFee))
                       {
                               $FeeLookup = mysql_query("SELECT SetupFee FROM administration LIMIT 1");
                               $Fee = mysql_result($FeeLookup,0);
                               if($Fee != '0.00')
                               {
                                       include "header.php";
                                       print ("This appears to be your first login to the $Systemname website.<p>
                                               There is an initial fee of $Fee ecodollars for the set-up of your account on the web system.  Do you agree to paying the set-up fee for your account?<p>
                                               <form action='processlogin.php' method=POST>
                                               <input type=hidden name=LoginID value='$LoginID'>
                                               <input type=hidden name=Password value='$Password'>
                                               <input type=radio name=ApproveFee value='Yes'> Yes, I agree to paying the set-up fee<br>
                                               <input type=radio name=ApproveFee value='No'> No, I will not pay the set-up fee<p>");
                                       $AccountsLookup = mysql_query("SELECT account.AccountID,AccountName
                                                                        FROM membertoaccountlink, account
                                                                        WHERE account.AccountID = membertoaccountlink.AccountID
                                                                        AND membertoaccountlink.MemberID = '$currentRow[0]'");
                                       print mysql_error();
                                       if(mysql_num_rows($AccountsLookup) > 1)
                                       {
                                                print ("You have more than one account associated with your membership.  To which account do you wish to have the set-up fee debited?<p>");
                                                while($Accounts = mysql_fetch_array($AccountsLookup))
                                                {
                                                        print ("<input type=radio name=ChargeTo value='$Accounts[AccountID]'> $Accounts[AccountID]: $Accounts[AccountName]<br>");
                                                        }
                                                }
                                       else
                                       {
                                                $AccountID = mysql_result($AccountsLookup,0);
                                                print ("<input type=hidden name=ChargeTo Value='$AccountID'>");
                                                }
                                       print ("<p><input type=submit value=Continue>
                                               </form>");
                                       include "footer.php";
                                       exit();
                                       }
                               else
                               {
                                       mysql_query("UPDATE member
                                                    SET PriorLogin=1
                                                    WHERE LoginID = '$LoginID'
                                                    AND Password = '$Password'");
                                       }
                               }
                       elseif($ApproveFee == 'Yes')
                       {
                               $FeeLookup = mysql_query("SELECT SetupFee FROM administration");
                               $Fee = mysql_result($FeeLookup,0);
                               $lookupbalance = mysql_query("SELECT CurrentBalance
                                                             FROM transactions
                                                             WHERE AccountID = '$ChargeTo'
                                                             ORDER BY Reference DESC
                                                             LIMIT 1");
                               $balance = mysql_result($lookupbalance,0);
                               $balance = $balance - $Fee;
                               $systembalance = mysql_query("SELECT CurrentBalance
                                                             FROM transactions
                                                             WHERE AccountID = '$SystemAccountID'

                                                             ORDER BY Reference DESC
                                                             LIMIT 1");
                               $system = mysql_result($systembalance,0);
                               $system = $system + $Fee;

                               $Date = date("Y-m-d");
                               $FeeID = TransID($currentRow[0]);
                               mysql_query("INSERT INTO transactions
                                            SET TransactionID = '$FeeID',
					    TradeDate = '$Date',
					    AccountID = '$ChargeTo',
					    Amount = '-$Fee',
					    Description = 'Online Account Set-up',
					    CurrentBalance = '$balance',
					    OtherAccountID = '$SystemAccountID'");
                               
			       print mysql_error();
                               mysql_query("INSERT INTO transactions
                                            SET TransactionID = '$FeeID',
					    TradeDate = '$Date',
					    AccountID = '$SystemAccountID',
					    Amount = '$Fee',
					    Description = 'Online Account Set-up',
					    CurrentBalance = '$system',
					    OtherAccountID = '$ChargeTo'");
                               mysql_query("UPDATE member
                                                    SET PriorLogin=1
                                                    WHERE LoginID = '$LoginID'
                                                    AND Password = '$Password'");
                               }
                       else
                       {
                               $title = "Unable to Login";
                               include "header.php";
                               print ("<strong>Sorry</strong>,<p>use of the online web system requires payment of the system set-up fee.
                                       <p>You may continue to use the system as a guest, or if you choose to register your login and password, select \"Member Login\" and choose \"Yes\" when asked if you consent to having your account debited.");
                               include "footer.php";
                               exit();
                               }
                       }
               SetCookie("MemberID", $currentRow[0], time()+3600);
               $MemberID = $currentRow[0];
               SetCookie("MemberFirstName", $currentRow[1], time()+3600);
               $MemberFirstName = $currentRow[1];
               include "myhome.php";
               break;
          case 0:
               SetCookie("MemberID", 0, time()-3600);
               unset($MemberID);
               SetCookie("MemberFirstName", "", time()-3600);
               unset($MemberFirstName);
               $title = "Login Failed";
               include "header.php";
               ?>Login Rejected.<BR>
	       Forgot your login information? <a href=forgotpassword.php>Click here.</a><?
               break;
          default:
               SetCookie("MemberID", 0, time()-3600);
               SetCookie("MemberFirstName", "", time()-3600);
               $title = "DatabaseError";
               unset($MemberFirstName);
               include "header.php";
               mail($admin_email, "Inconceivable Error","I don't think that word means what you think it means.  processLogin.php Line 75 loginid: $LoginID");
               ?>
                 A Database error has occured the administrators have been notified.  Please try again later.
               <?
               unset($MemberID);
               break;
      }
      mysql_free_result($result);
  }
  else
  {
      include "header.php";
      ?>ERROR GETTING MEMBER INFO<p><?
      include "footer.php";
      exit();
  }
?>

<?
  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php"
?>
