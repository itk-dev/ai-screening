let colorScheme = {
  0:'bg-slate-300',
  1:'bg-green-700',
  2:'bg-yellow-700',
  3:'bg-red-700'
}

let select = document.getElementById('edit-project-track-evaluation')
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
