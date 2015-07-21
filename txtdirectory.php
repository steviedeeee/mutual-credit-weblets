<?
  /*********************************************************************/
  /*
       Writen By:     Marti Settle
       Last Modified: July 20, 2002
       Called By:     processmailing.php
       Calls:
       Description:  This script prepares a simple text listing of all ads in the system

       Modification History:
                    July 20, 2002 - file created
  */
  /*********************************************************************/

  /* The usual includes */

  include "configuration.php";
  include "connectdb.php";

  error_reporting(255);
  /* to create an index, we need to look up all the categories that are in use, and build an array
  out of them */

  $categorieslookup = mysql_query("SELECT DISTINCT adcategories.CategoryID, HeadingName, CategoryName
                                                                  FROM adheadings, adcategories, advertisements, account
                                                                WHERE adheadings.HeadingID = adcategories.HeadingID
                                                                AND (adcategories.CategoryID = advertisements.CategoryID
                                                                        OR adcategories.CategoryID = advertisements.CategoryID2
                                                                        OR adcategories.CategoryID = advertisements.CategoryID3)
                                                                AND account.AccountID = advertisements.AccountID
                                                                AND advertisements.AdBeginDate <= CURDATE()
                                                                AND advertisements.AdExpiryDate >= CURDATE()
                                                                AND account.AccountRenewalDate >= CURDATE()
                                                                AND AccountStatus = 'OK'
                                                                ORDER BY HeadingName, CategoryName");
  $row = 0;
  while($categories = mysql_fetch_array($categorieslookup))
  {
        $CategoryID[$row] = $categories["CategoryID"];
        $Heading[$row] = $categories["HeadingName"];
        $CategoryName[$row] = $categories["CategoryName"];
        $CatListing[$categories["CategoryID"]] = "$categories[HeadingName]: $categories[CategoryName]";
        $row++;
        }


  /* now we look up the ads, and create a variable holding a list indexed by reference numbers.
  These reference numbers are then added to the list of ads referenced by the category variable */

  $adlookup = mysql_query("SELECT advertisements.*, AccountName, HomeNumber, EmailAddress
                           FROM advertisements,account,membertoaccountlink,member
                           WHERE advertisements.AccountID = account.AccountID
                           AND account.AccountID = membertoaccountlink.AccountID
                           AND membertoaccountlink.MemberID = member.MemberID
                           AND PrimaryContact = 1
                           AND AdBeginDate <= CURDATE()
                           AND AdExpiryDate >= CURDATE()
                           AND AccountRenewalDate >= CURDATE()
                           AND AccountStatus = 'OK'
                           ORDER BY AdExpiryDate, AdName");
  $ref = 1;
  $textads = '';
  while($ad = mysql_fetch_array($adlookup))
  {
        $textads .= "$ref. " . strtoupper($ad["AdName"]);
        if($ad["TradeType"] == 'O')
        {
            $textads .= " (Offered)\n";
            }
        else
        {
            $textads .= " (Requested)\n";
            }
        $textads .= "   Account: $ad[AccountName]\tPhone: $ad[HomeNumber]\n";
        $textads .= "   Categories: " . $CatListing["$ad[CategoryID]"] . "\n";
        if(!empty($ad["CategoryID2"]))
        {
            $textads .= "               " . $CatListing["$ad[CategoryID2]"] . "\n";
            }
        if(!empty($ad["CategoryID3"]))
        {
            $textads .= "               " . $CatListing["$ad[CategoryID3]"] . "\n";
            }
        $textads .= "   Expires: $ad[AdExpiryDate]\n";
        $textads .= strip_tags($ad["AdDescription"]) . "\n\n\n";

// This next section creates the arrays used to assemble the categorized index

        $AdList[$ad["CategoryID"]][] = $ref;
        if(!empty($ad["CategoryID2"]))
        {
            $AdList[$ad["CategoryID2"]][] = $ref;
            }
        if(!empty($ad["CategoryID3"]))
        {
            $AdList[$ad["CategoryID3"]][] = $ref;
            }

// This is where the HTML version would be assembled

        $ref++;
        }

// Build the categorized index

$CurrentHeading = '';
$textindex = '';

while(list($row, $ID) = each($CategoryID))
{
        if($Heading["$row"] != $CurrentHeading)
        {
             $textindex .= "\n" . strtoupper($Heading["$row"]) . "\n";
             $CurrentHeading = $Heading["$row"];
             }
        $textindex .= "    $CategoryName[$row]";
        $textindex .= "    ";
        while(list($key,$ref) = each($AdList[$ID]))
        {
             $textindex .= $ref . ", ";
             }
        $textindex = substr($textindex,0,-2);
        $textindex .= "\n";
        }

// Save the data to a file

$file = fopen("tmp/directory.txt",'w');
fputs($file,"$Systemname Advertisements\nas of " . date("Y-m-d") . "\n\n");
fputs($file,"***** ADVERTISEMENT INDEX BY CATEGORY *****\n");
fputs($file,"$textindex\n\n");
fputs($file,"***** ADVERTISEMENTS *****\n\n");
fputs($file,"$textads\n\n");
fclose($file);

?>