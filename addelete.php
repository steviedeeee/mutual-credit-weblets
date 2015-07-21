<?
  /*********************************************************************/
  /* Include the Configuration file. This includes system dependant    */
  /* variables that allow the system to be flexible and for future     */
  /* expansion.                                                        */
  /*********************************************************************/

  include "configuration.php"
?>

<?
  /*********************************************************************/
  /*
       Writen By:     Martin Settle
       Last Modified: September 10, 2001
       Called By:     adlisting, adinputform
       Calls:         connectdb
       Description:   This is a short routine to delete a specific ad
                             from the database

       Modification History:
                    September 10, 2001 - Program created
  */
  /*********************************************************************/

include "connectdb.php";

if(!mysql_query("DELETE FROM advertisements WHERE AdID = $AdID"))
{
        include "header.php";
        $error = mysql_error();
        print "<h2> Delete failed</h2>The system failed to register your delete request.<p>The database returned the following error message: $error";
        include "footer.php";
        exit();
        }
$title = "Ad Deleted";
include "header.php";
print "<h2>Ad Deleted</h2>The selected ad has been successfully removed from the database.<p>Please use your browser's \"Back\" button to return to the previous page (you may have to hit \"Refresh\" to remove the deleted ad)";
include "footer.php";

  /*********************************************************************/
  /*                     PAGE ENDS HERE          File: addelete.php    */
  /*********************************************************************/
?>