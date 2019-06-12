<?php
  /**
   * Date: June 6, 2019
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
   * Return executed SQL statement of the given sql filled with parameters.
   * @param {string} $sql - a string represents sql statement
   * @param {array} $para - an array of parameters
   * @return {object} an executed SQL statement object
   */
  function exec_sql($sql, $para, $err, $ok) {
    try {
      $db = get_PDO();
      $sql_state = $db->prepare($sql);
      $sql_state->execute($para);
    }
    catch (PDOException $ex) {
      rep_db_err($err, $ex);
    }
    if ($ok) {
      rep_ok($ok);
    }  
    return $sql_state;
  }

  /**
   * Return a string represents an error message,
   * includes the given type parameter string.
   * @param {string} $type - a string represents a type of parameter
   * @return {string} a string represents an error message
   */
  function para_err($type) {
    return "[PHP] Missing {$type}parameters. Missing ";
  }

  /**
   * Report 400 error by the given message,
   * and terminate the current script.
   * @param {string} $err - error message.
   */
  function rep_err($err) {
    header("Content-type: text/plain");
    header("HTTP/1.1 400 Invalid Request");
    die("Error: $err\n");
  }

  /**
   * Report 503 database error by the given message,
   * and terminate the current script.
   * @param {string} $err - error message.
   */
  function rep_db_err($err, $ex) {
    header("Content-type: text/plain");
    header("HTTP/1.1 503 Service Unavailable");
    die("Error: $err\n");
  }

  /**
   * Print out success by the given message.
   * @param {string} $ok - success message.
   */
  function rep_ok($ok) {
    header("Content-type: text/plain");
    echo "Success: $ok\n";
  }

  /**
   * Print out recorded food info in JSON.
   * @param {array} $info - an array of recorded food info.
   */
  function echo_info($info) {
    header("Content-type: application/json");
    echo json_encode($info) . "\n";
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
      $err = "Invalid {$type}.\n{$type} must use number.";
      rep_err($err);
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
    $err = "Invalid {$type}.\n";
    if (strlen($text) < 3) {
      $err .= "Length must greater than 2.";
      rep_err($err);
    } else if (!preg_match('/^(\w|\d|&| )+$/', $text)){
      $err .= "{$type} must use letter, number, space or &.";
      rep_err($err);
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
        $err = "[PHP] Empty parameters in {$name}.";
        rep_err($err);
      }
    }
  }

  /**
   * Return and find an array of the given name
   * and recorded password in the given database,
   * report 503 error if fails.
   * @param {string} $name - a string represents user name
   * @return {array} an array of found rows
   */
  function find_my_user($name) {
    $sql = "SELECT name, pass, ques, ans FROM MyUser WHERE name = :name;";
    return find_my($sql, $name, "User");
  }

  /**
   * Return and find an array of SQL result,
   * execute SQL with the given name,
   * report 503 error if fails.
   * @param {string} $name - a string represents food name
   * @return {array} an array of the given SQL result
   */
  function find_my_food($name) {
    $sql = "SELECT name, abs, cal FROM MyFood WHERE name = :name;";
    return find_my($sql, $name, "Food");
  }

  /**
   * Return and find an array of found info.
   * @param {string} $sql - a string represents SQL statement
   * @param {array} $name - a string represents item name
   * @param {string} $type - a string represents table type
   * @return {array} an array of found info
   */
  function find_my($sql, $name, $type) {
    $para = array("name" => $name);
    $err = "Fail to Find '$name' Info in $type Table.";
    $sql_state = exec_sql($sql, $para, $err, NULL);
    try {
      $row = $sql_state->fetch(PDO::FETCH_ASSOC);    
    }
    catch (PDOException $ex) {
      rep_db_err($err, $ex);
    }
    return $row;
  }

  /**
   * Change the given type table with the given parameter of info,
   * execute SQL with the given paramters.
   * @param {string} $sql - a string represents SQL statement
   * @param {array} $para - an array of info
   * @param {string} $type - a string represents table type
   * @param {string} $change - a string represents change operation
   */
  function change_my($sql, $para, $type, $change) {
    $name = $para["name"];
    $err = "Fail to $change '$name' Info in $type Table.";
    $ok = "$change '$name' Info in $type Table.";
    exec_sql($sql, $para, $err, $ok); 
  }

  /**
   * Return and find an array of the given SQL result,
   * execute SQL with the given parameters,
   * report 503 error if fails.
   * @param {string} $sql - a string represents sql statement
   * @param {array} $para - an array of parameters will be
   *                        filled in the given SQL statement
   * @return {array} an array of the given SQL result
   */
  function find_prof_food($sql, $para) {
    $key = str_replace("%", "", $para["key"]);
    try {
      $db = get_PDO();
      $sql_state = $db->prepare($sql);
      $sql_state->bindParam("key", $para["key"], PDO::PARAM_STR);
      $sql_state->bindParam("max", $para["max"], PDO::PARAM_INT);
      $sql_state->bindParam("min", $para["min"], PDO::PARAM_INT);
      $sql_state->bindParam("show", $para["show"], PDO::PARAM_INT);
      $sql_state->execute();
      $row = $sql_state->fetchAll(PDO::FETCH_ASSOC);  
    }
    catch (PDOException $ex) {
      $err = "Fail to Find Food with '$key' in Food Database.";
      rep_db_err($err, $ex);
    }
    return $row;
  }



  /**
   * Execute SQL with the given parameters,
   * update food info in the given database,
   * report 503 error if fails,
   * otherwise print out success.
   * @param {array} $para - an array of parameters will be
   *                        filled in the SQL statement
   */
  function update_my_food($para) {
    $sql = "UPDATE MyFood " .
           "SET abs = :abs, cal = :cal " .
           "WHERE name = :name;";
    change_my($sql, $para, "Food", "Update");
  }

  function update_my_user($para) {
    $sql = "UPDATE MyUser " .
           "SET pass = :pass, ques = :ques, ans = :ans " .
           "WHERE name = :name;";
    change_my($sql, $para, "User", "Update");
  }

  /**
   * Insert the given name and password in the given database,
   * report 503 error if fails.
   * @param {array} $para - an array of user info
   */
  function insert_my_user($para) {
    $sql = "INSERT INTO MyUser (name, pass, ques, ans) " .
           "VALUES (:name, :pass, :ques, :ans);";
    change_my($sql, $para, "User", "Add");
  }

  /**
   * Execute SQL with the given parameters,
   * insert food info into the given database,
   * report 503 error if fails,
   * otherwise print out success.
   * @param {array} $para - an array of parameters will be
   *                        filled in the SQL statement
   */
  function insert_my_food($para) {
    $sql = "INSERT INTO MyFood (name, abs, cal) " .
           "VALUES (:name, :abs, :cal);";
    change_my($sql, $para, "Food", "Add");
  }

  /**
   * Execute SQL with the given parameters,
   * delete food in the given database,
   * report 503 error if fails,
   * otherwise print out success.
   * @param {array} $para - an array of parameters will be
   *                        filled in the SQL statement
   */
  function delete_my_food($para) {
    $sql = "DELETE FROM MyFood " .
           "WHERE name = :name;";
    change_my($sql, $para, "Food", "Delete");
  }

  /**
   * Delete user account with the given parameter info.
   * @param {array} - an array of user info
   */
  function delete_my_user($para) {
    $sql = "DELETE FROM MyUser " .
           "WHERE name = :name AND pass = :pass;";
    change_my($sql, $para, "User", "Delete");
  }
?>
