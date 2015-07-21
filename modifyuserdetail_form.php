<?php
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/
  include "configuration.php";
  include "connectdb.php";
  include "adminlogin.php";
  $title = "Modify Member Details";

  /*********************************************************************/
  /* Include the Header file.  This is the logo, system name, and menu */
  /*********************************************************************/

  include "header.php";

  /*********************************************************************/
  /*
       Writen By:     Shawn Keown
       Last Modified: July 9, 2001
       Called By:     Nothing
       Calls:         UserDetailForm.php
       Description:   This is where a user would edit their data within the
                      system.

       Modification History:
                    July 18, 2001 - Form Created
                    October 27, 2001 - Added all fields
  */
  /*********************************************************************/


  /*********************************************************************/
  /*                     PAGE STARTS HERE     File: userdetail_form.php*/
  /*********************************************************************/

  /*Perform Queries here*/

if (empty($ModifyMemberID))
{
  $ModifyMemberID = "";
}

  ?>
  <form action="modifyuserdetail_form.php" method="post">
        <table>
               <tr>
                   <th align=left>Modify Member ID:</th>
                   <td><input type="text" name="ModifyMemberID" size="15" maxlength="15" value="<?print($ModifyMemberID);?>"></td>
               </tr>
               <tr>
                   <th></th>
                   <td><input type="submit" value="Search"></td>
               </tr>
        </table>
  </form>
  <?

if ($ModifyMemberID <> "")
{
$deliveryOptions = mysql_query("SELECT  DeliveryMethodID, DeliveryMethodName
                                FROM deliverymethodoptions");
$result = mysql_query("SELECT *
                  FROM member
                  WHERE MemberID = '$ModifyMemberID'");

if ($result)
{
    $numberOfRows = mysql_num_rows($result);

    $currentRow = mysql_fetch_array($result);
    if ($currentRow)
    {

?>
<form action="userdetail_entry.php" method="post">
<input type="hidden" value="<?print($ModifyMemberID)?>" name="ModifyMemberID">
<table>
       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">Name</th>
       </tr>
       <tr>
           <th align=left>First Name:</th>

           <td><input name="NewFirstName" type="text" size="15" maxlength="15" value="<?print($currentRow["MemberFirstName"])?>"></td>
       </tr>

       <tr>
           <th align=left>Middle Name:</th>
           <td><input name="NewMiddleName" type="text" size="15" maxlength="15" value="<?print($currentRow["MemberMiddleName"]);?>"></td>
       </tr>

       <tr>
           <th align=left>Last Name:</th>
           <td><input name="NewLastName" type="text" size="15" maxlength="15" value="<?print($currentRow["MemberLastName"]);?>"></td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td><input type="submit" value="Save Changes"></td>
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
           <td><input type="text" name="NewMailingAddress1" size="50" maxlength="50" value="<?print($currentRow["MailingAddress1"]);?>"></td>
       </tr>
       <tr>
           <th align=left>Address2:</th>
           <td><input type="text" name="NewMailingAddress2" size="50" maxlength="50" value="<?print($currentRow["MailingAddress2"]);?>"></td>
       </tr>
       <tr>
           <th align=left>City:</th>
           <td><input type="text" name="NewMailingCity" size="20" maxlength="20" value="<?print($currentRow["MailingCity"]);?>"></td>
       </tr>
       <tr>
           <th align=left>Province:</th>
           <td><input type="text" name="NewMailingProvince" size="2" maxlength="2" value="<?print($currentRow["MailingProvince"]);?>"></td>
       </tr>
       <tr>
           <th align=left>Postal Code:</th>
           <td><input type="text" name="NewMailingPostalCode" size="7" maxlength="7" value="<?print($currentRow["MailingPostalCode"]);?>"></td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td><input type="submit" value="Save Changes"></td>
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
           <td><input type="text" name="NewStreetAddress1" size="50" maxlength="50" value="<?print($currentRow["StreetAddress1"]);?>"></td>
       </tr>
       <tr>
           <th align=left>Address2:</th>
           <td><input type="text" name="NewStreetAddress2" size="50" maxlength="50" value="<?print($currentRow["StreetAddress2"]);?>"></td>
       </tr>
       <tr>
           <th align=left>City:</th>
           <td><input type="text" name="NewStreetCity" size="20" maxlength="20" value="<?print($currentRow["StreetCity"]);?>"></td>
       </tr>
       <tr>
           <th align=left>Province:</th>
           <td><input type="text" name="NewStreetProvince" size="2" maxlength="2" value="<?print($currentRow["StreetProvince"]);?>"></td>
       </tr>
       <tr>
           <th align=left>Postal Code:</th>
           <td><input type="text" name="NewStreetPostalCode" size="7" maxlength="7" value="<?print($currentRow["StreetPostalCode"]);?>"></td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td><input type="submit" value="Save Changes"></td>
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
           <td><input type="text" name="NewHomeNumber" size="20" maxlength="20" value="<?print($currentRow["HomeNumber"]);?>"></td>
       </tr>
       <tr>
           <th align=left>Other Number:</th>
           <td><input type="text" name="NewOtherNumber" size="20" maxlength="20" value="<?print($currentRow["OtherNumber"]);?>"></td>
       </tr>
       <tr>
           <th align=left>Email Address:</th>
           <td><input type="text" name="NewEmailAddress" size="50" maxlength="50" value="<?print($currentRow["EmailAddress"]);?>"></td>
       </tr>
       <tr>
           <th align=left>Homepage Address:</th>
           <td><input type="text" name="NewHomeURL" size="50" maxlength="128" value="<?print($currentRow["HomeURL"]);?>"></td>
       </tr>


       <tr>
           <td>&nbsp;</td>
           <td><input type="submit" value="Save Changes"></td>
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
                             if( mysql_result($deliveryOptions, $i, "DeliveryMethodID")
                                 == mysql_result($result, 0, "DeliveryMethodID") )
                             {
                                 print(" SELECTED ");
                             }
                             print('>'.
                                    mysql_result($deliveryOptions, $i, "DeliveryMethodName"));
                         }
                       ?>

               </select>
           </td>
       </tr>
       <tr>
           <th align=left>Public Profile:</th>
           <td><textarea name="NewProfile" cols="50" rows="5" maxlength="255" wrap="hard"><?print($currentRow["Profile"]);?></textarea></td>
       </tr>
       <tr>
           <th align=left>Show Profile:</th>
           <td>
               <select name="NewProfileEnabled">
                       <option value="0">No
                       <option value="1"<?if($currentRow["ProfileEnabled"] == "1") print(" selected");?>>Yes
               </select>
           </td>
       </tr>

       <tr>
           <td>&nbsp;</td>
           <td><input type="submit" value="Save Changes"></td>
       </tr>


       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">System Login</th>
       </tr>
       <tr>
           <th align=left>Login ID:</th>
           <td>
             <input type="text" name="NewLoginID" size="20" maxlength="20" value="<? 
               print($currentRow["LoginID"]); ?>">
           </td>
       </tr>
       <tr>
           <th align=left>Password:</th>
           <td><input type="text" name="NewPassword" size="20" maxlength="20" value="<? print($currentRow["Password"]); ?>"></td>
       </tr>


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
<?
    }
    else
    {
      printf("Unable to locate MemberID %d from the Database.  Please try again", $ChangeMemberID);
    }
}
else
{
      print("Failed to locate your MemberID in the Database.  Please reLogin and try again");
}
}
  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: Template.php    */
  /*********************************************************************/

  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php";
?>
