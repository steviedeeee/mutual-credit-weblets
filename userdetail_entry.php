<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/
  include "configuration.php";
  $title = "Member Details Saved";

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
if (!$deliveryOptionCheck)
{
    printf("Invalid Delivery Option Selected<BR>");
    $invalidData = True;
}

$memberIDCheck = mysql_query("SELECT MemberID
                              FROM member
                              WHERE MemberID = '$ModifyMemberID'");

if ($memberIDCheck)
{
    $numberOfRows = mysql_num_rows($memberIDCheck);

    $currentRow = mysql_fetch_array($memberIDCheck);
    if ($currentRow)
    {

       $update_query  = "UPDATE member SET\n";
       $update_query .= "   MemberFirstName = '$NewFirstName',\n";
       $update_query .= "   MemberMiddleName = '$NewMiddleName',\n";
       $update_query .= "   MemberLastName = '$NewLastName',\n";
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
       if(!empty($NewLoginID))
       {
                $update_query .= "   LoginID = '$NewLoginID',\n";
                }
       if(!empty($NewPassword))
       {
                $update_query .= "   Password = '$NewPassword',\n";
                }
       $update_query .= "   ProfileEnabled = '$NewProfileEnabled'\n";
       $update_query .= "WHERE MemberID = '$ModifyMemberID'";
       if ($debug)
       {
         print($update_query);
       }

       if ($invalidData)
       {
           printf("<br> Please press the <em>back</em> button, correct any problems, and resubmit");
       }
       else
       {
           $updateMember = mysql_query($update_query);
           if ($updateMember)
           {
               printf("Member ID %d - %s %s Updated Successfully", $ModifyMemberID, $NewFirstName, $NewLastName);
           }
           else
           {
               printf("An error was encountered updating Member ID %d - %s %s.  Please try again.", $ModifyMemberID, $NewFirstName, $NewLastName);
           }
       }

    }
    else
    {
      print("Failed to fetch Member Details from Database.  Please reLogin and try again");
    }
}
else
{
      print("Failed to locate your MemberID in the Database.  Please reLogin and try again");
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