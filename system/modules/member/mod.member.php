<?php

/*
=====================================================
 ExpressionEngine - by EllisLab
-----------------------------------------------------
 http://expressionengine.com/
-----------------------------------------------------
 Copyright (c) 2003 - 2009 EllisLab, Inc.
=====================================================
 THIS IS COPYRIGHTED SOFTWARE
 PLEASE READ THE LICENSE AGREEMENT
 http://expressionengine.com/docs/license.html
=====================================================
 File: mod.member.php
-----------------------------------------------------
 Purpose: Member Management Class
 Note: Because member management is so tightly
 integrated into the core system, most of the 
 member functions are contained in the core and cp
 files.
=====================================================

Multi Site Login

The login routine can set cookies for multiple domains if needed. 
This allows users who run separate domains for each blog to have
a way to enable users to log-in once and remain logged-in across
domains.  In order to use this feature this array index must be
added to the config file:

$conf['multi_login_sites'] = "http://www.siteone.com/|http://www.sitetwo.com";

Separate each domain with a pipe.

*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Member {

	var $trigger			= 'member';
	var $theme_class		= 'profile_theme';
	var $request			= 'public_profile';
	var $no_menu 			= array('public_profile', 'memberlist', 'do_member_search', 'member_search', 'register', 'smileys', 'login', 'unpw_update', 'email_console', 'send_email', 'aim_console', 'icq_console', 'forgot_password', 'delete', 'member_mini_search', 'do_member_mini_search');
	var $no_login 			= array('public_profile', 'memberlist', 'do_member_search', 'member_search', 'register', 'forgot_password', 'unpw_update');
	var $id_override		= array('edit_subscriptions', 'memberlist', 'member_search', 'browse_avatars', 'messages', 'unpw_update');
	var $no_breadcrumb 		= array('email_console', 'send_email', 'aim_console', 'icq_console', 'member_mini_search', 'do_member_mini_search');
	var $simple_page		= array('email_console', 'send_email', 'aim_console', 'icq_console', 'smileys', 'member_mini_search', 'do_member_mini_search');
	var $page_title 		= '';
	var $basepath			= '';
	var $forum_path			= '';
	var $image_url			= '';
	var $theme_path			= '';
	var $cur_id				= '';
	var $uri_extra			= '';
	var $return_data		= '';
	var $javascript			= '';
	var $head_extra			= '';
	var $var_single			= '';
	var $var_pair			= '';
	var $var_cond			= '';
	var $css_file_path		= '';
	var $board_id			= '';
	var $show_headings 		= TRUE;
	var $in_forum			= FALSE;
	var $is_admin			= FALSE;
	var $breadcrumb			= TRUE;
	var $us_datecodes 		= array('long'	=>	'%F %d, %Y &nbsp;%h:%i %A');
	var $eu_datecodes 		= array('long'	=>	'%d %F, %Y &nbsp;%H:%i');
	var $crumb_map 			= array(
								'profile'				=>	'your_control_panel',
								'delete'				=>	'mbr_delete',
								'forgot_password'		=>	'mbr_forgotten_password',
								'login'					=>	'mbr_login',
								'unpw_update'			=>  'settings_update',
								'register'				=> 	'mbr_member_registration',						
								'email'					=>	'mbr_email_member',
								'send_email'			=>	'mbr_send_email',
								'aim'					=>	'mbr_aim_console',
								'icq'					=>	'mbr_icq_console',
								'profile_main'			=>	'mbr_my_account',
								'edit_profile'			=>	'mbr_edit_your_profile',	
								'edit_email'			=>	'email_settings',	
								'edit_userpass'			=>	'username_and_password',
								'edit_localization'		=>	'localization_settings',
								'edit_subscriptions'	=>	'subscriptions',
								'edit_ignore_list'		=>	'ignore_list',
								'edit_notepad'			=>	'notepad',
								'edit_avatar'			=>	'edit_avatar',
								'edit_photo'			=>	'edit_photo',
								'edit_preferences'		=>	'edit_preferences',
								'update_preferences'	=> 	'update_preferences',
								'upload_photo'			=>	'update_photo',
								'browse_avatars'		=>	'browse_avatars',
								'update_profile'		=>	'profile_updated',
								'update_email'			=>	'mbr_email_updated',
								'update_userpass'		=>	'username_and_password',
								'update_localization'	=>	'localization_settings',
								'update_subscriptions'	=>	'subscription_manager',
								'update_ignore_list'	=>	'ignore_list',
								'update_notepad'		=>	'notepad',
								'select_avatar'			=>	'update_avatar',
								'upload_avatar'			=>	'upload_avatar',
								'update_avatar'			=>	'update_avatar',
								'pm_view'				=>	'private_messages',
								'pm'					=>	'compose_message',
								'view_folder'			=>  'view_folder',
								'view_message'			=>	'view_message',
								'edit_signature'		=>	'edit_signature',
								'update_signature'		=>  'update_signature',
								'compose'				=> 	'compose_message',
								'deleted'				=> 	'deleted_messages',
								'folders'				=>	'edit_folders',
								'buddies'				=>	'buddy_list',
								'blocked'				=>	'blocked_list',
								'edit_folders'			=>  'edit_folders',
								'inbox'					=>  'view_folder',
								'edit_list'				=>  'edit_list',
								'send_message'			=>  'view_folder',
								'modify_messages'		=>  'private_messages',
								'bulletin_board'		=>	'bulletin_board',
								'send_bulletin'			=>  'send_bulletin',
								'sending_bulletin'		=>	'sending_bulletin'
								);

			

    /** ----------------------------------
    /**  Member Profile Constructor
    /** ----------------------------------*/

	function Member()
	{
		global $DB, $LANG, $FNS;
		
		/** ----------------------------------
		/**  Load language files
		/** ----------------------------------*/
		
        $LANG->fetch_language_file('myaccount');
        $LANG->fetch_language_file('member');  
		$FNS->template_type = 'webpage';
		$DB->enable_cache = FALSE;
	}
	/* END */


	/** ----------------------------------
	/**  Prep the Request String
	/** ----------------------------------*/

	function _prep_request()
	{
		global $IN, $REGX;
		
		// Typcially the profile page URLs will be something like:
		//
		// index.php/member/123/
		// index.php/member/memberlist/
		// index.php/member/profile/
		// etc...
		//
		// The second segment will be assigned to the $this->request variable.
		// This determines what page is shown. Anything after that will normally 
		// be an ID number, so we'll assign it to the $this->cur_id variable.
	
		$this->request = $REGX->trim_slashes($IN->URI);
		
		if (FALSE !== ($pos = strpos($this->request, $this->trigger.'/')))
		{
			$this->request = substr($this->request, $pos);
		}
		
		if (preg_match("#/simple#", $this->request))
		{
			$this->request = str_replace("/simple", '', $this->request);
			$this->show_headings = FALSE;
		}
		
		if ($this->request == $this->trigger)
		{
			$this->request = '';
		}
		elseif (strpos($this->request, '/') !== FALSE)
		{			
			$xr = explode("/", $this->request);
			$this->request = str_replace(current($xr).'/', '', $this->request);
		}
		
		/** ----------------------------------
		/**  Determine the ID number, if any
		/** ----------------------------------*/
		
		$this->cur_id = '';

		if (strpos($this->request, '/') !== FALSE)
		{
			$x = explode("/", $this->request);
			
			if (count($x) > 2)
			{
				$this->request		= $x['0'];
				$this->cur_id		= $x['1'];
				$this->uri_extra	= $x['2'];
			}
			else
			{
				$this->request		= $x['0'];
				$this->cur_id		= $x['1'];
			}
		}				
 
		/** ----------------------------------
		/**  Is this a public profile request?
		/** ----------------------------------*/
		
		// Public member profiles are found at:
		//
		// index.php/member/123/
		//
		// Since the second segment contains a number instead of the
		// normal text string we know it's a public profile request.
		// We'll do a little reassignment...		
		
 		if (is_numeric($this->request))
 		{	
 			$this->cur_id	= $this->request;
 			$this->request	= 'public_profile';
 		}
 		
		if ($this->request == '')
		{
 			$this->request	= 'public_profile';
		}
 		 		
		/** ----------------------------------
		/**  Disable the full page view
		/** ----------------------------------*/
 		
 		if (in_array($this->request, $this->simple_page))
 		{
			$this->show_headings = FALSE;
 		}
 		
 		if (in_array($this->request, $this->no_breadcrumb))
 		{
			$this->breadcrumb = FALSE;
 		}
 		
		/** ----------------------------------
		/**  Validate ID number
		/** ----------------------------------*/
		
		// The $this->cur_id variable can only contain a number.
		// There are a few exceptions like the memberlist page and the
		// subscriptions page
 
 		if ( ! in_array($this->request, $this->id_override) AND $this->cur_id != '' AND ! is_numeric($this->cur_id))
 		{
 			return FALSE;
 		}
 		
 		return TRUE;
	}
	/* END */



    /** ----------------------------------
    /**  Run the Member Class
    /** ----------------------------------*/

	function manager()
	{
		global $IN, $FNS, $REGX, $SESS, $LANG, $OUT, $EXT;
		
		/** ---------------------------------
		/**  Prep the request
		/** ---------------------------------*/
		
		if ( ! $this->_prep_request())
		{
			exit("Invalid Page Request");
		}
		
		// -------------------------------------------
        // 'member_manager' hook.
        //  - Seize control over any Member Module user side request
        //  - Added: 1.5.2
        //
			if ($EXT->active_hook('member_manager') === TRUE)
			{
				$edata = $EXT->universal_call_extension('member_manager', $this);
				if ($EXT->end_script === TRUE) return $edata;
			}	
        //
        // -------------------------------------------

		/** ---------------------------------
		/**  Is the user logged in?
		/** ---------------------------------*/
		
		if ($this->request != 'login' AND ! in_array($this->request, $this->no_login) AND $SESS->userdata('member_id') == 0)
		{
			return $this->_final_prep($this->profile_login_form('self'));
 		}
		/** ---------------------------------
		/**  Left-side Menu
		/** ---------------------------------*/
		
		$left = ( ! in_array($this->request, $this->no_menu)) ? $this->profile_menu() : '';

		/** ------------------------------
		/**  Validate the request
		/** ------------------------------*/
		
		$methods = array(
							'public_profile',
							'memberlist',
							'member_search',
							'do_member_search',
							'login',
							'unpw_update',
							'register',
							'profile',
							'edit_preferences',
							'update_preferences',
							'edit_profile',
							'update_profile',
							'edit_email',
							'update_email',
							'edit_userpass',
							'update_userpass',
							'edit_localization',
							'update_localization',
							'edit_notepad',
							'update_notepad',
							'edit_signature',
							'update_signature',
							'edit_avatar',
							'browse_avatars',
							'select_avatar',
							'upload_avatar',
							'edit_photo',
							'upload_photo',
							'edit_subscriptions',
							'update_subscriptions',
							'edit_ignore_list',
							'update_ignore_list',
							'member_mini_search',
							'do_member_mini_search',
							'email_console',
							'aim_console',
							'icq_console',
							'send_email',
							'forgot_password',
							'smileys',
							'messages',
							'delete'
						);
		
		
		if ( ! in_array($this->request, $methods))
		{
        	return $OUT->show_user_error('general', array($LANG->line('invalid_action')));		
		}
		
		/** ------------------------------
		/**  Call the requested function
		/** ------------------------------*/
		
		if ($this->request == 'profile')	$this->request = 'profile_main';
		if ($this->request == 'register')	$this->request = 'registration_form';
		if ($this->cur_id  == 'member_search')		{$left = ''; $this->breadcrumb = FALSE; $this->show_headings = FALSE;}
		if ($this->cur_id  == 'do_member_search')	{$left = ''; $this->breadcrumb = FALSE; $this->show_headings = FALSE;}
		if ($this->cur_id  == 'buddy_search')		{$left = ''; $this->breadcrumb = FALSE; $this->show_headings = FALSE;}
		if ($this->cur_id  == 'do_buddy_search')	{$left = ''; $this->breadcrumb = FALSE; $this->show_headings = FALSE;}
								
		$function = $this->request;
		
		if (in_array($function, array('upload_photo', 'upload_avatar', 'upload_signature_image', '_upload_image')))
		{
			require_once PATH_MOD.'member/mod.member_images.php';
    	
    		$MI = new Member_images();
    		
			foreach(get_object_vars($this) as $key => $value)
			{
				$MI->{$key} = $value;
			}
    		
    		$content = $MI->$function();
		}
		else
		{
			$content = $this->$function();
		}
		
		if ($this->cur_id  == 'edit_folders')	{$left = $this->profile_menu();}
		if ($this->cur_id  == 'send_message')	{$left = $this->profile_menu();}

		/** ------------------------------
		/**  Parse the template the template
		/** ------------------------------*/
		
		if ($left == '')
		{
			$out = $this->_var_swap($this->_load_element('basic_profile'),
									array(
											'include:content'	=> $content
										 )
									 );	
		}
		else
		{
			$out = $this->_var_swap($this->_load_element('full_profile'),
									array(
											'include:menu'		=> $left,
											'include:content'	=> $content
										 )
									 );	
		}
		
		
		/** ------------------------------
		/**  Output the finalized request
		/** ------------------------------*/
		
		return $this->_final_prep($out);
	}
	/* END */


    /** ----------------------------------------
    /**  Private Messages
    /** ----------------------------------------*/

	function messages()
	{
		global $SESS, $IN;
		
		if (($SESS->userdata['can_send_private_messages'] != 'y' && $SESS->userdata['group_id'] != '1') OR $SESS->userdata['accept_messages'] != 'y')
		{
			return $this->profile_main();
		}
        
        if ( ! class_exists('Messages'))
		{
			require PATH_CORE.'core.messages'.EXT;
		}
		
		$MESS = new Messages;
		$MESS->base_url = $this->_member_path('messages');
		$MESS->allegiance = 'user';
		$MESS->theme_class = $this->theme_class;
		$MESS->request = $this->cur_id;
		$MESS->cur_id = $this->uri_extra;
		$MESS->manager();
		
		$this->page_title = $MESS->title;
		$this->head_extra = $MESS->header_javascript;
		return $MESS->return_data;
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Member Profile - Menu
    /** ----------------------------------------*/

	function profile_menu()
	{		
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->profile_menu();	
	}
	/* END */
	
	/** ----------------------------------------
    /**  Private Messages - Menu
    /** ----------------------------------------*/

	function pm_menu()
	{
		global $SESS;
		
		if (($SESS->userdata['can_send_private_messages'] != 'y' && $SESS->userdata['group_id'] != '1') OR $SESS->userdata['accept_messages'] != 'y')
		{
			return;
		}
		
		if ( ! class_exists('Messages'))
		{
			require PATH_CORE.'core.messages'.EXT;
		}
		
		$MESS = new Messages;
		$MESS->base_url = $this->_member_path('messages');
		$MESS->allegiance  = 'user';
		$MESS->theme_class = $this->theme_class;
		$MESS->create_menu();
		return $MESS->menu;
	}
	/* END */
	
	
	
    /** ----------------------------------------
    /**  Member Profile Main Page
    /** ----------------------------------------*/

	function profile_main()
	{	
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->profile_main();	
	}
	/* END */

	
	
    /** ----------------------------------------
    /**  Member Public Profile
    /** ----------------------------------------*/

    function public_profile()
    {    
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->public_profile();	
	}
	/* END */



    /** ----------------------------------------
    /**  Login Page
    /** ----------------------------------------*/
    
	function profile_login_form($return = '-2')
	{
		if ( ! class_exists('Member_auth'))
    	{
    		require PATH_MOD.'member/mod.member_auth.php';
    	}
    	
    	$MA = new Member_auth();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MA->{$key} = $value;
		}
    	
    	return $MA->profile_login_form($return);
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Member Profile Edit Page
    /** ----------------------------------------*/

	function edit_profile()
	{
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->edit_profile();
	}
	/* END */


	
    /** ----------------------------------------
    /**  Profile Update
    /** ----------------------------------------*/

	function update_profile()
	{
        if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->update_profile();
	}
	/* END */


    /** ----------------------------------------
    /**  Forum Preferences
    /** ----------------------------------------*/

	function edit_preferences()
	{
     	if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->edit_preferences();
	}
	/* END */

	

	
    /** ----------------------------------------
    /**  Update  Preferences
    /** ----------------------------------------*/

	function update_preferences()
	{
        if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->update_preferences();
	}
	/* END */
	

    /** ----------------------------------------
    /**  Email Settings
    /** ----------------------------------------*/

	function edit_email()
	{
     	if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->edit_email();
	}
	/* END */

	
	
	
    /** ----------------------------------------
    /**  Email Update
    /** ----------------------------------------*/

	function update_email()
	{
        if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->update_email();
	}
	/* END */
	
	
	
    /** ----------------------------------------
    /**  Username/Password Preferences
    /** ----------------------------------------*/

	function edit_userpass()
	{
     	if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->edit_userpass();
	}
	/* END */

	
	
	
    /** ----------------------------------------
    /**  Username/Password Update
    /** ----------------------------------------*/

	function update_userpass()
	{
        if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->update_userpass();
	}
	/* END */
	
	
	
    /** ----------------------------------------
    /**  Localization Edit Form
    /** ----------------------------------------*/
	
	function edit_localization()
	{
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->edit_localization();
	}
	/* END */
	
	
	
	
    /** ----------------------------------------
    /**  Update Localization Prefs
    /** ----------------------------------------*/
	
	function update_localization()
	{
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->update_localization();
	}
	/* END */
	


    /** ----------------------------------------
    /**  Signature Edit Form
    /** ----------------------------------------*/
	
	function edit_signature()
	{
		if ( ! class_exists('Member_images'))
    	{
    		require PATH_MOD.'member/mod.member_images.php';
    	}
    	
    	$MI = new Member_images();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MI->{$key} = $value;
		}
    	
    	return $MI->edit_signature();
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Update Signature
    /** ----------------------------------------*/
	
	function update_signature()
	{
		if ( ! class_exists('Member_images'))
    	{
    		require PATH_MOD.'member/mod.member_images.php';
    	}
    	
    	$MI = new Member_images();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MI->{$key} = $value;
		}
    	
    	return $MI->update_signature();
	}
	/* END */
	
	

    /** ----------------------------------------
    /**  Avatar Edit Form
    /** ----------------------------------------*/
	
	function edit_avatar()
	{
		if ( ! class_exists('Member_images'))
    	{
    		require PATH_MOD.'member/mod.member_images.php';
    	}
    	
    	$MI = new Member_images();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MI->{$key} = $value;
		}
		
    	return $MI->edit_avatar();
	}
	/* END */
	

    /** ----------------------------------------
    /**  Browse Avatars
    /** ----------------------------------------*/
	
	function browse_avatars()
	{
		if ( ! class_exists('Member_images'))
    	{
    		require PATH_MOD.'member/mod.member_images.php';
    	}
    	
    	$MI = new Member_images();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MI->{$key} = $value;
		}
    	
    	return $MI->browse_avatars();	
	}
	/* END */
	

    /** ----------------------------------------
    /**  Select Avatar From  Library
    /** ----------------------------------------*/
	
	function select_avatar()
	{
		if ( ! class_exists('Member_images'))
    	{
    		require PATH_MOD.'member/mod.member_images.php';
    	}
    	
    	$MI = new Member_images();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MI->{$key} = $value;
		}
    	
    	return $MI->select_avatar();
	}
	/* END */
	
	

    /** ----------------------------------------
    /**  Photo Edit Form
    /** ----------------------------------------*/
	
	function edit_photo()
	{
		if ( ! class_exists('Member_images'))
    	{
    		require PATH_MOD.'member/mod.member_images.php';
    	}
    	
    	$MI = new Member_images();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MI->{$key} = $value;
		}
    	
    	return $MI->edit_photo();
	}
	/* END */
	


    /** ----------------------------------------
    /**  Notepad Edit Form
    /** ----------------------------------------*/
	
	function edit_notepad()
	{
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->edit_notepad();
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Update Notepad
    /** ----------------------------------------*/
	
	function update_notepad()
	{
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->update_notepad();
	}
	/* END */
	
	

    /** ----------------------------------------
    /**  Member Login
    /** ----------------------------------------*/

    function member_login()
    {
        if ( ! class_exists('Member_auth'))
    	{
    		require PATH_MOD.'member/mod.member_auth.php';
    	}
    	
    	$MA = new Member_auth();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MA->{$key} = $value;
		}
    	
    	$MA->member_login();
    }
    /* END */


    /** ----------------------------------------
    /**  Member Logout
    /** ----------------------------------------*/

    function member_logout()
    {
        if ( ! class_exists('Member_auth'))
    	{
    		require PATH_MOD.'member/mod.member_auth.php';
    	}
    	
    	$MA = new Member_auth();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MA->{$key} = $value;
		}
    	
    	$MA->member_logout();
    }
    /* END */


	

    /** ----------------------------------------
    /**  Member Forgot Password Form
    /** ----------------------------------------*/

    function forgot_password($ret = '-3')
    {
		if ( ! class_exists('Member_auth'))
    	{
    		require PATH_MOD.'member/mod.member_auth.php';
    	}
    	
    	$MA = new Member_auth();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MA->{$key} = $value;
		}
    	
    	return $MA->forgot_password($ret);
    }
    /* END */



    /** ----------------------------------------
    /**  Retreive Forgotten Password
    /** ----------------------------------------*/

    function retrieve_password()
    {
        if ( ! class_exists('Member_auth'))
    	{
    		require PATH_MOD.'member/mod.member_auth.php';
    	}
    	
    	$MA = new Member_auth();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MA->{$key} = $value;
		}
    	
    	$MA->retrieve_password();
	}
	/* END */



	/** ----------------------------------------
	/**  Reset the user's password
	/** ----------------------------------------*/

	function reset_password()
	{
        if ( ! class_exists('Member_auth'))
    	{
    		require PATH_MOD.'member/mod.member_auth.php';
    	}
    	
    	$MA = new Member_auth();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MA->{$key} = $value;
		}
    	
    	$MA->reset_password();
	}
	/* END */



    /** ----------------------------------------
    /**  Subscriptions Edit Form
    /** ----------------------------------------*/
	
	function edit_subscriptions()
	{
		if ( ! class_exists('Member_subscriptions'))
    	{
    		require PATH_MOD.'member/mod.member_subscriptions.php';
    	}
    	
    	$MS = new Member_subscriptions();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->edit_subscriptions();
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Update Subscriptions
    /** ----------------------------------------*/
	
	function update_subscriptions()
	{
		if ( ! class_exists('Member_subscriptions'))
    	{
    		require PATH_MOD.'member/mod.member_subscriptions.php';
    	}
    	
    	$MS = new Member_subscriptions();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->update_subscriptions();
	}
	/* END */
	
	
	/** -------------------------------------
	/**  Edit Ignore List Form
	/** -------------------------------------*/

	function edit_ignore_list()
	{
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->edit_ignore_list();
	}
	/* END */
	
	
	/** -------------------------------------
	/**  Update Ignore List
	/** -------------------------------------*/
	
	function update_ignore_list()
	{
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->update_ignore_list();
	}
	/* END */
	
	
	/** -------------------------------------
	/**  Member Mini Search
	/** -------------------------------------*/
	
	function member_mini_search()
	{
		global $LANG;
		
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
		$this->_set_page_title($LANG->line('member_search'));
    	return $MS->member_mini_search();
	}
	/* END */
	
	
	/** -------------------------------------
	/**  Do Member Mini Search
	/** -------------------------------------*/
	
	function do_member_mini_search()
	{
		global $LANG;
		
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
		$this->_set_page_title($LANG->line('member_search'));
    	return $MS->do_member_mini_search();
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Member Registration Form
    /** ----------------------------------------*/

    function registration_form()
    {
    	if ( ! class_exists('Member_register'))
    	{
    		require PATH_MOD.'member/mod.member_register.php';
    	}
    	
    	$MR = new Member_register();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MR->{$key} = $value;
		}
    	
    	return $MR->registration_form();
    }
    /* END */




    /** ----------------------------------------
    /**  Register Member
    /** ----------------------------------------*/

    function register_member()
    {
    	if ( ! class_exists('Member_register'))
    	{
    		require PATH_MOD.'member/mod.member_register.php';
    	}
    	
    	$MR = new Member_register();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MR->{$key} = $value;
		}
    	
    	$MR->register_member();
	}
	/* END */




    /** ----------------------------------------
    /**  Member Self-Activation
    /** ----------------------------------------*/

	function activate_member()
	{
        if ( ! class_exists('Member_register'))
    	{
    		require PATH_MOD.'member/mod.member_register.php';
    	}
    	
    	$MR = new Member_register();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MR->{$key} = $value;
		}
    	
    	$MR->activate_member();
	}
	/* END */

	
	/** -------------------------------------
	/**  Delete Page
	/** -------------------------------------*/

	function delete()
	{
		return $this->confirm_delete_form();
	}
	/* END */
	
	
	
	/** -------------------------------------
	/**  Self-delete confirmation form
	/** -------------------------------------*/
	
	function confirm_delete_form()
	{
		global $LANG, $FNS, $OUT, $PREFS, $SESS;
		
		if ($SESS->userdata['can_delete_self'] !== 'y')
		{
			return $OUT->show_user_error('general', $LANG->line('cannot_delete_self'));
		}
		else
		{
			$delete_form = $this->_load_element('delete_confirmation_form');

	        $data['hidden_fields']['ACT'] = $FNS->fetch_action_id('Member', 'member_delete');	
			$data['onsubmit'] = "if(!confirm('{lang:final_delete_confirm}')) return false;";
			$data['id']	  = 'member_delete_form';
			
			$this->_set_page_title($LANG->line('member_delete'));

			return $this->_var_swap($delete_form, array('form_declaration' => $FNS->form_declaration($data)));	
		}
	}
	/* END */
	
	
	
	/** -------------------------------------
	/**  Member self-delete
	/** -------------------------------------*/
	
	function member_delete()
	{
		global $DB, $FNS, $IN, $LANG, $OUT, $PREFS, $REGX, $SESS, $STAT;
		
		/** -------------------------------------
		/**  Make sure they got here via a form
		/** -------------------------------------*/
		
		if ( ! $IN->GBL('ACT', 'POST'))
		{
			// No output for you, Mr. URL Hax0r
			return FALSE;
		}
		
		$LANG->fetch_language_file('login');
			
		/* -------------------------------------
		/*  No sneakiness - we'll do this in case the site administrator
		/*  has foolishly turned off secure forms and some monkey is
		/*  trying to delete their account from an off-site form or
		/*  after logging out.
		/* -------------------------------------*/
		
		if ($SESS->userdata['member_id'] == 0 OR $SESS->userdata['can_delete_self'] !== 'y')
		{
			return $OUT->show_user_error('general', $LANG->line('not_authorized'));
		}
		
		/** -------------------------------------
		/**  If the user is a SuperAdmin, then no deletion
		/** -------------------------------------*/
		
		if ($SESS->userdata['group_id'] == 1)
		{
			return $OUT->show_user_error('general', $LANG->line('cannot_delete_super_admin'));
		}
		
		/** ----------------------------------------
        /**  Is IP and User Agent required for login?  Then, same here.
        /** ----------------------------------------*/
    
        if ($PREFS->ini('require_ip_for_login') == 'y')
        {
			if ($SESS->userdata['ip_address'] == '' || $SESS->userdata['user_agent'] == '')
			{
            	return $OUT->show_user_error('general', $LANG->line('unauthorized_request'));
           	}
        }
        
		/** ----------------------------------------
        /**  Check password lockout status
        /** ----------------------------------------*/
		
		if ($SESS->check_password_lockout() === TRUE)
		{
            return $OUT->show_user_error('general', str_replace("%x", $PREFS->ini('password_lockout_interval'), $LANG->line('password_lockout_in_effect')));
		}
		
		/* -------------------------------------
		/*  Are you who you say you are, or someone sitting at someone
		/*  else's computer being mean?!
		/* -------------------------------------*/

		$query = $DB->query("SELECT password FROM exp_members WHERE member_id = '".$SESS->userdata['member_id']."'");
		$password = $FNS->hash(stripslashes($IN->GBL('password', 'POST')));
		
		if ($query->row['password'] != $password)
		{
			$SESS->save_password_lockout();
			
			return $OUT->show_user_error('general', $LANG->line('invalid_pw'));
		}
		
		/** -------------------------------------
		/**  No turning back, get to deletin'!
		/** -------------------------------------*/
			
		$id = $SESS->userdata['member_id'];

		$DB->query("DELETE FROM exp_members WHERE member_id = '{$id}'");
		$DB->query("DELETE FROM exp_member_data WHERE member_id = '{$id}'");
		$DB->query("DELETE FROM exp_member_homepage WHERE member_id = '{$id}'");
		
		$message_query = $DB->query("SELECT DISTINCT recipient_id FROM exp_message_copies WHERE sender_id = '{$id}' AND message_read = 'n'");
		$DB->query("DELETE FROM exp_message_copies WHERE sender_id = '{$id}'");
		$DB->query("DELETE FROM exp_message_data WHERE sender_id = '{$id}'");
		$DB->query("DELETE FROM exp_message_folders WHERE member_id = '{$id}'");
		$DB->query("DELETE FROM exp_message_listed WHERE member_id = '{$id}'");
		
		if ($message_query->num_rows > 0)
		{
			foreach($message_query->result as $row)
			{
				$count_query = $DB->query("SELECT COUNT(*) AS count FROM exp_message_copies WHERE recipient_id = '".$row['recipient_id']."' AND message_read = 'n'");
				$DB->query($DB->update_string('exp_members', array('private_messages' => $count_query->row['count']), "member_id = '".$row['recipient_id']."'"));
			}
		}
				
		/** -------------------------------------
		/**  Delete Forum Posts
		/** -------------------------------------*/
		
		if ($PREFS->ini('forum_is_installed') == "y")
		{
			$DB->query("DELETE FROM exp_forum_subscriptions  WHERE member_id = '{$id}'"); 
			$DB->query("DELETE FROM exp_forum_pollvotes  WHERE member_id = '{$id}'"); 
			 
			$DB->query("DELETE FROM exp_forum_topics WHERE author_id = '{$id}'");
			$DB->query("DELETE FROM exp_forum_administrators WHERE admin_member_id = '{$id}'");
			$DB->query("DELETE FROM exp_forum_moderators WHERE mod_member_id = '{$id}'");
						
			// Snag the affected topic id's before deleting the member for the update afterwards
			$query = $DB->query("SELECT topic_id FROM exp_forum_posts WHERE author_id = '{$id}'");
			
			if ($query->num_rows > 0)
			{
				$topic_ids = array();
				
				foreach ($query->result as $row)
				{
					$topic_ids[] = $row['topic_id'];
				}
				
				$topic_ids = array_unique($topic_ids);
			}
			
			$DB->query("DELETE FROM exp_forum_posts  WHERE author_id = '{$id}'");
			$DB->query("DELETE FROM exp_forum_polls  WHERE author_id = '{$id}'");

			// Kill any attachments
			$query = $DB->query("SELECT attachment_id, filehash, extension, board_id FROM exp_forum_attachments WHERE member_id = '{$id}'");
			
			if ($query->num_rows > 0)
			{
				// Grab the upload path
				$res = $DB->query('SELECT board_id, board_upload_path FROM exp_forum_boards');
			
				$paths = array();
				foreach ($res->result as $row)
				{
					$paths[$row['board_id']] = $row['board_upload_path'];
				}
			
				foreach ($query->result as $row)
				{
					if ( ! isset($paths[$row['board_id']]))
					{
						continue;
					}
					
					$file  = $paths[$row['board_id']].$row['filehash'].$row['extension'];
					$thumb = $paths[$row['board_id']].$row['filehash'].'_t'.$row['extension'];
				
					@unlink($file);
					@unlink($thumb);					
			
					$DB->query("DELETE FROM exp_forum_attachments WHERE attachment_id = '{$row['attachment_id']}'");
				}				
			}
						
			// Update the forum stats			
			$query = $DB->query("SELECT forum_id FROM exp_forums WHERE forum_is_cat = 'n'");
			
			if ( ! class_exists('Forum'))
			{
				require PATH_MOD.'forum/mod.forum'.EXT;
				require PATH_MOD.'forum/mod.forum_core'.EXT;
			}
			
			$FRM = new Forum_Core;
			
			foreach ($query->result as $row)
			{
				$FRM->_update_post_stats($row['forum_id']);
			}
			
			if (isset($topic_ids))
			{
				foreach ($topic_ids as $topic_id)
				{
					$FRM->_update_topic_stats($topic_id);
				}
			}
		}
		
		/** -------------------------------------
		/**  Va-poo-rize Weblog Entries and Comments
		/** -------------------------------------*/
		
		$entry_ids			= array();
		$weblog_ids			= array();
		$recount_ids		= array();
		
		// Find Entry IDs and Weblog IDs, then delete
		$query = $DB->query("SELECT entry_id, weblog_id FROM exp_weblog_titles WHERE author_id = '{$id}'");
		
		if ($query->num_rows > 0)
		{
			foreach ($query->result as $row)
			{
				$entry_ids[]	= $row['entry_id'];
				$weblog_ids[]	= $row['weblog_id'];
			}
			
			$DB->query("DELETE FROM exp_weblog_titles WHERE author_id = '{$id}'");
			$DB->query("DELETE FROM exp_weblog_data WHERE entry_id IN ('".implode("','", $entry_ids)."')");
			$DB->query("DELETE FROM exp_comments WHERE entry_id IN ('".implode("','", $entry_ids)."')");
			$DB->query("DELETE FROM exp_trackbacks WHERE entry_id IN ('".implode("','", $entry_ids)."')");
		}
		
		// Find the affected entries AND weblog ids for author's comments
		$query = $DB->query("SELECT DISTINCT(entry_id), weblog_id FROM exp_comments WHERE author_id = '{$id}'");
		
		if ($query->num_rows > 0)
		{
			foreach ($query->result as $row)
			{
				$recount_ids[] = $row['entry_id'];
				$weblog_ids[]  = $row['weblog_id'];
			}
			
			$recount_ids = array_diff($recount_ids, $entry_ids);
		}
		
		// Delete comments by member
		$DB->query("DELETE FROM exp_comments WHERE author_id = '{$id}'");
		
		// Update stats on weblog entries that were NOT deleted AND had comments by author
		
		if (count($recount_ids) > 0)
		{
			foreach (array_unique($recount_ids) as $entry_id)
			{
				$query = $DB->query("SELECT MAX(comment_date) AS max_date FROM exp_comments WHERE status = 'o' AND entry_id = '".$DB->escape_str($entry_id)."'");
				
				$comment_date = ($query->num_rows == 0 OR !is_numeric($query->row['max_date'])) ? 0 : $query->row['max_date'];
				
				$query = $DB->query("SELECT COUNT(*) AS count FROM exp_comments WHERE entry_id = '{$entry_id}' AND status = 'o'");				
				
				$DB->query("UPDATE exp_weblog_titles SET comment_total = '".$DB->escape_str($query->row['count'])."', recent_comment_date = '$comment_date' WHERE entry_id = '{$entry_id}'");
			}
		}
		
		if (count($weblog_ids) > 0)
		{	
			foreach (array_unique($weblog_ids) as $weblog_id)
			{
				$STAT->update_weblog_stats($weblog_id);
				$STAT->update_comment_stats($weblog_id);
			}
		}
		
		/** -------------------------------------
		/**  Email notification recipients
		/** -------------------------------------*/

		if ($SESS->userdata['mbr_delete_notify_emails'] != '')
		{
			$notify_address = $SESS->userdata['mbr_delete_notify_emails'];
			
			$swap = array(
							'name'				=> $SESS->userdata['screen_name'],
							'email'				=> $SESS->userdata['email'],
							'site_name'			=> stripslashes($PREFS->ini('site_name'))
						 );
			
			$email_tit = $FNS->var_swap($LANG->line('mbr_delete_notify_title'), $swap);
			$email_msg = $FNS->var_swap($LANG->line('mbr_delete_notify_message'), $swap);
							   
			// No notification for the user themselves, if they're in the list
			$notify_address = str_replace($SESS->userdata['email'], '', $notify_address);
			
			$notify_address = $REGX->remove_extra_commas($notify_address);
			
			if ($notify_address != '')
			{				
				/** ----------------------------
				/**  Send email
				/** ----------------------------*/
				
				if ( ! class_exists('EEmail'))
				{
					require PATH_CORE.'core.email'.EXT;
				}
				
				$email = new EEmail;
				
				foreach (explode(',', $notify_address) as $addy)
				{
					$email->initialize();
					$email->wordwrap = false;
					$email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));	
					$email->to($addy); 
					$email->reply_to($PREFS->ini('webmaster_email'));
					$email->subject($email_tit);	
					$email->message($REGX->entities_to_ascii($email_msg));		
					$email->Send();
				}
			}			
		}
		
		/** -------------------------------------
		/**  Trash the Session and cookies
		/** -------------------------------------*/

        $DB->query("DELETE FROM exp_online_users WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' AND ip_address = '{$IN->IP}' AND member_id = '{$id}'");

        $DB->query("DELETE FROM exp_sessions WHERE session_id = '".$SESS->userdata['session_id']."'");
                
        $FNS->set_cookie($SESS->c_uniqueid);       
        $FNS->set_cookie($SESS->c_password);   
        $FNS->set_cookie($SESS->c_session);   
        $FNS->set_cookie($SESS->c_expire);   
        $FNS->set_cookie($SESS->c_anon);  
        $FNS->set_cookie('read_topics');  
        $FNS->set_cookie('tracker');

		/** -------------------------------------
		/**  Update global member stats
		/** -------------------------------------*/
		
		$STAT->update_member_stats();
		
		/** -------------------------------------
		/**  Build Success Message
		/** -------------------------------------*/
		
		$url	= $PREFS->ini('site_url');
		$name	= stripslashes($PREFS->ini('site_name'));
		
		$data = array(	'title' 	=> $LANG->line('mbr_delete'),
        				'heading'	=> $LANG->line('thank_you'),
        				'content'	=> $LANG->line('mbr_account_deleted'),
        				'redirect'	=> '',
        				'link'		=> array($url, $name)
        			 );
					
		$OUT->show_message($data);
	}
	/* END */
	
	
	
    /** -----------------------------------
    /**  Login Page
    /** -----------------------------------*/

	function login()
	{
		return $this->profile_login_form();
	}
	/* END */



    /** ----------------------------------------
    /**  Manual Login Form
    /** ----------------------------------------*/
    
    // This lets users create a stand-alone login form in any template

	function login_form()
	{
		global $TMPL, $IN, $FNS, $LANG, $PREFS;
						
		if ($PREFS->ini('user_session_type') != 'c')
		{
			$TMPL->tagdata = preg_replace("/{if\s+auto_login}.*?{".SLASH."if}/s", '', $TMPL->tagdata);
		}
		else
		{
			$TMPL->tagdata = preg_replace("/{if\s+auto_login}(.*?){".SLASH."if}/s", "\\1", $TMPL->tagdata);
		}
				
        /** ----------------------------------------
        /**  Create form
        /** ----------------------------------------*/
                                              
        $data['hidden_fields'] = array(
										'ACT' => $FNS->fetch_action_id('Member', 'member_login'),
										'RET' => ($TMPL->fetch_param('return') AND $TMPL->fetch_param('return') != "") ? str_replace(SLASH, '/', $TMPL->fetch_param('return')) : '-2'
									  );   
		
		if ($TMPL->fetch_param('name') !== FALSE && 
			preg_match("#^[a-zA-Z0-9_\-]+$#i", $TMPL->fetch_param('name'), $match))
		{
			$data['name'] = $TMPL->fetch_param('name');
		}
		
		if ($TMPL->fetch_param('id') !== FALSE && 
			preg_match("#^[a-zA-Z0-9_\-]+$#i", $TMPL->fetch_param('id'), $match))
		{
			$data['id'] = $TMPL->fetch_param('id');
		}
                              
        $res  = $FNS->form_declaration($data);
        
        $res .= stripslashes($TMPL->tagdata);
        
        $res .= "</form>"; 
        
		return $res;
	}
	/* END */


    /** ----------------------------------
    /**  Username/password update
    /** ----------------------------------*/

	function unpw_update()
	{
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	return $MS->unpw_update();
	}
	/* END */
	

    /** ----------------------------------
    /**  Update the username/password
    /** ----------------------------------*/
	
	function update_un_pw()
	{
		if ( ! class_exists('Member_settings'))
    	{
    		require PATH_MOD.'member/mod.member_settings.php';
    	}
    	
    	$MS = new Member_settings();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MS->{$key} = $value;
		}
    	
    	$MS->update_un_pw();
	}
	/* END */


    /** ----------------------------------
    /**  Member Email Form
    /** ----------------------------------*/

	function email_console()
	{
		if ( ! class_exists('Member_memberlist'))
    	{
    		require PATH_MOD.'member/mod.member_memberlist.php';
    	}
    	
    	$MM = new Member_memberlist();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MM->{$key} = $value;
		}
    	
    	return $MM->email_console();	
	}
	/* END */




    /** ----------------------------------
    /**  Send Member Email
    /** ----------------------------------*/

	function send_email()
	{
		if ( ! class_exists('Member_memberlist'))
    	{
    		require PATH_MOD.'member/mod.member_memberlist.php';
    	}
    	
    	$MM = new Member_memberlist();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MM->{$key} = $value;
		}
    	
    	return $MM->send_email();	
	}
	/* END */



	/** ----------------------------------
	/**  AIM Console
	/** ----------------------------------*/

	function aim_console()
	{
		if ( ! class_exists('Member_memberlist'))
    	{
    		require PATH_MOD.'member/mod.member_memberlist.php';
    	}
    	
    	$MM = new Member_memberlist();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MM->{$key} = $value;
		}
    	
    	return $MM->aim_console();	
	}
	/* END */
	
	
	

	/** ----------------------------------
	/**  ICQ Console
	/** ----------------------------------*/
	
	function icq_console()
	{
		if ( ! class_exists('Member_memberlist'))
    	{
    		require PATH_MOD.'member/mod.member_memberlist.php';
    	}
    	
    	$MM = new Member_memberlist();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MM->{$key} = $value;
		}
    	
    	return $MM->icq_console();		
	}
	/* END */
	


    /** ----------------------------------------
    /**  Member List
    /** ----------------------------------------*/

    function memberlist()
    {
		if ( ! class_exists('Member_memberlist'))
    	{
    		require PATH_MOD.'member/mod.member_memberlist.php';
    	}
    	
    	$MM = new Member_memberlist();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MM->{$key} = $value;
		}
    	
    	return $MM->memberlist();	
	}
	/* END */


    /** ----------------------------------------
    /**  Member Search Results
    /** ----------------------------------------*/

    function member_search()
    {
		if ( ! class_exists('Member_memberlist'))
    	{
    		require PATH_MOD.'member/mod.member_memberlist.php';
    	}
    	
    	$MM = new Member_memberlist();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MM->{$key} = $value;
		}
    	
    	return $MM->memberlist();	
	}
	/* END */
	
	/** ----------------------------------------
    /**  Do A Member Search
    /** ----------------------------------------*/

    function do_member_search()
    {
		if ( ! class_exists('Member_memberlist'))
    	{
    		require PATH_MOD.'member/mod.member_memberlist.php';
    	}
    	
    	$MM = new Member_memberlist();
    	
    	foreach(get_object_vars($this) as $key => $value)
		{
			$MM->{$key} = $value;
		}
    	
    	return $MM->do_member_search();	
	}
	/* END */


  
    /** -----------------------------------------------------------
    /**  Emoticons
    /** -----------------------------------------------------------*/

    function smileys()
    {
        global $IN, $LANG, $PREFS, $DB, $OUT, $SESS;
        
        if ($SESS->userdata('member_id') == 0)
        {
        	return $OUT->fatal_error($LANG->line('must_be_logged_in'));
        }
        
        $class_path = PATH_MOD.'emoticon/emoticons'.EXT;
        
        if ( ! is_file($class_path) OR ! @include_once($class_path))
        {
        	return $OUT->fatal_error('Unable to locate the smiley images');
        }
        
        if ( ! is_array($smileys))
        {
            return;
        }
        
        $path = $PREFS->ini('emoticon_path', 1);
                
        ob_start();
        ?>             
        <script type="text/javascript"> 
        <!--

        function add_smiley(smiley)
        {
            var  form = document.forms[0];  
			var el = opener.window.document.getElementById('submit_post').body;
			el.value += " " + smiley + " ";
			el.focus();

			// Move to end for non-IE
			if ('selectionStart' in el) 
			{
				el.setSelectionRange(el.value.length, el.value.length);
			}
			
			window.close();
        }
        
        //-->
        </script>
        
        <?php

        $javascript = ob_get_contents();
        ob_end_clean();        
        $r = $javascript;
                
        
        $i = 1;
        
        $dups = array();
        
        foreach ($smileys as $key => $val)
        {
            if ($i == 1)
            {
                $r .= "<tr>\n";                
            }
            
            if (in_array($smileys[$key]['0'], $dups))
            	continue;
            
            $r .= "<td class='tableCellOne' align='center'><a href=\"#\" onclick=\"return add_smiley('".$key."');\"><img src=\"".$path.$smileys[$key]['0']."\" width=\"".$smileys[$key]['1']."\" height=\"".$smileys[$key]['2']."\" alt=\"".$smileys[$key]['3']."\" border=\"0\" /></a></td>\n";

			$dups[] = $smileys[$key]['0'];

            if ($i == 10)
            {
                $r .= "</tr>\n";                
                
                $i = 1;
            }
            else
            {
                $i++;
            }      
        }
        
        $r = rtrim($r);
                
        if (substr($r, -5) != "</tr>")
        {
            $r .= "</tr>\n";
        }
        
		$this->_set_page_title($LANG->line('smileys'));
		return str_replace('{include:smileys}', $r, $this->_load_element('emoticon_page'));
    }
    /* END */


    /** ----------------------------------------
    /**  Convet special characters
    /** ----------------------------------------*/

	function _convert_special_chars($str)
	{
		return str_replace(array('<', '>', '{', '}', '\'', '"', '?'), array('&lt;', '&gt;', '&#123;', '&#125;', '&#39;', '&quot;', '&#63;'), $str);
	}
	/* END */


    /** ----------------------------------
    /**  Parse the index template
    /** ----------------------------------*/

	function _parse_index_template($str)
	{
		global $LANG, $TMPL, $FNS;
		
		$req = ($this->request == '') ? 'profile' : $this->request;
		
		// We have to call this before putting it into the array
		$breadcrumb = $this->breadcrumb();
		
		return $this->_var_swap($TMPL->tagdata,
								array(
										'stylesheet'	=>	"<style type='text/css'>\n\n".$this->_load_element('stylesheet')."\n\n</style>",
										'javascript'	=>	$this->javascript,
										'heading'		=>	$this->page_title,
										'breadcrumb'	=>	$breadcrumb,
										'content'		=>	$str,
										'copyright'		=>	$this->_load_element('copyright')
									 )
								 );
	
	}
	/* END */



    /** ----------------------------------
    /**  Member Home Page
    /** ----------------------------------*/

	function _member_page($str)
	{
		$template = $this->_load_element('member_page');
	
		if ($this->show_headings == TRUE)
		{
			$template = $this->_allow_if('show_headings', $template);
		}
		else
		{
			$template = $this->_deny_if('show_headings', $template);
		}
	
	
		// We have to call this before putting it into the array
		$breadcrumb = $this->breadcrumb();
				
		$header = $this->_load_element('html_header');
		$css 	= $this->_load_element('stylesheet');
		
		$header = str_replace('{include:stylesheet}', $css, $header);
		$header = str_replace('{include:head_extra}', $this->head_extra, $header);

		return $this->_var_swap($template,
								array(

										'include:html_header'		=> $header,
										'include:page_header'		=> $this->_load_element('page_header'),
										'include:page_subheader'	=> $this->_load_element('page_subheader'),
										'include:member_manager'	=> $str,
										'include:breadcrumb'		=> $breadcrumb,
										'include:html_footer'		=> $this->_load_element('html_footer')
									 )
								);
	
	
	}
	/* END */
	


    /** ----------------------------------
    /**  Load theme element
    /** ----------------------------------*/

	function _load_element($which)
	{ 
		global $PREFS;

		if ( ! class_exists($this->theme_class))
		{
			if ($this->theme_path == '')
			{
				$theme = ($PREFS->ini('member_theme') == '') ? 'default' : $PREFS->ini('member_theme');
				
				$this->theme_path = PATH_MBR_THEMES.$theme.'/profile_theme'.EXT;
			}
		
            include_once $this->theme_path;    
		}
		
		if ( ! isset($MS) OR ! is_object($MS))
		{
			$MS = new $this->theme_class();
		}

		if ( ! method_exists($MS, $which))
		{
			global $OUT, $LANG, $PREFS;
			
			$data = array(	'title' 	=> $LANG->line('error'),
							'heading'	=> $LANG->line('general_error'),
							'content'	=> $LANG->line('nonexistant_page'),
							'redirect'	=> '',
							'link'		=> array($PREFS->ini('site_url'), stripslashes($PREFS->ini('site_name')))
						 );
               
			return $OUT->show_message($data, 0);
		
		}

		return $this->_prep_element(trim($MS->$which()));
	}
	/* END */


    /** -------------------------------------
    /**  Trigger Error Template
    /** -------------------------------------*/

	function _trigger_error($heading, $message = '', $use_lang = TRUE)
	{
		global $LANG;
	
		return $this->_var_swap($this->_load_element('error'),
								array(
										'lang:heading'	=>	$LANG->line($heading),
										'lang:message'	=>	($use_lang == TRUE) ? $LANG->line($message) : $message
									 )
								);		
	}
	/* END */
	

    /** -------------------------------------
    /**  Sets the title of the page
    /** -------------------------------------*/

	function _set_page_title($title)
	{
		if ($this->page_title == '')
		{
			$this->page_title = $title;
		}
	}
	/* END */
	

		
    /** ----------------------------------------
    /**  Member Breadcrumb
    /** ----------------------------------------*/

	function breadcrumb()
	{
		global $IN, $SESS, $PREFS, $LANG, $DB;
		
		if ($this->breadcrumb == FALSE)
		{
			return '';
		}
		
		$crumbs = $this->_crumb_trail(
										array(
												'link'	=> $PREFS->ini('site_url'), 
												'title'	=> stripslashes($PREFS->ini('site_name'))
											 )
									);

			if ($IN->fetch_uri_segment(2) == '')
			{
				return $this->_build_crumbs($LANG->line('member_profile'), $crumbs, $LANG->line('member_profile'));
			}
			
			if ($IN->fetch_uri_segment(2) == 'messages')
			{				
				$crumbs .= $this->_crumb_trail(array(	
													'link' => $this->_member_path('/profile'), 
													'title' => $LANG->line('control_panel_home')
													)
												);
												
				$pm_page =  (FALSE !== ($mbr_crumb = $this->_fetch_member_crumb($IN->fetch_uri_segment(3)))) ? $LANG->line($mbr_crumb) : $LANG->line('view_folder');

				return $this->_build_crumbs($pm_page, $crumbs, $pm_page);
			}
			
			
			if (is_numeric($IN->fetch_uri_segment(2)))
			{				
				$query = $DB->query("SELECT screen_name FROM exp_members WHERE member_id = '".$IN->fetch_uri_segment(2)."'");
				
				$crumbs .= $this->_crumb_trail(array(	
													'link' => $this->_member_path('/memberlist'), 
													'title' => $LANG->line('mbr_memberlist')
													)
												);
				
				return $this->_build_crumbs($query->row['screen_name'], $crumbs, $query->row['screen_name']);
			}
			else
			{
				if ($IN->fetch_uri_segment(2) == 'memberlist')
				{
					return $this->_build_crumbs($LANG->line('mbr_memberlist'), $crumbs, $LANG->line('mbr_memberlist'));
				}
				elseif ($IN->fetch_uri_segment(2) == 'member_search' OR $IN->fetch_uri_segment(2) == 'do_member_search')
				{
					return $this->_build_crumbs($LANG->line('member_search'), $crumbs, $LANG->line('member_search'));
				}
				elseif ($IN->fetch_uri_segment(2) != 'profile' AND ! in_array($IN->fetch_uri_segment(2), $this->no_menu))
				{
					$crumbs .= $this->_crumb_trail(array(	
														'link' => $this->_member_path('/profile'), 
														'title' => $LANG->line('control_panel_home')
														)
													);
				}
				
			}
			
			if (FALSE !== ($mbr_crumb = $this->_fetch_member_crumb($IN->fetch_uri_segment(2))))
			{
				return $this->_build_crumbs($LANG->line($mbr_crumb), $crumbs, $LANG->line($mbr_crumb));
			}
	}
	/* END */


    /** -------------------------------------
    /**  Breadcrumb trail links
    /** -------------------------------------*/

	function _crumb_trail($data)
	{
		$trail	= $this->_load_element('breadcrumb_trail');

		$crumbs = '';

		$crumbs .= $this->_var_swap($trail,
									array(
											'crumb_link'	=> $data['link'], 
											'crumb_title'	=> $data['title']
											)
									);		
		return $crumbs;
	}
	/* END */

	
    /** -------------------------------------
    /**  Finalize the Crumbs
    /** -------------------------------------*/

	function _build_crumbs($title, $crumbs, $str)
	{	
		global $FNS, $SESS;
		
		$this->_set_page_title(($title == '') ? 'Powered By ExpressionEngine' : $title);
	
		$crumbs .= str_replace('{crumb_title}', $str, $this->_load_element('breadcrumb_current_page'));		
	
		$breadcrumb = $this->_load_element('breadcrumb');
						
		$breadcrumb = str_replace('{name}', $SESS->userdata('screen_name'), $breadcrumb);
			
		return str_replace('{breadcrumb_links}', $crumbs, $breadcrumb);			
	}
	/* END */
	
	
    /** -------------------------------------
    /**  Fetch member profile crumb item
    /** -------------------------------------*/

	function _fetch_member_crumb($item = '')
	{
		if ($item == '')
			return FALSE;
	
		return ( ! isset($this->crumb_map[$item])) ? FALSE : $this->crumb_map[$item];
	}
	/* END */
	

	/** ----------------------------------------
	/**  Create the "year" pull-down menu
	/** ----------------------------------------*/

	function _birthday_year($year = '')
	{
		global $LANG, $LOC;
			
		$r = "<select name='bday_y' class='select'>\n";
		
		$selected = ($year == '') ? " selected='selected'" : '';
		
		$r .= "<option value=''{$selected}>".$LANG->line('year')."</option>\n";
		
		for ($i = date('Y', $LOC->now); $i > 1904; $i--)
		{                                      
			$selected = ($year == $i) ? " selected='selected'" : '';
			
			$r .= "<option value='{$i}'{$selected}>".$i."</option>\n";                            
		}
		
		$r .= "</select>\n";
	
		return $r;
	}
	/* END */

	/** ----------------------------------------
	/**  Create the "month" pull-down menu
	/** ----------------------------------------*/

	function _birthday_month($month = '')
	{
		global $LANG;
			
		$months = array('01' => 'January','02' => 'February','03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
		
		$r = "<select name='bday_m' class='select'>\n";
		
		$selected = ($month == '') ? " selected='selected'" : '';
		
		$r .= "<option value=''{$selected}>".$LANG->line('month')."</option>\n";
		
		for ($i = 1; $i < 13; $i++)
		{
			if (strlen($i) == 1)
				$i = '0'.$i;
			
			$selected = ($month == $i) ? " selected='selected'" : '';
			
			$r .= "<option value='{$i}'{$selected}>".$LANG->line($months[$i])."</option>\n";                            
		}
				
		$r .= "</select>\n";
	
		return $r;
	}
	/* END */


	/** ----------------------------------------
	/**  Create the "day" pull-down menu
	/** ----------------------------------------*/

	function _birthday_day($day = '')
	{
		global $LANG;
			
		$r = "<select name='bday_d' class='select'>\n";
		
		$selected = ($day == '') ? " selected='selected'" : '';
		
		$r .= "<option value=''{$selected}>".$LANG->line('day')."</option>\n";
		
		for ($i = 31; $i >= 1; $i--)
		{                                      
			$selected = ($day == $i) ? " selected='selected'" : '';
			
			$r .= "<option value='{$i}'{$selected}>".$i."</option>\n";                            
		}
		
		$r .= "</select>\n";
	
		return $r;
	}
	/* END */



    /** -------------------------------------
    /**  Prep Element Data
    /** -------------------------------------*/
	
	// Right now we only use this to parse the logged-in/logged-out vars

	function _prep_element($str)
	{
		global $SESS, $PREFS;
		
		if ($str == '')
			return '';
		
		if ($SESS->userdata('member_id') == 0)
		{
			$str = $this->_deny_if('logged_in', $str);
			$str = $this->_allow_if('logged_out', $str);
		}
		else
		{
			$str = $this->_allow_if('logged_in', $str);
			$str = $this->_deny_if('logged_out', $str);
		}
    	
		/** ----------------------------------------
		/**  Parse the forum conditional
		/** ----------------------------------------*/
		
		if ($PREFS->ini('forum_is_installed') == "y")
		{	
			$str = $this->_allow_if('forum_installed', $str);
		}
		else
		{
			$str = $this->_deny_if('forum_installed', $str);
		}    
		
		/** -------------------------------------
		/**  Parse the self deletion conditional
		/** -------------------------------------*/
		
		if ($SESS->userdata['can_delete_self'] == 'y' && $SESS->userdata['group_id'] != 1)
		{
			$str = $this->_allow_if('can_delete', $str);
		}
		else
		{
			$str = $this->_deny_if('can_delete', $str);
		}
    	
		return $str;		
	}
	/* END */


    /** ----------------------------------
    /**  Finalize a few things
    /** ----------------------------------*/

	function _final_prep($str)
	{
		global $FNS, $LANG, $PREFS, $SESS;
		
		/** ------------------------------
		/**  Which mode are we in?
		/** ------------------------------*/
		
		// This class can either be run in "stand-alone" mode or through the template engine. 

		$template_parser = FALSE;
		
		if (class_exists('Template'))
		{
			global $TMPL;
			
			if ($TMPL->tagdata != '')
			{
				$str = $this->_parse_index_template($str);
				$template_parser = TRUE;
				$TMPL->disable_caching = TRUE;
			}
		}
		
		if ($template_parser == FALSE AND $this->in_forum == FALSE)
		{
			$str = $this->_member_page($str);
		}
				
		
		/** ----------------------------------------
		/**  Parse the language text
		/** ----------------------------------------*/

   		if (preg_match_all("/{lang:(.+?)\}/i", $str, $matches))
   		{	
			for ($j = 0; $j < count($matches['0']); $j++)
			{ 			
				$line = ($LANG->line($matches['1'][$j]) == '') ? $LANG->line('mbr_'.$matches['1'][$j]) : $LANG->line($matches['1'][$j]);
			
				$str = str_replace($matches['0'][$j], $line, $str);
			}
		}
		
		/** ----------------------------------------
		/**  Parse old style path variables
		/** ----------------------------------------*/
		
		// This is here for backward compatibility for people with older templates
		$str = preg_replace_callback("/".LD."\s*path=(.*?)".RD."/", array(&$FNS, 'create_url'), $str);
		
		if (preg_match_all("#".LD."\s*(profile_path\s*=.*?)".RD."#", $str, $matches))
		{
			$i = 0;
			foreach ($matches['1'] as $val)
			{
				$path = $FNS->create_url($FNS->extract_path($val).'/'.$SESS->userdata['member_id']);			
				$str = preg_replace("#".$matches['0'][$i++]."#", $path, $str, 1); 
			}
		}
		// -------
		
		/** ----------------------------------------
		/**  Set some paths
		/** ----------------------------------------*/
		
		$theme_images = $PREFS->ini('theme_folder_url', 1).'profile_themes/'.$PREFS->ini('member_theme', 1).'images/';
	
		if ($SESS->userdata('profile_theme') != '')
		{
			$img_path = $PREFS->ini('theme_folder_url', 1).'profile_themes/'.$SESS->userdata('profile_theme').'/images/';
		}
		else
		{
			$img_path = $PREFS->ini('theme_folder_url', 1).'profile_themes/'.$PREFS->ini('member_theme', 1).'images/';
		}
		
		$simple = ($this->show_headings == FALSE) ? '/simple' : '';
		
		if ($this->css_file_path == '')
		{
			$this->css_file_path = $PREFS->ini('theme_folder_url', 1).'profile_themes/'.$PREFS->ini('member_theme', 1).'profile.css';
		}

		/** ----------------------------------------
		/**  Finalize the output
		/** ----------------------------------------*/
		
		$str = $this->_var_swap($str,
								array(
										'lang'						=> $PREFS->ini('xml_lang'),
										'charset'					=> $PREFS->ini('charset'),
										'path:image_url'			=> ($this->image_url == '') ? $theme_images : $this->image_url,
										'path:your_control_panel'	=> $this->_member_path('profile'),
										'path:your_profile'			=> $this->_member_path($SESS->userdata('member_id')),
										'path:edit_preferences'		=> $this->_member_path('edit_preferences'),
										'path:register'				=> $this->_member_path('register'.$simple),
										'path:private_messages'		=> $this->_member_path('messages'),
										'path:memberlist'			=> $this->_member_path('memberlist'),
										'path:signature'			=> $this->_member_path('edit_signature'),
										'path:avatar'				=> $this->_member_path('edit_avatar'),
										'path:photo'				=> $this->_member_path('edit_photo'),
										'path:smileys'				=> $this->_member_path('smileys'),
										'path:forgot'				=> $this->_member_path('forgot_password'.$simple),
										'path:login'				=> $this->_member_path('login'.$simple),
										'path:delete'				=> $this->_member_path('delete'),
										'page_title'				=> $this->page_title,
										'site_name'					=> stripslashes($PREFS->ini('site_name')),
										'path:theme_css'			=> $this->css_file_path
									)
								 );	
								 
		
        
		//  Add security hashes to forms
		if ( ! class_exists('Template'))
		{
			$str = $FNS->insert_action_ids($FNS->add_form_security_hash($str));
		}
			
		return $str;
	}
	/* END */
	

    /** ----------------------------------
    /**  Set base values of class vars
    /** ----------------------------------*/

	function _set_properties($props = array())
	{    
		if (count($props) > 0)
		{
			foreach ($props as $key => $val)
			{
				$this->$key = $val;
			}
		}
	}
	/* END */


    /** ----------------------------------------
    /**  Sets the member basepath
    /** ----------------------------------------*/

	function _member_set_basepath()
	{
		global $FNS;

		$this->basepath = $FNS->create_url($this->trigger, 1);
	}
	/* END */


    /** ----------------------------------------
    /**  Compiles a path string
    /** ----------------------------------------*/

	function _member_path($uri = '', $end_slash = '/')
	{
		global $FNS;
		
		if ($this->basepath == '')
		{
			$this->_member_set_basepath();
		}

		return $FNS->remove_double_slashes($this->basepath.$uri.$end_slash);
	}
	/* END */
		

    /** -------------------------------------
    /**  Helpers for "if" conditions
    /** -------------------------------------*/

	function _deny_if($cond, $str, $replace = '')
	{
		return preg_replace("/\{if\s+".$cond."\}.+?\{\/if\}/si", $replace, $str);
	}
	
	function _allow_if($cond, $str)
	{
		return preg_replace("/\{if\s+".$cond."\}(.+?)\{\/if\}/si", "\\1", $str);
	}
	
	
    /** ----------------------------------------
    /**  Replace variables
    /** ----------------------------------------*/
		
	function _var_swap($str, $data)
	{
		if ( ! is_array($data))
		{
			return false;
		}
	
		foreach ($data as $key => $val)
		{
			$str = str_replace('{'.$key.'}', $val, $str);
		}
	
		return $str;
	}
	/* END */




    /** ---------------------------------------
    /**  Swap single variables with final value
    /** ---------------------------------------*/

    function _var_swap_single($search, $replace, $source)
    {
        return str_replace(LD.$search.RD, $replace, $source);  
    }
    /* END */



	/** ----------------------------------------
	/**  Custom Member Profile Data
	/** ----------------------------------------*/

	function custom_profile_data()
	{
		global $DB, $SESS, $TMPL, $FNS, $PREFS, $LOC, $LANG;
		
		$member_id = ( ! $TMPL->fetch_param('member_id')) ? $SESS->userdata['member_id'] : $TMPL->fetch_param('member_id');
		        
        /** ----------------------------------------
        /**  Default Member Data
        /** ----------------------------------------*/
        
		$query = $DB->query("SELECT m.member_id, m.group_id, m.username, m.screen_name, m.email, m.signature,
									m.sig_img_filename, m.sig_img_width, m.sig_img_height,
									m.avatar_filename, m.avatar_width, m.avatar_height, 
									m.photo_filename, m.photo_width, m.photo_height, 
									m.url, m.location, m.occupation, m.interests, 
									m.bio, 
									m.join_date, m.last_visit, m.last_activity, m.last_entry_date, m.last_comment_date, 
									m.last_forum_post_date, m.total_entries, m.total_comments, m.total_forum_topics, m.total_forum_posts, 
									m.language, m.timezone, m.daylight_savings, m.bday_d, m.bday_m, m.bday_y,
									g.group_title
							 FROM exp_members m, exp_member_groups g 
							 WHERE m.member_id = '".$DB->escape_str($member_id)."'
							 AND g.site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'
							 AND m.group_id = g.group_id");
		
		if ($query->num_rows == 0)
		{
			return $TMPL->tagdata = '';
		}
		
		$default_fields = $query->row;
		
		/** ----------------------------------------
		/**  Is there an avatar?
		/** ----------------------------------------*/
						
		if ($PREFS->ini('enable_avatars') == 'y' AND $query->row['avatar_filename'] != '')
		{
			$avatar_path	= $PREFS->ini('avatar_url', 1).$query->row['avatar_filename'];
			$avatar_width	= $query->row['avatar_width'];
			$avatar_height	= $query->row['avatar_height'];
			$avatar			= 'TRUE';
		}
		else
		{
			$avatar_path	= '';
			$avatar_width	= '';
			$avatar_height	= '';
			$avatar			= 'FALSE';
		}	
		
		/** ----------------------------------------
		/**  Is there a member photo?
		/** ----------------------------------------*/
						
		if ($PREFS->ini('enable_photos') == 'y' AND $query->row['photo_filename'] != '')
		{
			$photo_path		= $PREFS->ini('photo_url', 1).$query->row['photo_filename'];
			$photo_width	= $query->row['photo_width'];
			$photo_height	= $query->row['photo_height'];
			$photo			= 'TRUE';
		}
		else
		{
			$photo_path		= '';
			$photo_width	= '';
			$photo_height	= '';
			$photo			= 'FALSE';
		}		

		/** ----------------------------------------
		/**  Is there a signature image?
		/** ----------------------------------------*/
						
		if ($PREFS->ini('enable_signatures') == 'y' AND $query->row['sig_img_filename'] != '')
		{
			$sig_img_path	= $PREFS->ini('sig_img_url', 1).$query->row['sig_img_filename'];
			$sig_img_width	= $query->row['sig_img_width'];
			$sig_img_height	= $query->row['sig_img_height'];
			$sig_img_image	= 'TRUE';
		}
		else
		{
			$sig_img_path	= '';
			$sig_img_width	= '';
			$sig_img_height	= '';
			$sig_img		= 'FALSE';
		}
		
		/** ----------------------------------------
		/**  Parse variables
		/** ----------------------------------------*/
		
		$qs = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';        
				
		if ($this->in_forum == TRUE)
		{
			$search_path = $this->forum_path.'member_search/'.$this->cur_id.'/';
		}
		else
		{
			$search_path = $FNS->fetch_site_index(0, 0).$qs.'ACT='.$FNS->fetch_action_id('Search', 'do_search').'&amp;mbr='.urlencode($query->row['member_id']);
		}
		
		$more_fields = array(
							'send_private_message'		=> $this->_member_path('messages/pm/'.$member_id),
							'search_path'				=> $search_path,
							'avatar_url'				=> $avatar_path,
							'avatar_filename'			=> $query->row['avatar_filename'],
							'avatar_width'				=> $avatar_width,
							'avatar_height'				=> $avatar_height,
							'photo_url'					=> $photo_path,
							'photo_filename'			=> $query->row['photo_filename'],
							'photo_width'				=> $photo_width,
							'photo_height'				=> $photo_height,
							'signature_image_url'		=> $sig_img_path,
							'signature_image_filename'	=> $query->row['sig_img_filename'],
							'signature_image_width'		=> $sig_img_width,
							'signature_image_height'	=> $sig_img_height					
							);
		
		$default_fields = array_merge($default_fields, $more_fields);
					
        /** ----------------------------------------
        /**  Fetch the custom member field definitions
        /** ----------------------------------------*/
        
        $fields = array();
        
        $query = $DB->query("SELECT m_field_id, m_field_name, m_field_fmt FROM exp_member_fields");
        
        if ($query->num_rows > 0)
        {
        	foreach ($query->result as $row)
        	{
            	$fields[$row['m_field_name']] = array($row['m_field_id'], $row['m_field_fmt']);
        	}
        }

        $query = $DB->query("SELECT * FROM exp_member_data WHERE member_id = '".$member_id."'");      
        
        if ($query->num_rows == 0)
        {
            foreach ($fields as $key => $val)
            {
                $TMPL->tagdata = $TMPL->swap_var_single($key, '', $TMPL->tagdata);
            }        
        
        	return $TMPL->tagdata;
        }

        /** ----------------------------------------
        /**  Do some prep for conditionals
        /** ----------------------------------------*/	
		
		$cond = $default_fields;
		
        $date_fields = array('join_date', 'last_visit', 'last_activity', 'last_entry_date', 'last_comment_date', 'last_forum_post_date');
        
        foreach ($date_fields as $v)
        {
        	if (isset($default_fields[$v]) && $default_fields[$v] == 0)
        	{
        		$cond[$v] = FALSE;
        	}
        }

		if ( ! class_exists('Typography'))
		{
			require PATH_CORE.'core.typography'.EXT;
		}
		
		$TYPE = new Typography;
		    
        foreach ($query->result as $row)
        {
			$cond['avatar']					= $avatar;
			$cond['photo']					= $photo;
			$cond['signature_image']		= $sig_img;	
			$cond['birthday']       		= (($default_fields['bday_m'] != '' && $default_fields['bday_m'] != 0)
												OR ($default_fields['bday_y'] != '' && $default_fields['bday_y'] != 0) 
												OR ($default_fields['bday_d'] != '' && $default_fields['bday_d'] != 0)) ? TRUE : FALSE;
			$cond['total_forum_replies']	= $default_fields['total_forum_posts'];
			$cond['total_forum_posts']		= $default_fields['total_forum_topics'] + $default_fields['total_forum_posts'];
					  
	       	foreach($fields as $key =>  $value)
        	{
        		if (substr($key, 0, 7) == 'mfield_');
        	
        		$cond[$key] = $TYPE->parse_type($row['m_field_id_'.$value['0']], 
												array(
													  'text_format'   => $value['1'],
													  'html_format'   => 'safe',
													  'auto_links'    => 'y',
													  'allow_img_url' => 'n'
												     )
										  	  );	
        	}
        	
        	$TMPL->tagdata = $FNS->prep_conditionals($TMPL->tagdata, $cond);
            
            /** ----------------------------------------
            /**  Swap Variables
            /** ----------------------------------------*/
    
            foreach ($TMPL->var_single as $key => $val)
            {
            	/** ----------------------------------------
                /**  parse default member data
                /** ----------------------------------------*/
	
				/** ----------------------------------------
				/**  Format URLs
				/** ----------------------------------------*/
	
				if ($key == 'url')
				{
					if (substr($default_fields['url'], 0, 4) != "http" AND strpos($default_fields['url'], '://') === FALSE)
					{
						$default_fields['url'] = "http://".$default_fields['url']; 	
					}
				}
			
				/** ----------------------------------------
				/**  "last_visit" 
				/** ----------------------------------------*/
				
				if (strncmp('last_visit', $key, 10) == 0)
				{			
					$TMPL->tagdata = $this->_var_swap_single($key, ($default_fields['last_activity'] > 0) ? $LOC->decode_date($val, $default_fields['last_activity']) : '', $TMPL->tagdata);
				}
			  
				/** ----------------------------------------
				/**  "join_date" 
				/** ----------------------------------------*/
				
				if (strncmp('join_date', $key, 9) == 0)
				{                     
					$TMPL->tagdata = $this->_var_swap_single($key, ($default_fields['join_date'] > 0) ? $LOC->decode_date($val, $default_fields['join_date']) : '', $TMPL->tagdata);
				}
				
				/** ----------------------------------------
				/**  "last_entry_date" 
				/** ----------------------------------------*/
				
				if (strncmp('last_entry_date', $key, 15) == 0)
				{                     
					$TMPL->tagdata = $this->_var_swap_single($key, ($default_fields['last_entry_date'] > 0) ? $LOC->decode_date($val, $default_fields['last_entry_date']) : '', $TMPL->tagdata);
				}
				
				/** ----------------------------------------
				/**  "last_forum_post_date" 
				/** ----------------------------------------*/
				
				if (strncmp('last_forum_post_date', $key, 20) == 0)
				{                     
					$TMPL->tagdata = $this->_var_swap_single($key, ($default_fields['last_forum_post_date'] > 0) ? $LOC->decode_date($val, $default_fields['last_forum_post_date']) : '', $TMPL->tagdata);
				}
				
				/** ----------------------------------------
				/**  parse "recent_comment" 
				/** ----------------------------------------*/
				
				if (strncmp('last_comment_date', $key, 17) == 0)
				{                     
					$TMPL->tagdata = $this->_var_swap_single($key, ($default_fields['last_comment_date'] > 0) ? $LOC->decode_date($val, $default_fields['last_comment_date']) : '', $TMPL->tagdata);
				}
				
				/** ----------------------
				/**  {name}
				/** ----------------------*/
				
				$name = ( ! $default_fields['screen_name']) ? $default_fields['username'] : $default_fields['screen_name'];
				
				$name = $this->_convert_special_chars($name);
				
				if ($key == "name")
				{
					$TMPL->tagdata = $this->_var_swap_single($val, $name, $TMPL->tagdata);
				}
							
				/** ----------------------
				/**  {member_group}
				/** ----------------------*/
				
				if ($key == "member_group")
				{
					$TMPL->tagdata = $this->_var_swap_single($val, $default_fields['group_title'], $TMPL->tagdata);
				}
				
				/** ----------------------
				/**  {email}
				/** ----------------------*/
				
				if ($key == "email")
				{				
					$TMPL->tagdata = $this->_var_swap_single($val, $TYPE->encode_email($default_fields['email']), $TMPL->tagdata);
				}
				
				/** ----------------------
				/**  {birthday}
				/** ----------------------*/
				
				if ($key == "birthday")
				{
					$birthday = '';
					
					if ($default_fields['bday_m'] != '' && $default_fields['bday_m'] != 0)
					{
						$month = (strlen($default_fields['bday_m']) == 1) ? '0'.$default_fields['bday_m'] : $default_fields['bday_m'];
								
						$m = $LOC->localize_month($month);
					
						$birthday .= $LANG->line($m['1']);
						
						if ($default_fields['bday_d'] != '' && $default_fields['bday_d'] != 0)
						{
							$birthday .= ' '.$default_fields['bday_d'];
						}
					}
			
					if ($default_fields['bday_y'] != '' && $default_fields['bday_y'] != 0)
					{
						if ($birthday != '')
						{
							$birthday .= ', ';
						}
					
						$birthday .= $default_fields['bday_y'];
					}
					
					if ($birthday == '')
					{
						$birthday = '';
					}
				
					$TMPL->tagdata = $this->_var_swap_single($val, $birthday, $TMPL->tagdata);
				}
				
				/** ----------------------
				/**  {timezone}
				/** ----------------------*/
				
				if ($key == "timezone")
				{				
					$timezone = ($default_fields['timezone'] != '') ? $LANG->line($default_fields['timezone']) : ''; 
					
					$TMPL->tagdata = $this->_var_swap_single($val, $timezone, $TMPL->tagdata);
				}
		
				/** ----------------------
				/**  {local_time}
				/** ----------------------*/
				
				if (strncmp('local_time', $key, 10) == 0)
				{           
					$time = $LOC->now;
	
					if ($SESS->userdata('member_id') != $this->cur_id)
					{  			    
						// Default is UTC?
						$zone = ($default_fields['timezone'] == '') ? 'UTC' : $default_fields['timezone'];
						$time = $LOC->set_localized_time($time, $zone, $default_fields['daylight_savings']);					
					}
					  
					$TMPL->tagdata = $this->_var_swap_single($key, $LOC->decode_date($val, $time), $TMPL->tagdata);
				}
				
				/** ----------------------
				/**  {bio}
				/** ----------------------*/
				
				if ($key == 'bio')
				{
					$bio = $TYPE->parse_type($default_fields[$val], 
																 array(
																			'text_format'   => 'xhtml',
																			'html_format'   => 'safe',
																			'auto_links'    => 'y',
																			'allow_img_url' => 'n'
																	   )
																);
						  
					$TMPL->tagdata = $this->_var_swap_single($key, $bio, $TMPL->tagdata);
				}

				/** ----------------------
				/**  {signature}
				/** ----------------------*/
				
				if ($key == 'signature')
				{
					$signature = $TYPE->parse_type($default_fields[$val], 
																	array(
																			'text_format'   => 'xhtml',
																			'html_format'   => 'safe',
																			'auto_links'    => 'y',
																			'allow_img_url' => $PREFS->ini('sig_allow_img_hotlink')
																	   )
																);
						  
					$TMPL->tagdata = $this->_var_swap_single($key, $signature, $TMPL->tagdata);
				}
								
				// Special condideration for {total_forum_replies}, and
				// {total_forum_posts} whose meanings do not match the
				// database field names
				
				if ($key == 'total_forum_replies')
				{
					$TMPL->tagdata = $this->_var_swap_single($key, $default_fields['total_forum_posts'], $TMPL->tagdata);
				}
				
				if ($key == 'total_forum_posts')
				{
					$total_posts = $default_fields['total_forum_topics'] + $default_fields['total_forum_posts'];
					$TMPL->tagdata = $this->_var_swap_single($key, $total_posts, $TMPL->tagdata);
				}
				
				/** ----------------------------------------
				/**  parse basic fields (username, screen_name, etc.)
				/** ----------------------------------------*/
	
				if (isset($default_fields[$val]))
				{           
					$TMPL->tagdata = $this->_var_swap_single($val, $default_fields[$val], $TMPL->tagdata);
				}
            
                /** ----------------------------------------
                /**  parse custom member fields
                /** ----------------------------------------*/

                if ( isset($fields[$val]) AND isset($row['m_field_id_'.$fields[$val]['0']]))
                {
                    $TMPL->tagdata = $TMPL->swap_var_single(
                                                        $val, 
                                                        $TYPE->parse_type( 
																				$row['m_field_id_'.$fields[$val]['0']], 
																				array(
																						'text_format'   => $fields[$val]['1'],
																						'html_format'   => 'safe',
																						'auto_links'    => 'y',
																						'allow_img_url' => 'n'
																					  )
																			  ), 
                                                        $TMPL->tagdata
                                                      );
                }
			}
        }
		
		return $TMPL->tagdata;	
	}
	/* END */

	
	/** -------------------------------------
	/**  Ignore List
	/** -------------------------------------*/
	
	function ignore_list()
	{
		global $DB, $SESS, $TMPL, $PREFS;
		
		$pre = 'ignore_';
		$prelen = strlen($pre);
		
		if ($member_id = $TMPL->fetch_param('member_id'))
		{
			$query = $DB->query("SELECT ignore_list FROM exp_members WHERE member_id = '{$member_id}'");

			if ($query->num_rows == 0)
			{
				return $TMPL->no_results();
			}

			$ignored = ($query->row['ignore_list'] == '') ? array() : explode('|', $query->row['ignore_list']);
		}
		else
		{
			$ignored = $SESS->userdata['ignore_list'];
		}
		
		$query = $DB->query("SELECT m.member_id, m.group_id, m.username, m.screen_name, m.email, m.ip_address, m.location, m.total_entries, m.total_comments, m.private_messages, m.total_forum_topics, m.total_forum_posts AS total_forum_replies, m.total_forum_topics + m.total_forum_posts AS total_forum_posts, 
							g.group_title AS group_description FROM exp_members AS m, exp_member_groups AS g
							WHERE g.group_id = m.group_id 
							g.site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'
							AND m.member_id IN ('".implode("', '", $ignored)."')");
		
		if ($query->num_rows == 0)
		{
			return $TMPL->no_results();
		}
		
		$tagdata = $TMPL->tagdata;
		$out = '';
		
		foreach($query->result as $row)
		{
			$temp = $tagdata;
			
			foreach ($TMPL->var_single as $key => $val)
			{
				$val = substr($val, $prelen);
				
				if (isset($row[$val]))
				{
					$temp = $TMPL->swap_var_single($pre.$val, $row[$val], $temp);
				}
			}
			
			$out .= $temp;
		}
		
		return $TMPL->tagdata = $out;
	}
	/* END */
	
}
// END CLASS
?>