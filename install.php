<? # Install script

/*

This script will install and configure the LETSweb software system,
including the initial set-up of the system database.
                                                        */

/* the following switch statement will step the user through the install process. 
*/

if(empty($Step)) $Step = '';

switch($Step)
{
        case 1:        # mysql config data
        ?>
<html>
<head>
<title>LETSweb System Set-up</title>
</head>
<body>
<center><h1>LETSweb Setup</h1>
<h2>Database Access and Configuration</h2>
</center>
<p>
The first step in setting up the webLETS system is to configure the software to access your mySQL server, and to set-up the structure and core information for the webLETS database.<p>

 Some of the set-up actions require administrative priviledges on your mySQL database, while the day to day operations of webLETS require only read/write access.  For security purposes you may wish to configure the software with a read/write user and password. If you leave those sections blank, the system will be configured with the full admin information.<p>

<form action="install.php" method=POST>
<input type=hidden name=Step value=2>
<table noborder>
<tr bgcolor=#F0F0F0>
<th colspan=2>Database Location</th></tr>
<tr><th align=left>MySQL server IP address:</th>
<td><input type=text name="MySQLaddress" length=50></td></tr>
<tr><th align=left>webLETS database name:</th>
<td><input type=text name="database" length=20></td></tr>
<tr bgcolor=#F0F0F0>
<th colspan=2>Admin User Information</th></tr>
<tr><th align=left>Admin User Name:</th>
<td><input type=text name="adminuser" length=20></td></tr>
<tr><th align=left>Admin User Password:</th>
<td><input type=text name="adminpassword" length=20></td></tr>
<tr bgcolor=#F0F0F0>
<th colspan=2>Read/Write User Information (optional)</th></tr>
<tr><th align=left>Read/Write User Name:</th>
<td><input type=text name="regularuser" length=20></td></tr>
<tr><th align=left>Read/Write User Password:</th>
<td><input type=text name="regularpassword" length=20></td></tr>
<tr bgcolor=#F0F0F0>
<th colspan=2>&nbsp;</th></tr>
<tr><td colspan=2 align=center><input type=submit value="Continue"></td></tr>
</table>
</form>
</body></html>
<?
                exit();
                break;

        case 2:        # process db config
                        if(empty($MySQLaddress)) $MySQLaddress = '';
                        if(empty($adminuser)) $adminuser = '';
                        if(empty($adminpassword)) $adminpassword = '';
                        if(empty($database)) $database = '';
                        if(empty($regularuser)) $regularuser = '';
                        if(empty($regularpassword)) $regularpassword = '';

                        if(!mysql_connect("$MySQLaddress","$adminuser","$adminpassword"))
                        {
                                #print form for wrong information
                                ?>
<html>
<head>
<title>LETSweb System Set-up</title>
</head>
<body>
<center><h1>LETSweb Setup</h1>
<h2>Database Access Error</h2>
</center>
<p>
There is an error in some of the information you provided on the previous page.  The combination of server address and admin username/password you provided failed to access a valid mysql account.  Please correct these values, and try again.
<p>
<form action="install.php" method=POST>
<input type=hidden name=Step value=2>
<?
print ("<input type=hidden name=database value-'$database'>
<input type=hidden name=regularuser value='$regularuser'>
<input type=hidden name=regularpassword value='$regularpassword'>
<table noborder>
<tr bgcolor=#F0F0F0>
<th colspan=2>Database Location</th></tr>
<tr><th align=left>MySQL server IP address:</th>
<td><input type=text name=MySQLaddress length=50 value='$MySQLaddress'></td></tr>
<tr bgcolor=#F0F0F0>
<th colspan=2>Admin User Information</th></tr>
<tr><th align=left>Admin User Name:</th>
<td><input type=text name=adminuser length=20 value='$adminuser'></td></tr>
<tr><th align=left>Admin User Password:</th>
<td><input type=text name='adminpassword' length=20 value='$adminpassword'></td></tr>");
	?>
<tr bgcolor=#F0F0F0>
<th colspan=2>&nbsp;</th></tr>
<tr><td colspan=2 align=center><input type=submit value="Continue"></td></tr>
</table>
</form>
</body>
</html>

<?
                                exit();
                                }
                        if(!mysql_select_db("$database"))
                        {
                                #print form
                                ?>
<html>
<head>
<title>LETSweb System Set-up</title>
</head>
<body>
<center><h1>LETSweb Setup</h1>
<h2>Database Access Error</h2>
</center>
<p>
There is an error in some of the information you provided on the previous page.  The database was not found on the server.  Please correct the value, and try again.
<p>
<form action="install.php" method=POST>
<input type=hidden name=Step value=2>
<?
print ("<input type=hidden name=MySQLaddress value='$MySQLaddress'>
<input type=hidden name=adminuser value='$adminuser'>
<input type=hidden name=adminpassword value='$adminpassword'>
<input type=hidden name=regularuser value='$regularuser'>
<input type=hidden name=regularpassword value='$regularpassword'>
<table noborder>
<tr bgcolor=#F0F0F0>
<th colspan=2>Database Location</th></tr>
<tr><th align=left>MySQL database name:</th>
<td><input type=text name='database' length=50 value='$database'></td></tr>");
	?>
<tr bgcolor=#F0F0F0>
<th colspan=2>&nbsp;</th></tr>
<tr><td colspan=2 align=center><input type=submit value="Continue"></td></tr>
</table>
</form>
</body>
</html>

<?
                                exit();
                                }

                        # process db config

                        if(!is_readable("database.sql"))
                        {
                                ?>
<html>
<head>
<title>LETSweb System Set-up</title>
</head>
<body>
<center><h1>LETSweb Setup</h1>
<h2>Database Configuration File Not Available</h2>
</center>
<p>
In order to configure your database, this install script needs access to the file <em>database.sql</em>.  This file should have been included in your webLETS package.  You may need to check the UNIX file permissions to ensure that there is global read access.
<p>
If the file is missing, you will need to get a new webLETS package, which is available at
<a href="http://www.sourceforge.net/projects/weblets">www.sourceforge.net/projects/weblets</a>.
<p>
<form action="install.php" method=POST>
<input type=hidden name=Step value=2>
<input type=hidden name=MySQLaddress value="$MySQLaddress">
<input type=hidden name=adminuser value="$adminuser">
<input type=hidden name=adminpassword value="$adminpassword">
<input type=hidden name=regularuser value="$regularuser">
<input type=hidden name=regularpassword value="$regularpassword">
<input type=hidden name=database value="$database">
<center>
<input type=submit value="Try Again">
</center>
</form>
</body>
</html>
<?
                                exit();
                                }
			
			$query = '';
			$sql = fopen('database.sql','r');
			while(!feof($sql))
			{
				$line = fgets($sql, 4096);
				if(substr($line,0,1) != '#')
				{
					$query .= $line;
					}
				}
			$query = str_replace("&amp;","&amp","$query");
			$queries = explode(';', "$query");
			while(list($key,$q) = each($queries))
			{

				$q = str_replace("&amp","&amp;","$q");
				if(strlen("$q") >= 10)
				{
					if(!mysql_query("$q"))
					{
						$badquery[] = mysql_error();
						}
					}
				}
			if(!empty($badquery))
			{
                                ?>
<html>
<head>
<title>LETSweb System Set-up</title>
</head>
<body>
<center><h1>LETSweb Setup</h1>
<h2>Database Setup Failed</h2>
</center>
<p>
The system's attempt to set up the structure and initial values in the database has failed.  The database server returned the following error message:<p>
<? while(list($key,$var) = each($badquery))
{
	print ("$var<br>");
	}
 ?>
<p>
If this problem continues you will need to download an updated version of the webLETS package, from <a href="http://www.sourceforge.net/projects/weblets">www.sourceforge.net/projects/weblets</a>.  If you continue to experience this failure after installing the newest release, please contact <a href="mailto:martin_settle@hotmail.com">Marti Settle</a>.
</body>
</html>
<?
                                exit();
                                }

                        # test user login

                        if(!empty($regularuser) && !empty($regularpassword))
                        {
                                mysql_close();

                                if(mysql_connect("$MySQLaddress","$regularuser","$regularpassword"))
                                {

                                        $userisgood = '1';
                                        }
                                }

                        # write config

                        if(!$config = fopen('configuration.php', 'w'))
                        {
                                print ("The system was unable to write your configuration file.  There is a problem with your website set-up, likely relating to file permissions.");

                                exit();
                                }

                        fputs($config, "<?\n");
                        fputs($config, "\$host = '$MySQLaddress';\n");
                        fputs($config, "\$database = '$database';\n");
                        if(!empty($userisgood))
                        {
                                fputs($config, "\$user = '$regularuser';\n");
                                fputs($config, "\$password = '$regularpassword';\n");
                                }
                        else
                        {
                                fputs($config, "\$user = '$adminuser';\n");
                                fputs($config, "\$password = '$adminpassword';\n");
                                }
                        fputs($config,"?>\n");
                        fclose($config);

                        # name and e-mail
                        ?>
<html>
<head>
<title>LETSweb System Set-up</title>
</head>
<body>
<center><h1>LETSweb Setup</h1>
<h2>System and Contact Information</h2>
</center>
<p>
The initial set-up of your system database has been successfully completed.
<p>
The next step begins the process of configuring the software and website to reflect your LETSystem.  Please complete all of the following fields.  Duplicate values for the e-mail fields are acceptable.  If you wish to have system messages sent to more than one e-mail account, include all desired addresses in the input box, separated by a comma.
<p>
<form enctype="multipart/form-data" action="install.php" method=POST>
<input type=hidden name=Step value=3>
<table noborder>
<tr bgcolor="#F0F0F0">
<th colspan=2>LETSystem Information</th></tr>
<tr><th align=left>LETSystem Name:</th>
<td><input type=text name="name" length=50></td></tr>
<tr><th align=left>LETSystem E-mail:</th>
<td><input type=text name="email" length=50></td></tr>
<tr bgcolor="#F0F0F0">
<th colspan=2>Website Design</th></tr>
<tr><th align=left>LETSystem logo file:</th>
<td><input type=file name="logo" length=50></td>
<br><font size=-1></font></tr>
<tr><th align=left>Background Image:</th>
<td><input type=file name="back" length=50></td>
<br><font size=-1></font></tr>
<tr><th align=left>Background Colour:</th>
<td><input type=text name="bgcolor" length=50></td>
<br><font size=-1></font></tr>
<tr bgcolor="#F0F0F0">
<th colspan=2>Software Administrator</th></tr>
<tr><th align=left>Administrator E-mail:</th>
<td><input type=text name="sysemail" length=50><br><font size=-1>This is the address to which details about software errors will be sent.</font></td></tr>
<tr bgcolor="#F0F0F0"><td colspan=2>&nbsp;</td></tr>
<tr><td colspan=2><center><input type=submit value="Continue"></td></tr>
</table>
</form>
</body>
</html>

<?
                exit();
                break;

        case 3:        # write config
                        include "configuration.php";

			if(!empty($_FILES['logo']['name']))
			{
				move_uploaded_file($_FILES['logo']['tmp_name'], $_FILES['logo']['name']);
				chmod($_FILES['logo']['name'], 0644);
				}
			if(!empty($_FILES['back']['name']))
			{
				move_uploaded_file($_FILES['back']['tmp_name'], $_FILES['back']['name']);
				chmod($_FILES['back']['name'], 0644);
				}

                        if(!$config = fopen('configuration.php', 'w'))
                        {
                                print ("The system was unable to write your configuration file.  There is a problem with your website set-up, likely relating to file permissions.");
                                exit();
                                }

                        fputs($config, "<?\n");
                        fputs($config, "\$host = '$host';\n");
                        fputs($config, "\$database = '$database';\n");
                        fputs($config, "\$user = '$user';\n");
                        fputs($config, "\$password = '$password';\n");
                        fputs($config, "\$Systemname = '$name';\n");
                        fputs($config, "\$SystemEmail = '$email';\n");
                        fputs($config, '$logofile = "' . $_FILES['logo']['name'] . "\";\n");
                        fputs($config, '$background = "' . $_FILES['back']['name'] . "\";\n");
                        fputs($config, "\$backgroundcolor = '$bgcolor';\n");
                        fputs($config, "\$Admin_Email = '$sysemail';\n");
                        fputs($config,"?>\n");
                        fclose($config);

                        ?>
<html>
<head>
<title>LETSweb System Set-up</title>
</head>
<body>
<center><h1>LETSweb Setup</h1>
<h2>Member Set-up</h2>
</center>
<p>
Your webLETS system is configured and ready to run.  However, in order to use the Administrative functions of webLETS you will need to have at least one member registered with the system.  You can choose to automatically import data from the QLETS registry software, or you can enter a single member in the form below.<p>

<table noborder width=100%>
<tr>
<td>
<form action="install.php" method=POST>
<input type=hidden name=Step value=4>
<table noborder>
<tr>
<th align=left>First Name:</th><td align=left><input type=text name=FirstName size=12></td></tr>
<tr><th align=left>Last Name:</th><td align=left><input type=text name=LastName size=12></td></tr>
<tr><th align=left>Login:</th><td align=left><input type=text name=Login size=12></td></tr>
<tr><th align=left>Password:</th><td align=left><input type=text name=Password size=12></td></tr>
<tr><td></td><td><p right><input type=submit value="Create this Member"></p><text size=-1><strong>Note:</strong> This member will be linked to the System account.<br>Use the Member/Account Administration function to create and link accounts.</td></tr> </table>
</form>
</td>
<td valign=top align=right><form action=qlets_convert.php method=GET><input type=submit value="Convert QLETS Data"></form></td>
</tr>
</table>
</body>
</html>

<?
                exit();
                break;
        case 4:
                        include "configuration.php";
                        include "connectdb.php";

			$today = date ('Y-m-d');
			$expiry = date('Y-m-d',mktime(0,0,0,1,1,date(('Y')+10))); 
                        mysql_query("INSERT INTO member (MemberFirstName,MemberLastName,LoginID,Password)
                                     VALUES ('$FirstName','$LastName','$Login','$Password')");
			mysql_query("INSERT INTO account (AccountName,AccountCreated,AccountRenewalDate,AccountIsFeeExempt,AccountCreditLimit,AccountTypeID) 
					VALUES ('$Systemname System','$today','$expiry','1','10000',3)");
			mysql_query("INSERT INTO transactions (AccountID, TradeDate, Description,Amount,CurrentBalance) 
					VALUES ('1','$today','Account Created','0','0')");
			mysql_query("INSERT INTO membertoaccountlink (AccountID,MemberID) VALUES(1,1)");
                        if(!$config = fopen('configuration.php', 'w'))
                        {
                                print ("The system was unable to write your configuration file.  There is a problem with your website set-up, likely relating to file permissions.");
                                exit();
                                }

                        fputs($config, "<?\n");
                        fputs($config, "\$host = '$host';\n");
                        fputs($config, "\$database = '$database';\n");
                        fputs($config, "\$user = '$user';\n");
                        fputs($config, "\$password = '$password';\n");
                        fputs($config, "\$Systemname = '$Systemname';\n");
                        fputs($config, "\$SystemEmail = '$SystemEmail';\n");
			fputs($config, "\$SystemAccountID = '1';\n");
                        fputs($config, "\$logofile = '$logofile';\n");
			fputs($config, "\$background = '$background';");
			fputs($config, "\$backgroundcolor = '$backgroundcolor';");
                        fputs($config, "\$Admin_Email = '$Admin_Email';\n");
                        fputs($config,"?>\n");
                        fclose($config);

                        ?>
<html>
<head>
<title>LETSweb System Setup Complete</title>
</head>
<body>
<h1>LETSweb Setup</h1>
<h2>Setup Complete</h2>
Your LETSweb system is ready for use.  You can log into the system using the Login and Password you supplied:<p>
<? print ("<strong>Login:</strong> $Login<br><strong>Password:</strong> $Password<p>"); ?>To access your LETSweb system, use the address 
<? $path="http://".$SERVER_NAME.strrev(strstr(strrev($PHP_SELF),"/"));
print ("<a href='$path'>$path</a>"); ?>.
<p>Access to the system administration functions requires a password.  System set-up uses a default password of "<strong>Password</strong>" for the administrative functions.  <em>It is recommended that you change this password immediately.</em><p>
<center><font size=+1>Enjoy your LETSweb system!</font></center>
</body>
</html>
<?
                        # account import or setup
                exit();
                break;
	default:
        	?>
<html>
<head>
<title>LETSweb System Set-up</title>
</head>
<body>
<center><h1>LETSweb Setup</h1>
<h2>Welcome and Introduction</h2>
</center>
<p>
This script will install and configure the webLETS software on the current server.
<p>
In order to successfully complete installation of the webLETS system you will <strong>need</strong> the following:<ul>
<li>a MySQL database (empty)
<li>the <i>domain name</i> or <i>IP address</i> of the MySQL database server
<li>login information (<i>user ID and password</i>) for a MySQL account authorized to modify data in your database.  This information will be stored in the system configuration file.
<li>login information (<i>user ID and password</i>) for a MySQL account 
authorized to modify <strong>tables</strong> in your database.  This 
account information will be used by this install script only, and will not 
be recorded anywhere, unless the account information above fails to access 
the database.  In that circumstance, the install script will set up the 
system to use this account as the default database login.
<li>the name of your LETSystem.
<li>the e-mail address of your LETSystem administrator. Notifications about account irregularities will be sent to this account, and all system e-mails will appear to originate from this account.
<li>the e-mail address of your LETSystem software administrator (<i>this is probably you!</i>).  System error notices will be sent here.
</ul>
You will also be offered a number of optional website design choices. These include identifying:<ul>
<li>an electronic version of your system logofile, in JPEG or GIF format.  Ideally this logo should be 150 pixels in width and square. This logo will appear in the upper left of every page.
<li>a background image for your site, or
<li>a background colour.
</ul>
Once the system is set-up and configured, you will have the opportunity to set up a system account and an initial user, which will allow you to log in to the system and access the user and account administration functions. <p>
 Alternatively, you can choose to import data from a previous QLets system. To import QLets data, you will need to save the account list to a text file with all options except comments, and upload that file to the server directory that holds the webLETS system.<p>
<form action=install.php method=post>
<input type=hidden name=Step value=1>
<center><input type=submit value="Continue"></center>
</form>
</body>
</html>
        	<?
        	exit();
		break;		
        }


?>

