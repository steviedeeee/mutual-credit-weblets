<?

include "configuration.php";
include "connectdb.php";
include "adminlogin.php";

if(!empty($Submit))
{
        $datalookup = mysql_query("SELECT AdminPassword FROM administration");
        $data = mysql_fetch_array($datalookup);

        if(!mysql_query("UPDATE administration
                          SET AdminPassword = '$admin', DataPassword = '$dataentry', UpperCreditLimitFactor = '$limitfactor', TradeEntryBy = '$tradeentry'
                          WHERE AdminPassword = '$data[AdminPassword]'"))
        {
                $title = 'Database Error';
                include "header.php";
                print ("There was an error updating the database information.  The server replied:<p>\n");
                print mysql_error();
                include "footer.php";
                exit();
                }

        $newconfig = fopen("configuration.php","w");
        fputs($newconfig,('<?
/* This is the configuration file for the web-enabled LETS system.  It includes
database access information, and global information that is used frequently by
the system software.  */

###################################################
# LETS SYSTEM INFORMATION
#
# SYSTEM NAME (used as title on all pages)

$Systemname = "' . $newSystemname . '";

# SYSTEM ACCOUNT
# This is the account number for the LETS System account, to which all transaction
# fees and administration fees will be credited

$SystemAccountID = "' . $newSystemAccountID . '";

# SYSTEM E-MAIL
# This is the e-mail address for contacting the directors of the system, NOT
# the software administrator, which is the admin_email

$SystemEmail = "' . $newSystemEmail . '";

# ADMIN E-MAIL
# The Admin E-mail is used to send error messages and reports about the system
# software.  This should most likely be set to the person who set up and configured
# the system.

$admin_email = "' . $newadmin_email . '";

####################################################
# SOFTWARE AND WEBSITE CONFIGURATION
#
# BASE URL ADDRESS
# http directory information for system pages

$baseURL = "' . $newbaseURL . '";

# LOGO FILE
# name of the file containing the system logo, which appears in the upper left
# corner of all pages

$logofile = "' . $newlogofile . '";

# BACKGROUND IMAGE
# name of the file containing the background image for the website

$background = "' . $newbackground . '";

# BACKGROUND COLOUR
# this is the hex colour code that is called by the header.php file to use as a
# background if there is no image

$backgroundcolor = "'. $newbackgroundcolor . '";

# DATABASE NAME
# The name of the MySQL database that stores LETS information

$database = "' . $newdatabase . '";

# HOST NAME
# The server on which the MySQL database server is running

$host = "' . $newhost . '";

# USER NAME
# The MySQL user name for access to the database named above

$user = "' . $newuser . '";

# PASSWORD
# The MySQL password for the aforementioned user

$password = "' . $newpassword . '";

#############################################################
# DEBUG SYSTEM
# Note: This section is not administered by the constants.php system.  Debugging
# can be turned on or off by editing this file manually.

        $debug = FALSE;
        error_reporting(1);  /*Display Normal Errors Only*/

        if ($debug)
        {
            ?>DEBUG INFO<br><?
            # phpinfo(); /* Lots of info... too much! */
            error_reporting(255); /*Display All Errors*/
            ?>END DEBUG INFO<p><?
        }

?>'));

/* log the action in the adminactions table.  This should be more detailed,
identifying what constants were changed.... */

        mysql_query("INSERT INTO adminactions
                        VALUES (NULL,'$MemberID', 'Changed system setup constants')");

/* And print the confirmation page */

        $title = "New information registered.";
        include "header.php";
        print "Your changes to the system constants have been registered in the configuration file.";
        include "footer.php";
        exit();

        }


/* If no "submit" is registered, then print the form with current values showing */

$title = "System Constants Configuration";
include "header.php";

$constantslookup = mysql_query("SELECT * FROM administration");
$constants = mysql_fetch_array($constantslookup);

print ("<h2>System Constants</h2>
        <font size=+1><strong>These constants should almost never need to be altered.</strong></font><p>Do so only if you are absolutely confident you know what you are doing.  Misconfiguration could result in the entire system (including this admin page) becoming unusable.
        <form action=constants.php method=POST>
        <table>
        <tr>
        <th colspan=2 bgcolor=#D3D3D3 class=Banner>System Information</th>
        </tr>
        <tr>
        <th align=left>System Name:</th><td><input type=text name=newSystemname value='$Systemname'></td>
        </tr>
        <tr>
        <th align=left>Systam Account Number:</th><td><input type=text name=newSystemAccountID value='$SystemAccountID'></td>
        </tr>
        <tr>
        <th align=left>System E-mail:</th><td><input type=text name=newSystemEmail value='$SystemEmail'></td>
        </tr>
        <tr>
        <th align=left>SysAdmin E-mail:</th><td><input type=text name=newadmin_email value='$admin_email'></td>
        </tr>
        <tr>
        <th colspan=2 bgcolor=#D3D3D3 class=Banner>Website Configuration</th>
        </tr>
        <tr>
        <th align=left>Base URL:</th><td><input type=text name=newbaseURL value='$baseURL'></td>
        </tr>
        <tr>
        <th align=left>Logo File:</th><td><input type=text name=newlogofile value='$logofile'></td>
        </tr>
        <tr>
        <th align=left>Background Image:</th><td><input type=text name=newbackground value='$background'></td>
        </tr>
        <tr>
        <th align=left>Background Colour:</th><td><input type=text name=newbackgroundcolor value='$backgroundcolor'></td>
        </tr>
        <tr>
        <th colspan=2 bgcolor=#D3D3D3 class=Banner>Software Setup</th>
        </tr>
        <th align=left>Trade Entry By:</th><td><select name=tradeentry><option>$constants[TradeEntryBy]");
if($constants["TradeEntryBy"]=='seller')
{
        print '<option>buyer';}
else
{
        print '<option>seller';}
print ("</select></td>
        </tr>
        <tr>
        <th align=left>Upper Limit Factor:</th><td><input type=text name=limitfactor value='$constants[UpperCreditLimitFactor]'></td>
        </tr>
        <tr>
        <th align=left>Data Entry Password:</th><td><input type=text name=dataentry value='$constants[DataPassword]'></td>
        </tr>
        <tr>
        <th align=left>Admin Password:</th><td><input type=text name=admin value='$constants[AdminPassword]'></td>
        </tr>
        <tr>
        <th colspan=2 bgcolor=#D3D3D3 class=Banner>Database Setup</th>
        </tr>
        <tr>
        <th align=left>Server Address:</th><td><input type=text name=newhost value='$host'></td>
        </tr>
        <tr>
        <th align=left>Database Name:</th><td><input type=text name=newdatabase value='$database'></td>
        </tr>
        <tr>
        <th align=left>User Name:</th><td><input type=text name=newuser value='$user'></td>
        </tr>
        <tr>
        <th align=left>User Password:</th><td><input type=text name=newpassword value='$password'></td>
        </tr>
        <tr>
        <th colspan=2 bgcolor=#D3D3D3 class=Banner>&nbsp;</th>
        </tr>
        <tr>
        <td colspan=2 align=right><input type=submit name=Submit value='Submit Changes'></td>
        </tr>
        </table>");
include "footer.php";
?>