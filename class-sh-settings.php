<?php
/**
 * Main SyntaxHighlight Settings Class.
 */

 // Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'SH_Settings' ) ) :

class SH_Settings {

	/**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    public $checkboxes = array(
    	'word_wrap', 
    	'show_line_numbers', 
    	'use_soft_tabs', 
    	'highlight_curr_line', 
    	'full_line_selection', 
    	'unsaved_changes'
    );

    public $defaults = array(
  		'tab_size' => '4',
  		'show_line_numbers' => '1',
  		'word_wrap' => '0',
  		'use_soft_tabs' => '1',
  		'highlight_curr_line' => '1',
  		'key_bindings' => 'default',
  		'full_line_selection' => '1',
  		'theme' => 'chrome',
  		'unsaved_changes' => '1'
	);

	/**
	 * The main SyntaxHighlight settings loader.
	 *
	 * @since SyntaxHighlight (1.0)
	 *
	 * @uses SH_Settings::setup_globals() Setup the globals needed.
	 * @uses SH_Settings::setup_actions() Setup the hooks and actions.
	 */
	public function __construct() {
		$this->setup_globals();		
		$this->setup_actions();
	}

	/**
	 * Set admin-related globals.
	 *
	 * @access private
	 * @since SyntaxHighlight (1.0)
	 */
	private function setup_globals() {

		// Main settings page
		$this->settings_page 		= 'options-general.php';

		// SyntaxHighlight Settings slug (i.e. ?page=...)
		$this->sh_settings_id 		= 'sh_settings';

		// Main capability
		$this->capability 			= 'manage_options';

		// Options constants
		$this->option_group 		= 'sh_option_group';
		$this->option_name 			= 'sh_option_name';
		$this->setting_section_id 	= 'sh_settings_section';

        // Plugin text domain
        $this->td                   = 'syntax-highlight';
	}

	/**
	 * Set up the admin hooks, actions, and filters.
	 *
	 * @access private
	 * @since SyntaxHighlight (1.0)
	 *
	 * @uses add_action() To add various actions.
	 * @uses add_filter() To add various filters.
	 */
	private function setup_actions() {

		// Add actions
		add_action( 'admin_menu', 			array( $this, 'admin_menu' ) );
		add_action( 'admin_init', 			array( $this, 'admin_init' ) );

		// Add filters
		add_filter( 'plugin_action_links', 	array( $this, 'modify_plugin_action_links'), 10, 2 );
	}

	/**
	 * Add Settings link to plugins area.
	 *
	 * @since SyntaxHighlight (1.0)
	 *
	 * @param array $links Links array in which we would prepend our link.
	 * @param string $file Current plugin basename.
	 * @return array Processed links.
	 */
	public function modify_plugin_action_links($links, $file) {

		// Return normal links if not SyntaxHighlight
		if ( syntaxhigh()->basename != $file ) {
			return $links;
		}

		$url = add_query_arg( array( 'page' => $this->sh_settings_id ), admin_url( $this->settings_page ) );
		$link = '<a href="' . $url . '">' . __( 'Settings' ) . '</a>';

		return array_merge( $links, array(
			'settings' => $link
		) );
	}

	/**
	 * Add the navigational menu elements.
	 *
	 * @since SyntaxHighlight (1.0)
	 *
	 * @uses add_options_page() 
	 */
	public function admin_menu() {
		add_options_page( 
            __( 'Syntax Highlight Options', $this->td ),
			__( 'Syntax Highlight', $this->td ), 
			$this->capability, 
			$this->sh_settings_id, 
			array( $this, 'options_page') 
		);
	}

	/**
	 * Renders the SyntaxHighlight options page.
	 *
	 * @since SyntaxHighlight (1.0)
	 */
	public function options_page() {
		
		// Check if user has permissions
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// Set class property
        $this->options = wp_parse_args( get_option( $this->option_name ), $this->defaults );
        ?>
        <div class="wrap">
            <h2><?php _e('Settings', $this->td) ?></h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( $this->option_group );   
                do_settings_sections( $this->sh_settings_id );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
	}

	/**
     * Register and add settings
     * 
     * @since SyntaxHighlight (1.0)
     * 
     * @uses register_setting()
     * @uses add_settings_section()
     * @uses add_settings_field()
     */
    public function admin_init()
    {        
        register_setting(
            $this->option_group,		// Option group
            $this->option_name, 		// Option name
            array( $this, 'sanitize' ) 	// Sanitize
        );

        add_settings_section(
            $this->setting_section_id, // ID
            __( 'SyntaxHighlight Settings', $this->td ), // Title
            array( $this, 'print_section_info' ), // Callback
            $this->sh_settings_id // Page
        );  

        add_settings_field(
            'tab_size', // ID
            __( 'Default tab size', $this->td ), // Title 
            array( $this, 'tab_size_callback' ), // Callback
            $this->sh_settings_id, // Page
            $this->setting_section_id // Section           
        );

  		add_settings_field(
            'use_soft_tabs', // ID
            __( 'Use soft tabs?', $this->td ), // Title 
            array( $this, 'use_soft_tabs_callback' ), // Callback
            $this->sh_settings_id, // Page
            $this->setting_section_id // Section           
        );

        add_settings_field(
            'show_line_numbers', 
            __( 'Show line numbers?', $this->td ),
            array( $this, 'show_line_numbers_callback' ), 
            $this->sh_settings_id, 
            $this->setting_section_id
        );

        add_settings_field(
            'word_wrap', 
            __( 'Wrap words?', $this->td ),
            array( $this, 'word_wrap_callback' ), 
            $this->sh_settings_id, 
            $this->setting_section_id
        );

        add_settings_field(
            'highlight_curr_line', 
            __( 'Highlight current line?', $this->td ), 
            array( $this, 'highlight_curr_line_callback' ), 
            $this->sh_settings_id, 
            $this->setting_section_id
        );

        add_settings_field(
            'key_bindings', 
            __( 'Key bindings', $this->td ), 
            array( $this, 'key_bindings_callback' ), 
            $this->sh_settings_id, 
            $this->setting_section_id
        );

         add_settings_field(
            'theme', 
            __('Theme', $this->td ),
            array( $this, 'theme_callback' ), 
            $this->sh_settings_id, 
            $this->setting_section_id
        );

        add_settings_field(
            'full_line_selection', 
            __('Full line selection', $this->td ),
            array( $this, 'full_line_selection_callback' ), 
            $this->sh_settings_id, 
            $this->setting_section_id
        );

        add_settings_field(
            'unsaved_changes', 
            __('Show alert on leave when unsaved changes', $this->td ),
            array( $this, 'unsaved_changes_callback' ), 
            $this->sh_settings_id, 
            $this->setting_section_id
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input ) {

    	if( !is_array( $input ) || empty( $input ) || ( false === $input ) ) {
        	return array();
    	}

	    $new_input = $input;

        if( isset( $input['tab_size'] ) ) {
            $new_input['tab_size'] = intval( $input['tab_size'] );
            if ( $new_input['tab_size'] > 8) $new_input['tab_size'] = 8;
            if ( $new_input['tab_size'] < 1) $new_input['tab_size'] = 1;
        }

        foreach ($this->checkboxes as $option_name) {
        	if( isset( $input[$option_name] ) && ( 1 == $input[$option_name] ) ) {
	        	$new_input[$option_name] = 1;
	    	} else {
	    		$new_input[$option_name] = 0;
	    	}	
        }

	    unset( $input );
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info() {
        print __( 'Enter your settings below:', $this->td );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function tab_size_callback() {
        printf(
            '<input type="text" id="tab_size" name="' . $this->option_name . '[tab_size]" value="%s" />',
            isset( $this->options['tab_size'] ) ? esc_attr( $this->options['tab_size']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function show_line_numbers_callback() {
		?>
    	<input 
    		type="checkbox" 
    		id="show_line_numbers" 
    		name="<?php echo $this->option_name; ?>[show_line_numbers]" 
    		value="1" 
    		<?php checked( $this->options['show_line_numbers'], 1 ); ?> />
        <?php
    }

     /** 
     * Get the settings option array and print one of its values
     */
    public function unsaved_changes_callback() {
		?>
    	<input 
    		type="checkbox" 
    		id="unsaved_changes" 
    		name="<?php echo $this->option_name; ?>[unsaved_changes]" 
    		value="1" 
    		<?php checked( $this->options['unsaved_changes'], 1 ); ?> />
        <?php
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function word_wrap_callback() {
		?>
    	<input 
    		type="checkbox" 
    		id="word_wrap" 
    		name="<?php echo $this->option_name; ?>[word_wrap]" 
    		value="1" 
    		<?php checked( $this->options['word_wrap'], 1 ); ?> />
        <?php
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function use_soft_tabs_callback() {
		?>
    	<input 
    		type="checkbox" 
    		id="use_soft_tabs" 
    		name="<?php echo $this->option_name; ?>[use_soft_tabs]" 
    		value="1" 
    		<?php checked( $this->options['use_soft_tabs'], 1 ); ?> />
        <?php
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function highlight_curr_line_callback() {
		?>
    	<input 
    		type="checkbox" 
    		id="highlight_curr_line" 
    		name="<?php echo $this->option_name; ?>[highlight_curr_line]" 
    		value="1" 
    		<?php checked( $this->options['highlight_curr_line'], 1 ); ?> />
        <?php
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function full_line_selection_callback() {
		?>
    	<input 
    		type="checkbox" 
    		id="full_line_selection" 
    		name="<?php echo $this->option_name; ?>[full_line_selection]" 
    		value="1" 
    		<?php checked( $this->options['full_line_selection'], 1 ); ?> />
        <?php
    }

    public function key_bindings_callback() {
		?>
		<select 
			id="key_bindings" 
    		name="<?php echo $this->option_name; ?>[key_bindings]" 
			value="<?php esc_attr_e( $this->options['key_bindings'] ); ?>">
			<option value="default" <?php selected( 'default' == $this->options['key_bindings'] ); ?> >Default</option>
	    	<option value="vim" <?php selected( 'vim' == $this->options['key_bindings'] ); ?> >Vim</option>
	      	<option value="emacs" <?php selected( 'emacs' == $this->options['key_bindings'] ); ?> >Emacs</option>
    	</select>
        <a href="https://github.com/ajaxorg/ace/wiki/Default-Keyboard-Shortcuts" target="_blank">
            <?php echo __( 'List of default keyboard shortcuts', $this->td )?>
        </a>
        <?php
    }

    public function theme_callback() {
		?>
		<select 
			id="theme" 
    		name="<?php echo $this->option_name; ?>[theme]" 
			value="<?php esc_attr_e( $this->options['theme'] ); ?>">
			<option value="chrome" <?php selected( 'chrome' == $this->options['theme'] ); ?> >Chrome</option>
	    	<option value="tomorrow_night" <?php selected( 'tomorrow_night' == $this->options['theme'] ); ?> >Tomorrow Night</option>
    	</select>
        <?php
    }

}

endif; // class exists check

/**
 * Setup SyntaxHighlight Settings.
 */
syntaxhigh()->settings = new SH_Settings();
