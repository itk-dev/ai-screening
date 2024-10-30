# Content fixtures module

* [Introduction](#introduction)
* [Similar project](#similar-project)
* [Usage](#usage)
  * [Creating and loading fixtures](#creating-and-loading-fixtures)
    * [I.Creating a fixture](#icreating-a-fixture)
    * [II.Implementing the load method](#iiimplementing-the-load-method)
  * [Splitting fixtures into separate files](#splitting-fixtures-into-separate-files)
    * [I.Sharing objects between fixtures](#isharing-objects-between-fixtures)
    * [II.Loading the fixture files in specific order](#iiloading-the-fixture-files-in-a-specific-order)
  * [Using fixture groups to only execute some fixtures](#fixture-groups-only-executing-some-fixtures)
  * [Execution](#execution)
* [Requirements](#requirements)
* [Maintainers](#maintainers)

## Introduction

Do you want to build a running website straight from your repository, but you realized that you have to get some dummy
content from somewhere? Search no more. This module will give you an API to program your own content generators, that
you will be able to run with one command, and fill your website with content required either for development
or presentation.

This module is different from [data_fixtures](https://www.drupal.org/project/data_fixtures) in that it aims to mimic as
much as it's reasonably possible [DoctrineFixturesBundle](https://symfony.com/doc/master/bundles/DoctrineFixturesBundle/index.html).

It was made with a workflow of programmatically building your website from scratch in mind, and tries to avoid any
ambigious states, that's why it deletes all content before loading any fixtures (don't worry, it will warn you :) ).

Great match with Docker, if you are looking for ultimate automation.

## Similar project

This module has API very similar to [DoctrineFixturesBundle](https://symfony.com/doc/master/bundles/DoctrineFixturesBundle/index.html),
with some Drupal-specific differences/simplifications.

## Usage

### Creating and loading fixtures

Minimally, your fixture class has to implement `FixtureInterface`, and be registered as a service tagged with a `content_fixture`
tag.

#### I.Creating a fixture

1. Inside your module src create **Fixture** directory (this is just a convention for storing fixtures).
2. Inside that folder create a class, ex: `ArticleFixture.php` that implements the `FixtureInterface` :
```php
 // see full code example at: /content_fixtures/modules/content_fixtures_example/src/Fixture/ArticleFixture.php
use Drupal\content_fixtures\Fixture\FixtureInterface;

class ArticleFixture implements FixtureInterface {
   /**
    * {@inheritDoc}
    */
   public function load() {

   }
}
```
3. Register the created class as a service and give it a tag name = `content_fixture`
```php
  services:
    content_fixtures_example.fixture.article:
      class: Drupal\content_fixtures_example\Fixture\ArticleFixture
      tags:
        - { name: content_fixture }
```
4. Clear the cache then execute `drush content-fixtures:list` to list all the created fixtures. The `ArticleFixture` should be on the list,
   it means that the Fixture has been created successfully.

#### II.Implementing the load method

1. Inject the `entity_type.manager` service into our Fixture class :
```php
   content_fixtures_example.article_fixture:
     class: Drupal\content_fixtures_example\Fixture\ArticleFixture
     arguments: [ '@entity_type.manager' ]
     tags:
       - { name: content_fixture }
```
2. The load method will use the injected entity_type.manager service to create a set of articles :
```php
    /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

 /**
   * {@inheritDoc}
   */
  public function load(): void {
    // Create nodes and save them into the DB.
    $nodeStorage = $this->entityTypeManager->getStorage('node');
    for ($i = 0; $i < 5; $i++) {
      $node = $nodeStorage->create([
        'type' => 'article',
        'title' => 'Article title - Group tutorial' . $i,
        'body' => 'Article title - Group tutorial' . $i,
      ]);
      $node->save();
    }
  }
```
3.Execute the `drush content-fixtures:load` command to execute the `load()` method and create the Articles.
> :information_source: When we execute `drush content-fixtures:load` all the `load` methods of all services tagged with `content_fixture` will be executed.

> :warning: The load command will remove all content from the site before inserting the new content, but don't worry, the command will ask you if you want to proceed or not.


###  Splitting fixtures into separate files
If we have a simple fixture, it will be fine to put it in just one class, but if we have multiple fixtures and class ends up being too long, hard to debug and extend in the future,
then it will be better to separate these fixtures across multiple classes.

#### I.Sharing objects between fixtures.
We can create an object using one fixture, then pass it to the next fixtures. This is especially useful if we want to set up references between objects created in multiple fixtures.

The easiest way of achieving this is to extend `AbstractFixture` class that implements `SharedFixtureInterface`, which will provide you with some additional
methods that make it possible to share objects across fixtures:
- `addReference()` : exposes the object to the fixtures that will load after the one using this method.
- `getReference()` : allows you to access any object that has been exposed by the `addReference` method.

For example:

1. Create a fixture class that extends the `AbstractFixture` class, this will allow you to use the `addReference` method later on.
  ```php
    // see full code example at: /content_fixtures/modules/content_fixtures_example/src/Fixture/splitting_fixtures/UserFixture.php
    use Drupal\content_fixtures\Fixture\AbstractFixture;

    class UserFixture extends AbstractFixture {}
  ```
2. Use the `addReference()` method to store the created $user object under the reference name `user`. It will make it accessible to the next fixtures, by using that reference name.
```php
    // see full code example at: /content_fixtures/modules/content_fixtures_example/src/Fixture/splitting_fixtures/UserFixture.php
    use Drupal\content_fixtures\Fixture\AbstractFixture;

    class UserFixture extends AbstractFixture {
        public const USER_REFERENCE = 'user';

        $this->addReference(self::USER_REFERENCE, $user);
    }
  ```

3. The object we just shared, will now be accessible for any other fixture that will run later, by using the `getReference()` method. We just need to address it by its reference name.
```php
    // see full code example at: /content_fixtures/modules/content_fixtures_example/src/Fixture/splitting_fixtures/ArticleDependentOnUserFixture.php
    use Drupal\content_fixtures\Fixture\AbstractFixture;

    class ArticleDependentOnUserFixture extends AbstractFixture {
      public function load(): void {
         /** @var \Drupal\user\Entity\User $user */
         $user = $this->getReference(UserFixture::USER_REFERENCE);

         $nodeStorage = $this->entityTypeManager->getStorage('node');
         $node = $nodeStorage->create([
          'type' => 'page',
          'title' => 'Fixture title - Splitting fixture tutorial',
           // This is another nice thing about the AbstractFixture class: it gives
           // us a random string generator.
           'body' => $this->getRandom()->paragraphs(2),
           'uid' => $user->id(),
         ]);
         $node->save();
      }
    }
  ```
#### II.Loading the fixture files in a specific order
The problem with the previous step that what if the NodeFixture has been loaded before the UserFixture
this will cause an error because the NodeFixture depends on the UserFixture (`'uid' => $user->id()`) so we need to
make sure that the `drush content-fixtures:load` will load the UserFixture first.

Fortunately, we can control the fixtures' execution order, by using one of these two methods (method 2 is recommended):

1. By implementing `OrderedFixtureInterface` - this will allow you to assign a value to each fixture, that will be used
   for ordering.
   ```php
   use Drupal\content_fixtures\Fixture\OrderedFixtureInterface;

   class NodeFixture extends AbstractFixture implements OrderedFixtureInterface  {

    public function getOrder() {
     return 2;
    }

   }

   ```
2. By implementing `DependentFixtureInterface`, that will allow you to declare dependencies between fixtures, and order
   of execution will be calculated by using this information.
   ```php
   // see the full example at : src/web/modules/contrib/content_fixtures/modules/content_fixtures_example/src/Fixture/splitting_fixtures/ArticleDependentOnUserFixture.php
   use Drupal\content_fixtures\Fixture\DependentFixtureInterface;

   class ArticleDependentOnUserFixture extends AbstractFixture implements DependentFixtureInterface {
     public function getDependencies(): array {
      return [
          UserFixture::class,
      ];
    }
   }
   ```
   > :information_source: To display the fixtures' execution order just use : `drush content-fixture:list`

### Fixture Groups: Only executing some fixtures
The `drush content-fixtures:load` will load all existing fixtures, but what if we want to load only a specific set of fixtures?
To do that you can implement `FixtureGroupInterface` in your fixture, to assign it to some custom groups. It will allow you
to run fixtures by groups they belong to. This way you can have different set of fixtures for presentation, different
for development etc.
1. Make your class implement the `FixtureGroupInterface`. This interface has a method `getGroup()` that should return an array of arbitrary strings,
   representing your groups, so just return names of groups you want your fixture to belong to.
  ```php
   // see the full example at : /content_fixtures/modules/content_fixtures_example/src/Fixture/groups/ArticleBelongingToGroupFixture.php
   class ArticleBelongingToGroupFixture implements FixtureInterface, FixtureGroupInterface {
       public function getGroups(): array {
          return [
            'node_group',
          ];
        }
   }
   ```

  ```php
   // see the full example at : /content_fixtures/modules/content_fixtures_example/src/Fixture/groups/PageBelongingToGroupFixture.php
   class PageBelongingToGroupFixture.php implements FixtureInterface, FixtureGroupInterface {
       public function getGroups(): array {
          return [
            'node_group',
          ];
        }
   }
   ```
2. In order to display all the fixtures that belong to the `node_group`, use : `drush content-fixtures:list --groups=node_group`
3. In order to load only the fixtures that belong to the `node_group`, use : `drush content-fixtures:load --groups=node_group`

### Execution

You need `drush` to run your fixtures. This module provides you with three commands with some options:
* `drush content-fixtures:list`: list all fixtures (services tagged using the `content_fixture` tag).
* `drush content-fixtures:load`: Delete all content, then load all fixtures.
* `drush content-fixtures:load --groups=group1`: load all fixtures that belong to a specific group.
* `drush content-fixtures:load --groups=group1,group2,group3`: load all fixtures that belong to any of these groups.
* `drush content-fixtures:purge`: delete all existing content on website.

See: `drush help content-fixtures:list` and `drush help content-fixtures:load` .

Happy coding !

## Requirements

* PHP >= 7.1
* Drush 9 / 10

## Maintainers

* Łukasz Zaroda <luken@luken-tech.pl>
* Marwen Amri <marwen.amri@ekino.com>
