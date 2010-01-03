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
 File: mod.member_auth.php
=====================================================
*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Member_auth extends Member {

	/** ----------------------------------
    /**  Member_auth Profile Constructor
    /** ----------------------------------*/

	function Member_auth()
	{
	}
	/* END */

	/** ----------------------------------------
    /**  Login Page
    /** ----------------------------------------*/
    
	function profile_login_form($return = '-2')
	{
		global $LANG, $IN, $FNS, $PREFS;
		
		$login_form = $this->_load_element('login_form');
		
		if ($PREFS->ini('user_session_type') != 'c')
		{
			$login_form = $this->_deny_if('auto_login', $login_form); 
		}
		else
		{
			$login_form = $this->_allow_if('auto_login', $login_form);  
		}

		// match {form_declaration} or {form_declaration return="foo"}
		// [0] => {form_declaration return="foo"}
	    // [1] => form_declaration return="foo"
	    // [2] =>  return="foo"
	    // [3] => "
	    // [4] => foo
		preg_match("/".LD."(form_declaration"."(\s+return\s*=\s*(\042|\047)([^\\3]*?)\\3)?)".RD."/s", $login_form, $match);

		if (empty($match))
		{	
			// don't even return the login template because the form will not work since
			// the template does not contain a {form_declaration}
			return;
		}
		
        $data['hidden_fields']['ACT']	= $FNS->fetch_action_id('Member', 'member_login');

		if (isset($match['4']))
		{
			$data['hidden_fields']['RET'] = (substr($match['4'], 0, 4) !== 'http') ? $FNS->create_url($match['4']) : $match['4'];
		}
		elseif ($this->in_forum == TRUE) 
		{
			$data['hidden_fields']['RET'] = $this->forum_path;
		}
		else
		{
			$data['hidden_fields']['RET']	= ($return == 'self') ? $this->_member_path($this->request.'/'.$this->cur_id) : $return;
		}

		$data['hidden_fields']['FROM'] = ($this->in_forum === TRUE) ? 'forum' : '';		
		$data['id']	  = 'member_login_form';

		$this->_set_page_title($LANG->line('member_login'));

		return $this->_var_swap($login_form, array($match['1'] => $FNS->form_declaration($data)));
	}
	/* END */

	/** ----------------------------------------
    /**  Member Login
    /** ----------------------------------------*/

    function member_login()
    {
        global $IN, $LANG, $SESS, $PREFS, $OUT, $LOC, $FNS, $DB, $EXT, $REGX;
        
        /** ----------------------------------------
        /**  Is user banned?
        /** ----------------------------------------*/
        
        if ($SESS->userdata['is_banned'] == TRUE)
		{            
            return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
		}
				
        $LANG->fetch_language_file('login');
        
        
		/* -------------------------------------------
		/* 'member_member_login_start' hook.
		/*  - Take control of member login routine
		/*  - Added EE 1.4.2
		*/
			$edata = $EXT->call_extension('member_member_login_start');
			if ($EXT->end_script === TRUE) return;
		/*
		/* -------------------------------------------*/
        
        
        /** ----------------------------------------
        /**  Error trapping
        /** ----------------------------------------*/
                
        $errors = array();

        /** ----------------------------------------
        /**  No username/password?  Bounce them...
        /** ----------------------------------------*/
    
        if ( ! $IN->GBL('multi', 'GET') && ( ! $IN->GBL('username', 'POST') || ! $IN->GBL('password', 'POST')))
        {
			$OUT->show_user_error('submission', array($LANG->line('mbr_form_empty')));        
        }
        
        /** ----------------------------------------
        /**  Is IP and User Agent required for login?
        /** ----------------------------------------*/
    
        if ($PREFS->ini('require_ip_for_login') == 'y')
        {
			if ($SESS->userdata['ip_address'] == '' || $SESS->userdata['user_agent'] == '')
			{
				$OUT->show_user_error('general', array($LANG->line('unauthorized_request')));        
           	}
        }
                
        /** ----------------------------------------
        /**  Check password lockout status
        /** ----------------------------------------*/
		
		if ($SESS->check_password_lockout() === TRUE)
		{
			$line = $LANG->line('password_lockout_in_effect');
		
			$line = str_replace("%x", $PREFS->ini('password_lockout_interval'), $line);
		
			$OUT->show_user_error('general', array($line));        
		}
				        
        /** ----------------------------------------
        /**  Fetch member data
        /** ----------------------------------------*/

		if ( ! $IN->GBL('multi', 'GET'))
		{
			$sql = "SELECT exp_members.password, exp_members.unique_id, exp_members.member_id, exp_members.group_id
					FROM   exp_members, exp_member_groups
					WHERE  username = '".$DB->escape_str($IN->GBL('username', 'POST'))."'
					AND    exp_members.group_id = exp_member_groups.group_id
					AND	   exp_member_groups.site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'";
                
        	$query = $DB->query($sql);
        	
        }
        else
        {
			if ($PREFS->ini('allow_multi_logins') == 'n' || ! $PREFS->ini('multi_login_sites') || $PREFS->ini('multi_login_sites') == '')
			{
				return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
			}
        	
			// Current site in list.  Original login site.
			if ($IN->GBL('cur', 'GET') === FALSE OR $IN->GBL('orig', 'GET') === FALSE OR $IN->GBL('orig_site_id', 'GET') === FALSE)
			{
				return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
			}
			
			// Kill old sessions first
		
			$SESS->gc_probability = 100;
			
			$SESS->delete_old_sessions();
		
			// Set cookie expiration to one year if the "remember me" button is clicked
	
			$expire = ( ! isset($_POST['auto_login'])) ? '0' : 60*60*24*365;

			// Check Session ID
				
			$query = $DB->query("SELECT exp_members.member_id, exp_members.password, exp_members.unique_id
							FROM   	exp_sessions, exp_members 
							WHERE  	exp_sessions.session_id  = '".$DB->escape_str($IN->GBL('multi', 'GET'))."'
							AND		exp_sessions.member_id = exp_members.member_id
							AND    	exp_sessions.last_activity > $expire");
			 
			if ($query->num_rows == 0) 
				return;
			
			// Set Various Cookies
			
			$FNS->set_cookie($SESS->c_anon);
			$FNS->set_cookie($SESS->c_expire , time()+$expire, $expire);
			$FNS->set_cookie($SESS->c_uniqueid , $query->row['unique_id'], $expire);       
			$FNS->set_cookie($SESS->c_password , $query->row['password'],  $expire); 
				
			if ($PREFS->ini('user_session_type') == 'cs' || $PREFS->ini('user_session_type') == 's')
			{                    
				$FNS->set_cookie($SESS->c_session , $IN->GBL('multi', 'GET'), $SESS->session_length);     
			}
			
			// -------------------------------------------
			// 'member_member_login_multi' hook.
			//  - Additional processing when a member is logging into multiple sites
			//
				$edata = $EXT->call_extension('member_member_login_multi', $query->row);
				if ($EXT->end_script === TRUE) return;
			//
			// -------------------------------------------
				
			// Check if there are any more sites to log into
			
			$sites	= explode('|',$PREFS->ini('multi_login_sites'));
			$next	= ($IN->GBL('cur', 'GET') + 1 != $IN->GBL('orig', 'GET')) ? $IN->GBL('cur', 'GET') + 1 : $IN->GBL('cur', 'GET') + 2;
			
			if ( ! isset($sites[$next]))
			{
				// Figure out return
				if  ( ! $ret = $IN->GBL('ret', 'GET'))
				{
					$ret = $sites[$IN->GBL('orig', 'GET')];
				}
				else
				{
					if (strncmp($ret, 's-', 2) == 0) 
					{
						$ret = substr_replace($ret, 'https:', 0, 2);
					}
					elseif (strncmp($ret, 'n-', 2) == 0) 
					{
						$ret = substr_replace($ret, 'http:', 0, 2);
					}
				}

				
				// We're done.
				$data = array(	'title' 	=> $LANG->line('mbr_login'),
								'heading'	=> $LANG->line('thank_you'),
								'content'	=> $LANG->line('mbr_you_are_logged_in'),
								'redirect'	=> $ret,
								'link'		=> array($ret, $LANG->line('back'))
								 );

				// Pull preferences for the original site

				if (is_numeric($IN->GBL('orig_site_id', 'GET')))
				{
					$query = $DB->query("SELECT site_name, site_id FROM exp_sites WHERE site_id = '".$DB->escape_str($IN->GBL('orig_site_id', 'GET'))."'");
					
					if ($query->num_rows == 1)
					{
						$final_site_name = $query->row['site_name'];
						$final_site_id = $query->row['site_id'];

						$PREFS->site_prefs($final_site_name, $final_site_id);	
					}
				}
				
				$OUT->show_message($data);
			}
			else
			{
				// Next Site
				
				$next_url = $sites[$next].'?ACT='.$FNS->fetch_action_id('Member', 'member_login').
							'&multi='.$IN->GBL('multi', 'GET').'&cur='.$next.'&orig='.$IN->GBL('orig').'&orig_site_id='.$IN->GBL('orig_site_id', 'GET').'&ret='.$IN->GBL('ret', 'GET');
							
				return $FNS->redirect($next_url);
			}        	
		}
        
       
        /** ----------------------------------------
        /**  Invalid Username
        /** ----------------------------------------*/

        if ($query->num_rows == 0)
        {
        	$SESS->save_password_lockout();
        	
			$OUT->show_user_error('submission', array($LANG->line('no_username')));        
        }
                
        /** ----------------------------------------
        /**  Is the member account pending?
        /** ----------------------------------------*/

        if ($query->row['group_id'] == 4)
        { 
			$OUT->show_user_error('general', array($LANG->line('mbr_account_not_active')));        
        }
                
        /** ----------------------------------------
        /**  Check password
        /** ----------------------------------------*/

        $password = $FNS->hash(stripslashes($IN->GBL('password', 'POST')));
        
        if ($query->row['password'] != $password)
        {
            // To enable backward compatibility with pMachine we'll test to see 
            // if the password was encrypted with MD5.  If so, we will encrypt the
            // password using SHA1 and update the member's info.
            
            $orig_enc_type = $PREFS->ini('encryption_type');
            $PREFS->core_ini['encryption_type'] = ($PREFS->ini('encryption_type') == 'md5') ? 'sha1' : 'md5';
			$password = $FNS->hash(stripslashes($IN->GBL('password', 'POST')));

            if ($query->row['password'] == $password)
            {
            	$PREFS->core_ini['encryption_type'] = $orig_enc_type;
				$password = $FNS->hash(stripslashes($IN->GBL('password', 'POST')));

                $sql = "UPDATE exp_members 
                        SET    password = '".$password."' 
                        WHERE  member_id = '".$query->row['member_id']."' ";
                        
                $DB->query($sql);
            }
            else
            {
				/** ----------------------------------------
				/**  Invalid password
				/** ----------------------------------------*/
					
        		$SESS->save_password_lockout();
	
				$errors[] = $LANG->line('no_password');        
            }
        }
        
        /** --------------------------------------------------
        /**  Do we allow multiple logins on the same account?
        /** --------------------------------------------------*/
        
        if ($PREFS->ini('allow_multi_logins') == 'n')
        {
            // Kill old sessions first
        
            $SESS->gc_probability = 100;
            
            $SESS->delete_old_sessions();
        
            $expire = time() - $SESS->session_length;
            
            // See if there is a current session

            $result = $DB->query("SELECT ip_address, user_agent 
                                  FROM   exp_sessions 
                                  WHERE  member_id  = '".$query->row['member_id']."'
                                  AND    last_activity > $expire
                                  AND	 site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'");
                                
            // If a session exists, trigger the error message
                               
            if ($result->num_rows == 1)
            {
                if ($SESS->userdata['ip_address'] != $result->row['ip_address'] || 
                    $SESS->userdata['user_agent'] != $result->row['user_agent'] )
                {
					$errors[] = $LANG->line('multi_login_warning');        
                }               
            } 
        }  
        
		/** ----------------------------------------
		/**  Are there errors to display?
		/** ----------------------------------------*/
        
        if (count($errors) > 0)
        {
			return $OUT->show_user_error('submission', $errors);
        }
        
		/** ----------------------------------------
		/**  Is the UN/PW the correct length?
		/** ----------------------------------------*/
		
		// If the admin has specfified a minimum username or password length that
		// is longer than the current users's data we'll have them update their info.
		// This will only be an issue if the admin has changed the un/password requiremements
		// after member accounts already exist.
		
		$uml = $PREFS->ini('un_min_len');
		$pml = $PREFS->ini('pw_min_len');
		
		$ulen = strlen($IN->GBL('username', 'POST'));
		$plen = strlen($IN->GBL('password', 'POST'));
		
		if ($ulen < $uml OR $plen < $pml)
		{
			$trigger = '';
			if ($IN->GBL('FROM') == 'forum')
			{
				$this->basepath = $REGX->xss_clean($IN->GBL('mbase'));
				$trigger =  $REGX->xss_clean($IN->GBL('trigger'));
			}
		
			$path = 'unpw_update/'.$query->row['member_id'].'_'.$ulen.'_'.$plen;
			
			if ($trigger != '')
			{
				$path .= '/'.$trigger;
			}
			
			return $FNS->redirect($this->_member_path($path));
		}
        
        
        /** ----------------------------------------
        /**  Set cookies
        /** ----------------------------------------*/
        
        // Set cookie expiration to one year if the "remember me" button is clicked

        $expire = ( ! isset($_POST['auto_login'])) ? '0' : 60*60*24*365;

		$FNS->set_cookie($SESS->c_expire , time()+$expire, $expire);
        $FNS->set_cookie($SESS->c_uniqueid , $query->row['unique_id'], $expire);       
        $FNS->set_cookie($SESS->c_password , $password,  $expire);  
                
        // Does the user want to remain anonymous?
        
        if ( ! isset($_POST['anon'])) 
        {
            $FNS->set_cookie($SESS->c_anon , 1,  $expire);
            
            $anon = 'y';            
        }
        else
        { 
            $FNS->set_cookie($SESS->c_anon);
                   
            $anon = '';
        }

        /** ----------------------------------------
        /**  Create a new session
        /** ----------------------------------------*/
        
        $SESS->create_new_session($query->row['member_id']);
        $SESS->userdata['username']  = $IN->GBL('username');
        
        // -------------------------------------------
		// 'member_member_login_single' hook.
		//  - Additional processing when a member is logging into single site
		//
			$edata = $EXT->call_extension('member_member_login_single', $query->row);
			if ($EXT->end_script === TRUE) return;
		//
		// -------------------------------------------
    
        /** ----------------------------------------
        /**  Update stats
        /** ----------------------------------------*/
 
		$cutoff		= $LOC->now - (15 * 60);
		$weblog_id	= (USER_BLOG !== FALSE) ? UB_BLOG_ID : 0;

        $DB->query("DELETE FROM exp_online_users WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' AND ((ip_address = '$IN->IP' AND member_id = '0') OR (date < $cutoff AND weblog_id = '$weblog_id'))");
                
		$data = array(
						'weblog_id'		=> $weblog_id,
						'member_id'		=> $SESS->userdata('member_id'),
						'name'			=> ($SESS->userdata['screen_name'] == '') ? $SESS->userdata['username'] : $SESS->userdata['screen_name'],
						'ip_address'	=> $IN->IP,
						'date'			=> $LOC->now,
						'anon'			=> $anon,
						'site_id'		=> $PREFS->ini('site_id')
					);
       
		$DB->query($DB->update_string('exp_online_users', $data, array("ip_address" => $IN->IP, "member_id" => $data['member_id'], "weblog_id" => $data['weblog_id'])));
               
        /** ----------------------------------------
        /**  Delete old password lockouts
        /** ----------------------------------------*/
        
		$SESS->delete_password_lockout();
		
		/** ----------------------------------------
        /**  Multiple Site Logins
        /** ----------------------------------------*/
        
		if ($PREFS->ini('allow_multi_logins') == 'y' && $PREFS->ini('multi_login_sites') != '')
		{
			// Next Site
			$sites		=  explode('|',$PREFS->ini('multi_login_sites'));
			$current	= $FNS->fetch_site_index();
			$ret = $FNS->remove_double_slashes($FNS->form_backtrack());
			
			if (strncmp($ret, 'https:', 6) == 0) 
			{
				$ret = '&ret=s-'.str_replace('https:', '', $ret);
			}
			elseif (strncmp($ret, 'http:', 5) == 0)
			{
				$ret = '&ret=n-'.str_replace('http:', '', $ret);
			}
			else
			{
				$ret = '';
			}
			
			if (sizeof($sites) > 1 && in_array($current, $sites))
			{
				$orig = array_search($current, $sites);
				$next = ($orig == '0') ? '1' : '0';
			
				$next_url = $sites[$next].'?ACT='.$FNS->fetch_action_id('Member', 'member_login').
							'&multi='.$SESS->userdata['session_id'].'&cur='.$next.'&orig='.$orig.'&orig_site_id='.$PREFS->ini('site_id').$ret;
							
				return $FNS->redirect($next_url);
			}		
		}
	
        /** ----------------------------------------
        /**  Build success message
        /** ----------------------------------------*/
		
		$site_name = ($PREFS->ini('site_name') == '') ? $LANG->line('back') : stripslashes($PREFS->ini('site_name'));
		
        $return = $FNS->remove_double_slashes($FNS->form_backtrack());
		                
        /** ----------------------------------------
        /**  Is this a forum request?
        /** ----------------------------------------*/
        
		if ($IN->GBL('FROM') == 'forum')
		{
			if ($IN->GBL('board_id') !== FALSE && is_numeric($IN->GBL('board_id')))
			{
				$query	= $DB->query("SELECT board_label FROM exp_forum_boards WHERE board_id = '".$DB->escape_str($IN->GBl('board_id'))."'");
			}
			else
			{
				$query	= $DB->query("SELECT board_label FROM exp_forum_boards WHERE board_id = '1'");
			}
			
			$site_name	= $query->row['board_label'];
		}

        /** ----------------------------------------
        /**  Build success message
        /** ----------------------------------------*/
		                
        $data = array(	'title' 	=> $LANG->line('mbr_login'),
						'heading'	=> $LANG->line('thank_you'),
						'content'	=> $LANG->line('mbr_you_are_logged_in'),
						'redirect'	=> $return,
						'link'		=> array($return, $site_name)
					 );
			
		$OUT->show_message($data);
    }
    /* END */


    /** ----------------------------------------
    /**  Member Logout
    /** ----------------------------------------*/

    function member_logout()
    {
        global $PREFS, $IN, $EXT, $LANG, $SESS, $OUT, $FNS, $DB;
        
        /** ----------------------------------------
        /**  Kill the session and cookies
        /** ----------------------------------------*/

        $DB->query("DELETE FROM exp_online_users WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' AND ip_address = '$IN->IP' AND member_id = '".$SESS->userdata('member_id')."'");

        $DB->query("DELETE FROM exp_sessions WHERE session_id = '".$SESS->userdata['session_id']."'");
                
        $FNS->set_cookie($SESS->c_uniqueid);       
        $FNS->set_cookie($SESS->c_password);   
        $FNS->set_cookie($SESS->c_session);   
        $FNS->set_cookie($SESS->c_expire);   
        $FNS->set_cookie($SESS->c_anon);  
        $FNS->set_cookie('read_topics');  
        $FNS->set_cookie('tracker');  

		/* -------------------------------------------
		/* 'member_member_logout' hook.
		/*  - Perform additional actions after logout
		/*  - Added EE 1.6.1
		*/
			$edata = $EXT->call_extension('member_member_logout');
			if ($EXT->end_script === TRUE) return;
		/*
		/* -------------------------------------------*/
		
        /** ----------------------------------------
        /**  Is this a forum redirect?
        /** ----------------------------------------*/
        
        $name = '';
        unset($url);

		if ($IN->GBL('FROM') == 'forum')
		{
			if ($IN->GBL('board_id') !== FALSE && is_numeric($IN->GBL('board_id')))
			{
				$query	= $DB->query("SELECT board_forum_url, board_label FROM exp_forum_boards WHERE board_id = '".$DB->escape_str($IN->GBl('board_id'))."'");
			}
			else
			{
				$query	= $DB->query("SELECT board_forum_url, board_label FROM exp_forum_boards WHERE board_id = '1'");
			}
			
			$url	= $query->row['board_forum_url'];
			$name	= $query->row['board_label'];
		}

        /** ----------------------------------------
        /**  Build success message
        /** ----------------------------------------*/

		$url	= ( ! isset($url)) ? $PREFS->ini('site_url')	: $url;
		$name	= ( ! isset($url)) ? stripslashes($PREFS->ini('site_name'))	: $name;
		
        $data = array(	'title' 	=> $LANG->line('mbr_login'),
        				'heading'	=> $LANG->line('thank_you'),
        				'content'	=> $LANG->line('mbr_you_are_logged_out'),
        				'redirect'	=> $url,
        				'link'		=> array($url, $name)
        			 );
					
		$OUT->show_message($data);
    }
    /* END */


	

    /** ----------------------------------------
    /**  Member Forgot Password Form
    /** ----------------------------------------*/

    function forgot_password($ret = '-3')
    {
		global $IN, $FNS, $LANG, $PREFS;
		
		$data['id']				= 'forgot_password_form';
		$data['hidden_fields']	= array(
										'ACT'   => $FNS->fetch_action_id('Member', 'retrieve_password'),
										'RET'	=> $ret,
										'FROM'	=> ($this->in_forum == TRUE) ? 'forum' : ''
									  );            

		if ($this->in_forum === TRUE)
		{
			$data['hidden_fields']['board_id'] = $this->board_id;
		}
		
		$this->_set_page_title($LANG->line('mbr_forgotten_password'));
            
		return $this->_var_swap($this->_load_element('forgot_form'), 
										array(
												'form_declaration'		=>	$FNS->form_declaration($data)
											 )
										);
    }
    /* END */



    /** ----------------------------------------
    /**  Retreive Forgotten Password
    /** ----------------------------------------*/

    function retrieve_password()
    {
        global $LANG, $PREFS, $SESS, $REGX, $FNS, $DSP, $IN, $DB, $OUT;
                
        /** ----------------------------------------
        /**  Is user banned?
        /** ----------------------------------------*/
        
        if ($SESS->userdata['is_banned'] == TRUE)
		{            
            return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
		}
		
        /** ----------------------------------------
        /**  Error trapping
        /** ----------------------------------------*/
        
        if ( ! $address = $IN->GBL('email', 'POST'))
        {
			return $OUT->show_user_error('submission', array($LANG->line('invalid_email_address')));
        }
        
        if ( ! $REGX->valid_email($address))
        {
			return $OUT->show_user_error('submission', array($LANG->line('invalid_email_address')));
        }
        
		$address = strip_tags($address);
        
        // Fetch user data
        
        $sql = "SELECT member_id, username FROM exp_members WHERE email ='".$DB->escape_str($address)."'";
        
        $query = $DB->query($sql);
        
        if ($query->num_rows == 0)
        {
			return $OUT->show_user_error('submission', array($LANG->line('no_email_found')));
        }
        
        $member_id = $query->row['member_id'];
        $username  = $query->row['username'];
        
        // Kill old data from the reset_password field
        
        $time = time() - (60*60*24);
        
        $DB->query("DELETE FROM exp_reset_password WHERE date < $time OR member_id = '$member_id'");
        
        // Create a new DB record with the temporary reset code
        
        $rand = $FNS->random('alpha', 8);
                
        $data = array('member_id' => $member_id, 'resetcode' => $rand, 'date' => time());
         
        $DB->query($DB->insert_string('exp_reset_password', $data));
        
        // Buid the email message       
        
        $qs = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';    
                
		if ($IN->GBL('FROM') == 'forum')
		{
			if ($IN->GBL('board_id') !== FALSE && is_numeric($IN->GBL('board_id')))
			{
				$query	= $DB->query("SELECT board_forum_url, board_id, board_label FROM exp_forum_boards WHERE board_id = '".$DB->escape_str($IN->GBl('board_id'))."'");
			}
			else
			{
				$query	= $DB->query("SELECT board_forum_url, board_id, board_label FROM exp_forum_boards WHERE board_id = '1'");
			}
			
			$return		= $query->row['board_forum_url'];
			$site_name	= $query->row['board_label'];
			$board_id	= $query->row['board_id'];
		}
		else
		{
			$site_name	= stripslashes($PREFS->ini('site_name'));
			$return 	= $PREFS->ini('site_url');
		}
                		
		$forum_id = ($IN->GBL('FROM') == 'forum') ? '&r=f&board_id='.$board_id : '';
        		
		$swap = array(
						'name'		=> $username,
						'reset_url'	=> $FNS->fetch_site_index(0, 0).$qs.'ACT='.$FNS->fetch_action_id('Member', 'reset_password').'&id='.$rand.$forum_id,
						'site_name'	=> $site_name,
						'site_url'	=> $return
					 );
		
		$template = $FNS->fetch_email_template('forgot_password_instructions');
		$email_tit = $this->_var_swap($template['title'], $swap);
		$email_msg = $this->_var_swap($template['data'], $swap);
                 
        // Instantiate the email class
             
        require PATH_CORE.'core.email'.EXT;
        
        $email = new EEmail;
        $email->wordwrap = true;
        $email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));	
        $email->to($address); 
        $email->subject($email_tit);	
        $email->message($email_msg);	
        
        if ( ! $email->Send())
        {
			return $OUT->show_user_error('submission', array($LANG->line('error_sending_email')));
        } 

        /** ----------------------------------------
        /**  Build success message
        /** ----------------------------------------*/
		                
        $data = array(	'title' 	=> $LANG->line('mbr_login'),
        				'heading'	=> $LANG->line('thank_you'),
        				'content'	=> $LANG->line('forgotten_email_sent'),
        				'link'		=> array($return, $site_name)
        			 );
			
		$OUT->show_message($data);
	}
	/* END */



	/** ----------------------------------------
	/**  Reset the user's password
	/** ----------------------------------------*/

	function reset_password()
	{
        global $LANG, $PREFS, $SESS, $FNS, $DSP, $IN, $OUT, $DB;
        
        /** ----------------------------------------
        /**  Is user banned?
        /** ----------------------------------------*/
        
        if ($SESS->userdata['is_banned'] == TRUE)
		{            
            return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
		}               
        
        if ( ! $id = $IN->GBL('id'))
        {
			return $OUT->show_user_error('submission', array($LANG->line('mbr_no_reset_id')));
        }
                
        $time = time() - (60*60*24);
                   
        // Get the member ID from the reset_password field   
                
        $query = $DB->query("SELECT member_id FROM exp_reset_password WHERE resetcode ='".$DB->escape_str($id)."' and date > $time");
        
        if ($query->num_rows == 0)
        {
			return $OUT->show_user_error('submission', array($LANG->line('mbr_id_not_found')));
        }
        
        $member_id = $query->row['member_id'];
                
        // Fetch the user data
        
        $sql = "SELECT username, email FROM exp_members WHERE member_id ='$member_id'";
        
        $query = $DB->query($sql);
        
        if ($query->num_rows == 0)
        {
            return false;
        }
        
        $address   = $query->row['email'];
        $username  = $query->row['username'];
                
        $rand = $FNS->random('alpha', 8);
        
        // Update member's password
        
        $sql = "UPDATE exp_members SET password = '".$FNS->hash($rand)."' WHERE member_id = '$member_id'";
       
        $DB->query($sql);
        
        // Kill old data from the reset_password field
        
        $DB->query("DELETE FROM exp_reset_password WHERE date < $time OR member_id = '$member_id'");
                
        // Buid the email message   
        
        
		if ($IN->GBL('r') == 'f')
		{
			if ($IN->GBL('board_id') !== FALSE && is_numeric($IN->GBL('board_id')))
			{
				$query	= $DB->query("SELECT board_forum_url, board_label FROM exp_forum_boards WHERE board_id = '".$DB->escape_str($IN->GBl('board_id'))."'");
			}
			else
			{
				$query	= $DB->query("SELECT board_forum_url, board_label FROM exp_forum_boards WHERE board_id = '1'");
			}
			
			$return		= $query->row['board_forum_url'];
			$site_name	= $query->row['board_label'];
		}
		else
		{
			$site_name = stripslashes($PREFS->ini('site_name'));
			$return 	= $PREFS->ini('site_url');
		}
        
		$swap = array(
						'name'		=> $username,
						'username'	=> $username,
						'password'	=> $rand,
						'site_name'	=> $site_name,
						'site_url'	=> $return
					 );
		
		$template = $FNS->fetch_email_template('reset_password_notification');
		$email_tit = $this->_var_swap($template['title'], $swap);
		$email_msg = $this->_var_swap($template['data'], $swap);

        // Instantiate the email class
             
        require PATH_CORE.'core.email'.EXT;
        
        $email = new EEmail;
        $email->wordwrap = true;
        $email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));
        $email->to($address); 
        $email->subject($email_tit);	
        $email->message($email_msg);	
        
        if ( ! $email->Send())
        {
			return $OUT->show_user_error('submission', array($LANG->line('error_sending_email')));
        } 

        /** ----------------------------------------
        /**  Build success message
        /** ----------------------------------------*/
        
		$site_name = ($PREFS->ini('site_name') == '') ? $LANG->line('back') : stripslashes($PREFS->ini('site_name'));
		                
        $data = array(	'title' 	=> $LANG->line('mbr_login'),
        				'heading'	=> $LANG->line('thank_you'),
        				'content'	=> $LANG->line('password_has_been_reset'),
        				'link'		=> array($return, $site_name)
        			 );
			
		$OUT->show_message($data);
	}
	/* END */



}
// END CLASS
?>