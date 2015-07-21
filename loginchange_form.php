<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/

  include "configuration.php";
?>

<?
  /*********************************************************************/
  /* Include the Header file.  This is the logo, system name, and menu */
  /*********************************************************************/
  $title = "Change Login Information";
  include "header.php";
?>

<?
  /*********************************************************************/
  /*
       Writen By:     Shawn Keown
       Last Modified: October 28, 2001
       Called By:     header.php
       Calls:         Nothing
       Description:   This is the page to modify loginid and passwords.

       Modification History:
                    October 28, 2001 - File Created
  */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /*                     PAGE STARTS HERE        File: Template.php    */
  /*********************************************************************/
?>
<?
include "connectdb.php";

$result = mysql_query("SELECT LoginID
                       FROM member
                       WHERE MemberID = '$MemberID'");
?>
<form action="loginchange_entry.php" method="post">
   <table>
       <tr>
           <th align=center colspan=2 bgcolor="#D3D3D3">Login/Password Change</th>
       </tr>
       <tr>
           <th align=left>Login ID:</th>
           <td><input type="text" name="loginid" value=<?print(mysql_result($result, 0, "LoginID"));?> size=10 maxlength=10></td>
       </tr>
       <tr>
           <th align=left>Old Password</th>
           <td><input type="password" name="oldPassword" size=10 maxlength=10></td>
       </tr>
       <tr>
           <th align=left>New Password:</th>
           <td><input type="password" name="newPassword" size=10 maxlength=10></td>
       </tr>
       <tr>
           <th align=left>Confirm New Password:</th>
           <td><input type="password" name="confirmPassword" size=10 maxlength=10></td>
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
  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: Template.php    */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php"
?>

