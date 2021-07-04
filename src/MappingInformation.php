<?php

namespace Drupal\localgov_openreferral;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Helper service for querying details about Open Referral entity mappings.
 *
 * @todo move arrays to config.
 */
class MappingInformation {

  /**
   * Canfig Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Mapping information constrcutor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * To trait, or base class.
   */
  public function getPublicType($entity_type, $bundle) {
    $entity_bundle_mapping = [
      'node.localgov_directories_venue' => 'service',
      'localgov_geo.address' => 'location',
      'localgov_directories_facets.facet_type_1' => 'taxonomy',
    ];

    $type = 'unknown';
    if (isset($entity_bundle_mapping[$entity_type . '.' . $bundle])) {
      $type = $entity_bundle_mapping[$entity_type . '.' . $bundle];
    }

    return $type;
  }

  /**
   * Get internal types by Open Referral type.
   */
  public function getInternalTypes($type) {
    $entity_bundle_mapping = [
      'node.localgov_directories_venue' => 'service',
      'localgov_geo.address' => 'location',
      'localgov_directories_facets.facet_type_1' => 'taxonomy',
    ];

    $internal_types = [];
    $entity_types = array_keys($entity_bundle_mapping, $type);
    foreach ($entity_types as $entity) {
      list($entity_type, $bundle) = explode('.', $entity);
      $internal_types[] = [
        'entity_type' => $entity_type,
        'bundle' => $bundle,
      ];
    }
    return $internal_types;
  }

  /**
   * Get property mapping.
   */
  public function getPropertyMapping($entity_type, $bundle) {
    if ($entity_type == 'node' && $bundle == 'localgov_directories_venue') {
      $property_mapping = [
        'uuid' => [
          'fieldName' => 'uuid',
          'publicName' => 'id',
        ],
        'localgov_location' => [
          'fieldName' => 'localgov_location',
          'publicName' => 'service_at_locations',
        ],
        'body' => [
          'fieldName' => 'body',
          'publicName' => 'description',
          'property' => 'value',
        ],
        'localgov_directory_email' => [
          'fieldName' => 'localgov_directory_email',
          'publicName' => 'email',
        ],
        'localgov_directory_website' => [
          'fieldName' => 'localgov_directory_website',
          'publicName' => 'url',
          'property' => 'uri',
        ],
        'localgov_directory_facets_select' => [
          'fieldName' => 'localgov_directory_facets_select',
          'publicName' => 'service_taxonomys',
        ],
      ];
    }
    elseif ($entity_type == 'localgov_geo' && $bundle == 'address') {
      $property_mapping = [
        'uuid' => [
          'fieldName' => 'uuid',
          'publicName' => 'id',
        ],
        'label' => [
          'fieldName' => 'label',
          'publicName' => 'name',
        ],
        'geo' => [
          'fieldName' => 'location',
          'publicName' => '_flatten',
        ],
        'postal_address' => [
          'fieldName' => 'postal_address',
          'publicName' => 'physical_addresses',
        ],
      ];
    }
    elseif ($entity_type == 'localgov_directories_facets') {
      $property_mapping = [
        'uuid' => [
          'fieldName' => 'uuid',
          'publicName' => 'id',
        ],
        'title' => [
          'fieldName' => 'title',
          'publicName' => 'name',
        ],
      ];
    }
    elseif ($entity_type == 'taxonomy_term') {
      $property_mapping = [
        'uuid' => [
          'fieldName' => 'uuid',
          'publicName' => 'id',
        ],
        'name' => [
          'fieldName' => 'name',
          'publicName' => 'name',
        ],
        // @todo parents -- no just in the Normalizer need logic.
      ];
    }
    else {
      $property_mapping = [
        'uuid' => [
          'fieldName' => 'uuid',
          'publicName' => 'id',
        ],
      ];
    }

    return $property_mapping;
  }

}
