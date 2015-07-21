<?
  /*********************************************************************/
  /*
       Writen By:     Marti Settle
       Last Modified: January 11, 2002
       Called By:     myhome.php, systemadmin.php
       Calls:
       Description:   This is the bulletin routine.  Called by a member
                             without directove it will return priority bulletins
                      followed by an option to see all bulletins (which
                      calls this file with function=all).  Called by an
                      admin user this program will allow administration
                      of member bulletins.

       Modification History:
                    January 11, 2002 - file created
  */
  /*********************************************************************/

/* don't allow this page to cache (this forces renewal when a user logs out
of the admin system) */

if(!empty($AuthorizationCode))
{
        header("Cache-Control: max-age=10, no-cache");
        }

/* Setup the configuration variables */

include "configuration.php";

/* Connect to the database */

include "connectdb.php";

/* Check to see if the user is an authorized member.  If not, print a message
and shut down */

if(empty($MemberID))
{
        $title='Not Authorized';
        include 'header.php';
        print ("You are not authorized to view this page.  If you are a member of this system, please log in.  If you are not a member, please look under <strong>About $Systemname</strong> for information on joining.");
        include 'footer.php';
        exit();
        }

/* Add a prinform function */

function printform($BulletinID,$Priority,$BeginDate,$EndDate,$Title,$Text)
{
        if(empty($BeginDate)) {$BeginDate = date("Y-m-d");}
        if(empty($EndDate)) {$EndDate = date("Y-") . (date("m") + 1) . date("-d");}

        print ("<form action=bulletins.php method=post>
                <table noborder>
                <tr><th colspan=2 class=Banner>
                Bulletin Administration Form</th></tr>
                <tr><th class=FormLabel>Priority</th><td><select name='Priority'>\n");
        if($Priority == 'High')
        {
                print ("<option>High\n<option>Low\n");
                }
        else
        {
                print ("<option>Low\n<option>High\n");
                }
        print ("</select></td></tr>
                <tr><th class=FormLabel>Begin Date</th>
                <td><input type=text name=BeginDate value='$BeginDate' size=10></td></tr>
                <tr><th class=FormLabel>End Date</th>
                <td><input type=text name=EndDate value='$EndDate' size=10></td></tr>
                <tr><th class=FormLabel>Title</th>
                <td><input type=text name=Title value='$Title' size=50></td></tr>
                <tr><th class=FormLabel>Text</th>
                <td><textarea name=Text rows=6 cols=50>$Text</textarea></td></tr>
                <tr><th colspan=2 class=Banner>&nbsp;</th></tr>");

        if(!empty($BulletinID))
        {
                print ("<input type=hidden name=BulletinID value='$BulletinID'>
                        <input type=hidden name=Command value='Edit'>
                        <input type=hidden name=Complete value='1'>\n");
                }
        else
        {
                print ("<input type=hidden name=Command value='Add a Bulletin'>\n");
                }
        print ("<tr><td colspan=2 align=center><input type=Submit value=Submit></td></tr>
                <tr><th colspan=2 class=Banner>&nbsp;</th></tr>
                </table>");
        }

/* Check for an admin Authorization Code variable.  If present, run the admin
functions */

if(!empty($AuthorizationCode))
{

/* If a command has already been entered, run the process.  Otherwise, open
the admin menu page */

        if(!empty($Command))
        {
                switch($Command)
                {
                        case 'Add a Bulletin':
                                if(!isset($Title))
                                {
                                        $title = 'Add News Bulletin';
                                        include 'header.php';
                                        print ("Please complete all fields of the form below, and submit.<p>\n");
                                        printform('','','','','','');
                                        include 'footer.php';
                                        exit();
                                        }
                                if((empty($Title)) || (empty($BeginDate)) || (empty($EndDate)) || (empty($Priority)) || (empty($Text)))
                                {
                                        $title = 'Incomplete submission data';
                                        include 'header.php';
                                        print ("Your submission was incomplete.  Please ensure that all fields are complete, and re-submit.<p>\n");
                                        printform('',"$Priority","$BeginDate","$EndDate","$Title","$Text");
                                        include 'footer.php';
                                        exit();
                                        }
                                $Text = nl2br($Text);
                                if(!mysql_query("INSERT INTO bulletins
                                                  VALUES ('','$Priority','$BeginDate','$EndDate',\"$Title\",\"$Text\")"))
                                {
                                        $title = 'Database Error';
                                        include 'header.php';
                                        print ("<h2>Database Error</h2><p>
                                                The system was unable to process your submission.  The database returned the following error:<p>\n");
                                        print mysql_error();
                                        include 'footer.php';
                                        exit();
                                        }
                                # success! print the page (after the switch)
                                break;
                        case 'Edit':
                                if(empty($Complete))
                                {
                                        $ValuesLookup = mysql_query("SELECT *
                                                                      FROM bulletins
                                                                      WHERE BulletinID = '$BulletinID'");
                                        $Values = mysql_fetch_array($ValuesLookup);

                                        $Text = eregi_replace("<BR>","\n",$Values["Text"]);

                                        $title = "Edit Bulletin";
                                        include 'header.php';
                                        print ("Please change the values where appropriate.  When complete, click <strong>Submit</strong>.<p>");
                                        printform("$BulletinID","$Values[Priority]","$Values[BeginDate]","$Values[EndDate]","$Values[Title]","$Text");
                                        include 'footer.php';
                                        exit();
                                        }
                                $Text = nl2br($Text);
                                if(!mysql_query("UPDATE bulletins
                                                  SET Priority='$Priority',
                                                  BeginDate='$BeginDate',
                                                  EndDate='$EndDate',
                                                  Title='$Title',
                                                  Text='$Text'
                                                  WHERE BulletinID = '$BulletinID'"))
                                {
                                        $title = 'Database Error';
                                        include 'header.php';
                                        print ("<h2>Database Error</h2>
                                                The system was unable to process your request.  The database returned the following error:<p>\n");
                                                print mysql_error();
                                                include 'footer.php';
                                                exit();
                                        }
                                # Success.  Now return to the admin menu...
                                break;
                        case 'Delete':
                                if(!mysql_query("DELETE FROM bulletins
                                                  WHERE BulletinID = '$BulletinID'"))
                                {
                                        $title = 'Database Error';
                                        include 'header.php';
                                        print ("<h2>Database Error</h2>
                                                The system was unable to process your request.  The database returned the following error:<p>\n");
                                                print mysql_error();
                                        include 'footer.php';
                                        exit();
                                        }
                                break;
                        }
                }
        $title='News Bulletin Administration';
        include 'header.php';
        print ("<h2>News Bulletin Administration</h2>
                This page allows you to create, edit, or delete news bulletins, which are accessible to authorized user through their personal home page.<p>
                <center>
                <form action=bulletins.php method=post>
                <input type=submit name=Command value='Add a Bulletin'>
                </form>
                <table noborder>
                <tr><th colspan=2 class=Banner><font size=+1>High Priority Bulletins</font></th></tr>\n");
        $HighLookup = mysql_query("SELECT * FROM bulletins
                                    WHERE Priority = 'High'");
        while($High = mysql_fetch_array($HighLookup))
        {
                print ("<tr><th  colspan=2 class=LightBanner>$High[BeginDate] - expires $High[EndDate]<br>
                        $High[Title]
                        </th>
                        </tr>
                        <tr><td>$High[Text]<br><br></td>
                        <td>
                        <form action=bulletins.php method=post>
                        <input type=hidden name=BulletinID value='$High[BulletinID]'>
                        <input type=submit name=Command value=Edit>
                        <input type=submit name=Command value=Delete>
                        </form>
                        </td></tr>\n");
                }
        print ("<tr><th colspan=2 class=Banner><font size=+1>Low Priority Bulletins</font></th></tr>\n");
        $LowLookup = mysql_query("SELECT * FROM bulletins
                                    WHERE Priority = 'Low'");
        while($Low = mysql_fetch_array($LowLookup))
        {
                print ("<tr><th colspan =2 class=LightBanner>$Low[BeginDate] - expires $Low[EndDate]<br>
                        $Low[Title]
                        </th>
                        </tr>
                        <tr><td>$Low[Text]<br><br></td>
                        <td>
                        <form action=bulletins.php method=post>
                        <input type=hidden name=BulletinID value='$Low[BulletinID]'>
                        <input type=submit name=Command value=Edit>
                        <input type=submit name=Command value=Delete>
                        </form>
                        </td>
                        </tr>\n");
                }
        print ("</table>\n");
        include 'footer.php';
        exit();
        }

/* If we're still in the running, the user is not admin-authorized.
Check to see if the user has asked to see if there is a variable called
$ShortBulletin.  If so, print only high bulletins and a link to this
program (this routine is for printing bulletins as part of another page,
i.e. myhome.php) */

if(!empty($ShortBulletin))
{
        print ("<table noborder cellspacing=1>
                <tr><th class=BulletinBanner>System News Bulletins</th></tr>\n");
        $BulletinLookup = mysql_query("SELECT BulletinID,Priority,BeginDate,Title,LEFT(Text,100) AS Text FROM bulletins
                                        WHERE BeginDate <= NOW()
                                        AND EndDate >= NOW()
                                        ORDER BY Priority DESC,BeginDate DESC
                                        LIMIT 3");
        switch(mysql_num_rows($BulletinLookup))
        {
                case '0':
                        print ("<tr><td class=Bulletin><strong class=Bulletin>There are no system news bulletins at the current time</strong></td></tr>\n");
                        break;
                default:
                        while($Bulletin = mysql_fetch_array($BulletinLookup))
                        {

                                $Bulletin["Text"] = strtok($Bulletin["Text"],"<BR>");
                                if($Bulletin["Priority"] == 'High')
                                {
                                        print ("<tr><td class=BulletinHigh><strong class=BulletinHigh><em class=BulletinHigh>");
                                        }
                                else
                                {
                                        print ("<tr><td class=BulletinLow><strong class=BulletinLow><em class=BulletinLow>");
                                        }
                                print ("$Bulletin[BeginDate]
                                        </em>
                                        &nbsp;&nbsp;&nbsp;$Bulletin[Title]</strong><br>
                                        <font class=BulletinSmall>$Bulletin[Text]<a href=bulletins.php#$Bulletin[BulletinID]>...[See full text]</a>
                                        </font></td></tr>\n");
                                }
                }
        print ("</table>
                <center><a href=bulletins.php class=BulletinLink>View All News Bulletins</a></center>\n");

        }

/* If that didn't run, then print all the bulletins */
else
{
        $title = "News Bulletins";
        include "header.php";
        print ("<h2>$Systemname News Bulletins</h2><p>
                <table noborder width=100%>
                <tr>
                <th bgcolor=#D3D3D3><font size=+1>High Priority</font></th>
                </tr>\n");

        /* Look up and print the high priority bulletins first. */

        $HighLookup = mysql_query("SELECT * FROM bulletins
                                    WHERE Priority = 'High'
                                    AND BeginDate<=NOW()
                                    AND EndDate >= NOW()
                                    ORDER BY BeginDate DESC");
        switch(mysql_num_rows($HighLookup))
        {
                case '0':
                        print ("<tr><th align=left bgcolor=#F0F0F0>
                                There are no high priority bulletins at this time.
                                </th></tr>\n");
                        break;
                default:
                        while($High = mysql_fetch_array($HighLookup))
                        {
                                print ("<tr><th align=left bgcolor=#F0F0F0>
                                        <a name=$High[BulletinID]><em>$High[BeginDate]</em><br>
                                        $High[Title]
                                        </th></tr>
                                        <tr><td>$High[Text]</td></tr>\n");
                                }
                        }
        /* Now print a blank cell and the low priority bulletins */

        print ("<tr><td>&nbsp;</td></tr>
                <tr><th bgcolor=#D3D3D3 class=banner><font size=+1>Low Priority</font></th></tr>\n");

        $LowLookup = mysql_query("SELECT * FROM bulletins
                                   WHERE Priority = 'Low'
                                   AND BeginDate<=NOW()
                                   AND EndDate >= NOW()
                                   ORDER BY BeginDate DESC");
        switch(mysql_num_rows($LowLookup))
        {
                case '0':
                        print ("<tr><th align=left bgcolor=#F0F0F0>
                                There are no low priority bulletins at this time.
                                </th></tr>\n");
                        break;
                default:
                        while($Low = mysql_fetch_array($LowLookup))
                        {
                                print ("<tr><th align=left bgcolor=#F0F0F0>
                                        <a name=$Low[BulletinID]><em>$Low[BeginDate]</em><br>
                                        $Low[Title]
                                        </th></tr>
                                        <tr><td>$Low[Text]</td></tr>\n");
                                }
                        }


        /* end the table, close the page, and exit */

        print ("</table>\n");
        include 'footer.php';
        exit();
        }


?>