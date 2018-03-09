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
    'system',
    'node',
    'user',
    'field',
    'filter',
    'text',
    'file',
    'image',
    'taxonomy',
    'graphql',
    'graphql_core',
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

    $this->installConfig(['system', 'node', 'field', 'text', 'filter', 'file', 'image', 'taxonomy', 'graphql', 'graphql_core', 'graphql_examples']);
    $this->installEntitySchema('node', 'user', 'graphql_examples');

    $this->installEntitySchema('user');
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

  /**
   * Test if the article is created properly.
   */
  public function testCreateArticleMutation() {

    $query = $this->getQueryFromFile('createArticle.gql');
    $this->assertResults($query, [], [
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
    ], $this->defaultMutationCacheMetaData());
  }


  /**
   * Test if the article is updated properly.
   */
  public function testUpdateArticleMutation() {

    $query = $this->getQueryFromFile('updateArticle.gql');
    $this->assertResults($query, [], [
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
    ], $this->defaultMutationCacheMetaData());
  }

  /**
   * Test if the article is deleted properly.
   */
  public function testDeleteArticleMutation() {

    $query = $this->getQueryFromFile('deleteArticle.gql');
    $this->assertResults($query, [], [
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
    ], $this->defaultMutationCacheMetaData());
  }

}
