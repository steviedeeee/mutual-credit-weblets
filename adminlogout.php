<?
  /*********************************************************************/
  /*
       Writen By:     Shawn Keown
       Last Modified: July 9, 2001
       Called By:     header.php
       Calls:

       Description:   This is the page that processes the admin logout request

       Modification History:
                    October 28, 2001 - AdminLogout Page Created
  */
  /*********************************************************************/

   /* BECAUSE PHP IS LIMITED THERE CAN BE NO WHITESPACE OUTSIDE OF   */
   /* PHP indicators < ? and ? > PRIOR TO THE SET COOKIE COMMAND THIS*/
   /* IS A PAIN AND IS ALSO WHY FOR THIS ONE FILE THE INCLUDES ARE   */
   /* AFTER THE MAJORITY OF THE CODE */

  include "configuration.php";
  SetCookie("AuthorizationCode", "", time()-3600);
  unset($AuthorizationCode);
  SetCookie("LoginTime", "", time()-3600);
  unset($LoginTime);
  SetCookie("AdminType", "", time()-3600);
  unset($AdminType);
  $title = "Admin Logout Success";
  include "header.php";
  print("Admin Logout Successful");
?>

<?
  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php"
?>