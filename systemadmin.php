<?



/*



This is the system configuration menu page.



*/



include "configuration.php";

include "connectdb.php";

include "adminlogin.php";



$title = "System Configuration Menu";

include "header.php";



print ("<table class=Menu>

        <tr>

        <td class=Menu><a href=infopages.php class=Menu>\"About $Systemname\" Administration</a></td>

        </tr>

        <tr>

        <td class=Menu><a href=bulletins.php class=Menu>User Bulletins</a></td>

        </tr>

        <tr>

        <td class=Menu><a href=constants.php class=Menu>System Constants</a></td>

        </tr>

        <tr>

        <td class=Menu><a href=accountoptions.php class=Menu>Account Type Setup</a></td>

        </tr>

        <tr>

        <td class=Menu><a href=adcategories.php class=Menu>Advertisement Category Setup</a></td>

        </tr>

        </table>\n");



include "footer.php";

?>