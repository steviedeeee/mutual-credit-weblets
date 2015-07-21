<?

/******************************************************************************/
/*                                                                            */
/*            File Name:        infopages.php                                 */
/*            Created By:       Martin Settle                                 */
/*            Creation Date:    December 17, 2001                             */
/*            Called By:        header.php, self                              */
/*            Calls:            nothing                                       */
/*            Description:      This is the administration system for         */
/*                              the information pages accessible by the main  */
/*                              menu in guest and member mode.                */
/*                                                                            */
/*            Change Log:                                                     */
/*                 Dec. 17, 2001 - File Created                               */
/*                                                                            */
/******************************************************************************/

/* the PrintChildren function is a recursive function that will assemble the
menu structure of the infopages held in the system. */

function PrintChildren($PageID)
{
         $ChildrenLookup = mysql_query("SELECT PageID, Title FROM infopages WHERE Parent='$PageID' ORDER BY Priority");
         if(mysql_num_rows($ChildrenLookup) > 0)
         {
                 print ("<ul compact>\n");
                 while($Children = mysql_fetch_array($ChildrenLookup))
                 {
                       print ("<li><a href='infopages.php?PageID=$Children[PageID]'>$Children[Title]</a>\n");
                       PrintChildren($Children["PageID"]);
                       }
                 print ("</ul>\n");
                 }
         }


include "configuration.php";
include "connectdb.php";

/* Check that the user is an authorized admin user, and is logged in as system
admin */

include "adminlogin.php";

if($AdminType == 'data')
{
        $title = "Require System Admin authorization";
        include "header.php";
        print ("Sorry.<p>In order to access the information pages subroutines, you must be authorized as a System Administrator.  Please log out of the Data Entry system, and log in as a System Administrator.");
        include "footer.php";
        exit();
        }

/* Now start the page, with an introduction, or a message from the data update
routine, if available */

$title = "Information Page Administration";
include "header.php";

if(!empty($SystemMessage))
{
        print ("$SystemMessage\n<p>\n<hr>\n<p>\n");
        }
else
{
        print ("<h2>Welcome to the Information Page administration system.</h2>
                This page allows you to add web pages that can be accessible through the \"About $Systemname\" menu item on the guest and member sites.  To add a new page, complete the form below.  To edit a page currently in use by the system, select the title of the page from the map below.<p>
                <hr>
                <p>\n");
        }

/* Now print the form for the user to add or edit a page.  First, check for a pageID
and look up the necessary information */

if(!empty($PageID))
{
        $Pagelookup = mysql_query("SELECT * FROM infopages
                                    WHERE PageID = '$PageID'");
        $Page = mysql_fetch_array($Pagelookup);
        }
else
{
        $Page["Parent"] = '';
        $Page["Title"] = '';
        $Page["Menu"] = '';
        $Page["MenuPlacement"] = '';
        $Page["Priority"] = '';
        $Page["Data"] = 'Place your page information here';
        $Page["MainPage"] = 'No';
        }

print ("<form action=submitpage.php method=POST>
        <table width=100%>
        <tr>
        <th colspan=2 bgcolor=#D3D3D3 class='Banner'>
        Information Page Entry
        </th></tr>
        <tr><th align=left>Title:</th><td><input type=text name=Title value='$Page[Title]' size=40></td></tr>
        <tr><th align=left>Parent Page:</th><td><select name=Parent>\n<option>");
if(!empty($Page["Parent"]))
{
        $LookupParentTitle = mysql_query("SELECT Title FROM infopages
                                          WHERE PageID = '$Page[Parent]'");
        $ParentTitle = mysql_result($LookupParentTitle,0,'Title');
        print ("$Page[Parent]: $ParentTitle");
        }
$MenuPagesLookup = mysql_query("SELECT PageID,Title
                                 FROM infopages
                                  WHERE Menu = 'Yes'");
while($MenuPages = mysql_fetch_array($MenuPagesLookup))
{
        print ("<option>$MenuPages[PageID]: $MenuPages[Title]");
        }
print ("</select></td></tr>
        <tr><th align=left>Menu Page:</th><td><select name=Menu>");
switch($Page["Menu"])
{
       case 'Yes':
             print ("<option>Yes<option>No");
             break;
       default:
             print ("<option>No<option>Yes");
       }
print ("</select></td></tr>
        <tr><th align=left>Menu Placement</th><td><select name=MenuPlacement>");
switch($Page["MenuPlacement"])
{
       case 'Top':
             print ("<option>Bottom<option>Top");
             break;
       default:
             print ("<option>Top<option>Bottom");
             }
print ("</select></td></tr>
        <tr><th align=left valign=top>Page Data:<br><input type=radio name=html value=yes");
if(!empty($PageID))
{
        print " checked";
        }
        print(">HTML code<br><input type=radio name=html value=no");
if(empty($PageID))
{
        print " checked";
        }
print (">Raw Text</th>
        <td><textarea cols=45 rows=10 name=Data>$Page[Data]</textarea></tr>
        <tr><th align=left>Main Page:</th><td><select name=MainPage>");
switch($Page["MainPage"])
{
       case 'Yes':
             print "<option>Yes<option>No";
             break;
       default:
             print "<option>No<option>Yes";
             }
print ("</select></td></tr>\n");

/* If the page being edited is a MenuPage, lookup and print the priority of the submenu */

if($Page["Menu"] == 'Yes')
{
        print ("</table><table width=100%>
        	<tr><td colspan=2>The following table lists the menu items on this page, in the priority that they currently appear.  To change the order, number all pages in the order you wish them to appear, beginning with number one.</td></tr>
                <tr><th colspan=2 bgcolor=#D3D3D3 Class='Banner'>Menu Items on this Page</th><tr>");
        $MenuLookup = mysql_query("SELECT PageID, Title, Priority
                                    FROM infopages
                                     WHERE Parent = '$PageID'
                                      ORDER BY Priority");
        while($Menu = mysql_fetch_array($MenuLookup))
        {
              print ("<tr><th align=left>$Menu[Title]</th><td><input type=text name=MenuPriority[$Menu[PageID]] size=3 value='$Menu[Priority]'></td></tr>\n");
              }
        }

/* Now give the Form submit button, and print the whole infopage structure */

print ("</table><p>");
if(!empty($PageID))
{
        print ("<input type=hidden name=PageID value='$PageID'>\n");
        }
print ("<center><input type=submit value='Submit'></center></form><p>
        <table>
        <tr><th bgcolor=#D3D3D3 Class='Banner'>Current Information Pages</th></tr>
        <tr><td>The following List shows all pages currently contained in the system.  To edit any page or sub-menu structure, click on the page title.<p>
        ");
$MainPageLookup = mysql_query("SELECT * FROM infopages WHERE MainPage = 'Yes'");
$MainPage = mysql_fetch_array($MainPageLookup);
print ("<strong><a href='infopages.php?PageID=$MainPage[PageID]'>$MainPage[Title]\n");

PrintChildren($MainPage["PageID"]);

print ("</td></tr>
        <tr><th bgcolor=#D3D3D3>&nbsp;</th></tr></table>
        <p>
        <form action=infopages.php action=POST>
        <center><input type=submit value='Add a New Page'></center></form>");
include "footer.php";
?>