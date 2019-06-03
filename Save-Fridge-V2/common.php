<?php
  /**
   * Name: LINNI CAI
   * Date: May 30, 2019
   * Section: CSE 154 AO
   * This is the common.php for food.php, share SQL and report functions.
   */

  /**
   * Returns a PDO object connected to the database. If a PDOException is thrown when
   * attempting to connect to the database, responds with a 503 Service Unavailable
   * error.
   * @return {PDO} connected to the database upon a succesful connection.
   */
  function get_PDO() {
    # Variables for connections to the database.
    $host = "localhost";     # fill in with server name (e.g. localhost)
    $port = "8889";      # fill in with a port if necessary (will be different mac/pc)
    $user = "root";     # fill in with user name
    $password = "root"; # fill in with password (will be different mac/pc)
    $dbname = "fooddb";   # fill in with db name containing your SQL tables

    # Make a data source string that will be used in creating the PDO object
    $ds = "mysql:host={$host}:{$port};dbname={$dbname};charset=utf8";

    try {
      $db = new PDO($ds, $user, $password);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $db;
    } catch (PDOException $ex) {
      rep_db_err($ex);
    }
  }

  /**
   * Return a string represents an error message,
   * includes the given type parameter string.
   * @param {string} $type - a string represents a type of parameter
   * @return {string} a string represents an error message
   */
  function para_err($type) {
    return "PHP Error: Missing {$type}parameters. Missing ";
  }

  /**
   * Report 400 error by the given message,
   * and terminate the current script.
   * @param {string} $mess - error message.
   */
  function rep_err($mess) {
    header("Content-type: text/plain");

    header("HTTP/1.1 400 Invalid Request");
    die("$mess\n");
  }

  /**
   * Report 503 database error by the given message,
   * and terminate the current script.
   * @param {string} $mess - error message.
   */
  function rep_db_err($mess, $ex) {
    header("Content-type: text/plain");

    header("HTTP/1.1 503 Service Unavailable");
    die("$mess\n");
  }

  /**
   * Print out success by the given message.
   * @param {string} $mess - success message.
   */
  function rep_ok($mess) {
    header("Content-type: text/plain");

    echo "$mess\n";
  }

  /**
   * Print out recorded food info in JSON.
   * @param {array} $info - an array of recorded food info.
   */
  function echo_info($info) {
    header("Content-type: application/json");

    echo json_encode($info);
  }

  /**
   * Check the given number matches the number pattern,
   * report error if it doesn't match,
   * error message include the given type label.
   * @param {string} $text - some user-input text
   * @param {string} $type - a label of user-input text
   */
  function check_num($text, $type) {
    check_para(array($text, $type), "check_num");
    if (!preg_match('/^[0-9]{1,6}$/', $text)) {
      $mess = "Error: Invalid {$type}.";
      rep_err($mess);
    }
  }

  /**
   * Check the given text matches the text pattern,
   * report error if it doesn't match and
   * error message include the given type label.
   * @param {string} $text - some user-input text
   * @param {string} $type - a label of user-input text
   */
  function check_text($text, $type) {
    $mess = "Error: Invalid {$type}.\n";
    if (strlen($text) < 3) {
      $mess .= "Length must greater than 2.";
      rep_err($mess);
    } else if (!preg_match('/^(\w|\d|&| )+$/', $text)){
      $mess .= "{$type} must use letter, number, space or &.";
      rep_err($mess);
    }
  }

  /**
   * Check parameters of the given function are empty or not,
   * return true for the given function has no null variable,
   * otherwise false.
   * @param {array} $list - an array of variables
   * @param {string} $name - the name of a function
   */
  function check_para($paras, $name) {
    foreach ($paras as $para) {
      if (!$para) {
        $mess = "PHP Error: Empty parameters in {$name}.";
        rep_err($mess);
      }
    }
  }

  /**
   * Return and find an array of the given name
   * and recorded password in the given database,
   * report 503 error if fails.
   * @param {PDO} $db - a PDO of food database
   * @param {string} $name - a string represents user name
   * @return {array} an array of found rows
   */
  function find_my_user($db, $name) {
    try {
      $sql = "SELECT name, pass, ques, ans FROM MyUser WHERE name = :name;";
      $sql_state = $db->prepare($sql);
      $para = array("name" => $name);
      $sql_state->execute($para);
      $row = $sql_state->fetch(PDO::FETCH_ASSOC);
      return $row;
    }

    catch (PDOException $ex) {
      $mess = "Error: Fail to Find '$name' Info in User Database.";
      rep_db_err($mess, $ex);
    }
  }

  /**
   * Insert the given name and password in the given database,
   * report 503 error if fails.
   * @param {PDO} $db - a PDO of food database
   * @param {string} $name - a string represents user name
   * @param {string} $pass - a string represents user password
   */
  function insert_my_user($db, $para) {
    try {
      $sql = "INSERT INTO MyUser (name, pass, ques, ans) " .
             "VALUES (:name, :pass, :ques, :ans);";
      $sql_state = $db->prepare($sql);
      $sql_state->execute($para);

      $mess = "Success: Registered.";
      rep_ok($mess);
    }

    catch (PDOException $ex) {
      $name = $para["name"];
      $mess = "Error: Fail to Register New Account '$name' in User Database.";
      rep_db_err($mess, $ex);
    }
  }

  /**
   * Return and find an array of the given SQL result,
   * execute SQL with the given parameters,
   * report 503 error if fails.
   * @param {PDO} $db - a PDO of food database
   * @param {string} $sql - a string represents sql statement
   * @param {array} $para - an array of parameters will be
   *                        filled in the given SQL statement
   * @return {array} an array of the given SQL result
   */
  function find_prof_food($db, $sql, $para) {
    // echo $sql . "\n";
    $key = str_replace("%", "", $para["key"]);
    try {
      $sql_state = $db->prepare($sql);
      $sql_state->bindParam("key", $para["key"], PDO::PARAM_STR);
      $sql_state->bindParam("max", $para["max"], PDO::PARAM_INT);
      $sql_state->bindParam("min", $para["min"], PDO::PARAM_INT);
      $sql_state->bindParam("show", $para["show"], PDO::PARAM_INT);
      $sql_state->execute();
      $row = $sql_state->fetchAll(PDO::FETCH_ASSOC);
      return $row;
    }

    catch (PDOException $ex) {
      $mess = "Error: Fail to Find Food with '$key' in Food Database.";
      echo $ex . "\n";
      rep_db_err($mess, $ex);
    }
  }

  /**
   * Return and find an array of SQL result,
   * execute SQL with the given name,
   * report 503 error if fails.
   * @param {PDO} $db - a PDO of food database
   * @param {string} $name - a string represents food name
   * @return {array} an array of the given SQL result
   */
  function find_my_food($db, $name) {
    try {
      $sql = "SELECT name, abs, cal FROM MyFood WHERE name = :name;";
      $sql_state = $db->prepare($sql);
      $para = array("name" => $name);
      $sql_state->execute($para);
      $row = $sql_state->fetch(PDO::FETCH_ASSOC);
      return $row;
    }

    catch (PDOException $ex) {
      $mess = "Error: Fail to Find '$name' Info in Food Database.";
      rep_db_err($mess, $ex);
    }
  }

  /**
   * Execute SQL with the given parameters,
   * insert food info into the given database,
   * report 503 error if fails,
   * otherwise print out success.
   * @param {PDO} $db - a PDO of food database
   * @param {array} $para - an array of parameters will be
   *                        filled in the SQL statement
   */
  function insert_my_food($db, $para) {
    $name = $para["name"];
    try {
      $sql = "INSERT INTO MyFood (name, abs, cal) " .
             "VALUES (:name, :abs, :cal);";
      $sql_state = $db->prepare($sql);
      $sql_state->execute($para);

      $mess = "Success: Add New Food '$name' to Database.";
      rep_ok($mess);
    }

    catch (PDOException $ex) {
      $mess = "Error: Fail to Add New Food '$name' Info to Food Database.";
      rep_db_err($mess, $ex);
    }
  }

  /**
   * Execute SQL with the given parameters,
   * update food info in the given database,
   * report 503 error if fails,
   * otherwise print out success.
   * @param {PDO} $db - a PDO of food database
   * @param {array} $para - an array of parameters will be
   *                        filled in the SQL statement
   */
  function update_my_food($db, $para) {
    $name = $para["name"];
    try {
      $sql = "UPDATE MyFood " .
             "SET abs = :abs, cal = :cal " .
             "WHERE name = :name";
      $sql_state = $db->prepare($sql);
      $sql_state->execute($para);
      $mess = "Success: Update Existed '$name' Info in Food Database.";
      rep_ok($mess);
    }

    catch (PDOException $ex) {
      $mess = "Error: Fail to Update Existed '$name' Info in Food Database.";
      rep_db_err($mess, $ex);
    }
  }

  function update_my_user($db, $para) {
    $name = $para["name"];
    try {
      $sql = "UPDATE MyUser " .
             "SET pass = :pass, ques = :ques, ans = :ans " .
             "WHERE name = :name";
      $sql_state = $db->prepare($sql);
      $sql_state->execute($para);
      $mess = "Success: Update Existed '$name' Info in User Table of Food Database.";
      rep_ok($mess);
    }

    catch (PDOException $ex) {
      $mess = "Error: Fail to Update Existed '$name' Info " .
              "in User Table of Food Database.";
      rep_db_err($mess, $ex);
    }
  }

  /**
   * Execute SQL with the given parameters,
   * delete food in the given database,
   * report 503 error if fails,
   * otherwise print out success.
   * @param {PDO} $db - a PDO of food database
   * @param {array} $para - an array of parameters will be
   *                        filled in the SQL statement
   */
  function delete_my_food($db, $name) {
    try {
      $sql = "DELETE FROM MyFood " .
             "WHERE name = :name";
      $sql_state = $db->prepare($sql);
      $para = array("name" => $name);
      $sql_state->execute($para);
      $mess = "Success: Delete '$name' Info in Food Database.";
      rep_ok($mess);
    }

    catch (PDOException $ex) {
      $mess = "Error: Fail to Delete '$name' Info in Food Database.";
      rep_db_err($mess, $ex);
    }
  }
?>
