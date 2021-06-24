/******/ (() => { // webpackBootstrap
var __webpack_exports__ = {};
/*!*********************************!*\
  !*** ./resources/js/moveTag.js ***!
  \*********************************/
var modal = document.querySelector('#status_modal');
var closeModal = document.querySelector('.closeModal');
var body = document.querySelector('body');
body.addEventListener('submit', function (e) {
  e.preventDefault();
});
body.addEventListener("keyup", function (e) {
  var targetElement = e.target;
  var elementID = 'movetag';
  e.preventDefault();

  if (targetElement.id === 'movetag') {
    processRequest(targetElement, elementID);
  }

  if (targetElement.id === 'itemsPerBar') {
    var qty = parseInt(document.querySelector('.totalQuantity').innerText);
    var itemsPerBar = parseInt(document.querySelector('#itemsPerBar').value);
    var bars = "Total Bars: " + Math.ceil(qty / itemsPerBar);
    document.querySelector('#totalBars').innerText = bars;
  }
}, false);

closeModal.onclick = function (event) {
  modal.style.display = "none";
  document.querySelector('.modal-dialog').innerText = '';
};

window.onclick = function (event) {
  if (event.target == modal) {
    modal.style.display = "none";
    document.querySelector('.modal-dialog').innerText = '';
  }
};

function processRequest(targetElement, elementID) {
  var value = targetElement.value;

  if (value.length === 7) {
    console.log(value);
    var form = document.querySelector('#anodizeMoveTagScan');
    var formData = new FormData(form);
    fetch('/api/anodize/movetag', {
      method: 'POST',
      body: formData
    }).then(function (response) {
      return response.json();
    }).then(function (data) {
      console.log(data);

      if (data.code != 200) {
        modal.style.display = "block";
        document.querySelector('.modal-dialog').innerHTML = "<strong>Error:</strong> " + data.message;
        document.querySelector('.modal-dialog').classList.add('text-danger');
        document.querySelector('.modal-dialog').style.fontSize = "18px";
      } else {
        modal.style.display = "none";
        document.querySelector('.anodizeContainer').innerHTML = data.message;
      }
    });
  }
}
/******/ })()
;