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
 File: mod.member_memberlist.php
=====================================================

*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Member_memberlist extends Member {

	var $is_search			= FALSE;
	var $search_keywords	= '';
	var $search_fields		= '';
	var $search_total		= 0;

    /** ----------------------------------
    /**  Member_memberlist Profile Constructor
    /** ----------------------------------*/

	function Member_memberlist()
	{
	}
	/* END */

    /** ----------------------------------
    /**  Member Email Form
    /** ----------------------------------*/

	function email_console()
	{
		global $DB, $IN, $FNS, $LANG, $PREFS, $SESS, $OUT;		

		/** ---------------------------------
		/**  Is the user logged in?
		/** ---------------------------------*/
		
		if ($SESS->userdata('member_id') == 0)
		{
			return $this->profile_login_form($this->_member_path('self'));
		}
		
		/** ---------------------------------
		/**  Is user allowed to send email?
		/** ---------------------------------*/
				
		if ($SESS->userdata['can_email_from_profile'] == 'n')
		{
			return $OUT->show_user_error('general', array($LANG->line('mbr_not_allowed_to_use_email_console')));
		}
		
			
		$query = $DB->query("SELECT screen_name, accept_user_email FROM exp_members WHERE member_id = '{$this->cur_id}'");
		
		if ($query->num_rows == 0)
		{
			return false;
		}
		
		if ($query->row['accept_user_email'] != 'y')
		{							
			return $this->_var_swap($this->_load_element('email_user_message'),
													array(
															'lang:message'	=>	$LANG->line('mbr_email_not_accepted'),
															'css_class'		=>	'highlight'
														)
													);	
		}
		
		$data = array(
						'hidden_fields' => array('MID' => $this->cur_id),
						'action' 		=> $this->_member_path('send_email')
					);
					
		$data['id']		= 'email_console_form';
					
		$this->_set_page_title($LANG->line('email_console'));
		
		return $this->_var_swap($this->_load_element('email_form'),
										 array(
												'form_declaration'	=>	$FNS->form_declaration($data),
												'name'				=>	$query->row['screen_name']
											 )
										);	
	}
	/* END */




    /** ----------------------------------
    /**  Send Member Email
    /** ----------------------------------*/

	function send_email()
	{
		global $DB, $IN, $FNS, $OUT, $LANG, $PREFS, $LOC, $SESS;
			
		if ( ! $member_id = $IN->GBL('MID', 'POST'))
		{
			return false;
		}
		
        /** ----------------------------------------
        /**  Is the user banned?
        /** ----------------------------------------*/
        
        if ($SESS->userdata['is_banned'] == TRUE)
        {
			return false;
        }
		
		/** ---------------------------------
		/**  Is the user logged in?
		/** ---------------------------------*/
		
		if ($SESS->userdata('member_id') == 0)
		{
			return $this->profile_login_form($this->_member_path('email_console/'.$member_id));
		}
		
		/** ---------------------------------
		/**  Are we missing data?
		/** ---------------------------------*/
		
		if ( ! $member_id = $IN->GBL('MID', 'POST'))
		{
			return false;
		}
		
		if ( ! isset($_POST['subject']) || ! isset($_POST['message']))
		{
			return false;
		}
		
		if ($_POST['subject'] == '' OR $_POST['message'] == '')
		{
			return $OUT->show_user_error('submission', array($LANG->line('mbr_missing_fields')));
		}
            
        /** ----------------------------------------
        /**  Check Email Timelock
        /** ----------------------------------------*/
        
        if ($SESS->userdata['group_id'] != 1)
        {
        	$lock = $PREFS->ini('email_console_timelock');
        
			if (is_numeric($lock) AND $lock != 0)
			{
				if (($SESS->userdata['last_email_date'] + ($lock*60)) > $LOC->now)
				{														
					return $this->_var_swap($this->_load_element('email_user_message'),
										array(
												'lang:message'			=>	str_replace("%x", $lock, $LANG->line('mbr_email_timelock_not_expired')),
												'css_class'				=>	'highlight',
												'lang:close_window'		=>	$LANG->line('mbr_close_window')
											)
										);	
				}
			}
        }
               
		/** ---------------------------------
		/**  Do we have a secure hash?
		/** ---------------------------------*/
		
        if ($PREFS->ini('secure_forms') == 'y')
        {
			$query = $DB->query("SELECT COUNT(*) AS count FROM exp_security_hashes WHERE hash='".$DB->escape_str($_POST['XID'])."' AND ip_address = '".$IN->IP."' AND date > UNIX_TIMESTAMP()-7200");
		
			if ($query->row['count'] == 0)
			{
				return false;
			}
			
			$DB->query("DELETE FROM exp_security_hashes WHERE (hash='".$DB->escape_str($_POST['XID'])."' AND ip_address = '".$IN->IP."') OR date < UNIX_TIMESTAMP()-7200");
		}		
				
		/** ---------------------------------
		/**  Does the recipient accept email?
		/** ---------------------------------*/
		
		$query = $DB->query("SELECT email, screen_name, accept_user_email FROM exp_members WHERE member_id = '{$member_id}'");
		
		if ($query->num_rows == 0)
		{
			return false;
		}
		
		if ($query->row['accept_user_email'] != 'y')
		{							
			return $this->_var_swap($this->_load_element('email_user_message'),
									array(
											'lang:message'	=>	$LANG->line('mbr_email_not_accepted'),
											'css_class'		=>	'highlight'
										)
									);	
		}
		
		$message  = stripslashes($_POST['message'])."\n\n";
		$message .= $LANG->line('mbr_email_forwarding')."\n";
		$message .= $PREFS->ini('site_url')."\n"; 
		$message .= $LANG->line('mbr_email_forwarding_cont');

		/** ----------------------------
		/**  Send email
		/** ----------------------------*/
		
		if ( ! class_exists('EEmail'))
		{
			require PATH_CORE.'core.email'.EXT;
		}
			 
		$email = new EEmail;
		$email->wordwrap = true;
		$email->from($SESS->userdata['email']);	
		$email->subject(stripslashes($_POST['subject']));	
		$email->message($message);		
		
		if (isset($_POST['self_copy']))
		{
			/*	If CC'ing the send, they get the email and the recipient is BCC'ed
				Because Rick says his filter blocks emails without a To: field
			*/
			
			$email->to($SESS->userdata['email']);
			$email->bcc($query->row['email']);	
		}
		else
		{
			$email->to($query->row['email']); 
		}
		
		$swap['lang:close_window'] = $LANG->line('mbr_close_window');
		
		if ( ! $email->Send())
		{		
			$swap['lang:message']	= $LANG->line('mbr_email_error');
			$swap['css_class'] 		= 'alert';
		}
		else
		{
			$this->log_email($query->row['email'], $query->row['screen_name'], $_POST['subject'], $_POST['message']);

			$swap['lang:message']	= $LANG->line('mbr_good_email');
			$swap['css_class'] 		= 'success';
			
			$DB->query("UPDATE exp_members SET last_email_date = '{$LOC->now}' WHERE member_id = '".$SESS->userdata('member_id')."'");
			
		}
		
		$this->_set_page_title($LANG->line('email_console'));
		
		return $this->_var_swap($this->_load_element('email_user_message'), $swap);			
	}
	/* END */



	/** ---------------------------------
	/**  Log Email Message
	/** ---------------------------------*/

	function log_email($recipient, $recipient_name, $subject, $message)
	{
		global $IN, $LOC, $DB, $SESS, $REGX, $PREFS;
		
		if ($PREFS->ini('log_email_console_msgs') == 'y')
		{
			$data = array(
							'cache_date'		=> $LOC->now,
							'member_id'			=> $SESS->userdata('member_id'),
							'member_name'		=> $SESS->userdata['screen_name'],
							'ip_address'		=> $IN->IP,
							'recipient'			=> $recipient,
							'recipient_name'	=> $recipient_name,
							'subject'			=> $subject,
							'message'			=> $REGX->xss_clean($message)
						);
									
			$DB->query($DB->insert_string('exp_email_console_cache', $data));
		}      
	}
	/* END */



	/** ----------------------------------
	/**  AIM Console
	/** ----------------------------------*/

	function aim_console()
	{
		global $IN, $DB, $FNS, $LANG, $PREFS;
				
		$query = $DB->query("SELECT aol_im FROM exp_members WHERE member_id = '".$DB->escape_str($this->cur_id)."'");
						
		if ($query->num_rows == 0)
		{
			return;
		}
		
		$this->_set_page_title($LANG->line('mbr_aim_console'));
	
		return $this->_var_swap($this->_load_element('aim_console'),
										array(
												'aol_im'			=>	$query->row['aol_im'],
												'lang:close_window'	=>	$LANG->line('mbr_close_window')
											 )
										);
	}
	/* END */
	
	
	

	/** ----------------------------------
	/**  ICQ Console
	/** ----------------------------------*/
	
	function icq_console()
	{
		global $DB, $IN, $FNS, $LANG, $PREFS, $SESS;
				
		/** ---------------------------------
		/**  Is the user logged in?
		/** ---------------------------------*/
		
		if ($SESS->userdata('member_id') == 0)
		{
			return $this->profile_login_form($this->_member_path('self'));
		}
					
		$query = $DB->query("SELECT screen_name, icq FROM exp_members WHERE member_id = '{$this->cur_id}'");
		
		if ($query->num_rows == 0)
		{ 
			return false;
		}
		
		$data = array(
						'hidden_fields' => array(
													'to'		=> $query->row['icq'],
													'from'		=> $SESS->userdata['screen_name'],
													'fromemail'	=> ''
												),
						'action' 		=> 'http://wwp.icq.com/scripts/WWPMsg.dll',
						'secure' 		=> FALSE
					);
						
						
		$this->_set_page_title($LANG->line('mbr_icq_console'));

		return $this->_var_swap($this->_load_element('icq_console'),
										array(
												'form_declaration'	=>	$FNS->form_declaration($data),
												'name'				=>	$query->row['screen_name'],
												'icq'				=>	$query->row['icq'],
												'icq_im'			=>	$query->row['icq'],
												'lang:recipient'	=>	$LANG->line('mbr_icq_recipient'),
												'lang:subject'		=>	$LANG->line('mbr_icq_subject'),
												'lang:message'		=>	$LANG->line('mbr_icq_message')
											 )
										);	
	}
	/* END */
	


    /** ----------------------------------------
    /**  Member List
    /** ----------------------------------------*/

    function memberlist()
    {
		global $IN, $DB, $LANG, $OUT, $SESS, $LOC, $FNS, $PREFS;
					        		
        /** ----------------------------------------
        /**  Can the user view profiles?
        /** ----------------------------------------*/
				
		if ($SESS->userdata['can_view_profiles'] == 'n')
		{
			return $OUT->show_user_error('general', array($LANG->line('mbr_not_allowed_to_view_profiles')));
		}
		
        /** ----------------------------------------
        /**  Grab the templates
        /** ----------------------------------------*/
		
		$template = $this->_load_element('memberlist');
		$vars = $FNS->assign_variables($template, '/');	
		$var_cond = $FNS->assign_conditional_variables($template, '/');
	
		$memberlist_rows = $this->_load_element('memberlist_rows');
		$mvars = $FNS->assign_variables($memberlist_rows, '/');
		$mvar_cond = $FNS->assign_conditional_variables($memberlist_rows, '/');

		$this->var_cond		= array_merge($var_cond, $mvar_cond);
		$this->var_single	= array_merge($vars['var_single'], $mvars['var_single']);
		$this->var_pair		= array_merge($vars['var_pair'], $mvars['var_pair']);
					  
        /** ----------------------------------------
        /**  Fetch the custom member field definitions
        /** ----------------------------------------*/
        
        $fields = array();
        
        $query = $DB->query("SELECT m_field_id, m_field_name FROM exp_member_fields");
        
        if ($query->num_rows > 0)
        {
			foreach ($query->result as $row)
			{
				$fields[$row['m_field_name']] = $row['m_field_id'];
			}
        }
        
		
        /** ----------------------------------------
        /**  Assign default variables
        /** ----------------------------------------*/
                                                                     			
		$vars = array(
						'group_id'		=>	0, 
						'order_by'		=>	($PREFS->ini('memberlist_order_by') == '') ? 'total_posts' : $PREFS->ini('memberlist_order_by'), 
						'sort_order'	=>	($PREFS->ini('memberlist_sort_order') == '') ? 'desc' : $PREFS->ini('memberlist_sort_order'),
						'row_limit'		=>	($PREFS->ini('memberlist_row_limit') == '') ? 20 : $PREFS->ini('memberlist_row_limit'),
						'row_count'		=>	0
					);
					
		
		foreach ($vars as $key => $val)
		{
			$$key = ( ! isset($_POST[$key])) ? $val : $_POST[$key];
		}
		
		/* ----------------------------------------
        /*  Check for Search URL
        /*		- In an attempt to be clever, I decided to first check for
        		the Search ID and if found, use an explode to set it and
        		find a new $this->cur_id.  This solves the problem easily
        		and saves me from using substr() and strpos() far too many times
        		for a sane man to consider reasonable. -Paul
        /* ----------------------------------------*/
        
        $search_path = '';
		
		if (preg_match("|\/([a-z0-9]{32})\/|i", '/'.$IN->QSTR.'/', $match))
		{
			foreach(explode('/', '/'.$IN->QSTR.'/') as $val)
			{
				if (isset($search_id))
				{
					$this->cur_id = $val;
					break;
				}
				elseif($match['1'] == $val)
				{
					$search_id = $val;
					$search_path .= '/'.$search_id.'/';
				}
			}
		}

        /** ----------------------------------------
        /**  Parse the request URI
        /** ----------------------------------------*/
		
		$path = '';
                
		if (preg_match("#^[0-9]{1,}\-[0-9a-z_]{1,}\-[0-9a-z]{1,}\-[0-9]{1,}\-[0-9]{1,}$#i", $this->cur_id))
		{
			$x = explode("-", $this->cur_id);
		
			$group_id	= $x['0'];
			$order_by 	= $x['1'];
			$sort_order	= $x['2'];
			$row_limit	= $x['3'];
			$row_count	= $x['4'];
									
			$path = '/'.$x['0'].'-'.$x['1'].'-'.$x['2'].'-'.$x['3'].'-';
		}
		else
		{
			$path = '/'.$group_id.'-'.$order_by.'-'.$sort_order.'-'.$row_limit.'-';
		}
        
        /** ----------------------------------------
        /**  Build the query
        /** ----------------------------------------*/
        
        $f_sql	= "SELECT m.member_id, m.username, m.screen_name, m.email, m.url, m.location, m.icq, m.aol_im, m.yahoo_im, m.msn_im, m.location, m.join_date, m.last_visit, m.last_activity, m.last_entry_date, m.last_comment_date, m.last_forum_post_date, m.total_entries, m.total_comments, m.total_forum_topics, m.total_forum_posts, m.language, m.timezone, m.daylight_savings, m.bday_d, m.bday_m, m.bday_y, m.accept_user_email, m.avatar_filename, m.avatar_width, m.avatar_height, (m.total_forum_topics + m.total_forum_posts) AS total_posts, g.group_title ";		
		$p_sql	= "SELECT COUNT(member_id) AS count ";
		$sql    = "FROM exp_members m, exp_member_groups g 
				   WHERE m.group_id = g.group_id 
				   AND g.group_id != '3' 
				   AND g.group_id != '4' 
				   AND g.site_id = '".$DB->escape_str($PREFS->ini('site_id'))."'
				   AND g.include_in_memberlist = 'y' ";
		
		if ($this->is_admin == FALSE OR $SESS->userdata('group_id') != 1)
		{
			$sql .= "AND g.group_id != '2' ";
		}

        // 2 = Banned 3 = Guests 4 = Pending
						
		if ($group_id != 0)
		{
			$sql .= " AND g.group_id = '$group_id'";
		}
		
		/** ----------------------------------------
        /**  Load the Search's Member IDs
        /** ----------------------------------------*/
        
        if (isset($search_id))
        {
        	$sql .= $this->fetch_search($search_id);
        	
        	//echo $this->search_keywords.' => '.$this->search_fields;
        }
		
		/** -------------------------------------
		/**  First Letter of Screen Name, Secret Addition
		/** -------------------------------------*/
		
		$first_letter = '';
		
		// No pagination
		// Pagination or No Pagination & Forum
		// Pagination & Forum
		
		for ($i=3; $i <= 5; ++ $i)
		{
			if (isset($IN->SEGS[$i]) && strlen($IN->SEGS[$i]) == 1 && preg_match("/[A-Z]{1}/", $IN->SEGS[$i]))
			{
				$first_letter = $IN->SEGS[$i];
				$sql .= " AND m.screen_name LIKE '{$first_letter}%' ";
				break;
			}
		}

        /** ----------------------------------------
        /**  Run "count" query for pagination
        /** ----------------------------------------*/
        
		$query = $DB->query($p_sql.$sql);
		
		
 		if ($order_by == 'total_posts')
 		{
			$sql .= " ORDER BY ".$order_by." ".$sort_order;
 		}
		else
		{
			$sql .= " ORDER BY m.".$order_by." ".$sort_order;
		}
		
				
		/** -----------------------------
    	/**  Build Pagination
    	/** -----------------------------*/
						
		// Set the stats for: {current_page} of {total_pages}
		
		$current_page = floor(($row_count / $row_limit) + 1);
		$total_pages  = ceil($query->row['count'] / $row_limit);			
		
		// Deprecate this
		$page_count = $LANG->line('page').' '.$current_page.' '.$LANG->line('of').' '.$total_pages;
		
		$pager = ''; 
		
		if ($query->row['count'] > $row_limit)
		{ 											
			if ( ! class_exists('Paginate'))
			{
				require PATH_CORE.'core.paginate'.EXT;
			}
			
			$PGR = new Paginate();
			
			$PGR->first_url 	= $this->_member_path('memberlist'.$search_path);
			$PGR->path			= $this->_member_path('memberlist'.$search_path.$path, '');
			$PGR->suffix		= ($first_letter != '') ? $first_letter.'/' : '';
			$PGR->total_count 	= $query->row['count'];
			$PGR->per_page		= $row_limit;
			$PGR->cur_page		= $row_count;
			$PRG->first_page	= $LANG->line('first');   
			$PRG->last_page		= $LANG->line('last');   
			
			if (preg_match("/".LD.'pagination_links'.RD."/", $template))
			{
				$PGR->first_div_o	= '<td><div class="paginate">';
				$PGR->first_div_c	= '</div></td>';
				$PGR->next_div_o	= '<td><div class="paginate">';
				$PGR->next_div_c	= '</div></td>';
				$PGR->prev_div_o	= '<td><div class="paginate">';
				$PGR->prev_div_c	= '</div></td>';
				$PGR->num_div_o		= '<td><div class="paginate">';
				$PGR->num_div_c		= '</div></td>';
				$PGR->cur_div_o		= '<td><div class="paginateCur">';
				$PGR->cur_div_c		= '</div></td>';
				$PGR->last_div_o	= '<td><div class="paginate">';
				$PGR->last_div_c	= '</div></td>';
			}
			
			$pager = $PGR->show_links();			
			 
			$sql .= " LIMIT ".$row_count.", ".$row_limit;			
		}
					
        /** ----------------------------------------
        /**  Run the full query and process result
        /** ----------------------------------------*/
        
		$query = $DB->query($f_sql.$sql);    
	
		$str = '';
		$i = 0;

		if ($query->num_rows > 0)
		{	
			foreach ($query->result as $row)
			{
				$temp = $memberlist_rows;
				
            	$style = ($i++ % 2) ? 'memberlistRowOne' : 'memberlistRowTwo';
				
				$temp = str_replace("{member_css}", $style, $temp);
				$temp = str_replace("{path:profile}", $this->_member_path($row['member_id']), $temp);					
				
				if ($row['url'] != '' AND substr($row['url'], 0, 4) != "http") 
				{ 
					$row['url'] = "http://".$row['url']; 
				} 
				
				$temp = $this->_var_swap($temp,
										array(
												'aim_console'	=> "onclick=\"window.open('".$this->_member_path('aim_console/'.$row['member_id'])."', '_blank', 'width=240,height=360,scrollbars=yes,resizable=yes,status=yes,screenx=5,screeny=5');\"",
												'icq_console'	=> "onclick=\"window.open('".$this->_member_path('icq_console/'.$row['member_id'])."', '_blank', 'width=650,height=580,scrollbars=yes,resizable=yes,status=yes,screenx=5,screeny=5');\"",
												'yahoo_console'	=> "http://edit.yahoo.com/config/send_webmesg?.target=".$row['yahoo_im']."&amp;.src=pg",
												'email_console'	=> "onclick=\"window.open('".$this->_member_path('email_console/'.$row['member_id'])."', '_blank', 'width=650,height=600,scrollbars=yes,resizable=yes,status=yes,screenx=5,screeny=5');\"",
											)
										);

				/** ----------------------------------------
				/**  Parse conditional pairs
				/** ----------------------------------------*/
	
				foreach ($this->var_cond as $val)
				{								
					/** ----------------------------------------
					/**  Conditional statements
					/** ----------------------------------------*/
							
					$cond = $FNS->prep_conditional($val['0']);

					$lcond	= substr($cond, 0, strpos($cond, ' '));
					$rcond	= substr($cond, strpos($cond, ' '));
														
					/** ----------------------------------------
					/**  Parse conditions in standard fields
					/** ----------------------------------------*/
				
					if ( isset($row[$val['3']]))
					{       
						$lcond = str_replace($val['3'], "\$row['".$val['3']."']", $lcond);
						$cond = $lcond.' '.$rcond;
						$cond = str_replace("\|", "|", $cond);
								 
						eval("\$result = ".$cond.";");

						if ($result)
						{
							$temp = preg_replace("/".LD.$val['0'].RD."(.*?)".LD.'\/if'.RD."/s", "\\1", $temp); 
						}
						else
						{
							$temp = preg_replace("/".LD.$val['0'].RD."(.*?)".LD.'\/if'.RD."/s", "", $temp); 
						}   
					}
					/** ------------------------------------------
					/**  Parse conditions in custom member fields
					/** ------------------------------------------*/

					elseif (isset($fields[$val['3']]))
					{
						if (isset($row['m_field_id_'.$fields[$val['3']]]))
						{
							$v = $row['m_field_id_'.$fields[$val['3']]];
										 
							$lcond = str_replace($val['3'], "\$v", $lcond);
							$cond = $lcond.' '.$rcond;
							$cond = str_replace("\|", "|", $cond);
									 
							eval("\$result = ".$cond.";");
		
							if ($result)
							{
								$temp = preg_replace("/".LD.$val['0'].RD."(.*?)".LD.'\/if'.RD."/s", "\\1", $temp); 
							}
							else
							{
								$temp = preg_replace("/".LD.$val['0'].RD."(.*?)".LD.'\/if'.RD."/s", "", $temp); 
							}   
						}
					}                        

					/** ----------------------------------------
					/**  {if accept_email}
					/** ----------------------------------------*/

					if (preg_match("/^if\s+accept_email.*/i", $val['0']))
					{ 
						if ($row['accept_user_email'] == 'n')
						{
							$temp = $this->_deny_if('accept_email', $temp);
						}
						else
						{ 
							$temp = $this->_allow_if('accept_email', $temp);
						} 
					}
					
					/** ----------------------------------------
					/**  {if avatar}
					/** ----------------------------------------*/

					if (preg_match("/^if\s+avatar.*/i", $val['0']))
					{ 
						if ($PREFS->ini('enable_avatars') == 'y' AND $row['avatar_filename'] != '' AND $SESS->userdata('display_avatars') == 'y' )
						{
							$avatar_path	= $PREFS->ini('avatar_url', 1).$row['avatar_filename'];
							$avatar_width	= $row['avatar_width'];
							$avatar_height	= $row['avatar_height'];
							
							$temp = $this->_allow_if('avatar', $temp);
						}
						else
						{
							$avatar_path	= '';
							$avatar_width	= '';
							$avatar_height	= '';
							
							$temp = $this->_deny_if('avatar', $temp);
						}
					}
					
				}
				// END PAIRS
				
						
				/** ----------------------------------------
				/**  Manual replacements
				/** ----------------------------------------*/
											
				$temp = str_replace(LD.'name'.RD, ($row['screen_name'] != '') ? $row['screen_name'] : $row['username'], $temp);
		
				/** ----------------------------------------
				/**  1:1 variables
				/** ----------------------------------------*/
			
				foreach ($this->var_single as $key => $val)
				{    
					/** ----------------------------------------
					/**  parse profile path
					/** ----------------------------------------*/
					
					if (strncmp('profile_path', $key, 12) == 0)
					{                       
						$temp = $this->_var_swap_single($key, $FNS->create_url($FNS->extract_path($key).'/'.$row['member_id']), $temp);
					}
				
					/** ----------------------------------------
					/**  parse avatar path
					/** ----------------------------------------*/
					
					if (strncmp('path:avatars', $key, 12) == 0)
					{                       
						$temp = $this->_var_swap_single($key, $avatar_path, $temp);
					}
				
					/** ----------------------------------------
					/**  parse "last_visit" 
					/** ----------------------------------------*/
					
					if (strncmp('last_visit', $key, 10) == 0)
					{			
						$temp = $this->_var_swap_single($key, ($row['last_activity'] > 0) ? $LOC->decode_date($val, $row['last_activity']) : '--', $temp);
					}
				  
					/** ----------------------------------------
					/**  parse "join_date" 
					/** ----------------------------------------*/
					
					if (strncmp('join_date', $key, 9) == 0)
					{        
						$temp = $this->_var_swap_single($key, ($row['join_date'] > 0) ? $LOC->decode_date($val, $row['join_date']) : '--', $temp);
					}
					
					/** ----------------------------------------
					/**  parse "last_entry_date" 
					/** ----------------------------------------*/
					
					if (strncmp('last_entry_date', $key, 15) == 0)
					{                     
						$temp = $this->_var_swap_single($key, ($row['last_entry_date'] > 0) ? $LOC->decode_date($val, $row['last_entry_date']) : '--', $temp);
					}
					
					/** ----------------------------------------
					/**  parse "last_comment_date" 
					/** ----------------------------------------*/
					
					if (strncmp('last_comment_date', $key, 17) == 0)
					{                     
						$temp = $this->_var_swap_single($key, ($row['last_comment_date'] > 0) ? $LOC->decode_date($val, $row['last_comment_date']) : '--', $temp);
					}

					/** ----------------------------------------
					/**  parse "last_forum_post_date" 
					/** ----------------------------------------*/
					
					if (strncmp('last_forum_post_date', $key, 20) == 0)
					{                     
						$temp = $this->_var_swap_single($key, ($row['last_forum_post_date'] > 0) ? $LOC->decode_date($val, $row['last_forum_post_date']) : '--', $temp);
					}

					/** ----------------------------------------
					/**  {total_forum_posts}
					/** ----------------------------------------*/
				
					if ($key == 'total_forum_posts')
					{                    
						$temp = $this->_var_swap_single($val, $row['total_forum_topics']+$row['total_forum_posts'], $temp);
					}

					/** ----------------------------------------
					/**  {total_combined_posts}
					/** ----------------------------------------*/
				
					if ($key == 'total_combined_posts')
					{                    
						$temp = $this->_var_swap_single($val, $row['total_forum_topics']+$row['total_forum_posts']+$row['total_entries']+$row['total_comments'], $temp);
					}
					
					/** ----------------------------------------
					/**  {total_entries}
					/** ----------------------------------------*/
				
					if ($key == 'total_entries')
					{                    
						$temp = $this->_var_swap_single($val, $row['total_entries'], $temp);
					}

					/** ----------------------------------------
					/**  {total_comments}
					/** ----------------------------------------*/
				
					if ($key == 'total_comments')
					{                    
						$temp = $this->_var_swap_single($val, $row['total_comments'], $temp);
					}
					
					/** ----------------------------------------
					/**  parse literal variables
					/** ----------------------------------------*/
				
					if (isset($row[$val]))
					{                    
						$temp = $this->_var_swap_single($val, $row[$val], $temp);
					}
					
					/** ----------------------------------------
					/**  parse custom member fields
					/** ----------------------------------------*/
	
					if ( isset($fields[$val]) AND isset($row['m_field_id_'.$fields[$val]]))
					{
						$temp = $this->_var_swap_single($val, $row['m_field_id_'.$fields[$val]], $temp);
					}
				}			
			
				$str .= $temp;
			}
		}
				
		/** ----------------------------------------
		/**  Render the member group list
		/** ----------------------------------------*/
		
		$english = array('Guests', 'Banned', 'Members', 'Pending', 'Super Admins');
		
		$sql = "SELECT group_id, group_title FROM exp_member_groups 
				WHERE include_in_memberlist = 'y' AND site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' AND group_id != '3' AND group_id != '4' ";
		
		if ($this->is_admin == FALSE OR $SESS->userdata('group_id') != 1)
		{
			$sql .= "AND group_id != '2' ";
		}
		
		$sql .= " order by group_title";
		
		$query = $DB->query($sql);

		$selected = ($group_id == 0) ? " selected='selected' " : '';

		$menu = "<option value='0'".$selected.">".$LANG->line('mbr_all_member_groups')."</option>\n";
				
		foreach ($query->result as $row)
		{
			$group_title = $row['group_title'];
		
            if (in_array($group_title, $english))
            {
                $group_title = $LANG->line(strtolower(str_replace(" ", "_", $group_title)));
            }
			
			$selected = ($group_id == $row['group_id']) ? " selected='selected' " : '';
					
			$menu .= "<option value='".$row['group_id']."'".$selected.">".$group_title."</option>\n";
		}
		
		$template = str_replace(LD.'group_id_options'.RD, $menu, $template);
		
		
		/** ----------------------------------------
		/**  Create the "Order By" menu
		/** ----------------------------------------*/
		
		$selected = ($order_by == 'screen_name') ? " selected='selected' " : '';
		$menu = "<option value='screen_name'".$selected.">".$LANG->line('mbr_member_name')."</option>\n";

		if ($this->in_forum == TRUE)
		{
			$selected = ($order_by == 'total_posts') ? " selected='selected' " : '';
			$menu .= "<option value='total_posts'".$selected.">".$LANG->line('total_posts')."</option>\n";
		}
		else
		{
			$selected = ($order_by == 'total_comments') ? " selected='selected' " : '';
			$menu .= "<option value='total_comments'".$selected.">".$LANG->line('mbr_total_comments')."</option>\n";
			
			$selected = ($order_by == 'total_entries') ? " selected='selected' " : '';
			$menu .= "<option value='total_entries'".$selected.">".$LANG->line('mbr_total_entries')."</option>\n";
		}
		
		$selected = ($order_by == 'join_date') ? " selected='selected' " : '';
		$menu .= "<option value='join_date'".$selected.">".$LANG->line('join_date')."</option>\n";

		$template = str_replace(LD.'order_by_options'.RD, $menu, $template);
		
		/** ----------------------------------------
		/**  Create the "Sort By" menu
		/** ----------------------------------------*/
		
		$selected = ($sort_order == 'asc') ? " selected='selected' " : '';
		$menu = "<option value='asc'".$selected.">".$LANG->line('mbr_ascending')."</option>\n";
		
		$selected = ($sort_order == 'desc') ? " selected='selected' " : '';
		$menu .= "<option value='desc'".$selected.">".$LANG->line('mbr_descending')."</option>\n";
		
		$template = str_replace(LD.'sort_order_options'.RD, $menu, $template);
		
		/** ----------------------------------------
		/**  Create the "Row Limit" menu
		/** ----------------------------------------*/
		
		$selected = ($row_limit == '10') ? " selected='selected' " : '';
		$menu  = "<option value='10'".$selected.">10</option>\n";
		$selected = ($row_limit == '20') ? " selected='selected' " : '';
		$menu .= "<option value='20'".$selected.">20</option>\n";
		$selected = ($row_limit == '30') ? " selected='selected' " : '';
		$menu .= "<option value='30'".$selected.">30</option>\n";
		$selected = ($row_limit == '40') ? " selected='selected' " : '';
		$menu .= "<option value='40'".$selected.">40</option>\n";
		$selected = ($row_limit == '50') ? " selected='selected' " : '';
		$menu .= "<option value='50'".$selected.">50</option>\n";
		
		if ($row_limit > 50)
		{
			$menu .= "<option value='".$row_limit."' selected='selected'>".$row_limit."</option>\n";
		}
		
		$template = str_replace(LD.'row_limit_options'.RD, $menu, $template);
		
		
		/** ----------------------------------------
		/**  Custom Profile Fields for Member Search
		/** ----------------------------------------*/
		
		$sql = "SELECT m_field_id, m_field_label FROM exp_member_fields WHERE m_field_public = 'y' ORDER BY m_field_order ";
		
		$query = $DB->query($sql);
		
		$profile_options = '';
				
		foreach ($query->result as $row)
		{
			$profile_options .= "<option value='m_field_id_".$row['m_field_id']."'>".$row['m_field_label']."</option>\n";
		}
		
		$template = str_replace(LD.'custom_profile_field_options'.RD, $profile_options, $template);

		/** ----------------------------------------
		/**  Put rendered chunk into template
		/** ----------------------------------------*/

		if ($pager == '')
		{
			$template = $this->_deny_if('paginate', $template); 
		}
		else
		{
			$template = $this->_allow_if('paginate', $template); 
			
			// Deprecate these...
			$template = str_replace(LD.'paginate'.RD, 			$pager, $template);
			$template = str_replace(LD.'page_count'.RD, 		$page_count, $template);
			//.....
			$template = str_replace(LD.'pagination_links'.RD,	$pager, 		$template);
			$template = str_replace(LD.'current_page'.RD, 		$current_page, 	$template);
			$template = str_replace(LD.'total_pages'.RD, 		$total_pages,	$template);
		}

		if ($this->is_search === TRUE)
		{
			$template = str_replace(LD."form_declaration".RD, "<form method='post' action='".$this->_member_path('member_search'.$search_path)."'>", $template);		
		}
		else
		{
			$template = str_replace(LD."form_declaration".RD, "<form method='post' action='".$this->_member_path('memberlist'.(($first_letter != '') ? $first_letter.'/' : $search_path))."'>", $template);		
		}
		
		$template = str_replace(LD."form:form_declaration:do_member_search".RD, "<form method='post' action='".$this->_member_path('do_member_search')."'>", $template);
		
		$template = str_replace(LD."member_rows".RD, $str, $template);		
		
		return	$template;
	}
	/* END */
	
	/** ------------------------------------------
	/**  Take Search ID and Fetch Member IDs
	/** ------------------------------------------*/
	
	function fetch_search($search_id)
	{
		global $DB;
		
		$query = $DB->query("SELECT * FROM exp_member_search WHERE search_id = '".$DB->escape_str($search_id)."'");
		
		if ($query->num_rows == 0)
		{
			return '';
		}
		
		$this->is_search = TRUE;
		$this->search_keywords	= str_replace('|', ", ", $query->row['keywords']);
		$this->search_fields	= str_replace('|', ", ", $query->row['fields']);
		$this->search_total		= $query->row['total_results'];
		
		$query = $DB->query($query->row['query']);
		
		$return = '';
		
		if ($query->num_rows > 0)
		{
			$return = 'AND m.member_id IN (';
			
			foreach($query->result as $row)
			{
				$return .= "'".$row['member_id']."',";
			}
			
			$return = substr($return, 0, -1).")";
		}
		
		return $return;
	}
	/* END */
	
	
	
	/** ------------------------------------------
	/**  Perform a Search
	/** ------------------------------------------*/
	
	function do_member_search()
	{
		global $DB, $SESS, $OUT, $LANG, $LOC, $FNS, $IN, $PREFS;
		
		/** ----------------------------------------
        /**  Fetch the search language file
        /** ----------------------------------------*/
        
        $LANG->fetch_language_file('search');
		
		/** ----------------------------------------
        /**  Is the current user allowed to search?
        /** ----------------------------------------*/

        if ($SESS->userdata['can_search'] == 'n' AND $SESS->userdata['group_id'] != 1)
        {            
            return $OUT->show_user_error('general', array($LANG->line('search_not_allowed')));
        }
        
        /** ----------------------------------------
        /**  Flood control
        /** ----------------------------------------*/
        
        if ($SESS->userdata['search_flood_control'] > 0 AND $SESS->userdata['group_id'] != 1)
		{
			$cutoff = time() - $SESS->userdata['search_flood_control'];

			$sql = "SELECT search_id FROM exp_search WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' AND search_date > '{$cutoff}' AND ";
			
			if ($SESS->userdata['member_id'] != 0)
			{
				$sql .= "(member_id='".$DB->escape_str($SESS->userdata('member_id'))."' OR ip_address='".$DB->escape_str($IN->IP)."')";
			}
			else
			{
				$sql .= "ip_address='".$DB->escape_str($IN->IP)."'";
			}
			
			$query = $DB->query($sql);
								
			$text = str_replace("%x", $SESS->userdata['search_flood_control'], $LANG->line('search_time_not_expired'));
				
			if ($query->num_rows > 0)
			{
            	return $OUT->show_user_error('general', array($text));
			}
		}
		
		/** ----------------------------------------
        /**  Valid Fields for Searching
        /** ----------------------------------------*/
		
		$valid = array(	'screen_name', 'email', 'url', 
						'location', 'occupation', 'interests', 
						'aol_im', 'yahoo_im', 'msn_im', 'icq', 
						'bio', 'signature');
		
		$custom_fields = FALSE;
		$query = $DB->query("SELECT m_field_id, m_field_label FROM exp_member_fields WHERE m_field_public = 'y' ORDER BY m_field_order");
		
		if ($query->num_rows > 0)
		{
			$custom_fields = array();
			
			foreach($query->result as $row)
			{
				$custom_fields[$row['m_field_id']] = $row['m_field_label'];
				
				$valid[] = 'm_field_id_'.$row['m_field_id'];
			}
		}
		
		/** ----------------------------------------
        /**  Compile the Search
        /** ----------------------------------------*/
        
        $search_array = array();
		
		foreach($_POST as $key => $value)
		{
			if (substr($key, 0, 13) == 'search_field_' && isset($_POST['search_keywords_'.substr($key, 13)]))
			{
				if (in_array($value, $valid) && trim($_POST['search_keywords_'.substr($key, 13)]) != '')
				{
					$search_array[] = array($value, trim($_POST['search_keywords_'.substr($key, 13)]));
				}
			}
		}
		
		/** ----------------------------------------
        /**  Stuff that is tediously boring to explain
        /** ----------------------------------------*/
		
		if (isset($_POST['search_group_id']))
		{
			$_POST['group_id'] = $_POST['search_group_id'];
		}
		
		if (sizeof($search_array) == 0)
		{	
			return $this->memberlist();
		}
		
		/** ----------------------------------------
        /**  Create Query
        /** ----------------------------------------*/
		
		$keywords = array();
		$fields   = array();
		
		$xsql = ($this->is_admin == FALSE OR $SESS->userdata('group_id') != 1) ? ",'2'" : "";
		
		if ($custom_fields === FALSE)
		{
			$sql = "SELECT m.member_id FROM exp_members m 
					WHERE m.group_id NOT IN ('3', '4'{$xsql}) ";
		}
		else
		{
			$sql = "SELECT m.member_id FROM exp_members m, exp_member_data md
					WHERE m.member_id = md.member_id 
					AND m.group_id NOT IN ('3', '4'{$xsql}) ";
		}
		
		if (isset($_POST['search_group_id']) && $_POST['search_group_id'] != '0')
		{
			$sql .= "AND m.group_id = '".$DB->escape_str($_POST['search_group_id'])."'";
		}
		
		foreach($search_array as $search)
		{
			if (substr($search['0'], 0, 11) == 'm_field_id_' && is_numeric(substr($search['0'], 11)))
			{
				$fields[] = $custom_fields[substr($search['0'], 11)];
				
				$sql .= "AND md.".$search['0']." LIKE '%".$DB->escape_like_str($search['1'])."%' ";
			}
			else
			{
				$fields[] = $LANG->line($search['0']);
				
				$sql .= "AND m.".$search['0']." LIKE '%".$DB->escape_like_str($search['1'])."%' ";
			}
			
			$keywords[] = $search['1'];
		}
		
		$query = $DB->query($sql);
		
		if ($query->num_rows == 0)
		{
			return $OUT->show_user_error('off', array($LANG->line('search_no_result')), $LANG->line('search_result_heading'));
		}
		
		/** ----------------------------------------
        /**  If we have a result, cache it
        /** ----------------------------------------*/
		
		$hash = $FNS->random('md5');
		
		$data = array(
						'search_id'		=> $hash,
						'search_date'	=> $LOC->now,
						'member_id'		=> $SESS->userdata('member_id'),
						'keywords'		=> implode('|', $keywords),
						'fields'		=> implode('|', $fields),
						'ip_address'	=> $IN->IP,
						'total_results'	=> $query->num_rows,
						'query'			=> $sql,
						'site_id'		=> $PREFS->ini('site_id')
						);
		
		$DB->query($DB->insert_string('exp_member_search', $data));
					
        /** ----------------------------------------
        /**  Redirect to search results page
        /** ----------------------------------------*/
		
		return $FNS->redirect($FNS->remove_double_slashes($this->_member_path('member_search/'.$hash)));
	}
	/* END */
	

	
}
// END CLASS
?>