<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/
  include "configuration.php";
  $title = "New Account Created";


  /* call the adminlogin file to ensure admin level access */
  include "adminlogin.php";

?>

<?
  /*********************************************************************/
  /* Include the Header file.  This is the logo, system name, and menu */
  /*********************************************************************/

  include "header.php";
?>

<?
  /*********************************************************************/
  /*
       Writen By:     Shawn Keown
       Last Modified: April 13, 2002
       Called By:     newaccount_form.php
       Calls:         Nothing
       Description:   This is the processing of adding a new account

       Modification History:
                    April 13, 2002 - File Created
  */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /*                     PAGE STARTS HERE     File: newaccount_entry.php*/
  /*********************************************************************/
?>

<? /*Perform Queries here*/
include "connectdb.php";

/* Confirm that the Primary Member ID sent is valid */
$invalidData = False;

$memberCheck = mysql_query("SELECT MemberID
                              FROM member
                              WHERE MemberID = '$PrimaryMemberID'");
$numberOfRows = mysql_num_rows($memberCheck);
if ($memberCheck)
{
    if ($numberOfRows == 0)
    {
      printf("The primary contact could not be found in the membership list.  Please ensure that member %d exists in the database", $PrimaryMemberID);
      $invalidData = True;
    }

    if ($invalidData == False)
    {
      $update_query  = "Insert INTO account SET\n";
      $update_query .= "   AccountName = '$NewAccountName',\n";
      $update_query .= "   AccountTypeID = '$NewAccountTypeID',\n";
      $update_query .= "   AccountRenewalDate = '$NewAccountRenewalDate',\n";
      $update_query .= "   AccountCreated  = '$NewAccountCreated',\n";
      $update_query .= "   AccountIsFeeExempt = '$NewAccountIsFeeExempt',\n";
      $update_query .= "   AccountCreditLimit = '$NewAccountCreditLimit',\n";
      $update_query .= "   AccountStatus = '$NewAccountStatus'\n";

      if ($debug)
      {
           printf("<pre>%s</pre>\n",$update_query);
      }
    }

    if ($invalidData)
    {
        printf("<br> Please press the <em>back</em> button, correct any problems, and resubmit");
    }
    else
    {
        $updateAccount = mysql_query($update_query);

        $accountIDCheck = mysql_query("SELECT AccountID, AccountName
                                       FROM account
                                       WHERE AccountName = '$NewAccountName'");
        $numberOfRows = mysql_num_rows($accountIDCheck);
        if ($numberOfRows <> 1)
        {
            printf("A failure was encountered adding the account.  Please contact the administrators.\n");
        }
        else
        {
            $currentRow = mysql_fetch_array($accountIDCheck);
            $NewAccountID = $currentRow["AccountID"];

            printf("<BR> Account %s Added as ID %d", $NewAccountName, $NewAccountID);

            /* Add the Primary Contact - Always required */
            $update_query  = "Insert INTO membertoaccountlink SET\n";
            $update_query .= "  AccountID = '$NewAccountID',\n";
            $update_query .= "  MemberID = '$PrimaryMemberID',\n";
            $update_query .= "  PrimaryContact = '1'\n";
            if ($debug)
            {
             print($update_query);
            }
            $updatePrimaryContact = mysql_query($update_query);

            /* Add remaining Members */
            for($i=1; $i<$maxMembers; $i++)
            {
                $NewMemberID = "NewMemberID$i";
                $update_query  = "Insert INTO membertoaccountlink SET\n";
                $update_query .= "  AccountID = '$NewAccountID',\n";
                eval("\$update_query .= \"  MemberID = '\$$NewMemberID',\n\";");
                $update_query .= "  PrimaryContact = '0'\n";
                if ($debug)
                {
                     print($update_query);
                }

                $updateAccountMember = mysql_query($update_query);
            }

            //Now create the default trade entry
            $update_query  = "Insert INTO transactions SET\n";
            $update_query .= "  TransactionID = '1',\n";
            $today = date("Y-m-d");
            $update_query .= "  TradeDate = '$today',\n";
            $update_query .= "  AccountID = '$NewAccountID',\n";
            $update_query .= "  Description = 'Account Created',\n";
            $update_query .= "  OtherAccountID = '4',\n";
            $update_query .= "  CurrentBalance = '0.00'\n";
            if ($debug)
            {
             print($update_query);
            }
            $updateTransactions = mysql_query($update_query);

        }
    }
}
else
{
      print("Failed to perform pre-add check.  Please reLogin and try again");
}
?>
<?
  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: Template.php    */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php";
?>