<?
  /*********************************************************************/
  /*
       Writen By:     Shawn Keown
       Last Modified: October 28, 2001
       Called By:     header.php
       Calls:

       Description:   This is the page that processes the logout request

       Modification History:
                    October 28, 2001 - logout Page Created
  */
  /*********************************************************************/
?>
<?
   /* BECAUSE PHP IS LIMITED THERE CAN BE NO WHITESPACE PRIOR TO THE */
   /* SET COOKIE COMMAND WHICH IS WHY FOR THIS ONE FILE THE INCLUDES */
   /* ARE AFTER THE MAJORITY OF THE CODE */
?>
<?
  SetCookie("MemberID", "", time()-3600);
  unset($MemberID);
  SetCookie("MemberFirstName", "", time()-3600);
  unset($MemberFirstName);
  /* Also logout the Admin to remove the admin menu options */
  SetCookie("AuthorizationCode", "", time()-3600);
  unset($AuthorizationCode);
  SetCookie("LoginTime", "", time()-3600);
  unset($LoginTime);
  include "configuration.php";
  $title = "Logout Success";
  include "header.php";
  print("Logout Successful");
?>

<?
  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php"
?>