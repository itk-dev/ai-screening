let colorScheme = {
  'none':'bg-slate-300',
  'approved':'bg-green-700',
  'undecided':'bg-yellow-700',
  'refused':'bg-red-700'
}

let select = document.getElementById('edit-project-track-evaluation-overridden')
let colorBlock = document.getElementById('selectColorBlock');

if (colorBlock) {
  // Set color on page load.
  colorBlock.classList.add(colorScheme[select.value])

// Set color on change.
  select.addEventListener("change", function() {
    colorBlock.classList.remove(...Object.values(colorScheme));
    colorBlock.classList.add(colorScheme[select.value])
  });
}
