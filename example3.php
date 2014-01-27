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
<hr/>

<?php
include "bat.php";

// Connect to database
$dbh = mysql_connect ('localhost', 'admin', 'admin');
$selected = mysql_select_db ('grantsni', $dbh) or die( "Unable to select database");

echo '
<a href="example3.php"><button><img src="images/bat/refresh.gif"/> Refresh</button></a>
<a href="example3.php?bat=new"><button><img src="images/bat/add.png"/> New</button></a>
<a href="example3.php?bat=excel"><button><img src="images/bat/excel.png"/> Export (Excel)</button></a><br/><br/>';

// Create query

// [ _flags ] L=Show is list view, S=Sortable (list view only), E=Show in edit view, R=Show as read-only (edit view only), N=New
// [ _val ]   E=Cannot be empty,

$batDef = array (
	'_action' => 'example3.php',
	'_db_table' => 'scheme',
	// '_db_list_sql' => $listSql,
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => false, '_can_pdf' => false, '_can_print' => false,
	'_list_delete' => 'name',
	'_default_sort' => 0, '_default_asc' => 1,
	'_debug_sql' => true,
	'_cols' => array (
		array ('_lb' => 'Name',                  '_pk' => 'name',                   '_fl' => 'LSRN', '_in' => 'text', '_v' => 'E'),
		array ('_lb' => 'Summary',               '_cl' => 'summary',                '_fl' => 'EN',   '_in' => 'textarea', '_v' => 'E', '_class' => 'summary'),
		array ('_lb' => 'Description',           '_cl' => 'description',            '_fl' => 'EN',   '_in' => 'textarea', '_v' => 'E', '_class' => 'description'),
		array ('_lb' => 'Exclusions',            '_cl' => 'exclusions',             '_fl' => 'EN',   '_in' => 'text', '_v' => 'E'),
		array ('_lb' => 'How To Apply',          '_cl' => 'howtoapply_id',          '_fl' => 'LSEN', '_in' => 'select|-1,-|select id, description from how_to_apply', '_v_eq' => '-1'),
		array ('_lb' => 'Grants Given',          '_cl' => 'grants_given',           '_fl' => 'LSEN', '_in' => 'select|-1,-|0..30..1', '_v_eq' => '-1'),
		array ('_lb' => 'Year',                  '_cl' => 'year',                   '_fl' => 'LSEN', '_in' => 'select|-1,-|2010..2060..1', '_v_eq' => '-1'),
		array ('_lb' => 'Grant Range Id',        '_cl' => 'grant_range_id',         '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Contact Name',          '_cl' => 'contact_name',           '_fl' => 'LSEN', '_in' => 'text'),
		array ('_lb' => 'Contact Title',         '_cl' => 'contact_title',          '_fl' => 'EN',   '_in' => 'radio|Mr|Mrs|Miss|Dr|under_grad,Under Grad'),
		array ('_lb' => 'Contact Address 1',     '_cl' => 'contact_addresss1',      '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Contact Address 2',     '_cl' => 'contact_addresss2',      '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Contact Address 3',     '_cl' => 'contact_addresss3',      '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Contact City',          '_cl' => 'contact_city',           '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Contact Postcode',      '_cl' => 'contact_postcode',       '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Contact Country',       '_cl' => 'contact_country',        '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Contact Telephone',     '_cl' => 'contact_telephone',      '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Contact Email',         '_cl' => 'contact_email',          '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Contact External URL',  '_cl' => 'contact_external_url',   '_fl' => 'EN',   '_in' => 'text'),
		array ('_lb' => 'Charity Commision URL', '_cl' => 'charity_commission_url', '_fl' => 'EN',   '_in' => 'text'),
	),
	'_pagination' => array (
		'_rows_per_page' => 25,
	    '_row_counts' => array (2, 10, 25, 100, 500)
	),
	'_delete_sql' => array ("delete from scheme_topic where scheme_id = '{name}'")
);

doBat ($batDef, $dbh);

mysql_close($dbh);		// close database connection
?>
</body>
</html>