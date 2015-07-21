<?
  function showMemberSummary($LookupMemberID)
  {
      include "connectdb.php";

      $ValidAccounts = mysql_query("SELECT account.AccountID
					FROM member, membertoaccountlink, account
					WHERE member.MemberID = membertoaccountlink.MemberID
					AND membertoaccountlink.AccountID = account.AccountID
					AND AccountStatus != 'Closed'
					AND member.MemberID = '$LookupMemberID'");
      switch(mysql_num_rows($ValidAccounts))
      {
	case 0:
		$memberInfo = mysql_query("SELECT LoginID,MemberFirstName, MemberMiddleName, MemberLastName
						FROM member
						WHERE MemberID = '$LookupMemberID'");
		$currentRow = mysql_fetch_array($memberInfo);
		print("<table><tr><th colspan=2 bgcolor=#D3D3D3>Member Summary For ");
		if((!empty($GLOBALS["AuthorizationCode"])) && ($GLOBALS["AdminType"] == 'system')) print "Member $LookupMemberID : ";
		print " $currentRow[LoginID] : $currentRow[MemberFirstName] $currentRow[MemberMiddleName] $currentRow[MemberLastName]</th></tr>\n";		
		break;

        default:
	        $memberInfo = mysql_query("SELECT  LoginID, MemberFirstName, MemberMiddleName, MemberLastName,
                                         ProfileEnabled, Profile, HomeURL, EmailAddress, HomeNumber
                             FROM member
                             WHERE MemberID = '$LookupMemberID'");

		$currentRow = mysql_fetch_array($memberInfo);

		print("<table><tr><th colspan=2 bgcolor=#D3D3D3>Member Summary For ");
		if(!empty($GLOBALS["AuthorizationCode"]))
		{
		     if($GLOBALS["AdminType"] == 'system')
		     {
			  print "Member $LookupMemberID : ";
			  }
		     }
		print " $currentRow[LoginID]";
		if (!empty($GLOBALS["MemberID"]))
		{
		    printf(" : %s %s %s", $currentRow["MemberFirstName"], $currentRow["MemberMiddleName"], $currentRow["MemberLastName"]);
		}
		print("</th></tr>");

		if (!empty($GLOBALS["MemberID"]))
		{
		   print("<tr><th colspan=2 bgcolor=#D3D3D3>Contact Information</th></tr>");

		   print("<tr>");
		      print("<th>Email Address:</th>");
		      printf("<td><a href=\"mailto:%s\">%s</a></td>", $currentRow["EmailAddress"], $currentRow["EmailAddress"]);
		   print("</tr>");
		   print("<tr>");
		      print("<th>Home Number:</th>");
		      printf("<td>%s</td>", $currentRow["HomeNumber"]);
		   print("</tr>");
		   print("<tr>");
		      print("<th>Hompage:</th>");
		      printf("<td><a href='http://%s'>%s</a></td>", $currentRow["HomeURL"], $currentRow["HomeURL"]);
		   print("</tr>");
		   print("<tr>");
		      print("<th>Public Profile is:</th>");
		      if ($currentRow["ProfileEnabled"])
		      {
			  print("<td>Enabled</td>");
		      }
		      else
		      {
			  print("<td>Disabled</td>");
		      }
		   print("</tr>");
		   if($currentRow["ProfileEnabled"])
		   {
		    print("<tr>");
				  print("<th valign=top>Profile:</th>");
				  printf("<td><pre>%s</pre></td>", $currentRow["Profile"]);
		    print("</tr>");
		   }
           }
	   }



         $memberAccounts = mysql_query("SELECT AccountID
                                        FROM membertoaccountlink
                                        WHERE MemberID = '$LookupMemberID'");

         while($accountResult = mysql_fetch_array($memberAccounts))
         {
           $currentAccountID = $accountResult["AccountID"];

           $accountNameQuery =mysql_query("SELECT AccountName, AccountStatus
                                           FROM account
                                           WHERE AccountID = '$currentAccountID'");
           $accountNameResult = mysql_fetch_array($accountNameQuery);

           $tradeInfo = mysql_query("SELECT MAX(Reference), MAX(TransactionID),
                                            SUM(ABS(Amount)) As TotalTradeVolume,
                                            (COUNT(DISTINCT(TransactionID))-1) AS TotalTrades
                                            FROM transactions
                                            WHERE AccountID = '$currentAccountID'
                                            GROUP BY AccountID");
           $currentTradeRow = mysql_fetch_array($tradeInfo);

           $GetCurrentBalance = mysql_query("SELECT CurrentBalance
                                             FROM transactions
                                             WHERE AccountID = '$currentAccountID'
                                               AND Reference = '$currentTradeRow[0]'
                                               AND TransactionID = '$currentTradeRow[1]'");
           $currentBalanceRow = mysql_fetch_array($GetCurrentBalance);

           print("<tr><th colspan=2 bgcolor=#D3D3D3>Trade Information for Account: $currentAccountID - $accountNameResult[AccountName]</th></tr>");
           print("<tr>");
	   
	   if($accountNameResult["AccountStatus"] == 'Closed')
	   {
		print("<th colspan=2>Account Closed</th></tr>\n");
		}
	   else
	   {
	   
                 print("<th>Current Balance:</th>");
                 printf("<td>%s</td>", $currentBalanceRow["CurrentBalance"]);
		 print("</tr>");
		 print("<tr>");
                 print("<th>Total Trades:</th>");
                 printf("<td>%s</td>", $currentTradeRow["TotalTrades"]);
		 print("</tr>");
		 print("<tr>");
                 print("<th>Total Trade Volume:</th>");
                 printf("<td>%s</td>", $currentTradeRow["TotalTradeVolume"]);
	  	 print("</tr>");
		 }
	   if(!empty($GLOBALS["AuthorizationCode"]))
           {
                if($GLOBALS["AdminType"]=='system')
                {
                        print ("<tr><td colspan=2 align=right><form action=accounthistory.php method=POST><input type=hidden name=AccountID value=$currentAccountID><input type=submit value='Show Account History'></form></td></tr>");
                        }
                }
         }

      }
      print("</table>");

      mysql_free_result($memberInfo);

  }
?>
