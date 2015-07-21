<? 

/**********************************************************************


		File Name: 	printcheques.php
		Written by:	Marti Settle
		Date:		February 24, 2003
		Calls:		nothing unusuala

		Modification History
			February 24 - File completed

**********************************************************************/
		

# this is a routine to have unique cheques issued for each account

include "configuration.php";
include "connectdb.php";

# check user is logged in

if(empty($MemberID))
{
        $title = "Authorization Required";
        include "header.php";
        print ("<h2>Authorization Required</h2>
You are not currently logged in as a member of $Systemname.  Please use the <a href=login.php>Login Page</a> to access this page.\n");
        include "footer.php";
        exit();
        }

# print the requested cheques

if(!empty($AccountID) && !empty($Number))
{
# Start the page


        print ("<html>
		<head>
		<link rel=StyleSheet HREF=cheques.css type=text/css title='Cheque Print Style'>
		<title>Cheques for Account $AccountID</title></head>
                <body>\n");

# get the member and Account Details
	if(!empty($AuthorizationCode))
	{
		$AccountLookup =mysql_query("SELECT AccountName,MemberFirstName,MemberLastName,HomeNumber, AccountRenewalDate
		                             FROM account,membertoaccountlink,member
					     WHERE account.AccountID = $AccountID
					     AND account.AccountID = membertoaccountlink.AccountID
					     AND membertoaccountlink.MemberID = member.MemberID
					     AND PrimaryContact = 1");
		}
	else
	{
		$AccountLookup = mysql_query("SELECT AccountName,MemberFirstName,MemberLastName,HomeNumber, AccountRenewalDate
                                        FROM account,membertoaccountlink,member
                                        WHERE account.AccountID = $AccountID
                                        AND account.AccountID = membertoaccountlink.AccountID
                                        AND membertoaccountlink.MemberID = member.MemberID
                                        AND member.MemberID = $MemberID");
		}
	print mysql_error();
        $Account = mysql_fetch_array($AccountLookup);

# create the cheque numbers

        $PreviousLookup = mysql_query("SELECT COUNT(ChequeID) AS P
                                        FROM cheques
                                        WHERE AccountID = $AccountID");
        $Previous = mysql_result($PreviousLookup,0,'P');

        $today = date("Y-m-d");

        for($count=0; $count<$Number; $count++)
        {
                mysql_query("INSERT INTO cheques
                                SET AccountID = $AccountID,
                                ExpiryDate = '$Account[AccountRenewalDate]',
                                IssueDate = '$today'");
                print mysql_error();
                }

# now get the cheque numbers

	$printnumber = 0;

        $IDLookup = mysql_query("SELECT ChequeID FROM cheques
                                        WHERE AccountID = $AccountID
                                        ORDER BY IssueDate
                                        LIMIT $Previous,$Number");
        while($ID = mysql_fetch_array($IDLookup))
        {

# Print each cheque

                if(empty($Design)) $Design = 'default.gif';

                print ("<DIV id=cheque>
			<DIV class=account>Acct. $Account[AccountName]<br>$Account[MemberFirstName] $Account[MemberLastName] ($Account[HomeNumber])</DIV>
                        <DIV class=date><br>,");
                print date("Y");
                print ("</DIV>
                        <DIV class=payto>Pay to the Order of:</DIV>
                        <DIV class=numberamount><br>E$</DIV>
                        <DIV class=writtenamount>&nbsp;</DIV>
			<DIV class=currency><br>/100 Ecodollars</DIV>
                        <DIV class=system>$Systemname</DIV>
                        <DIV class=url>$baseURL</DIV>
                        <DIV class=memo>Memo:</DIV>
			<DIV class=signature>&nbsp;</DIV>
                        <DIV class=expiry>Valid Until: $Account[AccountRenewalDate]</DIV>
			<DIV class=number>Cheque #$ID[ChequeID]</DIV>
             		</DIV>");
                switch($printnumber)
		{
			case 2:
				print ("<p style='page-break-after: always;'>\n");
				$printnumber = 0;
				break;
			default:
				print ("<p>\n");
				$printnumber++;
				break;
			}
		}
        print ("</body></html>");
        exit();
        }

$title = 'Print Cheques';
include "header.php";

print ("<h2>Print Cheques</h2>
Complete and submit the form below to create LETS cheques.  You will need to have a printer attached to your computer to produce the cheques.<p>
<form action=printcheques.php method=POST target=_new>
<table noborder>
<tr><th align=left>Account:</th>
<td>\n");

if(!empty($AuthorizationCode))
{
        $AcctLookup = mysql_query("SELECT AccountID, AccountName FROM account WHERE AccountStatus != 'Closed' AND AccountRenewalDate > CURDATE() ORDER BY AccountID");
        }
else
{
        $AcctLookup = mysql_query("SELECT account.AccountID AS AccountID, AccountName FROM account, membertoaccountlink WHERE account.AccountID = membertoaccountlink.AccountID AND membertoaccountlink.MemberID = '$MemberID' ORDER BY AccountID");
        }

if(mysql_num_rows($AcctLookup)  == 0)
{
        $Account = mysql_fetch_array($AcctLookup);
        print ("<input type=hidden name=AccountID value=$Account[AccountID]>$Account[AccountID]: $Account[AccountName]");
        }
else
{
        print ("<select name=AccountID><option>");

        while($Account = mysql_fetch_array($AcctLookup))
        {
                print ("<option value=$Account[AccountID]>$Account[AccountID]: $Account[AccountName]\n");
                }
        print ("</select>\n");
        }

print ("</td></tr>
<tr><th align=left>Number:</th>
<td><select name=Number><option>3<option>4<option>5<option>6<option>7<option>8<option>9<option>10</select></td></tr>
<tr><th align=left>Background:</th>
<td>Available Soon!</td></tr>
<tr><td colspan=2 align=center><input type=submit value='Print Cheques'></td></tr>
</table></form>");

include "footer.php";

?>

