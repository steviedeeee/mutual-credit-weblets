<?php
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/
  include "configuration.php";
  include "connectdb.php";
  /* call the adminlogin file to ensure admin level access */
  include "adminlogin.php";

  $title = "Modify Account";

  /*********************************************************************/
  /* Include the Header file.  This is the logo, system name, and menu */
  /*********************************************************************/

  include "header.php";

  /*********************************************************************/
  /*
       Writen By:     Shawn Keown
       Last Modified: March 23, 2002
       Called By:     Nothing
       Calls:         newaccount_entry.php
       Description:   This is where an admin would add a new account to the
                      system.

       Modification History:
                    March 23, 2002 - Form Created
  */
  /*********************************************************************/
?>

<!--- Javascript Functions --->
<script language="JAVASCRIPT">
function reload()
{
     document.ReloadwithAccountType.submit()
}
</script>

<?php
  /*********************************************************************/
  /*                     PAGE STARTS HERE     File: userdetail_form.php*/
  /*********************************************************************/

  /*Perform Queries here*/

$accountTypeOptions = mysql_query("SELECT AccountTypeID, AccountTypeName, AccountTypeMaxMembers
                                FROM accounttypeoptions");

if (empty($ModifyAccountID))
{
 $ModifyAccountID = "";
}

?>
  <form action="modifyaccount_form.php" method="post">
        <table>
            <tr>
                <th align="left">Modify Account ID:</th>
                <td>
                    <input type="text" name="ModifyAccountID" size="15" maxlength="15" 
                        value="<?print($ModifyAccountID);?>">
                </td>
            </tr>
            <tr>
                <th></th>
                <td><input type="submit" value="Search"></td>
            </tr>
        </table>
  </form>
<?php

if (!empty($ModifyAccountID))
{
    $result = mysql_query("SELECT *
                           FROM account
                           WHERE AccountID = '$ModifyAccountID'");
    if (!$result)
    {
        print ("Internal error querying account: " . $mysql_error());
        exit;
    }

    $numberOfRows = mysql_num_rows($result);
    if ($numberOfRows == 0)
    {
        print ("Could not Locate Account.  Please Verify it is valid and try again.");
    }
    else
    {
        $currentRow = mysql_fetch_array($result);
        if ($currentRow)
        {
            if (empty($NewAccountTypeID))
            {
                // grab the existing AccountType.... Hmmm...
                $NewAccountTypeID = $currentRow["AccountTypeID"];
            }

            // if we have the account typeid then we need to calculate how many
            // member entries to display.  So lets find that out.
            if ($NewAccountTypeID != 0)
            {
                $maxMembersCheck = mysql_query("SELECT AccountTypeMaxMembers
                                                FROM accounttypeoptions
                                                WHERE AccountTypeID = '$NewAccountTypeID'");

                $AccountRow = mysql_fetch_array($maxMembersCheck);
                $maxMembers = $AccountRow["AccountTypeMaxMembers"];
            }
            else
            {
                $maxMembers = 0;
            }

            $MemberResult = mysql_query("SELECT *
                                         FROM membertoaccountlink
                                         WHERE AccountID = '$ModifyAccountID'
                                         ORDER BY PrimaryContact DESC");
            if (mysql_num_rows($MemberResult) > $maxMembers)
            {
                $maxMembers = mysql_num_rows($MemberResult);
            }
?>
<form action="modifyaccount_form.php" method="post" name="ReloadwithAccountType">
<input type="hidden" name="ModifyAccountID" value="<?print($ModifyAccountID);?>">
<table>
       <tr>
           <th align="center" colspan="2" bgcolor="#D3D3D3">New Account</th>
       </tr>
       <tr>
           <th align="left">Account Type:</th>
           <td>
             <select ONCHANGE="reload()" name="NewAccountTypeID">
               <option value="0">Select Account Type:
               <option value="0">
               <?php
                 for($i=0; $i< mysql_num_rows($accountTypeOptions); $i++)
                 {
                     print('<option value="'.
                         mysql_result($accountTypeOptions, $i, "AccountTypeID").
                         '"');
                     if ($NewAccountTypeID == mysql_result($accountTypeOptions, $i, "AccountTypeID"))
                     {
                         print(' selected ');
                     }

                     print('>'.
                         mysql_result($accountTypeOptions, $i, "AccountTypeName"));
                 }
               ?>
             </select>
           </td>
       </tr>
</table>
</form>

<form action="modifyaccount_entry.php" method="post" name="SubmitData">
<input type="hidden" name="NewAccountTypeID" value="<?print($NewAccountTypeID);?>">
<input type="hidden" name="maxMembers" value="<?print($maxMembers);?>">
<input type="hidden" name="ModifyAccountID" value="<?print($ModifyAccountID);?>">
<table>
       <tr>
           <th align="left">Account Name:</th>
           <td>
               <input name="NewAccountName" type="text" size="20" maxlength="20" 
                   value="<?print($currentRow["AccountName"]);?>">
           </td>
       </tr>

       <tr>
           <th align="left">Renewal Date:</th>
           <td>
               <input name="NewAccountRenewalDate" type="text" size="15" maxlength="15" 
                   value="<?print($currentRow["AccountRenewalDate"]);?>">
           </td>
       </tr>

       <tr>
           <th align="left">Account Created:</th>
           <td>
               <input name="NewAccountCreated" type="text" size="15" maxlength="15" READONLY 
                   value="<?print($currentRow["AccountCreated"]);?>">
           </td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>

       <tr>
           <th align="center" colspan="2" bgcolor="#D3D3D3">Administration</th>
       </tr>
       <tr>
           <th align="left">Account is Fee Exempt?:</th>
           <td>
               <select name="NewAccountIsFeeExempt">
                   <option value="0" <?if($currentRow["AccountIsFeeExempt"] == "0"){print("selected");}?>>No
                   <option value="1" <?if($currentRow["AccountIsFeeExempt"] == "1"){print("selected");}?>>Yes
               </select>
           </td>
       </tr>
       <tr>
           <th align="left">Credit Limit:</th>
           <td>
               <input type="text" name="NewAccountCreditLimit" size="4" maxlength="4" 
                   value="<?print($currentRow["AccountCreditLimit"]);?>">
           </td>
       </tr>
       <tr>
           <th align="left">Account Status:</th>
           <td>
               <select name="NewAccountStatus">
                 <option value="OK" <?if($currentRow["AccountStatus"] == "OK") print("selected"); ?>>OK
                 <option value="Suspended" <?if($currentRow["AccountStatus"] == "Suspended") print("selected"); ?>>Suspended
                 <option value="Suspended from Sale" <?if($currentRow["AccountStatus"] == "Suspended from Sale") print("selected"); ?>>Suspended from Sale
                 <option value="Suspended from Buy" <?if($currentRow["AccountStatus"] == "Suspended from Buy") print("selected"); ?>>Suspended from Buy
               </select>
           </td>
       </tr>


       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>

       <tr>
           <th align="center" colspan="2" bgcolor="#D3D3D3">Members</th>
       </tr>
<?php
           $MemberRow = mysql_fetch_array($MemberResult);
?>

       <tr>
           <th align="left">Primary Member ID:</th>
           <td>
               <input type="text" name="PrimaryMemberID" size="6" maxlength="6" 
                   value="<?php print($MemberRow["MemberID"]); ?>">
           </td>
       </tr>
<?php
           for($i=1; $i<$maxMembers; $i++)
           {
               $MemberRow = mysql_fetch_array($MemberResult);

               print ('
       <tr>
           <th align="left">Account Member ID:</th>
           <td>
               <input type="text" name="NewMemberID' . $i . '" size="6" maxlength="6" 
                   value="' . $MemberRow["MemberID"] . '">
           </td>
       </tr>
               ');

           }
?>
       <tr>
           <td>&nbsp;</td>
           <td><input type="submit" value="Save Changes"></td>
       </tr>
       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>

</table>
</form>
<?php

        } // endif currentrow

    } // endif rows>0

} // endif ModifyAccountID

  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: Template.php    */
  /*********************************************************************/


  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php";
?>
