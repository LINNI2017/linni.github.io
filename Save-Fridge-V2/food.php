<?php
/*
 * Date: June 6, 2019
 * This is the food.php web service to demonstrate basic POST parameter. 
 * API to search user account and food data.
 *
 * Web service details:
 *
 *   Possible POST parameters:
 *     - base = user
 *     - mode = log/reg/update/search/delete
 *
 *   Possible Required parameters:
 *     - log: name, pass
 *     - reg: name, pass, ques, ans
 *     - update: name, pass, ques, ans
 *     - search: name, ques, ans
 *     - delete: name, pass
 *
 *   Possible POST parameters:
 *     - base = food
 *     - mode = add/update/delete/ssearch/msearch
 *
 *   Possible Required parameters:
 *     - add: name, abs, cal
 *     - update: name, abs, cal
 *     - delete: name
 *     - ssearch: name
 *     - msearch: table, show, macro, maorder, micro, miorder, min, max, key
 *
 *   Output Format:
 *     - TXT
 *     - JSON
 */

  include("common.php");

  error_reporting(E_ALL);
  base_post();

  /**
   * Check base and check required mode.
   */
  function base_post() {
    check_post("base");
    $base = $_POST["base"];
    base_check($base);
    check_post("mode");
    $mode = $_POST["mode"];
    if ($base === "user") {
      user_mode_check($mode);
      user_target_check($mode);
    } else {
      food_mode_check($mode);
      food_target_check($mode);
    }
  }

  /**
   * Check parameters for the given mode.
   * @param {string} $mode - a string represents a mode
   */
  function food_target_check($mode) {
    if ($mode === "add") {
      food_add_post();
    } else if ($mode === "update") {
      food_update_post();
    } else if ($mode === "ssearch") {
      food_ssearch_post();
    } else if ($mode === "msearch") {
      food_msearch_post();
    } else { // mode = delete
      food_delete_post();
    }
  }

  /**
   * Check food info validity and return it in an array.
   * @param {array} an array of food info
   */
  function food_info_check() {
    check_post("name");
    check_post("abs");
    check_post("cal");
    $name = strtolower($_POST["name"]);
    $abs = $_POST["abs"];
    $cal = $_POST["cal"];
    check_text($name, "Food Name");
    check_text($abs, "Food Abstract");
    check_num($cal, "Food Calories");
    return array("name" => $name, 
                 "abs" => $abs, 
                 "cal" => $cal);
  }

  /**
   * Check food existence and insert new food,
   * report error for existed food, or invalid food info.
   */
  function food_add_post() {
    $para = food_info_check();
    $name = $para["name"];
    item_exist_check($name, "Food", true);
    insert_my_food($para);
  }

  /**
   * Check food existence and update existed food,
   * report error for unknown food, or invalid food info.
   */
  function food_update_post() {
    $para = food_info_check();
    $name = $para["name"];
    item_exist_check($name, "Food", false);
    update_my_food($para);
  }

  /**
   * Check food existence and delete existed food,
   * report error for unknown food, or invalid food info.
   */
  function food_delete_post() {
    check_post("name");
    $name = $_POST["name"];
    check_text($name, "Food Name");
    item_exist_check($name, "Food", false);
    delete_my_food(array("name" => $name));
  }

  /**
   * Check food existence and print out existed food info,
   * report error for unknown food, or invalid food info.
   */
  function food_ssearch_post() {
    check_post("name");
    $name = $_POST["name"];
    check_text($name, "Food Name");
    $row = item_exist_check($name, "Food", false);
    echo_info($row);
  }

  /**
   * Check food existence and print out existed food info,
   * with multiple filters,
   * report error for no matched food, or invalid food info.
   */
  function food_msearch_post() {
    $para = multi_para();
    $table = $para["table"];
    $macro = $para["macro"];
    $maorder = $para["maorder"];
    $micro = $para["micro"];
    $miorder = $para["miorder"];
    $key = $para["key"];
    $sql = select_multi($para["micro"]) .
           "FROM $table\n" .
           "WHERE nf_calories <= :max\n" .
           "AND nf_calories >= :min\n" .
           "AND (item_name LIKE :key OR nf_ingredient_statement LIKE :key)\n" .
           "ORDER BY $macro $maorder, $micro $miorder\n" .
           "LIMIT :show;";
    $row = find_prof_food($sql, $para);
    if (!$row) {
      $key = str_replace("%", "", $key);
      $err = "No Matched Food with '$key' in $table Table.";
      rep_err($err);
    } else {
      echo_info($row);
    }
  }

  /**
   * Check parameters for the given mode.
   * @param {string} $mode - a string represents a mode
   */
  function user_target_check($mode) {
    if ($mode === "reg") {
      user_reg_post();
    } else if ($mode === "log") {
      user_log_post();
    } else if ($mode === "search") {
      user_search_post();
    } else if ($mode === "update") {
      user_update_post();
    } else { // mode = delete
      user_delete_post();
    } 
  }

  /**
   * Check user account info validity, return it in an array,
   * report error for invalid user info.
   * @param {array} $para - an array of user info
   * @return {array} $para - an array of user account info
   */
  function user_acc_check($para) {
    check_post("name");
    check_post("pass");
    $name = $_POST["name"];
    $pass = $_POST["pass"];
    check_text($name, "Username");
    check_text($pass, "Password");
    $para["name"] = $name;
    $para["pass"] = $pass;
    return $para;
  }

  /**
   * Check user security info validity, return it in an array,
   * report error for invalid user info.
   * @param {array} $para - an array of user info
   * @return {array} $para - an array of user security info
   */
  function user_qa_check($para) {
    check_post("ques");
    check_post("ans");
    $ques = $_POST["ques"];
    $ans = $_POST["ans"];
    check_text($ques, "Security Question");
    check_text($ans, "Security Question Answer");
    $para["ques"] = $ques;
    $para["ans"] = $ans;
    return $para;
  }

  /**
   * Check user info validity, insert user info into user table.
   * report error for invalid or unknown user info.
   */
  function user_reg_post() {
    $para = array();
    $para = user_acc_check($para);
    $para = user_qa_check($para);
    $name = $para["name"];
    item_exist_check($name, "User", true);
    insert_my_user($para);
  }

  /**
   * Check user info validity, log in user.
   * report error for invalid or unknown or no matched user info.
   */
  function user_log_post() {
    $para = array();
    $para = user_acc_check($para);
    $row_find = find_my_user($para["name"]);
    log_check($row_find, $para);
  }

  /**
   * Check user info validity, delete user info from user table.
   * report error for invalid, unknown or no matched user info.
   */
  function user_delete_post() {
    $para = array();
    $para = user_acc_check($para);
    $name = $para["name"];
    $pass = $para["pass"];
    $row = item_exist_check($name, "User", false);
    if ($row["pass"] !== $pass) {
      $err = "Incorrect Password, Cannot Validate Your Identity.";
      rep_err($err);   
    }
    delete_my_user($para);
  }

  /**
   * Check item info existence with the given name in the given type table,
   * return existed item info in an array.
   * report error for searching unknown item or 
   * inserting existed item in the given table.
   * @param {string} $name - a string represents the item name
   * @param {string} $type - a string represents the item table type
   * @param {boolean} $insert - true to insert the given item in the given table,
   *                            false to search the given item in the given table
   * @return {array} an array of existed item info
   */
  function item_exist_check($name, $type, $insert) {
    $row = NULL;
    if ($type === "User") {
      $row = find_my_user($name);
    } else {
      $row = find_my_food($name);
    }
    if (!$row && !$insert) {
      $err = "Unknown $type '$name' in $type Table.";
      rep_err($err);
    } else if ($row && $insert) {
      if ($type === "Food") {
        echo_info($row);
      }
      $err = "Existed $type '$name' in $type Table.";
      rep_err($err);
    }
    return $row;
  }

  /**
   * Check user info validity, update user info from user table,
   * report error for invalid or unknown user info.
   */
  function user_update_post() {
    $para = array();
    $para = user_acc_check($para);
    $para = user_qa_check($para);
    $name = $para["name"];
    item_exist_check($name, "User", false); 
    update_my_user($para);
  }

  /**
   * Check user info validity, search user info from user table,
   * with valid security question and answer,
   * report error for invalid or unknown or not matched user info.
   */
  function user_search_post() {
    check_post("name");
    $name = $_POST["name"];
    check_text($name, "Username");
    $para = array("name" => $name);
    $para = user_qa_check($para);
    $row = item_exist_check($name, "User", false); 
    $rec_ques = $row["ques"];
    $rec_ans = $row["ans"];   
    qa_check($name, $para["ques"], $rec_ques);
    qa_check($name, $para["ans"], $rec_ans);
    $ok = "Valid Security QA, use Update for your new User Password.";
    rep_ok($ok);
  }

  /**
   * Check security question and answer validty,
   * compare the given answer and recorded answer,
   * report error for not matched user security question or answer.
   * @param {string} $name - a string represents the user name
   * @param {string} $ans - a string represents the user answer
   * @param {string} $rec - a string represents the user recorded answer
   */
  function qa_check($name, $ans, $rec) {
    if ($ans !== $rec) {
      $err = "Existed User '$name', but Incorrect Security Q&A.";
      rep_err($err);
    }
  }

  /**
   * Return, get and check an array of parameters of multiple search,
   * report error for any invalid parameter.
   * @return {array} an array of parameters of multiple search
   */
  function multi_para() {
    multi_check();
    $table = $_POST["table"];
    $show = (int)$_POST["show"];
    $macro = $_POST["macro"];
    $maorder = strtolower($_POST["maorder"]);
    $micro = $_POST["micro"];
    $miorder = strtolower($_POST["miorder"]);
    $min = (int)$_POST["min"];
    $max = (int)$_POST["max"];
    $key = "%" . strtolower($_POST["key"]) . "%";
    table_check($table);  
    macro_check($macro); 
    order_check($maorder);
    micro_check($micro);  
    order_check($miorder);    
    return array("table" => $table,
                  "key" => $key,
                  "macro" => $macro,
                  "maorder" => $maorder,
                  "micro" => $micro,
                  "miorder" => $miorder,
                  "max" => $max,
                  "min" => $min,
                  "show" => $show);
  }

  /**
   * Check if the given element is in the given array,
   * report error if it's not.
   * @param {array} $arr - an array of string elements
   * @param {string} $ele - a string element
   */
  function ele_check($arr, $ele) {
    if (!in_array($ele, $arr)) {
      $err = "[PHP] Invalid Parameter, must be ";
      foreach ($arr as $each) {
        $err .= "{$each}/";
      }
      $err = trim($err, "/");
      $err .= ".";
      rep_err($err);
    }
  }

  /**
   * Check the given table is in the possible table pool,
   * report error if it's not.
   * @param {string} $table - a string element represents table
   */
  function table_check($table) {
    $table_arr = array("Grocery", "Common", "Restaurant");
    ele_check($table_arr, $table);
  }

  function user_mode_check($mode) {
    $mode_arr = array("log", "reg", "update", "search", "delete");
    ele_check($mode_arr, $mode);
  }

  function food_mode_check($mode) {
    $mode_arr = array("add", "update", "delete", "ssearch", "msearch");
    ele_check($mode_arr, $mode);
  }

  function base_check($base) {
    $base_arr = array("food", "user");
    ele_check($base_arr, $base);
  }

  /**
   * Check the given macro-nutrient is in the possible macro-nutrient pool,
   * report error if it's not.
   * @param {string} $macro - a string element represents macro-nutrient
   */
  function macro_check($macro) {
    $macro_arr = array("nf_protein", "nf_total_fat", "nf_total_carbohydrate");
    ele_check($macro_arr, $macro);
  }

  /**
   * Check the given order is in the possible order pool,
   * report error if it's not.
   * @param {string} $order - a string element represents order
   */
  function order_check($order) {
    $order_arr = array("asc", "desc");
    ele_check($order_arr, $order);
  }

  /**
   * Check the given micro-nutrient is in the possible micro-nutrient pool,
   * report error if it's not.
   * @param {string} $micro - a string element represents micro-nutrient
   */
  function micro_check($micro) {
    $micro_arr = array("nf_sugars", "nf_sodium", "nf_cholesterol",
                       "nf_vitamin_a_dv", "nf_vitamin_c_dv",
                       "nf_calcium_dv", "nf_iron_dv");
    ele_check($micro_arr, $micro);
  }

  /**
   * Check if an array of parameters of multiple search is NULL,
   * report error for any NULL parameter.
   */
  function multi_check() {
    check_post("table");
    check_post("show");
    check_post("macro");
    check_post("maorder");
    check_post("micro");
    check_post("miorder");
    check_post("min");
    check_post("max");
    check_post("key");
  }

  /**
   * Return a string represents a SELECT clause,
   * includes the given micro-nutrient column.
   * @param {string} $micro - a string represents micro-nutrient
   * @return {string} a string represents a SELECT clause
   */
  function select_multi($micro) {
    $micro_std = std_colname($micro);
    return "SELECT DISTINCT brand_name AS Brand, item_name AS Food,\n" .
           "nf_calories AS Calories, nf_total_fat AS Fat,\n" .
           "nf_total_carbohydrate AS Carbohydrate,\n" .
           "nf_protein AS Protein,\n" .
           "$micro AS $micro_std\n";
  }

  /**
   * Return and convert the given string to a standard format of string,
   * first letter of each word is uppercase.
   * @param {string} $str - a string will be converted to standard format
   * @return {string} a string in standard format, with uppercase first letter
   */
  function std_colname($str) {
    $str = str_replace("nf_", "", $str);
    $str = str_replace("_dv", "", $str);
    $words = explode("_", $str);
    $res = "";
    foreach ($words as $word) {
      $word = ucfirst($word);
      $res .= "{$word}_";
    }
    return trim($res, "_");
  }

  /**
   * Check POST variable validity,
   * report error for invalid POST with the given name.
   * @param {string} $name - a string represents POST variable name
   */
  function check_post($name) {
    if (!isset($_POST[$name])) {
      $err = para_err("POST ") . "{$name}.\n";
      rep_err($err);
    }
  }

  /**
   * find the user input account,
   * if it exists, check if the existed password matches,
   * print out success if it matches, 
   * report error if it fails,
   * report error for unknown user name.
   * @param {array} $row - an array of found rows
   * @param {string} $name - a string represents user name
   * @param {string} $pass - a string represents user password
   */
  function log_check($row, $para) {
    $name = $para["name"];
    $pass = $para["pass"];
    $row = item_exist_check($name, "User", false);
    if ($row["pass"] === $pass) {
      $ok = "Logged in.";
      rep_ok($ok);
    } else {
      $err = "Existed Username '$name' " .
             "but Invalid Password '$pass'.";
      rep_err($err);
    }
  }
?>