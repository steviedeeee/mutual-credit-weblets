<?
include "configuration.php";
if(!mysql_connect("$host","$user","$password"))
{
      include "header.php";
      ?>ERROR CONNECTING TO SERVER<?
      include "footer.php";
      exit();
  }
if(!mysql_select_db("$database"))
{
      include "header.php";
      ?>ERROR CONNECTING TO DATABASE<?
      include "footer.php";
      exit();
  }
?>
