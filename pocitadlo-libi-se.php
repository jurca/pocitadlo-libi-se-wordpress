<?php
/**
 * Plugin Name: Počítadlo Líbí se
 * Plugin URI: https://www.seznam.cz/
 * Description: Integrace sociálního tlačítka Líbí se od Seznam.cz.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Seznam.cz
 * Author URI: https://www.seznam.cz/
 * License: ISC
 * License URI: https://opensource.org/licenses/ISC
 */

require_once __DIR__ . '/pocitadlo-libi-se-common/index.php';

use function Cz\Seznam\PocitadloLibiSe\renderButton;
use function Cz\Seznam\PocitadloLibiSe\renderButtonScript;
use Cz\Seznam\PocitadloLibiSe\ButtonColorVariable;
use Cz\Seznam\PocitadloLibiSe\ButtonElementAttributeName;
use Cz\Seznam\PocitadloLibiSe\ButtonLayout;
use Cz\Seznam\PocitadloLibiSe\ButtonSize;

add_action( 'wp_head', function () {
  $styles = file_get_contents( __DIR__ . '/pocitadlo-libi-se-common/pocitadlolibise.css' );
  echo "<style>\n$styles</style>\n";
  echo renderButtonScript() . "\n";
} );

(function () {
  $PAGE_ID = 'cz-seznam-pocitadlolibise';
  $OPTION_NAME = "$PAGE_ID-options";
  
  final class ButtonPosition {
    const CONTENT_START = 'content_start';
    const CONTENT_END = 'content_end';
    const CONTENT_START_AND_END = 'content_start_and_end';
    
    private function __construct() {}
  }

  $DEFAULT_OPTIONS = [
    ButtonElementAttributeName::LAYOUT => ButtonLayout::SEAMLESS,
    ButtonElementAttributeName::SIZE => ButtonSize::SMALL,
    'button_position' => ButtonPosition::CONTENT_END,
    ButtonColorVariable::PRIMARY_COLOR => '#111111',
    ButtonColorVariable::BACKGROUND_COLOR => '#ffffff',
    ButtonColorVariable::HOVER_COLOR => '#888888',
    ButtonColorVariable::COUNT_COLOR => '#888888',
    ButtonColorVariable::ACTIVE_COLOR => '#de0000',
    ButtonElementAttributeName::ANALYTICS_HIT_PAYLOAD => '',
  ];
  $PLACEHOLDER = '<span></span>';

  add_filter( 'the_content', function ($content) use ($OPTION_NAME, $DEFAULT_OPTIONS, $PLACEHOLDER) {
    if ( is_main_query() && in_the_loop() && is_single() ) {
      $options = get_option( $OPTION_NAME, $DEFAULT_OPTIONS );

      $buttonOptions = [
        ButtonElementAttributeName::ENTITY_ID => get_permalink(),
        ButtonElementAttributeName::LAYOUT => $options[ ButtonElementAttributeName::LAYOUT ],
        ButtonElementAttributeName::SIZE => $options[ ButtonElementAttributeName::SIZE ],
        ButtonElementAttributeName::ANALYTICS_HIT_PAYLOAD => $options[
          ButtonElementAttributeName::ANALYTICS_HIT_PAYLOAD
        ],
        'colors' => [
          ButtonColorVariable::PRIMARY_COLOR => $options[ ButtonColorVariable::PRIMARY_COLOR ],
          ButtonColorVariable::BACKGROUND_COLOR => $options[ ButtonColorVariable::BACKGROUND_COLOR ],
          ButtonColorVariable::HOVER_COLOR => $options[ ButtonColorVariable::HOVER_COLOR ],
          ButtonColorVariable::COUNT_COLOR => $options[ ButtonColorVariable::COUNT_COLOR ],
          ButtonColorVariable::ACTIVE_COLOR => $options[ ButtonColorVariable::ACTIVE_COLOR ],
        ],
      ];

      if (
        $options[ 'button_position' ] === ButtonPosition::CONTENT_START ||
        $options[ 'button_position' ] === ButtonPosition::CONTENT_START_AND_END
      ) {
        $socialButton = renderButton(
          array_merge(
            $buttonOptions,
            [ ButtonElementAttributeName::ANALYTICS_HIT_BUTTON_POSITION => ButtonPosition::CONTENT_START ],
          ),
          $PLACEHOLDER,
        );
        $content = "<div class=\"seznam-pocitadlo-libi-se\">$socialButton</div>\n$content";
      }
      if (
        $options[ 'button_position' ] === ButtonPosition::CONTENT_END ||
        $options[ 'button_position' ] === ButtonPosition::CONTENT_START_AND_END
      ) {
        $socialButton = renderButton(
          array_merge(
            $buttonOptions,
            [ ButtonElementAttributeName::ANALYTICS_HIT_BUTTON_POSITION => ButtonPosition::CONTENT_END ],
          ),
          $PLACEHOLDER,
        );
        $content = "$content\n<div class=\"seznam-pocitadlo-libi-se\">$socialButton</div>";
      }

      return $content;
    }
  
    return $content;
  } );
  
  $settings_section_header = function ($args) {
    echo "<a id=\"" . esc_attr( $args['id'] ) . "\"></p>";
  };

  $settings_page = function () use ($PAGE_ID) {
    if ( ! current_user_can( 'manage_options' ) ) {
      return;
    }

    if ( isset( $_GET['settings-updated'] ) ) {
      add_settings_error(
        'cz_seznam_pocitadlolibise_messages',
        'cz_seznam_pocitadlolibise_message',
        'Nastavení bylo uloženo.',
        'success',
      );
    }

    settings_errors( 'cz_seznam_pocitadlolibise_messages' );

    ?>
      <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
          <?php
          settings_fields( $PAGE_ID );
          // (sections are registered for "wporg", each field is registered to a specific section)
          do_settings_sections( $PAGE_ID );
          // output save settings button
          submit_button( 'Uložit nastavení' );
          ?>
        </form>
      </div>
    <?php
  };

  $settings_field_input = function ($args) use ($OPTION_NAME, $DEFAULT_OPTIONS) {
    $options = get_option( $OPTION_NAME, $DEFAULT_OPTIONS );
    ?>
      <input
        id="<?php echo esc_attr( $args['label_for'] ); ?>"
        name="<?php echo esc_attr( $OPTION_NAME ) ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
        type="<?php echo esc_attr( $args['type'] ) ?>"
        value="<?php echo esc_attr( $options[ $args['label_for'] ] ) ?>"
      >
    <?php
  };

  $settings_field_select = function ($args) use ($OPTION_NAME, $DEFAULT_OPTIONS) {
    $options = get_option( $OPTION_NAME, $DEFAULT_OPTIONS );
    ?>
      <select
        id="<?php echo esc_attr( $args['label_for'] ); ?>"
        name="<?php echo esc_attr( $OPTION_NAME ) ?>[<?php echo esc_attr( $args['label_for'] ); ?>]"
      >
        <?php foreach ( $args['options'] as $value => $label ) {
          ?>
            <option
              value="<?php echo esc_attr( $value ) ?>"
              <?php echo isset( $options[ $args['label_for'] ] ) ? ( selected( $options[ $args['label_for'] ], $value, false ) ) : ( '' ); ?>
            >
              <?php echo esc_html( $label ) ?>
            </option>
          <?php
        } ?>
      </select>
    <?php
  };

  add_action( 'admin_init', function () use (
    $PAGE_ID,
    $OPTION_NAME,
    $DEFAULT_OPTIONS,
    $settings_section_header,
    $settings_field_input,
    $settings_field_select
  ) {
    $SETTINGS_SECTION = 'cz_seznam_pocitadlolibise';

    register_setting( $PAGE_ID, $OPTION_NAME, [
      'type' => 'array',
      'description' => 'Configuration for the Líbí se social button by Seznam.cz',
      'show_in_rest' => false,
      'default' => $DEFAULT_OPTIONS,
    ] );

    add_settings_section(
      $SETTINGS_SECTION,
      'Nastavení vzhledu',
      $settings_section_header,
      $PAGE_ID,
    );

    $szn_add_settings_field = function ($name, $label, $callback, $options) use ($PAGE_ID, $SETTINGS_SECTION) {
      add_settings_field(
        $name,
        $label,
        $callback,
        $PAGE_ID,
        $SETTINGS_SECTION,
        array_merge(
          [
            'label_for' => $name,
          ],
          $options,
        ),
      );
    };
    
    $szn_add_settings_input = function ($name, $label, $type) use ($szn_add_settings_field, $settings_field_input) {
      $szn_add_settings_field($name, $label, $settings_field_input, [ 'type' => $type ]);
    };

    $szn_add_settings_select = function ($name, $label, $options) use (
      $szn_add_settings_field,
      $settings_field_select
    ) {
      $szn_add_settings_field($name, $label, $settings_field_select, [ 'options' => $options ]);
    };

    $szn_add_settings_select(
      ButtonElementAttributeName::LAYOUT,
      'Šablona vzhledu',
      [
        ButtonLayout::SEAMLESS => 'Obarvitelná',
        ButtonLayout::BUTTON_COUNT => 'Tlačítko',
        ButtonLayout::BOX_COUNT => 'Box',
      ],
    );

    $szn_add_settings_select(
      ButtonElementAttributeName::SIZE,
      'Velikost',
      [
        ButtonSize::MINIMALISTIC => 'Minimalistická (pouze obarvitelný vzhled)',
        ButtonSize::SMALL => 'Malá',
        ButtonSize::LARGE => 'Velká (nepodporuje obarvitelný vzhled)',
      ],
    );

    $szn_add_settings_select(
      'button_position',
      'Umístění tlačítka',
      [
        ButtonPosition::CONTENT_START => 'Začátek příspěvku',
        ButtonPosition::CONTENT_END => 'Konec příspěvku',
        ButtonPosition::CONTENT_START_AND_END => 'Začátek i konec příspěvku',
      ],
    );

    $szn_add_settings_input(ButtonColorVariable::PRIMARY_COLOR, 'Základní barva textu', 'color');
    $szn_add_settings_input(ButtonColorVariable::BACKGROUND_COLOR, 'Barva pozadí', 'color');
    $szn_add_settings_input(ButtonColorVariable::HOVER_COLOR, 'Barva textu při najetí myší', 'color');
    $szn_add_settings_input(ButtonColorVariable::COUNT_COLOR, 'Barva počtu hlasů', 'color');
    $szn_add_settings_input(ButtonColorVariable::ACTIVE_COLOR, 'Barva textu po zahlasování', 'color');

    $szn_add_settings_input(ButtonElementAttributeName::ANALYTICS_HIT_PAYLOAD, 'Dodatečná data pro analytiku', 'text');
  } );

  add_action( 'admin_menu', function () use ($PAGE_ID, $settings_page) {
    add_options_page(
      'Počítadlo Líbí se',
      'Počítadlo Líbí se',
      'manage_options',
      $PAGE_ID,
      $settings_page,
    );
  } );

  add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ($links) use ($PAGE_ID) {
    $links[] = '<a href="' . esc_url( get_admin_url( null, 'options-general.php?page=' . $PAGE_ID ) ) . '">Nastavení</a>';
    return $links;
  } );
})();
