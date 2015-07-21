<?php

  /*********************************************************************/
  /*
       Writen By:     Shawn Keown
       Last Modified: July 9, 2001
       Called By:     Nothing
       Calls:         AdminSystem.php
       Description:   This is the introductory or splash page that welcomes
                      users to the LETS Administrative system.

       Modification History:
                    October 28, 2001 - File Created
  */
  /*********************************************************************/


  function printwelcome($MemberFirstName,$Systemname)
  {
            print ("<strong>Welcome to the $Systemname Online System, $MemberFirstName!</strong><p>
                <ul compact style='list-style-position: inside'><font size=-1><strong>This is your home page.  From this page you can:</strong>
                <li>View and edit your contact information and description (profile)
                <li>View your account summary details
                <li>Access your account statement
                <li>Read news about $Systemname</ul></font>
                <center><form action=loginchange_form.php method=post><input type=submit value='Change Login and Password'></form></center>
                <strong>Don't forget to log out when you are finished!</strong>
                ");
         }

  /* This is a function to recalculate an Account's Credit Limit */

  function CheckCreditLimit($AccountID)
  {
        $lookupvolume = mysql_query("SELECT SUM(ABS(Amount)) as Volume
                                      FROM transactions
                                       WHERE AccountID = '$AccountID'
                                       AND Description !='Transaction Fee'");
        $volume = mysql_result($lookupvolume,0,"Volume");

        $lookupcurrentlimit = mysql_query("SELECT AccountCreditLimit
                                            FROM account
                                             WHERE AccountID = '$AccountID'");
        $currentlimit = mysql_result($lookupcurrentlimit,0,"AccountCreditLimit");
        $newlimit = $currentlimit;

        $lookuplimits = mysql_query("SELECT creditlimits.*
                                     FROM creditlimits,account
                                      WHERE creditlimits.AccountTypeID = account.AccountTypeID
                               AND account.AccountID = '$AccountID'
                                        AND CreditLimit > '$currentlimit'
                                         ORDER BY TradeVolume");
        while($limits = mysql_fetch_array($lookuplimits))
        {
                if($limits["TradeVolume"] <= $volume)
                {
                        $newlimit = $limits["CreditLimit"];
                        }
                }

        if($newlimit != $currentlimit)
        {
                mysql_query("UPDATE account
                             SET AccountCreditLimit = '$newlimit'
                             WHERE AccountID = '$AccountID'");
                }
        return($newlimit);
        }

  /* The main page begins here... */

  if(empty($MemberID))
  {
        include "login.php";
        exit();
        }

  include "configuration.php";
  include "connectdb.php";
  $title="My Home";
  include "header.php";

  /* The next section looks up address and profile information for the logged in
  member, and prints it. */

  printwelcome("$MemberFirstName","$Systemname");

  $MemberInfoLookup = mysql_query("SELECT * FROM member WHERE MemberID = '$MemberID'");
  $MemberInfo = mysql_fetch_array($MemberInfoLookup);

  print ("<table noborder width=100%>
        <tr>
        <th colspan=2 bgcolor=#D3D3D3 class='Banner'><font size=+2>$MemberInfo[MemberFirstName] $MemberInfo[MemberLastName]: aka $MemberInfo[LoginID]</font></th>
        </tr>
        <tr>
        <td valign=top>\n");

  $ShortBulletin = 1;
  include 'bulletins.php';

  print ("</td>
        <td>
                <table border=1 cellspacing=0 width=100%>
                <tr>
                <th bgcolor=#D3D3D3 class='Banner'><a href=userdetail_form.php>Contact Info</a></th>
                </tr>
                <tr>
                <td>
                <font size=-1>
                <strong>Mailing Address:</strong><br>
                $MemberInfo[MailingAddress1]<br>\n");

  if(!empty($MemberInfo["MailingAddress2"]))
  {
        print "$MemberInfo[MailingAddress2]<br>\n";
        }
  print ("$MemberInfo[MailingCity], $MemberInfo[MailingProvince]<br>
        $MemberInfo[MailingPostalCode]<p>\n");

  if(!empty($MemberInfo["StreetAddress1"]))
  {
        print ("<strong>Street Address</strong><br>
                $MemberInfo[StreetAddress1]\n");
        if(!empty($MemberInfo["StreetAddress2"]))
        {
                print "<br>$MemberInfo[StreetAddress2]\n";
                }
        if(!empty($MemberInfo["StreetCity"]))
        {
                print "<br>$MemberInfo[StreetCity], $MemberInfo[StreetProvince]\n";
                }
        if(!empty($MemberInfo["StreetPostalCode"]))
        {
                print "<br>$MemberInfo[StreetPostalCode]\n";
                }
        print "<p>";
        }

  print ("<strong>Phone Numbers/Electronic Addresses</strong><br>
        <table noborder>
        <tr><td><font size=-1>Phone:</td><td><font size=-1>$MemberInfo[HomeNumber]</td></tr>\n");
  if(!empty($MemberInfo["MobileNumber"]))
  {
        print "<tr><td><font size=-1>Cell Phone:</td><td><font size=-1>$MemberInfo[MobileNumber]</td></tr>\n";
        }
  if(!empty($MemberInfo["EmailAddress"]))
  {
        print "<tr><td><font size=-1>E-mail:</td><td><font size=-1>$MemberInfo[EmailAddress]</td></tr>\n";
        }
  if(!empty($MemberInfo["HomeURL"]))
  {
        print "<tr><td><font size=-1>Web Page:</td><td><font size=-1>$MemberInfo[HomeURL]</td></tr>\n";
        }
  print ("</table>
        </font>
        </td></tr></table>
        </td>
        </tr>
        <tr>
        <th colspan=2 bgcolor=#D3D3D3 Class='Banner'><a href=userdetail_form.php>User Profile</a><br>
        <font size=-1>Public viewing of this profile is ");
  if($MemberInfo["ProfileEnabled"]==1) {print "enabled";}
  else {print "disabled";}
  print ("</font></th>
         </tr>
         <tr>
         <td colspan=2>
         $MemberInfo[Profile]
         </td>
         </tr>
	 <tr>
	 <td colspan=2><center><br><form action=printcheques.php method=post><input type=submit value='Print LETS Cheques'></form></center></td>
	 </tr>
         </table>
         ");

   /* Then we look up each account associated with the member, and print summary
  information */

   /* Get account information */

   $lookupaccounts = mysql_query("SELECT * FROM account,membertoaccountlink
                                         WHERE account.AccountID = membertoaccountlink.AccountID
                                          AND membertoaccountlink.MemberID = '$MemberID'");
   print mysql_error();

   while($account = mysql_fetch_array($lookupaccounts))
   {
         print ("<table noborder width=100% cellspacing=0>
                 <tr><th colspan=3 bgcolor=#D3D3D3>Account Details</th></tr>
                 <tr><th align=left>Account Name:</th>
                 <td>$account[AccountName]</td>
                 <td rowspan=7 align=right valign=top>
                 <form action=accounthistory.php? method=POST>
                 <input type=hidden name=AccountID value=$account[AccountID]>
                 <input type=submit value='View Trading History'>
                 </form>
                 </td></tr>
                 <tr><th align=left>Account Number:</th>
                 <td>$account[AccountID]</td></tr>");

         $lookuptype = mysql_query("SELECT * FROM accounttypeoptions
                                     WHERE AccountTypeID = '$account[AccountTypeID]'");
         $accounttype = mysql_result($lookuptype,0,"AccountTypeName");

         $creditlimit = CheckCreditLimit($account["AccountID"]);

         print ("<tr><th align=left>Account Type:</th>
                 <td>$accounttype</td></tr>
                 <tr><th align=left>Credit Limit: </th>
                 <td>$creditlimit</td></tr>\n");

         $lookupfactor = mysql_query("SELECT UpperCreditLimitFactor
                                       FROM administration");
         $factor = mysql_result($lookupfactor,0,"UpperCreditLimitFactor");
         $maxbalance = $creditlimit * $factor;

         print ("<tr><th align=left>Maximum Balance:</th>
                 <td>$maxbalance</td></tr>\n");

         $lookuptotals = mysql_query("SELECT (COUNT(DISTINCT(TransactionID))-1) AS Trades, SUM(ABS(Amount)) AS Volume
                                       FROM transactions
                                        WHERE AccountID = '$account[AccountID]'");
        print mysql_error();
        $total = mysql_fetch_array($lookuptotals);
        $lookuplastrecord = mysql_query("SELECT MAX(Reference) AS Reference
                                          FROM transactions
                                            WHERE AccountID = '$account[AccountID]'");
        $lastrecord = mysql_result($lookuplastrecord, '0', "Reference");
        $lookupbalance = mysql_query("SELECT CurrentBalance
                                      FROM transactions
                                        WHERE AccountID = '$account[AccountID]'
                                         AND Reference = '$lastrecord'");
        $balance = mysql_fetch_array($lookupbalance);


         print ("<tr><th align=left>Total Trades:</th>
                 <td>$total[Trades]</td></tr>
                 <tr><th align=left>Total Volume:</th>
                 <td>$total[Volume]</td></tr>
                <tr><th align=left>Current Balance:</th>
                <td>$balance[CurrentBalance]</td></tr>
                 </table>");
         }

   include "footer.php";


 ?>
