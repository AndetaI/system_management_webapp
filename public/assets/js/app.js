"use strict";

/*
 * Barkea Resort
 * Base frontend JavaScript
 */

document.addEventListener("DOMContentLoaded", function () {

    const searchForm = document.getElementById("searchForm");

    if (searchForm) {
        searchForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const searchInput = document.getElementById("searchInput");

            if (!searchInput) {
                return;
            }

            const searchValue = searchInput.value.trim();

            if (searchValue === "") {
                searchInput.focus();
                return;
            }

            /* 
             * this search request will later be sent to PHP.
             * innerHTML is not used with user input.
             */
            console.log("Search requested:", searchValue);
        });
    }

});

document.addEventListener('submit', function (event) {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) {
        return;
    }

    const message = form.dataset.confirm;

    if (!message) {
        return;
    }

    if (!window.confirm(message)) {
        event.preventDefault();
    }
});