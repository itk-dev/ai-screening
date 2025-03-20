/**
 * @file
 *
 * Report law track toolbar.
 * Reacts to changes in checkboxes in law track report.
 */

(function () {
  Drupal.behaviors.lawTrackToolbar = {
    attach: function (context, settings) {
      let toolbarCheckboxes = document.querySelectorAll("#reportLawTrackToolbar input")

      toolbarCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener("change", function(event) {
          toggleElements(event.target)
        })
      })

      initialize()
    }
  }
}())

/**
 * ToggleElements.
 *
 * @param elementTrigger
 */
function toggleElements(elementTrigger) {
  switch (elementTrigger.id) {
    case 'hideQuestionsAnswers':
      setDisplay(getFormState())
      break
    case 'showStops':
      toggleAllOfType(elementTrigger, 'stop', 'groupDemands')
      break
    case 'showRules':
      toggleAllOfType(elementTrigger, 'rule', 'groupRules')
      break
    case 'showTasks':
      toggleAllOfType(elementTrigger, 'task', 'groupTasks')
      break
    case 'showConsiderations':
      toggleAllOfType(elementTrigger, 'consideration', 'groupConsiderations')
      break
  }
}

/**
 * Toggle all elements of a specific type.
 *
 * @param elementTrigger
 *  The form element trigger related to this type of webform elements.
 * @param cssClass
 *   Css class of elements to toggle.
 * @param groupId
 *   Sorting group related to the element type.
 */
function toggleAllOfType(elementTrigger, cssClass, groupId) {
  const submissionElements = document.querySelectorAll(`.ai-screening-yes-no-stop .${cssClass}`)

  Array.from(submissionElements).forEach(function(element) {
    toggleElement(elementTrigger, element, groupId)
  })
}

/**
 * Show/hide a webform submission element
 *
 * @param checkbox
 *   Checkbox that determines the action to perform.
 * @param element
 *   The element to toggle
 * @param groupId
 *   Sorting group related to the element type.
 */
function toggleElement(checkbox, element, groupId) {
  element.style.display = checkbox.checked ? 'block' : 'none'
  cloneRemove(checkbox, element, groupId)
}

/**
 * Clone an element into a sorting group or remove it from a sorting group.
 *
 * @param checkbox
 *   Checkbox that determines the action to perform.
 * @param element
 *   Element to clone or remove.
 * @param groupId
 *   Group to clone into or remove element from.
 */
function cloneRemove(checkbox, element, groupId) {
  if (checkbox.checked) {
    document.getElementById(groupId).appendChild(element.cloneNode(true))
  }
  else {
    const groupedElements = document.getElementById(groupId).children
    Array.from(groupedElements).forEach(function(element) {
      element.remove()
    })
  }
}

/**
 * Initialize the page based on form state.
 */
function initialize() {
  setDisplay(getFormState())
  toggleAllOfType(document.getElementById('showStops'), 'stop', 'groupDemands')
  toggleAllOfType(document.getElementById('showRules'), 'rule', 'groupRules')
  toggleAllOfType(document.getElementById('showTasks'), 'task', 'groupTasks')
  toggleAllOfType(document.getElementById('showConsiderations'), 'consideration', 'groupConsiderations')
}

/**
 * Get the state of the filter form.
 *
 * @returns {NodeListOf<Element>}
 */
function getFormState() {
  return document.querySelectorAll("#reportLawTrackToolbar input")
}

/**
 * Set display mode of the page depending on hideQuestionsAnswers checkbox.
 *
 * @param formState
 *   The state of the form.
 */
function setDisplay(formState) {
  formState.forEach(function(checkbox) {
    if ('hideQuestionsAnswers' === checkbox.id) {
      document.getElementById('elementGroups').style.display = checkbox.checked ? 'block' : 'none'
      document.querySelector('.webform-submission-data').style.display = checkbox.checked ? 'none' : 'block'
      document.querySelector('.ai-screening-yes-no-stop').style.display = checkbox.checked ? 'none' : 'block'
    }
  })
}