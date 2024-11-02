<?php

namespace Drupal\ai_screening_project_track;

/**
 * Project track tool status.
 */
enum ProjectTrackToolStatus: string {
  case NONE = '';
  case NEW = 'new';
  case IN_PROGRESS = 'in_progress';
  case COMPLETED = 'completed';
}
