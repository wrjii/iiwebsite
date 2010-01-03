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
 File: mcp.mailinglist.php
-----------------------------------------------------
 Purpose: Basic Mailint List class
=====================================================
*/


if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Mailinglist {

	var $email_confirm	= TRUE;  // TRUE/FALSE - whether to send an email confirmation when users sign up
    var $return_data	= '';

    /** -------------------------------------
    /**  Constructor
    /** -------------------------------------*/

    function Mailinglist()
    {
    }
    /* END */
    

    /** ----------------------------------------
    /**  Mailing List Submission Form
    /** ----------------------------------------*/

    function form()
    {
        global $FNS, $TMPL, $DB, $SESS;
        
        $tagdata = $TMPL->tagdata; 
        
        $list = ($TMPL->fetch_param('list') === FALSE) ? '0' : $TMPL->fetch_param('list');
        $name = '';
        
        if ($list !== FALSE)
        {
        	if (preg_match("/full_name/", $tagdata))
        	{
        		$query = $DB->query("SELECT list_title FROM exp_mailing_lists WHERE list_name ='".$DB->escape_str($list)."'");
        		
        		if ($query->num_rows == 1)
        		{
					$name = $query->row['list_title'];
        		}        	
        	}
        }
        
        $tagdata = str_replace(LD.'full_name'.RD, $name, $tagdata);
        
        if ($SESS->userdata('email') != '')
        {
			$tagdata = str_replace(LD.'email'.RD, $SESS->userdata('email'), $tagdata);
        }
        else
        {
			$tagdata = str_replace(LD.'email'.RD, '', $tagdata);
        }
                
        /** ----------------------------------------
        /**  Create form
        /** ----------------------------------------*/
                                               
		if ($TMPL->fetch_param('name') !== FALSE && 
			preg_match("#^[a-zA-Z0-9_\-]+$#i", $TMPL->fetch_param('name'), $match))
		{
			$data['name'] = $TMPL->fetch_param('name');
		}
        
		$data['id']				= 'mailinglist_form';
		$data['hidden_fields']	= array(
										'ACT'	=> $FNS->fetch_action_id('Mailinglist', 'insert_new_email'),
										'RET'	=> $FNS->fetch_current_uri(),
										'list'	=> $list
									  );            
                             
        $res  = $FNS->form_declaration($data);
        
        $res .= $tagdata;
        
        $res .= "</form>"; 
            
        return $res;
    }
    /* END */



    /** ----------------------------------------
    /**  Insert new email
    /** ----------------------------------------*/

    function insert_new_email()
    {
        global $IN, $FNS, $OUT, $DB, $PREFS, $SESS, $REGX, $LANG;
        
        /** ----------------------------------------
        /**  Fetch the mailinglist language pack
        /** ----------------------------------------*/
        
        $LANG->fetch_language_file('mailinglist');
        
        // Is the mailing list turned on?
        
        if ($PREFS->ini('mailinglist_enabled') == 'n')
        {
			return $OUT->show_user_error('general', $LANG->line('mailinglist_disabled'));
        }
        
        /** ----------------------------------------
        /**  Blacklist/Whitelist Check
        /** ----------------------------------------*/
        
        if ($IN->blacklisted == 'y' && $IN->whitelisted == 'n')
        {
        	return $OUT->show_user_error('general', array($LANG->line('not_authorized')));
        }
        
		if ( ! isset($_POST['RET']))
		{
			exit;
		}
        
        /** ----------------------------------------
        /**  Error trapping
        /** ----------------------------------------*/
                
        $errors = array();
        
        $email = $IN->GBL('email');
        $email = trim(strip_tags($email));
		$list = $IN->GBL('list', 'POST');
		$list_id = FALSE;

		if ($email == '')
		{
			$errors[] = $LANG->line('ml_missing_email');
		}		
        
        if ( ! $REGX->valid_email($email))
        {
			$errors[] = $LANG->line('ml_invalid_email');
        }
        
        if (count($errors) == 0)
        {        
			/** ----------------------------------------
			/**  Is the security hash valid?
			/** ----------------------------------------*/
        
			if ($PREFS->ini('secure_forms') == 'y')
			{			
				$query = $DB->query("SELECT COUNT(*) AS count FROM exp_security_hashes WHERE hash='".$DB->escape_str($_POST['XID'])."' AND ip_address = '".$IN->IP."' AND date > UNIX_TIMESTAMP()-7200");
			
				if ($query->row['count'] == 0)
				{
					$FNS->redirect(stripslashes($_POST['RET']));
					exit;			
				}
			}
			
			// Kill duplicate emails from authorization queue.  This prevents an error if a user
			// signs up but never activates their email, then signs up again.
			
			$DB->query("DELETE FROM exp_mailing_list_queue WHERE email = '".$DB->escape_str($email)."'");
			
			/** ----------------------------------------
			/**  Which list is being subscribed to?
			/** ----------------------------------------*/
			
			// If there is no list ID we'll have to figure it out.
			
			if ($list == '0')
			{
				$query = $DB->query("SELECT COUNT(*) AS count FROM exp_mailing_lists WHERE list_id = 1");
			
				if ($query->row['count'] != 1)
				{
					$errors[] = $LANG->line('ml_no_list_id');
				}
				else
				{
					$list_id = 1;
				}
			}
			else
			{
				$query = $DB->query("SELECT list_id FROM exp_mailing_lists WHERE list_name = '".$DB->escape_str($list)."'");
				
				if ($query->num_rows != 1)
				{
					$errors[] = $LANG->line('ml_no_list_id');
				}
				else
				{
					$list_id = $query->row['list_id'];
				}
			}
			
        
			/** ----------------------------------------
			/**  Is the email already in the list?
			/** ----------------------------------------*/

			if ($list_id !== FALSE)
			{
				$query = $DB->query("SELECT count(*) AS count FROM exp_mailing_list WHERE email = '".$DB->escape_str($email)."' AND list_id = '".$DB->escape_str($list_id)."'");
				
				if ($query->row['count'] > 0)
				{
					$errors[] = $LANG->line('ml_email_already_in_list');
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
		/**  Insert email
		/** ----------------------------------------*/
				
		$code = $FNS->random('alpha', 10);
        
        $return = '';
        
		if ($this->email_confirm == FALSE)
		{
			$DB->query("INSERT INTO exp_mailing_list (user_id, list_id, authcode, email, ip_address) 
						VALUES ('', '".$DB->escape_str($list_id)."', '".$code."', '".$DB->escape_str($email)."', '".$DB->escape_str($IN->IP)."')");			
			
			$content  = $LANG->line('ml_email_accepted');
			
			$return = $_POST['RET'];
		}        
        else
        {        	
			$DB->query("INSERT INTO exp_mailing_list_queue (email, list_id, authcode, date) VALUES ('".$DB->escape_str($email)."', '".$DB->escape_str($list_id)."', '".$code."', '".time()."')");			
			
			$this->send_email_confirmation($email, $code, $list_id);

			$content  = $LANG->line('ml_email_confirmation_sent')."\n\n";
			$content .= $LANG->line('ml_click_confirmation_link');
        }
        
		/** ----------------------------------------
		/**  Clear security hash
		/** ----------------------------------------*/
		
		if ($PREFS->ini('secure_forms') == 'y')
		{
			$DB->query("DELETE FROM exp_security_hashes WHERE (hash='".$DB->escape_str($_POST['XID'])."' AND ip_address = '".$IN->IP."') OR date < UNIX_TIMESTAMP()-7200");
		}
		
		
		$site_name = ($PREFS->ini('site_name') == '') ? $LANG->line('back') : stripslashes($PREFS->ini('site_name'));
                
        $data = array(	'title' 	=> $LANG->line('ml_mailinglist'),
        				'heading'	=> $LANG->line('thank_you'),
        				'content'	=> $content,
        				'link'		=> array($_POST['RET'], $site_name)
        			 );
				
		$OUT->show_message($data);
    }
    /* END */



	
	/** ----------------------------------------
	/**  Send confirmation email
	/** ----------------------------------------*/

	function send_email_confirmation($email, $code, $list_id)
	{
		global $DB, $LANG, $PREFS, $FNS;
        
		$query = $DB->query("SELECT list_title FROM exp_mailing_lists WHERE list_id = '".$DB->escape_str($list_id)."'");
		
        $qs = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';        
		$action_id  = $FNS->fetch_action_id('Mailinglist', 'authorize_email');

		$swap = array(
						'activation_url'	=> $FNS->fetch_site_index(0, 0).$qs.'ACT='.$action_id.'&id='.$code,
						'site_name'			=> stripslashes($PREFS->ini('site_name')),
						'site_url'			=> $PREFS->ini('site_url'),
						'mailing_list'		=> $query->row['list_title']
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
		$E->to($email); 
		$E->subject($email_tit);	
		$E->message($email_msg);	
		$E->Send();
	}
	/* END */
	



	/** ------------------------------
	/**  Authorize email submission
	/** ------------------------------*/

	function authorize_email()
	{
        global $IN, $FNS, $OUT, $DB, $PREFS, $SESS, $REGX, $LANG;
        
        /** ----------------------------------------
        /**  Fetch the mailinglist language pack
        /** ----------------------------------------*/
        
        $LANG->fetch_language_file('mailinglist');
   
        // Is the mailing list turned on?
        
        if ($PREFS->ini('mailinglist_enabled') == 'n')
        {
			return $OUT->show_user_error('general', $LANG->line('mailinglist_disabled'));
        }
   
        /** ----------------------------------------
        /**  Fetch the name of the site
        /** ----------------------------------------*/
        
		$site_name = ($PREFS->ini('site_name') == '') ? $LANG->line('back') : stripslashes($PREFS->ini('site_name'));
        
                
        /** ----------------------------------------
        /**  No ID?  Tisk tisk...
        /** ----------------------------------------*/
                
        $id  = $IN->GBL('id');        
                
        if ($id == FALSE)
        {
                        
			$data = array(	'title' 	=> $LANG->line('ml_mailinglist'),
							'heading'	=> $LANG->line('error'),
							'content'	=> $LANG->line('invalid_url'),
							'link'		=> array($FNS->fetch_site_index(), $site_name)
						 );
        
			$OUT->show_message($data);
        }
        
        /** ----------------------------------------
        /**  Fetch email associated with auth-code
        /** ----------------------------------------*/
                        
        $expire = time() - (60*60*48);
        
		$DB->query("DELETE FROM exp_mailing_list_queue WHERE date < '$expire' ");
        
        $query = $DB->query("SELECT email, list_id FROM exp_mailing_list_queue WHERE authcode = '".$DB->escape_str($id)."'");
        
		if ($query->num_rows == 0)
		{
			$data = array(	'title' 	=> $LANG->line('ml_mailinglist'),
							'heading'	=> $LANG->line('error'),
							'content'	=> $LANG->line('ml_expired_date'),
							'link'		=> array($FNS->fetch_site_index(), $site_name)
						 );
		        
			echo  $OUT->show_message($data);
			exit;
		}       
        
        /** ----------------------------------------
        /**  Transfer email to the mailing list
        /** ----------------------------------------*/
        
        $email = $query->row['email'];
        $list_id = $query->row['list_id'];
        
        if ($list_id == 0)
        {
			$query = $DB->query("SELECT COUNT(*) AS count FROM exp_mailing_lists WHERE list_id = 1");
		
			if ($query->row['count'] != 1)
			{				
				return $OUT->show_user_error('general', $LANG->line('ml_no_list_id'));
			}
			else
			{
				$list_id = 1;
			}
        }
        
		$DB->query("INSERT INTO exp_mailing_list (user_id, list_id, authcode, email, ip_address) 
					VALUES ('', '".$DB->escape_str($list_id)."', '$id', '".$DB->escape_str($email)."', '".$DB->escape_str($IN->IP)."')");
					
		$DB->query("DELETE FROM exp_mailing_list_queue WHERE authcode = '".$DB->escape_str($id)."'");


        /** ----------------------------------------
        /**  Is there an admin notification to send?
        /** ----------------------------------------*/

		if ($PREFS->ini('mailinglist_notify') == 'y' AND $PREFS->ini('mailinglist_notify_emails') != '')
		{
			$query = $DB->query("SELECT list_title FROM exp_mailing_lists WHERE list_id = '".$DB->escape_str($list_id)."'");
		
			$swap = array(
							'email'	=> $email,
							'mailing_list' => $query->row['list_title']
						 );
			
			$template = $FNS->fetch_email_template('admin_notify_mailinglist');
			$email_tit = $FNS->var_swap($template['title'], $swap);
			$email_msg = $FNS->var_swap($template['data'], $swap);
                                                
            /** ----------------------------
            /**  Send email
            /** ----------------------------*/

			$notify_address = $REGX->remove_extra_commas($PREFS->ini('mailinglist_notify_emails'));
			
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
					$email->wordwrap = true;
					$email->from($PREFS->ini('webmaster_email'), $PREFS->ini('webmaster_name'));	
					$email->to($addy); 
					$email->reply_to($PREFS->ini('webmaster_email'));
					$email->subject($email_tit);	
					$email->message($REGX->entities_to_ascii($email_msg));		
					$email->Send();
				}
			}
		}

		/** ------------------------------
		/**  Success Message
		/** ------------------------------*/

		$data = array(	'title' 	=> $LANG->line('ml_mailinglist'),
						'heading'	=> $LANG->line('thank_you'),
						'content'	=> $LANG->line('ml_account_confirmed'),
						'link'		=> array($FNS->fetch_site_index(), $site_name)
					 );
										
		$OUT->show_message($data);
	}
	/* END */
	
	

	/** ------------------------------
	/**  Unsubscribe a user
	/** ------------------------------*/

	function unsubscribe()
	{
        global $IN, $FNS, $OUT, $DB, $PREFS, $SESS, $REGX, $LANG;
        
        
        /** ----------------------------------------
        /**  Fetch the mailinglist language pack
        /** ----------------------------------------*/
        
        $LANG->fetch_language_file('mailinglist');
        
        
		$site_name = ($PREFS->ini('site_name') == '') ? $LANG->line('back') : stripslashes($PREFS->ini('site_name'));
                
        /** ----------------------------------------
        /**  No ID?  Tisk tisk...
        /** ----------------------------------------*/
                
        $id  = $IN->GBL('id');        
                
        if ($id == FALSE)
        {			
			$data = array(	'title' 	=> $LANG->line('ml_mailinglist'),
							'heading'	=> $LANG->line('error'),
							'content'	=> $LANG->line('invalid_url'),
							'link'		=> array($FNS->fetch_site_index(), $site_name)
						 );
		        
			$OUT->show_message($data);
        }
        
        /** ----------------------------------------
        /**  Fetch email associated with auth-code
        /** ----------------------------------------*/
                        
        $expire = time() - (60*60*48);
        
		$DB->query("DELETE FROM exp_mailing_list WHERE authcode = '$id' ");
		
		if ($DB->affected_rows == 0)
		{
			$data = array(	'title' 	=> $LANG->line('ml_mailinglist'),
							'heading'	=> $LANG->line('error'),
							'content'	=> $LANG->line('ml_unsubscribe_failed'),
							'link'		=> array($FNS->fetch_site_index(), $site_name)
						 );
		        
			$OUT->show_message($data);
		}

                
		$data = array(	'title' 	=> $LANG->line('ml_mailinglist'),
						'heading'	=> $LANG->line('thank_you'),
						'content'	=> $LANG->line('ml_unsubscribe'),
						'link'		=> array($FNS->fetch_site_index(), $site_name)
					 );
										
		$OUT->show_message($data);
	}
	/* END */
	
}
// END CLASS
?>