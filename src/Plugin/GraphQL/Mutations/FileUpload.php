<?php

namespace Drupal\graphql_examples\Plugin\GraphQL\Mutations;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Plugin\GraphQL\Mutations\MutationPluginBase;
use Drupal\graphql_core\GraphQL\EntityCrudOutputWrapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * TODO: Add the whole range of file upload validations from file_save_upload().
 *
 * @GraphQLMutation(
 *   id = "file_upload",
 *   secure = "false",
 *   name = "fileUpload",
 *   type = "EntityCrudOutput!",
 *   entity_type = "file",
 *   arguments = {
 *     "file" = "Upload!",
 *   }
 * )
 */
class FileUpload extends MutationPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The mime type guesser service.
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    AccountProxyInterface $currentUser,
    MimeTypeGuesserInterface $mimeTypeGuesser,
    FileSystemInterface $fileSystem
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->currentUser = $currentUser;
    $this->mimeTypeGuesser = $mimeTypeGuesser;
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('file.mime_type.guesser'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
    $file = $args['file'];

    // Check for file upload errors and return FALSE for this file if a lower
    // level system error occurred.
    //
    // @see http://php.net/manual/features.file-upload.errors.php.
    switch ($file->getError()) {
      case UPLOAD_ERR_INI_SIZE:
      case UPLOAD_ERR_FORM_SIZE:
        return new EntityCrudOutputWrapper(NULL, NULL, [
          $this->t('The file %file could not be saved because it exceeds %maxsize, the maximum allowed size for uploads.', [
            '%file' => $file->getFilename(),
            '%maxsize' => format_size(file_upload_max_size())
          ]),
        ]);

      case UPLOAD_ERR_PARTIAL:
      case UPLOAD_ERR_NO_FILE:
        return new EntityCrudOutputWrapper(NULL, NULL, [
          $this->t('The file %file could not be saved because the upload did not complete.', [
            '%file' => $file->getFilename(),
          ]),
        ]);

      case UPLOAD_ERR_OK:
        // Final check that this is a valid upload, if it isn't, use the
        // default error handler.
        if (is_uploaded_file($file->getRealPath())) {
          break;
        }

      // Unknown error.
      default:
        return new EntityCrudOutputWrapper(NULL, NULL, [
          $this->t('The file %file could not be saved. An unknown error has occurred.', [
            '%file' => $file->getFilename(),
          ]),
        ]);
    }

    $name = $file->getClientOriginalName();
    $mime = $this->mimeTypeGuesser->guess($name);
    $destination = file_destination("public://{$file->getFilename()}", FILE_EXISTS_RENAME);

    // Begin building file entity.
    $values = [
      'uid' => $this->currentUser->id(),
      'status' => 0,
      'filename' => $name,
      'uri' => $destination,
      'filesize' => $file->getSize(),
      'filemime' => $mime,
    ];

    $storage = $this->entityTypeManager->getStorage('file');
    /** @var \Drupal\file\FileInterface $entity */
    $entity = $storage->create($values);

    // Check if the current user is allowed to create file entities.
    if (!$entity->access('create')) {
      return new EntityCrudOutputWrapper(NULL, NULL, [
        $this->t('You do not have the necessary permissions to create entities of this type.'),
      ]);
    }

    // Validate the entity values.
    if (($violations = $entity->validate()) && $violations->count()) {
      return new EntityCrudOutputWrapper(NULL, $violations);
    }

    // Validate the file name length.
    if ($errors = file_validate($entity, ['file_validate_name_length' => []])) {
      return new EntityCrudOutputWrapper(NULL, NULL, [
        $this->t('The specified file %name could not be uploaded.', [
          '%file' => $file->getFilename(),
        ]),
      ]);
    }

    // Move uploaded files from PHP's upload_tmp_dir to Drupal's temporary
    // directory. This overcomes open_basedir restrictions for future file
    // operations.
    if (!$this->fileSystem->moveUploadedFile($file->getRealPath(), $entity->getFileUri())) {
      return new EntityCrudOutputWrapper(NULL, NULL, [
        $this->t('Could not move uploaded file %name.', [
          '%file' => $file->getFilename(),
        ]),
      ]);
    }

    // Set the permissions on the new file.
    $this->fileSystem->chmod($entity->getFileUri());

    // If we reached this point, we can save the file.
    if (($status = $entity->save()) && $status === SAVED_NEW) {
      return new EntityCrudOutputWrapper($entity);
    }

    return NULL;
  }

}
