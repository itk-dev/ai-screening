function openGlobalNavigation() {
  document.getElementById("globalNavigation").classList.remove("hidden");
}

function closeGlobalNavigation() {
  document.getElementById("globalNavigation").classList.add("hidden");
}

document
  .getElementById("btnOpenGlobalNavigation")
  .addEventListener("click", openGlobalNavigation);
document
  .getElementById("btnCloseGlobalNavigation")
  .addEventListener("click", closeGlobalNavigation);
