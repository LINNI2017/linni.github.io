/**
 * Date: April 20, 2019
 * This is the JS to implement the UI for daily meal plan web page,
 * calculate calories of selected, and give health comment for
 * selected meal plan from user input.
 */

/* global Map */

(function() {
  "use strict";

  // MODULE GLOBAL VARIABLES
  // Get category selected option
  let cate;
  // Get item selected option
  let item;
  // Get add-on selected option
  let add;
  // Get submit buttom access
  let submit;
  // Store selected food in a list
  let food;
  // Store total calories
  let cal;
  // Store nutrition info in a map
  // each item has a calories-info
  let nuMap;

  /**
   * Add a function that will be called when the window is loaded.
   */
  window.addEventListener("load", init);

  /**
  * Initialize module-global variables,
  * keep track of user chosen option and submission.
  */
  function init() {
    food=[];
    cal=0;
    nuMap = new Map();
    nuInfo();
    cate = id("cate");
    cate.addEventListener("change", pickCate);
    submit = id("submit");
  }

  /**
  * Update module-global map with nutrition information,
  * food map with its calories per 100g.
  */
  function nuInfo() {
    nuMap.set("Apple",52);
    nuMap.set("Orange",49);
    nuMap.set("Romain Lettuce", 17);
    nuMap.set("Oyster Mushroom", 33);
    nuMap.set("Beer", 43);
    nuMap.set("Pork", 238);
    nuMap.set("Beef", 291);
    nuMap.set("Milk Tea Boba", 48);
    nuMap.set("Fried Fish", 229);
    nuMap.set("Kettle Chips", 532);
  }

  /**
  * Give health comment for final total calories of selected food,
  * compared with some threshold of daily calories for a woman to lose weight.
  * note: this threshold is not scientifically proved.
  */
  function showCom() {
    let editField = addP();
    if (cal < 1000) {
      editField.innerHTML = "Hungry, GRAB SOME FOOD!";
    } else if (cal <= 1500) {
      editField.innerHTML = "Daily Satisfied Plan!";
    } else if (cal > 1500) {
      editField.innerHTML = "Too much, REDUCE SOME FOOD!";
    }
    id("result").classList.remove("hidden");
    let result = id("comment");
    result.appendChild(editField);
    submit.classList.add("hidden");
    id("choice").classList.add("hidden");
    showSelect();
  }

  /**
  * Give final selected food list.
  */
  function showSelect() {
    let editField = addP();
    let result = id("selected");
    for (let i = 0; i < food.length; i++) {
      editField = document.createElement("p");
      editField.innerHTML = food[i] + " 100g , " + nuMap.get(food[i]) + " Cal";
      result.appendChild(editField);
    }
  }

  /**
  * Get user chosen type of food, show new selection box of it.
  */
  function pickCate() {
    let itemN = opt(cate);
    let itemQ = id(itemN+"Q");
    item = id(itemN);
    item.selectedIndex = 0;
    item.disabled = false;
    itemQ.classList.remove("hidden");
    item.addEventListener("change", pickItem);
    cate.disabled = true;
  }

  /**
  * Get user chosen item, update selected food list, show new add-on box.
  */
  function pickItem() {
    let newItem = opt(item);
    calCheck(newItem);
    food.push(newItem);
    item.disabled = true;
    let addQ = id("addQ");
    addQ.classList.remove("hidden");
    add = id("add");
    add.selectedIndex = 0;
    add.disabled = false;
    add.addEventListener("change", pickAdd);
  }

  /**
  * Get user chosen add-on option.
  */
  function pickAdd() {
    add.disabled = true;
    if (opt(add) === "yes") {
      cate.selectedIndex = 0;
      cate.disabled = false;
      cate.addEventListener("change", pickCate);
    } else {
      submit.classList.remove("hidden");
      submit.addEventListener("click", showCom);
    }
  }

  /**
  * Update total calories of selected food list.
  * @param {string} newItem - new selected food name
  */
  function calCheck(newItem) {
    let itemCal = nuMap.get(newItem);
    if (itemCal) {
      cal = Number(id("cal").innerHTML) + itemCal;
      id("cal").innerHTML = cal;
    }
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
   * Returns the selected option value of the given selection object.
   * @param {object} ele - DOM object associated with selection.
   * @returns {string} selected option value of the given object.
   */
  function opt(ele) {
    return ele.options[ele.selectedIndex].text;
  }

  /**
   * Returns a DOM object represents an HTML element P with some class style.
   * @returns {object} DOM object with submit style and represents a p-element.
   */
  function addP() {
    let editField = document.createElement("p");
    editField.classList.add("submit");
    return editField;
  }
})();
