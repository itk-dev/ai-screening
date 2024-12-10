let checkbox = document.getElementById('edit-project-track-evaluation-set-manual')
let autoEvaluation = document.getElementById('auto-evaluation')

checkbox.addEventListener("change", function() {
  autoEvaluation.classList.toggle('text-slate-300')
})

if (checkbox.checked) {
  autoEvaluation.classList.add('text-slate-300')
}
else {
  autoEvaluation.classList.remove('text-slate-300')
}