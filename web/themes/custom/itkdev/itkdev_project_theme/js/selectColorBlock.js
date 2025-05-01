let colorSchemeStatus = {
  'none':'bg-slate-300',
  'approved':'bg-green-600',
  'undecided':'bg-yellow-500',
  'refused':'bg-red-700'
}

let colorSchemeStaticSelect = {
  'high':'bg-green-600',
  'average':'bg-yellow-500',
  'low':'bg-red-700',
  'irrelevant':'bg-slate-300',
}

let selectElements = document.querySelectorAll('.colored-select')
selectElements.forEach(function(element) {
  let wrapper = element.parentElement;
  let colorBlock = wrapper.querySelector('.select-color-block');
  let scheme = eval(colorBlock.getAttribute('data-scheme'))
  if (colorBlock) {
    // Set color on page load.
    colorBlock.classList.add(scheme[element.value])
    if ('{Ingen}' !== element.value) {
      colorBlock.classList.add('show-color')
    }

    // Set color on change.
    element.addEventListener("change", function() {
      if ('{Ingen}' === element.value) {
        colorBlock.classList.remove('show-color');
        colorBlock.classList.remove(...Object.values(scheme));
      }
      else {
        colorBlock.classList.remove(...Object.values(scheme));
        colorBlock.classList.add(scheme[element.value], 'show-color')
      }
    });
  }
});
