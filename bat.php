<?php

// Main do bat function
function doBat ($batDef, $dbh) {

  // Do actions first
  $action = getAction ();
  if ($action == 'delete') {
    deleteRow ($batDef, $dbh);
  }
  else if ($action == 'update') {
    updateRow ($batDef, $dbh);
  }
  else if ($action == 'insert') {
    insertRow ($batDef, $dbh);
  }

  // Display view
  $action = getAction ();
  if ($action == 'edit' || $action == 'new') {
    echo showBatEdit ($batDef, $dbh, ($action == 'new'));
  }
  else {
    echo showBatList ($batDef, $dbh);
  }
}

// Show table list
function showBatList ($batDef, $dbh) {
  $html = '';

  // 1) Parameters
  $cols = $batDef ['_cols'];
  $action = $batDef ['_action'];
  $defaultSort = isset ($batDef['_default_sort']) ? $batDef['_default_sort'] : -1;
  $defaultAsc = isset ($batDef['_default_asc']) ? $batDef['_default_asc'] : 1;
  $sort = isset ($_GET['sort']) ? $_GET['sort'] : $defaultSort;
  $asc = isset ($_GET['asc']) ? $_GET['asc'] : $defaultAsc;
  $isPaged = isset ($batDef['_pagination']);
  $rowsPerPage = getRowsPerPage ($batDef);
  $filterSql = '';
  $filterParams = "&rows=$rowsPerPage";
  $filtersHidden = '<input type="hidden" name="rows" value="'.$rowsPerPage.'"/>';

  // 2) filter section
  if (isAnyColFlagExists ($batDef, 'F')) {
    $html .= '<form name="filter" action="'.$action.'" method="GET">
      <input type="hidden" name="bat" value="list"/>
      <input type="hidden" name="sort" value="'.$sort.'"/>
      <input type="hidden" name="rows" value="'.$rowsPerPage.'"/>
      <input type="hidden" name="asc" value="'.$asc.'"/>
      <table class="list_filters"><tr>';

    $cols = $batDef ['_cols'];
    $sep = '';
    for ($i = 0; $i < count ($cols); $i++) {
      $col = $cols[$i];
      $flags = $col['_fl'];
      $label = $col['_lb'];

      if (strpos($flags, 'F') !== false) {
        // Set value
        $value = isset ($_GET[$i]) ? trim($_GET[$i]) : '';

        // Create default input (textfield)
        $html .= '<td><span>'.$label.'</span></td>';
        $html .= '<td>'.getInputHtml ($batDef, $i, $value, false).'</td>';
        $html .= '</td>';

        // Append filter SQL
        if ($value != '') {
          if (isset($col['_filter_sql'])) {
            $sql = $col['_filter_sql'];
            $sql = str_replace("{lc_value}", mysql_real_escape_string(strtolower($value)), $sql);
            $sql = str_replace("{uc_value}", mysql_real_escape_string(strtoupper($value)), $sql);
            $sql = str_replace("{value}", mysql_real_escape_string($value), $sql);

            // Update filter SQL
            $filterSql .= $sep.$sql;
          }
          else {
            $column = isset ($col['_pk']) ? $col['_pk'] : $col['_cl'];
            $filterSql .= "$sep$column = '".mysql_real_escape_string($value)."'";
          }
          $sep = " AND ";
        }

        // Apend filter params
        $filterParams .= "&$i=$value";
        $filtersHidden .= '<input type="hidden" name="'.$i.'" value="'.$value.'"/>';
      }
    }

    $html .= '
    <td><button type="submit"><img src="images/bat/find.png"/></button></td>
    </tr></table></form>';
  }

  // 2) Generate list SQL

  // (a) Generate / Grab SQL
  $sql = isset ($batDef['_db_list_sql']) ? $batDef['_db_list_sql'] : getListSql ($batDef);

  // (b) Add filtering if applicable
  if ($filterSql != '') {
    $sql .= strpos($sql, ' WHERE ') !== false ? ' AND ' : ' WHERE ';
    $sql .= $filterSql;
  }

  // (c) Add column sorting if applicable
  if ($sort != -1) {
    $col = isset ($cols[$sort]['_pk']) ? $cols[$sort]['_pk'] : $cols[$sort]['_cl'];
    $sql .= ' ORDER BY '.$col.' '.($asc == 1 ? 'ASC' : 'DESC');
  }

  // (d) Add pagination if applicable
  if ($isPaged) {
    $pagination = $batDef['_pagination'];
    $rowsPerPage = getRowsPerPage ($batDef);
    $pageNum = isset($_GET['page']) ? $_GET['page'] : 0;
    $offset = $pageNum * $rowsPerPage;

    $sql .= " LIMIT $offset, $rowsPerPage";
  }

  debug ($batDef, $sql.';');
  $result = mysql_query ($sql);
  $rowcount = mysql_num_rows ($result);

  // Only display table if rows exists
  $url = $action.'?bat=list'.$filterParams;
  if ($rowcount) {
    // 3) Create table headers
    $tableId = isset ($batDef['_list_id']) ? ' id="'.$batDef['_list_id'].'"' : '';
    $tableClass = ' class="'.(isset ($batDef['_list_class']) ? $batDef['_list_class'] : 'list_table').'"';

    $html .= "<table$tableId$tableClass>
      <thead><tr>";

    // Action columns
    if (isset ($batDef['_can_edit']) && $batDef['_can_edit']) {
      $html .= '<td>Edit</td>';
    }
    if (isset ($batDef['_can_delete']) && $batDef['_can_delete']) {
      $html .= '<td>Del</td>';
    }
    if (isset ($batDef['_can_print']) && $batDef['_can_print']) {
      $html .= '<td>Prt</td>';
    }
    if (isset ($batDef['_can_excel']) && $batDef['_can_excel']) {
      $html .= '<td>Exl</td>';
    }
    if (isset ($batDef['_can_pdf']) && $batDef['_can_pdf']) {
      $html .= '<td>Pdf</td>';
    }

    // ... columns
    for ($i = 0; $i < count ($cols); $i++) {
      $col = $cols[$i];
      $flags = $cols[$i]['_fl'];
      $label = $cols[$i]['_lb'];
      if (strpos($flags, 'L') !== false) {
        $colClass = isset ($col['_class']) ? ' class="'.$col['_class'].'" ' : '';
        $header = '';
        if (strpos($flags, 'S') !== false) {
          $curAsc = $i == $sort ? -$asc : $asc;
          $header .= '<a href="'.$url.'&sort='.$i.'&asc='.$curAsc.'">'.$label.'</a>';
          if ($sort == $i) {
            $header .= ($asc == -1) ? ' <img src="images/bat/navdown.gif" alt="Sort" />' : ' <img src="images/bat/navup.gif" alt="Sort" />';
          }
        }
        else {
          $header = $label;
        }

        $html .= "<td$colClass>$header</td>";
      }
    }
    $html .= '</tr></thead>';

    // 4) Iterate through result set and create body
    $html .= '<tbody>';
    while ($row = mysql_fetch_assoc($result)) {
      $html .= '<tr>';
      $params = getPkeyParams($row, $batDef).
        (isset($_GET['sort']) ? '&sort='.$_GET['sort'] : '').
        (isset($_GET['asc']) ? '&asc='.$_GET['asc'] : '');

      // Do actions
      $action = $batDef['_action'];
      if (isset ($batDef['_can_edit']) && $batDef['_can_edit']) {
        $html .= '<td><a href="'.$action.'?bat=edit'.$params.'"><img src="images/bat/edit.gif"/></a></td>';
      }
	  if (isset ($batDef['_can_delete']) && $batDef['_can_delete']) {
	    $deleteJs = '';
	    if (isset($batDef['_list_delete']) && $batDef['_list_delete'] != -1) {
	      $ld = $batDef['_list_delete'];
	      $column = isset ($cols[$ld]['_pk']) ? $cols[$ld]['_pk'] : $cols[$ld]['_cl'];
	      // echo "asfd column $column";
          $value = isset ($row [$column]) ? $row [$column] : '';
	      $deleteJs = ' onclick="return confirm(\'Are you sure you want do delete [ '.$value.' ]\')"';
	    }
	    $html .= '<td><a href="'.$action.'?bat=delete'.$params.'" '.$deleteJs.'><img src="images/bat/delete.gif"/></a></td>';
	  }
	  if (isset ($batDef['_can_print']) && $batDef['_can_print']) {
	    $html .= '<td><a href="'.$action.'?bat=print'.$params.'"><img src="images/bat/print.png"/></a></td>';
	  }
	  if (isset ($batDef['_can_excel']) && $batDef['_can_excel']) {
	    $html .= '<td><a href="'.$action.'?bat=excel'.$params.'"><img src="images/bat/excel.png"/></a></td>';
	  }
	  if (isset ($batDef['_can_pdf']) && $batDef['_can_pdf']) {
	    $html .= '<td><a href="'.$action.'?bat=pdf'.$params.'"><img src="images/bat/pdf.png"/></a></td>';
	  }

      // Iterate over table columns
      for ($i = 0; $i < count ($batDef ['_cols']); $i++) {
        $flags = $cols[$i]['_fl'];

        if (strpos($flags, 'L') !== false) {
          $col = $cols[$i];
          $colClass = isset ($col['_class']) ? ' class="'.$col['_class'].'" ' : '';
          $html .= "<td$colClass>";

          if (isset ($col['_pk'])) {
            $html .= $row[$col['_pk']];
          }
          else if (isset ($col['_cl'])) {
            $html .= $row[$col['_cl']];
          }
          $html .='</td>';
        }
      }
      $html .= '</tr>';
    }
    $html .= '</tbody>';

    // 5) Display pagination footer if applicable
    if ($isPaged) {
      // Count rows
      $countSql = isset ($pagination['_db_count_sql']) ? $pagination['_db_count_sql'] : "SELECT COUNT(1) FROM ".$batDef['_db_table'];
      if ($filterSql != '') {
        $countSql .= strpos($countSql, ' WHERE ') !== false ? ' AND ' : ' WHERE ';
        $countSql .= $filterSql;
      }
      debug ($batDef, $countSql.';');

      $pagination = $batDef ['_pagination'];
      $page = isset ($_GET['page']) ? $_GET['page'] : 0;
      $totalRows = getValue ($countSql);
      $maxPage = ceil ($totalRows / $rowsPerPage);

      $html .= '<form name="rows" action="'.$batDef['_action'].'">
      <input type="hidden" name="page" value="'.$page.'"/>'.$filtersHidden.'
      <tfoot><tr><td colspan="'.getColumns($batDef).'">';

      // Create "Display" combo box
      if (isset ($pagination['_row_counts'])) {
	        $html .= '<span><strong>Display: </strong></span>
          <select name="rows" onchange="javascript:document.rows.submit()">';
        foreach ($pagination['_row_counts'] as $rowCount) {
          $s = $rowsPerPage == $rowCount ? ' selected="selected"' : '';
          $html .= '<option'.$s.'>'.$rowCount.'</option>';
        }
        $html .= '</select>';
      }

      $html .= '<span>';

      $addHtml = "&rows=".$rowsPerPage.$filterParams;
      if ($page > 0) {
     	  $html .= ' <a href="'.$action.'?page=0'.$addHtml.'"><img alt="First" title="First" src="images/bat/navfirst.gif" width="16" height="16" /></a>';
	    $html .= ' <a href="'.$action.'?page='.($page - 1).$addHtml.'"><img alt="Previous" title="Previous" src="images/bat/navprev.gif" width="16" height="16" /></a>';
      }
      $html .= '</span><span><strong>'.($page + 1).'</strong> of <strong>'.($maxPage).'</strong></span> <span>';
      if ($page + 1 < $maxPage) {
	    $html .= ' <a href="'.$action.'?page='.($page + 1).$addHtml.'"><img alt="Next" title="Next" src="images/bat/navnext.gif" width="16" height="16" /></a>';
	    $html .= ' <a href="'.$action.'?page='.($maxPage - 1).$addHtml.'"><img alt="Last" title="Last" src="images/bat/navlast.gif" width="16" height="16" /></a>';
      }
      $html .= '</span>';
      $html .= '</td></tr></tfoot></form>';
    }

    $html .= '</table>';
  }
  else {
    $html .= '<p class="error">No rows exists</p>';
  }

  return $html;
}

// Show table edit screen
function showBatEdit ($batDef, $dbh, $isNew) {
  // HTML attributes
  $tableId = isset ($batDef['_edit_id']) ? ' id="'.$batDef['_edit_id'].'"' : '';
  $tableClass = ' class="'.(isset ($batDef['_edit_class']) ? $batDef['_edit_class'] : 'edit_table').'"';
  $title = $isNew ? 'New' : 'Edit';
  $action = $isNew ? 'insert' : 'update';

  // Run database query
  if (!$isNew) {
  	$rowData = getRowData ($batDef);
  }

  // Start creating HTML
  $html = '<form name="cancel" action="'.$batDef['_action'].'" method="GET"></form>
    <form name="edit" action="'.$batDef['_action'].'" method="POST">
    <input type="hidden" name="bat" value="'.$action.'"/>';
  if (!$isNew) {
    $html .= getPkeyHiddenInputs($batDef, $rowData);
  }
  $html .= "<table$tableId$tableClass>";

  // Header
  $html .= '<thead><tr><td colspan="2">'.$title.'</td></tr></thead>';

  // Body
  $html .= '<tbody>';
  $cols = $batDef ['_cols'];
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $col['_fl'];
    $label = $col['_lb'];

    $display = $isNew ? strpos($flags, 'N') !== false : strpos($flags, 'E') !== false || strpos($flags, 'R') !== false;
    if ($display) {
      $value = '';

      // Set value
      if (isset ($_POST[$i])) {
        $value = $_POST[$i];
      } else if (!$isNew) {
        $column = isset ($cols[$i]['_pk']) ? $cols[$i]['_pk'] : $cols[$i]['_cl'];
        $value = $rowData [strtolower($column)];
      }
      $readOnly = !$isNew && strpos($flags, 'R') !== false;

      $html .= '<tr>';
      $html .= '<td>'.$label.'</td>';
      $html .= '<td>'.getInputHtml ($batDef, $i, $value, $readOnly).'</td>';
      $html .= '</tr>';
    }
  }

  $html .= '<tfoot><tr><td colspan="2">
  <input type="button" value="Cancel" onclick="javascript:document.cancel.submit();" />
  <input type="submit" value="OK" />
  </td></tr></tfoot>';

  // Footer
  $html .= '</table></form>';

  return $html;
}

// Delete row
function deleteRow ($batDef, $dbh) {
  // Check columns for '_v_fk' flag (foreigh key validation)
  $cols = $batDef ['_cols'];
  $errors = '';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $col['_fl'];
    if (isset ($col['_v_fk'])) {
      // $column = isset ($cols[$i]['_pk']) ? $cols[$i]['_pk'] : $cols[$i]['_cl'];
      $value = mysql_real_escape_string($_GET [$i]);
      foreach ($col['_v_fk'] as $fk) {
        $kfArray = explode(".", $fk);
        if (count ($kfArray) == 2) {
          $sql = "SELECT COUNT(1) FROM ".$kfArray[0]." WHERE ".$kfArray[1]." = '$value'";
          debug ($batDef, $sql.';');
          if (getValue ($sql) > 0) {
            $errors = 'Item [ '.$value.' ] cannot be deleted because a reference to it exists in the '.$kfArray[0].' table.';
          }
        }
      }
    }
  }

  if ($errors == '') {
    $sql = "DELETE FROM ".$batDef["_db_table"]." WHERE ".getPkeySql($batDef);
    debug ($batDef, $sql.';');
    updateDb ($sql, $dbh);
  }
  else {
    error ($errors);
  }
}

// Update row
function updateRow ($batDef, $dbh) {
  // Perform validation first
  $errors = validate ($batDef);
  if ($errors != '') {
    $_POST['bat'] = 'edit';
    error ($errors);
    return;
  }

  // Starting creating SQL (UPDATE)
  $updateSql = 'UPDATE '.$batDef["_db_table"];
  $setSql = ' SET ';
  $whereSql = ' WHERE';
  $cols = $batDef ['_cols'];

  // Create update cols
  $sep1 = ' ';
  $sep2 = ' ';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $col['_fl'];

    // Create SET part
    if (strpos($flags, 'E') !== false) {
      $column = isset ($cols[$i]['_pk']) ? $cols[$i]['_pk'] : $cols[$i]['_cl'];
      $value = mysql_real_escape_string($_POST [$i]);

      // Append to update SQL
      $setSql .= "$sep1$column = '$value'";
      $sep1 = ", ";
    }

    // Create WHERE condition
    if (isset($col['_pk'])) {
      $pKey = $col['_pk'];
      $value = mysql_real_escape_string($_POST ["pk_$i"]);
      $whereSql .= "$sep2$pKey = '$value'";
      $sep2 = " AND ";
    }
  }
  $sql = $updateSql.$setSql.$whereSql;
  debug ($batDef, $sql.';');
  if (!mysql_query ($sql, $dbh)) {
    $_GET['bat'] = 'edit';
    error (mysql_error());
  }
  else {
    success ("Item update successfully");
  }
}

// Update row
function insertRow ($batDef, $dbh) {
  // Perform validation first
  $errors = validate ($batDef);
  if ($errors != '') {
    $_GET['bat'] = 'new';
    error ($errors);
    return;
  }

  // Starting creating SQL (INSERT)
  $dbTable = $batDef["_db_table"];
  $insertSql = 'INSERT INTO '.$dbTable.' ';
  $columnsSql = '(';
  $valuesSql = ') VALUES (';
  $cols = $batDef ['_cols'];

  // Create update cols
  $sep = '';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    $flags = $col['_fl'];

    // Create SET part
    if (strpos($flags, 'N') !== false || strpos($flags, 'G') !== false) {
      $column = isset ($cols[$i]['_pk']) ? $cols[$i]['_pk'] : $cols[$i]['_cl'];
      if (strpos($flags, 'G') !== false) {
        $sql = "SELECT MAX($column)+1 AS ID FROM $dbTable";
        debug ($batDef, $sql.';');
        $value = getValue ($sql);
      }
      else {
        $value = mysql_real_escape_string($_POST [$i]);
      }

      // Append to update SQL
      $columnsSql .= "$sep$column";
      $valuesSql .= "$sep'$value'";
      $sep = ", ";
    }
  }
  $valuesSql .= ")";

  $sql = $insertSql.$columnsSql.$valuesSql;
  debug ($batDef, $sql.';');
  if (!mysql_query ($sql, $dbh)) {
    $_GET['bat'] = 'new';
    error (mysql_error());
  }
  else {
    success ("New item inserted successfully");
  }
}

// Update database
function updateDb ($sql, $dbh) {
  if (!mysql_query ($sql, $dbh)) {
    return mysql_error();
  }
}

// Get single value
function getValue ($sql) {
  $result = mysql_query ($sql);
  $row = mysql_fetch_array($result, MYSQL_NUM);
  return $row[0];
}

// Execute database query
function getResult ($sql) {
  $result = mysql_query ($sql);
  return mysql_fetch_assoc($result);
}

// Return row data
function getRowData ($batDef) {
  $sql = "SELECT * FROM ".$batDef['_db_table']." WHERE ".getPkeySql ($batDef);
  debug ($batDef, $sql.';');
  return getResult ($sql);
}

// Return primary key SQL
function getPkeySql ($batDef) {
  $sql = '';
  $cols = $batDef ['_cols'];
  $sep = '';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    if (isset($col['_pk'])) {
      $val = isset($_GET[$i]) ? $_GET[$i] : $_POST["pk_$i"];
      $sql .= $sep.$col['_pk']." = '$val'";
      $sep = " AND ";
    }
  }
  return $sql;
}

// Return primary key parameters (using position in $batDef) from database row
function getPkeyParams ($rowData, $batDef) {
  $params = '';
  $cols = $batDef ['_cols'];
  $sep = '';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    if (isset($col['_pk'])) {
      $pKey = $col['_pk'];
      if (isset($rowData[$pKey])) {
        $val = $rowData[$pKey];
        $params .= "&$i=$val";
      }
    }
  }
  return $params;
}

// Return primary key as input items of type hidden (using position in $batDef) from database row
function getPkeyHiddenInputs ($batDef, $rowData) {
  $hidden = '';
  $cols = $batDef ['_cols'];
  $sep = '';
  for ($i = 0; $i < count ($cols); $i++) {
    $col = $cols[$i];
    if (isset($col['_pk'])) {
      $pKey = $col['_pk'];
      if (isset($rowData[strtolower($pKey)])) {
        $val = $rowData[strtolower($pKey)];
        $hidden .= '<input type="hidden" name="pk_'.$i.'" value="'.$val.'" />';
      }
    }
  }
  return $hidden;
}

// Return list SQL
function getListSql ($batDef) {
  $sql = 'SELECT ';
  $sep = '';
  for ($i = 0; $i < count ($batDef ['_cols']); $i++) {
    $col = $batDef['_cols'][$i];
    $flags = $col['_fl'];
    if (strpos($flags, 'L') !== false) {
      $column = isset ($col['_pk']) ? $col['_pk'] : $col['_cl'];
      $sql .= $sep.$column;
      $sep = ',';
    }
  }
  $sql .= ' FROM '.$batDef['_db_table'];
  return $sql;
}

// Get input
function getInputHtml ($batDef, $i, $value, $readOnly) {
  $cols = $batDef['_cols'];
  $col = $cols[$i];

  // Create default input (textfield)
  $inputHtml = '<input type="text" name="'.$i.'"'.($readOnly ? 'disabled="disabled"' : '').' value="'.$value.'" />';

  // Check optinal "_in" field
  if (isset($col['_in'])) {
    $input = explode("|", $col['_in']);
    $colClass = isset ($col['_class']) ? ' class="'.$col['_class'].'" ' : '';
    $numOfInputs = count ($input);

    // 1) Text field
    if ($input [0] == 'text') {
      $inputHtml = '<input type="text" name="'.$i.'"'.($readOnly ? 'disabled="disabled"' : '').' value="'.$value.'" '.$colClass.'/>';
    }

    // 2) Text area
    else if ($input [0] == 'textarea') {
      $inputHtml = '<textarea type="text" name="'.$i.'"'.($readOnly ? 'disabled="disabled"' : '').' '.$colClass.'/>'.$value.'</textarea>';
    }

    // 3) Check boxes
    else if ($input [0] == 'checkbox') {
      $inputHtml = 'TODO';
    }

    // 4) combo box / radio buttons
    else if (($input [0] == 'select' || $input [0] == 'radio') && $numOfInputs > 1) {
      $isRadio = $input [0] == 'radio';
      $inputHtml = !$isRadio ? '<select name="'.$i.'"'.($readOnly ? 'disabled="disabled"' : '').$colClass.'>' : '';

      // Create key values
      for ($j = 1; $j < $numOfInputs; $j++) {
        $curInput = $input [$j];

  		// a) Number range
  		if (strpos($curInput, '..') !== false) {
  		  $nums = explode("..", $curInput);

  		  if (count($nums) == 3) {
            for ($n = $nums[0]; $n < $nums[1]; $n++) {
  	          if ($isRadio) {
                $inputHtml .= '<input type="radio" name="'.$i.'" value="'.$n.'"'.($n == $value ? ' checked="checked"' : '').'/> '.$n;
              }
              else {
                $inputHtml .= '<option value="'.$n.'"'.($n == $value ? ' selected="selected"' : '').'>'.$n.'</option>';
              }
  	        }
  	      }
  		}

  		// b) SQL query
  		else if (strStarts(strtoupper($curInput), "SELECT ")) {
          $result = mysql_query ($curInput);
  	      while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
  	        $key = $row[0];
  	        $text = isset ($row[1]) ? $row[1] : $row[0];

  	        if ($isRadio) {
              $inputHtml .= '<input type="radio" name="'.$i.'" value="'.$key.'"'.($key == $value ? ' checked="checked"' : '').'/> '.$text;
            }
            else {
              $inputHtml .= '<option value="'.$key.'"'.($key == $value ? ' selected="selected"' : '').'>'.$text.'</option>';
            }
  	      }
  		}

  		// c) User supplied text
  		else {
  	  	  $values = explode(",", $curInput);
  	 	  $key = $values[0];
  		  $text = count ($values) == 2 ? $values [1] : $values[0];

  	      if ($isRadio) {
            $inputHtml .= '<input type="radio" name="'.$i.'" value="'.$key.'"'.($key == $value ? ' checked="checked"' : '').'/> '.$text;
          }
          else {
            $inputHtml .= '<option value="'.$key.'"'.($key == $value ? ' selected="selected"' : '').'>'.$text.'</option>';
          }
  		}
      }

      $inputHtml .= '</select>';
    }
  }
  return $inputHtml;
}

// Return true/false if a character exists in any of the column "_flags"
function isAnyColFlagExists($batDef, $char) {
  for ($i = 0; $i < count ($batDef ['_cols']); $i++) {
    $flags = $batDef['_cols'][$i]['_fl'];
    if (strpos($flags, $char) !== false) {
      return true;
    }
  }
  return false;
}

// Return number of columns in the list table
function getColumns ($batDef) {
  $count = 0;
  if (isset ($batDef['_can_edit']) && $batDef['_can_edit']) {
    $count ++;
  }
  if (isset ($batDef['_can_delete']) && $batDef['_can_delete']) {
    $count ++;
  }
  if (isset ($batDef['_can_print']) && $batDef['_can_print']) {
    $count ++;
  }
  if (isset ($batDef['_can_excel']) && $batDef['_can_excel']) {
    $count ++;
  }
  if (isset ($batDef['_can_pdf']) && $batDef['_can_pdf']) {
    $count ++;
  }

  // Iterate over table columns
  for ($i = 0; $i < count ($batDef ['_cols']); $i++) {
    $flags = $batDef['_cols'][$i]['_fl'];
    if (strpos($flags, 'L') !== false) {
      $count ++;
    }
  }
  return $count;
}

// Validation method
function validate ($batDef) {
  $errors = '';
  foreach ($batDef['_cols'] as $colId => $col) {
    // Check to see if value is set
    if (isset ($_POST[$colId])) {
      $value = trim ($_POST[$colId]);
      $label = $col['_lb'];
      $error = false;

      // Check flags
      if (isset ($col['_v']) && strpos($col['_v'], 'E') !== false && $value == '') {
        $errors .= 'Please fill in the "'.$label.'" field<br/>';
      }
      else if (isset ($col['_v_eq']) && $col['_v_eq'] == $value) {
        $errors .= 'Please select a value for the "'.$label.'" field<br/>';
      }
    }
  }
  return $errors;
}

// Return rows per page
function getRowsPerPage ($batDef) {
  $defaultRowsPerPage = isset ($batDef['_pagination']['_rows_per_page']) ? $batDef['_pagination']['_rows_per_page'] : 25;
  $rowsPerPage = isset ($_GET['rows']) ? $_GET['rows'] : $defaultRowsPerPage;
  return $rowsPerPage;
}

// Get action
function getAction () {
  $action = "list";
  if (isset ($_GET['bat'])) {
    $action = $_GET['bat'];
  }
  else if (isset ($_POST['bat'])) {
    $action = $_POST['bat'];
  }
  return $action;
}

// Display success message
function success ($message) {
  echo '<p class="success">'.$message.'</p>';
}

// Display error message
function error ($message) {
  echo '<p class="error">'.$message.'</p>';
}

// Display debug message
function debug ($batDef, $message) {
  if (isset ($batDef['_debug_sql']) && $batDef['_debug_sql']) {
    echo '<div id="debug">'.$message.'<div>';
  }
}

// Starts with function
function strStarts($haystack, $needle) {
  return $needle === "" || strpos($haystack, $needle) === 0;
}
?>