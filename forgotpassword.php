<?

include "configuration.php";
include "connectdb.php";
include "header.php";

  if (empty($passedEmailAddress))
  {
   ?>
   <form action="forgotpassword.php">
    Enter the email address your account is registered under and your
    login and password information will be emailed to you.<br><br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input name="passedEmailAddress" type="text"><br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<INPUT TYPE="SUBMIT" name="send" value="Request Login Information">
   </form>
   <?
  }
  else
  {
      $headers = '';
      $boundary = "====webLETSoftware." . md5(uniqid(time())) . "====";
      //$headers = "Followup: $Priority\r\n";
      $headers .= "From: $SystemEmail\r\n";
      //$headers .= "MIME-Version:1.0\r\n";
      //$headers .= "Content-Type: multipart/mixed;\r\n\tboundary=\"$boundary\"\r\n\r\n";

      $query = "SELECT DISTINCT LoginID, Password, EmailAddress
                       FROM member, membertoaccountlink,account
                       WHERE member.EmailAddress = '$passedEmailAddress'
                       AND member.memberID = membertoaccountlink.MemberID
                       AND membertoaccountlink.AccountID = account.AccountID
                       AND AccountStatus != 'Closed'";

      $UserIDLookup = mysql_query($query);

      if (mysql_numrows($UserIDLookup) == 0)
      {
          printf("The email address provided could not be found.\n");
          printf("Contact an <a href='mailto:%s'>administrator</a> if you believe this is incorrect.\n", $SystemEmail);
          include "footer.php";
          exit();
      }

      while($UserID = mysql_fetch_array($UserIDLookup))
      {

             $mailbody = "A request was made to retrieve your login information.\n";
             $mailbody .= "     Your login id is: " . $UserID[LoginID] . "\n";
             $mailbody .= "     Your password is: " . $UserID[Password];

             mail("$UserID[EmailAddress]","Lets Automated Password Recovery","$mailbody","$headers");

      }

      printf("Your login information has been successfully emailed to you.<br>");
      printf("If you do not receive an email from Lets within the next day, contact");
      printf("an <a href='mailto:%s'>administrator</a> for help", $SystemEmail);
  }
include "footer.php";

?>
