<?php
/*
 * Date: May 24, 2019
 * This is the food.php web service to demonstrate basic 
 * GET and POST parameter. Search user and food data.
 * Web service details:
 *   Possible GET/POST parameters:
 *     - mode
 *     - name
 *     - pass
 *   Possible GET/POST parameters:
 *     - mode
 *     - food
 *   Possible GET/POST parameters:
 *     - mode
 *     - food
 *     - abs
 *     - cal
 *   Possible mode: log/reg/search/update
 *   Output Format:
 *     - TXT or
 *     - JSON
 */
  
  error_reporting(E_ALL);  
  $mode = check_mode();
  $target = check_target($mode);
  $list = check_list($mode);
  $find = find_info($mode, $target, $list);
  check_exist($mode, $find, $target);

  /**
   * Check existence of the given target,
   * For mode register and update,
   * append the given target to the file,
   * otherwise report error for existence.
   * @param {string} mode - current mode.
   * @param {boolean} find - true for known target, false for unknown target.
   * @param {array} target - an array of user-input.
   */
  function check_exist($mode, $find, $target) {
    check_para(array($mode, $target), "check_exist");

    $file = null;
    $type = null;
    if ($mode === "reg" || $mode === "update") {
      if ($mode === "reg") {
        $file = "user.txt";
        $type = "Username";
      } else { // mode === update
        $file = "food.txt";
        $type = "Food Info";
      }
      if ($find) {
        $mess = "Error: Existed {$type}.\n";
        rep_err($mess);
      } else {
        update_file($target, $file);
      }
    }  
  }

  /**
   * Get the target from food update post,
   * return the target if it's successful,
   * otherwise return null and report error.
   * @return {array} an array of targets, user-input.
   */
  function update_post() {
    $target = null;
    if (!isset($_POST["food"])) {
      $mess = para_err("POST ") . "food.\n";
      rep_err($mess);
    } else if (!isset($_POST["abs"])) {
      $mess = para_err("POST ") . "abs.\n";
      rep_err($mess);
    } else if (!isset($_POST["cal"])) {
      $mess = para_err("POST ") . "cal.\n";
      rep_err($mess);
    }
    $food = strtolower($_POST["food"]);
    $abs = strtolower($_POST["abs"]);
    $cal = strtolower($_POST["cal"]);
    if (check_text($food, "Food Name") && 
        check_text($abs, "Food Abstract") && 
        check_num($cal, "Food Calories Number")) {
      $target = array($food, $abs, $cal);
    }
    return $target;
  }

  /**
   * Check mode, return mode if it's successful,
   * otherwise return null and report error.
   * @return {string} current mode.
   */
  function check_mode() {
    $mode = null;
    $mess = "";
    if (isset($_GET["mode"])) {
      $mode = $_GET["mode"];
    } else if (isset($_POST["mode"])) {
      $mode = $_POST["mode"];
    }
    $modeArr = array("log", "reg", "update", "search");
    if (!$mode) {
      $mess = para_err("") . "mode.\n"; 
    } else if (!in_array($mode, $modeArr)) {
      $mess = "PHP Error: Invalid Mode.\n";
    }
    if ($mess) {
      $mess .= "Possible Mode: log, reg, update, search.\n";
      rep_err($mess);
    } 
    return $mode;
  }

  /**
   * Check target in the given mode,
   * return target if it's successful,
   * otherwise return null and report error.
   * @param {string} mode - current mode.
   */
  function check_target($mode) {
    check_para(array($mode), "check_target");

    $target = null; 
    if ($mode === "reg" || $mode === "log") {
      if ($mode === "log") { // log mode
        $target = log_get();
      } else { // reg mode
        $target = reg_post();
      }    
    } else if ($mode === "search" || $mode === "update") {
      if ($mode === "search") { // search mode
        $target = search_get();
      } else { // update
        $target = update_post();
      }
    } else { // Invalid mode
      $mess = "Error: Invalid mode.\n";
      rep_err($mess);
    }
    if (!$target) {
      $mess = para_err("") . "target.\n" .
        "Possible Parameters: [mode, name, pass], " . 
        "[mode, food], [mode, food, abs, cal].\n";
      rep_err($mess);
    }
    return $target;
  }

  /**
   * Check info list in the given mode,
   * return list if it's successful,
   * otherwise return null and report error.
   * @param {string} mode - current mode.
   * @return {array} list - an array of info.
   */
  function check_list($mode) {
    check_para(array($mode), "check_list");

    $list = null; 
    if ($mode === "reg" || $mode === "log") {
      $list = get_list("user.txt");
    } else if ($mode === "search" || $mode === "update") {
      $list = get_list("food.txt");
    } else { // Invalid mode
      $mess = "PHP Error: Invalid Mode.\n";
      rep_err($mess);
    }
    if (empty($list)) {
      $mess = "PHP Error: Empty File Input.\n";
      rep_err($mess);
    }
    return $list;
  }

  /**
   * Get the target from food search get,
   * return the target if it's successful,
   * otherwise return null and report error.
   * @return {array} an array of targets, user-input.
   */
  function search_get() {
    $target = null;
    if (!isset($_GET["food"])) {
      $mess = para_err("GET ") . "food.\n";
      rep_err($mess);
    } else {
      $food = strtolower($_GET["food"]);
      if (check_text($food, "Food Name")) {
        $target = array($food);
      }
    }
    return $target;
  }

  /**
   * Get the target from user register post,
   * return the target if it's successful,
   * otherwise return null and report error.
   * @return {array} an array of targets, user-input.
   */
  function reg_post() {
    $target = null;
    if (!isset($_POST["name"])) {
      $mess = para_err("POST ") . "name.\n";
      rep_err($mess);
    } else if (!isset($_POST["pass"])) {
      $mess = para_err("POST ") . "pass.\n";
      rep_err($mess);
    } else {
      $name = $_POST["name"];
      $pass = $_POST["pass"]; 
      if (check_text($name, "Username") &&
          check_text($pass, "Password")) {
        $target = array($name, $pass);
      }
    }
    return $target;
  }

  /**
   * Get the target from user login get,
   * return the target if it's successful,
   * otherwise return null and report error.
   * @return {array} an array of targets, user-input.
   */
  function log_get() {
    $target = null;
    if (!isset($_GET["name"])) {
      $mess = para_err("GET ") . "name.\n";
      rep_err($mess);
    } else if (!isset($_GET["pass"])) {
      $mess = para_err("GET ") . "pass.\n";
      rep_err($mess);
    } else {
      $name = $_GET["name"];
      $pass = $_GET["pass"];
      if (check_text($name, "Username") &&
          check_text($pass, "Password")) {
        $target = array($name, $pass);
      }
    }
    return $target;
  }

  /**
   * Check the given text matches the text pattern,
   * report error if it doesn't match and 
   * error message include the given type label.
   * @param {string} $text - some user-input text.
   * @param {string} $type - a label of user-input text.
   */
  function check_text($text, $type) {
    if (strlen($text) < 3 || !preg_match('/\w+/', $text)) {
      $mess = "Error: Invalid {$type}. Length must greater than 2.\n";
      rep_err($mess);
      return false;
    }
    return true;
  }

  /**
   * Check the given number matches the number pattern,
   * report error if it doesn't match and
   * error message include the given type label.
   * @param {string} $text - some user-input text.
   * @param {string} $type - a label of user-input text.
   */
  function check_num($text, $type) {
    check_para(array($text, $type), "check_num");

    if (!preg_match('/[0-9]{1,6}/', $text)) {
      $mess = "Error: Invalid {$type}.\n";
      rep_err($mess);
      return false;
    }
    return true;
  }
  
  /**
   * Find target in the given list,
   * return true for successful log-in or register or found food,
   * otherwise return false.
   * @param {string} $mode - current mode, log or reg or food
   * @param {string} $target - array of rest parameters.
   * @param {array} $list - array of stored data.
   * @return {boolean} a boolean true for success, false otherwise.
   */
  function find_info($mode, $target, $list) {
    check_para(array($mode, $target, $list), "find_info");

    if ($mode === "search" || $mode === "update") {
      if (isset($target[0]) && isset($list[$target[0]])) {
        $food = $target[0];
        $info = $list[$food];
        if ($mode === "search") {
          printFoodInfo($info); 
        }
        return true;
      } else {
        if ($mode === "search") {
          $mess = "Error: Unknown food.\n";
          rep_err($mess);
        } 
        return false;
      }
    } else if ($mode === "log" || $mode === "reg") {
      if (isset($target[0]) && isset($target[1])) {
        $name = $target[0];
        $pass = $target[1];
        if (isset($list[$name])) {
          if ($mode === "reg") {
            return true;
          } else { // mode === log
            if (isset($list[$name]["password"])) {
              if ($pass !== $list[$name]["password"]) {
                $mess = "Error: Existed Username but Invalid Password.\n";
                rep_err($mess);
                return false;
              } else {
                $mess = "Success: Log in.\n";
                rep_ok($mess);
                return true;
              }
            }
          }
        } else {
          if ($mode === "log") {
            $mess = "Error: Unknown Username.\n";
            rep_err($mess);
          }
          return false;
        }
      }
    }
    $mess = "Error: Fail to find info.\n";     
    $mess = "Error: Empty Input.\n";
    rep_err($mess);
    return false;
  }

  /**
   * Return an array of info in the given file,
   * read file and build up corresponding array,
   * report error if fail.
   * @param {string} $file - a file contains info.
   * @return {array} an array of info in the given file.  
   */
  function get_list($file) {
    check_para(array($file), "get_list");

    $infoList = array();
    if (check_file_exist($file) && check_csv($file)) {
      $text = file($file, FILE_IGNORE_NEW_LINES);
      foreach ($text as $line) {
        if ($line) {
          $list = explode(", ", $line);
          if ($file === "food.txt") {
            $food = $list[0];
            $abs = $list[1];
            $cal = $list[2];
            $infoList[$food] = array(
              "name" => $food, 
              "abstract" => $abs, 
              "calories" => $cal
            );
          } else if ($file === "user.txt") {
            $name = $list[0];
            $pass = $list[1]; 
            $infoList[$name] = array(
              "username" => $name,
              "password" => $pass
            );
          } else {
            $mess = "Error: Invalid File.\n";
            rep_err($mess);
          } 
        }   
      }
    }
    return $infoList;
  }

  /**
   * Check parameters of the given function are empty or not,
   * return true for the given function has no null variable,
   * otherwise false.
   * @param {array} $list - an array of variables.
   * @param {string} $name - the name of a function.
   * @return {boolean} true for no null parameters, otherwise false.  
   */
  function check_para($paras, $name) {
    foreach ($paras as $para) {
      if (!$para) {
        $mess = "PHP Error: Empty parameters in {$name}.\n";
        rep_err($mess);
      }
    }
  }

  /**
   * Check the given file exist or not,
   * return true for existed file,
   * otherwise false.
   * @param {string} $file - the file will be checked.
   * @return {boolean} true for existed file, false otherwise.
   */
  function check_file_exist($file) {
    if (!file_exists($file)) {
      $mess = "Error: File doesn't exist.\n";
      rep_err($mess);
      return false;
    }
    return true;
  }

  /**
   * Check the given file is in csv format,
   * on each line, each info is separated by comma,
   * return true for csv file, otherwise false.
   * @param {string} $file - the file will be checked.
   * @return {boolean} true for csv file, false otherwise.
   */
  function check_csv($file) {
    if (check_file_exist($file)) {
      $num = 0;
      if ($file === "food.txt") {
        $num = 3;
      } else if ($file === "user.txt") {
        $num = 2;
      } else {
        $mess = "Error: Unknown File Input Format.\n";
        rep_err($mess);
        return false;
      }
      $text = file($file, FILE_IGNORE_NEW_LINES);
      foreach ($text as $line) {
        $list = explode(", ", $line);
        if (count($list) !== $num) {
          $mess = "Error: Invalid File Input Format.\n";
          rep_err($mess);
          return false;
        }
      }
    } else {
      return false;
    }
    return true;
  }

  /**
   * Update file with given info,
   * report error if fail to write file.
   * @param {array} $arr - an array of info to add.
   * @param {string} $file - a file to append. 
   */
  function update_file($arr, $file) {
    check_para(array($arr, $file), "update_file");

    if (check_file_exist($file)) {
      $mess = "Error: Fail to update info.\n";
      $food = null;
      $cate;
      $calo;
      $name = null;
      $pass;

      if ($file === "food.txt") {
        $food = $arr[0];
        $cate = $arr[1];
        $calo = $arr[2];
      } else if ($file === "user.txt") {
        $name = $arr[0];
        $pass = $arr[1];
      } else {
        $mess = "Error: Invalid File.\n";
        rep_err($mess);
      }

      if ($food) {
        add_to_file($arr, $file);
        $mess = "Success: Update database.\n";
        rep_ok($mess);
      } else if ($name) {
        add_to_file($arr, $file);
        $mess = "Success: New account.\n";
        rep_ok($mess);
      } else {
        rep_err($mess);
      } 
    }
  }

  /**
   * Append the text version of the given array to the given file,
   * report error if fail to write file.
   * @param {array} $arr - an array of info to add.
   * @param {string} $file - a file to append.
   */
  function add_to_file($arr, $file) {
    check_para(array($arr, $file), "add_to_file");

    if (check_file_exist($file)) {
      $openF = fopen($file, 'a');
      $data = implode(", ", $arr) . "\n";
      if (!fwrite($openF, $data)) {
        $mess = "Error: Fail to write file.\n";
        rep_err($mess);
      }
    }
  }

  function para_err($type) {
    return "PHP Error: Missing {$type}parameters. Missing ";
  }

  /**
   * Report error by the given message.
   * @param {string} $mess - error message.
   */
  function rep_err($mess) {
    header("Content-type: text/plain");
    header("HTTP/1.1 400 Invalid Request");
    die($mess);
  }

  /**
   * Report success by the given message.
   * @param {string} $mess - success message.
   */
  function rep_ok($mess) {
    header("Content-type: text/plain");
    echo $mess;
  }

  /**
   * Print recorded food info in JSON.
   * @param {array} $info - an array of recorded food info.
   */
  function printFoodInfo($info) {
    header("Content-type: application/json");
    echo json_encode($info) . "\n";
  }
?>
