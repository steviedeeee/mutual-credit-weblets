<?

/******************************************************************************/
/*
                Written by:     Martin Settle
                Last Modified:  January 13, 2002
                Called by:      header.php
                Calls:          connectdb.php
                Description:    This file is simply a form that allows the user
                                to request options in a system report

                Modification History:
                                January 17, 2002 - file created

*/
/******************************************************************************/

/* Here are all the includes */

include 'configuration.php';
include 'connectdb.php';

include 'adminlogin.php';
error_reporting(E_ALL ^ E_NOTICE);
/* If no date has been specified, assume the request is for the previous
month */

if(empty($BeginDate))
{
        if(date("m") < 2)
        {
                $BeginDate = (date('Y') - 1) . '-12-01';
                }
        else
        {
                $BeginDate = date("Y-") . (date('m')-1) . '-01';
                }
        }

/* Now calculate the EndDate (if not specified) as the last day of the previous
month */

if(empty($EndDate))
{
        $Month = substr($BeginDate,5,-3);
        $Day = substr($BeginDate,-2,2);
        $Year = substr($BeginDate,0,4);
        $EndDate = date("Y-m-d",mktime(0,0,0,$Month+1,$Day-1,$Year));
        }

/* Now start the page, and give directions */

$title = 'System Report Request';
include 'header.php';

print ("<h2>$Systemname Summary Reports</h2>
        This system will generate reports on certain aspects of the LETS System.<p>
        Please begin by selecting the time period for which you wish to generate a report.  Then, select as many of the specific calculations as you wish to have displayed on your report.
        <p><strong>Note: </strong>Each calculation below takes a small amount of time to process.  While these timeframes are relatively insignificant, processing many or all of them for a single report will greatly increase the time it takes to generate the final report, particularly with large databases.  Be prepared to wait!<p>
        <hr><p>
        <form action=showreports.php method=POST>
        <table noborder width=100%>
        <tr><th colspan=2 class=Banner>Reporting Period</th></tr>
        <tr>
        <td><table noborder><tr><th class=FormLabel valign=top>Begin Date:</th><td><input type=text name=BeginDate size=10 value='$BeginDate'><br>yyyy-mm-dd</td></tr></table></td>
        <td><table noborder><tr><th class=FormLabel valign=top>End Date:</th><td><input tyupe=text name=EndDate size=10 value='$EndDate'><br>yyyy-mm-dd</td></tr></table></td></tr>
        </table><p>\n");

/* Look up the available functions from the functions.inc file */
$functions = fopen("reports.inc",'r');

if (!$functions) {
        /* need to call the proper error dumping routine... -keb */
        echo "failed to open reports.inc for reading\n";
        exit;
        }

$line = '';
while(substr($line,0,3) != "+++")
{
        $line = fgets($functions,4096);
        }
$func = '';
$count=0;
while(substr($func,0,3) != "+++")
{
        $func=fgets($functions,4096);
        if(substr($func,0,3) != "+++")
        {
                $Option["$count"] = $func;
                $Title["$count"] = fgets($functions,4096);
                $Description["$count"] = fgets($functions,4096);
                $Category["$count"] = fgets($functions,4096);
                fgets($functions,4096);
                fgets($functions,4096);
                $count++;
                }
        }
fclose($functions);

print ("<table noborder>");
$CurrentCategory = '';

for($i=0;$i<$count;$i++)
{
        if($Category["$i"] != $CurrentCategory)
        {
                print ("<tr><th colspan=2 class=Banner>$Category[$i]</th></tr>");
                $CurrentCategory =  $Category["$i"];
                }
        print ("<tr><th valign=top><input type=checkbox name=$Option[$i]></th>
                <td><strong>$Title[$i]</strong><br>$Description[$i]</td></tr>");
        }

print ("<tr><th colspan=2 class=Banner>&nbsp;</th></tr>
        <tr><td colspan=2><center><input type=submit value='Show Report'></center></td></tr>
        </table>");

include 'footer.php';
?>