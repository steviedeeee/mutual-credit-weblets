<?
   /*********************************************************************/
  /*
       Writen By:     Martin Settle
       Last Modified: September 13, 2001
       Called By:     Nothing
       Calls:         Nothing
       Description:   This page allows an Admin-authorized user to edit
       		      the categories and headers for the Ad directory.

       Modification History:
                    September 13, 2001 - File created.
  */
  /*********************************************************************/

/* Make sure the user is logged in as a member */

if(empty($MemberID))
{
        $title = "Please login as member";
        include "header.php";
        print "You must be logged in to your LETS Member account in order to access administration functions.<p>\n";
        print "Click <a href=\"login.php\">here</a> to log in.";
        include "footer.php";
        exit();
        }

/* Check to see if the user has already submitted the form to this
script, and if so run the authorization routine */

if(!empty($HTTP_POST_VARS["AdminPassword"]))
{
/* Verify the submitted password */

	include "connectdb.php";
        switch($AdminType)
        {
        	case "system":
        		$Passwordquery = mysql_query("SELECT AdminPassword
        	       			 	       FROM administration
                                		        WHERE AdminPassword = '$AdminPassword'");
		        print mysql_error();
		        $result = mysql_num_rows($Passwordquery);
		        switch($result)
		        {
		                case 0:
		                        $title = "Authorization Failed";
		                        include "header.php";
		                        print "Your Administration Password is incorrect.<p>Authorization refused.";
		                        include "footer.php";
		                        exit();
 		               case 1:
 			               	break;
 		               default:
		                        $title = "Database Configuration Error";
 		                       include "header.php";
 		                       print "There has been an error in authorizing you for administration purposes.  The Administration password is configured incorrectly.  Please contact the system administrator.";
 		                       include "footer.php";
 		                       exit();
 		               }
                         break;
                case "data":
        		$Passwordquery = mysql_query("SELECT DataPassword
        	       			 	       FROM administration
                                		        WHERE DataPassword = '$AdminPassword'");
		        print mysql_error();
		        $result = mysql_num_rows($Passwordquery);
		        switch($result)
		        {
		                case 0:
		                        $title = "Authorization Failed";
		                        include "header.php";
		                        print "Your Administration Password is incorrect.<p>Authorization refused.";
		                        include "footer.php";
		                        exit();
 		               case 1:
 			               	break;
 		               default:
		               		$title = "Database Configuration Error";
 		                    	include "header.php";
 		                        print "There has been an error in authorizing you for administration purposes.  The Administration password is configured incorrectly.  Please contact the system administrator.";
 		                        include "footer.php";
 		                        exit();
 		               }
                         break;
                default:
                         $title = "No Admin type selected";
                         include "header.php";
                         print "You did not enter an Admin Type.  Please use the back button and try again.";
                         include "footer.php";
                         exit();
                }

/* Since we've gotten this far in this if routine (i.e. the script hasn't exited),
the user must have entered the correct Admin password.  So, we can create the cookie
and AdminAuthorization table entry */

	srand(time());
 	$Pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $Pool .= "abcdefghijklmnopqrstuvwxyz";
        $AuthorizationCode = "";
        for($index =0; $index < 12; $index++)
        {
        	$AuthorizationCode .= substr($Pool,(Rand()%(strlen($Pool))), 1);
        }
	$LoginTime = date('YmdHis');

/* Do the insert into the database first, so that the cookie will not be set if the
input fails */

	if(!mysql_query("INSERT INTO adminlogins
        		  VALUES($MemberID,'$AuthorizationCode',$LoginTime,'$AdminType')"))
        {
                $title = "Error in authorization process";
                include "header.php";
                print "The system was unable to register your authorization information.  The database returned the following message: <p>\n";
                print mysql_error();
                include "footer.php";
                exit();
                }

/* Made it.  Set the cookies. */

        SetCookie("AuthorizationCode",$AuthorizationCode, time() + 7200);
        SetCookie("LoginTime",$LoginTime, time() + 7200);
        SetCookie("AdminType",$AdminType, time() + 7200);

        }

/* If the cookie returns an authorization code, check to insure that it matches the
database table.  This may not be necessary, but it does provide an additional layer
of security */

elseif(!empty($HTTP_COOKIE_VARS["AuthorizationCode"]))
{
	include "connectdb.php";
        $AuthorizationCheck = mysql_query("SELECT *
        		      		    FROM adminlogins
                                             WHERE AuthorizationCode = '$AuthorizationCode'
                                              AND MemberID = '$MemberID'
                                               AND LoginTime = '$LoginTime'");
        $result = mysql_num_rows($AuthorizationCheck);
        switch($result)
        {
        	case 0:
                        SetCookie('AuthorizationCode');
                        SetCookie('LoginTime');
                        $title = "Admin Login Failed!";
                        include "header.php";
                        print "Your admin login information is illegitimate.  YOU LOSE!";
                        include "footer.php";
                        exit();
                case 1:
                	break;
                default:
                        $title = "System Error";
                        include "header.php";
                        print "I just realized as I was typing this that this could never happen.  Oh, well";
                        include "footer.php";
                        exit();
                }
        }

/* If we've made it this far, it means that the user has yet to login.  So, print a
form that points back to the page we are on. */

else
{
        $title = "System Administration Login";
        include "header.php";
        ?>
        <h2>System Administration Login</h2>
        Please enter the administration password in the space below.<p>
        <? print "<form action=\"$REQUEST_URI\" method=POST>\n"; ?>
        <table noborder>
        <tr><th align=left>Password: </th>
        <td><input type=password name="AdminPassword" size=20></td></tr>
        <tr><th align=left><b>Admin Type: </th>
        <td><select name="AdminType"><option value="data">data entry<option value="system">system configuration</select></td></tr>
        <tr><th colspan=2><input type=submit value="Authorize"></th>
        </tr>
        </table>
        <?
        include "footer.php";
        exit();
        }