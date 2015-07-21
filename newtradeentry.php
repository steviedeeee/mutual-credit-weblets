<?

  /*********************************************************************/
  /*
       Writen By:     Martin Settle
       Last Modified: October 1, 2001
       Called By:     Nothing
       Calls:         Nothing
       Description:   This is the transaction entry program.

       Modification History:
                    September 13, 2001 - File created.
                    December 21, 2001 - support for buyer entry added
  */
  /*********************************************************************/

/* Get the includes out of the way, right off the bat */

   include "configuration.php";
   include "connectdb.php";

/* The form used to enter the trade is printed by calling this subroutine.  If
some variables are given, they are used as default */

function tradeentryform()
{

/* First we have to look up whether the buyer or the seller enters the trades */

         $Entrylookup = mysql_query("SELECT TradeEntryBy FROM administration");
         $Entrybyarray = mysql_fetch_array($Entrylookup);
         $Entryby = $Entrybyarray["TradeEntryBy"];

/* Now begin printing the form */

         print ("<form action=\"tradeentry.php\" method=post>
                 <input type=hidden name=EnteredBy value='$Entryby'>
                 <table noborder>
                 <tr><th align=center colspan=2 bgcolor=#D3D3D3>Trade Data</th></tr>");

         switch($Entryby)
         {
                case 'seller':
                      print("<tr><th align=left>Your Account:</th>");

                      $selleraccounts = mysql_query("SELECT AccountID
                                         FROM membertoaccountlink
                                         WHERE MemberID = $GLOBALS[MemberID]");
                      switch(mysql_num_rows($selleraccounts))
                      {
                              case 1:
                                  $AccountID = mysql_fetch_array($selleraccounts);
                                  print ("<td><strong>$AccountID[AccountID]</strong>
                                          <input type=hidden name=SellerID value=$AccountID[AccountID]
                                          </td></tr>");
                                  break;
                              default:
                                  print ("<td><select name=SellerID><option>");
                                  if(!empty($GLOBALS["SellerID"]))
                                  {
                                          print $GLOBALS["SellerID"];
                                          }
                                  while($row = mysql_fetch_array($selleraccounts))
                                  {
                                          print "<option>$row[AccountID]\n";
                                          }
                                  print ("</select></td></tr>\n");
                                  break;
                              }
                      print ("<tr><th align=left>Buyer's Account:</th>
                              <td><input type=text name=BuyerID ");
                      if(!empty($GLOBALS["BuyerID"]))
                      {
                              print "value='$GLOBALS[BuyerID]'";
                              }
                      print ("></td></tr>\n");
                      break;

                case 'buyer':
                      print("<tr><th align=left>Your Account:</th>");

                      $buyeraccounts = mysql_query("SELECT AccountID
                                         FROM membertoaccountlink
                                         WHERE MemberID = $GLOBALS[MemberID]");
                      switch(mysql_num_rows($buyeraccounts))
                      {
                              case 1:
                                  $AccountID = mysql_fetch_array($buyeraccounts);
                                  print ("<td><strong>$AccountID[AccountID]</strong>
                                          <input type=hidden name=BuyerID value=$AccountID[AccountID]
                                          </td></tr>");
                                  break;
                              default:
                                  print ("<td><select name=BuyerID><option>");
                                  if(!empty($GLOBALS["BuyerID"]))
                                  {
                                          print $GLOBALS["BuyerID"];
                                          }
                                  while($row = mysql_fetch_array($selleraccounts))
                                  {
                                          print "<option>$row[AccountID]\n";
                                          }
                                  print ("</select></td></tr>\n");
                                  break;
                              }
                      print ("<tr><th align=left>Seller's Account:</th>
                              <td><input type=text name=SellerID ");
                      if(!empty($GLOBALS["SellerID"]))
                      {
                              print "value='$GLOBALS[SellerID]'";
                              }
                      print ("></td></tr>\n");
                      break;
                default:
                        print ("there is something wrong here...");
                }
        if(empty($GLOBALS["Description"]))
        {
                $GLOBALS["Description"] = "";
                }
        if(empty($GLOBALS["Amount"]))
        {
                $GLOBALS["Amount"] = "";
                }
        print ("</td></tr>
                <tr><th align=left>Item(s) Traded:</th>
                <td><input type=text name=Description size=40 value='$GLOBALS[Description]'></td></tr>
                <tr><th align=left>Eco Amount:</th>
                <td><input type=text name=Amount size=8 value='$GLOBALS[Amount]'></td></tr>
                <tr><td colspan=2 align=middle>
                <input type=hidden name=Function value=VerifyTrade>
                <input type=Submit value='Record Trade'></td></tr>
                </table>
                <p>");
        }

/* MAIN PROGRAM BEGINS HERE
Start by checking that the user is logged in as a member.  If not, print
a message and exit. */

if(empty($MemberID))
{
        $title = "Not Authorized";
        include "header.php";
        print ("<h1>Not Authorized</h1>
                You are not authorized to enter trades.  If you are a LETS member, please ensure that you have <a href=\"login.php\">logged in</a>.");
        include "footer.php";
        exit();
        }

/* Now check the $Function variable.  If it is not present, this is the first
time the page has been called.*/

if(empty($Function))
{

        /* Call the form subroutine and exit */

        $title = 'Trade Entry';
        include "header.php";
        print ("<h1>Trade Entry</h1>");
        tradeentryform();
        include "footer.php";
        exit();
         }

?>