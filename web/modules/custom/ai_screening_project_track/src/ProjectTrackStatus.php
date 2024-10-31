<?php

namespace Drupal\ai_screening_project_track;

/**
 * Project track status.
 */
enum ProjectTrackStatus: string {
  case NONE = '';
  case NEW = 'new';
  case IN_PROGRESS = 'in_progress';
}
