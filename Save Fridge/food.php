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

  header("Content-type: text/plain");
  error_reporting(E_ALL);  

  $mode = null;
  $target = null;
  $list = null;

  // get/post parameters
  // get mode
  if (isset($_GET["mode"])) {
    $mode = $_GET["mode"];
  } else if (isset($_POST["mode"])) {
    $mode = $_POST["mode"];
  } 

  // get rest parameters
  if (isset($_GET["name"]) && isset($_GET["pass"])) {
    $name = $_GET["name"];
    $pass = $_GET["pass"];
    $target = array($name, $pass);
    $list = getList("user.txt");
  } else if (isset($_GET["food"])) {
    $food = $_GET["food"];
    $target = array($food);
    $list = getList("food.txt");
  } else if (isset($_POST["name"]) && isset($_POST["pass"])) {
    $name = $_POST["name"];
    $pass = $_POST["pass"];
    $target = array($name, $pass);
    $list = getList("user.txt");
  } else if (isset($_POST["food"]) && 
             isset($_POST["abs"]) &&
             isset($_POST["cal"])) {
    $name = strtolower($_POST["food"]);
    $abs = strtolower($_POST["abs"]);
    $cal = strtolower($_POST["cal"]);
    if (checkNum($cal)) {
      $target = array($name, $abs, $cal);
      $list = getList("food.txt");
    } 
  }

  if ($mode && $target) {
    $find = findInfo($mode, $target, $list);
    if ($mode === "reg") {
      checkAccount($name, $pass, $find);
    } else if ($mode === "food") {
      checkFood($find, $target);
    }
  } else {
    missPara($mode, $target);
  }

  /**
   * Check the food user type-in is valid or not,
   * report error if invalid for food search with unknown food,
   * or food update with existed food, 
   * update food database with unknown and valid food.
   * @param {boolean} $find - a boolean true if 
   * the user type-in food exists, otherwise unknown food.
   * @param {array} $target - an array of food info.
   */
  function checkFood($find, $target) {
    if (isset($_POST["mode"])) {
      if ($find) {
        $mess = "Error: Existed food.";
        repErr($mess);
      } else {
        upFile($target, "food.txt");
      }
    } else if (isset($_GET["mode"])) {
      if (!$find) {
        $mess = "Error: Unknown food.";
        repErr($mess);
      } 
    }
  }

  /**
  * Check the account user type-in is valid or not,
  * report error if invalid, otherwise register a new account.
  * @param {string} $name - a username user type-in.
  * @param {string} $pass - a password user type-in.
  * @param {boolean} $find - a boolean true if 
  * the user type-in account exists and matches,
  * otherwise register a new account.
  */
  function checkAccount($name, $pass, $find) {
    if (strlen($name) < 3 ||
        !preg_match('/\w+/', $name)) {
      $mess = "Error: Invalid Username.";
      repErr($mess);
    } else if (strlen($pass) < 3 ||
               !preg_match('/\w+/', $pass)) {
      $mess = "Error: Invalid Password.";
      repErr($mess);
    } else if (!$find) {
      $target = array($name, $pass);
      upFile($target, "user.txt");
    }
  }

  /**
   * Check the given number has the valid formart,
   * return true if valid, 
   * otherwise return false and report error.
   * @param {string} $cal - a string might 
   * contain letters and number, valid or invalid.
   * @return {boolean} a boolean to be true if valid 
   * calories number, false if invalid calories number.
   */
  function checkNum($cal) {
    if (!preg_match('/[0-9]{1,6}/', $cal)) {
      $mess = "Error: Invalid calories number.\n";
      repErr($mess);
      return false;
    }
    return true;
  }

  /**
   * Report error for missing parameters.
   * @param {string} $mode - current mode, log or reg or food
   * @param {string} $target - array of rest parameters.
   */
  function missPara($mode, $target) {
    $mess = "Error: Missing parameters: ";
    if (!$mode) {
      $mess .= "missing mode, ";
    }
    if (!$target) {
      $mess .= "missing target. ";
    }
    $mess .= "\nExpected params: [mode, name, pass] or " . 
      "[mode, food] or [mode, food, abs, cal].";
    repErr($mess);
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
    $mess = "Error: Fail to find info.\n";

    if (!$list) {
      $mess = "Error: Empty file.\n";
    } else if ($mode === "food" && isset($target[0])) {
      $food = strtolower($target[0]);
      if (isset($list[$food])) {
        $info = $list[$food];
        header("Content-type: application/json");
        print_r(json_encode($info) . "\n");
        return true;
      } else {
        return false;
      }
    } else if (($mode === "log" || $mode === "reg") && 
      isset($target[0]) && 
      isset($target[1])) {
      $name = $target[0];
      $pass = $target[1];
      if (!isset($list[$name])) {
        if ($mode === "log") {
          $mess = "Error: Unknown username.\n";
        } else if ($mode === "reg") {
          return false;
        }  
      } else if ($mode === "log") {
        if (isset($list[$name]["password"]) && 
          $pass !== $list[$name]["password"]) {
          $mess = "Error: Existed username and Invalid password.\n";
        } else {
          echo "Success: Log in.";
          return true;
        }
      } else if ($mode === "reg") {
        $mess = "Error: Existed username.\n";
        repErr($mess);
        return true;
      }
    }
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
    $text = file($file, FILE_IGNORE_NEW_LINES);
    $infoList = array();

    foreach ($text as $line) {
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
        repErr("Error: Invalid file.\n");
      }  
    }
    return $infoList;
  }

  /**
   * Update file with given info,
   * report error if fail to write file.
   * @param {array} $arr - an array of info to add.
   * @param {string} $file - a file to append. 
   */
  function upFile($arr, $file) {
    $food = null;
    $mess = "Error: Fail to update info.\n";
    $cate;
    $calo;
    $name;
    $pass;

    if ($file === "food.txt") {
      $food = $arr[0];
      $cate = $arr[1];
      $calo = $arr[2];
    } else if ($file === "user.txt") {
      $name = $arr[0];
      $pass = $arr[1];
    } else {
      $mess = "Error: Invalid file.\n";
    }

    $list = getList($file);

    if ($food) {
      if (isset($list[$food])) {
        $mess = "Error: Unknown food.\n";
      } else {
        addToFile($arr, $file);
        echo "Success: Update database.\n";
        return;
      }
    } else {
      if (array_key_exists($name, $list)) {
        $mess = "Error: Existed username.\n";
      } else {
        addToFile($arr, $file);
        echo "Success: New account.\n";
        return;
      }
    }
    repErr($mess);
  }

  /**
   * Append the text version of the given array to the given file,
   * report error if fail to write file.
   * @param {array} $arr - an array of info to add.
   * @param {string} $file - a file to append.
   */
  function addToFile($arr, $file) {
    $openF = fopen($file, 'a');
    $data = implode(", ", $arr) . "\n";
    if (!fwrite($openF, $data)) {
      $mess = "Error: Fail to write file.\n";
      repErr($mess);
    }
  }

  /**
   * Report error by the given message.
   * @param {string} $mess - error message.
   */
  function repErr($mess) {
    header("HTTP/1.1 400 Invalid Request");
    echo $mess;
  }
?>
