<?php

/* connect to database */
require 'connectdb.php';
$viewGame = 0;
if (isset($_SESSION['ViewGame'])) {
	$viewGame = $_SESSION['ViewGame'];
}
$f=mysql_query("select gameID, p1.nick as white, p2.nick as black, lastMove from (games inner join players as p1 on whitePlayer=p1.playerID) inner join players p2 on blackPlayer=p2.playerID where gameID <=".$viewGame." and dateCreated != lastMove order by gameID DESC limit 0,1");


$c=0;
$d=0;

while($row=mysql_fetch_array($f, MYSQL_ASSOC)){
	$_SESSION['ViewGame']=$row['gameID'];
	echo "Game ".$row['gameID'].": White ".$row['white']." versus black ".$row['black']." ".$row['lastMove'];
}
?>
