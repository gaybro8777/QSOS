<html>
<head>
<meta charset="UTF-8">
</head>
<body>
<?php
// disable error
error_reporting(0);
//Based on this script: http://programmabilities.com/php/?id=2
require_once("database_pdo.php");
require_once("CharacteresC.php");
session_start();
session_unset();
session_destroy();
$_SESSION = array();

$searchstr = $_REQUEST['s'];

include("config.php");
include("lang.php");

echo "<html>\n";
echo "<head>\n";
echo "<LINK REL=StyleSheet HREF='skins/".$skin."/o3s.css' TYPE='text/css'/>\n";
echo "</head>\n";

echo "<body>\n";
echo "<center>\n";
echo "<img src='skins/".$skin."/o3s-".$git.".png'/>\n";
echo "<br/><br/>\n";

echo "<p><form action=".$_SERVER['PHP_SELF']." method='post'>\n";
echo "	<input type='text' name='s' value=".$searchstr." size='20' maxlength='30'/>\n";
echo "	<input type='submit' value='".$msg['s1_button_search']."'/><br/><br/>\n";
echo "	<input type='button' value='".$msg['s1_button_back']."' onclick=\"location.href='index.php?lang=".$lang."'\"/>\n";
echo "</form></p>\n";

echo "</center>\n";

if (! empty($searchstr)) {
     // empty() is used to check if we've any search string.
     // If we do, call grep and display the results.
     echo '<hr/>';
     // Call grep with case-insensitive search mode on all files
     /**/   
     $cmdstr = "grep -i -l $searchstr $repo/*.qsos";
     $fp = popen($cmdstr, 'r'); // open the output of command as a pipe
     $myresult = array(); // to hold my search results
     $objectConnect = new Connexion("pgsql"); // instance of Database Class
     while ($buffer = fgetss($fp, 4096)) {
        // add isset verification  
	if ( isset($buffer)){
	// grep returns in the format
          // filename: line
          // So, we use explode() to split the data
          list($fname, $fline) = explode(':', $buffer, 2);
          // we take only the first hit per file
          if (! defined($myresult[$fname])) {
              $myresult[$fname] = $fline;
          }
      }
     }
      // we have results in a hash. lets walk through it and print it
      if (count($myresult)) {
           echo '<ul><br/>';
           while (list($fname, $fline) = each ($myresult)) {
		$name = trim(basename($fname, ".qsos"));
 		$query = "SELECT id FROM evaluations WHERE file= :name  and language= :lang";
		$arr = array(
		  ":name" => $name,
		  ":lang"=> $lang 
		);
		$array_res = $objectConnect->select($query,$arr);
		if(count($array_res)==0){
		  /*
		 Create the log
		  */
		  die("error on your query ");
		  $id = $array_res[0]["id"];
		  echo "<li><a href='show.php?lang=".$lang."&svg=yes&s=".$searchstr."&id[]=".$id."'>".$name."</a></li>\n";
		
           }
           echo '</ul><br/>';
       } else { 
            // no hits
            echo $msg['s1_search_msg1']."<strong>".$searchstr."</strong>".$msg['s1_search_msg2']."<br/>\n";
       }
       pclose($fp);
   }

echo "</body>\n";
echo "</html>\n";
?>
</body>
</html>
