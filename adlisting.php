<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/

  include "configuration.php";
  $title = "Ad Listings";
?>

<?
  /*********************************************************************/
  /* Include the Header file.  This is the logo, system name, and menu */
  /*********************************************************************/

  include "header.php"
?>

<?
  /*********************************************************************/
  /*
       Written By:    Marti Settle
       Last Modified: August 26, 2001
       Called By:     AdMain.php
       Calls:         none
       Description:   This page processes the input from AdMain to query the
                      database and display the appropriate ads

       Modification History:
                    August 26, 2001 - page created
                    August 28, 2001 - Edit button added
		    February 12, 2003 - filter for expired accounts
		    
		  */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /*                     PAGE STARTS HERE        File: AdListing.php   */
  /*********************************************************************/
$today = date("Y-m-d");

include "connectdb.php";

// set up the query
$query = 'SELECT * ';
$query .= 'FROM adheadings, adcategories, advertisements, account ';
$query .= 'WHERE (advertisements.CategoryID = adcategories.CategoryID OR advertisements.CategoryID2 = adcategories.CategoryID OR advertisements.CategoryID3 = adcategories.CategoryID)';
$query .= 'AND adcategories.HeadingID = adheadings.HeadingID ';
$query .= "AND  AdExpiryDate >= '$today' ";
$query .= "AND  AdBeginDate <= '$today'";
$query .= "AND  account.AccountID = advertisements.AccountID ";
$query .= "AND  AccountRenewalDate >= '$today' ";

/* $query .= "AND NOT Status = 'Suspended' ";
$query .= "AND NOT Status = 'No Sell' "; */

// check for a search query
if(!empty($HTTP_GET_VARS))
{
        if(!empty($HTTP_GET_VARS['CategoryID']))
        {
                $query .= "AND adcategories.CategoryID = '$CategoryID' ";
                }
        if(!empty($HTTP_GET_VARS['TradeType']))
        {
                $query .= "AND TradeType = '$TradeType' ";
                }
        if(!empty($HTTP_GET_VARS['AccountID']))
        {
                $query .= "AND AccountID = '";
                $query .= $HTTP_GET_VARS['AccountID'];
                $query .= "' ";
                }
        }

// group the results
$query .= 'ORDER BY HeadingName, CategoryName, TradeType, AdName';

// run the query
$mysql_result = mysql_query($query);

$error = mysql_error();
print $error;

// print an apology if there are no ads

if(mysql_num_rows($mysql_result) == 0)
{
        print "Sorry, there are no ads that match your search.<p>Please try again.\n";
        include 'footer.php';
        exit();
        }

// check the MemberID to find the account numbers if logged in...

if(!empty($MemberID))
{
        $memberaccounts = mysql_query("SELECT membertoaccountlink.AccountID
                                        FROM member, membertoaccountlink
                                         WHERE member.MemberID = membertoaccountlink.MemberID
                                         AND member.MemberID = '$MemberID' ");
        while($accountnumbers = mysql_fetch_array($memberaccounts))
        {
                $accounts[] = $accountnumbers["AccountID"];
                }
        }

// get each row and print the data (formatting could use work)

print "<table noborder width=100% cellspacing=0>";

$currenttradetype = '';
$currentcategory = '';

while($row = mysql_fetch_array($mysql_result))
{
        if($row["CategoryName"] != $currentcategory)
        {
                $currentcategory = $row["CategoryName"];
                print "<tr><th bgcolor=#808080 colspan=2>";
                print "<font size='+2'>$row[HeadingName]: $row[CategoryName]</font></th></tr>\n";
		$currenttradetype = '';
                }
        switch($row["TradeType"])
        {
                case "O":
                        $rowtradetype = "Offered";
                        break;
                case "R":
                        $rowtradetype = "Requested";
                        break;
                }
        if($rowtradetype != $currenttradetype)
        {
                $currenttradetype = $rowtradetype;
                print "<tr></th><tr><th colspan=2 bgcolor=#D3D3D3><font size='+1'>$currenttradetype</font></th></tr>\n";
                }
		
        $AdName = stripslashes($row["AdName"]);
	$AdDescription = stripslashes($row["AdDescription"]);
	print ("<tr><td>
                <table cellspacing=1 width=100%>
                <tr><th align=left valign=top width=20%>Item: </th><td><strong>$AdName</strong></td></tr>

               <tr><th align=left valign=top>Description: </th><td>$AdDescription</td></tr>
               <tr><th align=left>Posted On: </th><td>$row[AdBeginDate]</td></tr>

               <tr><th align=left valign=top>Account: </th><td><a href=accountinfo.php?AccountID=$row[AccountID]>$row[AccountID]</a></td></tr>
                </table>");
        print "</td>\n";
// Print a form to edit this add, but only if the viewer is the ad owner

        if(!empty($memberaccounts))
        {
               print "<td>";
               reset($accounts);
               while(list(,$acct) = each($accounts))
                {
                        if($acct == $row["AccountID"])
                        {
                                print("<form action=\"adinputform.php\" method=POST>\n");
                                while(list($key,$value) = each($row))
                                {

                                       print("<input type=hidden name=\"$key\" value=\"$value\">\n");
                                        }
                                print("<input type=submit value=\"Edit this Ad\">\n");
                                print("</form>");
                                print("<form action=\"addelete.php\" method=POST>\n<input type=hidden name=\"AdID\" value=\"$row[AdID]\">\n<input type=submit value=\"Delete this Ad\">\n</form>");
                                }
                        }

               print "</td>\n";
                }
        print "</tr><tr><td colspan=2><hr width=60%></td></tr>";
        }
print "</table>";

  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: AdListing.php   */
  /*********************************************************************/
?>


<?
  /*********************************************************************/

 /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php"
?>