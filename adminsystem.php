<?
  include "configuration.php";
  /*********************************************************************/
  /*
       Writen By:     Shawn Keown
       Last Modified: July 9, 2001
       Called By:     Nothing
       Calls:         adminsystem.php
       Description:   This is the introductory or splash page that welcomes
                      users to the LETS Administrative system.

       Modification History:
                    October 28, 2001 - File Created
  */
  /*********************************************************************/


include "adminlogin.php";

$title = "Administration System";
include "header.php";

print ("<h1>Welcome to $Systemname System Administration</h1>
	Please note that as an Administrative User all of your actions will be logged in the system database.<p>
        To access your personal member options, please logout of the Administrative System.<p>");
include "footer.php";

?>