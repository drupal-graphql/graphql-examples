<?php

namespace Drupal\graphql_examples\Plugin\GraphQL\Mutations;

use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\DeleteEntityBase;

/**
 * Simple mutation for deleting an article node.
 *
 * @GraphQLMutation(
 *   id = "delete_article",
 *   entity_type = "node",
 *   entity_bundle = "article",
 *   secure = true,
 *   name = "deleteArticle",
 *   type = "EntityCrudOutput",
 *   arguments = {
 *      "id" = "String"
 *   }
 * )
 */
class DeleteArticle extends DeleteEntityBase {

}
