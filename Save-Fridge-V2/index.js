/**
 * Date: June 12, 2019
 * This is the JS to implement the UI for save fridge web page.
 * Allow users to login existed account and register new account.
 * After login successfully, calculate and show calories for users food input,
 * give food comment and info, update/delete food in local food database,
 * search food with filters in usda food database.
 */

(function() {
  "use strict";

  // MODULE GLOBAL VARIABLES
  // max length of input text
  const MAX_TEXT_LEN = 20;
  // max length of input number
  const MAX_NUM_LEN = 6;
  // default food weight
  const DEF_WEIGHT = 100;
  // base url for food api
  const BASE_URL = "food.php";
  // lower bound of valid status code
  const OK_STS_LB = 200;
  // upper bound of valid status code
  const OK_STS_UB = 300;
  // emoji base url
  const PIC_BASE_URL = "http://pics.sc.chinaz.com/Files/pic/faces/2402/";
  // total number of emoji gifs
  const GIF_NUM = 27;
  // lower bound of number complement with 0
  const GIF_LOW = 10;
  // forbid chosen number list
  const GIF_FORBID = [0, 14, 15, 21, 27];

  /**
   * Add a function that will be called when the window is loaded.
   */
  window.addEventListener("load", init);

  /**
   * Initialize module-global variables,
   * keep track of user chosen option and submission.
   */
  function init() {
    userView();
    userBtn();
    calcBtn();
    singleBtn();
    multiBtn();
    updateBtn();
  }

  /**
   * Add event listener for buttons on user account page.
   */
  function userBtn() {
    id("login").addEventListener("click", function() {logReg(true);});
    id("register").addEventListener("click", function() {logReg(false);});
    id("log-submit").addEventListener("click", function(e) {
      e.preventDefault();
      logFetch();
    });
    id("reg-submit").addEventListener("click", function(e) {
      e.preventDefault();
      regPara();
    });
    id("forget-pass-btn").addEventListener("click", forgetView);
    id("forget-back-btn").addEventListener("click", userView);
    id("forget-secu-ques").addEventListener("change", secuInput);
    id("reg-secu-ques").addEventListener("change", secuInput);
    id("forget-submit-btn").addEventListener("click", forgetFetch);
    id("forget-update-btn").addEventListener("click", resetFetch);
    id("delete-acc-btn").addEventListener("click", deleteView);
    id("delete-submit-btn").addEventListener("click", deleteUserFetch);
    id("delete-back-btn").addEventListener("click", userView);
    id("food-up-delete-btn").addEventListener("click", deleteFoodFetch);
    id("tog-pass").addEventListener("click", togPass);
  }

  /**
   * Toggle the view of password, visible or hidden.
   */
  function togPass() {
    let pass = id("user-pass");
    if (id("tog-pass").checked == true) {
      pass.type = "text";
    } else {
      pass.type = "password";
    }
  }

  /**
   * Display delete view, allow users to delete account with valid user info.
   */
  function deleteView() {
    cleanRec("user-mess");
    id("delete-user-name").value = "";
    id("delete-user-pass").value = "";
    id("user-main").classList.add("hidden");
    id("delete-acc").classList.remove("hidden");
    id("delete-btn-bar").classList.remove("hidden");
    let labels = qsa("#delete-acc label");
    for (let i = 0; i < labels.length; i++) {
      let label = labels[i];
      label.classList.remove("hidden");
    }
  }

  /**
   * Fetch the response from api, use POST info to delete food in the database.
   */
  function deleteFoodFetch() {
    cleanRec("food-update-result");
    id("food-update-result").classList.add("hidden");
    let data =  new FormData();
    data.append("base", "food");
    data.append("mode", "delete");
    data.append("name", id("food-up-name").value);
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .catch(requestErr);
  }

  /**
   * Fetch the response from api, use POST info to delete user in the database.
   */
  function deleteUserFetch() {
    let data =  new FormData();
    data.append("base", "user");
    data.append("mode", "delete");
    data.append("name", id("delete-user-name").value);
    data.append("pass", id("delete-user-pass").value);
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .then(resetView)
      .catch(requestErr);
  }

  /**
   * Display security question and answer box to type in,
   * clean previous records,
   */
  function secuInput() {
    let iden = this.id;
    let opt = id(iden).value;
    let div = "-secu-ans";
    let label = "-secu-ans label";
    if (iden.includes("reg")) {
      opt = "reg_" + opt;
      div = "reg" + div;
      label = "#reg" + label;
      hideAll("#reg-secu-ans input", true);
    } else {
      opt = "forget_" + opt;
      div = "forget" + div;
      label = "#forget" + label;
      hideAll("#forget-secu-ans input", true);
    }
    if (opt.includes("ques-def")) {
      id(div).classList.add("hidden");
    } else {
      qs(label).classList.remove("hidden");
      id(opt).classList.remove("hidden");
      id(opt).value = "";
      id(div).classList.remove("hidden");
    }
  }

  /**
   * Display forget view, allow users to reset password with valid user info.
   */
  function forgetView() {
    cleanRec("user-mess");
    id("forget-secu-ques").selectedIndex = 0;
    hideAll("#forget-secu-ans *", true);
    let ques = qsa("#forget-secu-ans input");
    for (let i = 0; i < ques.length; i++) {
      let q = ques[i];
      q.value = "";
    }
    id("forget-user-name").value = "";
    id("forget-user-pass").value = "";
    id("forget-btn-bar").classList.remove("hidden");
    id("user-main").classList.add("hidden");
    hideAll(".reset-user-pass", true);
    id("forget-user-name").disabled = false;
    id("forget-submit-btn").classList.remove("hidden");
    id("forget-pass").classList.remove("hidden");
  }

  /**
   * Display or hide the given selector's children.
   * @param {string} select - a string represents a selector.
   * @param {boolean} hide - true to hide the given selector's children,
   *                         false to display the given selector's children.
   */
  function hideAll(select, hide) {
    qsa(select).forEach(function(e) {
      e.classList.toggle("hidden", hide);
    });
  }

  /**
   * Show main view, clean previous record,
   * and initialize log-in and register form.
   */
  function userView() {
    cleanAcc();
    hideAll(".bar", true);
    id("log-out").classList.add("hidden");
    id("food-main").classList.add("hidden");
    id("forget-pass").classList.add("hidden");
    id("title").classList.remove("hidden");
    id("delete-acc").classList.add("hidden");
    if (this && this.id.includes("forget")) {
      id("title").classList.add("small-title");
      id("forget-pass-btn").classList.remove("hidden");
      id("user-main-input").classList.remove("hidden");
      id("user-main-bar").classList.replace("width-40", "width-29");
      id("user-main-btn-bar").classList.remove("hidden");
    } else {
      id("user-main-input").classList.add("hidden");
      id("title").classList.remove("small-title");
      id("forget-pass-btn").classList.add("hidden");
      id("user-main-bar").classList.replace("width-29", "width-40");
      id("user-main-btn-bar").classList.add("hidden");
    }
    id("user-main").classList.remove("hidden");
  }

  /**
   * Clean previous account record, and set password securely display.
   */
  function cleanAcc() {
    cleanRec("user-mess");
    id("tog-pass").checked = false;
    id("user-name").value = "";
    id("user-pass").value = "";
    id("user-pass").type = "password";
  }

  /**
   * Toggle log-in and register view by the given boolean,
   * true for log-in view, false for register view.
   * @param {boolean} log - a boolean to toggle view,
   *                        true for log-in, false for register.
   */
  function logReg(log) {
    cleanAcc();
    id("user-main-btn-bar").classList.remove("hidden");
    id("user-main-bar").classList.replace("width-40", "width-29");
    id("user-main-input").classList.remove("hidden");
    id("title").classList.add("small-title");
    id("user-main-bar").classList.remove("big-btn");
    id("user-main-bar").classList.add("small-btn");
    id("reg-secu-ques").selectedIndex = 0;
    hideAll("#reg-secu-ans *", true);
    let ques = qsa("#reg-secu-ans input");
    for (let i = 0; i < ques.length; i++) {
      let q = ques[i];
      q.value = "";
    }
    if (log) {
      id("log-submit").classList.remove("hidden");
      id("reg-submit").classList.add("hidden");
      id("forget-pass-btn").classList.remove("hidden");
      id("delete-acc-btn").classList.add("hidden");
      id("reg-secu-ques-div").classList.add("hidden");
      id("reg-secu-ans").classList.add("hidden");
    } else {
      id("forget-pass-btn").classList.add("hidden");
      id("log-submit").classList.add("hidden");
      id("delete-acc-btn").classList.remove("hidden");
      id("reg-submit").classList.remove("hidden");
      id("reg-secu-ques-div").classList.remove("hidden");
    }
  }

  /**
   * Display log-in view if given hide true,
   * display register view if given hide false.
   * @param {boolean} hide - true to display log-in view,
   *                         false to display register view
   */
  function togUser(hide) {
    id("log-submit").classList.toggle("hidden", !hide);
    id("reg-submit").classList.toggle("hidden", hide);
    id("forget-pass-btn").classList.toggle("hidden", !hide);
    id("delete-acc-btn").classList.toggle("hidden", hide);
    id("reg-secu-ques-div").classList.toggle("hidden", hide);
    id("reg-secu-ans").classList.toggle("hidden", hide);
  }

  /**
   * Fetch the response from api, use POST info to find existed user info,
   * display reset view for valid and matched user security question and answer,
   * otherwise display error message.
   */
  function forgetFetch() {
    id("forget-user-name").disabled = true;
    let data =  new FormData();
    data.append("base", "user");
    data.append("mode", "search");
    data.append("name", id("forget-user-name").value);
    let ques = id("forget-secu-ques").value;
    if (quesCheck(ques)) {
      data.append("ques", ques);
      data.append("ans", id("forget_" + ques).value);
      let url = BASE_URL;
      fetch(url, {method: "POST", body: data})
        .then(getResponse)
        .then(showMess)
        .then(resetView)
        .catch(requestErr);
    }
  }

  /**
   * Check user selected security question, 
   * return true with valid selected security question,
   * otherwise return false and display error message 
   * with invalid selected security question.
   * @param {string} ques - a string represents a security question
   * @return {boolean} true for a valid security question, false otherwise
   */
  function quesCheck(ques) {
    if (ques.includes("ques-def")) {
      let mess = "Error: Select One Security Question.\n";
      showMess(mess, false);
      errGIF();
      return false;
    }
    return true;
  }

  /**
   * Fetch result for reset account info.
   */
  function resetFetch() {
    let para = [];
    para["mode"] = "update";
    para["name"] = id("forget-user-name").value;
    para["pass"] = id("forget-user-pass").value;
    para["ques"] = id("forget-secu-ques").value;
    para["ans"] = id("forget_" + para["ques"]).value;
    regFetch(para);
  }

  /**
   * Get parameters for register mode, 
   * check validity of security question and answer.
   */
  function regPara() {
    let para = [];
    para["mode"] = "reg";
    para["name"] = id("user-name").value;
    para["pass"] = id("user-pass").value;
    para["ques"] = id("reg-secu-ques").value;
    let ques = para["ques"];
    if (quesCheck(ques)) {
      para["ans"] = id("reg_" + ques).value;
      regFetch(para);
    }
  }

  /**
   * Fetch register info from food API,
   * allow user to register if it's successful,
   * otherwise report error.
   * @param {array} para - an array of parameters
   */
  function regFetch(para) {
    let data =  new FormData();
    data.append("base", "user");
    data.append("mode", para["mode"]);
    data.append("name", para["name"]);
    data.append("pass", para["pass"]);
    data.append("ques", para["ques"]);
    data.append("ans", para["ans"]);
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .catch(requestErr);
  }

  /**
   * Display view for reset password.
   */
  function resetView() {
    hideAll(".reset-user-pass", false);
    id("forget-submit-btn").classList.add("hidden");
  }

  /**
   * Fetch log-in info from food api,
   * allow user to log in if it's successful,
   * otherwise report error.
   */
  function logFetch() {
    let data =  new FormData();
    data.append("base", "user");
    data.append("mode", "log");
    data.append("name", id("user-name").value);
    data.append("pass", id("user-pass").value);
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .then(mainView)
      .catch(requestErr);
  }

  /**
   * Add event listener for buttons on food update page.
   */
  function updateBtn() {
    id("food-up-btn").addEventListener("click", updateView);
    id("food-up-add-btn").addEventListener("click", function(e) {
      e.preventDefault();
      addFetch();
    });
    id("food-up-back-btn").addEventListener("click", mainView);
    id("food-up-refresh-btn").addEventListener("click", updateView);
    id("food-up-update-btn").addEventListener("click", function(e) {
      e.preventDefault();
      updateFoodFetch();
    });
  }

  /**
   * Add event listener for buttons on food calculation page.
   */
  function calcBtn() {
    id("food-calc-btn").addEventListener("click", calcView);
    id("add-food").addEventListener("click", addFood);
    id("calc-submit-btn").addEventListener("click", function(e) {
      e.preventDefault();
      calcFetch();
    });
    id("calc-back-btn").addEventListener("click", calcView);
    id("calc-refresh-btn").addEventListener("click", calcView);
    id("log-out").addEventListener("click", userView);
  }

  /**
   * Add event listener for buttons on food multi search page.
   */
  function multiBtn() {
    id("food-multi-search-btn").addEventListener("click", multiView);
    id("multi-search-btn").addEventListener("click", function(e) {
      e.preventDefault();
      multiFetch();
    });
    id("multi-back-btn").addEventListener("click", mainView);
    id("multi-refresh-btn").addEventListener("click", multiView);
  }

  /**
   * Add event listener for buttons on food single search page.
   */
  function singleBtn() {
    id("food-single-search-btn").addEventListener("click", singleView);
    id("single-search-btn").addEventListener("click", function(e) {
      e.preventDefault();
      singleFetch();
    });
    id("single-back-btn").addEventListener("click", mainView);
    id("single-refresh-btn").addEventListener("click", singleView);
  }

  /**
   * Return a string represents url of food multi search,
   * fill the url with user input.
   * @return {string} a string represents url of food multi search
   */
  function multiData() {
    let data =  new FormData();
    let show = id("multi-show").value;
    let min = id("multi-cal-min").value;
    let max = id("multi-cal-max").value;
    let key = id("multi-key").value;
    let table = id("multi-type").value;
    let macro = id("multi-macro").value;
    let maorder = id("macro-order").value;
    let micro = id("multi-micro").value;
    let miorder = id("micro-order").value;
    data.append("base", "food");
    data.append("mode", "msearch");
    data.append("table", table);
    data.append("show", show);
    data.append("macro", macro);
    data.append("micro", micro);
    data.append("maorder", maorder);
    data.append("miorder", miorder);
    data.append("min", min);
    data.append("max", max);
    data.append("key", key);
    return data;
  }

  /**
   * Fetch and display the food multi search result from food API,
   * otherwise report error.
   */
  function multiFetch() {
    cleanRec("user-mess");
    cleanRec("food-multi-search-result");
    let data =  multiData();
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .then(JSON.parse)
      .then(multiRes)
      .then(function() {
        id("food-multi-search-result").classList.remove("hidden");
      })
      .catch(requestErr);
  }

  /**
   * Display the result of the given list.
   * @param {array} list - an array of food info
   */
  function multiRes(list) {
    let table = newE("table");
    let keys = null;
    let keysLen = null;
    for (let i = 0; i < list.length; i++) {
      let item = list[i];
      if (i === 0) {
        let tr = newE("tr");
        keys = Object.keys(item);
        keysLen = keys.length;

        for (let j = 0; j < keysLen; j++) {
          let key = keys[j];
          if (key) {
            let th = newE("th");
            th.innerText = keys[j];
            tr.appendChild(th);
          }
        }
        table.appendChild(tr);
      }
      let tr = newE("tr");
      for (let j = 0; j < keysLen; j++) {
        let key = keys[j];
        let value = item[key];
        if(value || value === 0) {
          let td = newE("td");
          td.innerText = value;
          tr.appendChild(td);
        }
      }
      table.appendChild(tr);
    }
    id("food-multi-search-result").appendChild(table);
  }

  /**
   * Display food multi search page,
   * clear previous records,
   * display empty input box for refresh page,
   * otherwise diplay default values for easy-intro.
   */
  function multiView() {
    let iden = this.id;
    id("multi-type").selectedIndex = 0;
    id("multi-macro").selectedIndex = 0;
    id("macro-order").selectedIndex = 0;
    id("multi-micro").selectedIndex = 0;
    id("micro-order").selectedIndex = 0;
    if (iden.includes("refresh")) {
      id("multi-show").value = "";
      id("multi-cal-min").value = "";
      id("multi-cal-max").value = "";
      id("multi-key").value = "";
    } else {
      id("multi-show").value = 5;
      id("multi-cal-min").value = 10;
      id("multi-cal-max").value = 1000;
      id("multi-key").value = "beef";
    }
    cleanRec("food-multi-search-result");
    cleanRec("user-mess");
    id("multi-btn-bar").classList.remove("hidden");
    id("food-main-btn-bar").classList.add("hidden");
    id("food-search-multi").classList.remove("hidden");
  }

  /**
   * Show food view, and initialize form with default value.
   */
  function mainView() {
    cleanRec("user-mess");
    hideAll(".bar", true);
    id("log-out").classList.remove("hidden");
    id("user-main").classList.add("hidden");
    id("food-main").classList.remove("hidden");
    id("food-calc").classList.add("hidden");
    id("cal-sec").classList.add("hidden");
    id("calc-result-sec").classList.add("hidden");
    id("food-search-single").classList.add("hidden");
    id("food-update").classList.add("hidden");
    id("food-search-multi").classList.add("hidden");
    id("food-multi-search-result").classList.add("hidden");
    id("food-single-search-result").classList.add("hidden");
    id("food-update-result").classList.add("hidden");
    id("food-main-btn-bar").classList.remove("hidden");
  }

  /**
   * Fetch and display food single search result from food API,
   * otherwise report error.
   */
  function singleFetch() {
    cleanRec("user-mess");
    cleanRec("food-single-search-result");
    let food = id("single-name").value;
    let data =  new FormData();
    data.append("base", "food");
    data.append("mode", "ssearch");
    data.append("name", food);
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .then(JSON.parse)
      .then(singleRes)
      .then(function() {
        id("food-single-search-result").classList.remove("hidden");
      })
      .catch(requestErr);
  }

  /**
   * Display food sungle search page,
   * clear previous records,
   * display empty input box for refresh page,
   * otherwise diplay default values for easy-intro.
   */
  function singleView() {
    let iden = this.id;
    if (iden.includes("refresh")) {
      id("single-name").value = "";
    } else {
      id("single-name").value = "apple";
    }
    id("single-btn-bar").classList.remove("hidden");
    id("food-main-btn-bar").classList.add("hidden");
    id("food-search-single").classList.remove("hidden");
    cleanRec("food-single-search-result");
  }

  /**
   * Fetch food info from food api,
   * process food info if it's successful,
   * otherwise report error.
   */
  function calcFetch() {
    let num = Number(id("food-calc-input").getAttribute("num"));
    if (num === 0) {
      let mess = "Error: Nothing in Your Fridge.\n";
      showMess(mess, false);
      errGIF();
    }
    id("calc-submit-btn").classList.add("hidden");
    id("calc-refresh-btn").classList.add("hidden");
    id("cal-ques-bar").classList.add("hidden");
    id("food-calc-input").classList.add("hidden");
    for (let i = 1; i <= num; i++) {
      let food = id("food-" + i).value;
      let weight = id("weight-" + i).value;
      let data =  new FormData();
      data.append("base", "food");
      data.append("mode", "ssearch");
      data.append("name", food);
      let url = BASE_URL;
      fetch(url, {method: "POST", body: data})
        .then(getResponse)
        .then(showMess)
        .then(JSON.parse)
        .then(function(list) {
          foodCalc(list, food, weight, num);
        })
        .then(function() {
          id("calc-result-sec").classList.remove("hidden");
          id("cal-sec").classList.remove("hidden");
        })
        .catch(requestErr);
    }
  }

  /**
   * Display food multi search page,
   * clear previous records.
   */
  function calcView() {
    let iden = this.id;
    if (iden === "calc-back-btn"
        && !id("calc-submit-btn").classList.contains("hidden")) {
      mainView();
    } else {
      if (iden !== "calc-back-btn") {
        cleanRec("food-calc-input");
        id("food-calc-input").setAttribute("num", 0);
      }
      cleanRec("user-mess");
      cleanRec("food-calc-result");
      id("cal").setAttribute("cal", 0);
      id("food-main-btn-bar").classList.add("hidden");
      id("food-calc-btn-bar").classList.add("hidden");
      id("calc-result-sec").classList.add("hidden");
      id("cal-sec").classList.add("hidden");
      id("cal-ques-bar").classList.remove("hidden");
      id("title").classList.remove("hidden");
      id("food-calc").classList.remove("hidden");
      id("food-calc-input").classList.remove("hidden");
      id("calc-submit-btn").classList.remove("hidden");
      id("calc-refresh-btn").classList.remove("hidden");
      id("food-calc-btn-bar").classList.remove("hidden");
    }
  }

  /**
   * Display food update page,
   * clear previous records,
   * display empty input box for refresh page,
   * otherwise diplay default values for easy-intro.
   */
  function updateView() {
    let iden = this.id;
    if (iden.includes("refresh")) {
      id("food-up-name").value = "";
      id("food-up-abs").value = "";
      id("food-up-cal").value = "";
      id("food-up-weight").value = "";
    } else {
      id("food-up-name").value = "Apple";
      id("food-up-abs").value = "Sweet & Crispy & Daily Fruit Choice";
      id("food-up-cal").value = 52;
      id("food-up-weight").value = 100;
    }
    cleanRec("food-update-result");
    cleanRec("user-mess");
    id("food-up-add-btn").classList.remove("hidden");
    id("food-up-btn-bar").classList.remove("hidden");
    id("food-up-update-btn").classList.add("hidden");
    id("food-main-btn-bar").classList.add("hidden");
    id("food-update").classList.remove("hidden");
  }

  /**
   * Fetch and insert new food into food database in food API,
   * otherwise report error.
   */
  function addFetch() {
    cleanRec("user-mess");
    let name = id("food-up-name").value;
    let abs = id("food-up-abs").value;
    let cal = id("food-up-cal").value;
    let weight = id("food-up-weight").value;
    cal = stdCal(cal, weight);
    let data =  new FormData();
    data.append("base", "food");
    data.append("mode", "add");
    data.append("name", name);
    data.append("abs", abs);
    data.append("cal", cal);
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .catch(requestErr);
  }

  /**
   * Switch add button to update button,
   * allow user to update existed food info.
   * @param {array} list - an array of food info
   */
  function addUpdate(list) {
    let name = list["name"];
    name = stdName(name);
    let abs = list["abs"];
    let cal = list["cal"];
    let sec = newE("section");
    let par = newE("p");
    par.innerText = "Food: " + name + "\nCalories: " + cal
      + "\nAbstract: " + abs + "."
      + "\n" + name + " already exists in our Food Database."
      + "\nUse Update to override previous record.";
    sec.appendChild(par);
    id("food-update-result").appendChild(sec);
    id("food-update-result").classList.remove("hidden");
  }

  /**
   * Fetch and update existed food info into food database in food API,
   * otherwise report error.
   */
  function updateFoodFetch() {
    cleanRec("user-mess");
    cleanRec("food-update-result");
    let name = id("food-up-name").value;
    let abs = id("food-up-abs").value;
    let cal = id("food-up-cal").value;
    let weight = id("food-up-weight").value;
    cal = stdCal(cal, weight);
    let data =  new FormData();
    data.append("base", "food");
    data.append("mode", "update");
    data.append("name", name);
    data.append("abs", abs);
    data.append("cal", cal);
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .then(function(){
        id("food-up-add-btn").classList.remove("hidden");
        id("food-up-update-btn").classList.add("hidden");
      })
      .catch(requestErr);
  }

  /**
   * Process food info and add result.
   * @param {object} list - JSON object, contains food info.
   * @param {string} food - user type-in food name.
   * @param {int} weight - user type-in food weight.
   * @param {int} num - sequence number of the current food.
   */
  function foodCalc(list, food, weight, num) {
    let abs = list["abs"];
    let cal = list["cal"];
    let nowCal = newCal(cal, weight);
    calcRes(food, abs, nowCal, num);
  }

  /**
   * Display single food result of the given list.
   * @param {array} list - an array of food info
   */
  function singleRes(list) {
    let name = list["name"];
    let abs = list["abs"];
    let cal = list["cal"];
    let sec = newE("section");
    let par = newE("p");
    par.innerText = "Food: " + stdName(name)
      + "\nCalories: " + cal + "\nAbstract: " + abs + ".";
    sec.appendChild(par);
    id("food-single-search-result").appendChild(sec);
  }

  /**
   * Return a converted number of calories per 100g food.
   * @param {int} cal - a number represents calories.
   * @param {int} weight - a number represents weight.
   * @return {int} returns a converted integer of calories per 100g food.
   */
  function stdCal(cal, weight) {
    return Math.round(cal * DEF_WEIGHT / weight);
  }

  /**
   * Return a converted number of calories of the given weight.
   * @param {int} cal - a number represents calories.
   * @param {int} weight - a number represents weight.
   * @return {int} returns a converted integer of calories of given weight.
   */
  function newCal(cal, weight) {
    return Math.round(cal * weight / DEF_WEIGHT);
  }

  /**
   * Show result and current total calories.
   * @param {string} food - user type-in food name.
   * @param {string} abs - fetched food abstract.
   * @param {int} cal - food calories based on user type-in weight.
   * @param {int} num - sequence number of the current food.
   */
  function calcRes(food, abs, cal) {
    let sec = newE("section");
    let iden = "res-" + food;
    let totalCal = Number(id("cal").getAttribute("cal")) + cal;
    id("cal").setAttribute("cal", totalCal);
    id("cal").innerText = totalCal;
    if (!id(iden)) {
      sec.id = "res-" + food;
      let par = newE("p");
      par.innerText = stdName(food) + ": " + cal + " Cal, " + abs + ".";
      sec.appendChild(par);
      id("food-calc-result").appendChild(sec);
    } else { // added food
      let rec = qs("#" + iden + " p");
      let recData = rec.innerText;
      let calStart = recData.indexOf(":") + 2;
      let calEnd = recData.indexOf(" Cal");
      let preCal = recData.substring(calStart, calEnd);
      let newCal = Number(preCal) + cal;
      rec.innerText = recData.replace(preCal, newCal);
    }
  }

  /**
   * Standard format of a string, first letter with upper case,
   * rest letters with lower case,
   * return a standard format of the given string.
   * @param {string} name - a string of letters.
   * @return {string} a standard format of the given string.
   */
  function stdName(name) {
    return name.substring(0, 1).toUpperCase()
      + name.substring(1).toLowerCase();
  }

  /**
   * Add food box and label, weight box and label
   * to allow user type in more food.
   */
  function addFood() {
    let num = Number(id("food-calc-input").getAttribute("num")) + 1;
    id("food-calc-input").setAttribute("num", num);
    let food = newForm(MAX_TEXT_LEN, "text", num, "food");
    let weight = newForm(MAX_NUM_LEN, "number", num, "weight");
    let sec = newE("div");
    sec.appendChild(food);
    sec.appendChild(weight);
    id("food-calc-input").appendChild(sec);
  }

  /**
   * Add box and label to allow user type in.
   * return a section packed with input and label.
   * @param {int} len - max length of user type-in content.
   * @param {string} type - type of user type-in content.
   * @param {int} num - sequence number of the current food.
   * @param {string} mark - type of section box, food box or weight box.
   * @return {object} DOM object of a section packed with input and label.
   */
  function newForm(len, type, num, mark) {
    let sec = newE("span");
    let label = newE("label");
    let box = newE("input");
    label.for = mark + "-" + num;
    box.type = type;
    sec.id = mark + "-sec-" + num;
    box.id = mark + "-" + num;
    box.maxlength = len;
    if (mark === "food") {
     box.pattern="[a-zA-Z ]+";
     label.innerText = "Food " + num;
     sec.appendChild(label);
     sec.appendChild(box);
     sec.classList.add("width-20");
    } else {
      box.classList = "weight-box";
      label.classList = "weight-label";
      box.value = 100;
      box.min = 1;
      box.max = 1000;
      label.innerText = "(g)";
      sec.appendChild(box);
      sec.appendChild(label);
    }
    return sec;
  }

  /**
   * Clean the given selector's children.
   * @param {string} select - a selector.
   */
  function cleanRec(select) {
    let sec = id(select);
    while(sec.firstChild) {
      sec.removeChild(sec.firstChild);
    }
  }

  /**
   * Get response by fetching from food api,
   * returns fetched text, includes error and success messae.
   * @param {object} response - response to check for success/error
   * @return {object} - result text.
   */
  function getResponse(response) {
    id("user-main").setAttribute("status", response.status);
    return response.text();
  }

  /**
   * Show user friendly message,
   * return the response's result text if successful,
   * otherwise returns the rejected Promise result with
   * an error status and corresponding text.
   * @param {string} mess - error/success response message
   * @param {boolean} end - true if script will be terminated after this function,
   *                        don't return promise reject, otherwise false.
   * @return {object} - valid result text if response is successful,
   *                    otherwise rejected Promise result.
   */
  function showMess(mess, end) {
    id("title").classList.remove("hidden");
    if (end == null) {
      end = false;
    }
    if (mess.includes("Existed Food")) {
      id("food-up-add-btn").classList.add("hidden");
      id("food-up-update-btn").classList.remove("hidden");
      let list = mess.split("\n");
      let info = JSON.parse(list[0]);
      mess = list[1] + "\n";
      addUpdate(info);
    } else if (mess.includes("Unknown Food")) {
      id("food-up-add-btn").classList.remove("hidden");
      id("food-up-update-btn").classList.add("hidden");
    }
    let error = checkErr();
    id("user-mess-sec").classList.remove("hidden");
    if (mess.includes("Success")) {
      id("user-mess").classList.remove("err-mess");
      id("user-mess").classList.add("succ-mess");
      id("user-mess").innerText = mess;
    } else if (mess.includes("Error")) {
      id("user-mess").classList.add("err-mess");
      id("user-mess").classList.remove("succ-mess");
      id("user-mess").innerText = mess;
    }
    if (!end && error) {
      return Promise.reject(new Error(status + ": Invalid Request"));
    } else {
      return mess;
    }
  }

  /**
   * Display request error message,
   * return the given err.
   * @param {object} err - error
   * @return {object} err - error
   */
  function requestErr(err) {
    let mess = "";
    if (id("user-mess").innerText === "") {
      mess += "Error: Busy Request, Have A Tea and Try Again Later.\n";
    }
    showMess(mess, true);
    errGIF();
    return err;
  }

  /**
   * Check status code and return true for success,
   * otherwise return false.
   * @return {boolean} true for success, false for fail
   */
  function checkErr() {
    let status = id("user-main").getAttribute("status");
    if (status >= OK_STS_LB && status < OK_STS_UB || status === 0) {
      return false;
    } else {
      return true;
    }
  }

  /**
   * Display alpaca emoji for error message.
   */
  function errGIF() {
    let img = qs("#user-mess img");
    let add = false;
    if (!img) {
      img = newE("img");
      add = true;
    }
    let rand = getRand();
    img.src = PIC_BASE_URL + rand + ".gif";
    img.alt = "alpaca-emoji-" + rand;
    if (add) {
      id("user-mess").appendChild(img);
    }
  }

  /**
   * Return a random number between 0 and the number gifs,
   * complement 0 for a number under 10.
   * @return {string} a string represents a random number
   */
  function getRand() {
    let rand = 0;
    while (GIF_FORBID.includes(rand)) {
      rand = Math.floor(Math.random() * GIF_NUM) + 1;
    }
    if (rand < GIF_LOW) {
      return "0" + rand;
    }
    return "" + rand;
  }

  /* ------------------------------ Helper Functions  ----------------------- */
  // Note: You may use these in your code, but do remember that your code
  // should not have any functions defined that are unused.

  /**
   * Returns the element that has the ID attribute with the specified value.
   * @param {string} ele - element ID
   * @return {object} DOM object associated with id.
   */
  function id(ele) {
    return document.getElementById(ele);
  }

  /**
   * Returns a new element with HTML tag.
   * @param {string} tag - html tag
   * @return {object} a new DOM object associated with tag.
   */
  function newE(tag) {
    return document.createElement(tag);
  }

  /**
   * Returns the first element that matches the given CSS selector.
   * @param {string} query - CSS query selector.
   * @return {object} The first DOM object matching the query.
   */
  function qs(query) {
    return document.querySelector(query);
  }

  /**
   * Returns the array of elements that match the given CSS selector.
   * @param {string} query - CSS query selector
   * @return {object[]} array of DOM objects matching the query.
   */
  function qsa(query) {
    return document.querySelectorAll(query);
  }
})();