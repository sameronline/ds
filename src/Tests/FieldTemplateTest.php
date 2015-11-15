<?php

/**
 * @file
 * Contains Drupal\ds\Tests\FieldTemplateTest.
 */

namespace Drupal\ds\Tests;

use Drupal\Core\Cache\Cache;

/**
 * Tests for display of nodes and fields.
 *
 * @group ds
 */
class FieldTemplateTest extends FastTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setup() {
    parent::setup();

    // Enable field templates
    \Drupal::configFactory()->getEditable('ds.settings')
      ->set('field_template', TRUE)
      ->save();
  }

  /**
   * Tests on field templates.
   */
  function testDSFieldTemplate() {
    // Get a node.
    $node = $this->entitiesTestSetup('hidden');
    $body_field = $node->body->value;

    // -------------------------
    // Default theming function.
    // -------------------------
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="clearfix text-formatted field field--name-body field--type-text-with-summary field--label-hidden field__item"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    $this->entitiesSetLabelClass('above', 'body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="clearfix text-formatted field field--name-body field--type-text-with-summary field--label-above"]/div[@class="field__label"]');
    $this->assertTrimEqual($xpath[0], 'Body');
    $xpath = $this->xpath('//div[@class="clearfix text-formatted field field--name-body field--type-text-with-summary field--label-above"]/div[@class="field__item"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    $this->entitiesSetLabelClass('above', 'body', 'My body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="clearfix text-formatted field field--name-body field--type-text-with-summary field--label-above"]/div[@class="field__label"]');
    $this->assertTrimEqual($xpath[0], 'My body');
    $xpath = $this->xpath('//div[@class="clearfix text-formatted field field--name-body field--type-text-with-summary field--label-above"]/div[@class="field__item"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    $this->entitiesSetLabelClass('hidden', 'body', '', 'test_field_class');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="test_field_class clearfix text-formatted field field--name-body field--type-text-with-summary field--label-hidden field__item"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);
  }

  /**
   * Tests on field templates.
   */
  function testDSFieldTemplate2() {
    // Get a node.
    $node = $this->entitiesTestSetup('hidden');
    $body_field = $node->body->value;

    // -----------------------
    // Reset theming function.
    // -----------------------
    $edit = array(
      'fs1[ft-default]' => 'reset',
    );
    $this->drupalPostForm('admin/structure/ds/settings', $edit, t('Save configuration'));

    // As long as we don't change anything in the UI, the default template will be used
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->div->p, $body_field);

    $this->entitiesSetLabelClass('above', 'body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="field-label-above"]');
    $this->assertTrimEqual($xpath[0], 'Body');

    $this->entitiesSetLabelClass('inline', 'body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="field-label-inline"]');
    $this->assertTrimEqual($xpath[0], 'Body');

    $this->entitiesSetLabelClass('above', 'body', 'My body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="field-label-above"]');
    $this->assertTrimEqual($xpath[0], 'My body');

    $this->entitiesSetLabelClass('inline', 'body', 'My body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="field-label-inline"]');
    $this->assertTrimEqual($xpath[0], 'My body');

    $edit = array(
      'fs1[ft-show-colon]' => 'reset',
    );
    $this->drupalPostForm('admin/structure/ds/settings', $edit, t('Save configuration'));
    // Clear node cache to get the colon
    $tags = $node->getCacheTags();
    Cache::invalidateTags($tags);

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="field-label-inline"]');
    $this->assertTrimEqual($xpath[0], 'My body:');

    $this->entitiesSetLabelClass('hidden', 'body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);
  }

  /**
   * Tests on field templates.
   */
  function testDSFieldTemplate3() {
    // Get a node.
    $node = $this->entitiesTestSetup('hidden');
    $body_field = $node->body->value;

    // ----------------------
    // Custom field function.
    // ----------------------

    // With outer wrapper.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][id]' => 'expert',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    // As long as we don't change anything in the UI, the default template will be used
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->div->p, $body_field);

    // With outer div wrapper and class.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-cl]' => 'ow-class'
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // With outer span wrapper and class.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'span',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-cl]' => 'ow-class-2'
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/span[@class="ow-class-2"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);
  }

  /**
   * Tests on field templates.
   */
  function testDSFieldTemplate4() {

    // Get a node.
    $node = $this->entitiesTestSetup('hidden');
    $body_field = $node->body->value;

    // With outer wrapper and field items wrapper.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][id]' => 'expert',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-el]' => 'div'
    );
    $this->dsEditFormatterSettings($edit);

    drupal_flush_all_caches();
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div/div');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // With outer wrapper and field items div wrapper with class.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-cl]' => 'fi-class'
    );
    $this->dsEditFormatterSettings($edit);
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div/div[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // With outer wrapper and field items span wrapper and class.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-el]' => 'span',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-cl]' => 'fi-class'
    );
    $this->dsEditFormatterSettings($edit);
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // With outer wrapper class and field items span wrapper and class.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-cl]' => 'ow-class',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-el]' => 'span',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-cl]' => 'fi-class'
    );
    $this->dsEditFormatterSettings($edit);
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // With outer wrapper span class and field items span wrapper and class.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'span',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-cl]' => 'ow-class',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-el]' => 'span',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-cl]' => 'fi-class-2'
    );
    $this->dsEditFormatterSettings($edit);
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/span[@class="ow-class"]/span[@class="fi-class-2"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);
  }

  /**
   * Tests on field templates.
   */
  function testDSFieldTemplate5() {
    // Get a node.
    $node = $this->entitiesTestSetup('hidden');
    $body_field = $node->body->value;

    // With field item div wrapper.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][id]' => 'expert',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi]' => '1',
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->div->p, $body_field);

    // With field item span wrapper.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-el]' => 'span',
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());

    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0]->span->p, $body_field);

    // With field item span wrapper and class.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-el]' => 'span',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-cl]' => 'fi-class',
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // With fis and fi.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-cl]' => 'fi-class-2',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-cl]' => 'fi-class',
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="fi-class-2"]/div[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // With all wrappers.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-cl]' => 'ow-class',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-cl]' => 'fi-class-2',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-el]' => 'span',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-cl]' => 'fi-class',
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="fi-class-2"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // With all wrappers and attributes.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-cl]' => 'ow-class',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-at]' => 'name="ow-att"',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-cl]' => 'fi-class-2',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-at]' => 'name="fis-att"',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-el]' => 'span',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-cl]' => 'fi-class',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-at]' => 'name="fi-at"',
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class" and @name="ow-att"]/div[@class="fi-class-2" and @name="fis-att"]/span[@class="fi-class" and @name="fi-at"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // Remove attributes.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-cl]' => 'ow-class',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-at]' => '',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-cl]' => 'fi-class-2',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fis-at]' => '',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-el]' => 'span',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-cl]' => 'fi-class',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][fi-at]' => '',
    );
    $this->dsEditFormatterSettings($edit);

    // Label tests with custom function.
    $this->entitiesSetLabelClass('above', 'body');
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="field-label-above"]');
    $this->assertTrimEqual($xpath[0], 'Body');
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="fi-class-2"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    $this->entitiesSetLabelClass('inline', 'body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="field-label-inline"]');
    $this->assertTrimEqual($xpath[0], 'Body');
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="fi-class-2"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    $this->entitiesSetLabelClass('above', 'body', 'My body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="field-label-above"]');
    $this->assertTrimEqual($xpath[0], 'My body');
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="fi-class-2"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    $this->entitiesSetLabelClass('inline', 'body', 'My body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="field-label-inline"]');
    $this->assertTrimEqual($xpath[0], 'My body');
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="fi-class-2"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    $this->entitiesSetLabelClass('inline', 'body', 'My body', '', TRUE);
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="field-label-inline"]');
    $this->assertTrimEqual($xpath[0], 'My body:');
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="fi-class-2"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    $this->entitiesSetLabelClass('hidden', 'body');
    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="fi-class-2"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // Test default classes on outer wrapper.
    // @todo figure out a way to actually test this as the default cases don't have classes anymore
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-cl]' => 'ow-class',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-def-cl]' => '1',
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class"]/div[@class="fi-class-2"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // Test default attributes on field item.
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow]' => '1',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-el]' => 'div',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-cl]' => 'ow-class',
      'fields[body][settings_edit_form][third_party_settings][ds][ft][settings][ow-def-at]' => '1',
    );
    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]/div[@class="ow-class" and @data-quickedit-field-id="node/1/body/en/full"]/div[@class="fi-class-2"]/span[@class="fi-class"]');
    $this->assertTrimEqual($xpath[0]->p, $body_field);

    // Use the test field theming function to test that this function is
    // registered in the theme registry through ds_extras_theme().
    $edit = array(
      'fields[body][settings_edit_form][third_party_settings][ds][ft][id]' => 'ds_test_template',
    );

    $this->dsEditFormatterSettings($edit);
    drupal_flush_all_caches();

    $this->drupalGet('node/' . $node->id());
    $xpath = $this->xpath('//div[@class="group-right"]');
    $this->assertTrimEqual($xpath[0], 'Testing field output through custom function');
  }
}
