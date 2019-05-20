<?php
/*
 * Date: May 18, 2019
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
 *   Output Format:
 *     - TXT or
 *     - JSON
 */

  error_reporting(E_ALL);  
  $mode = checkMode();
  $target = checkTarget($mode);
  $list = checkList($mode);
  $find = findInfo($mode, $target, $list);
  checkExist($mode, $find, $target);

  /**
   * Check existence of the given target,
   * For mode register and update,
   * append the given target to the file,
   * otherwise report error for existence.
   * @param {string} mode - current mode.
   * @param {boolean} find - true for known target, false for unknown target.
   * @param {array} target - an array of user-input.
   */
  function checkExist($mode, $find, $target) {
    $para = array($mode, $target);
    if (checkPara($para, "checkExist")) {
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
          repErr($mess);
        } else {
          updateFile($target, $file);
        }
      }  
    }
  }

  /**
   * Get the target from food update post,
   * return the target if it's successful,
   * otherwise return null and report error.
   * @return {array} an array of targets, user-input.
   */
  function updatePost() {
    $target = null;
    if (!isset($_POST["food"])) {
      $mess = "Error: Empty food name.\n";
      repErr($mess);
    } else if (!isset($_POST["abs"])) {
      $mess = "Error: Empty food abstract.\n";
      repErr($mess);
    } else if (!isset($_POST["cal"])) {
      $mess = "Error: Empty food calories.\n";
      repErr($mess);
    } else {
      $food = strtolower($_POST["food"]);
      checkText($food, "Food Name");
      $abs = strtolower($_POST["abs"]);
      checkText($abs, "Food Abstract");
      $cal = strtolower($_POST["cal"]);
      checkNum($cal, "Food Calories Number");
      $target = array($food, $abs, $cal);
    }
    return $target;
  }

  /**
   * Check mode, return mode if it's successful,
   * otherwise return null and report error.
   * @return {string} current mode.
   */
  function checkMode() {
    $mode = null;
    if (isset($_GET["mode"])) {
      $mode = $_GET["mode"];
    } else if (isset($_POST["mode"])) {
      $mode = $_POST["mode"];
    }
    if (!$mode) {
      $mess = "Error: Missing parameters. Missing mode.\n";
      repErr($mess);
    }
    return $mode;
  }

  /**
   * Check target in the given mode,
   * return target if it's successful,
   * otherwise return null and report error.
   * @param {string} mode - current mode.
   */
  function checkTarget($mode) {
    $para = array($mode);
    $target = null;
    if (checkPara($para, "checkText")) {
      if ($mode === "reg" || $mode === "log") {
        if ($mode === "log") { // log mode
          $target = logGet();
        } else { // reg mode
          $target = regPost();
        }    
      } else if ($mode === "search" || $mode === "update") {
        if ($mode === "search") { // search mode
          $target = searchGet();
        } else { // update
          $target = updatePost();
        }
      } else { // incorrect mode
        $mess = "Error: Incorrect mode.\n";
        repErr($mess);
      }
      if (!$target) {
        $mess = "Error: Missing parameters. Missing target.\n" .
          "Expected Parameters: [mode, name, pass] or " . 
          "[mode, food] or [mode, food, abs, cal].\n";
        repErr($mess);
      }
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
  function checkList($mode) {
    $para = array($mode);
    $list = null;
    if (checkPara($para, "checkText")) {
      if ($mode === "reg" || $mode === "log") {
        $list = getList("user.txt");
      } else if ($mode === "search" || $mode === "update") {
        $list = getList("food.txt");
      } else { // incorrect mode
        $mess = "Error: Incorrect mode.\n";
        repErr($mess);
      }
      if (empty($list)) {
        $mess = "Error: Empty File Input.\n";
        repErr($mess);
      }
    }
    return $list;
  }

  /**
   * Get the target from food search get,
   * return the target if it's successful,
   * otherwise return null and report error.
   * @return {array} an array of targets, user-input.
   */
  function searchGet() {
    $target = null;
    if (!isset($_GET["food"])) {
      $mess = "Error: Empty food name.\n";
      repErr($mess);
    } else {
      $food = strtolower($_GET["food"]);
      checkText($food, "Food Name");
      $target = array($food);
    }
    return $target;
  } 

  /**
   * Get the target from user register post,
   * return the target if it's successful,
   * otherwise return null and report error.
   * @return {array} an array of targets, user-input.
   */
  function regPost() {
    $target = null;
    if (!isset($_POST["name"])) {
      $mess = "Error: Empty username.\n";
      repErr($mess);
    } else if (!isset($_POST["pass"])) {
      $mess = "Error: Empty password.\n";
      repErr($mess);
    } else {
      $name = $_POST["name"];
      checkText($name, "Username");
      $pass = $_POST["pass"];
      checkText($pass, "Password");
      $target = array($name, $pass);
    }
    return $target;
  }

  /**
   * Get the target from user login get,
   * return the target if it's successful,
   * otherwise return null and report error.
   * @return {array} an array of targets, user-input.
   */
  function logGet() {
    $target = null;
    if (!isset($_GET["name"])) {
      $mess = "Error: Empty username.\n";
      repErr($mess);
    } else if (!isset($_GET["pass"])) {
      $mess = "Error: Empty password.\n";
      repErr($mess);
    } else {
      $name = $_GET["name"];
      checkText($name, "Username");
      $pass = $_GET["pass"];
      checkText($pass, "Password");
      $target = array($name, $pass);
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
  function checkText($text, $type) {
    $para = array($text, $type);
    if (checkPara($para, "checkText") && strlen($text) < 3 ||
        !preg_match('/\w+/', $text)) {
      $mess = "Error: Invalid {$type}.\n";
      repErr($mess);
    }
  }

  /**
   * Check the given number matches the number pattern,
   * report error if it doesn't match and
   * error message include the given type label.
   * @param {string} $text - some user-input text.
   * @param {string} $type - a label of user-input text.
   */
  function checkNum($text, $type) {
    $para = array($text, $type);
    if (checkPara($para, "checkNum") && !preg_match('/[0-9]{1,6}/', $text)) {
      $mess = "Error: Invalid {$type}.\n";
      repErr($mess);
    }
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
  function findInfo($mode, $target, $list) {
    $para = array($mode, $target, $list);
    if (checkPara($para, "findInfo")) {
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
            repErr($mess);
          } else {
            return false;
          }
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
                  repErr($mess);
                  return false;
                } else {
                  $mess = "Success: Log in.\n";
                  repOK($mess);
                  return true;
                }
              }
            }
          } else {
            if ($mode === "log") {
              $mess = "Error: Unknown Username.\n";
              repErr($mess);
            }
            return false;
          }
        }
      }
    }
    $mess = "Error: Fail to find info.\n";
    repErr($mess);
    return false;
  }

  /**
   * Return an array of info in the given file,
   * read file and build up corresponding array,
   * report error if fail.
   * @param {string} $file - a file contains info.
   * @return {array} an array of info in the given file.  
   */
  function getList($file) {
    $infoList = array();
    $para = array($file);
    if (checkPara($para, "getList") && checkFileExist($file) && checkCSV($file)) {
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
            repErr($mess);
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
  function checkPara($list, $name) {
    $ok = true;
    foreach ($list as $para) {
      if (!$para) {
        $ok = false;
      }
    }
    if (!$ok) {
      $mess = "Error: Empty parameters in {$name}.\n";
      repErr($mess);
    }
    return $ok;
  }

  /**
   * Check the given file exist or not,
   * return true for existed file,
   * otherwise false.
   * @param {string} $file - the file will be checked.
   * @return {boolean} true for existed file, false otherwise.
   */
  function checkFileExist($file) {
    if (!file_exists($file)) {
      $mess = "Error: File doesn't exist.\n";
      repErr($mess);
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
  function checkCSV($file) {
    if (checkFileExist($file)) {
      $num = 0;
      if ($file === "food.txt") {
        $num = 3;
      } else if ($file === "user.txt") {
        $num = 2;
      } else {
        $mess = "Error: Unknown File Input Format.\n";
        repErr($mess);
        return false;
      }
      $text = file($file, FILE_IGNORE_NEW_LINES);
      foreach ($text as $line) {
        $list = explode(", ", $line);
        if (count($list) !== $num) {
          $mess = "Error: Invalid File Input Format.\n";
          repErr($mess);
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
  function updateFile($arr, $file) {
    if (!$arr || !$file) {
      $mess = "Error: Empty parameters in updateFile.\n";
      repErr($mess);
    }
    if (checkFileExist($file)) {
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
        repErr($mess);
      }

      if ($food) {
        addToFile($arr, $file);
        $mess = "Success: Update database.\n";
        repOK($mess);
      } else if ($name) {
        addToFile($arr, $file);
        $mess = "Success: New account.\n";
        repOK($mess);
      } else {
        repErr($mess);
      } 
    }
  }

  /**
   * Append the text version of the given array to the given file,
   * report error if fail to write file.
   * @param {array} $arr - an array of info to add.
   * @param {string} $file - a file to append.
   */
  function addToFile($arr, $file) {
    if (!$arr || !$file) {
      $mess = "Error: Empty parameters in updateFile.\n";
      repErr($mess);
      return;
    }
    if (checkFileExist($file)) {
      $openF = fopen($file, 'a');
      $data = implode(", ", $arr) . "\n";
      if (!fwrite($openF, $data)) {
        $mess = "Error: Fail to write file.\n";
        repErr($mess);
      }
    }
  }

  /**
   * Report error by the given message.
   * @param {string} $mess - error message.
   */
  function repErr($mess) {
    header("Content-type: text/plain");
    header("HTTP/1.1 400 Invalid Request");
    echo $mess;
  }

  /**
   * Report success by the given message.
   * @param {string} $mess - success message.
   */
  function repOK($mess) {
    header("Content-type: text/plain");
    echo $mess;
  }

  /**
   * Print recorded food info in JSON.
   * @param {array} $info - an array of recorded food info.
   */
  function printFoodInfo($info) {
    header("Content-type: application/json");
    print_r(json_encode($info) . "\n");
  }
?>
