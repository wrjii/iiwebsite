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
 File: mod.member_register.php
=====================================================

*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Member_register extends Member {


    /** ----------------------------------
    /**  Member_register Profile Constructor
    /** ----------------------------------*/

	function Member_register()
	{
	}
	/* END */


    /** ----------------------------------------
    /**  Member Registration Form
    /** ----------------------------------------*/

    function registration_form()
    {
        global $IN, $FNS, $LANG, $PREFS, $DB, $OUT, $SESS, $LOC;
                
        /** -------------------------------------
        /**  Do we allow new member registrations?
        /** ------------------------------------*/
        
		if ($PREFS->ini('allow_member_registration') == 'n')
		{ 
                
			$data = array(	'title' 	=> $LANG->line('mbr_registration'),
							'heading'	=> $LANG->line('notice'),
							'content'	=> $LANG->line('mbr_registration_not_allowed'),
							'link'		=> array($FNS->fetch_site_index(), stripslashes($PREFS->ini('site_name')))
						 );
				
			$OUT->show_message($data);	
        }
        
        /** -------------------------------------
        /**  Is the current user logged in?
        /** ------------------------------------*/
        
		if ($SESS->userdata('member_id') != 0)
		{ 
			return $OUT->show_user_error('general', array($LANG->line('mbr_you_are_registered')));
        }
                
        /** -------------------------------------
        /**  Fetch the registration form
        /** ------------------------------------*/
                   
		$reg_form = $this->_load_element('registration_form');

        /** -------------------------------------
        /**  Do we have custom fields to show?
        /** ------------------------------------*/
        
        $query = $DB->query("SELECT * FROM  exp_member_fields WHERE m_field_reg = 'y' ORDER BY m_field_order");

        // If not, we'll kill the custom field variables from the template
        
        if ($query->num_rows == 0)
        {
            $reg_form = preg_replace("/{custom_fields}.*?{\/custom_fields}/s", "", $reg_form);
        }        
        else
        {
            /** -------------------------------------
            /**  Parse custom field data
            /** ------------------------------------*/
            
            // First separate the chunk between the {custom_fields} variable pairs.
            
            $field_chunk = (preg_match("/{custom_fields}(.*?){\/custom_fields}/s", $reg_form, $match)) ? $match['1'] : '';
            
            // Next, separate the chunck between the {required} variable pairs
            
            $req_chunk   = (preg_match("/{required}(.*?){\/required}/s", $field_chunk, $match)) ? $match['1'] : '';
            
            
            /** -------------------------------------
            /**  Loop through the query result
            /** ------------------------------------*/
            
            $str = '';
            
            foreach ($query->result as $row)
            {
                $field  = '';           
                $temp   = $field_chunk;
                
                /** --------------------------------
                /**  Replace {field_name}
                /** --------------------------------*/
                
                $temp = str_replace("{field_name}", $row['m_field_label'], $temp);
                
                if ($row['m_field_description'] == '')
                {
					$temp = preg_replace("/{if field_description}.+?{\/if}/s", "", $temp); 		
                }
                else
                {
					$temp = preg_replace("/{if field_description}(.+?){\/if}/s", "\\1", $temp); 		
                }
                
				$temp = str_replace("{field_description}", $row['m_field_description'], $temp);
                
                /** --------------------------------
                /**  Replace {required} pair
                /** --------------------------------*/
                
                if ($row['m_field_required'] == 'y')
                {
                    $temp = preg_replace("/".LD."required".RD.".*?".LD."\/required".RD."/s", $req_chunk, $temp);
                }
                else
                {
                    $temp = preg_replace("/".LD."required".RD.".*?".LD."\/required".RD."/s", '', $temp);
                }                

                /** --------------------------------
                /**  Parse input fields
                /** --------------------------------*/
                
                // Set field width            

                $width = ( ! stristr($row['m_field_width'], 'px')  AND strpos($row['m_field_width'], '%') === FALSE) ? $row['m_field_width'].'px' : $row['m_field_width'];
                                                                                              

                //  Textarea fields
    
                if ($row['m_field_type'] == 'textarea')
                {   
                    $rows = ( ! isset($row['m_field_ta_rows'])) ? '10' : $row['m_field_ta_rows'];
    
                    $field = "<textarea style=\"width:{$width};\" name=\"m_field_id_".$row['m_field_id']."\"  cols='50' rows='{$rows}' class=\"textarea\" ></textarea>";
                }
                else
                {   
                    //  Text fields
                                 
                    if ($row['m_field_type'] == 'text')
                    {   
                        $maxlength = ($row['m_field_maxl'] == 0) ? '100' : $row['m_field_maxl'];   
                    
                        $field = "<input type=\"text\" name=\"m_field_id_".$row['m_field_id']."\" value=\"\" class=\"input\" maxlength=\"$maxlength\" size=\"40\" style=\"width:{$width};\" />";
                    }
                    elseif ($row['m_field_type'] == 'select')
                    {     
                    
                        //  Drop-down fields
                        
                        $select_list = trim($row['m_field_list_items']);
                    
                        if ($select_list != '')
                        {
                            $field = "<select name=\"m_field_id_".$row['m_field_id']."\" class=\"select\">";
                            
                            foreach (explode("\n", $select_list) as $v)
                            {   
                                $v = trim($v);
                                
                                 $field .= "<option value=\"$v\">$v</option>";
                            }
                            
                             $field .= "</select>";  
                        }                      
                    }
                }
                                
                $temp = str_replace("{field}", $field, $temp);

                $str .= $temp;
            }

			// since $str may have sequences that look like PCRE backreferences, the two choices are to escape them
			// and use preg_replace() or to match the pattern and use str_replace().  This way happens to be faster in this case.
			if (preg_match("/".LD."custom_fields".RD.".*?".LD."\/custom_fields".RD."/s", $reg_form, $match))
			{
				$reg_form = str_replace($match[0], $str, $reg_form);	
			}
        } 
             
        
		/** ----------------------------------------
		/**  {if captcha}
		/** ----------------------------------------*/
		
		if (preg_match("/{if captcha}(.+?){\/if}/s", $reg_form, $match))
		{
			if ($PREFS->ini('use_membership_captcha') == 'y')
			{
				$reg_form = preg_replace("/{if captcha}.+?{\/if}/s", $match['1'], $reg_form); 
				
				// Bug fix.  Deprecate this later..
				$reg_form = str_replace('{captcha_word}', '', $reg_form);
				
				if ( ! class_exists('Template'))
				{
					$reg_form = preg_replace("/{captcha}/", $FNS->create_captcha(), $reg_form);
				}
			}
			else
			{
				$reg_form = preg_replace("/{if captcha}.+?{\/if}/s", "", $reg_form); 		
			}
		}

		$un_min_len = str_replace("%x", $PREFS->ini('un_min_len'), $LANG->line('mbr_username_length'));
		$pw_min_len = str_replace("%x", $PREFS->ini('pw_min_len'), $LANG->line('mbr_password_length'));
		
		// Time format selection menu
		
		$tf = "<select name='time_format' class='select'>\n";
		$tf .= "<option value='us'>".$LANG->line('united_states')."</option>\n";
		$tf .= "<option value='eu'>".$LANG->line('european')."</option>\n";
		$tf .= "</select>\n";
		
		
		/** ----------------------------------------
		/**  Parse languge lines
		/** ----------------------------------------*/
				
		$reg_form = $this->_var_swap($reg_form,
									array(
											'lang:username_length'	=> $un_min_len,
											'lang:password_length'	=> $pw_min_len,
											'form:localization'		=> $LOC->timezone_menu('UTC'),
											'form:time_format'		=> $tf,
											'form:language'			=> $FNS->language_pack_names('english')
									 
										)
									);
		
        /** ----------------------------------------
        /**  Generate Form declaration
        /** ----------------------------------------*/
                
        $data['hidden_fields'] = array(
										'ACT'	=> $FNS->fetch_action_id('Member', 'register_member'),
										'RET'	=> $FNS->fetch_site_index(),
										'FROM'	=> ($this->in_forum == TRUE) ? 'forum' : '',
									  );            
									
		if ($this->in_forum === TRUE)
		{
			$data['hidden_fields']['board_id'] = $this->board_id;
		}
     	
		$data['id']		= 'register_member_form';
     
        /** ----------------------------------------
        /**  Return the final rendered form
        /** ----------------------------------------*/

        return $FNS->form_declaration($data).$reg_form."\n"."</form>";
    }
    /* END */




    /** ----------------------------------------
    /**  Register Member
    /** ----------------------------------------*/

    function register_member()
    {
        global $IN, $DB, $SESS, $PREFS, $FNS, $LOC, $LANG, $OUT, $STAT, $REGX, $EXT;
        
        /** -------------------------------------
        /**  Do we allow new member registrations?
        /** ------------------------------------*/
        
		if ($PREFS->ini('allow_member_registration') == 'n')
		{
			return false;
        }

        /** ----------------------------------------
        /**  Is user banned?
        /** ----------------------------------------*/
        
        if ($SESS->userdata['is_banned'] == TRUE)
		{            
            return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
		}	
		
		/** ----------------------------------------
        /**  Blacklist/Whitelist Check
        /** ----------------------------------------*/
        
        if ($IN->blacklisted == 'y' && $IN->whitelisted == 'n')
        {
        	return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
        }
        
        /* -------------------------------------------
		/* 'member_member_register_start' hook.
		/*  - Take control of member registration routine
		/*  - Added EE 1.4.2
		*/
			$edata = $EXT->call_extension('member_member_register_start');
			if ($EXT->end_script === TRUE) return;
		/*
		/* -------------------------------------------*/
        
		        
        /** ----------------------------------------
        /**  Set the default globals
        /** ----------------------------------------*/
        
        $default = array('username', 'password', 'password_confirm', 'email', 'screen_name', 'url', 'location');
                
        foreach ($default as $val)
        {
        	if ( ! isset($_POST[$val])) $_POST[$val] = '';
        }
        
        if ($_POST['screen_name'] == '')
        	$_POST['screen_name'] = $_POST['username'];
        
        /** -------------------------------------
        /**  Instantiate validation class
        /** -------------------------------------*/

		if ( ! class_exists('Validate'))
		{
			require PATH_CORE.'core.validate'.EXT;
		}
		
		$VAL = new Validate(
								array( 
										'member_id'			=> '',
										'val_type'			=> 'new', // new or update
										'fetch_lang' 		=> TRUE, 
										'require_cpw' 		=> FALSE,
									 	'enable_log'		=> FALSE,
										'username'			=> $_POST['username'],
										'cur_username'		=> '',
										'screen_name'		=> stripslashes($_POST['screen_name']),
										'cur_screen_name'	=> '',
										'password'			=> $_POST['password'],
									 	'password_confirm'	=> $_POST['password_confirm'],
									 	'cur_password'		=> '',
									 	'email'				=> $_POST['email'],
									 	'cur_email'			=> ''
									 )
							);
		
		$VAL->validate_username();
		$VAL->validate_screen_name();
		$VAL->validate_password();
		$VAL->validate_email();

        /** -------------------------------------
        /**  Do we have any custom fields?
        /** -------------------------------------*/
        
        $query = $DB->query("SELECT m_field_id, m_field_name, m_field_label, m_field_required FROM exp_member_fields WHERE m_field_reg = 'y'");
        
        $cust_errors = array();
        $cust_fields = array();
        
        if ($query->num_rows > 0)
        {
			foreach ($query->result as $row)
			{
				if ($row['m_field_required'] == 'y' && ( ! isset($_POST['m_field_id_'.$row['m_field_id']]) OR $_POST['m_field_id_'.$row['m_field_id']] == ''))
				{
					$cust_errors[] = $LANG->line('mbr_field_required').'&nbsp;'.$row['m_field_label'];
				}
				elseif (isset($_POST['m_field_id_'.$row['m_field_id']])) 
				{
					$cust_fields['m_field_id_'.$row['m_field_id']] = $REGX->xss_clean($_POST['m_field_id_'.$row['m_field_id']]);
				}           
			}
        }      
		
		if ($PREFS->ini('use_membership_captcha') == 'y')
		{
			if ( ! isset($_POST['captcha']) || $_POST['captcha'] == '')
			{
				$cust_errors[] = $LANG->line('captcha_required');
			}
		}		
        
        if ($PREFS->ini('require_terms_of_service') == 'y')
        {
			if ( ! isset($_POST['accept_terms']))
			{
				$cust_errors[] = $LANG->line('mbr_terms_of_service_required');
			}
        }
                
		$errors = array_merge($VAL->errors, $cust_errors);
		
		
        /** -------------------------------------
        /**  Display error is there are any
        /** -------------------------------------*/

         if (count($errors) > 0)
         {
			return $OUT->show_user_error('submission', $errors);
         }
         
         
        /** ----------------------------------------
        /**  Do we require captcha?
        /** ----------------------------------------*/
		
		if ($PREFS->ini('use_membership_captcha') == 'y')
		{			
            $query = $DB->query("SELECT COUNT(*) AS count FROM exp_captcha WHERE word='".$DB->escape_str($_POST['captcha'])."' AND ip_address = '".$IN->IP."' AND date > UNIX_TIMESTAMP()-7200");
		
            if ($query->row['count'] == 0)
            {
				return $OUT->show_user_error('submission', array($LANG->line('captcha_incorrect')));
			}
		
            $DB->query("DELETE FROM exp_captcha WHERE (word='".$DB->escape_str($_POST['captcha'])."' AND ip_address = '".$IN->IP."') OR date < UNIX_TIMESTAMP()-7200");
		}
		
        /** ----------------------------------------
        /**  Secure Mode Forms?
        /** ----------------------------------------*/
		
        if ($PREFS->ini('secure_forms') == 'y')
        {
            $query = $DB->query("SELECT COUNT(*) AS count FROM exp_security_hashes WHERE hash='".$DB->escape_str($_POST['XID'])."' AND ip_address = '".$IN->IP."' AND ip_address = '".$IN->IP."' AND date > UNIX_TIMESTAMP()-7200");
        
            if ($query->row['count'] == 0)
            {
				return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
			}
			
            $DB->query("DELETE FROM exp_security_hashes WHERE (hash='".$DB->escape_str($_POST['XID'])."' AND ip_address = '".$IN->IP."') OR date < UNIX_TIMESTAMP()-7200");
		}
                  
        /** -------------------------------------
        /**  Assign the base query data
        /** -------------------------------------*/
        
        // Set member group
                        
        if ($PREFS->ini('req_mbr_activation') == 'manual' || $PREFS->ini('req_mbr_activation') == 'email')
        {
        	$data['group_id'] = 4;  // Pending
        }
        else
        {
        	if ($PREFS->ini('default_member_group') == '')
        	{
				$data['group_id'] = 4;  // Pending
        	}
        	else
        	{
				$data['group_id'] = $PREFS->ini('default_member_group');
        	}
        }       
                 
        $data['username']    = $_POST['username'];
        $data['password']    = $FNS->hash(stripslashes($_POST['password']));
        $data['ip_address']  = $IN->IP;
        $data['unique_id']   = $FNS->random('encrypt');
        $data['join_date']   = $LOC->now;
        $data['email']       = $_POST['email'];
        $data['screen_name'] = $_POST['screen_name'];
        $data['url']         = $REGX->prep_url($_POST['url']);
        $data['location']	 = $_POST['location'];
        
        // Optional Fields
        
        $optional = array('bio'					=> 'bio', 
        				  'language'			=> 'deft_lang', 
        				  'timezone'			=> 'server_timezone', 
        				  'time_format'			=> 'time_format');
        
        foreach($optional as $key => $value)
        {
        	if (isset($_POST[$value]))
        	{
        		$data[$key] = $_POST[$value];
        	}
        }
        
        $data['daylight_savings'] = ($IN->GBL('daylight_savings', 'POST') == 'y') ? 'y' : 'n';
        
        // We generate an authorization code if the member needs to self-activate
        
		if ($PREFS->ini('req_mbr_activation') == 'email')
		{
			$data['authcode'] = $FNS->random('alpha', 10);
		}
		        
        /** -------------------------------------
        /**  Insert basic member data
        /** -------------------------------------*/

        $DB->query($DB->insert_string('exp_members', $data)); 
        
        $member_id = $DB->insert_id;
         
        /** -------------------------------------
        /**  Insert custom fields
        /** -------------------------------------*/

		$cust_fields['member_id'] = $member_id;
											   
		$DB->query($DB->insert_string('exp_member_data', $cust_fields));


        /** -------------------------------------
        /**  Create a record in the member homepage table
        /** -------------------------------------*/

		// This is only necessary if the user gains CP access, but we'll add the record anyway.            
                           
        $DB->query($DB->insert_string('exp_member_homepage', array('member_id' => $member_id)));
        
        
        /** -------------------------------------
        /**  Mailinglist Subscribe
        /** -------------------------------------*/
        
        $mailinglist_subscribe = FALSE;
        
        if (isset($_POST['mailinglist_subscribe']) && is_numeric($_POST['mailinglist_subscribe']))
		{
			// Kill duplicate emails from authorizatin queue.
			$DB->query("DELETE FROM exp_mailing_list_queue WHERE email = '".$DB->escape_str($_POST['email'])."'");
			
			// Validate Mailing List ID
			$query = $DB->query("SELECT COUNT(*) AS count 
								 FROM exp_mailing_lists 
								 WHERE list_id = '".$DB->escape_str($_POST['mailinglist_subscribe'])."'");
			
			// Email Not Already in Mailing List
			$results = $DB->query("SELECT count(*) AS count 
								   FROM exp_mailing_list 
								   WHERE email = '".$DB->escape_str($_POST['email'])."' 
								   AND list_id = '".$DB->escape_str($_POST['mailinglist_subscribe'])."'");
			
			/** -------------------------------------
			/**  INSERT Email
			/** -------------------------------------*/
			
			if ($query->row['count'] > 0 && $results->row['count'] == 0)
			{	
				$mailinglist_subscribe = TRUE;
				
				$code = $FNS->random('alpha', 10);
				
				if ($PREFS->ini('req_mbr_activation') == 'email')
				{
					// Activated When Membership Activated
					$DB->query("INSERT INTO exp_mailing_list_queue (email, list_id, authcode, date) 
								VALUES ('".$DB->escape_str($_POST['email'])."', '".$DB->escape_str($_POST['mailinglist_subscribe'])."', '".$code."', '".time()."')");			
				}
				elseif ($PREFS->ini('req_mbr_activation') == 'manual')
				{
					// Mailing List Subscribe Email
					$DB->query("INSERT INTO exp_mailing_list_queue (email, list_id, authcode, date) 
								VALUES ('".$DB->escape_str($_POST['email'])."', '".$DB->escape_str($_POST['mailinglist_subscribe'])."', '".$code."', '".time()."')");			
					
					$LANG->fetch_language_file('mailinglist');
					
					$qs = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';        
					$action_id  = $FNS->fetch_action_id('Mailinglist', 'authorize_email');
			
					$swap = array(
									'activation_url'	=> $FNS->fetch_site_index(0, 0).$qs.'ACT='.$action_id.'&id='.$code,
									'site_name'			=> stripslashes($PREFS->ini('site_name')),
									'site_url'			=> $PREFS->ini('site_url')
								 );
					
					$template = $FNS->fetch_email_template('mailinglist_activation_instructions');
					$email_tit = $FNS->var_swap($template['title'], $swap);
					$email_msg = $FNS->var_swap($template['data'], $swap);
					
					/** ----------------------------
					/**  Send email
					/** ----------------------------*/
			
					if ( ! class_exists('EEmail'))
					{
						require PATH_CORE.'core.email'.EXT;
					}
								
					$E = new EEmail;        
					$E->wordwrap = true;
					$E->mailtype = 'plain';
					$E->priority = '3';
					
					$E->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));	
					$E->to($_POST['email']); 
					$E->subject($email_tit);	
					$E->message($email_msg);	
					$E->Send();
				}	
				else
				{
					// Automatically Accepted
					$DB->query("INSERT INTO exp_mailing_list (user_id, list_id, authcode, email, ip_address) 
								VALUES ('', '".$DB->escape_str($_POST['mailinglist_subscribe'])."', '".$code."', '".$DB->escape_str($_POST['email'])."', '".$DB->escape_str($IN->IP)."')");			
				}
			}
		}
        
        /** -------------------------------------
        /**  Update global member stats
        /** -------------------------------------*/
      
		if ($PREFS->ini('req_mbr_activation') == 'none')
		{
			$STAT->update_member_stats();
		}
		
        /** -------------------------------------
        /**  Send admin notifications
        /** -------------------------------------*/
	
		if ($PREFS->ini('new_member_notification') == 'y' AND $PREFS->ini('mbr_notification_emails') != '')
		{
			$name = ($data['screen_name'] != '') ? $data['screen_name'] : $data['username'];
            
			$swap = array(
							'name'					=> $name,
							'site_name'				=> stripslashes($PREFS->ini('site_name')),
							'control_panel_url'		=> $PREFS->ini('cp_url'),
							'username'				=> $data['username'],
							'email'					=> $data['email']
						 );
			
			$template = $FNS->fetch_email_template('admin_notify_reg');
			$email_tit = $this->_var_swap($template['title'], $swap);
			$email_msg = $this->_var_swap($template['data'], $swap);
                                    
			$notify_address = $REGX->remove_extra_commas($PREFS->ini('mbr_notification_emails'));
                        
            /** ----------------------------
            /**  Send email
            /** ----------------------------*/
            
            if ( ! class_exists('EEmail'))
            {
				require PATH_CORE.'core.email'.EXT;
            }
                 
            $email = new EEmail;
            $email->wordwrap = true;
            $email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));	
            $email->to($notify_address); 
            $email->subject($email_tit);	
            $email->message($REGX->entities_to_ascii($email_msg));		
            $email->Send();
		}
		
		// -------------------------------------------
		// 'member_member_register' hook.
		//  - Additional processing when a member is created through the User Side
		//
			$edata = $EXT->call_extension('member_member_register', $data);
			if ($EXT->end_script === TRUE) return;
		//
		// -------------------------------------------
	
	
        /** -------------------------------------
        /**  Send user notifications
        /** -------------------------------------*/

		if ($PREFS->ini('req_mbr_activation') == 'email')
		{
			$qs = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';        
			
			$action_id  = $FNS->fetch_action_id('Member', 'activate_member');
		
			$name = ($data['screen_name'] != '') ? $data['screen_name'] : $data['username'];
			
			$board_id = ($IN->GBL('board_id') !== FALSE && is_numeric($IN->GBL('board_id'))) ? $IN->GBL('board_id') : 1;
		
			$forum_id = ($IN->GBL('FROM') == 'forum') ? '&r=f&board_id='.$board_id : '';
			
			$add = ($mailinglist_subscribe !== TRUE) ? '' : '&mailinglist='.$_POST['mailinglist_subscribe']; 
				
			$swap = array(
							'name'				=> $name,
							'activation_url'	=> $FNS->fetch_site_index(0, 0).$qs.'ACT='.$action_id.'&id='.$data['authcode'].$forum_id.$add,
							'site_name'			=> stripslashes($PREFS->ini('site_name')),
							'site_url'			=> $PREFS->ini('site_url'),
							'username'			=> $data['username'],
							'email'				=> $data['email']
						 );
			
			$template = $FNS->fetch_email_template('mbr_activation_instructions');
			$email_tit = $this->_var_swap($template['title'], $swap);
			$email_msg = $this->_var_swap($template['data'], $swap);
                                                
            /** ----------------------------
            /**  Send email
            /** ----------------------------*/
            
            if ( ! class_exists('EEmail'))
            {
				require PATH_CORE.'core.email'.EXT;
            }
                 
            $email = new EEmail;
            $email->wordwrap = true;
            $email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));	
            $email->to($data['email']); 
            $email->subject($email_tit);	
            $email->message($REGX->entities_to_ascii($email_msg));		
            $email->Send();
            
            $message = $LANG->line('mbr_membership_instructions_email');		
        }
        elseif ($PREFS->ini('req_mbr_activation') == 'manual')
        {
			$message = $LANG->line('mbr_admin_will_activate');
        }	
		else
		{
			/** ----------------------------------------
			/**  Log user in
			/** ----------------------------------------*/
				
			$expire = 60*60*24*182;
					
			$FNS->set_cookie($SESS->c_expire , time()+$expire, $expire);
			$FNS->set_cookie($SESS->c_uniqueid , $data['unique_id'], $expire);       
			$FNS->set_cookie($SESS->c_password , $data['password'],  $expire);   

			/** ----------------------------------------
			/**  Create a new session
			/** ----------------------------------------*/
			
			if ($PREFS->ini('user_session_type') == 'cs' || $PREFS->ini('user_session_type') == 's')
			{  
				$SESS->sdata['session_id'] = $FNS->random();  
				$SESS->sdata['member_id']  = $member_id;  
				$SESS->sdata['last_activity'] = $LOC->now;
				$SESS->sdata['site_id']	= $PREFS->ini('site_id');
								
				$FNS->set_cookie($SESS->c_session , $SESS->sdata['session_id'], $SESS->session_length);   
				
				$DB->query($DB->insert_string('exp_sessions', $SESS->sdata));          
			}
			
			/** ----------------------------------------
			/**  Update existing session variables
			/** ----------------------------------------*/
			
			$SESS->userdata['username']  = $data['username'];
			$SESS->userdata['member_id'] = $member_id;
		
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
							'anon'			=> 'y',
							'site_id'		=> $PREFS->ini('site_id')
						);
		   
			$DB->query($DB->update_string('exp_online_users', $data, array("ip_address" => $IN->IP, "member_id" => $data['member_id'], "weblog_id" => $data['weblog_id'])));
			
			$message = $LANG->line('mbr_your_are_logged_in');
		}
    	
        
        /** ----------------------------------------
        /**  Build the message
        /** ----------------------------------------*/
		
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
				
			$site_name	= $query->row['board_label'];
			$return		= $query->row['board_forum_url'];
		}
		else
		{
			$site_name = ($PREFS->ini('site_name') == '') ? $LANG->line('back') : stripslashes($PREFS->ini('site_name'));
			$return = $PREFS->ini('site_url');
		}
		
        $data = array(	'title' 	=> $LANG->line('mbr_registration_complete'),
        				'heading'	=> $LANG->line('thank_you'),
        				'content'	=> $LANG->line('mbr_registration_completed')."\n\n".$message,
        				'redirect'	=> '',
        				'link'		=> array($return, $site_name)
        			 );
			
		$OUT->show_message($data);
	}
	/* END */




    /** ----------------------------------------
    /**  Member Self-Activation
    /** ----------------------------------------*/

	function activate_member()
	{
        global $IN, $FNS, $OUT, $DB, $PREFS, $SESS, $REGX, $LANG, $STAT, $EXT;

        /** ----------------------------------------
        /**  Fetch the site name and URL
        /** ----------------------------------------*/
        
		if ($IN->GBL('r') == 'f')
		{
			if ($IN->GBL('board_id') !== FALSE && is_numeric($IN->GBL('board_id')))
			{
				$query	= $DB->query("SELECT board_forum_url, board_id, board_label FROM exp_forum_boards WHERE board_id = '".$DB->escape_str($IN->GBl('board_id'))."'");
			}
			else
			{
				$query	= $DB->query("SELECT board_forum_url, board_id, board_label FROM exp_forum_boards WHERE board_id = '1'");
			}
				
			$site_name	= $query->row['board_label'];
			$return		= $query->row['board_forum_url'];
		}
		else
		{
			$return 	= $FNS->fetch_site_index();
			$site_name 	= ($PREFS->ini('site_name') == '') ? $LANG->line('back') : stripslashes($PREFS->ini('site_name'));		
		}
        
        /** ----------------------------------------
        /**  No ID?  Tisk tisk...
        /** ----------------------------------------*/
                
        $id  = $IN->GBL('id');        
                
        if ($id == FALSE)
        {
                        
			$data = array(	'title' 	=> $LANG->line('mbr_activation'),
							'heading'	=> $LANG->line('error'),
							'content'	=> $LANG->line('invalid_url'),
							'link'		=> array($return, $site_name)
						 );
        
			$OUT->show_message($data);
        }
        
        
        /** ----------------------------------------
        /**  Set the member group
        /** ----------------------------------------*/
        
        $group_id = $PREFS->ini('default_member_group');
        
        // Is there even an account for this particular user?
        $query = $DB->query("SELECT member_id, group_id, email FROM exp_members WHERE authcode = '".$DB->escape_str($id)."'");        
        
        if ($query->num_rows == 0)
        {
			$data = array(	'title' 	=> $LANG->line('mbr_activation'),
							'heading'	=> $LANG->line('error'),
							'content'	=> $LANG->line('mbr_problem_activating'),
							'link'		=> array($return, $site_name)
						 );
        
			$OUT->show_message($data);        
        }
        
		$member_id = $query->row['member_id'];
		
        if ($IN->GBL('mailinglist') !== FALSE && is_numeric($IN->GBL('mailinglist')))
        {
        	$expire = time() - (60*60*48);
        
			$DB->query("DELETE FROM exp_mailing_list_queue WHERE date < '$expire' ");
        
        	$results = $DB->query("SELECT authcode
        						   FROM exp_mailing_list_queue
        						   WHERE email = '".$DB->escape_str($query->row['email'])."'
        						   AND list_id = '".$DB->escape_str($IN->GBL('mailinglist'))."'");
        						 
        	$DB->query("INSERT INTO exp_mailing_list (user_id, list_id, authcode, email) 
        				VALUES ('', '".$DB->escape_str($IN->GBL('mailinglist'))."', '".$DB->escape_str($results->row['authcode'])."', '".$DB->escape_str($query->row['email'])."')");	
        				
			$DB->query("DELETE FROM exp_mailing_list_queue WHERE authcode = '".$DB->escape_str($results->row['authcode'])."'");
        }
        
        // If the member group hasn't been switched we'll do it.
        
		if ($query->row['group_id'] != $group_id)
		{
			$DB->query("UPDATE exp_members SET group_id = '".$DB->escape_str($group_id)."' WHERE authcode = '".$DB->escape_str($id)."'");        
		}
        
        $DB->query("UPDATE exp_members SET authcode = '' WHERE authcode = '$id'");
        
		// -------------------------------------------
        // 'member_register_validate_members' hook.
        //  - Additional processing when member(s) are self validated
        //  - Added 1.5.2, 2006-12-28
        //  - $member_id added 1.6.1
		//
        	$edata = $EXT->call_extension('member_register_validate_members', $member_id);
        	if ($EXT->end_script === TRUE) return;
        //
        // -------------------------------------------
        
       // Upate Stats
       
		$STAT->update_member_stats();

        /** ----------------------------------------
        /**  Show success message
        /** ----------------------------------------*/
                
		$data = array(	'title' 	=> $LANG->line('mbr_activation'),
						'heading'	=> $LANG->line('thank_you'),
						'content'	=> $LANG->line('mbr_activation_success')."\n\n".$LANG->line('mbr_may_now_log_in'),
						'link'		=> array($return, $site_name)
					 );
										
		$OUT->show_message($data);
	}
	/* END */



	
}
// END CLASS
?>