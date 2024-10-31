<?php 
class CP_Help
{
	public $tabs = array(
		// The assoc key represents the ID
		// It is NOT allowed to contain spaces
		 'EXAMPLE' => array(
		 	 'title'   => 'TEST ME!'
		 	,'content' => 'FOO'
		 )
	);

	public function __construct()
	{
		add_action( "current_screen", array( $this, 'add_tabs' ), 999 );
	}

	public function add_tabs(){
		$screen = get_current_screen();
		foreach ( $this->tabs as $id => $data )
		{
			$screen->add_help_tab( array(
				 'id'       => $id
				,'title'    => __( $data['title'], 'some_textdomain' )
				// Use the content only if you want to add something
				// static on every help tab. Example: Another title inside the tab
				,'content'  => '<p>Some stuff that stays above every help text</p>'
				,'callback' => array( $this, 'prepare' )
			) );
		}
	}

	public function prepare( $screen, $tab ) {
		
	    	printf( 
			 '<p>%s</p>'
			,__( 
	    			 $tab['callback'][0]->tabs[ $tab['id'] ]['content']
				,'dmb_textdomain' 
			 )
		);
	}
}
return new CP_Help();