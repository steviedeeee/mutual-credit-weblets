<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/

  include "configuration.php";
  $title = "Ad Directory";

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
       Writen By:     Marti Settle
       Last Modified: August 26, 2001
       Called By:     Main Menu
       Calls:         adquery.php
       Description:   This is the initial page in the Ad directory sub-
                      system, which provides the users with search options,
		      and edit/add options for members.

       Modification History:
                    August 26, 2001 - File created
  */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /*                     PAGE STARTS HERE        File: AdMain.php    */
  /*********************************************************************/
?>

<table width=100% border=1>

<!--- Show all the ads --->

<form action="adlisting.php" method="GET">
<tr><td colspan=2><b>Show all the ads!</b></td>
<td><input type=submit value="GO!"></td></tr></form>

<?

// Query by Category (using drop down select box)

include "connectdb.php";

$result = mysql_query("SELECT CategoryID, CategoryName, HeadingName
				 FROM adcategories, adheadings
				  WHERE adcategories.HeadingID = adheadings.HeadingID");


print('<form action="adlisting.php" action="GET">');
print('<tr><td><b>Search by Category:</b></td><td>');
print('<select name="CategoryID">');

while($row = mysql_fetch_row($result))
{
	print("<option value=" . $row[0] . ">" . $row[2] . ": " . $row[1]);
	}
print('</select></td><td><input type=submit VALUE="GO!"></td></tr></form>');

?>

<!--- Query by Trade Type --->

<form action="adlisting.php" action="GET">
<tr>
<td><b>Search by Trade Type:</b></td>
<td>
<table width=100% align=center><tr><td width=50%>
<input type=radio name="TradeType" value="O"> Offered</td>
<td><input type=radio name="TradeType" value="R"> Requested</td>
</tr></table>
</td>
<td><input type=submit value="GO!"></td></tr>
</form>

<!--- Query by Member Number --->

<form action="adlisting.php" action="GET">
<tr><td><b>Search by Account</b></td>
<td>Account Number: <input type=text name="AccountID" cols=4></td>
<td><input type=submit value="GO!"></td></tr>
</form>
</table>
<p>
<hr>
<p>
<?

  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: AdMain.php    */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php"
?>