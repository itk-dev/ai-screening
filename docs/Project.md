# Project details

## Editing webform submissions

In order to make full use of the draft functionality on webforms, we make sure that a webform submission on a webform
using drafts is _always a draft_ (see
[ai_screening_project_track/src/Hook/EntityHooks.php](../web/modules/custom/ai_screening_project_track/src/Hook/EntityHooks.php)
for details).

This makes it possible to save the submission when navigation between pages on multi-page webforms.

## Roles and permissions

We have three roles in the system

1. Administrator
2. Editor
3. Authenticated user

### Administrator

An Administrator can administer some site settings.

### Editor

An Editor can create a new screening and when a screening is created, we use the [Group
module](https://www.drupal.org/project/group) to create a group for the screening. The creator of the screening, i.e.
the Editor, is the owner of the group. Any members of a groups can edit the screening in the group and any members can
add other users as members of the groups, i.e. add contributors to the screening.

A new screening is unpublished and we use the [Publish Content module](https://www.drupal.org/project/publishcontent) to
let an Editor publish (and unpublish) a screening.

An Editor can see all screenings (both published and unpublished), but can only edit the ones in groups they are a
member of. We use the Content Moderation core module to let Editors see unpublished screenings (actually any unpublished
content via the "View any unpublished content" permission).

### Authenticated user

An Authenticated user can _see all published_ screenings, but cannot see unpublished screenings nor create or edit
anything.

We use the "Published status or admin user" filter on all views showing screenings to filter out unpublished screenings
(the filter works in tandem with Content moderation to make this work, cf.
[node/src/Plugin/views/filter/Status.php](https://git.drupalcode.org/project/drupal/-/blob/main/core/modules/node/src/Plugin/views/filter/Status.php).
