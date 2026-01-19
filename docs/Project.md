# Project details

## Editing webform submissions

In order to make full use of the draft functionality on webforms, we make sure that a webform submission on a webform
using drafts is _always a draft_ (see
[ai_screening_project_track/src/Hook/EntityHooks.php](../web/modules/custom/ai_screening_project_track/src/Hook/EntityHooks.php)
for details).

This makes it possible to save the submission when navigation between pages on multi-page webforms.
