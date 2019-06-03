/**
 * Name: LINNI CAI
 * Date: May 30, 2019
 * Section: CSE 154 AO
 * This is the JS to implement the UI for save fridge web page.
 * Allow users to login existed account and register new account.
 * After login successfully,
 * calculate and show calories for users food input,
 * give food comment and info,
 * or update food database.
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
  // mode for login
  const MODE_LOG = "mode=log";
  // mode for searcg
  const MODE_SEARCH = "mode=search";
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
  // url of main page 
  const MAIN_URL = "save_fridge_v2.html";

  let username = "";
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
  }

  function secuInput() {
    let iden = this.id;
    let opt = optValue(iden);
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
    qs(label).classList.remove("hidden");
    id(opt).classList.remove("hidden");
    id(div).classList.remove("hidden");
  }

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
    id("user-main").classList.add("hidden");
    hideAll(".reset-user-pass", true);
    id("forget-user-name").disabled = false;
    id("forget-submit-btn").classList.remove("hidden");
    id("forget-pass").classList.remove("hidden");
  }

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
    cleanRec("user-mess");
    id("forget-pass").classList.add("hidden");
    id("title").classList.remove("hidden");
    if (this && this.id.includes("forget")) {
      id("title").classList.add("small-title");
      id("forget-pass-btn").classList.remove("hidden");
      id("user-main-bar").classList.replace("width-50", "width-35");
    } else {
      id("title").classList.remove("small-title");
      id("forget-pass-btn").classList.add("hidden");
      id("user-main-bar").classList.replace("width-35", "width-50");
    }    
    id("user-main").classList.remove("hidden");
  }

  /**
   * Toggle log-in and register view by the given boolean,
   * true for log-in view, false for register view.
   * @param {boolean} log - a boolean to toggle view,
   *                        true for log-in, false for register.
   */
  function logReg(log) {
    id("user-main-bar").classList.replace("width-50", "width-35");
    id("user-main-input").classList.remove("hidden");
    id("title").classList.add("small-title");
    id("user-main-bar").classList.remove("big-btn");
    id("user-main-bar").classList.add("small-btn");
    id("user-name").value = "";
    id("user-pass").value = "";
    cleanRec("user-mess");
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
      id("reg-secu-ques-div").classList.add("hidden");
      id("reg-secu-ans").classList.add("hidden");
    } else {
      id("forget-pass-btn").classList.add("hidden");
      id("log-submit").classList.add("hidden");
      id("reg-submit").classList.remove("hidden");
      id("reg-secu-ques-div").classList.remove("hidden");
    }
  }

  function forgetFetch() {
    id("forget-user-name").disabled = true;
    username = inputValue("forget-user-name");
    let ques = optValue("forget-secu-ques");
    let ans = inputValue("forget_" + ques);
    let url = BASE_URL
      + "?" + MODE_SEARCH
      + "&type=user"
      + "&name=" + username
      + "&ques=" + ques
      + "&ans=" + ans;
    fetch(url)
      .then(getResponse)
      .then(showMess)
      .then(resetView)
      .catch(requestErr);
  }

  function resetFetch() {
    let para = [];
    para["mode"] = "update";
    para["name"] = username;
    para["pass"] = inputValue("forget-user-pass");
    para["ques"] = optValue("forget-secu-ques");
    para["ans"] = inputValue("forget_" + para["ques"]);
    regFetch(para);
  }

  function regPara() { 
    let para = [];
    para["mode"] = "reg";
    para["name"] = inputValue("user-name");
    para["pass"] = inputValue("user-pass");
    para["ques"] = optValue("reg-secu-ques");
    para["ans"] = inputValue("reg_" + para["ques"]);
    regFetch(para);
  }

  /**
   * Fetch register info from food API,
   * allow user to register if it's successful,
   * otherwise report error.
   */
  function regFetch(para) {
    let data =  new FormData();
    let mode = para["mode"];
    data.append("mode", mode);
    if (mode === "update") {
      data.append("type", "user");
    } 
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
    let name = id("user-name").value;
    let pass = id("user-pass").value;
    let url = BASE_URL
      + "?" + MODE_LOG
      + "&name=" + name
      + "&pass=" + pass;
    fetch(url)
      .then(getResponse)
      .then(showMess)
      .then(function(e) {
        window.location = MAIN_URL;
        e.preventDefault();
      })
      .catch(requestErr);
  }

  /**
   * Return the given selector's selected option value.
   * @param {string} select - a string represents a selector
   * @return {string} a string represents selected option value
   */
  function optValue(select) {
    let opt = id(select);
    return opt.options[opt.selectedIndex].value;
  }

  /**
   * Return the given selector's input value.
   * @param {string} select - a string represents a selector
   * @return {string} a string represents input value
   */
  function inputValue(select) {
    let input = id(select);
    return input.value;
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
    let error = checkErr();
    id("user-mess-sec").classList.remove("hidden");
    if (mess.startsWith("Success")) {
      id("user-mess").classList.remove("err-mess");
      id("user-mess").classList.add("succ-mess");
      id("user-mess").innerText = mess;
    } else if (mess.startsWith("Error")) {
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