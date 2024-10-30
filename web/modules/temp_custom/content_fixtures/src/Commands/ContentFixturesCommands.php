<?php

namespace Drupal\content_fixtures\Commands;

use Drupal\content_fixtures\Fixture\FixtureGroupInterface;
use Drupal\content_fixtures\Loader\LoaderInterface;
use Drupal\content_fixtures\Purger\PurgerInterface;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Input\InputOption;

/**
 * Drush ^9 commands.
 */
class ContentFixturesCommands extends DrushCommands {

  /**
   * @var \Drupal\content_fixtures\Loader\LoaderInterface
   */
  protected $fixturesLoader;

  /**
   * @var \Drupal\content_fixtures\Purger\PurgerInterface*/
  private $purger;

  /**
   * FixturesCommands constructor.
   *
   * @param \Drupal\content_fixtures\Loader\LoaderInterface $fixturesLoader
   * @param \Drupal\content_fixtures\Purger\PurgerInterface $purger
   */
  public function __construct(LoaderInterface $fixturesLoader, PurgerInterface $purger) {
    $this->fixturesLoader = $fixturesLoader;
    $this->purger = $purger;
  }

  /**
   * Purge all content.
   *
   * @command content-fixtures:purge
   * @aliases content-fixtures-purge
   * @usage drush content-fixtures:purge
   *   Purge all content.
   *
   * @validate-module-enabled content_fixtures
   */
  public function purge() {

    if (!$this->io()->confirm('Are you sure you want to delete all existing content on this instance of Drupal?', FALSE)) {
      return;
    }

    $this->output()->writeln('Deleting existing content.');
    $this->purger->purge();
    $this->output()->writeln('Done.');
  }

  /**
   * List all fixtures.
   *
   * @command content-fixtures:list
   * @aliases content-fixtures-list
   * @option groups
   * @usage drush content-fixtures:list
   *   Lists all fixtures.
   * @usage drush content-fixtures:list --groups=group1,group2,group3
   *   Lists fixtures belonging to three groups.
   *
   * @validate-module-enabled content_fixtures
   */
  public function listFixtures($options = ['groups' => InputOption::VALUE_REQUIRED]) {
    if (!empty($options['groups'])) {
      $groups = explode(',', $options['groups']);
      $this->output()->writeln('Selected groups: ' . implode(', ', $groups));
    }

    $fixtures = $this->fixturesLoader->getFixtures();

    if (empty($fixtures)) {
      $this->output()->writeln('No fixtures found.');
      return;
    }

    $this->output()->writeln('Fixtures in order of execution:');
    $index = 1;
    foreach ($fixtures as $fixture) {
      if (isset($groups) && (!$fixture instanceof FixtureGroupInterface || empty(array_intersect($groups, $fixture->getGroups())))) {
        continue;
      }

      $this->output()->writeln("$index: " . \get_class($fixture));
      $index++;
    }
  }

  /**
   * Load all fixtures.
   *
   * @command content-fixtures:load
   * @aliases content-fixtures-load
   * @option groups
   * @usage drush content-fixtures:load
   *   Loads all fixtures.
   * @usage drush content-fixtures:load --groups=group1,group2,group3
   *   Loads fixtures belonging to three groups.
   *
   * @validate-module-enabled content_fixtures
   */
  public function load($options = ['groups' => InputOption::VALUE_REQUIRED]) {

    if (!empty($options['groups'])) {
      $groups = explode(',', $options['groups']);
      $this->output()->writeln('Selected groups: ' . implode(', ', $groups));
    }

    if (!$this->io()->confirm('Are you sure you want to delete all existing content and load fixtures?', FALSE)) {
      return;
    }

    $this->output()->writeln('Deleting existing content.');
    $this->purger->purge();

    $fixtures = $this->fixturesLoader->getFixtures();

    if (empty($fixtures)) {
      $this->output()->writeln('No fixtures found.');
      return;
    }

    foreach ($fixtures as $fixture) {
      if (isset($groups) && (!$fixture instanceof FixtureGroupInterface || empty(array_intersect($groups, $fixture->getGroups())))) {
        continue;
      }

      $this->output()->writeln('Loading fixture: ' . \get_class($fixture));
      $fixture->load();
    }
    $this->output()->writeln('Done.');
  }

}
