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
  /*
       Writen By:     Shawn Keown
       Last Modified: October 28, 2001
       Called By:     loginchange_form.php
       Calls:         Nothing
       Description:   This is the page to process the modify loginid and
                      passwords request.

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

$checkPassword = mysql_query("SELECT LoginID
                              FROM member
                              WHERE MemberID = '$MemberID'
                                AND Password = '$oldPassword'");

$numberOfRows = mysql_num_rows($checkPassword);
if ($numberOfRows == 0)
{
    $title = "Change Login Information Failed";
    include "header.php";
    print("Failure changing information.  MemberID not found or Old Password Incorrect");
}
else if ($numberOfRows == 1)
{
    if ($confirmPassword != $newPassword)
    {
        $title = "Change Login Information Failed";
        include "header.php";
        print("Failure changing information.  New Password and confirmation did not match");
    }
    else if (strlen($newPassword) < 3)
    {
        $title = "Change Login Information Failed";
        include "header.php";
        print("Failure changing information.  Passwords must be at least 3 characters long");
    }
    else
    {
        $checkLoginID = mysql_query("SELECT MemberID
                                     FROM member
                                     WHERE LoginID = '$loginid'
                                       AND MemberID != '$MemberID'");
        $numberOfRows = mysql_num_rows($checkLoginID);
        if ($numberOfRows != 0)
        {
            $title = "Change Login Information Failed";
            include "header.php";
            print("Failure changing information.  LoginID is already in use by another member");
        }
        else
        {
            $changeInformation = mysql_query("UPDATE member
                                              SET LoginID = '$loginid',
                                                  Password = '$newPassword'
                                              WHERE MemberID = '$MemberID'");

            $title = "Change Login Information Succeeded";
            include "header.php";
            print("Login Information successfully changed.");
        }
    }
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

  include "footer.php"
?>

