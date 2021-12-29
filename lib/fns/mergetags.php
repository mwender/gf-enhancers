<?php

namespace gfenhancers\mergetags;

/**
 * Merge Tags as Dynamic Population Parameters
 * http://gravitywiz.com/dynamic-products-via-post-meta/
 * @version 1.3
 */
function prepopluate_merge_tags( $form ) {
  global $gw_filter_names;

  $gw_filter_names = array();

  foreach( $form['fields'] as &$field ) {

    if( ! rgar( $field, 'allowsPrepopulate' ) ) {
      continue;
    }

    // complex fields store inputName in the "name" property of the inputs array
    if( is_array( rgar( $field, 'inputs' ) ) && $field['type'] != 'checkbox' ) {
      foreach( $field->inputs as $input ) {
        if( $input['name'] ) {
          $gw_filter_names[ $input['name'] ] = GFCommon::replace_variables_prepopulate( $input['name'] );
        }
      }
    } else {
      $gw_filter_names[ $field->inputName ] = GFCommon::replace_variables_prepopulate( $field->inputName );
    }

  }

  foreach( $gw_filter_names as $filter_name => $filter_value ) {

    if( $filter_value && $filter_name != $filter_value ) {
      add_filter( "gform_field_value_{$filter_name}", function( $value, $field, $name ) {
        global $gw_filter_names;
        $value = $gw_filter_names[ $name ];
        /** @var GF_Field $field  */
        if( $field->get_input_type() == 'list' ) {
          remove_all_filters( "gform_field_value_{$name}" );
          $value = GFFormsModel::get_parameter_value( $name, array( $name => $value ), $field );
        }
        return $value;
      }, 10, 3 );
    }

  }

  return $form;
}
add_filter( 'gform_pre_render', __NAMESPACE__ . '\\prepopluate_merge_tags' );