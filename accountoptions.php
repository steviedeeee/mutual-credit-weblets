<?
/******************************************************************************/
/*
		Written by:	Martin Settle
		Last Modified:	November 2, 2001
		Called by:	header.php
		Calls:		configuration.php
				connectdb.php
				header.php
				footer.php
		Description:	This file will allow the admin authorized user
				to edit the properties of each account type,
				including credit limits.


		Modification History:
				November 2, 2001 - File Created

*/
/******************************************************************************/

/* get the includes out of the way */

include "configuration.php";
include "connectdb.php";

/* confirm that the user is an authorized admin user... */

include "adminlogin.php";

/* set up the PrintForm function.  It will change dependant upon the
destination function to which it is submitting.  If an AccountTypeName
is submitted, it will look up appropriate values. */

function PrintForm($AccountTypeName, $Function)
{
	if(!empty($AccountTypeName))
	{
		$lookupdetails = mysql_query("SELECT * FROM accounttypeoptions
						WHERE AccountTypeName = '$AccountTypeName'");
		$details = mysql_fetch_array($lookupdetails);
		}
	print ("<form action=accountoptions.php method=POST>
		<input type=hidden name=Function value='$Function'>
		<input type=hidden name=AccountTypeID value='$details[AccountTypeID]'>
		<table noborder cellspacing=0 cellpadding=1>
		<tr><th colspan=2 bgcolor=#D3D3D3>Account Type Options</th></tr>
		<tr><th align=left>Account Type Name:</th>
		<td><input type=text name=AccountTypeName value='$details[AccountTypeName]' size=20></td></tr>
		<tr><th align=left>Maximum Members:</th>
		<td><input type=text name=AccountTypeMaxMembers value='$details[AccountTypeMaxMembers]' size=3></td></tr>
		<tr><th align=left>New Account Cost:</th>
		<td><input type=text name=AccountTypeCost value='$details[AccountTypeCost]' size=6></td></tr>
		<tr><th align=left>Renewal Cost:</th>
		<td><input type=text name=AccountTypeRenewalCost value='$details[AccountTypeRenewalCost]' size=6</td></tr>
		<tr><th align=left>Number of Free Ads:</th>
		<td><input type=text name=AccountTypeNumFreeAds value='$details[AccountTypeNumFreeAds]' size=1></td></tr>
		<tr><th align=left>Extra Ad Cost:</th>
		<td><input type=text name=AccountTypeExtraAdCost value='$details[AccountTypeExtraAdCost]' size=6></td></tr>
		<tr><th align=left>Sale Transaction Fee:</th>
		<td><input type=text name=AccountTypeSaleTransactionFee value='$details[AccountTypeSaleTransactionFee]' size=5></td></tr>
		<tr><th align=left>Purchase Transaction Fee:</th>
		<td><input type=text name=AccountTypeBuyTransactionFee value='$details[AccountTypeBuyTransactionFee]' size=5></td></tr>\n");

/* if this is an edit function, print the end of the above form, lookup credit
limits, and print each with a form to delete.  When complete, add a blank form
on a final line that allows a new credit limit to be added... */

	if(!empty($AccountTypeName))
	{
		print ("<tr><td colspan=2 align=right><input type=submit value='Register Changes'></form></td></tr>
			<tr><th colspan=2 bgcolor=#D3D3D3>Credit Limits</th></tr>
			<tr><td>
			<table noborder width=100%><tr><th width=50%>Required<br>Volume</th><th>Credit<br>Limit</th></tr></table>
			</td>
			<td>&nbsp;</td></tr>\n");

		$colour = 1;
		$lookuplimits = mysql_query("SELECT * FROM creditlimits
					      WHERE AccountTypeID = '$details[AccountTypeID]'
					      ORDER BY TradeVolume");
		while($limit = mysql_fetch_array($lookuplimits))
		{
			$colour = -$colour;
			if($colour > 0)
			{
				print "<tr bgcolor=#F0F0F0>";
				}
			else
			{
				print "<tr>";
				}
			print ("<td valign=top><table noborder width=100%><tr><td align=center width=50%>$limit[TradeVolume]</td><td align=center>$limit[CreditLimit]</td></tr></table></td>
				<td align=right><form action=accountoptions.php method=post>
                                <input type=hidden name=AccountTypeName value='$AccountTypeName'>
                                <input type=hidden name=AccountTypeID value='$details[AccountTypeID]'>
				<input type=hidden name=TradeVolume value='$limit[TradeVolume]'>
				<input type=hidden name=CreditLimit value='$limit[CreditLimit]'>
				<input type=hidden name=Function value='DeleteCredit'>
				<input type=submit value='Delete this  Limit'>
				</td></form></tr>\n");
			}
		$colour = -$colour;
		if($colour > 0)
		{
			print "<tr bgcolor=#F0F0F0>";
			}
		else
		{
			print "<tr>";
			}
		print ("<td valign=top><form action=accountoptions.php method=post>
			<input type=hidden name=Function value=AddCredit>
                        <input type=hidden name=AccountTypeName value='$AccountTypeName'>
                        <input type=hidden name=AccountTypeID value='$details[AccountTypeID]'>
			<table noborder width=100%><tr><td align=center width=50%><input type=text name=TradeVolume size=6></td><td align=center><input type=text name=CreditLimit size=6></td></tr></table></td>
			<td align=right><input type=submit value='Add This Setting'></td></form></tr>
			<tr><th colspan=2 bgcolor=#D3D3D3>&nbsp;</th></tr></table>");
		}

/* If there was no AccountTypeName submitted to the function, ask for an
initial credit limit for the new account type, and print the submit button */

	else
	{
		print ("<tr><th align=left>Initial Credit Limit:</th>
			<td><input type=text name=CreditLimit size=6></td></tr>
			<tr><td colspan=2 align=right><input type=submit value='Add this Account Type'></form></td></tr>
			<tr><th colspan=2 bgcolor=#D3D3D3>&nbsp;</th></tr></table>");
		}
	}

/* print the initial welcome and option type form, if the Function variable is
not present */

if(empty($Function))
{
	$title = "Account Options Main Page";
	include "header.php";
	print ("<form action=accountoptions.php method=post>
		<input type=hidden name=Function value=EditCurrent>
		<table noborder>
		<tr><th colspan=3 bgcolor=#D3D3D3>Edit Account Type</th></tr>
		<th align=left valign=top>Account Type:</th><td valign=top>
		<select name=AccountTypeName>");
	$lookuptypes = mysql_query("SELECT AccountTypeName FROM accounttypeoptions");
	while($type = mysql_fetch_array($lookuptypes))
	{
		print "<option value='$type[AccountTypeName]'>$type[AccountTypeName]";
		}
	print ("</select></td>
		<td valign=top align=right><input type=submit value=Edit></td></form></tr>
                <tr><th colspan=3 bgcolor=#D3D3D3>Delete Account Type</th</tr>
                <tr><form action=accountoptions.php method=post>
                <input type=hidden name=Function value=DeleteType>
                <th align=left valign=top>Account Type:</th>
                <td valign=top><select name=AccountTypeName>");
	$lookuptypes = mysql_query("SELECT AccountTypeName FROM accounttypeoptions");
        while($type = mysql_fetch_array($lookuptypes))
        {
        	print "<option value='$type[AccountTypeName]'>$type[AccountTypeName]";
                }
	print ("</select></td>
        	<td valign=top align=right><input type=submit value=Delete><td></form></tr>
        	<tr><th colspan=3 bgcolor=#D3D3D3>Create New Account Type</th></tr>
		<td colspan=3 align=right valign=top>
                <form action=accountoptions.php method=POST>
                <input type=hidden name=Function value=CreateNew>
		<input type=submit value=Create></td></form></tr>
		<tr><th colspan=3 bgcolor=#D3D3D3>&nbsp;</th></tr></table>\n");
	include "footer.php";
        exit();
	}

/* Now we start a switch statement on the value of Function */

if(empty($Function))
{
	$Function = '';
        }
switch($Function)
{

/* First we deal with the option of editting a previously existing account
type, which calls the PrintForm function (above) with the values for that
account type in the form */

	case 'EditCurrent':

		$title = "Edit Options for $AccountTypeName Account Type";
		include "header.php";

/* send the Account Type Name to the form, with instructions to call the
SubmitEdit function */

		PrintForm($AccountTypeName, 'SubmitEdit');

/* end the page, and the switch subsection */

		include "footer.php";
		break;

/* If the function calls to create new, print the form above with no
initial data ...  */

	case 'CreateNew':
		$title = "Add a new Account Type";
		include "header.php";
		PrintForm('','SubmitNew');
		include "footer.php";
		break;

/* The function SubmitEdit should process the changes submitted by the form, and
register the actions in the adminactions table. */

	case 'SubmitEdit':
		if(!mysql_query("UPDATE accounttypeoptions
				  SET AccountTypeName = '$AccountTypeName',
				  AccountTypeMaxMembers = '$AccountTypeMaxMembers',
				  AccountTypeCost = '$AccountTypeCost',
				  AccountTypeRenewalCost = '$AccountTypeRenewalCost',
				  AccountTypeNumFreeAds = '$AccountTypeNumFreeAds',
				  AccountTypeExtraAdCost = '$AccountTypeExtraAdCost',
				  AccountTypeSaleTransactionFee = '$AccountTypeSaleTransactionFee',
				  AccountTypeBuyTransactionFee = '$AccountTypeBuyTransactionFee'
				  WHERE AccountTypeID = '$AccountTypeID'"))
		{
			$error = mysql_error();
			mail($admin_email, 'LETS Update failed', "An attempt to update the accounttypeoptions table in the $Systemname database has failed.  The database returned the following error: \n\n$error\n\nThis message is generated automatically.");
			$title = "Update failed";
			print ("<h2>Unable to process requested changes</h2>
				The system was unable to process the changes you requested.<p>
                                print $error;
                                <hr><p>\n");
			PrintForm($AccountTypeName, 'SubmitEdit');
			include "footer.php";
			exit();
			}
		mysql_query("INSERT INTO adminactions
			      VALUES (NULL,'$MemberID','Altered Options for $AccountTypeName Account Type'");
		$title = "Options for $AccountTypeName Account Type";
		include "header.php";
		print ("<h2>Update Complete</h2>
			The options for the $AccountTypeName account type have been changed as requested.<p>");
		PrintForm($AccountTypeName, 'SubmitEdit');
		include "footer.php";
		exit();

/* Now the CreateNew function, which inputs the submitted data into the
database, and returns a report of the new information, along with a form for
adding new credit limit levels. */

	case 'SubmitNew':
		if(!mysql_query("INSERT INTO accounttypeoptions
				   VALUES ('','$AccountTypeName',
					      '$AccountTypeMaxMembers',
					      '$AccountTypeCost',
					      '$AccountTypeRenewalCost',
					      '$AccountTypeNumFreeAds',
					      '$AccountTypeExtraAdCost',
					      '$AccountTypeSaleTransactionFee',
					      '$AccountTypeBuyTransactionFee')"))
                {
			$title = "Create new account type failed";
			include "header.php";
			print ("<h2>Database Error</h2>
				The system was unable to register $AccountTypeName as a new type of account.  The database returned: \n\n");
			print mysql_error();
			include "footer.php";
			exit();
			}

/* Now we have to lookup the new ID so that we can add the new account credit
limit to the creditlimit table */

		$lookupID = mysql_query("SELECT AccountTypeID FROM accounttypeoptions
					  WHERE AccountTypeName = '$AccountTypeName'");
		$ID = mysql_result($lookupID,0,"AccountTypeID");
		if(!mysql_query("INSERT INTO creditlimits
				  VALUES ('$ID','0','$CreditLimit')"))
		{
			$title = "Unable to add credit limit";
			include "header.php";
			print ("<h2>Database Error</h2>
				The system was able to register you new account type, but was unable to update the credit limits table to add the default credit limit.<p>\nThe database returned: <p>");
			print mysql_error();
			include "footer.php";
			exit();
			}

/* And now record the action in adminactions */

		mysql_query("INSERT INTO adminactions
			      VALUES (NULL,'$MemberID','Created Account Type $ID ($AccountTypeName) with credit limit of $CreditLimit')");

/* Print the results page */

		$title = "New Account Type $AccountTypeName";
		include "header.php";
		print ("A new account type has been created with the following values:<p>
			<table noborder width=100%>
			<tr><th colspan=2 bgcolor=#D3D3D3>Account Type Summary</th></tr>
			<tr><th align=left>Name:</th><td>$AccountTypeName</td></tr>
			<tr><th align=left>Maximum Members:</th><td>$AccountTypeMaxMembers</td></tr>
			<tr><th align=left>Account Cost:</th><td>$AccountTypeCost</td></tr>
			<tr><th align=left>Renewal Cost:</th><td>$AccountTypeRenewalCost</td></tr>
			<tr><th align=left>Number of Free Ads:</th><td>$AccountTypeNumFreeAds</td></tr>
			<tr><th align=left>Extra Ad Cost:</th><td>$AccountTypeExtraAdCost</td></tr>
			<tr><th align=left>Sale Transaction Fee:</th><td>$AccountTypeSaleTransactionFee</td></tr>
			<tr><th align=left>Purchase Transaction Fee:</th><td>$AccountTypeBuyTransactionFee</td></tr>
			<tr><th align=left>Initial Credit Limit:</th><td>$CreditLimit</td></tr>
			<tr><th colspan=2 bgcolor=#D3D3D3>&nbsp;</th></tr>
			<tr><td colspan=2 align=center><form action=accountoptions.php method=POST>
			<input type=hidden name=AccountTypeName value='$AccountTypeName'>
			<input type=hidden name=Function value=EditCurrent>
			<input type=submit value='Edit this Account Type'></form></td></tr></table>\n");
		include "footer.php";
		exit();

/* The next two functions respond to the creditlimit section of the PrintForm
function, adding or deleting credit limits as appropriate.  Each simply run
the function, then return a result statement above the PrintForm routine above */

	case 'DeleteCredit':
		mysql_query("DELETE FROM creditlimits
			      WHERE AccountTypeID = '$AccountTypeID'
			       AND TradeVolume = '$TradeVolume'
			       AND CreditLimit = '$CreditLimit'");
		mysql_query("INSERT INTO adminactions
			      VALUES (NELL,'$MemberID','Deleted $CreditLimit Eco credit limit from $AccountTypeName Account Type')");
		$title = "$AccountTypeName Options -- Credit Limit Removed";
		include "header.php";
		print "<h3>Credit Limit Removed</h3><hr>";
		PrintForm($AccountTypeName, 'EditCurrent');
		include "footer.php";
		exit();

	case 'AddCredit':
		mysql_query("INSERT INTO creditlimits
			      VALUES ('$AccountTypeID','$TradeVolume','$CreditLimit')");
		mysql_query("INSERT INTO adminactions
			     VALUES (NULL,'$MemberID','Add $CreditLimit Eco credit limit to $AccountTypeName Account Type')");
		$title = "$AccountTypeName Options -- Credit Limit Added";
		include "header.php";
		print "<h3>Credit Limit Added</h3><hr>";
		PrintForm($AccountTypeName, 'EditCurrent');
		include "footer.php";
		exit();
        case 'DeleteType':
        	$lookupid=mysql_query("SELECT AccountTypeID FROM accounttypeoptions
                		        WHERE AccountTypeName = '$AccountTypeName'");
                $AccountTypeID = mysql_result($lookupid,0,"AccountTypeID");
                $lookupaccounts=mysql_query("SELECT * FROM account
                			      WHERE AccountTypeID = '$AccountTypeID'");
                switch(mysql_num_rows($lookupaccounts))
                {
                	case 0:
                        	$title="Delete $AccountTypeName account type";
                                include "header.php";
                                print ("<h2>Confirm Delete</h2>
                                	You have asked to have the $AccountTypeName account type removed from the system.  Please confirm this request by clicking the button below.<p>
                                        <form action=accountoptions.php method=post>
                                        <input type=hidden name=AccountTypeName value='$AccountTypeName'>
                                        <input type=hidden name=Function value='ConfirmDelete'>
                                        <input type=submit value='Delete the $AccountTypeName account type'><p>");
                                include "footer.php";
                                exit();
             		default:
                        	$title="Cannot Delete $AccountTypeName";
                                include "header.php";
                                print ("<h2>Cannot Delete $AccountTypeName</h2>
                                	The system is unable to remove the $AccountTypeName account type because one or more accounts are listed as that type.  In order to process this delete request, all accounts of the $AccountTypeName type must first be deleted or reassigned to another type.<p>
                                        The following accounts are listed as $AccountTypeName:<p>
                                        <table noborder width=100%><tr>");
                                $number = 0;
                                while($row = mysql_fetch_array($lookupaccounts))
                                {
                                	$number++;
                                        print "<td width=33%>#$row[AccountID] $row[AccountName]</td>";
                                        if($number==3)
                                        {
                                        	print "</tr><tr>";
                                                $number = 0;
                                                }
                                        }
                                print "</tr></table>";
                                include "footer.php";
                                exit();
                        }
        case 'ConfirmDelete':
        	if(!mysql_query("DELETE FROM accounttypeoptions
                		  WHERE AccountTypeName = '$AccountTypeName'"))
                {
                	$title="Delete failed";
                        include "header.php";
                        print ("<h2>Delete Account Type Failed</h2>
                        	The attempt to delete the $AccountTypeName account type has failed.  The database returned the following error message:<p>");
                        print mysql_error();
                        include "footer.php";
                        exit();
                        }
                mysql_query("INSERT INTO adminactions VALUES (NULL,'$MemberID','Deleted $AccountTypeName account type')");
                $title = "$AccountTypeName type deleted";
                include "header.php";
                print "The $AccountTypeName account type has been successfully removed from the system.<p>Please use the menu on the right to continue.";
                include "footer.php";
                exit();
        default:
		exit();
	}

print "we shouldn't ever be here...";


?>