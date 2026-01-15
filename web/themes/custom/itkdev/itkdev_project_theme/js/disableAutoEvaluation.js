let checkbox = document.getElementById(
  "edit-project-track-evaluation-set-manual",
);
let autoEvaluation = document.getElementById("auto-evaluation");

if (checkbox && autoEvaluation) {
  const updateState = () =>
    autoEvaluation.classList.toggle("text-slate-300", checkbox.checked);
  checkbox.addEventListener("change", updateState);
  // Initialize.
  updateState();
}
