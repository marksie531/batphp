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
$selected = mysql_select_db ('grantsni', $dbh) or die( "Unable to select database");

echo '
<a href="example5.php"><button><img src="images/bat/refresh.gif"/> Refresh</button></a>
<a href="example5.php?bat=new"><button><img src="images/bat/add.png"/> New</button></a>
<a href="example5.php?bat=excel"><button><img src="images/bat/excel.png"/> Export (Excel)</button></a><br/><br/>';

$listSql = "
select
	st.scheme_id  as scheme_id,
	st.topic_id   as topic_id,
	t.description as topic_desc
from
	scheme_topic st,
	topic t
where
	st.topic_id = t.id";
$countSql = "select count(1) from scheme_topic st, topic t where st.topic_id = t.id";

// Create query
$batDef = array (
	'_action' => 'example5.php',
	'_db_table' => 'scheme_topic',
	'_db_list_sql' => $listSql,
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => false, '_can_pdf' => false, '_can_print' => false,
	'_list_delete' => 1,
	'_default_sort' => 0, '_default_asc' => 1,
	'_debug_sql' => true,
	'_cols' => array (
		array ('_lb' => 'Scheme', '_pk' => 'scheme_id',  '_fl' => 'LSENF', '_in' => 'select|,-|select name from scheme order by name', '_v' => 'E'),
		array ('_lb' => 'Topic',  '_cl' => 'topic_desc', '_fl' => 'LS', '_in' => 'select|,-|select id,description from topic order by description', '_v' => 'E'),
		array ('_lb' => 'Topic',  '_pk' => 'topic_id',   '_fl' => 'EN', '_in' => 'select|,-|select id,description from topic order by description', '_v' => 'E'),
	),
	'_pagination' => array (
		'_rows_per_page' => 25,
	    '_row_counts' => array (10, 25, 100, 500),
	    '_db_count_sql' => $countSql
	)
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>