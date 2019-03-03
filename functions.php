<?php
/**
 * JavaScript や CSS を読み込みます。
 */
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
  wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

/**
 * more タグで URL 末端に付く #more-xxxx を削除します。
 */
function remove_more_link_scroll( $link ) {
  $link = preg_replace( '|#more-[0-9]+|', '', $link );
  return $link;
}
add_filter( 'the_content_more_link', 'remove_more_link_scroll' );

/**
 * メニューに検索フォームを追加します。
 */
function add_search_box_to_menu( $items, $args ) {
  //var_dump($args);
  if( $args->theme_location == 'menu-1' ){
    return $items . '<li>' . get_search_form(false) . '</li>';
  }
}
add_filter( 'wp_nav_menu_items', 'add_search_box_to_menu', 10, 2);

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 * 色 > アイキャッチ画像にメインカラーのフィルターを適用する のチェックなしをデフォルトにします。
 * 上記の以外は、 Twenty Nineteen をほぼそのまま上書きするもので、バージョンは以下です。
 * バージョン: 1.3
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function child_twentynineteen_customize_register( $wp_customize ) {
  $wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
  $wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
  $wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

  if ( isset( $wp_customize->selective_refresh ) ) {
    $wp_customize->selective_refresh->add_partial(
      'blogname',
      array(
        'selector'        => '.site-title a',
        'render_callback' => 'twentynineteen_customize_partial_blogname',
      )
    );
    $wp_customize->selective_refresh->add_partial(
      'blogdescription',
      array(
        'selector'        => '.site-description',
        'render_callback' => 'twentynineteen_customize_partial_blogdescription',
      )
    );
  }

  /**
   * Primary color.
   */
  $wp_customize->add_setting(
    'primary_color',
    array(
      'default'           => 'default',
      'transport'         => 'postMessage',
      'sanitize_callback' => 'twentynineteen_sanitize_color_option',
    )
  );

  $wp_customize->add_control(
    'primary_color',
    array(
      'type'     => 'radio',
      'label'    => __( 'Primary Color', 'twentynineteen' ),
      'choices'  => array(
        'default' => _x( 'Default', 'primary color', 'twentynineteen' ),
        'custom'  => _x( 'Custom', 'primary color', 'twentynineteen' ),
      ),
      'section'  => 'colors',
      'priority' => 5,
    )
  );

  // Add primary color hue setting and control.
  $wp_customize->add_setting(
    'primary_color_hue',
    array(
      'default'           => twentynineteen_get_default_hue(),
      'transport'         => 'postMessage',
      'sanitize_callback' => 'absint',
    )
  );

  $wp_customize->add_control(
    new WP_Customize_Color_Control(
      $wp_customize,
      'primary_color_hue',
      array(
        'description' => __( 'Apply a custom color for buttons, links, featured images, etc.', 'twentynineteen' ),
        'section'     => 'colors',
        'mode'        => 'hue',
      )
    )
  );

  // Add image filter setting and control.
  $wp_customize->add_setting(
    'image_filter',
    array(
      'default'           => 0,
      'sanitize_callback' => 'absint',
      'transport'         => 'postMessage',
    )
  );

  $wp_customize->add_control(
    'image_filter',
    array(
      'label'   => __( 'Apply a filter to featured images using the primary color', 'twentynineteen' ),
      'section' => 'colors',
      'type'    => 'checkbox',
    )
  );
}

/**
 * 親テーマのアクションを子テーマのアクションに入れ替えます。
 */
function setup_after_parent_theme() {
  // 子テーマのアクションを追加し、親テーマのアクションを削除
  add_action( 'customize_register', 'child_twentynineteen_customize_register' );
  remove_action( 'customize_register', 'twentynineteen_customize_register' );
}
// 親テーマの後に実行
add_action( 'after_setup_theme', 'setup_after_parent_theme', 20 );

/**
 * デフォルトのサイトアイコンを設定します。
 * カスタマイザーで設定された場合はそちらを使用します。
 */
function filter_site_icon_meta_tags() {
  if (has_site_icon()) {
    return;
  }

  $url = get_stylesheet_directory_uri();
  // 出力される HTML ソースコードを見やすくするめに、最後に空白行を設置
  echo <<<EOT
<link rel="icon" href="{$url}/images/cropped-site_icon-32x32.jpg" sizes="32x32" />
<link rel="icon" href="{$url}/images/cropped-site_icon-192x192.jpg" sizes="192x192" />
<link rel="apple-touch-icon-precomposed" href="{$url}/images/cropped-site_icon-180x180.jpg" />
<meta name="msapplication-TileImage" content="{$url}/images/cropped-site_icon-270x270.jpg" />

EOT;
}
add_filter('wp_head', 'filter_site_icon_meta_tags');
