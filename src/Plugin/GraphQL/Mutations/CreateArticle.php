<?php

namespace Drupal\graphql_examples\Plugin\GraphQL\Mutations;

use Drupal\graphql\GraphQL\Type\InputObjectType;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\CreateEntityBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Simple mutation for creating a new article node.
 *
 * @GraphQLMutation(
 *   id = "create_article",
 *   entity_type = "node",
 *   entity_bundle = "article",
 *   secure = true,
 *   name = "createArticle",
 *   type = "EntityCrudOutput!",
 *   arguments = {
 *     "input" = "ArticleInput"
 *   }
 * )
 */
class CreateArticle extends CreateEntityBase {

  /**
   * {@inheritdoc}
   */
  protected function extractEntityInput(array $args, ResolveInfo $info) {
    return [
      'title' => $args['title'],
      'body' => $args['body'],
    ];
  }

}
