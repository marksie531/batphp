<html>
<head>
	<title>Table Test</title>
	<link media="screen" type="text/css" href="css/bat.css" rel="stylesheet">
</head>
<body>
<h1>Table Test</h1>

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

echo '
<a href="example4.php"><button><img src="images/bat/refresh.gif"/> Refresh</button></a>
<a href="example4.php?bat=new"><button><img src="images/bat/add.png"/> New</button></a>
<a href="example4.php?bat=excel"><button><img src="images/bat/excel.png"/> Export (Excel)</button></a><br/><br/>';

// Create query

// [ _flags ] L=Show is list view, S=Sortable (list view only), E=Show in edit view, R=Show as read-only (edit view only), N=New
// [ _val ]   E=Cannot be empty,

$batDef = array (
	'_action' => 'example4.php',
	'_db_table' => 'TYPE',
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => false, '_can_pdf' => false, '_can_print' => false,
	'_list_delete' => 1,
	'_default_sort' => 0, '_default_asc' => 1,
	'_debug_sql' => false,
	'_cols' => array (
		0 =>  array ('_lb' => 'Id',          '_pk' => 'ID',          '_fl' => 'LSEN', '_in' => 'text',     '_v' => 'E', '_v_fk' => array ('ROOM.TYPEID')),
		1 =>  array ('_lb' => 'Description', '_cl' => 'DESCRIPTION', '_fl' => 'LSEN', '_in' => 'textarea', '_v' => 'E'),
	),
	'_pagination' => array (
		'_rows_per_page' => 25,
	    '_row_counts' => array (2, 10, 25, 100, 500)
	)
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>