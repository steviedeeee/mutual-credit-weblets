<?

  /*********************************************************************/
  /*
       Writen By:     Martin Settle
       Last Modified: April 27, 2002
       Called By:     memberadmin.php
       Calls:         connectdb
       Description:   allows an admin authorized user to add, edit, or
                      delete a user's ad

       Modification History:
                    April 27, 2002 - Program created
  */
  /*********************************************************************/

# a couple of functions

function printcategories ()
{
	 $Categories = mysql_query("SELECT *
                                    FROM adcategories, adheadings
                                     WHERE adcategories.HeadingID = adheadings.HeadingID
                                      ORDER BY HeadingName, CategoryName");
        while($row = mysql_fetch_array($Categories))
 	{
 	        print("<option value=$row[CategoryID]>$row[HeadingName]: $row[CategoryName]\n");
        	}
	}

function printform($AccountID,
				$AdID,
				$CategoryID,
				$CategoryID2,
				$CategoryID3,
				$TradeType,
				$AdBeginDate,
				$AdExpiryDate,
				$AdName,
				$AdDescription)
{
	print ("<form action=admin_adentry.php method=POST>
			<input type=hidden name=Function value=Process>
			<input type=hidden name=AccountID value='$AccountID'>\n");
	if(!empty($AdID))
	{
		print ("<input type=hidden name=AdID value=$AdID>\n");
		}
	print ("<table noborder>
			<tr><th colspan=2 class=Banner>Advertisement Entry</th></tr>
			<tr><th class=FormLabel valign=top>Trade Type:</th><td><input type=radio name=TradeType value=O");
	if(!empty($TradeType))
	{
		if($TradeType == 'O') print " checked";
		}
	print ("> Item or service offered<br>
			<input type=radio name=TradeType value=R");
	if(!empty($TradeType))
	{
		if($TradeType == 'R') print " checked";
		}
	print (">Item or service requested</td></tr>
			<tr><th class=FormLabel valign=top>Begin Date:</th><td><input type=text name=AdBeginDate");
	if(!empty($AdBeginDate)) print " value='$AdBeginDate'";
	print ("><br>(YYYY-MM-DD)</td></tr>
			<tr><th class=FormLabel valign=top>Expiry Date:</th><td><input type=text name=AdExpiryDate");
	if(!empty($AdExpiryDate)) print " value='$AdExpiryDate'";
	print ("><br>(YYYY-MM-DD)</td></tr>
			<tr><th class=FormLabel valign=top>Categories:</th><td><select name=CategoryID>\n");
	if(!empty($CategoryID))
	{
               $Selected = mysql_query("SELECT CategoryName, HeadingName
                                          FROM adcategories, adheadings
                                           WHERE adcategories.HeadingID = adheadings.HeadingID
                                           AND CategoryID = $CategoryID");
                $result = mysql_fetch_array($Selected);
                print("<option value=$CategoryID>$result[HeadingName]: $result[CategoryName]\n");
		}
	else print "<option>";
	printcategories();
	print ("</select><br>\n<select name=CategoryID2>\n");
	if(!empty($CategoryID2))
	{
               $Selected = mysql_query("SELECT CategoryName, HeadingName
                                          FROM adcategories, adheadings
                                           WHERE adcategories.HeadingID = adheadings.HeadingID
                                           AND CategoryID = $CategoryID2");
                $result = mysql_fetch_array($Selected);
                print("<option value=$CategoryID2>$result[HeadingName]: $result[CategoryName]\n");
		}
	else print "<option>";
	printcategories();
	print ("</select><br>\n<select name=CategoryID3>\n");
	if(!empty($CategoryID2))
	{
               $Selected = mysql_query("SELECT CategoryName, HeadingName
                                          FROM adcategories, adheadings
                                           WHERE adcategories.HeadingID = adheadings.HeadingID
                                           AND CategoryID = $CategoryID3");
                $result = mysql_fetch_array($Selected);
                print("<option value=$CategoryID3>$result[HeadingName]: $result[CategoryName]\n");
		}
	else print "<option>";
	printcategories();
	print ("</select></td></tr>
				<tr><th class=FormLabel>Ad Name: </th><td><input type=text name=AdName");
	if(!empty($AdName))
	{
		$AdName = stripslashes($AdName);
		print (" value='$AdName'");
		}
	print ("></td></tr>
			<tr><th class=FormLabel>Description:</th><td><textarea name=AdDescription cols=50 rows=6>");
	if(!empty($AdDescription)) 
	{
		$AdDescription = stripslashes($AdDescription);
		print "$AdDescription";
		}
	print ("</textarea></td></tr>
			<tr><th colspan=2 class=Banner>&nbsp;</th></tr>
			<tr><td colspan=2 align=center><input type=submit value=");
	if(empty($AdID)) { print "'List this Ad'"; }
	else { print "'Submit Changes'"; }
	print ("></td></tr>
			<tr><th colspan=2 class=Banner>&nbsp;</th></tr>
			</table>
			</form>\n");
	include 'footer.php';
	exit();
	}

# the usual includes...

include "configuration.php";
include "connectdb.php";
include "adminlogin.php";

# set up the page

$title = 'Advertisement Administration';
include "header.php";

# and now a switch on the mode that this is being called in...

if(!empty($AccountID))
{
        /* Check that the AccountID exists */
	$CheckID = mysql_query("SELECT * FROM account WHERE AccountID = '$AccountID' AND AccountRenewalDate > CURDATE()");
	print mysql_error();
	if(mysql_num_rows($CheckID) == 0)
	{
		print("The submitted Account Number is not active.");
		include 'footer.php';
		exit();
		}
	print ("<h2> Advertisments for Account #$AccountID</h2>");
        switch($Function)
        {
                case 'Add':
                        	printform("$AccountID",'','','','','','','','','');
			break;
                case 'Edit':
				$AdDetailLookup = mysql_query("SELECT * FROM advertisements WHERE AdID = '$AdID'");
				$Ad = mysql_fetch_array($AdDetailLookup);
				printform("$AccountID",
						"$Ad[AdID]",
						"$Ad[CategoryID]",
						"$Ad[CategoryID2]",
						"$Ad[CategoryID3]",
						"$Ad[TradeType]",
						"$Ad[AdBeginDate]",
						"$Ad[AdExpiryDate]",
						"$Ad[AdName]",
						"$Ad[AdDescription]");
                        break;
                case 'Delete':
				$AdLookup = mysql_query("SELECT AdName FROM advertisements WHERE AdID = '$AdID'");
				$Ad = mysql_fetch_array($AdLookup);
				print ("Do you really want to delete the advertisement titled \"$Ad[AdName]\"?<p>
						<form action=admin_adentry.php method=POST>
						<input type=hidden name=AccountID value=$AccountID>
						<input type=hidden name=Function value=ConfirmDelete>
						<input type=hidden name=AdID value='$AdID'>
						<input type=submit value='Yes'></form>
						<form action=admin_adentry.php method=POST>
						<input type=hidden name=AccountID value=$AccountID>
						<input type=submit value='No'>\n");
				include 'footer.php';
				exit();
		case 'ConfirmDelete':
				mysql_query("DELETE FROM advertisements WHERE AdID = '$AdID'");
                        break;
		case 'Process':
				$AdName = addslashes("$AdName");
				$AdDescription = addslashes("$AdDescription");
				if(!empty($AdID))
				{
					$query = "UPDATE advertisements ";
					$query .= "SET CategoryID = '$CategoryID',";
					$query .= "CategoryID2 = '$CategoryID2',";
					$query .= "CategoryID3 = '$CategoryID3',";
					$query .= "TradeType = '$TradeType',";
					$query .= "AdBeginDate = '$AdBeginDate',";
					$query .= "AdExpiryDate = '$AdExpiryDate',";
					$query .= "AdName = '$AdName',";
					$query .= "AdDescription = '$AdDescription' ";
					$query .= "WHERE AdID = '$AdID'";
					}
				else
				{
					$query = "INSERT INTO advertisements ";
					$query .= "(AccountID,CategoryID,CategoryID2,CategoryID3,TradeType,AdBeginDate,AdExpiryDate,AdName,AdDescription) ";
					$query .= "VALUES ";
					$query .= "('$AccountID', ";
					$query .= "'$CategoryID', ";
					$query .= "'$CategoryID2', ";
					$query .= "'$CategoryID3', ";
					$query .= "'$TradeType', ";
					$query .= "'$AdBeginDate', ";
					$query .= "'$AdExpiryDate', ";
					$query .= "'$AdName', ";
					$query .= "'$AdDescription')";
					}
				mysql_query("$query");
				break;
                default:
                }
        $AdLookup = mysql_query("SELECT * FROM advertisements WHERE AccountID = '$AccountID' ORDER BY AdExpiryDate DESC");
        while($ad = mysql_fetch_array($AdLookup))
        {

/* This is where we will simply print the ads, with links to edit or delete... */

                print ("<table width=100% noborder>
                        <tr><td>
                        <table width=100% cellspacing=1 cellpadding=0 noborder><tr bgcolor=#D0D0D0>
                        <th colspan=2><font size=+1>$ad[AdName]</font></th></tr>
                        <tr bgcolor=#F0F0F0><th align=left>Ad Begins:</th><td>$ad[AdBeginDate]</td></tr>
                        <tr bgcolor=#F0F0F0><th align=left>Ad Expires:</th><td>$ad[AdExpiryDate]</td></tr>\n");

/*The category routine always seems a little confusing.... */

               $category = mysql_query("SELECT HeadingName, CategoryName
                                          FROM adcategories, adheadings
                                           WHERE adcategories.HeadingID = adheadings.HeadingID
                                           AND CategoryID = $ad[CategoryID]");
                $result = mysql_fetch_array($category);

                print ("<tr bgcolor=#F0F0F0><th align=left valign=top>Categories:&nbsp;</th>
                        <td>$result[HeadingName]: $result[CategoryName]");

                if($ad["CategoryID2"] != "0")
                {
                               $category = mysql_query("SELECT HeadingName, CategoryName
                                                  FROM adcategories, adheadings
                                                   WHERE adcategories.HeadingID = adheadings.HeadingID
                                                   AND CategoryID = $ad[CategoryID2]");
                               $result = mysql_fetch_array($category);

                        print "<br>$result[HeadingName]: $result[CategoryName]";
                        }
                if($ad["CategoryID3"] != "0")
                {
                        $category = mysql_query("SELECT HeadingName, CategoryName
                                                  FROM adcategories, adheadings
                                                   WHERE adcategories.HeadingID = adheadings.HeadingID
                                                   AND CategoryID = $ad[CategoryID3]");
                               $result = mysql_fetch_array($category);

                        print "<br>$result[HeadingName]: $result[CategoryName]";
                        }
                print "\n</b></td></tr><tr bgcolor=#F0F0F0><th align=left>Ad Type: </th><td>";
                      switch($ad["TradeType"])
                      {
                            case 'R':
                                print "Requested";
                                break;
                        case 'O':
                                print "Offered";
                        }
                print "</td></tr>\n";
                print ("<tr bgcolor=#F0F0F0><th valign=top align=left>Description:</th>
                        <td>$ad[AdDescription]</td></tr></table>
                        <p><hr width=60%><p></td><td valign=top>\n");

/* Show the active status */

                if(($ad["AdExpiryDate"] > date("Y-m-d")) && ($ad["AdBeginDate"] < date("Y-m-d")))
                {
                        print "<i>Active</i><br>\n";
                        }
                else
                {
                        print "<i>Inactive</i><br>\n";
                        }

/* and print the edit and delete buttons */

                print ("<form action='admin_adentry.php' Method=POST>
		<input type=hidden name=AccountID value='$AccountID'>
		<input type=hidden name=Function value=Edit>");
                while(list($key,$value) = each($ad))
                {
                        $value = ereg_replace("<br />", "", $value);
                        print "<input type=hidden name=\"$key\" value=\"$value\">\n";
                        }
                print "<input type=submit value='Edit'></form>\n";
                print ("<form action=\"admin_adentry.php\" method=POST>
		<input type=hidden name=AccountID value='$AccountID'>
		<input type=hidden name=Function value=Delete>
		<input type=hidden name=\"AdID\" value=\"" . $ad["AdID"] . "\">
		<input type=submit value='Delete'>\n</form>\n</td></tr></table>");
                }
        print "<p>";
	print ("<center><form action=\"admin_adentry.php\" method=POST>
	<input type=hidden name='AccountID' value='$AccountID'>
	<input type=hidden name=Function value=Add>
	<input type=submit value='Add New Advertisement'></form></center>");
        include "footer.php";
        exit();
        }

# print the account form

print ("<form action=admin_adentry.php method=POST>
        For which account do you wish to administer advertisments?
        <table noborder>
        <tr>
        <th class=FormLabel>Account Number:</th>
        <td><input type=text name=AccountID size=4></td>
        </tr>
        <tr>
        <td colspan=2><center><input type=submit value=Submit></center></td>
        </tr>
        </table>
        ");

include "footer.php";

?>
