<?
/******************************************************************************/
/*
                Written by:     Martin Settle
                Last Modified:  January 13, 2002
                Called by:      all reporting pages
                Calls:          nothing
                Description:    This file includes all of the functions to
                                process a variety of report figures, all
                                based upon an inputted start and end date.

                Modification History:
                                January 19, 2002 - file created
				February 12, 2003 - SQL statements edited to
					filter account closures, system mailings
					
*******************************************************************************/

/* This file contains all of the functions required by the system reports section
of the site.  The following list summarizes the name of each function, the
parameters it requires (other than BeginDate and EndDate), and the result of the
function (this list is used to develop the request form)

NAME
TITLE
FUNCTION
CATEGORY
EXTRA PARAMETERS
----------------
+++Begin
SystemBalance
Total System Balance
returns the sum value of all transactions recorded by the system since its start.  This number should ALWAYS be zero!
System and Trades

----------------
TotalTrades
Total Number of Trades
returns the total number of trades entered into the system during the given time period
System and Trades

----------------
TotalValue
Total Value of all Transactions Processed
returns the total absolute value of all transfers processed.
System and Trades

----------------
TotalValueGST
Total Value of all Goods and Services Traded
return the total value of all goods or services traded on the system. This differs from the previous calculation in that the value of goods transferred is only counted once, and no transaction fees are included.
System and Trades

----------------
AverageTrade
Average Value of all Trades
returns the average value of all non-fee transactions processed during the time period.
System and Trades

----------------
LargestTransaction
Largest Single Transaction Value
returns the value of the larges single transaction processed during the time period.
System and Trades

----------------
TotalTransactionFee
Transaction Fees Processed
returns the total transaction fees credited to the system account during the time period
System and Trades

----------------
ValueTopTen
Top Ten Traders by Amount
returns a variable containing the top ten traders by the absolute value of the goods traded
Accounts and Activity
$Start,$Seperator,$End
----------------
NumberTopTen
Top Ten Traders by Number of Trades
returns a variable containing the top ten traders by the total trades made during the time period
Accounts and Activity
$Start,$Seperator,$End
----------------
NumberAccounts
Number of Accounts
returns the number of accounts that existed during the time period
Accounts and Activity

----------------
ActiveNumber
Number of Active Accounts
returns the number of distinct accounts that registered at least one transaction during the time period
Accounts and Activity

----------------
ActiveTraders
Active Accounts
returns the Account Names and IDs of all accounts that registered a trade during the time period
Accounts and Activity
$Start,$End
----------------
InactiveNumber
Number of Inactive Accounts
returns the number of distinct accounts that did not have any transactions processed during the time period
Accounts and Activity

----------------
InactiveTraders
Inactive Accounts
returns the Account Names and IDs of all accounts that did not register a trade during the time period.
Accounts and Activity
$Start,$End
----------------
TradePartners
Most Trade Partners
returns a variable containing the ten account names/ids that had the most distinct trading partners, and the number of partners
Accounts and Activity
$Start,$Seperator,$End
----------------
NumberAds
Number of Active Ads
Returns the number of Advertisements active during the time period
Advertisements

----------------
AccountsAds
Number of Accounts with Ads
returns the number of Accounts with active advertisements during the time period
Advertisements

----------------
PercentAds
Advertising Sales Percentage
returns the percent of Accounts with active advertisements which recorded at least one transaction during the time period
Advertisements

----------------
PercentNoAds
No Advertising Sales Percentage
returns the percent of Accounts with no advertisements which recorded at least one transaction during the time period
Advertisements

----------------
+++End
*/


# TOTAL NUMBER OF TRADES

function TotalTrades ($BeginDate,$EndDate)
{
        $TLookup = mysql_query("SELECT COUNT(DISTINCT TransactionID) AS Total
                                 FROM transactions
                                 WHERE Description != 'Account Created'
                                 AND Description NOT LIKE 'Closure of Account%'
                                 AND Description != 'System Balance Correction'
                                 AND TradeDate BETWEEN '$BeginDate'
                                 AND  '$EndDate'");
        if(mysql_error() != '')
        {
                return('function failed');
                }
        $T = mysql_result($TLookup,0,'Total');
        return($T);
        }


# TOTAL VALUE OF TRANSFERS BETWEEN ACCOUNTS

function TotalValue($BeginDate,$EndDate)
{
        $VLookup = mysql_query("SELECT SUM(ABS(Amount)) as Value
                                FROM transactions
                                WHERE Description != 'Account Created'
                                AND Description NOT LIKE 'Closure of Account%'
                                AND Description != 'System Balance Correction'
                                AND TradeDate BETWEEN '$BeginDate'
                                AND  '$EndDate'");
        if(mysql_error() != '')
        {
                return('function failed');
                }
        $V = mysql_result($VLookup,0,'Value');
        return($V);
        }

# TOTAL VALUE OF GOODS AND SERVICES TRADED (NOT INCLUDING TRANSACTION FEES)

function TotalValueGST($BeginDate,$EndDate)
{
        $VLookup = mysql_query("SELECT SUM(Amount) as Value
                                FROM transactions
                                WHERE Description != 'Account Created'
                                AND Description NOT LIKE 'Closure of Account%'
                                AND Description != 'Transaction Fee'
                                AND Description != 'System Balance Correction'
                                AND TradeDate BETWEEN '$BeginDate'
                                AND '$EndDate'
                                AND Amount > 0");
        if(mysql_error() != '')
        {
                return('function failed');
                }
        $V = mysql_result($VLookup,0,'Value');
        return($V);
        }

# TOTAL TRANSACTION FEES CHARGED

function TotalTransactionFee($BeginDate,$EndDate)
{
        $VLookup = mysql_query("SELECT SUM(Amount) as Value
                                FROM transactions
                                WHERE Description = 'Transaction Fee'
                                AND TradeDate BETWEEN '$BeginDate'
                                AND '$EndDate'
                                AND Amount > 0");
        if(mysql_error() != '')
        {
                return('function failed');
                }
        $V = mysql_result($VLookup,0,'Value');
        return($V);
        }

# LARGEST SINGLE TRANSACTION

function LargestTransaction($BeginDate,$EndDate)
{
        $VLookup = mysql_query("SELECT Max(Amount) as Value
                                FROM transactions
                                WHERE Description != 'Account Created'
                                AND Description NOT LIKE 'Closure of Account%'
                                AND Description != 'System Balance Correction'
                                AND TradeDate BETWEEN '$BeginDate'
                                AND '$EndDate'");
        if(mysql_error() != '')
        {
                return('function failed');
                }
        $V = mysql_result($VLookup,0,'Value');
        return($V);
        }

# TOP TEN TRADERS BY AMOUNT

function ValueTopTen($BeginDate,$EndDate,$Start,$Seperator,$End)
{
        $TenLookup = mysql_query("SELECT transactions.AccountID, AccountName,SUM(ABS(Amount)) AS Total
                                   FROM transactions, account
                                   WHERE transactions.AccountID = account.AccountID
                                   AND Description NOT LIKE 'Closure of Account%'
                                   AND Description != 'System Balance Correction'
                                   AND TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                   GROUP BY AccountID
                                   ORDER BY Total DESC
                                   LIMIT 10");
        if(mysql_error() != '')
        {
                return(mysql_error());
                }
        while($ten=mysql_fetch_array($TenLookup))
        {
                print ("$Start$ten[AccountName] (Account $ten[AccountID])$Seperator$ten[Total]$End");
                }
        }

# TOP TEN TRADERS BY NUMBER OF TRADES

function NumberTopTen($BeginDate,$EndDate,$Start,$Seperator,$End)
{
        $TenLookup = mysql_query("SELECT transactions.AccountID, AccountName,Count(Amount) AS Total
                                   FROM transactions, account
                                   WHERE transactions.AccountID = account.AccountID
                                   AND TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                   AND Description != 'Transaction Fee'
                                   AND Description != 'System Balance Correction'
                                   GROUP BY AccountID
                                   ORDER BY Total DESC
                                   LIMIT 10");
        if(mysql_error() != '')
        {
                return(mysql_error());
                }
        $R = '';
        while($ten=mysql_fetch_array($TenLookup))
        {
                $R .= "$Start$ten[AccountName] (Account $ten[AccountID])$Seperator$ten[Total]$End\n";
                }
        return($R);
        }

# AVERAGE TRADE VALUE

function AverageTrade($BeginDate,$EndDate)
{
        $AverageLookup = mysql_query("SELECT ROUND(AVG(Amount),2) AS Average
                                      FROM transactions
                                      WHERE Description != 'Transaction Fee'
                                      AND Description != 'System Balance Correction'
                                      AND TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                      AND Amount > 0");
        if(mysql_error() != '')
        {
                return(mysql_error());
                }
        $Avg = mysql_result($AverageLookup,0,'Average');
        return($Avg);
        }

# TRADERS ACTIVE DURING TIME PERIOD

function ActiveTraders($BeginDate,$EndDate,$Start,$End)
{
        $TradersLookup=mysql_query("SELECT DISTINCT transactions.AccountID, account.AccountName
                                    FROM transactions, account
                                    WHERE transactions.AccountID = account.AccountID
                                    AND TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                    AND Description != 'System Mailing'");
        if(mysql_error()!='')
        {
               return(mysql_error());
               }
        $T = '';
        while($Traders = mysql_fetch_array($TradersLookup))
        {
               $T .= "$Start$Traders[AccountName] (Acct $Traders[AccountID])$End";
               }
        return($T);
        }

# TRADERS INACTIVE DURING TIME PERIOD

function InactiveTraders($BeginDate,$EndDate,$Start,$End)
{
        $ActiveLookup=mysql_query("SELECT DISTINCT AccountID
                                    FROM transactions
                                    WHERE TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                    AND Description != 'Account Created'
                                    AND Description != 'System Mailing'
                                    AND Description NOT LIKE 'Closure of Account%'");
        $list = '';
        while($Active=mysql_fetch_array($ActiveLookup))
        {
                $list .= "'$Active[AccountID]',";
                }
        $list = substr($list,0,-1);
        $InactiveLookup=mysql_query("SELECT AccountID,AccountName
                                      FROM account
                                      WHERE AccountID NOT IN ($list)
                                      AND AccountCreated < '$EndDate'
                                      AND AccountStatus != 'Closed'");
        if(mysql_error() != '')
        {
                return(mysql_error());
                }
       $R = '';
       while($Inactive = mysql_fetch_array($InactiveLookup))
       {
               $R .= "$Start$Inactive[AccountName] (Acct $Inactive[AccountID])$End\n";
               }
        return($R);
        }

# NUMBER OF TRADERS ACTIVE DURING TIME PERIOD

function ActiveNumber($BeginDate,$EndDate)
{
        $ActiveLookup = mysql_query("SELECT COUNT(DISTINCT AccountID) AS Active
                                      FROM transactions
                                      WHERE TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                      AND Description != 'System Mailing'
                                      AND Description NOT LIKE 'Closure of Account%'");
        if(mysql_error() != '')
        {
                return(mysql_error());
                }
        $R = mysql_result($ActiveLookup,0,'Active');
        return($R);
        }

# NUMBER OF TRADERS INACTIVE DURING TIME PERIOD

function InactiveNumber($BeginDate,$EndDate)
{
        $InactiveLookup = mysql_query("SELECT ((COUNT(DISTINCT account.AccountID))-(COUNT(DISTINCT transactions.AccountID))) AS Inactive
                                       FROM transactions,account
                                       WHERE TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                       AND Description != 'System Mailing'
                                       AND Description NOT LIKE 'Closure of Account%'
                                       AND AccountCreated < '$EndDate'
                                       AND AccountRenewalDate > '$BeginDate'");
        if(mysql_error()!= '')
        {
                return(mysql_error());
                }
        $R = mysql_result($InactiveLookup,0,'Inactive');
        return($R);
        }

# TOP TEN WITH MOST DISTINCT TRADING PARTNERS

function TradePartners($BeginDate,$EndDate,$Start,$Seperator,$End)
{
        $PartnersLookup = mysql_query("SELECT account.AccountName,transactions.AccountID, COUNT(DISTINCT OtherAccountID) AS Partners
                                        FROM account,transactions
                                        WHERE account.AccountID = transactions.AccountID
                                        AND transactions.Description != 'Account Created'
                                        AND transactions.Description != 'Transaction Fee'
                                        AND transactions.Description != 'System Mailing'
                                        AND transactions.Description NOT LIKE 'Closure of Account%'
                                        AND TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                        GROUP BY AccountID
                                        ORDER BY Partners DESC
                                        LIMIT 10");
        if(mysql_error() != '')
        {
                $error = mysql_error();
                $R = $Start . $error . $End;
                }
        else
        {
                $R = '';
                while($Partners = mysql_fetch_array($PartnersLookup))
                {
                        $R .= "$Start$Partners[AccountName] (Acct $Partners[AccountID])$Seperator$Partners[Partners]$End\n";
                        }
                return($R);
                }
        }

# NUMBER OF ADVERTISEMENTS ACTIVE DURING TIME PERIOD

function NumberAds($BeginDate,$EndDate)
{
        $AdsLookup = mysql_query("SELECT COUNT(AdID) AS Ads
                                  FROM advertisements, account
                                  WHERE account.AccountID = advertisements.AccountID
                                  AND AdBeginDate <= '$EndDate'
                                  AND AdExpiryDate >= '$BeginDate'
                                  AND AccountRenewalDate >= '$BeginDate'");
        if(mysql_error() != '')
        {
                $R = mysql_error();
                }
        else
        {
                $R = mysql_result($AdsLookup,0,'Ads');
                }
        return($R);
        }

# NUMBER OF ACCOUNTS WITH ADS

function AccountsAds($BeginDate,$EndDate)
{
        $AccountsLookup = mysql_query("SELECT COUNT(DISTINCT AccountID) AS Accounts
                                       FROM advertisements
                                       WHERE AdBeginDate < '$EndDate'
                                       AND AdExpiryDate > '$BeginDate'");
        if(mysql_error() != '')
        {
                $R = mysql_error();
                }
        else
        {
                $R = mysql_result($AccountsLookup,0,'Accounts');
                }
        return($R);
        }

# PERCENTAGE OF ACCOUNTS WITH ADS THAT REGISTERED A TRADE

function PercentAds($BeginDate,$EndDate)
{
        $AccountsLookup = mysql_query("SELECT DISTINCT AccountID
                                       FROM advertisements
                                       WHERE AdBeginDate < '$EndDate'
                                       AND AdExpiryDate > '$BeginDate'");
        $A = '';
        while($Accounts = mysql_fetch_array($AccountsLookup))
        {
                $A .= "$Accounts[AccountID],";
                }
        $A = substr($A,0,-1);
        $SalesLookup = mysql_query("SELECT COUNT(DISTINCT AccountID) AS Sales
                                    FROM transactions
                                    WHERE TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                    AND Description != 'Account Created'
                                    AND AccountID IN ($A)");
        if(mysql_error() != '')
        {
                $R = 'Function failed';
                }
        else
        {
                $Sales = mysql_result($SalesLookup,0,'Sales');
                $Total = AccountsAds("$BeginDate","$EndDate");
                $Percent = $Sales / $Total * 100;
                $R = round($Percent);
                }
        return($R);
        }

# PERCENTAGE OF ACCOUTNS WITHOUT ADS REGISTERING A TRADE

function PercentNoAds($BeginDate,$EndDate)
{
        $AdsLookup = mysql_query("SELECT DISTINCT AccountID
                                       FROM advertisements
                                       WHERE AdBeginDate < '$EndDate'
                                       AND AdExpiryDate > '$BeginDate'");
        $A = '';
        while($Accounts = mysql_fetch_array($AdsLookup))
        {
                $A .= "$Accounts[AccountID],";
                }
        $A = substr($A,0,-1);
        $NoAdsLookup = mysql_query("SELECT AccountID
                                    FROM account
                                    WHERE AccountCreated < '$EndDate'
                                    AND AccountRenewalDate > '$EndDate'
                                    AND AccountID NOT IN ($A)");
        $A = '';
        while($Accounts = mysql_fetch_array($NoAdsLookup))
        {
                $A .= "$Accounts[AccountID],";
                }
        $A = substr($A,0,-1);
        $SalesLookup = mysql_query("SELECT COUNT(DISTINCT AccountID) AS Sales
                                    FROM transactions
                                    WHERE TradeDate BETWEEN '$BeginDate' AND '$EndDate'
                                    AND Description != 'Account Created'
                                    AND AccountID IN ($A)");
        if(mysql_error() != '')
        {
                $R = 'Function failed';
                }
        else
        {
                $Sales = mysql_result($SalesLookup,0,'Sales');
                $Total = (NumberAccounts("$BeginDate","$EndDate")) - (AccountsAds("$BeginDate","$EndDate"));
                $Percent = $Sales / $Total * 100;
                $R = round($Percent);
                }
        return($R);
        }

# NUMBER OF ACTIVE ACCOUNTS

function NumberAccounts($BeginDate,$EndDate)
{
        $AccountsLookup = mysql_query("SELECT COUNT(AccountID) AS Accounts
                                       FROM account
                                       WHERE AccountCreated < '$EndDate'
                                       AND AccountRenewalDate > '$BeginDate'");
        if(mysql_error() != '')
        {
                $R = 'Function failed';
                }
        else
        {
                $R = mysql_result($AccountsLookup,0,'Accounts');
                }
        return($R);
        }

# SYSTEM BALANCE

function SystemBalance()
{
        $Balance = mysql_query("SELECT SUM(Amount) AS Balance
                                from transactions");
        if(mysql_error() != '')
        {
                $R = 'Function failed';
                }
        else
        {
                $R = mysql_result($Balance,0,'Balance');
                }
        return($R);
        }

?>