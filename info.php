<?

/******************************************************************************/
/*                                                                            */
/*            File Name:        info.php                                      */
/*            Created By:       Martin Settle                                 */
/*            Creation Date:    December 17, 2001                             */
/*            Called By:        header.php, self                              */
/*            Calls:            nothing                                       */
/*                                                                            */
/*            Change Log:                                                     */
/*                 Dec. 17, 2001 - File Created                              */
/*                                                                            */
/******************************************************************************/

include "configuration.php";
include "connectdb.php";

/* The result of this page is controlled by a GET or POST variable entitled
Page.  If there is not Page specified, the system must print the default page */

if(empty($Page))
{
       if(!$pagelookup = mysql_query("SELECT PageID FROM infopages
                                       WHERE MainPage = 'Yes'"))
       {
           $title = 'No Page found';
           include "header.php";
           print ("<h1>No Information Page Available</h1>
                   Sorry, there is no information page available at this time.");
           include "footer.php";
           exit();
       }
       $Pageresult = mysql_fetch_array($pagelookup);
       $Page = $Pageresult["PageID"];
}

/* Now that we definitely have a page, look it up, and print it... */

$pageinfolookup = mysql_query("SELECT * FROM infopages
                                WHERE PageID = '$Page'");
$pageinfo = mysql_fetch_array($pageinfolookup);

$title = $pageinfo["Title"];

include "header.php";

print ("<h1>$pageinfo[Title]</h1>\n");

if(($pageinfo["Menu"] == 'Yes') && ($pageinfo["MenuPlacement"] == 'Top'))
{
    print ("<ul class='InfoMenu'>\n");

    $linklookup = mysql_query("SELECT PageID, Title
                                FROM infopages
                                 WHERE Parent = '$Page'
                                  ORDER BY Priority");
    while($link = mysql_fetch_array($linklookup))
    {
        print ("<li class='InfoMenu'><a href='info.php?Page=$link[PageID]' class='InfoMenu'>$link[Title]</a>\n");
    }

    print ("</ul>
            <p>
            <HR class='InfoMenu'>
            <p>\n");
}

print $pageinfo["Data"];

if(($pageinfo["Menu"] == 'Yes') && ($pageinfo["MenuPlacement"] == 'Bottom'))
{
    print ("<p>
            <HR class='InfoMenu'>
            <p>
            <ul class='InfoMenu'>\n");

    $linklookup = mysql_query("SELECT PageID, Title
                                FROM infopages
                                 WHERE Parent = '$Page'
                                  ORDER BY Priority");
    while($link = mysql_fetch_array($linklookup))
    {
        print ("<li class='InfoMenu'><a href='info.php?Page=$link[PageID]' class='InfoMenu'>$link[Title]</a>\n");
    }

    print ("</ul>\n");
}

if($pageinfo["MainPage"] != 'Yes')
{
        $ParentLookup = mysql_query("SELECT PageID, Title
                                      FROM infopages
                                       WHERE PageID = '$pageinfo[Parent]'");
        $Parent = mysql_fetch_array($ParentLookup);
        print ("<br><br><center><hr width=50%><br>
                Back to <a href='info.php?Page=$Parent[PageID]'>$Parent[Title]</a>
                </center><br>");
        }

include "footer.php";

?>