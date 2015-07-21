<?
  /*********************************************************************/
  /*
       Written By:    Martin Settle
       Last Modified: September 16, 2001
       Called By:     Administration System
       Calls:         AdminLogin.php

       Description:   This page allows an admin-authorized user to edit
       		      the ad categories and headings

       Modification History:
                    September 16, 2001 - page created.
  */
  /*********************************************************************/

  /*********************************************************************/
  /*                     PAGE STARTS HERE        File: adcategories.php*/
  /*********************************************************************/

include "configuration.php";

/* Run the AdminLogin routine to verify the user has been authorized for
admin processing */

include "adminlogin.php";

include "connectdb.php";

/* Use the Function variable returned with the form (at end of page) to determine
which subroutine to run (could use switch, but I like ifs for clarity).  For the
DeleteHeading function I debated automatically deleting all categories associated
with a heading, but instead chose to force the user to do so manually, because
accidents happen and sometimes people are stupid. */
if(empty($Function))
{
    $Function = "";
    #initialize it to get rid of warnings.
}
if($Function == "DeleteHeading")
{
        $query = mysql_query("SELECT *
        	 	       FROM adcategories
                                WHERE HeadingID = $HeadingID");
        $categoriesExist = mysql_num_rows($query);
        switch($categoriesExist)
        {
        	case 0:
                	if(!mysql_query("DELETE FROM adheadings
                        		  WHERE HeadingID = $HeadingID"))
                        {
                                $title = "Database Error";
                                include "header.php";
                                print "<h2>Error deleting Heading Information</h2>";
                                print "The system was unable to remove the Heading from the database.  The system returned the following error:<p>\n";
                                print mysql_error();
                                include "footer.php";
                                exit();
                                }
                        $title = "Heading deleted";
                        include "header.php";
                        print "<h2>Header Deleted</h2>\n";
                        print "The requested Heading information was successfully removed from the system.<p>To return to the Categories Administration Page, click <a href=\"adcategories.php\">here</a>.\n";
                        include "footer.php";
                        exit();
                default:
                	$title = "Heading Categories exist";
                        include "header.php";
                        print "<h2>Unable to remove heading</h2>";
                        print "The system cannot remove this heading until all categories listed for this header are deleted.  Please return to the <a href=\"adcategories.php\">Categories Administration Page</a> to remove the associated categories.\n";
                        include "footer.php";
                        exit();
                        }
        }

/* Now the AddHeader routine.  It's a bit more straightforward. */

if($Function == "AddHeading")
{
	if(!mysql_query("INSERT INTO adheadings
        		  Values ('', '$HeadingName')"))
        {
        	$title = "Database error";
                include "header.php";
                print "<h2>Error adding heading information</h2>";
                print "The system experienced an error while trying to ad the requested heading to the database.<p>The database reported the following error:<p>\n";
                print mysql_error();
                include "footer.php";
                exit();
                }
        $title = "Heading Added";
        include "header.php";
        print "<h2>Heading Added</h2>";
        print "The new heading has been successfully added to the system.  Please return to the <a href=\"adcategories.php\">Categories Administration Page</a> and input appropriate categories for this heading.<p>\n";
        include "footer.php";
        exit();
        }

/* We do the same two routines for Categories, but we don't have to add the lookup
for subcategories, so this is pretty straightforward. */

if($Function == "DeleteCategory")
{
	if(!mysql_query("DELETE FROM adcategories
        		  WHERE CategoryID = '$CategoryID'"))
        {
        	$title = "Database Error";
                include "header.php";
                print "<h2>Error deleting Category information</h2>\n";
                print "The system experienced an error while attempting to remove the category information from the database.  The system returned the following error message: <p>\n";
                print mysql_error();
                include "footer.php";
                exit();
                }
        $title = "Category Removed";
        include "header.php";
        print "<h2>Category Removed</h2>\n";
        print "The requested category has been removed from the system.<p>\nTo return to the Categories Administration Page click <a href=\"adcategories.php\">here</a>.\n";
        include "footer.php";
        exit();
        }

if($Function == "AddCategory")
{
	if(!mysql_query("INSERT INTO adcategories
        		  VALUES('','$CategoryName','$CategoryDescription','$HeadingID')"))
        {
        	$title = "Database Error";
                include "header.php";
                print "<h2>Error adding Category</h2>";
                print "The system experienced an error while attempting to add the Category information to the database. The system returned the following error:<p> \n";
                print mysql_error();
                include "footer.php";
                exit();
                }
        $title = "Category Added";
        include "header.php";
        print "<h2>Category Added</h2>\n";
        print "The requested Category has been successfully added to the database.<p>Click <a href=\"adcategories.php\">here</a> to return to the Category Administration Page, or use the menu on the left.<p>\n";
        include "footer.php";
        exit();
        }

/* If none of these has been run yet, then the user is looking for the menu/form to
run the above functions.  So, now we return that page */

$title = "Ad Category Administration";
include "header.php";
?>

<h2>Ad Category Administration</h2>
The LETS Database is configured to allow users to categorize their ads.  The system
allows for specific categories to be grouped under broader headings, which must be
added to the system prior to categories being created.<p>

<table border=1>
<tr><td colspan=3 bgcolor=#F0F0F0 align=center><font size=4><b>Ad Headings</b></font></td></tr>
<tr>
<td bgcolor=#F0F0F0><b>Add:</b></td>
<form action="adcategories.php" method=post>
<input type=hidden name="Function" value="AddHeading">
<td><input type=text name="HeadingName" length=20></td>
<td><input type=submit value="Add"></td>
</form></tr>
<tr>
<td bgcolor=#F0F0F0><b>Delete:</b></td>
<form action="adcategories.php" method=POST>
<input type=hidden name="Function" value="DeleteHeading">
<td><select name="HeadingID">
<option>
<?
$Headingquery = mysql_query("SELECT *
			      FROM adheadings");
while($row = mysql_fetch_array($Headingquery))
{
	print "<option value=$row[HeadingID]>$row[HeadingName]";
        }
?>
</select>
</td>
<td><input type=submit value="Delete"></td></tr>
</form>
<tr><td colspan=3 bgcolor=#F0F0F0 align=center><font size=4><b>Ad Categories</b></font></td></tr>
<tr>
<td bgcolor=#F0F0F0><b>Add:</b></td>
<form action="adcategories.php" method=post>
<input type=hidden name="Function" value="AddCategory">
<td>
<table noborder>
<tr>
<td>Name:</td><td><input type=text name="CategoryName" length=20><br></td></tr>
<tr><td>Description:</td><td><input type=text name="CategoryDescription" length=40><br></td></tr>
<tr><td>Heading:</td><td><select name="HeadingID"><option>
<?
$Headingquery = mysql_query("SELECT *
			      FROM adheadings");
while($row = mysql_fetch_array($Headingquery))
{
	print "<option value=$row[HeadingID]>$row[HeadingName]";
        }
?>
</td></tr></table></td>
<td><input type=submit value="Add"></td>
</form></tr>
<tr>
<td bgcolor=#F0F0F0><b>Delete:</b></td>
<form action="adcategories.php" method=POST>
<input type=hidden name="Function" value="DeleteCategory">
<td><select name="CategoryID">
<option>
<?
$Categoryquery = mysql_query("SELECT CategoryID, CategoryName, HeadingName
		 	       FROM adcategories, adheadings
                                WHERE adcategories.HeadingID = adheadings.HeadingID
                                 ORDER BY HeadingName, CategoryName");
while($row = mysql_fetch_array($Categoryquery))
{
	print "<option value=$row[CategoryID]>$row[HeadingName]: $row[CategoryName]";
        }
?>
</select>
</td>
<td><input type=submit value="Delete"></td></tr>
</form>
</table>
<p>
<?

include "footer.php";

  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: adcategories.php*/
  /*********************************************************************/
?>
