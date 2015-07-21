<?


/**********************************************************************************/
/*

        This file will convert user data from a QLets list file (no notes)
        and input them into the system.

*/
/**********************************************************************************/

#This is the function to set up the transaction ID for the current balance transfer

function TransID()
{
        $transIDtime = time();
        if(!mysql_query("INSERT INTO transidlookup
                          VALUES ('','$transIDtime','247')"))
        {
                print ("WARNING: Unable to look up transaction ID.  Current Balance Transfer will fail.\n");
                print mysql_error() . "\n";
                }
        $transIDlookup = mysql_query("SELECT TransactionID
                                        FROM transidlookup
                                        WHERE Time = $transIDtime
                                         AND MemberID = '247'");
        $Transaction = mysql_fetch_array($transIDlookup);
        mysql_query("DELETE FROM transidlookup WHERE Time = '$transIDtime' AND MemberID = '247'");
        return($Transaction["TransactionID"]);
        }


/* Get the filename of the data file */

if(empty($FileName))
{
        print ("<html>
                <head><title>QLets Conversion Utility</title></head>
                <body>
                <h1>QLets Data Conversion</h1>
                In order to convert data from QLets, you will first need to print an account list from the QLets software, including all data for open accounts except the Note field.  That file must be uploaded to the directory that holds this program.<p>
                You will need the following:<br>
                <form action='qlets_convert.php' method='POST'>
                <table noborder>
                <tr><th align=left>MySQL Admin login: </th><td><input type=text size=10 name=admin></td></tr>
                <tr><th align=left>MySQL Admin Password: </th><td><input type=password size=10 name=adminpassword></td></tr>
                </table><p></p>
                Please identify the file you wish to have converted:<br>
                <input type=text name=FileName size=40><p>
                <input type=submit value=Convert>
                </form>
                </body>
                </html>");
        exit();
        }

/* and then run the conversion... */

else
{
        include 'configuration.php';
        error_reporting(255);
// Connect to the database in admin mode, using variables supplied by user
         if(!mysql_connect("$host","$admin","$adminpassword"))
         {
               ?>ERROR CONNECTING TO SERVER<?
               exit();
               }
         if(!mysql_select_db("$database"))
         {
               ?>ERROR CONNECTING TO DATABASE<?
               exit();
               }


        print ("<html><head><title>QLets Conversion Utility</title></head><body>");
        if(!$Data = fopen("$FileName", 'r'))
        {
                 print "Error: File could not be opened";
                 print "</body></html>";
                 exit();
                 }
        print ("<pre>\n");
        for($count = 1; $count <= 4; $count++)
        {
                 fgets("$Data",255);
                 }
        $Record = 0;
        while(!feof($Data))
        {
                 $Record++;
                 $Current = $Record;
                 while($Current == $Record)
                 {
                          $Line = fgets("$Data",255);
                          if(strlen($Line) <= 2)
                          {
                                   $Current++;
                                   }
                          else
                          {
                                   if(substr("$Line",0,6) != "      ")
                                   {
                                            $AccountID["$Record"] = ltrim(substr("$Line",0,6));
                                            if(substr("$Line",8, 8) == 'Company:')
                                            {
                                                     $AccountName["$Record"] = chop(substr("$Line",17,30));
                                                     $Line = fgets("$Data",255);
                                                     $Name["$Record"] = chop(substr("$Line",8,39));
                                                     $HomePhone["$Record"] = chop(substr("$Line",47,14));
                                                     $BusinessPhone["$Record"] = chop(substr("$Line",64,14));
                                                     $AccountType["$Record"] = 'Business';
                                                     }
                                            else
                                            {
                                                     $Name["$Record"] = chop(substr("$Line",8,39));
                                                     $AccountName["$Record"] = $Name["$Record"];
                                                     $HomePhone["$Record"] = chop(substr("$Line",47,14));
                                                     $BusinessPhone["$Record"] = chop(substr("$Line",64,14));
                                                     $AccountType["$Record"] = 'Individual';
                                                     }
                                          }
                                   elseif(substr("$Line",8,6) == "email:")
                                   {
                                            $Email["$Record"] = chop(substr("$Line",15,50));
                                            }
                                   elseif(substr("$Line",8,8) == "balance:")
                                   {
                                            $Balance["$Record"] = ltrim(substr("$Line",17,9));
                                            $Volume["$Record"] = ltrim(substr("$Line",38,9));
                                            }
                                   elseif(substr("$Line",8,7) == "joined:")
                                   {
                                             $JoinDate["$Record"] = substr("$Line",22,4) . "-" . substr("$Line",16,2) . "-" . substr("$Line",19,2);
                                             }
# if it doesn't fall into any of the above categories, then it must be the address
# field, which fall on two lines
                                   else
                                   {
                                            $StreetAddress["$Record"] = chop(substr("$Line",8,50));
                                            $Line = fgets($Data,255);
                                            $PostalCode["$Record"] = substr("$Line",-8,7);
                                            $Province["$Record"] = substr("$Line", -12,2);
                                            $City["$Record"] = substr("$Line",8,-13);
                                           }
                                   }
                          }
                 }

        fclose("$Data");

# change the account table to allow users to keep their accountIDs

        if(!mysql_query("ALTER TABLE account MODIFY AccountID int(11) NOT NULL"))
        {
                print ("\n\n\nWARNING:  UNABLE TO MODIFY TABLE STRUCTURE\n" . mysql_error() . "\n\n\n");
                exit();
                }

# open an error log

        if(!$Errorfile = fopen("Qlets_Errors.txt",'w'))
        {
                 print "Unable to open Error file...\n";
                 }
        $date = date("Y-m-d");

# process each record

        for($R=1;$R<=$Record;$R++)
        {

                if(empty($Email["$R"]))
                {
                        $Email["$R"] = '';
                        }
#                print "\nAccountID = " . $AccountID["$R"] . "\n";
#                print "Join Date = $JoinDate[$R]\n";
#                print "AccountName = " . $AccountName["$R"] . "\n";
#                print "Account Type = $AccountType[$R]\n";
#                print ("Member Name = $Name[$R]\n");
                $Names = explode(" ", $Name["$R"]);
                $FirstName = $Names[0];
                if(count($Names) == 1)
                {
                        $MiddleName = '';
                        $LastName = '';
                        }
                elseif(count($Names) > 2)
                {
                        $MiddleName = $Names[1];
                        $LastName = '';
                        for($N = 2; $N <= (count($Names)-1); $N++)
                        {
                                $LastName .= "$Names[$N] ";
                                }
                        }
                else
                {
                        $LastName = $Names[1];
                        $MiddleName = '';
                        }
                $FirstName = ucfirst(strtolower("$FirstName"));
                $MiddleName = ucfirst(strtolower("$MiddleName"));
                $LastName = ucfirst(strtolower("$LastName"));

#                print ("First Name = $FirstName\nMiddle Name = $MiddleName\nLast Name = $LastName\n");
#                print ("City = $City[$R]\nProvince = $Province[$R]\n");
#                print ("Home Phone = $HomePhone[$R]\n");
#                print ("Mobile Phone = $BusinessPhone[$R]\n");
#                print ("E-mail = $Email[$R]\n");
#                print ("Account Balance = $Balance[$R]\n");
#                print ("Account Volume = $Volume[$R]\n");
                $PVolume = ($Volume["$R"] - $Balance["$R"])/2 + $Balance["$R"];
#                print ("Credit Volume = $PVolume\n");
                $NVolume = $Volume["$R"] - $PVolume;
#                print ("Debit Volume = $NVolume\n");

# Create a LoginID

                $LoginID = substr("$FirstName",0,5) . substr("$LastName",0,5);
   #             print "Login ID = $LoginID\n";
   #             print "Password = $PostalCode[$R]\n";

# and ensure a password...

                if(empty($PostalCode["$R"]))
                {
                        $PostalCode["$R"] = 'XXX XXX';
                        }
# Insert the data into the appropriate tables

                $memberquery = "INSERT INTO member ";
		$memberquery .= "SET MemberFirstName = '$FirstName' ";
		$memberquery .= "MemberMiddleName = '$MiddleName' ";
		$memberquery .= "MemberLastName = '$LastName' ";
		$memberquery .= "MailingAddress1 = '$StreetAddress[$R]' ";
		$memberquery .= "MailingCity = '$City[$R]' ";
		$memberquery .= "MailingProvince = '$Province[$R]' ";
		$memberquery .= "MailingPostalCode = '$PostalCode[$R]' ";
		$memberquery .= "HomeNumber = '$HomePhone[$R]' ";
		$memberquery .= "OtherNumber = '$BusinessPhone[$R]' ";
		$memberquery .= "EmailAddress = '$Email[$R]' ";
		$memberquery .= "DeliveryMethodID = '3' ";
		$memberquery .= "LoginID = '$LoginID' ";
		$memberquery .= "Password = '$PostalCode[$R]' ";
		$memberquery .= "PriorLogin = '0'";

                if(!mysql_query("$memberquery"))
                {
                        fputs("$Errorfile", mysql_error() . "\n$memberquery\n\n");
                        }

                $IDLookup = mysql_query("SELECT MemberID FROM member WHERE LoginID = \"$LoginID\" AND MailingPostalCode = \"$PostalCode[$R]\"");
                $MemberID = mysql_result("$IDLookup",0);
   #             print "MemberID = $MemberID\n";

                switch($AccountType["$R"])
                {
                        case 'Business':
                                $Type = 3;
                                break;
                        case 'Individual':
                                $Type = 2;
                                }
                $Renewal = date('Y')+1;
                $Renewal .= "-01-01";
                $accountquery = "INSERT INTO account VALUES(\"$AccountID[$R]\",\"$AccountName[$R]\",'$Type','$Renewal','$JoinDate[$R]','','300.00','OK')";
                if(!mysql_query("$accountquery"))
                {
                        fputs("$Errorfile", mysql_error() . "\n$accountquery\n\n");
                        }

                $m2alinkquery = "INSERT INTO membertoaccountlink VALUES ('$AccountID[$R]','$MemberID','1')";
                if(!mysql_query("$m2alinkquery"))
                {
                        fputs("$Errorfile", mysql_error() . "\n$m2alinkquery\n\n");
                        }

# Look up the transaction ID
                $creditID = TransID();
                $debitID = TransID();
                $creditquery = "INSERT INTO transactions SET TransactionID = '$creditID',TradeDate = '$date',AccountID = '$AccountID[$R]',Amount = '$PVolume',Description = 'Previous Sales Credit', CurrentBalance = '$PVolume',OtherAccountID = '0'";
                if(!mysql_query("$creditquery"))
                {
                        fputs("$Errorfile", mysql_error() . "\n$creditquery\n\n");
                        }
                $debitquery = "INSERT INTO transactions SET TransactionID = '$debitID',TradeDate = '$date',AccountID = '$AccountID[$R]',Amount = '-$NVolume',Description = 'Previous Purchase Debit',CurrentBalance = '$Balance[$R]',OtherAccountID = '0'";
                if(!mysql_query("$debitquery"))
                {
                        fputs("$Errorfile", mysql_error() . "\n$debitquery\n\n");
                        }
                print ("Completed processing AccountID $AccountID[$R].\n");
                }

        fputs("$Errorfile", "End of Error File");
        if(!mysql_query("ALTER TABLE account MODIFY AccountID int(11) NOT NULL auto_increment"))
        {
                print ("\n\n\nWARNING:  UNABLE TO RESTORE TABLE STRUCTURE\n\n\n");
                }

        print ("\nPROCESSING COMPLETE.  Check Qlets_Errors.txt for error messages.\n");
        }



exit();
?>