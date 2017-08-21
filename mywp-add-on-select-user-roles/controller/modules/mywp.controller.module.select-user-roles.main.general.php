<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpControllerAbstractModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpControllerModuleSelectUserRolesMainGeneral' ) ) :

final class MywpControllerModuleSelectUserRolesMainGeneral extends MywpControllerAbstractModule {

  static protected $id = 'select_user_roles_main_general';

  static protected $is_do_controller = true;

  protected static function after_init() {

    add_filter( 'mywp_controller_model_' . self::$id , array( __CLASS__ , 'mywp_controller_model' ) );

  }

  public static function mywp_controller_model( $pre_model ) {

    $pre_model = true;

    return $pre_model;

  }

  public static function mywp_wp_loaded() {

    if( ! is_admin() ) {

      return false;

    }

    add_filter( 'plugin_row_meta' , array( __CLASS__ , 'plugin_row_meta' ) , 10 , 4 );

    if( is_multisite() ) {

      add_filter( 'network_admin_plugin_action_links_' . MYWP_ADD_ON_SELECT_USER_ROLES_PLUGIN_BASENAME , array( __CLASS__ , 'plugin_action_links' ) , 10 , 4 );

    } else {

      add_filter( 'plugin_action_links_' . MYWP_ADD_ON_SELECT_USER_ROLES_PLUGIN_BASENAME , array( __CLASS__ , 'plugin_action_links' ) , 10 , 4 );

    }

  }

  private static function is_mywp_select_user_roles_plugin( $plugin_file_name = false ) {

    if( empty( $plugin_file_name ) ) {

      return false;

    }

    $plugin_file_name = strip_tags( $plugin_file_name );

    if ( strpos( MYWP_ADD_ON_SELECT_USER_ROLES_PLUGIN_BASENAME , $plugin_file_name ) === false ) {

      return false;

    }

    return true;

  }

  public static function plugin_row_meta( $plugin_meta , $plugin_file , $plugin_data , $status ) {

    if ( ! self::is_mywp_select_user_roles_plugin( $plugin_file ) ) {

      return $plugin_meta;

    }

    $plugin_info = MywpAddonSelectUserRolesApi::plugin_info();

    if( ! empty( $plugin_info['document_url'] ) ) {

      $plugin_meta[] =  sprintf( '<a href="%1$s" target="_blank">%2$s</a>' , esc_url( $plugin_info['document_url'] ) , __( 'Document' , 'mywp-add-on-user-roles' ) );

    }

    $plugin_meta = apply_filters( 'mywp_addon_select_user_roles_plugin_row_meta' , $plugin_meta , $plugin_file , $plugin_data , $status );

    return $plugin_meta;

  }

  public static function plugin_action_links( $actions , $plugin_file , $plugin_data , $context ) {

    if ( ! self::is_mywp_select_user_roles_plugin( $plugin_file ) ) {

      return $actions;

    }

    $plugin_info = MywpAddonSelectUserRolesApi::plugin_info();

    if( ! empty( $plugin_info['admin_url'] ) ) {

      $action_link = array( 'setting' => sprintf( '<a href="%1$s">%2$s</a>' , esc_url( $plugin_info['admin_url'] ) , __( 'Settings' ) ) );

      $actions = wp_parse_args( $actions , $action_link );

    }

    $actions = apply_filters( 'mywp_addon_select_user_roles_plugin_action_links' , $actions , $plugin_file , $plugin_data , $context );

    return $actions;

  }

}

MywpControllerModuleSelectUserRolesMainGeneral::init();

endif;
