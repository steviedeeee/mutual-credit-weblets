<?php

/*

This is the member administration menu page.

*/

include "configuration.php";
include "connectdb.php";
include "adminlogin.php";

$title = "System Configuration Menu";
include "header.php";

print ('<table class="Menu">
        <tr>
        <td class="Menu"><a href="newuserdetail_form.php" class="Menu">Add a New Member</a></td>
        </tr>
        <tr>
        <td class="Menu"><a href="modifyuserdetail_form.php" class="Menu">Edit an Existing Member</a></td>
        </tr>
        <tr>
        <td class="Menu"><a href="newaccount_form.php" class="Menu">Add a New Account</a></td>
        </tr>
        <tr>
        <td class="Menu"><a href="modifyaccount_form.php" class="Menu">Edit an Existing Account</a></td>
        </tr>
        <tr>
        <td class="Menu"><a href="accountrenewal.php" class="Menu">Renew an Account</a></td>
        </tr>
        <tr>
        <td class="Menu"><a href="closeaccount.php" class="Menu">Close an Account</a></td>
        </tr>
        </table>
	');

include "footer.php";
?>
