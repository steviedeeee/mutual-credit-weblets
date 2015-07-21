<?
  /*********************************************************************/
  /*
       Written By:    Marti Settle
       Last Modified: August 26, 2001
       Called By:     adedit.php, adListing.php
       Calls:         connectdb.php
       Description:   This is a form routine that handles either creating
                          a new Ad or editing the current ad.

       Modification History:
                    August 28, 2001 - file created
  */
  /*********************************************************************/

include "configuration.php";
include "connectdb.php";

if(empty($Submit))
{
        $Submit = "";
        }

/* Check the type of submission to determine whether to query the database
or provide a form */

if($Submit == 'Confirm')
{

/* Now fix the six separate date fields from the form to allow appropriate entry
into the database, and check to make sure the expiry date falls within one year of
the current date.  We also convert any newlines in the description into html breaks */

        if(empty($AdBeginDateYear))
        {
                $AdBeginDateYear = date("Y");
                }
        if(empty($AdBeginDateMonth))
        {
                $AdBeginDateMonth = date("m");
                }
        if(empty($AdBeginDateDay))
        {
                $AdBeginDateDay = date("d");
                }
        $AdBeginDate = $AdBeginDateYear . "-" . $AdBeginDateMonth . "-" . $AdBeginDateDay;

        $AdEndDateStamp  = date("Ymd",mktime (0,0,0,date("m"),  date("d"),  date("Y")+1));
        if((empty($AdEndDateYear)) or  ($AdEndDateYear<date("Y")))
        {
                $AdEndDateYear = date("Y")+1;
                }
        if(empty($AdEndDateMonth))
        {
                $AdEndDateMonth = date("m");
                }
        if(empty($AdEndDateDay))
        {
                $AdEndDateDay = date("d");
                }
        if(strlen($AdEndDateMonth)<2){$AdEndDateMonth = "0$AdEndDateMonth";}
        if(strlen($AdEndDateDay)<2){$AdEndDateDay = "0$AdEndDateDay";}
        $AdEndDate = "$AdEndDateYear-$AdEndDateMonth-$AdEndDateDay";
        $AdEndDateshort = $AdEndDateYear . $AdEndDateMonth . $AdEndDateDay;
/*        if($AdEndDateshort <= date("Ymd"))
        {
                      $AdEndDate = $AdEndDateYear + 1 . "-$AdEndDateMonth-$AdEndDateDay";
                }
*/
        if($AdEndDateshort>$AdEndDateStamp)
        {
        $AdEndDate  = date("Y-m-d", mktime (0,0,0,date("m"),  date("d"),  date("Y")+1));
                }
        $AdDescription = nl2br($AdDescription);

/* Check the presence of an AdID number to determine whether this is a new ad or an
update to an existing ad, then run the appropriate query */

        if(empty($AdID))
        {

/* Check that all required fields are present.  If they are not, send the user
back to the form to correct their omission. */

                if(empty($AccountID) or empty($CategoryID) or empty($TradeType) or empty($AdName))
                {
                        $title = "Incomplete Ad Submission";
                        include "header.php";
                        print("<center><h2>Your submission to the Ad Directory was not complete.<p>Click on \"Try Again\" to return to the form and complete all necessary fields.<p></h2>\n");

                        print("<form action=\"AdInputForm.php\" method=POST>\n");
                        while(list($key, $value) = each($HTTP_POST_VARIABLES))
                        {
                                if($key != "Submit")
                                {
                                        print("<input type=hidden name=\"$key\" value=\"$value\">\n");
                                        }
                                }
                        print("<input type=submit name=\"Submit\" value=\"Try Again\">");
                        print("</center>");
                        include "footer.php";
                        exit();
                        }

/* Now run the Insert query and provide a results page dependant upon success */

                if(!mysql_query("INSERT INTO advertisements
                                          VALUES('','$AccountID','$CategoryID','$CategoryID2','$CategoryID3','$TradeType','$AdBeginDate','$AdEndDate','$AdName','$AdDescription')"))
                {
                        $title = "Database Error";
                        include "header.php";
                        print("<center><h2>Sorry, the system is unable to register your ad at this time.<p>Please try again later</h2></center>");
                        $error = mysql_error();
                        print $error;
                        include "footer.php";
                        exit();
                        }
                $title = "Your Ad has been added!";
                include "header.php";
                print("Your LETS ad has been successfully added to the database.<p>Please use the menu on the left to continue, or go to the <a href=\"adlisting.php\">Ad Directory</a>.\n");
                include "footer.php";
                exit();

                }

/* If the AdID exists, run the update */

        else
        {
                if(!mysql_query("UPDATE advertisements
                                  SET CategoryID = '$CategoryID',
                                   CategoryID2 = '$CategoryID2',
                                   CategoryID3 = '$CategoryID3',
                                   TradeType = '$TradeType',
                                   AdBeginDate = '$AdBeginDate',
                                   AdExpiryDate = '$AdEndDate',
                                   AdName = '$AdName',
                                   AdDescription = '$AdDescription'
                                    WHERE AdID = '$AdID'"))
                {
                        $title = "Database Error";
                        include "header.php";
                        print("<center><h2>Sorry, the system is unable to register your changes at this time.<p>Please try again later</h2></center>");
                        $error = mysql_error();
                        print "\nError: " . $error;
                        include "footer.php";
                        exit();
                        }
                else
                {
                        $title = "Ad Updated!";
                        include "header.php";
                        print("Your LETS ad has been successfully updated.<p>Please use the menu on the left to continue, or go to the <a href=\"adlisting.php\">Ad Directory</a>.\n");
                        include "footer.php";
                        exit();
                        }
                }
        }

/* If the submission type was not a confirmation, print the form, checking for
existing variables if this is an edit or confirmation form */

else
{

/* but first, make sure the user is authorized to view this form */

        if(empty($MemberID))
        {
                $title = "Unauthorized user";
                include "header.php";
                print("You are not authorized to view this page.  If you are a LETS member, please <a href=\"Login.php\">login</a> and enter this page again.\n");
                include "footer.php";
                exit();
                }

        $title = "Input or Edit Ads";
        include "header.php";

/* Since this is the page that will be called by people wishing to view or edit
their ads, we need to provide a routine to look up their ads.  Note that this is
different from the routine called by the AdListing program in that it does not check
the ad's date, as members may wish to renew an expired ad. Show ads only if the
form has no data in it...*/

/* This first routine looks up the number of ads allowed for each of the member's
accounts */

        if(empty($HTTP_POST_VARS))
        {
                $adsfreequery = mysql_query("SELECT account.AccountID, accounttypeoptions.AccountTypeNumFreeAds, accounttypeoptions.AccountTypeExtraAdCost
                                              FROM membertoaccountlink, account, accounttypeoptions
                                               WHERE membertoaccountlink.AccountID = account.AccountID
                                                 AND account.AccountTypeID = accounttypeoptions.AccountTypeID
                                                  AND membertoaccountlink.MemberID = $MemberID
                                                   ORDER BY account.AccountID");
                $numberofaccounts = mysql_num_rows($adsfreequery);

                while($accountadsfree = mysql_fetch_array($adsfreequery))
                {
                        print "<h2>Account $accountadsfree[AccountID]</h2>\n";

                        $numadquery = mysql_query("SELECT DISTINCT advertisements.AdID
                                FROM advertisements,membertoaccountlink
                                WHERE advertisements.AdBeginDate < CURDATE()
                                   And advertisements.AdExpiryDate > CURDATE()
                                    And advertisements.AccountID = '$accountadsfree[AccountID]'");
                        $numberofads = mysql_num_rows($numadquery);

                        if($numberofads < $accountadsfree["AccountTypeNumFreeAds"])
                        {
                                print "You have $numberofads ads currently active on the LETS System.  This account is permitted $accountadsfree[AccountTypeNumFreeAds] free ads.  To activate more ads change the expiry date on old ads below or submit new ads through the form at the bottom of the page.<p><hr><p>\n";
                                }
                          if($numberofads >= $accountadsfree["AccountTypeNumFreeAds"])
                        {
                                print "You have $numberofads ads currently active on the LETS System.  Activating any further ads will result in a monthly surcharge of $accountadsfree[AccountTypeExtraAdCost] eco currency.\n";
                                print "<p>To change an ad to an inactive state, edit the Expiry date to be earlier than today's date.  To register additional ads in the system, complete the form at the bottom.  Service charges will be levied automatically.<p><hr><p>";
                                }

/* Now list the ads for each account.  This will not limit by date, so expired ads
(or ads not yet running) will be visible. */

                        $adslist = mysql_query("SELECT *
                                                 FROM advertisements
                                                  WHERE advertisements.AccountID = $accountadsfree[AccountID]
                                                    ORDER BY advertisements.AdExpiryDate DESC, advertisements.AdName");

                        print "\n<table noborder width=100%>\n";

                        while($ad = mysql_fetch_array($adslist))
                        {

/* This is where we will simply print the ads, with links to edit or delete... */

                                print ("<tr><td>
                                        <table width=100% cellspacing=1 cellpadding=0 noborder><tr bgcolor=#D0D0D0>
                                        <th colspan=2><font size=+1>$ad[AdName]</font></th></tr>
                                        <tr><th align=left>Ad Begins:</th><td>$ad[AdBeginDate]</td></tr>
                                        <tr><th align=left>Ad Expires:</th><td>$ad[AdExpiryDate]</td></tr>\n");

/*The category routine always seems a little confusing.... */

                                $category = mysql_query("SELECT HeadingName, CategoryName
                                                          FROM adcategories, adheadings
                                                           WHERE adcategories.HeadingID = adheadings.HeadingID
                                                           AND CategoryID = $ad[CategoryID]");
                                $result = mysql_fetch_array($category);

                                print ("<tr><th align=left valign=top>Categories:&nbsp;</th>
                                        <td>$result[HeadingName]: $result[CategoryName]");

                                if($ad["CategoryID2"] != "0")
                                {
                                               $category = mysql_query("SELECT HeadingName, CategoryName
                                                                  FROM adcategories, adheadings
                                                                   WHERE adcategories.HeadingID = adheadings.HeadingID
                                                                   AND CategoryID = $ad[CategoryID2]");
                                               $result = mysql_fetch_array($category);

                                        print "<br>$result[HeadingName]: $result[CategoryName]";
                                        }
                                if($ad["CategoryID3"] != "0")
                                {
                                        $category = mysql_query("SELECT HeadingName, CategoryName
                                                                  FROM adcategories, adheadings
                                                                   WHERE adcategories.HeadingID = adheadings.HeadingID
                                                                   AND CategoryID = $ad[CategoryID3]");
                                               $result = mysql_fetch_array($category);

                                        print "<br>$result[HeadingName]: $result[CategoryName]";
                                        }
                                print "\n</b></td></tr><tr><th align=left>Ad Type: </th><td>";
                                      switch($ad["TradeType"])
                                      {
                                            case 'R':
                                                print "Requested";
                                                break;
                                        case 'O':
                                                print "Offered";
                                        }
                                print "</td></tr>\n";
                                print ("<tr><th valign=top align=left>Description:</th>
                                        <td>$ad[AdDescription]</td></tr></table>
                                        <p><hr width=60%><p></td><td valign=top>\n");

/* Show the active status */

                                if(($ad["AdExpiryDate"] > date("Y-m-d")) && ($ad["AdBeginDate"] < date("Y-m-d")))
                                {
                                        print "<i>Active</i><br>\n";
                                        }
                                else
                                {
                                        print "<i>Inactive</i><br>\n";
                                        }

/* and print the edit and delete buttons */

                                print "<form action='adinputform.php' Method=POST>\n";
                                    while(list($key,$value) = each($ad))
                                {
                                        $value = ereg_replace("<br />", "", $value);
                                        print "<input type=hidden name=\"$key\" value=\"$value\">\n";
                                        }
                                print "<input type=submit value='Edit'></form>\n";
                                print "<form action=\"addelete.php\" method=POST>\n<input type=hidden name=\"AdID\" value=\"" . $ad["AdID"] . "\">\n<input type=submit value='Delete'>\n</form>\n</td></tr>";
                                     }
                        print "</table>\n<p>";
                        }
               }

/* If this is an edit of an old ad, we need to convert the date strings into the
separate fields used in the form.  We'll just do that if they exist.  Since no
HTML breaks would have been inputted unless we are editting, we will also use this
opportunity to convert any breaks back into newlines for our ad description field */

        if(!empty($AdBeginDate))
        {
                $AdBeginDateYear = strtok($AdBeginDate,"-");
                $AdBeginDateMonth = strtok("-");
                $AdBeginDateDay = strtok("-");

                $AdEndDateYear = strtok($AdExpiryDate,"-");
                $AdEndDateMonth = strtok("-");
                $AdEndDateDay = strtok("-");

                $AdDescription = ereg_replace("<br>", "", $AdDescription);
                $AdDescription = ereg_replace("<br />", "", $AdDescription);

                }

/* If this is a new submission, remind the user to fill in any fields still empty */

        if($Submit == "Submit")
        {
                print("<p>You have entered the following information for the LETS Ad Directory.  Please ensure that all information is correct, and that all <b>required</b> fields are complete.\n");
                print("<ul>\n");
                if(empty($AccountID)) {print("<li><b>Account Number</b>, <i>a required field</i> is empty\n") ;}
                if(empty($CategoryID)){print("<li><b>Category 1</b>, <i>a required field</i> is empty\n") ;}
                if(empty($CategoryID2)){print("<li>Category 2 is empty\n") ;}
                if(empty($CategoryID3)) {print("<li>Category 3 is empty\n");}
                if (empty($AdBeginDateYear)) {print("<li><b>Ad Begin Date (Year)</b>, <i>a required field</i> is empty\n");}
                if (empty($AdBeginDateMonth)) {print("<li><b>Ad Begin Date (Month)</b>, <i>a required field</i> is empty\n");}
                if (empty($AdBeginDateDay)) {print("<li><b>Ad Begin Date</b> (Day)<i>, a required field</i> is empty\n");}
                if(empty($AdEndDateYear)) {print("<li>End Date (Year) is empty (all ads are automatically expired after one year)\n");}
                if(empty($AdEndDateMonth)) {print("<li>End Date (Month) is empty (all ads are automatically expired after one year)\n");}
                if(empty($AdEndDateDay)) {print("<li>End Date (Day) is empty (all ads are automatically expired after one year)\n");}
                if(empty($AdName)) {print("<li><b>Ad Name</b>, <i>a required field</i> is empty\n");}
                if(empty($AdDescription)) {print("<li>Ad Description is empty\n");}
                print("</ul><p>\n<hr width=60%>\n<p>\n");
                }

/* If fields are empty, set them so that we don't get warnings all over the place */

        if(empty($AccountID)) {$AccountID = '';}
        if(empty($TradeType)) {$TradeType = '';}
        if(empty($CategoryID)){$CategoryID = '';}
        if(empty($CategoryID2)){$CategoryID2 = '';}
        if(empty($CategoryID3)) {$CategoryID3 = '';}
        if(empty($AdBeginDateYear)) {$AdBeginDateYear = '';}
        if(empty($AdBeginDateMonth)) {$AdBeginDateMonth = '';}
        if(empty($AdBeginDateDay)) {$AdBeginDateDay = '';}
        if(empty($AdEndDateYear)) {$AdEndDateYear = '';}
        if(empty($AdEndDateMonth)) {$AdEndDateMonth = '';}
        if(empty($AdEndDateDay)) {$AdEndDateDay = '';}
        if(empty($AdName)) {$AdName = '';}
        if(empty($AdDescription)) {$AdDescription = '';}

/* This next bit is just here to remove the URL encoding that may have happened on
our details as the form has been repeated... */

        if(!empty($AdName)) {$AdName = stripslashes($AdName);}
        if(!empty($AdDescription)) {$AdDescription = stripslashes($AdDescription);}

        /* And then here is the form. */

        print("<form action=\"adinputform.php\" method=post>\n");

/* We want to check the database first, to find out if the user is a member of more
than one account.  If more than one account exists, offer a choice as to which
account the ad should be associated.  Otherwise, just print the account number. */

        if(!empty($AdID))
        {
                print("<input type=hidden name=\"AdID\" value=$AdID>");
                $formtitle = "Edit Advertisement";
                }
        else
        {
                $formtitle = "Create a new Advertisement";
                }

        print ("<table noborder>
                <tr><th colspan=2 bgcolor=#D3D3D3><font size=+1>$formtitle</font></th></tr>
                <tr><th align=left>Account:</th>\n");
        $Accounts = mysql_query("SELECT AccountID
                                  FROM membertoaccountlink
                                   WHERE MemberID = $MemberID");
        $NumberOfAccounts = mysql_num_rows($Accounts);
        switch($NumberOfAccounts)
        {
                case 1:
                        $row = mysql_fetch_array($Accounts);
                        print("<td><input type=hidden name=\"AccountID\" value=\"$row[AccountID]\">\n$row[AccountID]</td>\n");
                        break;
                default:
                        print("<td><select name=\"AccountID\">\n");
                        print "<option value=$AccountID>$AccountID";
                        while($row = mysql_fetch_array($Accounts))
                        {
                                print("<option value=$row[AccountID]>$row[AccountID]</option>\n");
                                }
                        print("</select></td>\n");
                        break;
                }
        ?>

</tr>
<tr><th align=left valign=top>Trade Type:</th>
<td><input type=radio name="TradeType" value="R"
<? if($TradeType == 'R') {print(" checked"); } ?>
> I'm requesting something<br>
<input type=radio name="TradeType" value="O"
<? if($TradeType == 'O') {print(" checked"); } ?>
> I'm offering something</td></tr>
<tr><th align=left valign=top>Categories:<br>(select up to three)</th>
<td>

<?

/* The category selections are somewhat complex.  If an existing categoryID is
provided, we must first look up in the database which Heading and Category Name
the ID belongs to, so that the user can see useful information.  Then, we need to
look up all the categories so that we can assemble a select box for the user to
choose a category. This has to be repeated three times (one for each category ID).
Messy, but it works. */

        print("<select name=\"CategoryID\">\n");

        if(!empty($CategoryID))
        {
                $Selected = mysql_query("SELECT CategoryName, HeadingName
                                          FROM adcategories, adheadings
                                           WHERE adcategories.HeadingID = adheadings.HeadingID
                                           AND CategoryID = $CategoryID");
                $result = mysql_fetch_array($Selected);
                print("<option value=$CategoryID>$result[HeadingName]: $result[CategoryName]\n");
                }

        print "<option>";

        $Categories = mysql_query("SELECT *
                                    FROM adcategories, adheadings
                                     WHERE adcategories.HeadingID = adheadings.HeadingID
                                      ORDER BY HeadingName, CategoryName");

        while($row = mysql_fetch_array($Categories))
        {
                print("<option value=$row[CategoryID]>$row[HeadingName]: $row[CategoryName]\n");
                }

        print("</select><br>\n");

        print("<select name=\"CategoryID2\">\n");

        if(!empty($CategoryID2))
        {
                $Selected = mysql_query("SELECT CategoryName, HeadingName
                                          FROM adcategories, adheadings
                                           WHERE adcategories.HeadingID = adheadings.HeadingID
                                           AND CategoryID = $CategoryID2");
                $result = mysql_fetch_array($Selected);
                print("<option value=$CategoryID2>$result[HeadingName] $result[CategoryName]\n");

                }

        print "<option>";

        $Categories = mysql_query("SELECT *
                                    FROM adcategories, adheadings
                                     WHERE adcategories.HeadingID = adheadings.HeadingID
                                      ORDER BY HeadingName, CategoryName");

        while($row = mysql_fetch_array($Categories))
        {
                print("<option value=$row[CategoryID]>$row[HeadingName]: $row[CategoryName]\n");
                }

        print("</select><br>\n");

        print("<select name=\"CategoryID3\">");

        if(!empty($CategoryID3))
        {
                $Selected = mysql_query("SELECT HeadingName, CategoryName
                                          FROM adcategories, adheadings
                                           WHERE adcategories.HeadingID = adheadings.HeadingID
                                           AND CategoryID = $CategoryID3");
                $result = mysql_fetch_array($Selected);
                print("<option value=$CategoryID3>$result[HeadingName] $result[CategoryName]\n");
                }

        print "<option>";

        $Categories = mysql_query("SELECT *
                                    FROM adcategories, adheadings
                                     WHERE adcategories.HeadingID = adheadings.HeadingID
                                      ORDER BY HeadingName, CategoryName");

        while($row = mysql_fetch_array($Categories))
        {
                print("<option value=$row[CategoryID]>$row[HeadingName]: $row[CategoryName]\n");
                }

        print("</select></td></tr>\n");

/* Now we move on to dates.  These are also somewhat complex, though no where near
as much as the Categories.  Essentially the user is provided with a separate field
for each Y/M/D, so that we can control the format and order in which the fields go
into the database.  These are then reconstructed, in the routine above. */

        print ("<tr><th align=left valign=top>Begin Date:</th>
                <td><table noborder cellspacing=0 cellpadding=0><tr>
                       <td>Year:&nbsp;&nbsp;<br><input type=text name=\"AdBeginDateYear\" value=\"$AdBeginDateYear\" size=4>&nbsp;&nbsp;
                </td><td>Month:&nbsp;&nbsp;<br><select name=\"AdBeginDateMonth\"><option>$AdBeginDateMonth\n");
        for($month=1;$month<13;$month++)
        {
                print("<option>$month");
                }
        print("</select>&nbsp;&nbsp;</td><td>Day:&nbsp;&nbsp;<br><select name=\"AdBeginDateDay\"><option>$AdBeginDateDay\n");
        for($day=1;$day<31;$day++)
        {
                print("<option>$day");
                }
        print ("</select>&nbsp;&nbsp;</td></tr></table></td></tr>
                <tr><th align=left valign=top>End Date:</th>
                       <td><table noborder cellspacing=0 cellpadding=0><tr><td>Year:&nbsp;&nbsp;<br><input type=text name=\"AdEndDateYear\" value=\"$AdEndDateYear\" size=4>&nbsp;&nbsp;
                </td><td>Month:&nbsp;&nbsp;<br><select name=\"AdEndDateMonth\"><option>$AdEndDateMonth\n");
        for($month=1;$month<13;$month++)
        {
                print("<option>$month");
                }
        print("</select>&nbsp;&nbsp;</td><td>Day:&nbsp;&nbsp;<br><select name=\"AdEndDateDay\"><option>$AdEndDateDay\n");
        for($day=1;$day<32;$day++)
        {
                print("<option>$day");
                }
        print("</select>&nbsp;&nbsp;</td></tr></table><font size=-1>All ads automatically expire after one year<br>unless an earlier expiry is requested</font></td></tr>\n");

/* Really straightforward now, we just get the name and description of the ad */

        print("<tr><th align=left>Ad Title:</th>\n");
        print("<td><input type=text name=\"AdName\" value=\"$AdName\" size=30 maxlength=30>\n");
        print("</td></tr>\n");

        if (strlen($AdDescription) >= 255)
        {
            print("<tr><td colspan=2><font color=red><B>Warning:</B> Your Ad description exceeds the maximum length of 255 characters.<BR>Some description information may be lost during submission.</font></td></tr>");
        }
        print("<tr><th align=left valign=top>Description:</th>\n");
        print("<td><textarea name=\"AdDescription\" cols=30 rows=4>$AdDescription</textarea>\n");
        print("</td></tr>\n");

/* Finally, we have to offer a different submit button, depending on where we have
come from, to make sure that this system flows properly (i.e. initial form, then a
confirmation form, then the database is actually altered) */

        switch($Submit)
        {
                case 'Submit':
                        print("<tr><td colspan=2 align=right>\n<input type=submit name=\"Submit\" value=\"Confirm\">\n");
                        print("</td></tr></table></form>\n");
                        break;
                case 'Edit':
                        print("<tr><td colspan=2 align=right>\n<input type=submit name=\"Submit\" value=\"Confirm\">\n");
                        print("</td></tr></table></form>\n");
                        break;
                default:
                        print("<tr><td colspan=2 align=right>\n<input type=submit name=\"Submit\" value=\"Submit\">\n");
                        print("</td></tr></table></form>\n");
                        break;
                }

        include "footer.php";

        }
