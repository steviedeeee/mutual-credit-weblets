<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/
  include "configuration.php";
  $title = "Member Details Saved";


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
       Last Modified: March 3, 2002
       Called By:     NewUserDetailForm.php
       Calls:         Nothing
       Description:   This is the processing of adding a new member

       Modification History:
                    March 3, 2002 - File Created
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

/* Confirm that the delivery method sent is valid */
$invalidData = False;

$deliveryOptionCheck = mysql_query("SELECT  DeliveryMethodID
                                    FROM deliverymethodoptions
                                    WHERE DeliveryMethodID = '$NewDeliveryMethodID'");

/* Validate all of the data passed in */

// Delivery Option
if (!$deliveryOptionCheck)
{
    printf("- Invalid Delivery Option Selected<BR>");
    $invalidData = True;
}

// Passwords Match
if ($NewPassword != $VerifyPassword)
{
    printf("- The password and verify password did not match.<BR>");
    $invalidData = True;
}

// Unique Login ID
$loginIDCheck = mysql_query("SELECT LoginID, MemberID, MemberFirstName, MemberLastName
                             FROM member
                             WHERE LoginID = '$NewLoginID'");
$numberOfRows = mysql_num_rows($loginIDCheck);
if ($numberOfRows > 0 && $NewLoginID != "")
{
    $currentRow = mysql_fetch_array($loginIDCheck);

    printf("- The selected LoginID is already in use by member %d - %s %s.<BR>",$currentRow["MemberID"], $currentRow["MemberFirstName"], $currentRow["MemberLastName"] );
    $invalidData = True;
}

// Required Fields

/* Now check for a duplicate entry */
$memberNameCheck = mysql_query("SELECT MemberID
                              FROM member
                              WHERE MemberFirstName = '$NewFirstName'
                                AND MemberMiddleName = '$NewMiddleName'
                                AND MemberLastName = '$NewLastName'");

if ($memberNameCheck)
{
    $numberOfRows = mysql_num_rows($memberNameCheck);

    if ($numberOfRows > 0)
    {
      print("- A member with that name already exists in the database.<BR>
             New members cannot have the same First, Middle, and Last name<BR>
             as an existing member.<BR>");
      $invalidData = True;
    }

    $update_query  = "Insert INTO member SET\n";
    $update_query .= "   MemberFirstName = '$NewFirstName',\n";
    $update_query .= "   MemberMiddleName = '$NewMiddleName',\n";
    $update_query .= "   MemberLastName = '$NewLastName',\n";
    $update_query .= "   LoginID = '$NewLoginID',\n";
    $update_query .= "   Password = '$NewPassword',\n";
    $update_query .= "   MailingAddress1 = '$NewMailingAddress1',\n";
    $update_query .= "   MailingAddress2 = '$NewMailingAddress2',\n";
    $update_query .= "   MailingCity = '$NewMailingCity',\n";
    $update_query .= "   MailingProvince = '$NewMailingProvince',\n";
    $update_query .= "   MailingPostalCode = '$NewMailingPostalCode',\n";
    $update_query .= "   StreetAddress1 = '$NewStreetAddress1',\n";
    $update_query .= "   StreetAddress2 = '$NewStreetAddress2',\n";
    $update_query .= "   StreetCity = '$NewStreetCity',\n";
    $update_query .= "   StreetProvince = '$NewStreetProvince',\n";
    $update_query .= "   StreetPostalCode = '$NewStreetPostalCode',\n";
    $update_query .= "   HomeNumber = '$NewHomeNumber',\n";
    $update_query .= "   OtherNumber = '$NewOtherNumber',\n";
    $update_query .= "   EmailAddress = '$NewEmailAddress',\n";
    $update_query .= "   HomeURL = '$NewHomeURL',\n";
    $update_query .= "   DeliveryMethodID = '$NewDeliveryMethodID',\n";
    $update_query .= "   Profile = '$NewProfile',\n";
    $update_query .= "   ProfileEnabled = '$NewProfileEnabled'\n";

    if ($debug)
    {
     printf("<pre>%s</pre>\n",$update_query);
    }

    if ($invalidData)
    {
        printf("<br> Please press the <em>back</em> button, correct any problems, and resubmit");
    }
    else
    {
        $updateMember = mysql_query($update_query);

        $memberIDCheck = mysql_query("SELECT MemberID
                              FROM member
                              WHERE MemberFirstName = '$NewFirstName'
                                AND MemberMiddleName = '$NewMiddleName'
                                AND MemberLastName = '$NewLastName'");

        $numberOfRows = mysql_num_rows($memberIDCheck);

        $currentRow = mysql_fetch_array($memberIDCheck);

        printf("<BR> Member %s %s Added as ID %d", $NewFirstName, $NewLastName, $currentRow["MemberID"]);

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