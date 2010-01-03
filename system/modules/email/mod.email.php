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
 File: mod.email.php
-----------------------------------------------------
 Purpose: Email class
=====================================================
*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Email {

	var $email_time_interval = '20'; // In seconds
	var $email_max_emails = '50'; // Total recipients, not emails
	
	var $use_captchas = 'n';
	
	/** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/
	
	function Email()
	{
		global $PREFS, $SESS;
		
		if ($PREFS->ini('email_module_captchas') === FALSE OR $PREFS->ini('email_module_captchas') == 'n')
		{
			$this->use_captchas = 'n';            
		}
		elseif ($PREFS->ini('email_module_captchas') == 'y') 
		{            	
			$this->use_captchas = ($PREFS->ini('captcha_require_members') == 'y'  || 
								  ($PREFS->ini('captcha_require_members') == 'n' AND $SESS->userdata('member_id') == 0)) ? 'y' : 'n';
		}
	}
	/* END */

    /** ----------------------------------------
    /**  Contact Form
    /** ----------------------------------------*/

	function contact_form()
	{
		global $IN, $TMPL, $REGX, $FNS, $PREFS, $SESS, $LOC, $DB;
		
        $tagdata = $TMPL->tagdata;

        /** ----------------------------------------
        /**  Recipient Email Checking
        /** ----------------------------------------*/
        	
        $recipients			= ( ! $TMPL->fetch_param('recipients'))  ? '' : $TMPL->fetch_param('recipients');
		$user_recipients	= ( ! $TMPL->fetch_param('user_recipients'))  ? 'false' : $TMPL->fetch_param('user_recipients');
		$charset			= ( ! $TMPL->fetch_param('charset'))  ? '' : $TMPL->fetch_param('charset');
		$weblog				= ( ! $TMPL->fetch_param('weblog'))  ? '' : $TMPL->fetch_param('weblog');
		
		// No email left behind act
		if ($user_recipients == 'false' && $recipients == '')
		{
			$recipients = $PREFS->ini('webmaster_email');
		}
        
        // Clean and check recipient emails, if any
        if ($recipients != '')
        {
        	$array = $this->validate_recipients($recipients);
		
			// Put together into string again
			$recipients = implode(',',$array['approved']);
		}
		
		/** ----------------------------------------
		/**  Conditionals
		/** ----------------------------------------*/
		
		$cond = array();
		$cond['logged_in']			= ($SESS->userdata('member_id') == 0) ? 'FALSE' : 'TRUE';
		$cond['logged_out']			= ($SESS->userdata('member_id') != 0) ? 'FALSE' : 'TRUE';
		$cond['captcha']			= ($this->use_captchas == 'y') ? 'TRUE' : 'FALSE';
		
		$tagdata = $FNS->prep_conditionals($tagdata, $cond);
            
		/** ----------------------------------------
		/**  Parse "single" variables
		/** ----------------------------------------*/

		foreach ($TMPL->var_single as $key => $val)
		{
			/** ----------------------------------------
			/**  parse {member_name}
			/** ----------------------------------------*/
            
   			if ($key == 'member_name')
   			{
   				$name = ($SESS->userdata['screen_name'] != '') ? $SESS->userdata['screen_name'] : $SESS->userdata['username'];
   				$tagdata = $TMPL->swap_var_single($key, $REGX->form_prep($name), $tagdata);
   			}
   			
   			/** ----------------------------------------
   			/**  parse {member_email}
   			/** ----------------------------------------*/
   			
   			if ($key == 'member_email')
   			{
   				$email = ($SESS->userdata['email'] == '') ? '' : $SESS->userdata['email'];
   				$tagdata = $TMPL->swap_var_single($key, $REGX->form_prep($email), $tagdata);
   			}
   			
   			/** ----------------------------------------
   			/**  parse {current_time}
   			/** ----------------------------------------*/
   			
   			if (strncmp('current_time', $key, 12) == 0)
   			{
   				$now = $LOC->set_localized_time();
   				$tagdata = $TMPL->swap_var_single($key, $LOC->decode_date($val,$now), $tagdata);
   			}
   			
   			if (($key == 'author_email' || $key == 'author_name') && !isset($$key))
   			{
   				if ($IN->QSTR != '')
   				{
			        $entry_id = $IN->QSTR;
			        
   					$sql = "SELECT exp_members.username, exp_members.email, exp_members.screen_name
                      		FROM exp_weblog_titles, exp_members ";
                    
                    if($weblog != '')
                    {
                    	$sql .= "LEFT JOIN exp_weblogs ON exp_weblogs.weblog_id = exp_weblog_titles.weblog_id ";
                    }
                      		
                    $sql .= "WHERE exp_members.member_id = exp_weblog_titles.author_id  ";
                    
                    if ($weblog != '')
                    {
                     	$sql .= $FNS->sql_andor_string($weblog, 'exp_weblogs.blog_name')." ";
                    } 	
                      
					if ( ! is_numeric($entry_id))
					{
						$sql .= "AND exp_weblog_titles.url_title = '".$entry_id."' ";
					}
					else
					{
						$sql .= "AND exp_weblog_titles.entry_id = '$entry_id' ";
					}
					
					$query = $DB->query($sql);
					
					if ($query->num_rows == 0)
					{ 
						$author_name = '';
					}
					else
					{
						$author_name = ($query->row['screen_name'] != '') ? $query->row['screen_name'] : $query->row['username'];
					}
						
					$author_email = ($query->num_rows == 0) ? '' : $query->row['email'];
				}
				else
				{
					$author_email = '';
					$author_name = '';
				}
				
				// Do them both now and save ourselves a query
				$tagdata = $TMPL->swap_var_single('author_email', $author_email, $tagdata);
   				$tagdata = $TMPL->swap_var_single('author_name', $author_name, $tagdata);				
   			}		
   			
   			// Clear out any unused variables.
   			// This interferes with global variables so I think we should not use it.
   			// $tagdata = $TMPL->swap_var_single($key, '', $tagdata);
   		}
   		
   		/** ----------------------------------------
   		/**  Create form
   		/** ----------------------------------------*/
 		
 		if ($TMPL->fetch_param('name') !== FALSE && 
			preg_match("#^[a-zA-Z0-9_\-]+$#i", $TMPL->fetch_param('name'), $match))
		{
			$data['name'] = $TMPL->fetch_param('name');
		}
		
		if ( function_exists('mcrypt_encrypt') )
		{
			$init_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

			$recipients = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($DB->username.$DB->password), $recipients, MCRYPT_MODE_ECB, $init_vect);
		}
		else
		{
			$recipients = $recipients.md5($DB->username.$DB->password.$recipients);
		}
		
		$data['id']				= 'contact_form';
   		$data['hidden_fields']	= array(
										'ACT'      			=> $FNS->fetch_action_id('Email', 'send_email'),
										'RET'      			=> ( ! $TMPL->fetch_param('return'))  ? '' : $TMPL->fetch_param('return'),
										'URI'      			=> ($IN->URI == '') ? 'index' : $IN->URI,
										'recipients' 		=> base64_encode($recipients),
										'user_recipients' 	=> ($user_recipients == 'true') ? md5($DB->username.$DB->password.'y') : md5($DB->username.$DB->password.'n'),
										'charset'			=> $charset,
										'redirect'			=> ( ! $TMPL->fetch_param('redirect'))  ? '' : $TMPL->fetch_param('redirect'),
										'replyto'			=> ( ! $TMPL->fetch_param('replyto'))  ? '' : $TMPL->fetch_param('replyto')
										);            
                             
		$res  = $FNS->form_declaration($data);
		$res .= stripslashes($tagdata);
		$res .= "</form>";
		return $res;
	}
    /* END */
    
    
    /** ----------------------------------------
    /**  Tell A Friend Form
    /** ----------------------------------------*/
    // {exp:email:tell_a_friend charset="utf-8" allow_html='n'}
    // {exp:email:tell_a_friend charset="utf-8" allow_html='<p>,<a>' recipients='sales@expressionengine.com'}
	// {member_email}, {member_name}, {current_time format="%Y %d %m"}
	
	function tell_a_friend()
	{
		global $IN, $TMPL, $REGX, $FNS, $PREFS, $SESS, $LOC, $DB, $EXT;
		
		if ($IN->QSTR == '')
        {
            return false;
        }
        
		/** ----------------------------------------
        /**  Recipient Email Checking
        /** ----------------------------------------*/
		
		$user_recipients = true;  // By default
        	
        $recipients	= ( ! $TMPL->fetch_param('recipients'))	? ''  : $TMPL->fetch_param('recipients');
		$charset	= ( ! $TMPL->fetch_param('charset'))	? ''  : $TMPL->fetch_param('charset');
		$allow_html	= ( ! $TMPL->fetch_param('allow_html'))	? 'n' : $TMPL->fetch_param('allow_html');
		
		if ( ! $TMPL->fetch_param('status'))
		{
			$TMPL->tagparams['status'] = 'open';
		}
		
        // Clean and check recipient emails, if any
        if ($recipients != '')
        {
        	$array = $this->validate_recipients($recipients);
		
			// Put together into string again
			$recipients = implode(',',$array['approved']);
		}	
        
		/** --------------------------------------
		/**  Parse page number
		/** --------------------------------------*/
		
		// We need to strip the page number from the URL for two reasons:
		// 1. So we can create pagination links
		// 2. So it won't confuse the query with an improper proper ID
		
        $qstring = $IN->QSTR;
		
		if (preg_match("#/P(\d+)#", $qstring, $match))
		{
			$current_page = $match['1'];	
			
			$qstring = $FNS->remove_double_slashes(str_replace($match['0'], '', $qstring));
		}
		
		/** --------------------------------------
		/**  Remove "N" 
		/** --------------------------------------*/
		
		// The recent comments feature uses "N" as the URL indicator
		// It needs to be removed if presenst

		if (preg_match("#/N(\d+)#", $qstring, $match))
		{			
			$qstring = $FNS->remove_double_slashes(str_replace($match['0'], '', $qstring));
		}
		
		
		/* -------------------------------------
		/*  'email_module_tellafriend_override' hook.
		/*  - Allow use of Tell-A-Friend for things besides weblog entries
		/*  - Added EE 1.5.1
		*/  
			if ($EXT->active_hook('email_module_tellafriend_override') === TRUE)
			{
				$tagdata = $EXT->call_extension('email_module_tellafriend_override', $qstring, $this);
				if ($EXT->end_script === TRUE) return $tagdata;
			}

			/** --------------------------------------
			/**  Else Do the Default Weblog Processing
			/** -------------------------------------*/
		
			else
			{
				$entry_id = trim($qstring);
				 
				// If there is a slash in the entry ID we'll kill everything after it.
				
				$entry_id = preg_replace("#/.+#", "", $entry_id);
				 
				/** ----------------------------------------
				/**  Do we have a vaild weblog and ID number?
				/** ----------------------------------------*/
				
				$timestamp	= ($TMPL->cache_timestamp != '') ? $LOC->set_gmt($TMPL->cache_timestamp) : $LOC->now;
				$weblog		= ( ! $TMPL->fetch_param('weblog')) ? '' : $TMPL->fetch_param('weblog');
						
				$sql = "SELECT entry_id FROM exp_weblog_titles, exp_weblogs 
						WHERE exp_weblog_titles.weblog_id = exp_weblogs.weblog_id 
						AND (expiration_date = 0 || expiration_date > ".$timestamp.") 
						AND status != 'closed' AND ";
				
				$sql .= ( ! is_numeric($entry_id)) ? " url_title = '".$entry_id."' " : " entry_id = '$entry_id' ";
				
				if (USER_BLOG === FALSE) 
				{
					if ($weblog != '')
					{
						$sql .= $FNS->sql_andor_string($weblog, 'exp_weblogs.blog_name')." ";
					} 
					
					$sql .= " AND exp_weblogs.is_user_blog = 'n' ";			
				}
				else
				{
					$sql .= " AND exp_weblogs.weblog_id = '".UB_BLOG_ID."' ";		
				}
						
				$query = $DB->query($sql);
				
				// Bad ID?  See ya!
				
				if ($query->num_rows == 0)
				{
					return false;
				}
									
				/** ----------------------------------------
				/**  Fetch the weblog entry
				/** ----------------------------------------*/
				
				if ( ! class_exists('Weblog'))
				{
					require PATH_MOD.'/weblog/mod.weblog'.EXT;
				}
		
				$weblog = new Weblog;        
				
				$weblog->fetch_custom_weblog_fields();
				$weblog->fetch_custom_member_fields();
				$weblog->build_sql_query();
				
				if ($weblog->sql == '')
				{
					return false;
				}
				
				$weblog->query = $DB->query($weblog->sql);
				
				if ($weblog->query->num_rows == 0)
				{
					return false;
				}     
				
				if ( ! class_exists('Typography'))
				{
					require PATH_CORE.'core.typography'.EXT;
				}
				
				$weblog->TYPE = new Typography;
				$weblog->TYPE->encode_email = FALSE;
				$weblog->TYPE->convert_curly = FALSE;
				
				$TMPL->tagparams['rdf'] = 'off'; // Turn off RDF code
				
				$weblog->fetch_categories();
				$weblog->parse_weblog_entries();
				$tagdata = $weblog->return_data;
			
			}
		/*
		/* -------------------------------------*/
        
		/** ----------------------------------------
        /**  Parse conditionals
        /** ----------------------------------------*/
        
        $cond = array();
		$cond['captcha']			= ($this->use_captchas == 'y') ? 'TRUE' : 'FALSE';
		
		$tagdata = $FNS->prep_conditionals($tagdata, $cond);
		
   		/** ----------------------------------------
   		/**  Parse tell-a-friend variables
   		/** ----------------------------------------*/
   		
   		// {member_name}
                
		$tagdata = $TMPL->swap_var_single('member_name', $SESS->userdata['screen_name'], $tagdata);
		
   		// {member_email}
           
		$tagdata = $TMPL->swap_var_single('member_email', $SESS->userdata['email'], $tagdata);
   		           		
   		/** ----------------------------------------
   		/**  A little work on the form field's values
   		/** ----------------------------------------*/
   		
   		// Match values in input fields
   		preg_match_all("/<input(.*?)value=\"(.*?)\"/", $tagdata, $matches);
   		if(count($matches) > 0 && $allow_html != 'y')
   		{
   		     foreach($matches['2'] as $value)
   		     {
   		     	if ($allow_html == 'n')
   		     	{
   		     		$new = strip_tags($value);
   		     	}
   		     	else
   		     	{
   		     	    $new = strip_tags($value,$allow_html);
   		     	}
   		     	
   		     	$tagdata = str_replace($value,$new, $tagdata);
   		     }
   		}
   		
   		// Remove line breaks
   		$LB = 'snookums9loves4wookie';
   		$tagdata = preg_replace("/(\r\n)|(\r)|(\n)/", $LB, $tagdata);
   		
   		// Temporary switch back to slashes
   		$tagdata = str_replace(SLASH,'/',$tagdata);
   		
   		// Match textarea content
   		preg_match_all("/<textarea(.*?)>(.*?)<\/textarea>/", $tagdata, $matches);
   		if (count($matches) > 0 && $allow_html != 'y')
   		{
   			foreach($matches['2'] as $value)
   			{
   			    if ($allow_html == 'n')
   		     	{
   		     		$new = strip_tags($value);
   		     	}
   		     	else
   		     	{
   		     	    $new = strip_tags($value, $allow_html);
   		     	}
   		     	
   		     	$tagdata = str_replace($value, $new, $tagdata);   			     
   			}
   		}
   		
   		// Change it all back, yo.
   		$tagdata = str_replace('/', SLASH, $tagdata);
   		$tagdata = str_replace($LB, "\n", $tagdata);
   		
   		
   		/** ----------------------------------------
   		/**  Create form
   		/** ----------------------------------------*/
   		
   		if ($TMPL->fetch_param('name') !== FALSE && 
			preg_match("#^[a-zA-Z0-9_\-]+$#i", $TMPL->fetch_param('name'), $match))
		{
			$data['name'] = $TMPL->fetch_param('name');
		}
		
		if ( function_exists('mcrypt_encrypt') )
		{
			$init_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

			$recipients = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($DB->username.$DB->password), $recipients, MCRYPT_MODE_ECB, $init_vect);
		}
		else
		{
			$recipients = $recipients.md5($DB->username.$DB->password.$recipients);
		}

		$data['id'] = 'tellafriend_form';
   		$data['hidden_fields'] = array(
										'ACT'      			=> $FNS->fetch_action_id('Email', 'send_email'),
										'RET'      			=> ( ! $TMPL->fetch_param('return'))  ? '' : $TMPL->fetch_param('return'),
										'URI'      			=> ($IN->URI == '') ? 'index' : $IN->URI,
										'recipients' 		=> base64_encode($recipients),
										'user_recipients' 	=> ($user_recipients == 'true') ? md5($DB->username.$DB->password.'y') : md5($DB->username.$DB->password.'n'),
										'charset'			=> $charset,
										'allow_html'		=> $allow_html,
										'redirect'			=> ( ! $TMPL->fetch_param('redirect'))  ? '' : $TMPL->fetch_param('redirect'),
										'replyto'			=> ( ! $TMPL->fetch_param('replyto'))  ? '' : $TMPL->fetch_param('replyto')
										);            
                             
		$res  = $FNS->form_declaration($data);
		$res .= stripslashes($tagdata);
		$res .= "</form>";
		return $res;
	}
    /* END */



    /** ----------------------------------------
    /**  Send Email
    /** ----------------------------------------*/

    function send_email()
    {
        global $EXT, $IN, $SESS, $PREFS, $DB, $FNS, $OUT, $LANG, $REGX, $LOC;
        
        $error = array();
        
        /** ----------------------------------------
        /**  Blacklist/Whitelist Check
        /** ----------------------------------------*/
        
        if ($IN->blacklisted == 'y' && $IN->whitelisted == 'n')
        {
        	return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
        }
        
        /** ----------------------------------------
		/**  Is the nation of the user banend?
		/** ----------------------------------------*/
		$SESS->nation_ban_check();			
    	  	
    	/** ----------------------------------------
        /**  Check and Set
        /** ----------------------------------------*/
    
        $default = array('subject', 'message', 'from', 'user_recipients', 'to', 'recipients', 'name', 'required');
        
        foreach ($default as $val)
        {
			if ( ! isset($_POST[$val]))
			{
				$_POST[$val] = '';
			}
			else
			{
				if (is_array($_POST[$val]) && ($val == 'message' || $val == 'required'))
				{
					$temp = '';
					foreach($_POST[$val] as $post_value)
					{
						$temp .= $IN->clean_input_data($post_value)."\n";
					}
					
					$_POST[$val] = $temp;
				}
			    
			    if ($val == 'recipients')
			    {
			    	if ( function_exists('mcrypt_encrypt') )
					{
						$init_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
						$init_vect = mcrypt_create_iv($init_size, MCRYPT_RAND);

						$decoded_recipients = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($DB->username.$DB->password), base64_decode($_POST[$val]), MCRYPT_MODE_ECB, $init_vect), "\0");
					}
					else
					{
						$raw = base64_decode($_POST[$val]);

						$hash = substr($raw, -32);
						$decoded_recipients = substr($raw, 0, -32);

						if ($hash != md5($DB->username.$DB->password.$decoded_recipients))
						{
							$decoded_recipients = '';
						}
					}

			    	$_POST[$val] = $decoded_recipients;
			    }
			    
			    $_POST[$val] = $REGX->xss_clean(trim(stripslashes($_POST[$val])));
			}
        }
		
        /** ----------------------------------------
        /**  Clean incoming
        /** ----------------------------------------*/
        
        $clean = array('subject', 'from', 'user_recipients', 'to', 'recipients', 'name');
        
        foreach ($clean as $val)
        {
			$_POST[$val] = strip_tags($_POST[$val]);
        }
        
        /** ----------------------------------------
        /**  Fetch the email module language pack
        /** ----------------------------------------*/
        
        $LANG->fetch_language_file('email');
        
        
        /** ----------------------------------------
        /**  Basic Security Check
        /** ----------------------------------------*/
    	
    	if ($SESS->userdata['ip_address'] == '' || $SESS->userdata['user_agent'] == '')
    	{        	
            return $OUT->show_user_error('general', array($LANG->line('em_unauthorized_request')));    		
    	}
        
        
        /** ----------------------------------------
        /**  Return Variables
        /** ----------------------------------------*/
        
        $x = explode('|',str_replace('&#47;','/',$_POST['RET']));
        unset($_POST['RET']);
        
        if (is_numeric($x['0']))
        {
        	$return_link = $FNS->form_backtrack($x['0']);
        }
        else
        {
        	$return_link = ($x['0'] == '' OR !stristr($x['0'],'http://')) ? $FNS->form_backtrack(2) : $x['0'];
        }

        $site_name = ($PREFS->ini('site_name') == '') ? $LANG->line('back') : stripslashes($PREFS->ini('site_name'));
        
        $return_name = ( ! isset($x['1']) OR $x['1'] == '') ? $site_name : $x['1'];
        
        
        /** ----------------------------------------
        /**  ERROR Checking
        /** ----------------------------------------*/
                
        // If the message is empty, bounce them back
        
        if ($_POST['message'] == '')
        {
            return $OUT->show_user_error('general', array($LANG->line('message_required')));
        }
        
        // If the from field is empty, error
        if ($_POST['from'] == '' || !$REGX->valid_email($_POST['from']))
        {        	
            return $OUT->show_user_error('general', array($LANG->line('em_sender_required')));
        }
        
        // If no recipients, bounce them back
        
        if ($_POST['recipients'] == '' && $_POST['to'] == '')
        {            
            return $OUT->show_user_error('general', array($LANG->line('em_no_valid_recipients')));
        }
        
                
        /** ----------------------------------------
        /**  Is the user banned?
        /** ----------------------------------------*/
                
        if ($SESS->userdata['is_banned'] == TRUE)
        {            
            return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
        }
        
        
        /** ----------------------------------------
        /**  Check Form Hash
        /** ----------------------------------------*/
        
        if ($PREFS->ini('secure_forms') == 'y')
        {
            $query = $DB->query("SELECT COUNT(*) AS count FROM exp_security_hashes WHERE hash='".$DB->escape_str($_POST['XID'])."' AND ip_address = '".$IN->IP."' AND date > UNIX_TIMESTAMP()-7200");
        
            if ($query->row['count'] == 0)
            {
                return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
            }
        }    
        
        /** ----------------------------
        /**  Check Tracking Class
        /** ----------------------------*/
		
		$day_ago = $LOC->now - 60*60*24;
		$query = $DB->query("DELETE FROM exp_email_tracker WHERE email_date < '{$day_ago}'");
		
		if ($SESS->userdata['username'] === false OR $SESS->userdata['username'] == '')
		{
			$query = $DB->query("SELECT * 
								FROM exp_email_tracker 
								WHERE sender_ip = '".$IN->IP."'
								ORDER BY email_date DESC");		
		}
		else
		{
			$query = $DB->query("SELECT * 
								FROM exp_email_tracker 
								WHERE sender_username = '".$DB->escape_str($SESS->userdata['username'])."'
								OR sender_ip = '".$IN->IP."'
								ORDER BY email_date DESC");
		}
		
		if ($query->num_rows > 0)
		{
			// Max Emails - Quick check
			if ($query->num_rows >= $this->email_max_emails)
			{
				return $OUT->show_user_error('general', array($LANG->line('em_limit_exceeded')));  
			}
			
			// Max Emails - Indepth check
			$total_sent = 0;
			foreach($query->result as $row)
			{
				$total_sent = $total_sent + $row['number_recipients'];
			}
			
			if ($total_sent >= $this->email_max_emails)
			{
				return $OUT->show_user_error('general', array($LANG->line('em_limit_exceeded')));
			}
			
			// Interval check
			if ($query->row['email_date'] > ($LOC->now - $this->email_time_interval))
			{
				$error[] = str_replace("%s", $this->email_time_interval, $LANG->line('em_interval_warning'));
				return $OUT->show_user_error('general', $error);
			}
		}
        
        
        /** ----------------------------------------
        /**  Review Recipients
        /** ----------------------------------------*/
        
		$_POST['user_recipients'] = ($_POST['user_recipients'] == md5($DB->username.$DB->password.'y')) ? 'y' : 'n';

        if ($_POST['user_recipients'] == 'y' && trim($_POST['to']) != '')
        {
        	$array = $this->validate_recipients($_POST['to']);
		
			$error = array_merge($error, $array['error']);
			$approved_tos = $array['approved'];
		}
		else
		{
			$approved_tos = array();
		}
		
		if (trim($_POST['recipients']) != '')
        {
        	$array = $this->validate_recipients($_POST['recipients']);
			$approved_recipients = $array['approved'];
		}
		else
		{
			$approved_recipients = array();
		}
		
		/** ----------------------------------------------------
		/**  If we have no valid emails to send, back they go.
		/** ----------------------------------------------------*/
		
		if ($_POST['user_recipients'] == 'y' && sizeof($approved_tos) == 0)
        {
            $error[] = $LANG->line('em_no_valid_recipients');
        }
        elseif ( sizeof($approved_recipients) == 0 && sizeof($approved_tos) == 0)
        {
            $error[] = $LANG->line('em_no_valid_recipients');
        }
        
        
		/** -------------------------------------
		/**  Is from email banned?
		/** -------------------------------------*/
		
		if ($SESS->ban_check('email', $_POST['from']))
		{
			$error[] = $LANG->line('em_banned_from_email');
		}	
		
        /** ----------------------------------------
        /**  Do we have errors to display?
        /** ----------------------------------------*/
                
        if (count($error) > 0)
        {
           return $OUT->show_user_error('submission', $error);
        }
        
		/** ----------------------------------------
        /**  Check Captcha
        /** ----------------------------------------*/
        
        if ($this->use_captchas == 'y')
		{
			if ( ! isset($_POST['captcha']) || $_POST['captcha'] == '')
			{
				return $OUT->show_user_error('general', array($LANG->line('captcha_required')));
			}
			
			$query = $DB->query("SELECT COUNT(*) AS count FROM exp_captcha 
								 WHERE word='".$DB->escape_str($_POST['captcha'])."' 
								 AND ip_address = '".$IN->IP."' 
								 AND date > UNIX_TIMESTAMP()-7200");
		
            if ($query->row['count'] == 0)
            {
				return $OUT->show_user_error('submission', array($LANG->line('captcha_incorrect')));
			}
		
            $DB->query("DELETE FROM exp_captcha 
            			WHERE (word='".$DB->escape_str($_POST['captcha'])."' 
            			AND ip_address = '".$IN->IP."') 
            			OR date < UNIX_TIMESTAMP()-7200");
		}
        
        
        
        
        /** ----------------------------------------
        /**  Censored Word Checking
        /** ----------------------------------------*/
        
        if ( ! class_exists('Typography'))
        {
            require PATH_CORE.'core.typography'.EXT;
        }
        
        $TYPE = new Typography;
        
        $subject = $REGX->entities_to_ascii($_POST['subject']);
        $subject = $TYPE->filter_censored_words($subject);
        
        $message = ($_POST['required'] != '') ? $_POST['required']."\n".$_POST['message'] : $_POST['message'];
        $message = $REGX->xss_clean($message);
        
        if (isset($_POST['allow_html']) && $_POST['allow_html'] == 'y' && strlen(strip_tags($message)) != strlen($message))
        {
			$mail_type = 'html';
        }
        else
        {
			$mail_type = 'plain';
        }
        
        $message = $REGX->entities_to_ascii($message);
        $message = $TYPE->filter_censored_words($message);
        
        /** ----------------------------
        /**  Send email
        /** ----------------------------*/
        
        if ( ! class_exists('EEmail'))
        {
        	require PATH_CORE.'core.email'.EXT;
        }
        
        $email = new EEmail;
        $email->wordwrap = true;
        $email->mailtype = $mail_type;
		$email->priority = '3';
		
		if (isset($_POST['charset']) && $_POST['charset'] != '')
		{
			$email->charset = $_POST['charset'];
		}
		
		if ( sizeof($approved_recipients) == 0 && sizeof($approved_tos) > 0) // No Hidden Recipients
        {
        	foreach ($approved_tos as $val)
        	{
        		$email->initialize();
        		$email->to($val);
        		
        		if (isset($_POST['replyto']) && $_POST['replyto'] == 'y')
        		{
        			$email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));
        			$email->reply_to($_POST['from'], $_POST['name']);
        		}
        		else
        		{
        			$email->from($_POST['from'],$_POST['name']);
        		}
        		
        		$email->subject($subject);
       			$email->message($message);
        		$email->Send();
        	}
        }
        elseif ( sizeof($approved_recipients) > 0 && sizeof($approved_tos) == 0) // Hidden Recipients Only
        {
        	foreach ($approved_recipients as $val)
        	{
        		$email->initialize();
        		$email->to($val);
        		
        		if (isset($_POST['replyto']) && $_POST['replyto'] == 'y')
        		{
        			$email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));
        			$email->reply_to($_POST['from'], $_POST['name']);
        		}
        		else
        		{
        			$email->from($_POST['from'],$_POST['name']);
        		}
        		
        		$email->subject($subject);
       			$email->message($message);
        		$email->Send();
        	}
        }
        else // Combination of Hidden and Regular Recipients, BCC hidden on every regular recipient email
        {
        	foreach ($approved_tos as $val)
        	{
        		$email->initialize();
        		$email->to($val);
        		$email->bcc(implode(',', $approved_recipients));
        		
        		if (isset($_POST['replyto']) && $_POST['replyto'] == 'y')
        		{
        			$email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));
        			$email->reply_to($_POST['from'], $_POST['name']);
        		}
        		else
        		{
        			$email->from($_POST['from'], $_POST['name']);
        		}
        		
        		$email->subject($subject);
       			$email->message($message);
        		$email->Send();
        	}
        }
        
        
        /** ----------------------------
        /**  Store in tracking class
        /** ----------------------------*/
        
        $data = array(	'email_id'			=> '', 
        				'email_date'		=> $LOC->now, 
        				'sender_ip'			=> $IN->IP,
        				'sender_email'		=> $_POST['from'],
        				'sender_username'	=> $SESS->userdata['username'],
        				'number_recipients'	=> sizeof($approved_tos) + sizeof($approved_recipients)
					);
         
        $DB->query($DB->insert_string('exp_email_tracker', $data));
        
        /** -------------------------------------------
        /**  Delete spam hashes
        /** -------------------------------------------*/

        if (isset($_POST['XID']))
        {
			$DB->query("DELETE FROM exp_security_hashes WHERE (hash='".$DB->escape_str($_POST['XID'])."' AND ip_address = '".$IN->IP."') OR date < UNIX_TIMESTAMP()-7200");
        }        

		/* -------------------------------------
		/*  'email_module_send_email_end' hook.
		/*  - After emails are sent, do some additional processing
		/*  - Added EE 1.5.1
		*/  
			if ($EXT->active_hook('email_module_send_email_end') === TRUE)
			{
				$edata = $EXT->call_extension('email_module_send_email_end', $subject, $message, $approved_tos, $approved_recipients);
				if ($EXT->end_script === TRUE) return;
			}
		/*
		/* -------------------------------------*/
		
        /** -------------------------------------------
        /**  Thank you message
        /** -------------------------------------------*/
                
        $data = array(	'title' 	=> $LANG->line('email_module_name'),
        				'heading'	=> $LANG->line('thank_you'),
        				'content'	=> $LANG->line('em_email_sent'),
        				'redirect'	=> $return_link,
        				'link'		=> array($return_link, $return_name)
        			 );
        			 
        if ($IN->GBL('redirect') !== FALSE)
        {
        	if(is_numeric($IN->GBL('redirect')))
        	{
        		$data['rate'] = $IN->GBL('redirect');
        	}
        	elseif($IN->GBL('redirect') == 'none')
        	{
        		$data['redirect'] = '';
        	}
        }
				
		$OUT->show_message($data);
    }
    /* END */
    
    
    /** -----------------------------------
    /**  Validate List of Emails 
    /** -----------------------------------*/
    
    function validate_recipients($str)
    {
    	global $REGX, $SESS, $LANG;
    
    	// Remove white space and replace with comma
		$recipients = preg_replace("/\s*(\S+)\s*/", "\\1,", $str);
        	
        // Remove any existing doubles
        $recipients = str_replace(",,", ",", $recipients);
        	
        // Remove any comma at the end
        if (substr($recipients, -1) == ",")
		{
			$recipients = substr($recipients, 0, -1);
		}
		
		// Break into an array via commas and remove duplicates
		$emails = preg_split('/[,]/', $recipients);
		$emails = array_unique($emails);
			
		// Emails to send email to...
		
		$error = array();
		$approved_emails = array();
		
		foreach ($emails as $email)
		{
			 if (trim($email) == '') continue;
			 			
		     if ($REGX->valid_email($email))
		     {
		          if (!$SESS->ban_check('email', $email))
		          {
		               $approved_emails[] = $email;
		          }
		          else
		          {
		               $error['ban_recp'] = $LANG->line('em_banned_recipient');
		          }
		     }
		     else
		     {
		     	$error['bad_recp'] = $LANG->line('em_invalid_recipient');
		     }
		}
		
		return array('approved' => $approved_emails, 'error' => $error);
    }
    /* END */

}
// END CLASS
?>