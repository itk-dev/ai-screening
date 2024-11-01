<?php

namespace Drupal\Tests\ai_screening_project_track\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\ai_screening_project\Helper\ProjectHelper;
use Drupal\ai_screening_project_track\Computer\WebformSubmissionProjectTrackComputer;
use Drupal\ai_screening_project_track\Entity\ProjectTrack;
use Drupal\ai_screening_project_track\ProjectTrackInterface;
use Drupal\ai_screening_project_track\ProjectTrackStatus;
use Drupal\node\Entity\Node;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Tests for WebformSubmissionProjectTrackComputer.
 */
class WebformSubmissionProjectTrackComputerTest extends KernelTestBase {
  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'ai_screening_project_track',
    'user',
    'node',
    'webform',
    // Needed to define constants used by webform (like `DRUPAL_DISABLED`)
    'system',
  ];

  /**
   * Test track computer.
   *
   * Test that track computer computes correctly given a track and a webform
   * submission.
   */
  public function testWebformSubmissionProjectTrackComputer(): void {
    $computer = new WebformSubmissionProjectTrackComputer();

    $track = $this->createTrack([])
      ->setProjectTrackStatus(ProjectTrackStatus::NEW);
    $submission = $this->createWebformSubmission([]);
    $this->assertTrue($computer->supports($track, $submission));

    $this->assertSame(ProjectTrackStatus::NEW, $track->getProjectTrackStatus());
    $computer->compute($track, $submission);
    $this->assertSame(ProjectTrackStatus::IN_PROGRESS, $track->getProjectTrackStatus());
  }

  /**
   * Create project track.
   */
  private function createTrack(array $values): ProjectTrackInterface {
    $project = Node::create([
      'type' => ProjectHelper::BUNDLE_PROJECT,
    ]);

    return ProjectTrack::create([
      'project_id' => $project,
    ] + $values);
  }

  /**
   * Create webform submission.
   */
  private function createWebformSubmission(array $data): WebformSubmissionInterface {
    $webform = Webform::create([
      'id' => __METHOD__,
    ]);

    return WebformSubmission::create([
      'webform' => $webform,
      'data' => $data,
    ]);
  }

}
