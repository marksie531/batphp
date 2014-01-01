<html>
<head>
	<title>Table Test</title>
	<link media="screen" type="text/css" href="css/bat.css" rel="stylesheet">
</head>
<body>
<h1>Table Test</h1>

<p>
<a href="example2.php"><button><img src="images/bat/refresh.gif"/> Refresh</button></a>
<a href="example2.php?bat=new"><button><img src="images/bat/add.png"/> New</button></a>
<a href="example2.php?bat=excel"><button><img src="images/bat/excel.png"/> Export (Excel)</button></a>
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

// Create query
$listSql = "SELECT R.ID          ID,
                   R.NAME        NAME,
                   R.BEDS        BEDS,
                   R.TYPEID      TYPEID,
                   R.SLEEPS      SLEEPS,
                   R.MIN_SLEEPS  MIN_SLEEPS,
                   T.DESCRIPTION TYPE_DESC,
                   R.RATE_TYPE   RATE_TYPE,
                   R.DESCRIPTION DESCRIPTION
              FROM ROOM R, TYPE T
             WHERE T.ID = R.TYPEID";

$countSql = "COUNT (1) FROM ROOM R, TYPE T WHERE T.ID = R.TYPEID";

// [ Flags ] L=Show is list view, S=Sortable (list view only), E=Show in edit view, R=Show as read-only (edit view only), N=New
$batDef = array (
	'_action' => 'example2.php',
	'_db_table' => 'ROOM',
	'_db_list_sql' => $listSql,
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => true, '_can_pdf' => true, '_can_print' => true,
	'_list_delete' => 1,
	'_list_id' => 'rooms',
	'_edit_id' => 'room',
	'_default_sort' => 0, '_default_asc' => 1,
	'_debug_sql' => false,
	'_cols' => array (
		array ('_lb' => 'ID',          '_pk' => 'ID',          '_fl' => 'LSRN', '_in' => 'text', '_v' => 'E'),
		array ('_lb' => 'Name',        '_cl' => 'NAME',        '_fl' => 'LSEN', '_in' => 'text', '_v' => 'E'),
		array ('_lb' => 'Type',        '_cl' => 'TYPE_DESC',   '_fl' => 'LS'),
		array ('_lb' => 'Type',        '_cl' => 'TYPEID',      '_fl' => 'ENF',  '_in' => 'select|,-|SELECT ID,DESCRIPTION FROM TYPE', '_v_eq' => ''),
		array ('_lb' => 'Min.Sl',      '_cl' => 'MIN_SLEEPS',  '_fl' => 'LSEN', '_in' => 'select|-1,-|0..30..1', '_v_eq' => '-1'),
		array ('_lb' => 'Sleeps',      '_cl' => 'SLEEPS',      '_fl' => 'LSEN', '_in' => 'select|-1,-|0..30..1', '_v_eq' => '-1'),
		array ('_lb' => 'Beds Info',   '_cl' => 'BEDS',        '_fl' => 'LSEN', '_in' => 'text', '_v' => 'E', '_class' => 'beds_info'),
		array ('_lb' => 'Description', '_cl' => 'DESCRIPTION', '_fl' => 'LSEN', '_in' => 'textarea', '_v' => 'E', '_class' => 'desc'),
	),
	'_pagination' => array (
		'_rows_per_page' => 25,
	    '_row_counts' => array (2, 10, 25, 100, 500),
	    '_db_count_sql' => 'SELECT COUNT(1) FROM ROOM R, TYPE T WHERE T.ID = R.TYPEID',
	)
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>