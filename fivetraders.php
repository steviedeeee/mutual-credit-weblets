<?
/******************************************************************************/
/*
            File Name:        fivetraders.php
            Created By:       Martin Settle
            Last Modified:    December 17, 2001
            Called By:        header.php
            Calls:            nothing
            Description:      This file prints a list of five member first
            		      names and their profile (if made public).  It
                              uses a random number generator to choose which
                              traders to show.

            Change Log:
                 Dec. 17, 2001 - File Created
*/
/******************************************************************************/

include "configuration.php";
include "connectdb.php";
$title = "Trader Profiles";
include "header.php";
print ("<h2>Traders on $Systemname</h2>
        $Systemname is a diverse group of individuals committed to bettering their lives and their community through the trade of goods and services.  For the privacy of members, no contact information is available to the general public.  However, in order that you as a guest may gain some sense of the kind of people who are involved in $Systemname, some members have permitted their first names and profiles to be shared.<p>");

$today = date("Y-m-d");

$TradersLookup = mysql_query("SELECT DISTINCT member.MemberID,MemberFirstName,Profile
				FROM member,membertoaccountlink,account
                                WHERE member.MemberID = membertoaccountlink.MemberID
                                AND membertoaccountlink.AccountID = account.AccountID
                                AND ProfileEnabled = '1'
                                AND AccountRenewalDate > '$today'
                                AND AccountStatus != 'Suspended'");

if(mysql_num_rows($TradersLookup) < 5)
{
        $Number = mysql_num_rows($TradersLookup);
        switch($Number)
        {
        	case '0':
                	print "<strong>Sorry.  At present no members have enabled public viewing of their profiles</strong>\n";
                        include "footer.php";
                        exit();
                case '1':
                     	print ("Here is one member of $Systemname:<p>
                        	<table>
                                <tr><th class=Banner>" . mysql_result($TradersLookup,0,'MemberFirstName') . "</th></tr>
                                <tr><td>" . mysql_result($TradersLookup,0,'Profile') . "</td></tr>\n");
                        break;
                default:
        		print ("Here are $Number of the members of $Systemname:
	        		<table>\n");
			for($row=0;$row<$Number;$row++)
			{
	        		print ("<tr><th class=Banner>" . mysql_result($TradersLookup,$row,'MemberFirstName') . "</th></tr>
	               			<tr><td>" . mysql_result($TradersLookup,$row,'Profile') . "<td></tr>\n");
	       			 }
                        break;
                }
        }
else
{
        $Number = '5';
        $TotalRows = mysql_num_rows($TradersLookup);
	print ("Here are $Number of the members of $Systemname:
	        <table>\n");
	srand(time());
	$used[] = '';
	for($i=0;$i<$Number;$i++)
	{
		$success = '0';
		do
		{
			$noprint = 0;
			$row = rand(1,$TotalRows) - 1;
			reset ($used);
			while(list($count,$num) = each($used))
			{
				if($row == $num) $noprint = 1;
				}
			if($noprint == 0)
			{
				print ("<tr><th class=Banner>" . mysql_result($TradersLookup,$row,'MemberFirstName') . "</th></tr>
					<tr><td>" . mysql_result($TradersLookup,$row,'Profile') . "<td></tr>\n");
				$used[] = $row;
				$success = 1;
				}
			}
		while($success == 0);	
	        }
        }

print("<tr><th class=Banner>&nbsp;</th></tr>
       </table>\n");
include "footer.php";

?>