<?php

namespace Drupal\Tests\graphql_examples\Kernel\Extension;

use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;

/**
 * Test a simple mutation.
 *
 * @group graphql
 */
class ArticleMutationTest extends GraphQLContentTestBase {
  public static $modules = [
    'file',
    'image',
    'taxonomy',
    'graphql_examples',
  ];

  /**
   * {@inheritdoc}
   */
  protected function defaultCacheTags() {
    return array_merge([
      'config:field.storage.node.field_image',
      'config:field.storage.node.field_tags',
    ], parent::defaultCacheTags());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['file', 'image', 'taxonomy', 'graphql_examples']);
    $this->installEntitySchema('taxonomy_vocabulary');
    $this->installEntitySchema('taxonomy_term');

  }

  /**
   * {@inheritdoc}
   *
   * Add the 'access content' permission to the mocked account.
   */
  protected function userPermissions() {
    $perms = parent::userPermissions();
    $perms[] = 'access content';
    $perms[] = 'create article content';
    $perms[] = 'delete own article content';
    $perms[] = 'edit own article content';
    $perms[] = 'execute graphql requests';
    return $perms;
  }

  public function createTestArticle() {
    return $this->createNode([
      'title' => 'Hey',
      'status' => 1,
      'type' => 'article',
      'body' => [
        'value' => 'Ho',
      ],
    ]);
  }

  /**
   * Test if the article is created properly.
   */
  public function testCreateArticleMutation() {
    $query = $this->getQueryFromFile('createArticle.gql');
    $variables = [
      'input' => [
        'title' => 'Hey',
        'body' => "Ho"
      ]
    ];
    $expected = [
      'createArticle' => [
        'errors' => [],
        'violations' => [],
        'entity' => [
          'title' => 'Hey',
          'body' => [
            'value' => 'Ho'
          ]
        ]
      ],
    ];
    $this->assertResults($query, $variables, $expected, $this->defaultMutationCacheMetaData());
  }

  /**
   * Test if the article is NOT created properly.
   */
  public function testCreateArticleFailureMutation() {
    $query = $this->getQueryFromFile('createArticle.gql');
    $variables = [
      'input' => [
        'title' => 'Hey',
        'some-non-existent-field' => "Ho"
      ]
    ];
    $expected = [
      "Variable \"\$input\" got invalid value {\"title\":\"Hey\",\"some-non-existent-field\":\"Ho\"}.
In field \"some-non-existent-field\": Unknown field."
    ];
    $this->assertErrors($query, $variables, $expected, $this->defaultMutationCacheMetaData());
  }


  /**
   * Test if the article is updated properly.
   */
  public function testUpdateArticleMutation() {

    // SETUP
    $node = $this->createTestArticle();

    $query = $this->getQueryFromFile('updateArticle.gql');
    $variables = [
      'id' => $node->id(),
      'input' => [
        'title' => 'Heyo',
        'body' => "Let's go",
      ]
    ];
    $expected = [
      'updateArticle' => [
        'errors' => [],
        'violations' => [],
        'entity' => [
          'title' => 'Heyo',
          'body' => [
            'value' => "Let's go"
          ]
        ]
      ],
    ];
    $this->assertResults($query, $variables, $expected, $this->defaultMutationCacheMetaData());
  }

  /**
   * Test if the article is deleted properly.
   */
  public function testDeleteArticleMutation() {

    // SETUP
    $node = $this->createTestArticle();

    $query = $this->getQueryFromFile('deleteArticle.gql');
    $variables = [
      'id' => $node->id(),
    ];
    $expected = [
      'deleteArticle' => [
        'errors' => [],
        'violations' => [],
        'entity' => [
          'title' => 'Hey',
          'body' => [
            'value' => 'Ho'
          ]
        ]
      ],
    ];
    $this->assertResults($query, $variables, $expected, $this->defaultMutationCacheMetaData());
  }

  /**
   * Test if the article is deleted properly.
   */
  public function testDeleteNonExistentArticleMutation() {

    // SETUP
    $node = $this->createTestArticle();

    $query = $this->getQueryFromFile('deleteArticle.gql');
    $variables = [
      'id' => 999,
    ];
    // Note, the newline in the following string is required.
    $expected = ["Variable \"\$id\" got invalid value 999.
Expected type \"String\", found 999."];
    $this->assertErrors($query, $variables, $expected, $this->defaultMutationCacheMetaData());
  }


}
