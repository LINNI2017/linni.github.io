/**
 * Date: May 4, 2019
 * This is the JS to implement the UI for potato choice web page,
 * randomly generate movie/tv choice,
 * give filtered movie/tv results according to user's selection,
 * each piece of result shows poster picture,
 * overview, simple information of movie/tv.
 */

(function() {

  "use strict";

  // MODULE GLOBAL VARIABLES
  // movie database api
  const MEDIA_URL = "https://api.themoviedb.org/3/";
  // unique requested movie database api key
  const MEDIA_KEY = "9d5ec9c98168b6b7c1c3dc3fd0e0a8fd";
  // default background image
  const DEF_BACK = "url(https://images4.alphacoders.com/211/211736.jpg)";
  // random pool number
  const RAND_POOL = 1000;
  // poster image width in pixels
  const IMG_SIZE = "w200";

  // Function that will be called when the window is loaded.
  window.addEventListener("load", init);

  /**
   * Function that will be executed once the wepage loads,
   * show main page and activate buttons on the page.
   */
  function init() {
    showMain();
    id("rand-btn").addEventListener("click", function() {showResult("rand");});
    id("refresh-rand").addEventListener("click", function() {showResult("rand");});
    id("mov-btn").addEventListener("click", function() {showResult("movie");});
    id("tv-btn").addEventListener("click", function() {showResult("tv");});
    id("back-main-rand").addEventListener("click", showMain);
    id("back-main-filter").addEventListener("click", showMain);
  }

  /**
   * Show main page and clean previous results, deactivate submit button.
   */
  function showMain() {
    cleanRes();
    cleanFilter();
    id("res-view").setAttribute("rank", 0);
    id("res-view").classList.add("hidden");
    id("body").classList.remove("opac");
    id("header-h1").classList.remove("small-title");
    id("main-view").classList.remove("hidden");
    let submit = id("submit");
    submit.removeEventListener("click", buildFilter);
  }

  /**
   * Show result page and clean previous results, activate submit button.
   * @param {string} type - type of result according to user clicked.
   */
  function showResult(type) {
    cleanFilter();
    cleanRes();
    id("main-view").classList.add("hidden");
    id("res-view").classList.remove("hidden");
    id("body").classList.add("opac");
    id("header-h1").classList.add("small-title");
    if (type === "rand") {
      id("rand-sec").classList.remove("hidden");
      id("seq-sec").classList.add("hidden");
      fetchRand();
    } else {
      id("res-view").setAttribute("type", type);
      id("res-view").setAttribute("num", "multi");
      if (type === "movie") {
        id("sort-asc-tv").classList.add("hidden");
        id("sort-asc-mov").classList.remove("hidden");
      } else {
        id("sort-asc-mov").classList.add("hidden");
        id("sort-asc-tv").classList.remove("hidden");
      }
      id("seq-sec").classList.remove("hidden");
      id("rand-sec").classList.add("hidden");
      let submit = id("submit");
      submit.addEventListener("click", buildFilter);
    }
  }

  /**
   * Set default filters in result page.
   */
  function cleanFilter() {
    id("sort-asc-mov").selectedIndex = 0;
    id("sort-asc-tv").selectedIndex = 0;
    id("rel-yr").selectedIndex = 0;
    id("show-num").selectedIndex = 0;
  }

  /**
   * Remove previous results or error messages.
   */
  function cleanRes() {
    id("body").style.backgroundImage = DEF_BACK;
    let status = id("status");
    while (status.firstChild) {
      status.removeChild(status.firstChild);
    }
    let response = id("response");
    while (response.firstChild) {
      response.removeChild(response.firstChild);
    }
  }

  /**
   * Give one random movie/tv choice,
   * fetch data from a url contains random id,
   * if success then process data,
   * do json parse and show result,
   * otherwise report error.
   */
  function fetchRand() {
    let choice = ["movie", "tv"];
    let type = choice[Math.floor(Math.random() * choice.length)];
    let rand = Math.floor(Math.random() * RAND_POOL);
    id("res-view").setAttribute("type", type);
    id("res-view").setAttribute("num", "one");
    let url = MEDIA_URL
      + type
      + "/" + rand
      + "?api_key=" + MEDIA_KEY;
    fetch(url)
      .then(checkStatus) // return string of text
      .then(JSON.parse) // return javascript object
      .then(fetchOne) // process
      .catch(procErr);
  }

  /**
   * Give exactly one random movie/tv choice,
   * process fetched JSON parse data,
   * if success then show result,
   * otherwise redo fetching.
   * @param {object} data - the JSON version of data results from server.
   */
  function fetchOne(data) {
    if (!procOne(data)) {
      fetchRand();
    }
  }

  /**
   * Build up filter according to user's selections.
   */
  function buildFilter() {
    id("res-view").setAttribute("res-num", id("show-num").selectedOptions[0].value);
    id("res-view").setAttribute("rank", 0);
    cleanRes();
    let sort;
    let filter;
    let yr = id("rel-yr");

    if (id("res-view").getAttribute("type") === "movie") {
      sort = id("sort-asc-mov");
      filter = "&sort_by=" + sort.selectedOptions[0].value
        + "&primary_release_year=" + yr.selectedOptions[0].value;
    } else {
      sort = id("sort-asc-tv");
      filter = "&sort_by=" + sort.selectedOptions[0].value
        + "&first_air_date_year=" + yr.selectedOptions[0].value;
    }
    id("res-view").setAttribute("page", 1);
    id("res-view").setAttribute("filter", filter);
    fetchMedia(filter);
  }

  /**
   * Give filtered movie/tv results,
   * fetch data from a url contains user's selections,
   * if success then process data,
   * do json parse and show result,
   * otherwise report error.
   * @param {string} filter - a combination of user's selections.
   */
  function fetchMedia(filter) {
    let url = MEDIA_URL
      + "discover/"
      + id("res-view").getAttribute("type")
      + "?api_key=" + MEDIA_KEY
      + filter;
    fetch(url)
      .then(checkStatus) // return string of text
      .then(JSON.parse) // return javascript object
      .then(procMul) // process result field of object
      .catch(procErr);
  }

  /**
   * Process and show multiple movie/tv results,
   * with filter of user's selection,
   * @param {object} data - the JSON version of data results from server.
   */
  function procMul(data){
    let seq = 0;
    let resNum = Number(id("res-view").getAttribute("res-num"));

    while (resNum > 0) {
      if (seq === 20) { // note: movie db only returns 20 results each time.
        let page = 1 + Number(id("res-view").getAttribute("page"));
        id("res-view").setAttribute("page", page);
        let filter = id("res-view").getAttribute("filter") + "&page=" + page;
        fetchMedia(filter);
        break;
      } else {
        let one = data.results[seq];
        let find = procOne(one);
        if (find) {
          resNum--;
          id("res-view").setAttribute("res-num", resNum);
        }
        seq++;
      }
    }
  }

  /**
   * Process and show one movie/tv result,
   * with filter of user's selection,
   * @param {list} media - a result includes a movie/tv information.
   * @return {boolean} whether the media contains valid poster path,
   * overview and id.
   */
  function procOne(media) {
    if (!media.poster_path || !media.overview || !media.id) {
      return false;
    }
    let sec = newE("section");
    let image = addImg(sec, media.poster_path);
    addStyle(image.src);
    addAbs(sec, media.overview);
    let infoSec = newE("div");
    infoSec.classList.add("info-sec");
    let type = id("res-view").getAttribute("type");
    addCount(infoSec);
    let name = null;
    if (type === "movie") {
      name = media.title;
    } else {
      name = media.name;
    }
    addInfo(infoSec, "Name: " + name);
    image.setAttribute("alt", name + "-poster");
    addInfo(infoSec, "Media ID: " + media.id);
    addInfo(infoSec, "Vote Average: " + media.vote_average);
    addInfo(infoSec, "Vote Count: " + media.vote_count);
    addInfo(infoSec, "Language: " + media.original_language.toUpperCase());
    addInfo(infoSec, "Type: " + type.toUpperCase());
    sec.appendChild(infoSec);
    id("response").appendChild(sec);
    id("response").classList.remove("hidden");
    return true;
  }

  /**
   * Add current count/rank to result section.
   * @param {object} parent - a DOM object associate with
   * info section, the parent section of count/rank info piece.
   */
  function addCount(parent) {
    let rankNum = Number(id("res-view").getAttribute("rank")) + 1;
    if (id("res-view").getAttribute("num") === "one") {
      addInfo(parent, "Random Count: " + rankNum);
    } else {
      addInfo(parent, "Rank: " + rankNum);
    }
    id("res-view").setAttribute("rank", rankNum);
  }

  /**
   * Add style for different views,
   * for single result view:
   * set background image with the given image source;
   * for multiple results view:
   * add horizontal line to separate sections.
   * @param {string} img - an image src link.
   */
  function addStyle(img) {
    if (id("res-view").getAttribute("num") === "one") {
      id("body").style.backgroundImage = "url(" + img + ")";
    } else {
      id("response").appendChild(newE("hr"));
    }
  }

  /**
   * Add image to result section, return an image element.
   * @param {object} parent - a DOM object associate with
   * result section, the parent section of image section.
   * @param {string} child - a partial image src link of poster path.
   * @return {object} a DOM object associate with img src.
   */
  function addImg(parent, child) {
    let imgSec = newE("div");
    let image = newE("img");
    image.src = "https://image.tmdb.org/t/p/"
      + IMG_SIZE + child;
    imgSec.appendChild(image);
    parent.appendChild(imgSec);
    return image;
  }

  /**
   * Add abstract of movie/tv to result section.
   * @param {object} parent - a DOM object associate with
   * result section, the parent section of abstract section.
   * @param {string} child - an abstract of movie/tv.
   */
  function addAbs(parent, child) {
    let absSec = newE("div");
    let absH2 = newE("h2");
    absH2.innerText = "Overview";
    let abs = newE("p");
    abs.innerText = child;
    absSec.appendChild(absH2);
    absSec.appendChild(abs);
    absSec.classList.add("abs-sec");
    parent.appendChild(absSec);
  }

  /**
   * Add info piece of movie/tv to result section.
   * @param {object} parent - a DOM object associate with
   * info section, the parent section of a info piece.
   * @param {string} child - a piece of info of movie/tv.
   */
  function addInfo(parent, child) {
    let info = newE("h2");
    info.innerText = child;
    parent.appendChild(info);
  }

  /**
   * Report error when an error occurs in the fetch call chain,
   * display a user-friendly error message on the page.
   * @param {error} err - the err details of the request.
   */
  function procErr(err) {
    // ajax call failed! alert, place text and re-enable the button
    let response = newE("p");
    let msg = "There was an error requesting data from the MOVIE DB service.";
    if (err.message.includes("404")) {
      msg += "\n The resource you requested could not be found.";
    }
    msg += "\n Please try again later.";
    response.innerText = msg;
    id("status").appendChild(response);
  }

  /* ------------------------------ Helper Functions  ------------------------------ */
  // Note: You may use these in your code, but do remember that your code should not have
  // any functions defined that are unused.

  /**
   * Helper function to return the response's result text if successful, otherwise
   * returns the rejected Promise result with an error status and corresponding text
   * @param {object} response - response to check for success/error
   * @returns {object} - valid result text if response was successful, otherwise rejected
   *                     Promise result
   */
  function checkStatus(response) {
   if (response.status >= 200 && response.status < 300 || response.status === 0) {
     return response.text();
   } else {
     return Promise.reject(new Error(response.status + ": " + response.statusText));
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
   * Returns a new element with HTML tag.
   * @param {string} tag - html tag
   * @returns {object} a new DOM object associated with tag.
   */
  function newE(tag) {
    return document.createElement(tag);
  }

  // /**
  //  * Returns the first element that matches the given CSS selector.
  //  * @param {string} query - CSS query selector.
  //  * @returns {object} The first DOM object matching the query.
  //  */
  // function qs(query) {
  //   return document.querySelector(query);
  // }

  // *
  //  * Returns the array of elements that match the given CSS selector.
  //  * @param {string} query - CSS query selector
  //  * @returns {object[]} array of DOM objects matching the query.

  // function qsa(query) {
  //   return document.querySelectorAll(query);
  // }
})();
