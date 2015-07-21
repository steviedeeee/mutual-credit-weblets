<?

/* This script processes an account renewal, resetting the AccountRenewalDate
to one year later, or to another date specified by the administrator, and
looks up and processes the account renewal fee. */

# the usual includes

include "configuration.php";
include "connectdb.php";

include "adminlogin.php";

# if the AccountID is not given, print the form

if(empty($Submit))
{
        $title = "Account Renewal";
        include "header.php";

        print ("<!--- JAVASCRIPT FUNCTION --->
                <script language='JAVASCRIPT'>
                function reload()
                {
                        document.ReloadwithAccountID.submit()
                        }
                </script>
                <h2>Account Renewal</h2>
                Please select the Account you wish to renew:<br>
                <form action=accountrenewal.php method=post name=ReloadwithAccountID>
                <select ONCHANGE=reload() name=AccountID>
                <option value=0>Select Account");
        $AccountLookup = mysql_query("SELECT AccountID, AccountName
                                      FROM account
                                      WHERE AccountStatus != 'Closed'
                                      ORDER BY AccountID");
        while($Account = mysql_fetch_array($AccountLookup))
        {
                print ("<option value=$Account[AccountID]");
                if(!empty($AccountID))
                {
                        if($AccountID == $Account[AccountID]) print " selected";
                        }
                print (">$Account[AccountID]: $Account[AccountName]\n");
                }
        print ("</select>
                </form><p>\n");
        if(!empty($AccountID))
        {
                $MonthName = array('','January','February','March','April','May','June','July','August','September','October','November','December');
                $RenewalLookup = mysql_query("SELECT AccountName, AccountRenewalDate, AccountTypeRenewalCost
                                              FROM account, accounttypeoptions
                                              WHERE AccountID = $AccountID
                                              AND account.AccountTypeID = accounttypeoptions.AccountTypeID");
                $Renewal = mysql_fetch_array($RenewalLookup);
                list($Year,$Month,$Day) = explode('-',$Renewal["AccountRenewalDate"]);
                print ("Account #$AccountID: $Renewal[AccountName] is currently set to expire on " .  $MonthName[intval($Month)] . " $Day, $Year. Please set the new expiry date:<br>
                        <form action=accountrenewal.php method=post>
                        <input type=hidden name=AccountID value='$AccountID'>
                        <select name=Month>");
                while(list($key,$mon) = each($MonthName))
                {
                        print ("<option value=$key");
                        if(intval($Month) == $key) print " selected";
                        print (">$mon");
                        }
                $Newyear = $Year + 1;
                print ("</select>\n<input type=text name=Day size=2 value='$Day'>, <input type=text name=Year value='$Newyear'><p>
                        A renewal fee of $Renewal[AccountTypeRenewalCost] ecodollars will be charged to this account.<p>
                        <input type=submit name=Submit value='Renew Account'>
                        </form>\n");
                }
        print ("<p><hr><p>
                <form action=expiredaccounts.php method=POST>
                <strong>Show Expired Accounts as of: <input type=text name=Date value='");
        print date("Y-m-d");
        print ("' size=10> <input type=submit value='Print List'></form><p>");
        include "footer.php";
        exit();
        }

# if we have an AccountID, process the renewal.

mysql_query("UPDATE account
             SET AccountRenewalDate = '$Year-$Month-$Day'
             WHERE AccountID = '$AccountID'");

# process the fee

$CostLookup = mysql_query("SELECT AccountTypeRenewalCost
                           FROM account, accounttypeoptions
                           WHERE account.AccountTypeID = accounttypeoptions.AccountTypeID
                           AND AccountID = '$AccountID'");
$Cost = mysql_result($CostLookup,0,'AccountTypeRenewalCost');

# get the current balance

$BalanceLookup = mysql_query("SELECT CurrentBalance FROM transactions
                                                        WHERE AccountID = $AccountID
                                                        ORDER BY Reference DESC
                                                        LIMIT 1");
$Balance = mysql_result($BalanceLookup,0,'CurrentBalance');

# get the system balance

$SystemBalanceLookup =mysql_query("SELECT CurrentBalance FROM transactions
                                                        WHERE AccountID = $SystemAccountID
                                                        ORDER BY Reference DESC
                                                        LIMIT 1");
$SystemBalance = mysql_result($SystemBalanceLookup,0,'CurrentBalance');

# get a transaction ID

$transIDtime = time();
mysql_query("INSERT INTO transidlookup VALUES ('','$transIDtime','$MemberID')");
$transIDlookup = mysql_query("SELECT TransactionID
                                        FROM transidlookup
                                        WHERE Time = $transIDtime
                                         AND MemberID = $MemberID");
$TransactionID = mysql_result($transIDlookup,0,'TransactionID');
mysql_query("DELETE FROM transidlookup WHERE TransactionID = $TransactionID");

# Input the fee

mysql_query("INSERT INTO transactions
             SET TransactionID = $TransactionID,
             AccountID = '$AccountID',
             Description = 'Account Renewal',
             TradeDate = CURDATE(),
             Amount = '-$Cost',
             CurrentBalance = $Balance - $Cost,
             OtherAccountID = '$SystemAccountID'");

mysql_query("INSERT INTO transactions
             SET TransactionID = $TransactionID,
             AccountID = '$SystemAccountID',
             Description = 'Account Renewal',
             TradeDate = CURDATE(),
             Amount = $Cost,
             CurrentBalance = $SystemBalance + $Cost,
             OtherAccountID = '$AccountID'");

# print a confirmation page

$title = "Account $AccountID Renewed";
include 'header.php';
print ("<h2>Account Renewal Complete</h2>
        Account #$AccountID has been successfully renewed.\n");
include 'footer.php';

?>