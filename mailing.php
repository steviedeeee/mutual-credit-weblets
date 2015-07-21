<?
/******************************************************************************/
/*
                Written by:        Martin Settle
                Last Modified:        January 25, 2002
                Called by:        header.php
                Calls:                configuration.php
                                connectdb.php
                                header.php
                                footer.php
                Description:        presents the form to create a mailing to
                                all LETS members


                Modification History:
                                January 25, 2002 - File Created

*/
/******************************************************************************/

/* get the includes out of the way */

include "configuration.php";
include "connectdb.php";

/* make sure the user is admin authorized */

include "adminlogin.php";

/* now print the page */

$title = "Membership Mailing System";
include "header.php";

print ("<h1>Mailing System</h1>
        The mailing system will create and process an electronic mail message, with attachments, according to the data submitted in the form below.  A message will be sent to all members who have requested contact through e-mail, unless the \"Send to all registered e-mails\" checkbox is selected.  After all messages have been sent, a text file containing the mailing addresses of all account contacts <strong>not</strong> receiving the e-mail mailing will be printed and stored as \"addresses.txt\".<p>
        <strong>All accounts will be charged the default mailing fee</strong> unless the <em>No Fee Charged</em> checkbox is selected.<p>
        To avoid a network timeout, much of the processing of the mail message will occur <em>after</em> the confirmation page is printed.  If you are using a javascript enabled browser, a pop-up window will appear to monitor the progress of the mail process.  If this box does not appear automatically, initiate it manually by selecting the \"monitor progress\" link on the confirmation page.<p>
        <hr>
        <p>
        <form action=processmailing.php method=POST enctype=\"multipart/form-data\">
        <table noborder width=100%>
        <tr><th colspan=2 class=Banner>Mailing Information</th></tr>
        <tr><th class=FormLabel colspan=2><input type=checkbox name=NoFee> No Fee Charged</th></tr>
        <tr><th class=FormLabel colspan=2><input type=checkbox name=Everyone> Send to all registered e-mails</tr>
        <tr><th class=FormLabel colspan=2><input type=checkbox name=AccountStatement default=checked> Include Account Statement</th></tr>
        <tr><th class=FormLabel colspan=2><input type=checkbox name=Directory default=checked> Include Advertisment Directory (incomplete)</th></tr>
        <tr><th class=FormLabel colspan=2><input type=checkbox name=MemberList default=checked> Include Membership List</th></tr>
        <tr><th class=FormLabel>Priority:</th><td><select name=Priority><option><option></select></td></tr>
        <tr><th class=FormLabel>Subject:</th><td><input type=text name=Subject size=40></td></tr>
        <tr><th class=FormLabel valign=top>Message Body:</th><td><textarea name=Text cols=60 rows=10></textarea></td></tr>
        <tr><th class=FormLabel valign=top>Attached Files:</th><td><input type=file name=Attachment[] size=30><br>
        <input type=file name=Attachment[] size=30><br>
        <input type=file name=Attachment[] size=30><br>
        <input type=file name=Attachment[] size=30><br>
        <input type=file name=Attachment[] size=30></td></tr>
        <tr><th colspan=2 class=Banner>&nbsp;</th></tr>
        <tr><td colspan=2><center><input type=submit value='Send Mailing'></center></td><tr>
        <tr><th colspan=2 class=Banner>&nbsp;</th></tr>
        </table>
        </form>\n");
include "footer.php";
exit();
?>