<?php

namespace Drupal\Tests\localgov_openreferral\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\localgov_openreferral\Entity\PropertyMapping;

/**
 * Tests MappingInformation.
 *
 * @group localgov_openreferral
 */
class MappingInformationTest extends KernelTestBase {

  /**
   * Mapping Information service.
   *
   * @var \Drupal\localgov_openreferral\MappingInformation
   */
  protected $mappingInfo;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'localgov_openreferral',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // $this->installConfig(['system', 'localgov_directories_facets_ignore_test']);
    $this->mappingInfo = $this->container->get('localgov_openreferral.mapping_information');
  }

  /**
   * Test type methods.
   */
  public function testTypeMethods() {
    // @todo These might need swapping for entity_test when dependency checks added.
    $service = PropertyMapping::create([
      'entity_type' => 'node',
      'bundle' => 'localgov_directories_venue',
      'public_type' => 'service',
    ]);
    $service->save();

    $taxonomy = PropertyMapping::create([
      'entity_type' => 'taxonomy_term',
      'bundle' => 'openactiveactivity',
      'public_type' => 'taxonomy',
      'public_datatype' => 'openActiveActivity',
    ]);
    $taxonomy->save();

    $facets = PropertyMapping::create([
      'entity_type' => 'localgov_directories_facets',
      'bundle' => 'facet_type_1',
      'public_type' => 'taxonomy',
    ]);
    $facets->save();

    $this->assertEquals('service', $this->mappingInfo->getPublicType('node', 'localgov_directories_venue'));
    $this->assertEquals('taxonomy', $this->mappingInfo->getPublicType('taxonomy_term', 'openactiveactivity'));
    $this->assertEquals('taxonomy', $this->mappingInfo->getPublicType('localgov_directories_facets', 'facet_type_1'));

    $this->assertEquals(NULL, $this->mappingInfo->getPublicDataType('node', 'localgov_directories_venue'));
    $this->assertEquals('openActiveActivity', $this->mappingInfo->getPublicDataType('taxonomy_term', 'openactiveactivity'));
    $this->assertEquals(NULL, $this->mappingInfo->getPublicDataType('localgov_directories_facets', 'facet_type_1'));

    $this->assertEquals([
      ['entity_type' => 'node', 'bundle' => 'localgov_directories_venue'],
    ], $this->mappingInfo->getInternalTypes('service'));
    $taxonomy_entities = $this->mappingInfo->getInternalTypes('taxonomy');
    $this->assertCount(2, $taxonomy_entities);
    $this->assertEquals([
      [
        'entity_type' => 'localgov_directories_facets',
        'bundle' => 'facet_type_1',
      ],
      [
        'entity_type' => 'taxonomy_term',
        'bundle' => 'openactiveactivity',
      ],
    ], $taxonomy_entities);
    $this->assertEquals([
      ['entity_type' => 'taxonomy_term', 'bundle' => 'openactiveactivity'],
    ], $this->mappingInfo->getInternalTypes('taxonomy', 'openActiveActivity'));
  }

}
