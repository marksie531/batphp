BAT (PHP) - Bob's Awesome Tables (for PHP)
===================================================================================================

Author: Bob Marks (marksie531@yahoo.com)

Introduction
------------

Simple little PHP library for creating list / edit tables in PHP using a simple configuration.  
Adheres to the 80/20 rule i.e. should handle 80% of cases, for the more bespoke 20% of
cases then it's better to create your own or update the source code here.
This library handles: -

1. Two different views of a database table (a) list (b) edit
2. List view supports server side pagination, column sorting, custom multi-join queries
3. List view supports
4. List view supports exporting to excel, pdf, html

Creating a new table
--------------------

To create a new table you must do the following: -

1. Include the "bat.php" file
2. Create database connection in 
3. Create BAT definition (php array containing the required attributes)
4. Run to doBat () method.

Hello world
-----------

```php
$batDef = array (
  '_edit_id' => 'edit_id_thing',
	'_list_id' => 'list_id_thing',
	'_list_class' => 'test_table_list_class',
	'_edit_cass' => 'test_table_edit_class',
	'_action' => 'test1.php',
	'_db_table' => 'ROOM',
	'_db_list_sql' => $query,
	'_cols' => array (
		array ('_lb' => 'ID',          '_pkc' => 'ID',         '_w' => 10,  '_flags' => 'LS', '_input' => 'text|40'),
		array ('_lb' => 'Name',        '_col' => 'NAME',       '_w' => 100, '_flags' => 'LSE', '_input' => 'text|100'),
		array ('_lb' => 'Type',        '_col' => 'TDESC',      '_w' => 80,  '_flags' => 'LSE', '_input' => 'text|40'),
		array ('_lb' => 'Min.Sl',      '_col' => 'MIN_SLEEPS', '_w' => 40,  '_flags' => 'LSE', '_input' => 'text|40'),
		array ('_lb' => 'Sleeps',      '_col' => 'SLEEPS',     '_w' => 40,  '_flags' => 'LSE', '_input' => 'text|40'),
		array ('_lb' => 'Description', '_col' => 'RDESC',      '_w' => 500, '_flags' => 'LSE', '_input' => 'text|80,4')
	),
	'_can_edit' => true, '_can_delete' => true,	'_can_excel' => true, '_can_pdf' => false, '_can_print' => false,
	'_page_limit' => 30
);
```

Top level attributes (required)
-------------------------------

| Name              |      Description                                                                |
|-------------------|---------------------------------------------------------------------------------|
| _action           | Set this to the name of the PHP page where the table in e.g. customer.php       |
| _db_table         | The name of the database table that we are viewing / editing e.g. CUSTOMER      |
| _db_primary_keys  | An array of the primary keys of the previous table e.g. array ('ID', 'PROD_ID') |


Top level attributes (optional)
-------------------------------

| Name        | Defaults   | Description                                                                   |
|-------------|------------|-------------------------------------------------------------------------------|
| _list_id    | *blank*    | Use this to set the "id" attribute of the <table> element in the list page    |
| _edit_id    | *blank*    | Use this to set the "id" attribute of the <table> element in the edit page    |
| _list_class | list_table | Use this to set the "class" attribute of the <table> element in the list page |
| _edit_class | edit_table | Use this to set the "class" attribute of the <table> element in the edit page |


Column attributes (required)
----------------------------

*TODO*

Column attributes (optional)
----------------------------

```php
*TODO*

Example BAT defintion

$batDef = array (
'_edit_id' => 'edit_id_thing',
'_list_id' => 'list_id_thing',
'_list_class' => 'test_table_list_class',
'_edit_cass' => 'test_table_edit_class',
'_action' => 'test1.php',
'_db_table' => 'ROOM',
'_db_list_sql' => $query,
'_cols' => array (
array ('_lb' => 'ID',          '_pkc' => 'ID',         '_w' => 10,  '_flags' => 'LS', '_input' => 'text|40'),
array ('_lb' => 'Name',        '_col' => 'NAME',       '_w' => 100, '_flags' => 'LSE', '_input' => 'text|100'),
array ('_lb' => 'Type',        '_col' => 'TDESC',      '_w' => 80,  '_flags' => 'LSE', '_input' => 'text|40'),
array ('_lb' => 'Min.Sl',      '_col' => 'MIN_SLEEPS', '_w' => 40,  '_flags' => 'LSE', '_input' => 'text|40'),
array ('_lb' => 'Sleeps',      '_col' => 'SLEEPS',     '_w' => 40,  '_flags' => 'LSE', '_input' => 'text|40'),
array ('_lb' => 'Description', '_col' => 'RDESC',      '_w' => 500, '_flags' => 'LSE', '_input' => 'text|80,4')
),
'_can_edit' => true, '_can_delete' => true,	'_can_excel' => true, '_can_pdf' => false, '_can_print' => false,
'_page_limit' => 30
);
```