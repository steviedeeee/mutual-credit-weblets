<?php
  /*********************************************************************/
  /*
       Writen By:     Marti settle
       Last Modified: January 8, 2003
       Called By:     Nothing
       Calls:         Nothing
       Description:   Prints a contact list of expired account members.

       Modification History:
                    January 8, 2003 - File Created
  */
  /*********************************************************************/

  include "configuration.php";
  include "connectdb.php";
  /* call the adminlogin file to ensure admin level access */
  include "adminlogin.php";

  if(empty($Date)) $Date = date("Y-m-d");

  if(empty($Print))
  {
      $title = "Expired Account Contacts";
      include "header.php";
  }
  else print ("<html><head><title>Expired Account Contact List</title></head><body>\n");

  print ("<h2>Accounts Expired as of $Date</h2>
          <table noborder cellpadding=3 cellspacing=1>
          <tr>
          <th align=left class=Banner>Acct.</th>
          <th align=left class=Banner>Name</th>
          <th align=left class=Banner>Phone</th>
          <th align=left class=Banner>Other</th>
          <th align=left class=Banner>E-mail</th>
          <th align=left class=Banner>Expiry</th>
          </tr>\n");

  $ContactLookup = mysql_query("SELECT account.AccountID AS AccountID, CONCAT(MemberFirstName,' ',MemberLastName) as Name, HomeNumber, OtherNumber, EmailAddress, DATE_FORMAT(AccountRenewalDate,'%b. %e, %Y') AS Date
                                FROM account, member, membertoaccountlink
                                WHERE account.AccountID = membertoaccountlink.AccountID
                                AND membertoaccountlink.MemberID = member.MemberID
                                AND AccountStatus != 'Closed'
                                AND AccountRenewalDate < '$Date'
                                ORDER BY AccountID");
  while($C = mysql_fetch_array($ContactLookup))
  {
    print ("<tr>
            <td>$C[AccountID]</td>
            <td>$C[Name]</td>
            <td>$C[HomeNumber]</td>
            <td>$C[OtherNumber]</td>
            <td>$C[EmailAddress]</td>
            <td>$C[Date]</td>
            </tr>\n");
  }
  print ("</table>");
  if(empty($Print))
  {
     print ("<p><hr><p>
             <center><form action=expiredaccounts.php method=POST>
             Show Accounts expired as of: <input type=text name=Date value='$Date' size=10> <input type=submit value='Refresh'></form><p><hr><p>
             <form action=expiredaccounts.php method=POST>
             <input type=hidden name=Date value='$Date'>
             <input type=hidden name=Print value=Yes>
             <input type=submit value='Make this page printable'>
             </form></center>");
      include "footer.php";
  }
  else print ("</body></html>");

  ?>