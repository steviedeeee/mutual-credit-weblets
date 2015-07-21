<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/

  include "configuration.php"
?>

<?
  /*********************************************************************/
  /* Include the Header file.  This is the logo, system name, and menu */
  /*********************************************************************/
  $title = "Member Login";
  include "header.php"
?>

<?
  /*********************************************************************/
  /*
    At the head of each page I would like to see the following:
       Writen By:     Shawn Keown
       Last Modified: July 9, 2001
       Called By:     index.php
       Calls:         ProcessLogin.php

       Description:   This is the page that prompts for the login

       Modification History:
                    July 9, 2001 - Login Page Created
  */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /*                     PAGE STARTS HERE        File: Login.php       */
  /*********************************************************************/
?>

<?
  /*Check to see if the parameters exist.  If not default them. */
  if (empty($LoginType))
  {
      $LoginType = "Member";
  }

  if ($LoginType == "Member")
  {
      ?>
      <form action="processlogin.php" method="post">
            <table>
                   <tr>
                       <th> Login ID: </th>
                       <td> <input type="text" name="LoginID"> </td>
                   </tr>
                   <tr>
                       <th> Password: </th>
                       <td> <input type="password" name="Password"> </td>
                   </tr>
                   <tr>
                       <th></th>
                       <td> <input type="submit" value="Connect"> </td>
                   </tr>
		   <tr>
		       <th></th>
		       <td>Forgot your login information?  <a href=forgotpassword.php>Click here.</a></td>
		   </tr>
	   </table>
      </form>
      <?
  }
  else
  {
      ?>
      We do not currently support Guest Logins.  Try back soon
      <?
  }

?>



<?
  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: Login.php       */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php"
?>
