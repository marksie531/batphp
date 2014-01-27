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
$listSql = "select r.id          id,
                   r.name        name,
                   r.beds        beds,
                   r.typeid      typeid,
                   r.sleeps      sleeps,
                   r.min_sleeps  min_sleeps,
                   t.description type_desc,
                   r.rate_type   rate_type,
                   r.description description
              from room r, type t
             where t.id = r.typeid";

$countSql = "count(1) from room r, type t where t.id = r.typeid";

// [ Flags ] L=Show is list view, S=Sortable (list view only), E=Show in edit view, R=Show as read-only (edit view only), N=New
$batDef = array (
	'_action' => 'example2.php',
	'_db_table' => 'room',
	'_db_list_sql' => $listSql,
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => true, '_can_pdf' => true, '_can_print' => true,
	'_list_delete' => 1,
	'_list_id' => 'rooms',
	'_edit_id' => 'room',
	'_default_sort' => 0, '_default_asc' => 1,
	'_debug_sql' => false,
	'_cols' => array (
		array ('_lb' => 'ID',          '_pk' => 'id',          '_fl' => 'LSRN', '_in' => 'text', '_v' => 'E'),
		array ('_lb' => 'Name',        '_cl' => 'name',        '_fl' => 'LSEN', '_in' => 'text', '_v' => 'E'),
		array ('_lb' => 'Type',        '_cl' => 'type_desc',   '_fl' => 'LS'),
		array ('_lb' => 'Type',        '_cl' => 'typeid',      '_fl' => 'ENF',  '_in' => 'select|,-|select id,description from type', '_v_eq' => ''),
		array ('_lb' => 'Min.Sl',      '_cl' => 'min_sleeps',  '_fl' => 'LSEN', '_in' => 'select|-1,-|0..30..1', '_v_eq' => '-1'),
		array ('_lb' => 'Sleeps',      '_cl' => 'sleeps',      '_fl' => 'LSEN', '_in' => 'select|-1,-|0..30..1', '_v_eq' => '-1'),
		array ('_lb' => 'Beds Info',   '_cl' => 'beds',        '_fl' => 'LSEN', '_in' => 'text', '_v' => 'E', '_class' => 'beds_info'),
		array ('_lb' => 'Description', '_cl' => 'description', '_fl' => 'LSEN', '_in' => 'textarea', '_v' => 'E', '_class' => 'desc'),
	),
	'_pagination' => array (
		'_rows_per_page' => 25,
	    '_row_counts' => array (2, 10, 25, 100, 500),
	    '_db_count_sql' => $countSql,
	)
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>