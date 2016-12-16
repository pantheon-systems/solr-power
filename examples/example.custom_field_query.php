<?php
$args = array(
  //Include multiple post types
  'post_type', array('post', 'custom_post_type'),
  'meta_query' => array(
    //Include posts where both nested custom field clauses are true; can be replaced with 'OR'
    'relation' => 'AND',
    array(
      //Replace values with desired custom field data, modify operator for 'compare' as needed
      'key' => 'custom_field_key1',
      'value' => 'custom_field_value1',
      'compare' => 'LIKE'
    ),
    array(
      //Replace values with desired custom field data, modify operator for 'compare' as needed
      'key' => 'custom_field_key2',
      'value' => 'custom_field_value2',
      'compare' => 'LIKE'
    )
  )
);
$query = new WP_Query( $args );
