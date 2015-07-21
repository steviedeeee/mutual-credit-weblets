<?

include 'configuration.php';
include 'connectdb.php';
include 'adminlogin.php';

print ("<html><head><title>Non-e-mail List</title></head>\n<body>\n<table noborder>
<tr><th>Name</th><th>Home Number</th><th>Other Number</th></tr>\n");

$GetMembers = mysql_query("SELECT MemberFirstName,MemberLastName,HomeNumber,OtherNumber
                                FROM member
                                WHERE EmailAddress =''");

while($Members = mysql_fetch_array($GetMembers))
{
        print ("<tr><td>$Members[MemberFirstName] $Members[MemberLastName]</td>
                <td>$Members[HomeNumber]</td>
                <td>$Members[OtherNumber]</td></tr>\n");
        }

print ("</table></body></html>\n");

?>