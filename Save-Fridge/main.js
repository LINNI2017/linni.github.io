/**
 * Date: May 24, 2019
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
  // mode for login and register
  const MODE_LOG = "mode=log";
  // mode for food info
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

  /**
   * Add a function that will be called when the window is loaded.
   */
  window.addEventListener("load", init);

  /**
   * Initialize module-global variables,
   * keep track of user chosen option and submission.
   */
  function init() {
    mainView();
    id("add-food").addEventListener("click", addFood);
    id("login").addEventListener("click", function() {logReg(true);});
    id("register").addEventListener("click", function() {logReg(false);});
    id("log-submit").addEventListener("click", function(e) {
      e.preventDefault();
      logFetch();
    });
    id("reg-submit").addEventListener("click", function(e) {
      e.preventDefault();
      regFetch();
    });
    id("food-submit").addEventListener("click", function(e) {
      e.preventDefault();
      searchFetch();
    });
    id("food-up").addEventListener("click", updateView);
    id("up-food-btn").addEventListener("click", function(e) {
      e.preventDefault();
      updateFetch();
    });
    id("back-fridge-btn").addEventListener("click", searchView);
    id("back-main-btn").addEventListener("click", mainView);
    id("refresh-fridge").addEventListener("click", searchView);
    id("refresh-up").addEventListener("click", updateView);
  }

  /**
   * Show main view, clean previous record,
   * and initialize log-in and register form.
   */
  function mainView() {
    id("user-form").classList.remove("hidden");
    id("title").classList.remove("small-title");
    hideSec("user-form-bar", false);
    hideSec("food-info", true);
    hideSec("food-up", true);
    hideSec("food-update", true);
    hideSec("food-btn", true);
    hideSec("cal-sec", true);
    hideSec("food-result", true);
    id("user-mess").innerText = "";
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
   * Show food view, and initialize form with default value.
   */
  function searchView() {
    cleanRec("food-result");
    cleanRec("food-list");
    hideSec("food-update", true);
    hideSec("user-form", true);
    hideSec("food-btn", true);
    hideSec("cal-sec", true);
    hideSec("food-info", false);
    hideSec("food-up", false);
    hideSec("ques-bar", false);
    id("food-btn").classList.remove("hidden");

    id("food-result").innerText = "";
    id("user-mess").innerText = "";
    id("cal").innerText = 0;
    id("cal").setAttribute("cal", 0);
    id("food-list").setAttribute("num", 0);
  }

  /**
   * Show update food database view, and initialize form with default value.
   */
  function updateView() {
    id("up-food-name").value = "";
    id("up-food-abs").value = "";
    id("up-food-cal").value = "";
    id("up-food-weight").value = "";
    id("user-mess").innerText = "";
    hideSec("food-update", false);
    hideSec("food-info", true);
    hideSec("food-up", true);
    hideSec("cal-sec", true);
  }

  /**
   * Update food database by fetching from food.php,
   * update database if it's successful,
   * otherwise report error.
   */
  function updateFetch() {
    let mode = "update";
    let food = id("up-food-name").value;
    let abs = id("up-food-abs").value;
    let cal = id("up-food-cal").value;

    let weight = id("up-food-weight").value;
    cal = stdCal(cal, weight);
    let data =  new FormData();
    data.append("mode", mode);
    data.append("food", food);
    data.append("abs", abs);
    data.append("cal", cal);
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .catch(requestErr);
  }

  /**
   * Show/Hide the given selector and its children.
   * @param {string} select - a selector.
   * @param {boolean} hide - a boolean true to hide, false to show.
   */
  function hideSec(select, hide) {
    id(select).classList.toggle("hidden", hide);
    let child = id(select).children;
    for (let i = 0; i < child.length; i++) {
      child[i].classList.toggle("hidden", hide);
      let grandC = child[i].children;
      for (let j = 0; j < grandC.length; j++) {
        grandC[j].classList.toggle("hidden", hide);
      }
    }
  }

  /**
   * Fetch food info from food api,
   * process food info if it's successful,
   * otherwise report error.
   */
  function searchFetch() {
    id("food-submit").classList.add("hidden");
    hideSec("ques-bar", true);
    id("add-food").classList.add("hidden");
    hideSec("cal-sec", false);
    hideSec("food-result", false);
    let num = id("food-list").getAttribute("num");
    for (let i = 1; i <= num; i++) {
      let food = id("food-" + i).value;
      let weight = id("weight-" + i).value;
      let url = BASE_URL
        + "?" + MODE_SEARCH
        + "&food=" + food;
      fetch(url)
      .then(getResponse)
      .then(showMess)
      .then(JSON.parse)
      .then(function(list) {
        foodInfo(list, food, weight, num);
      })
      .then(removeMess)
      .catch(requestErr);
    }
  }

  /**
   * Remove success JSON response message.
   */
  function removeMess() {
    id("user-mess").innerText = "";
  }

  /**
   * Process food info and add result.
   * @param {object} list - JSON object, contains food info.
   * @param {string} food - user type-in food name.
   * @param {int} weight - user type-in food weight.
   * @param {int} num - sequence number of the current food.
   */
  function foodInfo(list, food, weight, num) {
    let abs = list["abstract"];
    let cal = list["calories"];
    let nowCal = newCal(cal, weight);
    addResult(food, abs, nowCal, num);
  }

  /**
   * Return a converted number of calories per 100g food.
   * @param {int} cal - a number represents calories.
   * @param {int} weight - a number represents weight.
   * @returns {int} returns a converted integer of calories per 100g food.
   */
  function stdCal(cal, weight) {
    return Math.round(cal * DEF_WEIGHT / weight);
  }

  /**
   * Return a converted number of calories of the given weight.
   * @param {int} cal - a number represents calories.
   * @param {int} weight - a number represents weight.
   * @returns {int} returns a converted integer of calories of given weight.
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
  function addResult(food, abs, cal, num) {
    let sec = newE("section");
    sec.id = "res-" + num;
    let par = newE("p");
    par.innerText = stName(food) + ": " + cal + " Cal, " + abs + ".";
    sec.append(par);
    id("food-result").append(sec);
    cal += Number(id("cal").getAttribute("cal"));
    id("cal").setAttribute("cal", cal);
    id("cal").innerText = cal;
  }

  /**
   * Standard format of a string, first letter with upper case,
   * rest letters with lower case,
   * return a standard format of the given string.
   * @param {string} name - a string of letters.
   * @returns {string} a standard format of the given string.
   */
  function stName(name) {
    return name.substring(0, 1).toUpperCase()
      + name.substring(1).toLowerCase();
  }

  /**
   * Toggle log-in and register view by the given boolean,
   * true for log-in view, false for register view.
   * @param {boolean} add - a boolean to toggle view,
   * true for log-in, false for register.
   */
  function logReg(add) {
    hideSec("user-form-input", false);
    id("log-submit").classList.toggle("hidden", !add);
    id("reg-submit").classList.toggle("hidden", add);
    id("title").classList.add("small-title");
    id("user-name").value = "";
    id("user-pass").value = "";
    id("user-mess").innerText = "";
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
      .then(searchView)
      .catch(requestErr);
  }

  /**
   * Display request error message.
   * @param {object} err - error
   * @return {object} err - error
   */
  function requestErr(err) {
    let status = id("user-form").getAttribute("status");
    if (status >= OK_STS_LB && status < OK_STS_UB || status === 0) {
      id("user-form").setAttribute("status", err.status);
      let mess = "Error: Busy Request, Have A Tea and Try Again Later.\n";
      showMess(mess);
    }
    errGIF();
    return err;
  }

  /**
   * Display alpaca emoji for error message.
   */
  function errGIF() {
    let img = newE("img");
    let rand = getRand();
    img.src = PIC_BASE_URL + rand + ".gif";
    img.alt = "alpaca-emoji-" + rand;
    id("user-mess").appendChild(img);
  }

  /**
   * Return a random number between 0 and the number gifs,
   * complement 0 for a number under 10.
   * @return {string} a string of a random number
   */
  function getRand() {
    let rand = "" + Math.floor(Math.random() * GIF_NUM);
    if (rand < GIF_LOW) {
      return "0" + rand;
    }
    return rand;
  }

  /**
   * Fetch register info from food api,
   * allow user to register if it's successful,
   * otherwise report error.
   */
  function regFetch() {
    let name = id("user-name").value;
    let pass = id("user-pass").value;
    let data =  new FormData();
    data.append("mode", "reg");
    data.append("name", name);
    data.append("pass", pass);
    let url = BASE_URL;
    fetch(url, {method: "POST", body: data})
      .then(getResponse)
      .then(showMess)
      .catch(requestErr);
  }

  /**
   * Add food box and label, weight box and label
   * to allow user type in more food.
   */
  function addFood() {
    let num = Number(id("food-list").getAttribute("num")) + 1;
    id("food-list").setAttribute("num", num);

    let food = newForm(MAX_TEXT_LEN, "text", num, "food");
    let weight = newForm(MAX_NUM_LEN, "number", num, "weight");

    let sec = newE("div");
    sec.appendChild(food);
    sec.appendChild(weight);

    id("food-list").appendChild(sec);

    if (num === 1) {
      id("food-up").classList.add("hidden");
      id("food-submit").classList.remove("hidden");
      id("back-main-btn").classList.remove("hidden");
      id("refresh-fridge").classList.remove("hidden");
    }
  }

  /**
   * Add box and label to allow user type in.
   * return a section packed with input and label.
   * @param {int} len - max length of user type-in content.
   * @param {string} type - type of user type-in content.
   * @param {int} num - sequence number of the current food.
   * @param {string} mark - type of section box, food box or weight box.
   * @returns {object} DOM object of a section packed with input and label.
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
     box.classList = "food-box";
     box.pattern="[a-zA-Z ]+";
     label.innerText = "Food " + num;
     sec.appendChild(label);
     sec.appendChild(box);
    } else {
      box.classList = "weight-box";
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
   * Get response by fetching from food api,
   * returns fetched text, includes error and success messae.
   * @param {object} response - response to check for success/error
   * @returns {object} - result text.
   */
  function getResponse(response) {
    id("user-form").setAttribute("status", response.status);
    return response.text();
  }

  /**
   * Show user friendly message,
   * return the response's result text if successful,
   * otherwise returns the rejected Promise result with
   * an error status and corresponding text.
   * @param {string} mess - error/success response message.
   * @returns {object} - valid result text if response
   * was successful, otherwise rejected Promise result.
   */
  function showMess(mess) {
    let status = id("user-form").getAttribute("status");
    id("user-mess-sec").classList.remove("hidden");
    id("user-mess").classList.remove("err-mess");
    id("user-mess").classList.remove("succ-mess");
    id("user-mess").innerText = mess;

    if (status >= OK_STS_LB && status < OK_STS_UB || status === 0) {
      id("user-mess").classList.add("succ-mess");
      return mess;
    }
    id("user-mess").classList.add("err-mess");
    return Promise.reject(new Error(status + ": Invalid Request"));
  }

  /* ------------------------------ Helper Functions  ------------------------------ */
  // Note: You may use these in your code, but do remember that your code should not have
  // any functions defined that are unused.

  /**
   * Returns the element that has the ID attribute with the specified value.
   * @param {string} ele - element ID
   * @returns {object} DOM object associated with id.
   */
  function id(ele) {
    return document.getElementById(ele);
  }

  /**
   * Returns a new element with HTML tag.
   * @param {string} tag - html tag
   * @returns {object} a new DOM object associated with tag.
   */
  function newE(tag) {
    return document.createElement(tag);
  }

  /**
   * Returns the first element that matches the given CSS selector.
   * @param {string} query - CSS query selector.
   * @returns {object} The first DOM object matching the query.
   */
  // function qs(query) {
  //   return document.querySelector(query);
  // }

  /**
   * Returns the array of elements that match the given CSS selector.
   * @param {string} query - CSS query selector
   * @returns {object[]} array of DOM objects matching the query.
   */
  // function qsa(query) {
  //   return document.querySelectorAll(query);
  // }
})();
