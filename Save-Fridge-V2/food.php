<?php
/*
 * Name: LINNI CAI
 * Date: May 30, 2019
 * Section: CSE 154 AO
 * This is the food.php web service to demonstrate basic
 * GET and POST parameter. Search user and food data.
 *
 * Web service details:
 *
 *   Possible GET parameters:
 *     - mode = log
 *     - name, pass
 *
 *   Possible POST parameters:
 *     - mode = reg
 *     - name, pass
 *   
 *   Possible GET parameters:
 *     - mode = search
 *     - name
 *
 *   Possible GET parameters:
 *     - mode = search
 *     - type = user
 *     - name
 *     - ques
 *     - ans
 *
 *   Possible GET parameters:
 *     - mode = search
 *     - type = single
 *     - name
 *
 *   Possible GET parameters:
 *     - mode = search
 *     - type = multi
 *     - table, show, macro, micro, min, max, key
 *
 *   Possible POST parameters:
 *     - mode = update
 *     - mode = food
 *     - name, abs, cal
 *
 *   Possible POST parameters:
 *     - mode = update
 *     - mode = user
 *     - pass, ques, ans
 *
 *   Possible mode: log/reg/search/update
 *
 *   Output Format:
 *     - TXT
 *     - JSON
 */

  include("common.php");

  error_reporting(E_ALL);
  get_mode();

  /**
   * Check mode, return mode if it's successful,
   * otherwise return null and report error.
   * @return {string} current mode.
   */
  function get_mode() {
    $mess = "";
    if (isset($_GET["mode"])) {
      $mode = $_GET["mode"];
    } else if (isset($_POST["mode"])) {
      $mode = $_POST["mode"];
    }
    mode_check($mode);
    target_check($mode);
  }

  /**
   * Check target in the given mode.
   * @param {string} mode - current mode.
   */
  function target_check($mode) {
    check_para(array($mode), "target_check");
    if ($mode === "reg") {
      reg_post();
    } else if ($mode === "log") {
      log_get();
    } else if ($mode === "search") {
      search_get();
    } else if ($mode === "update") {
      update_post();
    }
  }

  /**
   * Update food through fetching post,
   * check user input and report any error.
   */
  function update_post() {
    $db = get_PDO();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    checkPost("type");
    $type = strtolower($_POST["type"]);
    update_type_check($type);
    if ($type === "user") {
      update_user($db);
    } else {
      update_food($db);
    } 
  }

  function update_user($db) {
    update_user_check();
    $name = $_POST["name"];
    $pass = $_POST["pass"];
    $ques = $_POST["ques"];
    $ans = $_POST["ans"];
    check_text($name, "User Name");
    check_text($pass, "User Password");
    check_text($ques, "Security Question");
    check_text($ans, "Security Question Answer");
    $para = array("name" => $name,
                  "pass" => $pass,
                  "ques" => $ques,
                  "ans" => $ans);
    update_my_user($db, $para);
  }

  function update_food($db) {
    update_food_check();
    $name = strtolower($_POST["name"]);
    $abs = strtolower($_POST["abs"]);
    $cal = strtolower($_POST["cal"]);
    $cover = strtolower($_POST["cover"]); // yes/no
    check_text($name, "Food Name");
    check_text($abs, "Food Abstract");
    check_num($cal, "Food Calories Number");
    if ($cover !== "yes" && $cover !== "no") {
      $mess = "PHP Error: Invalid Cover Parameter, must be YES/NO.";
      rep_err($mess);
    } else {
      $row_find = find_my_food($db, $name);
      $para = array("name" => $name, 
                    "abs" => $abs, 
                    "cal" => $cal);
      update_food_exec($db, $cover, $row_find, $para);
    }
  }

  function update_user_check() {  
    checkPost("name");
    checkPost("pass");
    checkPost("ques");
    checkPost("ans");
  }

  /**
   * Check if user input in update section is NULL,
   * report error for any NULL input.
   */
  function update_food_check() {  
    checkPost("name");
    checkPost("abs");
    checkPost("cal");
    checkPost("cover");
  }

  /**
   * Update or add food through fetching post,
   * or print out existed food info.
   * @param {PDO} $db - a PDO of food database
   * @param {string} $cover - yes to override existed record,
   *                          no to print out existed record
   * @param {array} $row_find - an array of found rows
   * @param {array} $para - an array of user input
   */
  function update_food_exec($db, $cover, $row_find, $para) {
    if ($row_find) {
      if ($cover === "yes") {
        update_my_food($db, $para);
      } else {
        echo_info($row_find);
      }
    } else { // new food
      insert_my_food($db, $para);
    }
  }

  /**
   * Search food in the local or professional table,
   * check if user input is NULL, report error for any NULL input.
   */
  function search_get() {
    $db = get_PDO();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $type = NULL;
    $row_find = NULL;
    checkGet("type");
    $type = strtolower($_GET["type"]);
    search_type_check($type);
    if ($type === "user") {
      $row_find = search_user($db);
      search_check($row_find, "User");
      $mess = "Success: Valid Security QA, use Update for your new User Password.";
      rep_ok($mess);      
    } else {
      if ($type === "single") { // search in myfood
        $row_find = search_single($db);
      } else { // type = multiple, search in prof food db
        $row_find = search_multi($db);
      }
      search_check($row_find, "Food");
    }
  }

  function search_user($db) {
    checkGet("name");
    $name = $_GET["name"];
    check_text($name, "User Name");
    return find_my_user($db, $name);
  }

  /**
   * Check search result, print out found food result,
   * report error for unknown food.
   * @param {array} $row_find - an array of found rows
   */
  function search_check($row_find, $type) {
    if (!$row_find) {
      $mess = "Error: No Matched $type in the Database.";
      rep_err($mess);
    } else if ($type === "User") {
      $name = $row_find["name"];
      checkGet("ques");
      $ques = $_GET["ques"];
      check_text($ques, "Security Question");
      $rec_ques = $row_find["ques"];   
      qa_check($name, $ques, $rec_ques);
      checkGet("ans");
      $ans = $_GET["ans"];
      check_text($ques, "Security Question Answer");
      $rec_ans = $row_find["ans"];
      qa_check($name, $ans, $rec_ans);
    } else {
      echo_info($row_find);
    }
  }

  function qa_check($name, $ans, $rec) {
    if ($ans !== $rec) {
      $mess = "Error: Existed User $name, but Incorrect Security Q&A.";
      rep_err($mess);
    }
  }

  /** 
   * Return and search single food in local table,
   * check if food info is NULL before searching,
   * report error for any NULL food info.
   * @param {PDO} $db - a PDO of food database
   * @return {object} a JSON object of found rows
   */
  function search_single($db) {
    checkGet("name");
    $name = strtolower($_GET["name"]);
    check_text($name, "Food Name");
    return find_my_food($db, $name);
  }

  /**
   * Return and search food with multiple filters by sql.
   * @param {PDO} $db - a PDO of food database
   * @return {object} a JSON object of found rows
   */
  function search_multi($db) {
    $para = multi_para();
    $table = $para["table"];
    $macro = $para["macro"];
    $maorder = $para["maorder"];
    $micro = $para["micro"];
    $miorder = $para["miorder"];
    $sql = select_multi($para["micro"]) .
           "FROM $table\n" .
           "WHERE nf_calories <= :max\n" .
           "AND nf_calories >= :min\n" .
           "AND (item_name LIKE :key OR nf_ingredient_statement LIKE :key)\n" .
           "ORDER BY $macro $maorder, $micro $miorder\n" .
           "LIMIT :show;";
    return find_prof_food($db, $sql, $para);
  }

  /**
   * Return, get and check an array of parameters of multiple search,
   * report error for any invalid parameter.
   * @return {array} an array of parameters of multiple search
   */
  function multi_para() {
    multi_check();
    $table = $_GET["table"];
    table_check($table);
    $show = (int)$_GET["show"];
    $macro = $_GET["macro"];
    macro_check($macro);
    $maorder = $_GET["maorder"];
    order_check($maorder);
    $micro = $_GET["micro"];
    micro_check($micro);
    $miorder = $_GET["miorder"];
    order_check($miorder);
    $min = (int)$_GET["min"];
    $max = (int)$_GET["max"];
    $key = "%" . $_GET["key"] . "%";
    
    $para = array("table" => $table,
                  "key" => $key,
                  "macro" => $macro,
                  "maorder" => $maorder,
                  "micro" => $micro,
                  "miorder" => $miorder,
                  "max" => $max,
                  "min" => $min,
                  "show" => $show);
    return $para;
  }

  /**
   * Check if the given element is in the given array,
   * report error if it's not.
   * @param {array} $arr - an array of string elements
   * @param {string} $ele - a string element
   */
  function ele_check($arr, $ele) {
    if (!in_array($ele, $arr)) {
      $mess = "PHP Error: Invalid Parameter, must be ";
      foreach ($arr as $each) {
        $mess .= "{$each}/";
      }
      $mess = trim($mess, "/");
      $mess .= ".";
      rep_err($mess);
    }
  }

  function update_type_check($type) {
    $type_arr = array("user", "food");
    ele_check($type_arr, $type);
  }

  function search_type_check($type) {
    $type_arr = array("user", "single", "multi");
    ele_check($type_arr, $type);
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

  function mode_check($mode) {
    $mode_arr = array("log", "reg", "update", "search");
    ele_check($mode_arr, $mode);
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
    checkGet("table");
    checkGet("show");
    checkGet("macro");
    checkGet("maorder");
    checkGet("micro");
    checkGet("miorder");
    checkGet("min");
    checkGet("max");
    checkGet("key");
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
   * Check if the user name or user password is NULL,
   * report error if any is NULL,
   * find if the user name exists,
   * report error if it exists,
   * insert new account into User table,
   * report error if it fails.
   */
  function reg_post() {
    $db = get_PDO();
    $db->setAttribute(PDO::ATTR_ERRMODE,
                      PDO::ERRMODE_EXCEPTION);
    checkPost("name");
    checkPost("pass");
    checkPost("ques");
    checkPost("ans");
    $name = $_POST["name"];
    $pass = $_POST["pass"];
    $ques = $_POST["ques"];
    $ans = $_POST["ans"];
    check_text($name, "Username");
    check_text($pass, "Password");
    check_text($ques, "Security Question");
    check_text($ans, "Security Question Answer");
    $row_find = find_my_user($db, $name);
    $para = array("name" => $name,
                  "pass" => $pass,
                  "ques" => $ques,
                  "ans" => $ans);
    reg_check($db, $row_find, $para);
  }

  /**
   * Check if the user name exists,
   * report error if it exists,
   * insert new account into User table,
   * report error if it fails.
   * @param {PDO} $db - a PDO of food database
   * @param {array} $row_find - an array of found rows
   * @param {string} $name - a string represents user name
   * @param {string} $pass - a string represents user password
   */
  function reg_check($db, $row_find, $para) {
    if ($row_find) {
      $name = $para["name"];
      $mess = "Error: Existed Username '$name'.";
      rep_err($mess);
    } else {
      insert_my_user($db, $para);
    }
  }

  function checkPost($name) {
    if (!isset($_POST[$name])) {
      $mess = para_err("POST ") . "{$name}.\n";
      rep_err($mess);
    }
  }

  function checkGet($name) {
    if (!isset($_GET[$name])) {
      $mess = para_err("GET ") . "{$name}.\n";
      rep_err($mess);
    }
  }

  /**
   * Check if the user name or password is NULL,
   * report error if any is NULL,
   * find the user input account,
   * if it exists, check if the existed password matches,
   * report error if it fails,
   * report error for unknown user name.
   */
  function log_get() {
    $db = get_PDO();
    $db->setAttribute(PDO::ATTR_ERRMODE,
                      PDO::ERRMODE_EXCEPTION);
    checkGet("name");
    $name = $_GET["name"];
    checkGet("pass");
    $pass = $_GET["pass"];
    check_text($name, "Username");
    check_text($pass, "Password");
    $row_find = find_my_user($db, $name);
    log_check($row_find, $name, $pass);
  }

  /**
   * find the user input account,
   * if it exists, check if the existed password matches,
   * print out success if it matches, 
   * report error if it fails,
   * report error for unknown user name.
   * @param {array} $row_find - an array of found rows
   * @param {string} $name - a string represents user name
   * @param {string} $pass - a string represents user password
   */
  function log_check($row_find, $name, $pass) {
    if (!$row_find) {
      $mess = "Error: Unknown Username '$name'.";
      rep_err($mess);
    } else if ($row_find["pass"] === $pass) {
      $mess = "Success: Logged in.";
      rep_ok($mess);
    } else {
      $mess = "Error: Existed Username '$name' " .
              "but Invalid Password '$pass'.";
      rep_err($mess);
    }
  }
?>