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
<a href="example1.php?bat=excel"><button><img src="images/bat/excel.png"/> Export (Excel)</button></a>
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
	'_action' => 'example1.php',
	'_db_table' => 'ROOM',
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => false, '_can_pdf' => false, '_can_print' => false,
	'_list_delete' => 1,
	'_list_id' => 'rooms',
	'_edit_id' => 'room',
	'_default_sort' => 0, '_default_asc' => 1,
	'_debug_sql' => true,
	'_cols' => array (
		0 => array ('_lb' => 'ID',          '_pkc' => 'ID',          '_flags' => 'LSRN', '_input' => 'text'),
		1 => array ('_lb' => 'Name',        '_col' => 'NAME',        '_flags' => 'LSEN', '_input' => 'text'),
		2 => array ('_lb' => 'Min.Sl',      '_col' => 'MIN_SLEEPS',  '_flags' => 'LSEN', '_input' => 'combo_numbers|0|30|1'),
		3 => array ('_lb' => 'Sleeps',      '_col' => 'SLEEPS',      '_flags' => 'LSEN', '_input' => 'combo_numbers|0|30|1'),
		4 => array ('_lb' => 'Beds Info',   '_col' => 'BEDS',        '_flags' => 'LSEN', '_input' => 'text', '_class' => 'beds_info'),
		5 => array ('_lb' => 'Description', '_col' => 'DESCRIPTION', '_flags' => 'LSEN', '_input' => 'textarea', '_class' => 'desc'),
	),
	'_validation' => array (
	    0 => array ('_flags' => 'E', '_msg' => 'Please specify text for ID field'),
	    1 => array ('_flags' => 'E', '_msg' => 'Please specify text for Name field'),
	    2 => array ('_equals' => '0', '_msg' => 'Please specify a non-zero value for the Min Sleeps field'),
	    3 => array ('_equals' => '0', '_msg' => 'Please specify a non-zero value for the Sleeps field'),
	    4 => array ('_flags' => 'E', '_msg' => 'Please specify text for Beds field'),
	    5 => array ('_flags' => 'E', '_msg' => 'Please specify text for Description field'),
	),
	'_pagination' => array (
		'_rows_per_page' => 25,
	    '_row_counts' => array (2, 10, 25, 100, 500)
	),
	'_filters' => array (

	),
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>