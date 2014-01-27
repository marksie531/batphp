<html>
<head>
	<title>Table Test</title>
	<link media="screen" type="text/css" href="css/bat.css" rel="stylesheet">
</head>
<body>
<h1>Table Test</h1>

<p>
<a href="example1.php"><button><img src="images/bat/refresh.gif"/> Refresh</button></a>
<a href="example1.php?bat=new"><button><img src="images/bat/add.png"/> New</button></a>
<a href="example1.php?bat=export"><button><img src="images/bat/export.png"/> Export</button></a>
<a href="example1.php?bat=import"><button><img src="images/bat/import.png"/> Import</button></a>
</p>

<style>
table.list_table tbody td.beds_info { text-align: left; }
table.list_table tbody td.desc      { text-align: left; }

table.edit_table .beds_info { width: 400px; }
table.edit_table .desc      { width: 400px; height: 50px; }
</style>

<?php
include "bat.php";

// Connect to database
$dbh = mysql_connect ('localhost', 'admin', 'admin');
$selected = mysql_select_db ('bcm', $dbh) or die( "Unable to select database");

/*
[ Flags ]
L=Show is list mode,
S=Sortable (list mode only),
E=Show in edit mode,
R=Show as read-only (edit mode only),
N=Show in New mode,
G=Generated
*/
$batDef = array (
	'_action' => 'example1.php',
	'_db_table' => 'room',
	'_can_edit' => true, '_can_delete' => true,	'_can_export' => false, '_can_pdf' => false, '_can_print' => false,
	'_list_delete' => 1,
	'_list_id' => 'rooms',
	'_edit_id' => 'room',
	'_default_sort' => 0, '_default_asc' => 1,
	'_debug_sql' => false,
	'_cols' => array (
		array ('_lb' => 'ID',          '_pk' => 'id',          '_fl' => 'LSENX', '_in' => 'text'),
		array ('_lb' => 'Name',        '_cl' => 'name',        '_fl' => 'LSENX', '_in' => 'text'),
		array ('_lb' => 'Sleeps',      '_cl' => 'sleeps',      '_fl' => 'LSENX', '_in' => 'select|0..30..1'),
		array ('_lb' => 'Beds Info',   '_cl' => 'beds',        '_fl' => 'LSENX', '_in' => 'text', '_class' => 'beds_info'),
		array ('_lb' => 'Description', '_cl' => 'description', '_fl' => 'LSENX', '_in' => 'textarea', '_class' => 'desc'),

		array ('_lb' => 'Text',        '_cl' => '',            '_fl' => 'F',    '_in' => 'text', '_class' => 'desc', '_filter_sql' => "(description like '%{value}%' or beds like '%{value}%')"),
		array ('_lb' => 'Sleeps',      '_cl' => 'sleeps',      '_fl' => 'F',    '_in' => 'select|,All|0..30..1'),
        array ('_lb' => 'to',          '_cl' => 'sleeps',      '_fl' => 'F',    '_in' => 'select|,All|0..30..1', '_filter_sql' => "SLEEPS < {value}"),
	),
	'_pagination' => array (
		'_rows_per_page' => 25,
	    '_row_counts' => array (2, 25, 100, 500)
	)
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>