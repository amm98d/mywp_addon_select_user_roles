<?php

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

if( ! class_exists( 'MywpAbstractSettingModule' ) ) {
  return false;
}

if ( ! class_exists( 'MywpSettingScreenAddOnSelectUserRoles' ) ) :

final class MywpSettingScreenAddOnSelectUserRoles extends MywpAbstractSettingModule {

  static protected $id = 'add_on_select_user_roles';

  static protected $priority = 50;

  static private $menu = 'select_user_roles';

  public static function mywp_setting_menus( $setting_menus ) {

    $setting_menus[ self::$menu ] = array(
      'menu_title' => __( 'User Roles' ),
      'multiple_screens' => false,
    );

    return $setting_menus;

  }

  public static function mywp_setting_screens( $setting_screens ) {

    $setting_screens[ self::$id ] = array(
      'title' => __( 'User Roles' ),
      'menu' => self::$menu,
      'controller' => 'add_on_select_user_roles',
    );

    return $setting_screens;

  }

  public static function mywp_ajax() {

    add_action( 'wp_ajax_' . MywpSetting::get_ajax_action_name( self::$id , 'check_latest' ) , array( __CLASS__ , 'check_latest' ) );

  }

  public static function check_latest() {

    $action_name = MywpSetting::get_ajax_action_name( self::$id , 'check_latest' );

    if( empty( $_POST[ $action_name ] ) ) {

      return false;

    }

    check_ajax_referer( $action_name , $action_name );

    if( ! MywpApi::is_manager() ) {

      return false;

    }

    delete_site_transient( self::$id );

    $is_latest = MywpControllerModuleAddOnSelectUserRolesUpdater::is_latest();

    if( is_wp_error( $is_latest ) ) {

      wp_send_json_error( array( 'error' => $is_latest->get_error_message() ) );

    }

    if( ! $is_latest ) {

      wp_send_json_success( array( 'is_latest' => 0 ) );

    } else {

      wp_send_json_success( array( 'is_latest' => 1 , 'message' => __( 'Using a latest version.' , 'mywp-add-on-select-user-roles' ) ) );

    }

  }

  public static function mywp_current_setting_screen_content() {

    $setting_data = self::get_setting_data();

    $all_user_roles = MywpApi::get_all_user_roles();

    $count_user_roles = MywpAddOnSelectUserRolesApi::get_count_user_roles();

    ?>
    <p><?php printf( __( '%1$s allows the management for only your selected user roles to be customized.' , 'mywp-add-on-select-user-roles' ) , MYWP_ADD_ON_SELECT_USER_ROLES_NAME ); ?></p>
    <p><?php _e( 'Select the user roles to customize below.' , 'mywp-add-on-select-user-roles' ); ?></p>

    <h3><?php _e( 'User Roles' ); ?></h3>
    <ul>

      <?php foreach( $all_user_roles as $user_role => $user_role_data ) : ?>

        <?php $checked = false; ?>
        <?php if( in_array( $user_role , $setting_data['select_user_roles'] ) ) : ?>
          <?php $checked = true; ?>
        <?php endif; ?>
        <li>
          <label>
            <input type="checkbox" name="mywp[data][select_user_roles][]" value="<?php echo esc_attr( $user_role ); ?>" <?php checked( $checked , true ); ?> />
            <?php echo $user_role_data['label']; ?>
            <code>
              <?php if( isset( $count_user_roles[ $user_role ] ) ) : ?>
                <?php printf( __( '%1$s <span class="count">(%2$s)</span>' ), $user_role, number_format_i18n( $count_user_roles[ $user_role ] ) ); ?>
              <?php else :?>
                <?php echo $user_role; ?>
              <?php endif; ?>
            </code>
          </label>
        </li>

      <?php endforeach; ?>

    </ul>

    <p>&nbsp;</p>
    <?php

  }

  public static function mywp_current_setting_screen_after_footer() {

    $is_latest = MywpControllerModuleAddOnSelectUserRolesUpdater::is_latest();

    $have_latest = false;

    if( ! is_wp_error( $is_latest ) && ! $is_latest ) {

      $have_latest = MywpControllerModuleAddOnSelectUserRolesUpdater::get_latest();

    }

    $plugin_info = MywpAddOnSelectUserRolesApi::plugin_info();

    $class_have_latest = '';

    if( $have_latest ) {

      $class_have_latest = 'have-latest';

    }

    ?>
    <p>&nbsp;</p>
    <h3><?php _e( 'Plugin info' , 'mywp-add-on-select-user-roles' ); ?></h3>
    <table class="form-table <?php echo esc_attr( $class_have_latest ); ?>" id="version-check-table">
      <tbody>
        <tr>
          <th><?php _e( 'Version' , 'mywp-add-on-select-user-roles' ); ?></th>
          <td>
            <code><?php echo MYWP_ADD_ON_SELECT_USER_ROLES_VERSION; ?></code>
            <a href="<?php echo esc_url( $plugin_info['github'] ); ?>" target="_blank" class="button button-primary link-latest"><?php printf( __( 'Get Version %s' ) , $have_latest ); ?></a>
            <p class="already-latest"><span class="dashicons dashicons-yes"></span> <?php _e( 'Using a latest version.' , 'mywp-add-on-select-user-roles' ); ?></p>
            <br />
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Check latest' , 'mywp-add-on-select-user-roles' ); ?></th>
          <td>
            <button type="button" id="check-latest-version" class="button button-secondary check-latest"><span class="dashicons dashicons-update"></span> <?php _e( 'Check latest version' , 'mywp-add-on-select-user-roles' ); ?></button>
            <span class="spinner"></span>
          </td>
        </tr>
        <tr>
          <th><?php _e( 'Document' , 'mywp-add-on-select-user-roles' ); ?></th>
          <td>
            <a href="<?php echo esc_url( $plugin_info['document_url'] ); ?>" class="button button-secondary" target="_blank"><span class="dashicons dashicons-book"></span> <?php _e( 'Document' , 'mywp-add-on-select-user-roles' ); ?>
          </td>
        </tr>
      </tbody>
    </table>

    <p>&nbsp;</p>
    <?php

  }

  public static function mywp_current_admin_print_footer_scripts() {

?>
<style>
#version-check-table .spinner {
  visibility: hidden;
}
#version-check-table.checking .spinner {
  visibility: visible;
}
#version-check-table .link-latest {
  margin-left: 12px;
  display: none;
}
#version-check-table .already-latest {
  display: inline-block;
}
#version-check-table .check-latest {
}
#version-check-table.have-latest .link-latest {
  display: inline-block;
}
#version-check-table.have-latest .already-latest {
  display: none;
}
</style>
<script>
jQuery(document).ready(function($){

  $('#check-latest-version').on('click', function() {

    var $version_check_table = $(this).parent().parent().parent().parent();

    $version_check_table.addClass('checking');

    PostData = {
      action: '<?php echo MywpSetting::get_ajax_action_name( self::$id , 'check_latest' ); ?>',
      <?php echo MywpSetting::get_ajax_action_name( self::$id , 'check_latest' ); ?>: '<?php echo wp_create_nonce( MywpSetting::get_ajax_action_name( self::$id , 'check_latest' ) ); ?>'
    };

    $.ajax({
      type: 'post',
      url: ajaxurl,
      data: PostData
    }).done( function( xhr ) {

      if( typeof xhr !== 'object' || xhr.success === undefined ) {

        $version_check_table.removeClass('checking');

        alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

        return false;

      }

      if( ! xhr.success ) {

        $version_check_table.removeClass('checking');

        alert( xhr.data.error );

        return false;

      }

      if( xhr.data.is_latest ) {

        alert( xhr.data.message );

        $version_check_table.removeClass('checking');

        return false;

      }

      location.reload();

      return true;

    }).fail( function( xhr ) {

      $version_check_table.removeClass('checking');

      alert( '<?php _e( 'An error has occurred. Please reload the page and try again.' ); ?>' );

      return false;

    });

  });

});
</script>
<?php

  }

  public static function mywp_current_setting_post_data_format_update( $formatted_data ) {

    $mywp_model = self::get_model();

    if( empty( $mywp_model ) ) {

      return $formatted_data;

    }

    $new_formatted_data = $mywp_model->get_initial_data();

    $new_formatted_data['advance'] = $formatted_data['advance'];

    if( ! empty( $formatted_data['select_user_roles'] ) ) {

      foreach( $formatted_data['select_user_roles'] as $user_role ) {

        if( empty( $user_role ) ) {

          continue;

        }

        $new_formatted_data['select_user_roles'][] = strip_tags( $user_role );

      }

    }

    return $new_formatted_data;

  }

}

MywpSettingScreenAddOnSelectUserRoles::init();

endif;
