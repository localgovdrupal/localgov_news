<?php

namespace Drupal\Tests\localgov_news\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * Tests LocalGov News search.
 *
 * @group localgov_news
 */
class NewsSearchTest extends BrowserTestBase {

  use NodeCreationTrait;
  use CronRunTrait;

  /**
   * Test search in the Localgov profile.
   *
   * @var string
   */
  protected $profile = 'localgov';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_theme';

  /**
   * A user with permission to bypass content access checks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'localgov_news',
    'localgov_newsroom',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
    ]);
    $this->drupalLogin($this->adminUser);

    $body = [
      'value' => 'Science is the search for truth, that is the effort to understand the world: it involves the rejection of bias, of dogma, of revelation, but not the rejection of morality.',
      'summary' => 'One of the greatest joys known to man is to take a flight into ignorance in search of knowledge.',
    ];
    $this->createNode([
      'title' => 'Test News Article',
      'body' => $body,
      'type' => 'localgov_news_article',
      'status' => NodeInterface::PUBLISHED,
    ]);

    $this->drupalLogout();
    $this->cronRun();
  }

  /**
   * Basic search functionality.
   */
  public function testNewsSearch() {
    $this->drupalGet('news/test-news-article');
    $this->submitForm(['edit-search-api-fulltext' => 'dogma'], 'Apply');
    $this->assertSession()->pageTextContains('Test News Article');

    $this->drupalGet('news/test-news-article');
    $this->submitForm(['edit-search-api-fulltext' => 'xyzzy'], 'Apply');
    $this->assertSession()->pageTextNotContains('Test News Article');
  }

}
