<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/

  include "configuration.php"
?>

<?
  /*********************************************************************/
  /* Include the Header file.  This is the logo, system name, and menu */
  /*********************************************************************/
  $title = "Member Summary Search";
  include "header.php";
?>

<?
  /*********************************************************************/
  /*
       Writen By:     Shawn Keown
       Last Modified: July 9, 2001
       Called By:     header.php
       Calls:         membersummary.php
       Description:   This page is a lookup for member information

       Modification History:
                    October 29, 2001 - File Created
		    February 12, 2003 - MemberID Search hidden from
				non-admin user
  */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /*                     PAGE STARTS HERE    File: memberlookup.php    */
  /*********************************************************************/
?>
<?
if (empty($LookupMemberID))
{
    $LookupMemberID = "";
}
if (empty($LookupAccountID))
{
    $LookupAccountID = "";
}
if (empty($LookupFirstName))
{
    $LookupFirstName = "";
}
if (empty($LookupLastName))
{
    $LookupLastName = "";
}
if (empty($AccountIDOption))
{
    $AccountIDOption = "Exact";
}
if (empty($MemberIDOption))
{
    $MemberIDOption = "Exact";
}
if (empty($FirstNameOption))
{
    $FirstNameOption = "Starts";
}
if (empty($LastNameOption))
{
    $LastNameOption = "Starts";
}
?>


<table>
       <tr>
           <td align=center colspan=3><form action=accountlist.php method=get><input type=submit value='List All Accounts'></form></td>
       </tr>
<form action="memberlookup.php" method="post">
       <tr>
           <th align=center colspan=3 bgcolor="#D3D3D3">Search For Member</th>
       </tr>

       <tr>
           <th align=left>Account ID:</th>
           <td><input type="text" name="LookupAccountID" size="20" maxlength="15" value="<?print($LookupAccountID);?>"></td>
           <td><select name="AccountIDOption">
                       <option <? if($AccountIDOption == "Exact") print("selected");?> value="Exact">Exact Match
                       <option <? if($AccountIDOption == "Contains") print("selected");?> value="Contains">Contains
                       <option <? if($AccountIDOption == "Starts") print("selected");?> value="Starts">Starts With
               </select></td>
       </tr>

       <tr>
           <th align=left>First Name:</th>
           <td><input type="text" name="LookupFirstName" size="20" maxlength="15" value="<?print($LookupFirstName);?>"></td>
           <td><select name="FirstNameOption">
                       <option <? if($FirstNameOption == "Exact") print("selected");?> value="Exact">Exact Match
                       <option <? if($FirstNameOption == "Contains") print("selected");?> value="Contains">Contains
                       <option <? if($FirstNameOption == "Starts") print("selected");?> value="Starts">Starts With
               </select></td>
       </tr>

       <tr>
           <th align=left>Last Name:</th>
           <td><input type="text" name="LookupLastName" size="20" maxlength="20" value="<?print($LookupLastName);?>"></td>
           <td><select name="LastNameOption">
                       <option <? if($LastNameOption == "Exact") print("selected");?> value="Exact">Exact Match
                       <option <? if($LastNameOption == "Contains") print("selected");?> value="Contains">Contains
                       <option <? if($LastNameOption == "Starts") print("selected");?> value="Starts">Starts With
               </select></td>
       </tr>
       
<? if((!empty($AuthorizationCode)) && ($AdminType == 'system')) { ?>
       <tr>
           <th align=left>Member ID:</th>
           <td><input type="text" name="LookupMemberID" size="20" maxlength="15" value="<?print($LookupMemberID);?>"></td>
           <td><select name="MemberIDOption">
                       <option <? if($MemberIDOption == "Exact") print("selected");?> value="Exact">Exact Match
                       <option <? if($MemberIDOption == "Contains") print("selected");?> value="Contains">Contains
                       <option <? if($MemberIDOption == "Starts") print("selected");?> value="Starts">Starts With
               </select></td>
       </tr>
<? } ?>

       <tr>
           <th></th>
           <td><input type="submit" value="Search"></td>
       </tr>
</table>
</form>
<?
if (($LookupMemberID != "") || ($LookupFirstName != "")
 || ($LookupLastName != "") || ($LookupAccountID != ""))
{
    include "connectdb.php";
    include "membersummary.php";
    $queryCommand = "SELECT DISTINCT member.MemberID
                     FROM member
                     LEFT JOIN membertoaccountlink
                     ON member.MemberID = membertoaccountlink.memberID
                     WHERE ";
    if ($LookupMemberID != "")
    {
        $queryCommand .= "member.MemberID ";
        if ($MemberIDOption == "Exact")
        {
            $queryCommand .= "= '";
        }
        else
        {
            $queryCommand .= "LIKE '";
        }

        if ($MemberIDOption == "Contains")

        {
            $queryCommand .= "%";
        }
        $queryCommand .= "$LookupMemberID";
        if ($MemberIDOption == "Starts" || $MemberIDOption == "Contains")
        {
            $queryCommand .= "%";
        }
        $queryCommand .= "' AND ";
    }
    if ($LookupAccountID != "")
    {
        $queryCommand .= "membertoaccountlink.AccountID ";
        if ($AccountIDOption == "Exact")
        {
            $queryCommand .= "= '";
        }
        else
        {
            $queryCommand .= "LIKE '";
        }

        if ($AccountIDOption == "Contains")

        {
            $queryCommand .= "%";
        }
        $queryCommand .= "$LookupAccountID";
        if ($AccountIDOption == "Starts" || $AccountIDOption == "Contains")
        {
            $queryCommand .= "%";
        }
        $queryCommand .= "' AND ";
    }
    if ($LookupFirstName != "")
    {
        $queryCommand .= "member.MemberFirstName ";
        if ($FirstNameOption == "Exact")
        {
            $queryCommand .= "= ";
        }
        else
        {
            $queryCommand .= "LIKE '";
        }

        if ($FirstNameOption == "Contains")
        {
            $queryCommand .= "%";
        }
        $queryCommand .= "$LookupFirstName";
        if ($FirstNameOption == "Starts" || $FirstNameOption == "Contains")
        {
            $queryCommand .= "%";
        }
        $queryCommand .= "' AND ";
    }
    if ($LookupLastName != "")
    {
        $queryCommand .= "member.MemberLastName ";
        if ($LastNameOption == "Exact")
        {
            $queryCommand .= "= ";
        }
        else
        {
            $queryCommand .= "LIKE '";
        }

        if ($LastNameOption == "Contains")
        {
            $queryCommand .= "%";
        }
        $queryCommand .= "$LookupLastName";
        if ($LastNameOption == "Starts" || $LastNameOption == "Contains")
        {
            $queryCommand .= "%";
        }
        $queryCommand .= "' AND ";
    }
    $queryCommand .= "1=1";
    if ($debug)
    {
        print($queryCommand);
    }
    $memberIDs = mysql_query($queryCommand);

    printf("<h3>%d Members Found", mysql_num_rows($memberIDs));
    print("<BR><HR><BR>");
    $rowNumber = 0;
    while($currentMember = mysql_fetch_array($memberIDs))
    {
        $rowNumber++;
        printf("<h3>Result #%d:", $rowNumber);
        showMemberSummary($currentMember["MemberID"]);
        print("<BR><HR><BR>");
    }
}
?>





<?
  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: Template.php    */
  /*********************************************************************/
?>


<?
  /*********************************************************************/
  /* And finally include the Footer file.  This makes things nice and  */
  /* and proclaims to the world how amazing we all are.                */
  /*********************************************************************/

  include "footer.php"
?>