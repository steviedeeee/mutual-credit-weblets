<?php

if(empty($AdminType)) {$AdminType='';}

print ("<html>
        <head>
        <LINK REL=StyleSheet HREF=system.css TYPE='text/css' TITLE='LETS System Stylesheet'>
        <title>$Systemname: $title</title>
        </head>
        <body");
if(!empty($background))
{
        print(" background='$background'");
        }
if(!empty($backgroundcolor))
{
        print(" bgcolor='$backgroundcolor'");}
print (">
        <table noborder cellspacing=0 cellpadding=4 width=100%)
        <tr>
        <td class=Logo>
        <img src=$logofile alt='$Systemname logo'>
        </td>
        <td class=Title>$Systemname</td>
        </tr>
        <tr>
        <td valign=top>
        <table class=Menu>");
if(!empty($AuthorizationCode))
{
        switch($AdminType)
        {
                case 'data':
                        print ("<tr><td class=Menu>
                                <a href=admintradeentry.php class=Menu>Enter Trades</a>
                                </td></tr>
				<tr><td class=Menu>
				<a href=admin_adentry.php class=Menu>Administer Advertisements</a>
				</td></tr>
				<tr><td class=Menu>
                                <a href=memberlookup.php class=Menu>Member Lookup</a>
                                </td></tr>
                                <tr><td class=Menu>
                                <a href=accountrenewal.php class=Menu>Account Renewal</a>
                                </td></tr>
                                <tr><td class=Menu>
                                <a href=reports.php class=Menu>System Reports</a>
                                </td></tr>
                                <tr><td class=Menu>
                                <a href=adminlogout.php class=Menu>Admin Logout</a>
                                </td></tr>");
                        break;
                case 'system':
                        print ("<tr><td class=Menu>
                                <a href=admintradeentry.php class=Menu>Enter Trades</a>
                                </td></tr>
				<tr><td class=Menu>
				<a href=admin_adentry.php class=Menu>Administer Advertisements</a>
				</td></tr>
                                <tr><td class=Menu>
                                <a href=memberlookup.php class=Menu>Member Lookup</a>
                                </td></tr>
                                <tr><td class=Menu>
                                <a href=accounthistory.php class=Menu>Account Statements</a>
                                </td></tr>
                                <tr><td class=Menu>
                                <a href=memberadmin.php class=Menu>Member/Account Administration</a>
                                </td></tr>
                                <tr><td class=Menu>
                                <a href=mailing.php class=Menu>System Mailing</a>
                                </td></tr>
                                <tr><td class=Menu>
                                <a href=reports.php class=Menu>System Reports</a>
                                </td></tr>
                                <tr><td class=Menu>
                                <a href=systemadmin.php class=Menu>System Administration</a>
                                </td></tr>
                                <tr><td class=Menu>
                                <a href=adminlogout.php class=Menu>Admin Logout</a>
                                </td></tr>
                                ");
                        break;
                default:
                }
        }
elseif(!empty($MemberID))
{
        print ("<tr><td class=Menu>
                <a href=myhome.php class=Menu>My Home</a>
                </td></tr>
                <tr><td class=Menu>
                <a href=adinputform.php class=Menu>My Ads</a>
                </td></tr>
                <tr><td class=Menu>
                <a href=tradeentry.php class=Menu>Enter trades</a>
                </td></tr>
                <tr><td class=Menu>
                <a href=memberlookup.php class=Menu>Member Lookup</a>
                </td></tr>
                <tr><td class=Menu>
                <a href=admain.php class=Menu>Advertisements</a>
                </td></tr>
                <tr><td class=Menu>
                <a href=info.php class=Menu>About $Systemname</a>
                </td></tr>
                <tr><td class=Menu>
                <a href=logout.php class=Menu>Logout</a>
                </td></tr>
                <tr><td class=Menu>
                &nbsp;
                </td></tr>
                <tr><td class=Menu>
                <a href=adminsystem.php class=Menu>Admin Login</a>
                </td></tr>");
        }
else
{
        print ("<tr><td class=Menu>
                <a href=info.php class=Menu>About $Systemname</a>
                </td></tr>
                <tr><td class=Menu>
                <a href=fivetraders.php class=Menu>LETS Traders</a>
                </td></tr>
                <tr><td class=Menu>
                <a href=admain.php class=Menu>Advertisements</a>
                </td></tr>
                <tr><td class=Menu>
                <a href=login.php class=Menu>Member Login</a>
                </td></tr>");
        }
print ("</table></td>
        <td class=Data>");
?>
