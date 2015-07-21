<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/
  include "configuration.php";

  /* call the adminlogin file to ensure admin level access */
  include "adminlogin.php";

  $title = "New Account Creation";

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

<?
  /*********************************************************************/
  /*                     PAGE STARTS HERE     File: userdetail_form.php*/
  /*********************************************************************/
?>

<? /*Perform Queries here*/
include "connectdb.php";

$accountTypeOptions = mysql_query("SELECT AccountTypeID, AccountTypeName, AccountTypeMaxMembers
                                FROM accounttypeoptions");


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

  $today = date("Y-m-d");
  $remewDate = date("Y-m-d", mktime(0,0,0,1,1,date("Y")+1));
?>
<form action="newaccount_form.php" method="post" name="ReloadwithAccountType">
<table>
       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">New Account</th>
       </tr>
       <tr>
           <th align=left>Account Type:</th>
           <td>
             <select ONCHANGE="reload()" name="NewAccountTypeID">
               <option value="0">Select Account Type:
               <option value="0">
               <?
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
</form>
<form action="newaccount_entry.php" method="post">
<input type="hidden" name="NewAccountTypeID" value=<? print($NewAccountTypeID);i ?>>
<input type="hidden" name=maxMembers value=<? print($maxMembers); ?>>
       <tr>
           <th align=left>Account Name:</th>
           <td><input name="NewAccountName" type="text" size="20" maxlength="20"></td>
       </tr>

       <tr>
           <th align=left>Renewal Date:</th>
           <td><input name="NewAccountRenewalDate" type="text" size="15" maxlength="15" value="<?print($remewDate);?>"></td>
       </tr>

       <tr>
           <th align=left>Account Created:</th>
           <td><input name="NewAccountCreated" type="text" size="15" maxlength="15" READONLY value="<? print($today); ?>"></td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>

       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">Members</th>
       </tr>
       <tr>
           <th align=left>Primary Member ID:</th>
           <td><input type="text" name="PrimaryMemberID" size="6" maxlength="6"></td>
       </tr>
<?
       for($i=1; $i<$maxMembers; $i++)
       {
?>
       <tr>
           <th align=left>Account Member <? print($i); ?>:</th>
           <td><input type="text" name="NewMember<? print($i); ?>" size="6" maxlength="6"></td>
       </tr>
<?
       }
?>
       <tr>
           <td>&nbsp;</td>
           <td><input type="submit" value="Create Account"></td>
       </tr>
       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>


</table>
</form>
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
