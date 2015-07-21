<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/
  include "configuration.php";

  /* call the adminlogin file to ensure admin level access */
  include "adminlogin.php";


  $title = "Create New Member";

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
       Last Modified: March 3, 2002
       Called By:     Nothing
       Calls:         NewUserForm.php
       Description:   This is where an admin would add a new user to the
                      system.

       Modification History:
                    March 3, 2002 - Form Created
  */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /*                     PAGE STARTS HERE     File: userdetail_form.php*/
  /*********************************************************************/
?>

<? /*Perform Queries here*/
include "connectdb.php";

$deliveryOptions = mysql_query("SELECT  DeliveryMethodID, DeliveryMethodName
                                FROM deliverymethodoptions");
?>
<form action="newuserdetail_entry.php" method="post">
<table>
       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">Name</th>
       </tr>
       <tr>
           <th align=left>First Name:</th>
           <td><input name="NewFirstName" type="text" size="15" maxlength="15"></td>
       </tr>

       <tr>
           <th align=left>Middle Name:</th>
           <td><input name="NewMiddleName" type="text" size="15" maxlength="15"></td>
       </tr>

       <tr>
           <th align=left>Last Name:</th>
           <td><input name="NewLastName" type="text" size="15" maxlength="15"></td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>

       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">System Access</th>
       </tr>
       <tr>
           <th align=left>Login ID:</th>
           <td><input name="NewLoginID" type="text" size="10" maxlength="10"></td>
       </tr>
       <tr>
           <th align=left>Password:</th>
           <td><input name="NewPassword" type="password" size="10" maxlength="10"></td>
       </tr>
       <tr>
           <th align=left>Verify Password:</th>
           <td><input name="VerifyPassword" type="password" size="10" maxlength="10"></td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>

       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">Mailing Address</th>
       </tr>
       <tr>
           <th align=left>Address1:</th>
           <td><input type="text" name="NewMailingAddress1" size="50" maxlength="50"></td>
       </tr>
       <tr>
           <th align=left>Address2:</th>
           <td><input type="text" name="NewMailingAddress2" size="50" maxlength="50"></td>
       </tr>
       <tr>
           <th align=left>City:</th>
           <td><input type="text" name="NewMailingCity" size="20" maxlength="20"></td>
       </tr>
       <tr>
           <th align=left>Province:</th>
           <td><input type="text" name="NewMailingProvince" size="2" maxlength="2"></td>
       </tr>
       <tr>
           <th align=left>Postal Code:</th>
           <td><input type="text" name="NewMailingPostalCode" size="7" maxlength="7"></td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>

       <tr>
           <th align=center colspan=2 maxlength="2" bgcolor="#D3D3D3">Street Address</th>
       </tr>
       <tr>
           <th align=left>Address1:</th>
           <td><input type="text" name="NewStreetAddress1" size="50" maxlength="50"></td>
       </tr>
       <tr>
           <th align=left>Address2:</th>
           <td><input type="text" name="NewStreetAddress2" size="50" maxlength="50"></td>
       </tr>
       <tr>
           <th align=left>City:</th>
           <td><input type="text" name="NewStreetCity" size="20" maxlength="20"></td>
       </tr>
       <tr>
           <th align=left>Province:</th>
           <td><input type="text" name="NewStreetProvince" size="2" maxlength="2"></td>
       </tr>
       <tr>
           <th align=left>Postal Code:</th>
           <td><input type="text" name="NewStreetPostalCode" size="7" maxlength="7"></td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>

       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">Phone & Internet</th>
       </tr>
       <tr>
           <th align=left>Home Number:</th>
           <td><input type="text" name="NewHomeNumber" size="20" maxlength="20"></td>
       </tr>
       <tr>
           <th align=left>Other Number:</th>
           <td><input type="text" name="NewOtherNumber" size="20" maxlength="20"></td>
       </tr>
       <tr>
           <th align=left>Email Address:</th>
           <td><input type="text" name="NewEmailAddress" size="50" maxlength="50"></td>
       </tr>
       <tr>
           <th align=left>Homepage Address:</th>
           <td><input type="text" name="NewHomeURL" size="50" maxlength="128"></td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
       </tr>

       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">Options</th>
       </tr>
       <tr>
           <th align=left>Delivery Method:</th>
           <td><select name="NewDeliveryMethodID">
                       <option value="0">Select Method:
                       <option value="0">
                       <?
                         for($i=0; $i< mysql_num_rows($deliveryOptions); $i++)
                         {
                             print('<option value="'.
                                    mysql_result($deliveryOptions, $i, "DeliveryMethodID").
                                    '"');
                             print('>'.
                                    mysql_result($deliveryOptions, $i, "DeliveryMethodName"));
                         }
                       ?>

               </select>
           </td>
       </tr>
       <tr>
           <th align=left>Public Profile:</th>
           <td><textarea name="NewProfile" cols="50" rows="5" maxlength="255" wrap="hard"></textarea></td>
       </tr>
       <tr>
           <th align=left>Show Profile:</th>
           <td>
               <select name="NewProfileEnabled">
                       <option value="0">No
                       <option value="1">Yes
               </select>
           </td>
       </tr>


       <tr>
           <td>&nbsp;</td>
           <td><input type="submit" value="Create Member"></td>
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