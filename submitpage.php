<?
  /*********************************************************************/
  /*
       Writen By:     Martin Settle
       Last Modified: December 28, 2001
       Called By:     infopages.php
       Calls:         Nothing
       Description:   This processes edits of the system information
                      pages.

       Modification History:
                    December 28, 2001 - File created.
  */
  /*********************************************************************/

/* Get the includes out of the way */

include "configuration.php";
include "connectdb.php";

/* The major piece of processing that needs to be done of entered data is
altering non-html formatted data fields to html so that the page can be
properly displayed.  Of course, some only needs to be done if the data
submitted is not already HTML formatted. */

$Data = rawurldecode($Data);

if($html == 'no')
{
        $Data = htmlentities($Data);
        $Data = nl2br($Data);
        }

/* There should be a function here to check to make sure that that no more
than one page is identified as the MainPage */

if($MainPage == 'Yes')
{
	}

/* initialize a message variable */

$SystemMessage = '';

/* The process for inputting the data is dependent on whether or not the
page is being submitted for the first time.  This is shown by the presence
or absence of a PageID */

if(!empty($PageID))
{
        $query = "UPDATE infopages SET Parent = '$Parent', Title='$Title',Menu='$Menu', MenuPlacement='$MenuPlacement',Data='$Data',MainPage='$MainPage' WHERE PageID='$PageID'";
        if(!mysql_query("$query"))
        {
        	$title = 'InfoPage update failed';
                include "header.php";
                print ("The system was unable to update the info page as requested.<p>
                	The database returned the following:<p>");
                print mysql_error();
                include "footer.php";
                exit();
                }
        $SystemMessage .= "<strong>$Title</strong> was successfully changed in the LETSystem database.<p>\n";
        $time = time();
        mysql_query("INSERT INTO adminactions VALUES (NULL,'$MemberID','Edited information Page #$PageID and menu priority')");
        }
else
{

/* need to lookup the next priority */

	$PriorityLookup = mysql_query("SELECT Max(Priority) AS Last
        				FROM infopages
                                         WHERE Parent='$Parent'");
        $Priority = mysql_result($PriorityLookup,0,'Last');
        $Priority++;

        $query = "INSERT INTO infopages VALUES ('','$Parent','$Title','$Menu','$MenuPlacement','$Priority','$Data','$MainPage')";
        if(!mysql_query("$query"))
        {
                $title = 'Unable to add page';
                include "header.php";
                print ("The system was unable to add the $Title page to the LETSystem Database.<p>
                	The database returned the following:<p>");
                print mysql_error();
                include "footer.php";
                exit();
                }
        mysql_query("INSERT INTO adminactions VALUES (NULL,'$MemberID','Added information Page #$PageID')");
        $SystemMessage .= "<strong>$Title</strong> was successfully added to the LETSystem database.<p>";
        include "infopages.php";
        exit();
        }

/* If still in this system, we may need to process priority changes.  These are held
in an array of $Priority["$PageID"] */

while(list($newPageID,$newPriority) = each($MenuPriority))
{
        $query = "UPDATE infopages SET Priority='$newPriority' WHERE PageID = '$newPageID'";
        if(!mysql_query("$query"))
        {
        	$SystemMessage .= "The system was unable to change the priority of page #$newPageID.<br>";
                }
        }

/* Should be done now, so clear the PageID (so that the infopage will show a blank
form) and exit */

unset($PageID);
include "infopages.php";
exit();
?>