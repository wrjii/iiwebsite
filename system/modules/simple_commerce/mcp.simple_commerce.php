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
 File: mcp.simple_commerce.php
-----------------------------------------------------
 Purpose: Simple Commerce class - CP
=====================================================
*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}



class Simple_commerce_CP {

    var $version			= '1.0';
    var $export_type		= 'tab';
    
    var $perform_redirects	= TRUE;
    
    var $menu_email			= array();
    var $menu_groups		= array();
    
    
    /** -------------------------------------------
    /**  Constructor
    /** -------------------------------------------*/

	function Simple_commerce_CP ($switch = TRUE)
	{
		global $IN, $DB, $LANG;
		
		/** -------------------------------
		/**  Is the module installed?
		/** -------------------------------*/
        
        $query = $DB->query("SELECT module_version FROM exp_modules WHERE module_name = 'Simple_commerce'");
        
        if ($query->num_rows == 0)
        {
        	return;
        }
        elseif($query->row['module_version'] != $this->version)
        {
        	$this->simple_commerce_update_module($query->row['module_version']);
        }
		
		/** -------------------------------
		/**  On with the show!
		/** -------------------------------*/
		
		$LANG->fetch_language_file('publish');

		if ($switch)
        {
        	if ($IN->GBL('P') === FALSE OR $IN->GBL('P') == '' OR ! method_exists($this, $IN->GBL('P')))
        	{
        		$this->homepage();
        	}
        	else
        	{
        		$this->{$IN->GBL('P')}();
        	}
        }
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Control Panel homepage
    /** -------------------------------------------*/

	function homepage($msg = '')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB;
                        
        $DSP->title  = $LANG->line('simple_commerce_module_name');
        $DSP->crumb  = $LANG->line('simple_commerce_module_name');
        
        $DSP->body .= $DSP->qdiv('tableHeading', $LANG->line('simple_commerce_module_name')); 
        
        if ($msg != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
        
        $DSP->body	.=	$DSP->table('tableBorder', '0', '0', '100%').
						$DSP->tr().
						$DSP->table_qcell('tableHeadingAlt', 
											array('','','', '')
											).
						$DSP->tr_c();
		$i = 0;
		$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ++$i;
		
		$DSP->body .= $DSP->tr();
            
		$DSP->body .= $DSP->table_qcell($style, '<b>'.$LANG->line('store_items').'</b>', '25%');
        $DSP->body .= $DSP->table_qcell($style, 
        								$DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=add_item',
        											 $LANG->line('add_item')), '25%');
        $DSP->body .= $DSP->table_qcell($style, 
        								$DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_items',
        											 $LANG->line('edit_items')), '25%');
        											 
        $DSP->body .= $DSP->table_qcell($style, 
        								$DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=export_items',
        											 $LANG->line('export_items')), '25%');
											
		$DSP->body .= $DSP->tr_c();
		
		$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ++$i;
		
		$DSP->body .= $DSP->tr();
            
		$DSP->body .= $DSP->table_qcell($style, 
										'<b>'.$LANG->line('store_purchases').'</b>', '25%');
        $DSP->body .= $DSP->table_qcell($style, 
        								$DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=add_purchase',
        											 $LANG->line('add_purchase')), '25%');
        $DSP->body .= $DSP->table_qcell($style, 
        								$DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_purchases',
        											 $LANG->line('edit_purchases')), '25%');
        											 
        $DSP->body .= $DSP->table_qcell($style, 
        								$DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=export_purchases',
        											 $LANG->line('export_purchases')), '25%');
											
		$DSP->body .= $DSP->tr_c();
		
		
		$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ++$i;
		
		$DSP->body .= $DSP->tr();
            
		$DSP->body .= $DSP->table_qcell($style, 
										'<b>'.$LANG->line('store_emails').'</b>', '25%');
        $DSP->body .= $DSP->table_qcell($style, 
        								$DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=add_email',
        											 $LANG->line('add_email_template')), '25%');
        $DSP->body .= $DSP->table_qcell($style, 
        								$DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_emails',
        											 $LANG->line('edit_email_templates')), '25%');
        											 
        $DSP->body .= $DSP->table_qcell($style, NBS.'--'.NBS, '25%');
											
		$DSP->body .= $DSP->tr_c();
		
		$DSP->body .= $DSP->table_c().BR; 
		
		$DSP->body .= $DSP->qdiv('tableHeadingAlt', $LANG->line('ipn_url')).
					  $DSP->div('box').
					  $DSP->qdiv('itemWrapper', $LANG->line('ipn_details'));
		
		$qs = ($PREFS->ini('force_query_string') == 'y') ? '' : '?';
        $api_url = $FNS->fetch_site_index(0, 0).$qs.'ACT='.$FNS->fetch_action_id('Simple_commerce', 'incoming_ipn');
        
        $DSP->body .= $DSP->input_text('', $api_url, '20', '400', 'input', '400px', 'readonly="readonly"');
        $DSP->body .= $DSP->div_c();
        
        /** --------------------------------
        /**  Encryption Settings
        /** --------------------------------*/
        
        $DSP->body .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=various_settings'));	
        
        $DSP->body .= BR.$DSP->qdiv('tableHeadingAlt', $LANG->line('settings')).
        			  $DSP->table('tableBorder', '0', '0', '100%');
		
		$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ++$i;
		
		$DSP->body .= $DSP->tr().
					  $DSP->table_qcell($style, $DSP->qdiv('defaultBold', $LANG->line('paypal_account')), '50%').
					  $DSP->table_qcell($style,
					  					$DSP->input_text('sc_paypal_account', $PREFS->ini('sc_paypal_account')),
					  					'50%').
					  $DSP->tr_c();
		
		$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ++$i;
		
		$DSP->body .= $DSP->tr().
					  '<td class="tableHeadingAlt" colspan="2" style="width:100%; height:1px; padding:0;"></td>'.
					  $DSP->tr_c();
		
		$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ++$i;
		
		$DSP->body .= $DSP->tr().
					  $DSP->table_qcell($style, $DSP->qdiv('defaultBold', $LANG->line('encrypt_buttons_links')), '50%').
					  $DSP->table_qcell($style,
					  					$DSP->input_radio('sc_encrypt_buttons', 'y', 
					  									  ($PREFS->ini('sc_encrypt_buttons') == 'y') ? 1 : '').
					  					NBS.$LANG->line('yes').
					  					NBS.NBS.
					  					$DSP->input_radio('sc_encrypt_buttons', 'n', 
					  									  ($PREFS->ini('sc_encrypt_buttons') == 'y') ? '' : 1).
					  					NBS.$LANG->line('no'),
					  					'50%').
					  $DSP->tr_c();
					  
		$base = $FNS->remove_double_slashes(str_replace('/public_html', '', substr(PATH, 0, - strlen($PREFS->ini('system_folder').'/'))).'/encryption/');
		
		foreach(array('certificate_id', 'public_certificate', 'private_key', 'paypal_certificate', 'temp_path') as $val)
		{						  
			$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo'; ++$i;
			
			if ($val == 'certificate_id')
			{
				$value = ($PREFS->ini('sc_'.$val) === FALSE) ? '' : $PREFS->ini('sc_'.$val);
			}
			else
			{
				$value = ($PREFS->ini('sc_'.$val) === FALSE OR $PREFS->ini('sc_'.$val) == '') ? $base.$val.'.pem' : $PREFS->ini('sc_'.$val);
			}
			
			$DSP->body .= $DSP->tr().
						  $DSP->table_qcell($style, $DSP->qdiv('defaultBold', $LANG->line($val)), '50%').
						  $DSP->table_qcell($style,
											$DSP->input_text('sc_'.$val, $value),
											'50%').
						  $DSP->tr_c();
		}
					  
		$DSP->body .= $DSP->table_c().BR.
					  $DSP->qdiv('itemWrapper',
					  			 $DSP->input_submit($LANG->line('submit'))).
					  $DSP->form_close();
	}
	/* END */
	
	
	
	/** -------------------------------------------
    /**  Save Encryption Settings
    /** -------------------------------------------*/

	function various_settings()
	{
		global $LANG, $PREFS, $REGX, $DSP;
		
		$prefs = array('encrypt_buttons', 'paypal_account', 'certificate_id', 'public_certificate', 'private_key', 'paypal_certificate', 'temp_path');
		
		$insert = array();
		
		if ( ! isset($_POST['sc_paypal_account']))
		{
			return $this->homepage();
		}
		
		foreach($prefs as $val)
		{
			if (isset($_POST['sc_'.$val]))
			{
				if ($val != 'encrypt_buttons')
				{
					if ($insert['sc_encrypt_buttons'] == 'y' && $val != 'paypal_account' && $val != 'certificate_id')
					{
						if ( ! file_exists($_POST['sc_'.$val]))
						{
							return $DSP->error_message(str_replace('%pref%', $LANG->line($val), $LANG->line('file_does_not_exist')));
						}
					
						if ($val == 'temp_path' && ! is_writable($_POST['sc_'.$val]))
						{
							return $DSP->error_message($LANG->line('temporary_directory_unwritable'));
						}
					}
					
					$insert['sc_'.$val] = $REGX->xss_clean($_POST['sc_'.$val]);
				}
				else
				{
					$insert['sc_'.$val] = ($_POST['sc_'.$val] == 'y') ? 'y' : 'n';
				}
			}
		}
		
		if (sizeof($insert) == 0)
		{
			return $this->homepage();
		}
		
		if ( ! class_exists('Admin'))
		{
			require PATH_CP.'cp.admin'.EXT;
		}
		
		if ($PREFS->ini('sc_encrypt_buttons') === FALSE)
		{
			Admin::append_config_file($insert);
		}
		else
		{
			Admin::update_config_file($insert);
		}
		
		$PREFS->core_ini = array_merge($PREFS->core_ini, $insert);
		
		return $this->homepage($LANG->line('settings_updated'));
	}
	/* END */
	
	
	
	/** -------------------------------------------
    /**  Add Item
    /** -------------------------------------------*/

	function add_item($msg = '')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB, $SESS, $IN, $REGX;
		
		$DSP->title  = $LANG->line('simple_commerce_module_name');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce', $LANG->line('simple_commerce_module_name'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('add_items')); 
        
        /** -------------------------------------------
    	/**  Must be Assigned to Weblogs
    	/** -------------------------------------------*/
          
		if (count($SESS->userdata['assigned_weblogs']) == 0)
		{
			return $DSP->no_access_message($LANG->line('no_entries_matching_that_criteria').BR.BR.$LANG->line('site_specific_data'));
		}
        
        if ($msg != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
        
        /** -------------------------------------------
    	/**  Either Show Search Form or Process Entries
    	/** -------------------------------------------*/
        
        if ($IN->GBL('toggle', 'POST') !== FALSE OR $IN->GBL('entry_id') !== FALSE)
        {
        	$entry_ids = array();
        	
        	if ($IN->GBL('entry_id') !== FALSE)
        	{
        		$entry_ids[] = $DB->escape_str($IN->GBL('entry_id'));
        	}
        	else
        	{
        		foreach ($_POST as $key => $val)
        		{        
            		if (strstr($key, 'toggle') AND ! is_array($val))
            		{
            			if ($val != '' && is_numeric($val))
            			{
							$entry_ids[] = $DB->escape_str($val);
						}
            		}        
        		}
        	}
        	
        	if (sizeof($entry_ids) == 0)
        	{
        		unset($_POST['toggle']); 
        		unset($_POST['entry_id']); 
        		unset($_GET['entry_id']);
        		return $this->add_item($LANG->line('invalid_entries'));
        	}
        	
        	/** -------------------------------------------
    		/**  Valid Entries Selected?
    		/** -------------------------------------------*/
        	
        	$query = $DB->query("SELECT entry_id, weblog_id, title FROM exp_weblog_titles WHERE entry_id IN ('".implode("','", $entry_ids)."')");
        	
        	$entry_ids = array();
        	$titles	   = array();
        	
        	if ($query->num_rows > 0)
        	{
        		foreach($query->result as $row)
        		{
        			if (isset($SESS->userdata['assigned_weblogs'][$row['weblog_id']]))
        			{
        				$entry_ids[] = $row['entry_id'];
        				$titles[$row['entry_id']] = $row['title'];
        			}
        		}
        	}
        	
        	if (sizeof($entry_ids) == 0)
        	{
        		unset($_POST['toggle']); 
        		unset($_POST['entry_id']); 
        		unset($_GET['entry_id']);
        		return $this->add_item($LANG->line('invalid_entries'));
        	}
        	
        	/** -------------------------------------------
    		/**  Weed Out Any Entries that are already items
    		/** -------------------------------------------*/
    		
    		$query = $DB->query("SELECT entry_id FROM exp_simple_commerce_items WHERE entry_id IN ('".implode("','", $entry_ids)."')");
    		
    		if ($query->num_rows > 0)
    		{
    			foreach($query->result as $row)
    			{
    				unset($titles[$row['entry_id']]);
    			}
    		}
    		
    		if (sizeof($titles) == 0)
        	{
        		unset($_POST['toggle']); 
        		unset($_POST['entry_id']); 
        		unset($_GET['entry_id']);
        		return $this->add_item($LANG->line('invalid_entries'));
        	}
        	
        	/** -------------------------------------------
    		/**  Finally!  We can do something!
    		/** -------------------------------------------*/
		
        	$r  = $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=adding_items'));	
        	$r .= $DSP->qdiv('tableHeading', $LANG->line((sizeof($entry_ids) == 1) ? 'add_item' : 'add_items' ));
				
			foreach ($titles as $entry_id => $title)
			{
				$r .= $this->modify_entry_row($entry_id, $title);
			}
		
        	$r .= $DSP->qdiv('itemWrapperTop', 
        					 $DSP->input_submit($LANG->line((sizeof($entry_ids) == 1) ? 'add_item' : 'add_items' ))).
        	      $DSP->form_close();
    
        	$DSP->body  = $r;
        }
        else
        {
        	if ( ! class_exists('Publish')) require PATH_CP.'cp.publish'.EXT;
        	
        	$actions = NBS.$DSP->input_select_header('action').
        	      	   $DSP->input_select_option('add', $LANG->line('add_items')).
        	      	   $DSP->input_select_footer();
        	      	   
        	$query = $DB->query("SELECT entry_id FROM exp_simple_commerce_items");
        	
        	$extra_sql = array();
        	
        	if ($query->num_rows > 0)
        	{
        		$extra_sql['where'] = " AND exp_weblog_titles.entry_id NOT IN ('";
        		
        		foreach($query->result as $row) $extra_sql['where'] .= $row['entry_id']."','";
        		
        		$extra_sql['where'] = substr($extra_sql['where'], 0, -2).') ';
        	}
        	
        	$PUB = new Publish();
        	$DSP->body .= $PUB->view_entries('', 
        										'', 
        										$extra_sql,
        										'C=modules'.AMP.'M=simple_commerce'.AMP.'P=add_item', 
        										'C=modules'.AMP.'M=simple_commerce'.AMP.'P=add_item', 
        										$actions);
        	
        	/* ---------------------------------------
        	/*  Had to do a preg_replace to remove 
        	/*  the default 'Edit Entries' heading
        	/* ---------------------------------------*/
        	
        	$DSP->body = preg_replace("/".str_replace('REPLACE_HERE', 
        											  '.*?', 
        											  preg_quote($DSP->qdiv('tableHeading', 'REPLACE_HERE'), 
        											  			 '/')
        											 )."/",
        							  $DSP->qdiv('tableHeading', $LANG->line('choose_entry_for_item')), 
        							  $DSP->body,
        							  1);
        }
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Abstracted Display of Entry
    /** -------------------------------------------*/
	
	function modify_entry_row($entry_id, $title, $data=array())
	{
		global $DSP, $REGX, $LANG, $DB, $PREFS;
		
		if ( ! is_array($data)) $data = array();
		
		$r = '';
		$r .= $DSP->input_hidden('entry_id['.$entry_id.']', $entry_id);
				
		$r .= NL.'<div class="publishTabWrapper">';	
		$r .= NL.'<div class="publishBox">';
			
		$r .= NL."<table class='clusterBox' border='0' cellpadding='0' cellspacing='0' style='width:99%'><tr>";	
			
		$r .= NL.'<td class="publishItemWrapper" valign="top" style="width:30%;">'.BR;
			
		$r .= $DSP->div('clusterLineR').$DSP->heading($title, 5);
		
		$r .= BR.
			  $DSP->qdiv('defaultPadBold', $LANG->line('regular_price')).
			  $DSP->input_text('regular_price['.$entry_id.']', (isset($data['item_regular_price'])) ? $data['item_regular_price'] : '0.00', '20', '75', 'input', '95%');
		
		$r .= $DSP->qdiv('defaultSmall', NBS);
		$r .= $DSP->qdiv('defaultPadBold', $LANG->line('sale_price')).
			  $DSP->input_text('sale_price['.$entry_id.']', (isset($data['item_sale_price'])) ? $data['item_sale_price'] : '0.00', '20', '75', 'input', '95%');
			  
		$r .= $DSP->qdiv('defaultSmall', NBS);
		$r .= $DSP->qdiv('defaultBold', $DSP->input_checkbox('use_sale['.$entry_id.']', 'y', (isset($data['item_use_sale'])) ? $data['item_use_sale'] : '').' '.$LANG->line('use_sale_price'));
		$r .= $DSP->qdiv('defaultBold', $DSP->input_checkbox('enabled['.$entry_id.']', 'y', (isset($data['item_enabled'])) ? $data['item_enabled'] : 'y').' '.$LANG->line('item_enabled'));

		$r .= $DSP->div_c();
		
		$r .= '</td>';
			
		$r .= NL.'<td class="publishItemWrapper" valign="top" style="width:70%;">'.BR;
		
		$r .= $DSP->heading(NBS, 5);
		
		// Instead of having something complicated that will take forever
		// to code I will just give them three options:
		// 1.  Change Member Group
		// 2.  Send Receipt
		// 3.  Admin Notification
		
		/** -------------------------------------------
    	/**  Available Email Templates
    	/** -------------------------------------------*/
    	
    	if (sizeof($this->menu_email) == 0)
    	{
    		$query = $DB->query("SELECT email_id, email_name FROM exp_simple_commerce_emails");
    		
    		foreach($query->result as $row)
			{
				$this->menu_email[] = array($row['email_id'], $REGX->form_prep($row['email_name']));
			}
    	}
		
		$admin_template = $DSP->input_select_option('0', $LANG->line('send_no_email'), '');
		$custr_template = $DSP->input_select_option('0', $LANG->line('send_no_email'), '');
		
		foreach($this->menu_email as $values)
		{
			$admin_template .= $DSP->input_select_option($values['0'], $values['1'], (isset($data['admin_email_template']) && $data['admin_email_template'] == $values['0']) ? 'y' : '');
			$custr_template .= $DSP->input_select_option($values['0'], $values['1'], (isset($data['customer_email_template']) && $data['customer_email_template'] == $values['0']) ? 'y' : '');
		}
			
		$admin_template .= $DSP->input_select_footer();
		$custr_template .= $DSP->input_select_footer();
		
		/** -------------------------------------------
    	/**  Available Member Groups
    	/** -------------------------------------------*/
		
		if (sizeof($this->menu_groups) == 0)
		{
			$query = $DB->query("SELECT group_id, group_title FROM exp_member_groups WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' ORDER BY group_title");
			
			foreach($query->result as $row)
			{
				$this->menu_groups[] = array($row['group_id'], $REGX->form_prep($row['group_title']));
			}
		}
		
		$groups_menu = $DSP->input_select_option('no_change', $LANG->line('no_change'), '');
		
		foreach($this->menu_groups as $values)
		{
			$groups_menu .= $DSP->input_select_option($values['0'], $values['1'], (isset($data['new_member_group']) && $data['new_member_group'] == $values['0']) ? 'y' : '');
		}
		
		$groups_menu .= $DSP->input_select_footer(); 
		
		$r .= $DSP->qdiv('defaultSmall', NBS).
			  $DSP->qdiv('defaultPadBold', $LANG->line('admin_email_address')).
			  $DSP->input_text('admin_email_address['.$entry_id.']', (isset($data['admin_email_address'])) ? $data['admin_email_address'] : '', '20', '75', 'input', '50%').
			  $DSP->qdiv('defaultSmall', NBS).
			  $DSP->qdiv('defaultPadBold', $LANG->line('admin_email_template')).
			  $DSP->input_select_header('admin_email_template['.$entry_id.']').$admin_template;
		
		$r .= $DSP->qdiv('defaultSmall', NBS).
			  $DSP->qdiv('defaultPadBold', $LANG->line('customer_email')).
			  $DSP->input_hidden('customer_email['.$entry_id.']', 'purchaser').
			  $DSP->input_select_header('customer_email_template['.$entry_id.']').$custr_template;
		
		$r .= $DSP->qdiv('defaultSmall', NBS).
			  $DSP->qdiv('defaultPadBold', $LANG->line('member_group')).
			  $DSP->input_select_header('member_group['.$entry_id.']').$groups_menu;
		
		$r .= '</td>';
			
		$r .= "</tr></table>";
			
		$r .= $DSP->div_c();
		$r .= $DSP->div_c();
		
		return $r;
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Modify Store Items - Add/Update
    /** -------------------------------------------*/
    
    function adding_items()		{ $this->modify_items(); 	}
    function updating_items()	{ $this->modify_items('n');	}

	function modify_items($new = 'y')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB, $SESS, $REGX;
		
		if ( ! is_array($_POST['entry_id']))
		{
            return $DSP->no_access_message();
		}
		
		$entry_ids = array();
		
		foreach ($_POST['entry_id'] as $id)
		{
			$entry_ids[] = $DB->escape_str($id);
		}
		
		/** -------------------------------------------
		/**  Valid Entries Selected?
		/** -------------------------------------------*/
        	
        $query = $DB->query("SELECT entry_id, weblog_id FROM exp_weblog_titles WHERE entry_id IN ('".implode("','", $entry_ids)."')");
        	
        $entry_ids = array();
        	
        if ($query->num_rows > 0)
        {
        	foreach($query->result as $row)
        	{
        		if (isset($SESS->userdata['assigned_weblogs'], $row['weblog_id']))
        		{
        			$entry_ids[$row['entry_id']] = $row['entry_id'];
        		}
        	}
        }
        	
        if (sizeof($entry_ids) == 0)
        {
        	unset($_POST['entry_id']);
        	return $this->add_item($LANG->line('invalid_entries'));
        }
        	
        /** -------------------------------------------
    	/**  Weed Out Any Entries that are already items
    	/** -------------------------------------------*/
    	
    	if ($new == 'y')
    	{
    		$query = $DB->query("SELECT entry_id FROM exp_simple_commerce_items WHERE entry_id IN ('".implode("','", $entry_ids)."')");
    		
    		if ($query->num_rows > 0)
    		{
    			foreach($query->result as $row)
    			{
    				unset($entry_ids[$row['entry_id']]);
    			}
    		}
    		
    		if (sizeof($entry_ids) == 0)
        	{
        		unset($_POST['entry_id']); 
        		return $this->add_item($LANG->line('invalid_entries'));
        	}
        }
			
		foreach($entry_ids as $id)
		{
			$data = array(
							'entry_id'					=> $_POST['entry_id'][$id],
							'item_enabled'				=> ( ! isset($_POST['enabled'][$id])) ? 'n' : 'y',
							'item_regular_price'		=> $_POST['regular_price'][$id],
							'item_sale_price'			=> $_POST['sale_price'][$id],
							'item_use_sale'				=> ( ! isset($_POST['use_sale'][$id])) ? 'n' : 'y',
							'new_member_group'			=> ($_POST['member_group'][$id] == 'no_change') ? 0 : $_POST['member_group'][$id],
							'admin_email_address'		=> $_POST['admin_email_address'][$id],
							'admin_email_template'		=> $_POST['admin_email_template'][$id],
							'customer_email_template'	=> $_POST['customer_email_template'][$id],
							);
        
			$error = array();
			
			if ($data['item_regular_price'] == '0.00' OR ! is_numeric($data['item_regular_price']) OR $data['item_regular_price'] <= 0)
			{
				$error[] = $LANG->line('invalid_price');
			}
			
			if ($data['item_sale_price'] == '0.00' OR ! is_numeric($data['item_sale_price']) OR $data['item_sale_price'] <= 0)
			{
				$error[] = $LANG->line('invalid_price');
			}
			
			if (trim($data['admin_email_address']) != ''  && $data['admin_email_template'] == 0)
			{
				$error[] = $LANG->line('select_admin_template');
			}
			
			if (trim($data['admin_email_address']) != '' && ! $REGX->valid_email($data['admin_email_address']))
			{
				$error[] = $LANG->line('invalid_email');
			}
			
			
			/** ---------------------------------
			/**  Do we have an error to display?
			/** ---------------------------------*/
	
			 if (count($error) > 0)
			 {
				$msg = '';
				
				foreach($error as $val)
				{
					$msg .= $DSP->qdiv('itemWrapper', $val);  
				}
				
				return $DSP->error_message($msg);
			 }   
					
			/** ---------------------------------
			/**  Do our insert or update
			/** ---------------------------------*/
							
			if ($new == 'y')
			{
				$data['item_id'] = '';
				$DB->query($DB->insert_string('exp_simple_commerce_items', $data));
			}
			else
			{
            	$DB->query($DB->update_string('exp_simple_commerce_items', $data, "entry_id = '$id'"));
            }
		}
		
		$FNS->clear_caching('page');
        
        if ($this->perform_redirects === TRUE)
        {
   			if ($new == 'y')
			{
				$FNS->redirect(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_items');
			}
			else
			{
				$FNS->redirect(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_items');
			}
			
			exit;
		}
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Edit Store Items
    /** -------------------------------------------*/

	function edit_items($msg = '')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB, $IN;
                        
        $DSP->title  = $LANG->line('simple_commerce_module_name');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce', $LANG->line('simple_commerce_module_name'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('edit_items')); 
        
        $r = $DSP->qdiv('tableHeading', $LANG->line('simple_commerce_module_name') . ' - '.$LANG->line('edit_items')); 
        
        if ($msg != '')
        {
			$r .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
        
        /** -------------------------------------------
    	/**  Either Show Search Form or Process Entries
    	/** -------------------------------------------*/
        
        if ($IN->GBL('toggle', 'POST') !== FALSE OR $IN->GBL('entry_id') !== FALSE)
        {
        	$entry_ids = array();
        	
        	if ($IN->GBL('entry_id') !== FALSE)
        	{
        		$entry_ids[] = $DB->escape_str($IN->GBL('entry_id'));
        	}
        	else
        	{
        		foreach ($_POST as $key => $val)
        		{        
            		if (strstr($key, 'toggle') AND ! is_array($val))
            		{
            			if ($val != '' && is_numeric($val))
            			{
							$entry_ids[] = $DB->escape_str($val);
						}
            		}        
        		}
        	}
        	
        	
			if (sizeof($entry_ids) == 0)
        	{
        		unset($_POST['toggle']); 
        		unset($_POST['entry_id']); 
        		unset($_GET['entry_id']);
        		return $this->edit_items($LANG->line('invalid_entries'));
        	}
        	
        	/** -------------------------------------------
    		/**  Valid Entries Selected?
    		/** -------------------------------------------*/
    		
    		$query = $DB->query("SELECT sc.*, wt.title FROM exp_simple_commerce_items sc, exp_weblog_titles wt 
	        					 WHERE sc.entry_id = wt.entry_id
	        					 AND sc.entry_id IN ('".implode("','", $entry_ids)."')");
        	
        	if ($query->num_rows == 0)
        	{
        		unset($_POST['toggle']); 
        		unset($_POST['entry_id']); 
        		unset($_GET['entry_id']);
        		return $this->edit_items($LANG->line('invalid_entries'));
        	}
        	
        	if ($IN->GBL('action') == 'delete')
        	{
        		$DSP->title  = $LANG->line('simple_commerce_module_name');
        		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce', $LANG->line('simple_commerce_module_name'));
        		$DSP->crumb .= $DSP->crumb_item($LANG->line('delete_items_confirm'));
        		
				$DSP->body = $DSP->delete_confirmation(
												array(
														'url'		=> 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=delete_items',
														'heading'	=> 'delete_items_confirm',
														'message'	=> 'delete_items_confirm',
														'item'		=> '',
														'extra'		=> '',
														'hidden'	=> array('entry_ids' => implode('|', $entry_ids))
													)
												);	
												
				return;
        	}
        	else
			{	
				$r .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=updating_items'));	
				
				foreach($query->result as $row)
				{
					$r .= $this->modify_entry_row($row['entry_id'], $row['title'], $row);
				}
				
				$r .= $DSP->qdiv('itemWrapperTop', 
								 $DSP->input_submit($LANG->line((sizeof($entry_ids) == 1) ? 'update_item' : 'update_items' ))).
					  $DSP->form_close();
			}
        }
        else
        {
        	$DSP->right_crumb($LANG->line('add_items'), BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=add_item');
        	
        	$query = $DB->query("SELECT COUNT(*) AS count FROM exp_simple_commerce_items");
        
        	$total = $query->row['count'];
        
        	if ($total == 0)
        	{
        		$r .= $DSP->qdiv('box', $DSP->qdiv('highlight', $LANG->line('no_store_items')));
        	
        	    $DSP->body .= $r;        
	
	            return;
	        }
	        
	        if ( ! $rownum = $IN->GBL('rownum', 'GP'))
	        {        
	            $rownum = 0;
	        }
	        
	        $perpage = 100;
	        
	        $query = $DB->query("SELECT sc.*, wt.title FROM exp_simple_commerce_items sc, exp_weblog_titles wt 
	        					 WHERE sc.entry_id = wt.entry_id
	        					 ORDER BY item_id desc LIMIT $rownum, $perpage");
	        
	        $r .= $DSP->toggle();
	        
	        $r .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_items', 'name' => 'target', 'id' => 'target'));
			
	        $r .= $DSP->table('tableBorder', '0', '', '100%').
	              $DSP->tr().
	              $DSP->table_qcell('tableHeadingAlt', 
	                                array(  $LANG->line('entry_title'),
	                                        $LANG->line('regular_price'),
	                                        $LANG->line('sale_price'),
											$LANG->line('use_sale_price'),
											$LANG->line('item_purchases'),
											$DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\""),
										 )
									).
				  $DSP->tr_c();
	
			$i = 0;
			
			foreach($query->result as $row)
			{
				$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
						  
				$r .= $DSP->tr();
				
				$r .= $DSP->table_qcell($style, $DSP->qdiv(	'defaultBold', 
															$DSP->anchor(BASE.AMP.'C=modules'.
																			  AMP.'M=simple_commerce'.
																			  AMP.'P=edit_items'.
																			  AMP.'entry_id='.$row['entry_id'], 
																		 $row['title'])),
															'25%');
				
				$r .= $DSP->table_qcell($style, $row['item_regular_price'], '20%');
				
				$r .= $DSP->table_qcell($style, $row['item_sale_price'], '20%');
				
				$r .= $DSP->table_qcell($style, $row['item_use_sale'], '15%');
				
				$r .= $DSP->table_qcell($style, $row['item_purchases'], '15%');
				
				$r .= $DSP->table_qcell($style, $DSP->input_checkbox('toggle[]', $row['entry_id']), '5%');
				
				$r .= $DSP->tr_c();
			}
			
			$r .= $DSP->table_c();
				 
			$r .= $DSP->qdiv('defaultRight', 
							 $DSP->qdiv('itemWrapper', 
							 			$DSP->input_submit($LANG->line('submit')).NBS.
							 			$DSP->input_select_header('action').
							 			$DSP->input_select_option('edit', $LANG->line('edit_selected')).
							 			$DSP->input_select_option('delete', $LANG->line('delete_selected')).
							 			$DSP->input_select_footer()
							 			)
							 );
        	      
			$r .= $DSP->form_close();     
	
			// Pass the relevant data to the paginate class so it can display the "next page" links
			
			$r .=  $DSP->div('itemWrapper').
				   $DSP->pager(
								BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_items',
								$total,
								$perpage,
								$rownum,
								'rownum'
							  ).
				  $DSP->div_c();
	
		}
		
		$DSP->body .= $r;
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Delete Store Items
    /** -------------------------------------------*/

	function delete_items()
	{
		global $DB, $IN, $LANG;
        
        /** -------------------------------------------
    	/**  Either Show Search Form or Process Entries
    	/** -------------------------------------------*/
        
        if ($IN->GBL('entry_ids', 'POST') !== FALSE)
        {
        	$entry_ids = array();
        	
        	foreach(explode('|', $IN->GBL('entry_ids')) as $id)
        	{
        		$entry_ids[] = $DB->escape_str($id);
        	}
        
        	$DB->query("DELETE FROM exp_simple_commerce_items
        				WHERE entry_id IN ('".implode("','", $entry_ids)."')");
	    }
	    
		$this->edit_items($LANG->line('items_deleted'));
	}
	/* END */
	
	/** -------------------------------------------
    /**  Add Email Template
    /** -------------------------------------------*/

	function add_email($msg = '')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB, $SESS, $IN, $REGX;
		
		/** -------------------------------------------
    	/**  Must be Assigned to Weblogs
    	/** -------------------------------------------*/
                        
        $DSP->title  = $LANG->line('simple_commerce_module_name');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce', $LANG->line('simple_commerce_module_name'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('add_emails')); 
        
        if ($msg != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
	
		$r  = $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=adding_email'));	
		$r .= $DSP->qdiv('tableHeading', $LANG->line('add_email'));
			
		$data = array(	'email_id'		=> "",
						'email_name'	=> "",
						'email_subject'	=> "",
						'email_body'	=> ""
					 );
			
		$r .= $this->modify_email_row($data, $LANG->line('add_email'), TRUE);
	
		$r .= $DSP->qdiv('itemWrapperTop', 
						 $DSP->input_submit($LANG->line('add_email'))).
			  $DSP->form_close();
        
        $DSP->body  = $r;
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Abstracted Display of Email Templates
    /** -------------------------------------------*/
	
	function modify_email_row($data, $title='', $instructions=FALSE)
	{
		global $DSP, $REGX, $LANG, $DB, $PREFS;
		
		if ( ! is_array($data))
		{
			$data = array(	'email_id'		=> "",
							'email_name'	=> "",
							'email_subject'	=> "",
							'email_body'	=> ""
						 );
		}
		
		$r = '';
		$r .= $DSP->input_hidden('email_id['.$data['email_id'].']', $data['email_id']);
				
		$r .= NL.'<div class="publishTabWrapper">';	
		$r .= NL.'<div class="publishBox">';
			
		$r .= NL."<table class='clusterBox' border='0' cellpadding='0' cellspacing='0' style='width:99%'><tr>";	
			
		$r .= NL.'<td class="publishItemWrapper" valign="top" style="width:60%;">'.BR;
			
		$r .= $DSP->div('clusterLineR').$DSP->heading($title, 5);
		
		$r .= BR.
			  $DSP->qdiv('defaultPadBold', $LANG->line('email_name')).
			  $DSP->input_text('email_name['.$data['email_id'].']', $data['email_name'], '20', '75', 'input', '95%');
		
		$r .= $DSP->qdiv('defaultSmall', NBS);
		$r .= $DSP->qdiv('defaultPadBold', $LANG->line('email_subject')).
			  $DSP->input_text('email_subject['.$data['email_id'].']', $data['email_subject'], '20', '75', 'input', '95%');
			  
		$r .= $DSP->qdiv('defaultSmall', NBS);
		$r .= $DSP->qdiv('defaultPadBold', $LANG->line('email_body')).
			  $DSP->input_textarea('email_body['.$data['email_id'].']', $data['email_body'], '20');
			  
		$r .= $DSP->div_c();
		
		$r .= '</td>';
			
		$r .= NL.'<td class="publishItemWrapper" valign="top" style="width:40%;">'.BR;
		
		if ($instructions === TRUE)
		{
			$r .= $DSP->heading($LANG->line('email_instructions'), 5);
		
			$r .= $DSP->qdiv('itemWrapper', $LANG->line('add_email_instructions'));
		
			$possible_post		= array('business', 'receiver_email', 'receiver_id', 'item_name', 
    									 'item_number', 'quantity', 'invoice', 'memo', 
    									 'tax', 'mc_gross', 'mc_fee', 
    									 'mc_currency',
    									 '',
    									 'option_name1', 'option_selection1', 'option_name2', 
    									 'option_selection2',
    									 '',
    									 'payment_gross', 'payment_fee', 
    									 'payment_status', 'payment_type',
    									 'payment_date', 'txn_id', 'txn_type', 
    									 '',
    									 'first_name', 'last_name', 'member_id', 'screen_name',
    									 'payer_business_name', 'payer_id', 'payer_email', 'payer_status',
    									 'address_name', 'address_street', 'address_country_code',
    									 'address_city', 'address_state', 'address_zip', 
    									 'address_country', 'address_status',
    									 'verify_sign');
    	
    		foreach($possible_post as $var)
    		{
    			$r .= $DSP->qdiv('default', ($var == '') ? NBS : NBS.NBS.LD.$var.RD);
    		}
    	}
		
		$r .= '</td>';
			
		$r .= "</tr></table>";
			
		$r .= $DSP->div_c();
		$r .= $DSP->div_c();
		
		return $r;
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Modify Store Items - Add/Update
    /** -------------------------------------------*/
    
    function adding_email()		{ $this->modify_emails(); 	}
    function update_emails()	{ $this->modify_emails('n');	}

	function modify_emails($new = 'y')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB, $SESS, $REGX;
		
		if ( ! is_array($_POST['email_id']))
		{
            return $DSP->no_access_message();
		}
		
		/** -------------------------------------------
		/**  Valid Email Templates Selected?
		/** -------------------------------------------*/
		
		if ($new == 'y')
		{
			$email_ids = array('0');
		}
		else
		{
			
			$email_ids = array();
			
			foreach ($_POST['email_id'] as $id)
			{
				$email_ids[] = $DB->escape_str($id);
			}
				
			$query = $DB->query("SELECT email_id FROM exp_simple_commerce_emails WHERE email_id IN ('".implode("','", $email_ids)."')");
				
			$email_ids = array();
				
			if ($query->num_rows > 0)
			{
				foreach($query->result as $row)
				{
					$email_ids[$row['email_id']] = $row['email_id'];
				}
			}
				
			if (sizeof($email_ids) == 0)
			{
				unset($_POST['email_id']);
				return $this->add_email($LANG->line('invalid_emails'));
			}
		}
        
		foreach($email_ids as $id)
		{
			$data = array(
							'email_id'			=> $_POST['email_id'][$id],
							'email_name'		=> $_POST['email_name'][$id],
							'email_subject'		=> $_POST['email_subject'][$id],
							'email_body'		=> $_POST['email_body'][$id],
							);
        
			$error = array();
			
			if (trim($data['email_name']) == '' OR trim($data['email_subject']) == '' OR trim($data['email_body']) == '')
			{
				$error[] = $LANG->line('fields_left_blank');
			}
			
			
			/** ---------------------------------
			/**  Do we have an error to display?
			/** ---------------------------------*/
	
			 if (count($error) > 0)
			 {
				$msg = '';
				
				foreach($error as $val)
				{
					$msg .= $DSP->qdiv('itemWrapper', $val);  
				}
				
				return $DSP->error_message($msg);
			 }   
					
			/** ---------------------------------
			/**  Do our insert or update
			/** ---------------------------------*/
							
			if ($new == 'y')
			{
				$data['email_id'] = '';
				$DB->query($DB->insert_string('exp_simple_commerce_emails', $data));
			}
			else
			{
            	$DB->query($DB->update_string('exp_simple_commerce_emails', $data, "email_id = '$id'"));
            }
		}
        
        if ($this->perform_redirects === TRUE)
		{
			if ($new == 'y')
			{
				$FNS->redirect(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_emails');
			}
			else
			{
				$FNS->redirect(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_emails');
			}
			
			exit;
		}
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Delete Email Templates
    /** -------------------------------------------*/

	function delete_emails()
	{
		global $DB, $IN, $LANG;
        
        /** -------------------------------------------
    	/**  Either Show Search Form or Process Entries
    	/** -------------------------------------------*/
        
        if ($IN->GBL('email_ids', 'POST') !== FALSE)
        {
        	$email_ids = array();
        	
        	foreach(explode('|', $IN->GBL('email_ids')) as $id)
        	{
        		$email_ids[] = $DB->escape_str($id);
        	}
        
        	$DB->query("DELETE FROM exp_simple_commerce_emails
        				WHERE email_id IN ('".implode("','", $email_ids)."')");
	    }
	    
		$this->edit_emails($LANG->line('emails_deleted'));
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Edit Store Items
    /** -------------------------------------------*/

	function edit_emails($msg = '')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB, $IN;
                        
        $DSP->title  = $LANG->line('simple_commerce_module_name');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce', $LANG->line('simple_commerce_module_name'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('edit_email_templates')); 
        
        $r = $DSP->qdiv('tableHeading', $LANG->line('simple_commerce_module_name')); 
        
        if ($msg != '')
        {
			$r .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
        
		/** -------------------------------------------
    	/**  Either Show Search Form or Process Entries
    	/** -------------------------------------------*/
        
        if ($IN->GBL('toggle', 'POST') !== FALSE OR $IN->GBL('email_id') !== FALSE)
        {
        	$email_ids = array();
        	
        	if ($IN->GBL('email_id') !== FALSE)
        	{
        		$email_ids[] = $DB->escape_str($IN->GBL('email_id'));
        	}
        	else
        	{
        		foreach ($_POST as $key => $val)
        		{        
            		if (strstr($key, 'toggle') AND ! is_array($val))
            		{
            			if ($val != '' && is_numeric($val))
            			{
							$email_ids[] = $DB->escape_str($val);
						}
            		}        
        		}
        	}
        	
        	/** -------------------------------------------
    		/**  Weed Out Any Entries that are already items
    		/** -------------------------------------------*/
    		
    		$query = $DB->query("SELECT * FROM exp_simple_commerce_emails WHERE email_id IN ('".implode("','", $email_ids)."')");
    		
    		if ($query->num_rows == 0)
        	{
        		unset($_POST['toggle']); 
        		unset($_POST['email_id']); 
        		unset($_GET['email_id']);
        		return $this->add_email($LANG->line('invalid_entries'));
        	}
        	
        	if ($IN->GBL('action') == 'delete')
        	{
        		$DSP->title  = $LANG->line('simple_commerce_module_name');
        		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce', $LANG->line('simple_commerce_module_name'));
        		$DSP->crumb .= $DSP->crumb_item($LANG->line('delete_emails_confirm'));
        		
				$DSP->body = $DSP->delete_confirmation(
												array(
														'url'		=> 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=delete_emails',
														'heading'	=> 'delete_emails_confirm',
														'message'	=> 'delete_emails_confirm',
														'item'		=> '',
														'extra'		=> '',
														'hidden'	=> array('email_ids' => implode('|', $email_ids))
													)
												);	
												
				return;
        	}
        	else
        	{	
				/** -------------------------------------------
				/**  Finally!  We can do something!
				/** -------------------------------------------*/
			
				$r  = $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=update_emails'));	
				$r .= $DSP->qdiv('tableHeading', $LANG->line((sizeof($email_ids) == 1) ? 'update_email' : 'update_emails' ));
					
				foreach ($query->result as $key => $row)
				{
					$r .= $this->modify_email_row($row, (sizeof($email_ids) == 1) ? 'update_email' : 'update_emails', ($key == 0) ? TRUE : FALSE);
				}
			
				$r .= $DSP->qdiv('itemWrapperTop', 
								 $DSP->input_submit($LANG->line((sizeof($email_ids) == 1) ? 'update_email' : 'update_emails' ))).
					  $DSP->form_close();
			}
        }
        else
        {
        	$DSP->right_crumb($LANG->line('add_email'), BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=add_email');
        	
			/** -------------------------------------------
			/**  Check for pagination
			/** -------------------------------------------*/
			
			$query = $DB->query("SELECT COUNT(*) AS count FROM exp_simple_commerce_emails");
		
			$total = $query->row['count'];
		
			if ($total == 0)
			{
				$r .= $DSP->qdiv('box', $DSP->qdiv('highlight', $LANG->line('no_email_templates')));
			
				$DSP->body .= $r;        
	
				return;
			}
			
			if ( ! $rownum = $IN->GBL('rownum', 'GP'))
			{        
				$rownum = 0;
			}
			
			$perpage = 100;
			
			$query = $DB->query("SELECT email_id, email_name FROM exp_simple_commerce_emails
								 ORDER BY email_name desc LIMIT $rownum, $perpage");
			
			$r .= $DSP->toggle();
			
			$r .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_emails', 'name' => 'target', 'id' => 'target'));
			
			$r .= $DSP->table('tableBorder', '0', '', '100%').
				  $DSP->tr().
				  $DSP->table_qcell('tableHeadingAlt', 
									array(  $LANG->line('email_name'),
											$DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\""),
										 )
									).
				  $DSP->tr_c();
	
			$i = 0;
			
			foreach($query->result as $row)
			{
				$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
						  
				$r .= $DSP->tr().
						$DSP->table_qcell($style, $DSP->qdiv('defaultBold', 
															 $DSP->anchor(BASE.AMP.'C=modules'.
																			   AMP.'M=simple_commerce'.
																			   AMP.'P=edit_emails'.
																			   AMP.'email_id='.$row['email_id'], 
																		 	   $row['email_name'])), '90%').
																		 	
						$DSP->table_qcell($style, $DSP->input_checkbox('toggle[]', $row['email_id']), '10%').
					  $DSP->tr_c();
			}
			
			$r .= $DSP->table_c();
				 
			$r .= $DSP->qdiv('defaultRight', 
							 $DSP->qdiv('itemWrapper', 
							 			$DSP->input_submit($LANG->line('submit')).NBS.
							 			$DSP->input_select_header('action').
							 			$DSP->input_select_option('edit', $LANG->line('edit_selected')).
							 			$DSP->input_select_option('delete', $LANG->line('delete_selected')).
							 			$DSP->input_select_footer()
							 			)
							 );         
			
			$r .= $DSP->form_close();     
	
			// Pass the relevant data to the paginate class so it can display the "next page" links
			
			$r .=  $DSP->div('itemWrapper').
				   $DSP->pager(
								BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_emails',
								$total,
								$perpage,
								$rownum,
								'rownum'
							  ).
				  $DSP->div_c();
		}
		
		$DSP->body .= $r;
	}
	/* END */
	
	
/* ======================================================
/*  PURCHASES
/* ======================================================
	
	/** -------------------------------------------
    /**  Add Purchase
    /** -------------------------------------------*/

	function add_purchase($msg = '')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB, $SESS, $IN, $REGX;
		
		/** -------------------------------------------
    	/**  Must be Assigned to Weblogs
    	/** -------------------------------------------*/
                        
        $DSP->title  = $LANG->line('simple_commerce_module_name');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce', $LANG->line('simple_commerce_module_name'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('add_purchase')); 
        
        if ($msg != '')
        {
			$DSP->body .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
	
		$r  = $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=adding_purchase'));	
		$r .= $DSP->qdiv('tableHeading', $LANG->line('add_purchase'));
			
		$r .= $this->modify_purchase_row('', $LANG->line('add_purchase'), TRUE);
	
		$r .= $DSP->qdiv('itemWrapperTop', 
						 $DSP->input_submit($LANG->line('add_purchase'))).
			  $DSP->form_close();
        
        $DSP->body  = $r;
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Abstracted Display of Purchases
    /** -------------------------------------------*/
	
	function modify_purchase_row($data, $title='', $instructions=FALSE)
	{
		global $DSP, $REGX, $LANG, $DB, $PREFS, $LOC;
		
		if ( ! is_array($data))
		{
			$data = array(	'purchase_id'	=> "",
							'txn_id'		=> "",
							'member_id'		=> "",
							'item_id'		=> "",
							'purchase_date'	=> "",
							'item_cost'		=> "",
						 );
		}
		
		$r = '';
		$r .= $DSP->input_hidden('purchase_id['.$data['purchase_id'].']', $data['purchase_id']);
				
		$r .= NL.'<div class="publishTabWrapper">';	
		$r .= NL.'<div class="publishBox">';
			
		$r .= NL."<table class='clusterBox' border='0' cellpadding='0' cellspacing='0' style='width:99%'><tr>";	
			
		$r .= NL.'<td class="publishItemWrapper" valign="top" style="width:60%;">'.BR;
			
		$r .= $DSP->div('clusterLineR').$DSP->heading($title, 5);
		
		/** -------------------------------
		/**  Purchase TXN ID
		/** -------------------------------*/
		
		$r .= BR.
			  $DSP->qdiv('defaultPadBold', $LANG->line('txn_id')).
			  $DSP->input_text('txn_id['.$data['purchase_id'].']', $data['txn_id'], '20', '75', 'input', '95%');
		
		/** -------------------------------
		/**  Purchaser
		/** -------------------------------*/
		
		if ($data['member_id'] != '' && is_numeric($data['member_id']))
		{
			$query = $DB->query("SELECT screen_name FROM exp_members WHERE member_id = '".$DB->escape_str($data['member_id'])."'");
			
			$screen_name = ($query->num_rows == 0) ? $LANG->line('member_not_found') : $query->row['screen_name'];
		}
		else
		{
			$screen_name = '';
		}
		
		$r .= $DSP->qdiv('defaultSmall', NBS).
			  $DSP->qdiv('defaultPadBold', $LANG->line('purchaser_screen_name')).
			  $DSP->input_text('screen_name['.$data['purchase_id'].']', $screen_name, '20', '75', 'input', '95%');
			  
		/** -------------------------------
		/**  Item Purchased
		/** -------------------------------*/
		
		$query = $DB->query("SELECT sc.item_id, wt.title FROM exp_simple_commerce_items sc, exp_weblog_titles wt 
	        				 WHERE sc.entry_id = wt.entry_id");
		
		$r .= $DSP->qdiv('defaultSmall', NBS).
			  $DSP->qdiv('defaultPadBold', $LANG->line('item_purchased')).
			  $DSP->input_select_header('item_id['.$data['purchase_id'].']').
			  $DSP->input_select_option('null', $LANG->line('choose_item'));
		
		foreach($query->result as $row)
		{
			$r .= $DSP->input_select_option($row['item_id'], $row['title'], ($row['item_id'] == $data['item_id']) ? 'y' : '');
		}
		
		$r .= $DSP->input_select_footer();
		
		/** -------------------------------
		/**  Date Purchased
		/** -------------------------------*/
		
		$purchase_date = ($data['purchase_date'] == 0) ? $LOC->set_human_time() : $LOC->set_human_time($data['purchase_date']);
		
		$r .= $DSP->qdiv('defaultSmall', NBS).
			  $DSP->qdiv('defaultPadBold', $LANG->line('date_purchased')).
			  $DSP->input_text('purchase_date['.$data['purchase_id'].']', $purchase_date, '20', '75', 'input', '95%');
			  
		/** -------------------------------
		/**  Item Cost
		/** -------------------------------*/
		
		$r .= $DSP->qdiv('defaultSmall', NBS).
			  $DSP->qdiv('defaultPadBold', $LANG->line('item_cost')).
			  $DSP->input_text('item_cost['.$data['purchase_id'].']', $data['item_cost'], '20', '75', 'input', '95%');
			  
		/** -------------------------------
		/**  Perform Actions Checkbox
		/** -------------------------------*/
		
		/*
		$r .= $DSP->qdiv('defaultSmall', NBS).
			  $DSP->qdiv('defaultPadBold', 
			  			$DSP->input_checkbox('perform_actions['.$data['purchase_id'].']', 'y', ($data['purchase_id'] == '') ? 'y' : '').
			  			NBS.$LANG->line('perform_item_actions'));  
		*/
			  
		$r .= $DSP->div_c();
		
		$r .= '</td>';
			
		$r .= NL.'<td class="publishItemWrapper" valign="top" style="width:40%;">'.BR;
		$r .= '</td>';
			
		$r .= "</tr></table>";
			
		$r .= $DSP->div_c();
		$r .= $DSP->div_c();
		
		return $r;
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Modify Store Items - Add/Update
    /** -------------------------------------------*/
    
    function adding_purchase()	{ $this->modify_purchases(); 	}
    function update_purchases()	{ $this->modify_purchases('n');	}

	function modify_purchases($new = 'y')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB, $SESS, $REGX, $LOC;
		
		if ( ! is_array($_POST['purchase_id']))
		{
            return $DSP->no_access_message();
		}
		
		/** -------------------------------------------
		/**  Valid Purchases Selected?
		/** -------------------------------------------*/
		
		if ($new == 'y')
		{
			$purchase_ids = array('0');
		}
		else
		{
			
			$purchase_ids = array();
			
			foreach ($_POST['purchase_id'] as $id)
			{
				$purchase_ids[] = $DB->escape_str($id);
			}
				
			$query = $DB->query("SELECT purchase_id FROM exp_simple_commerce_purchases WHERE purchase_id IN ('".implode("','", $purchase_ids)."')");
				
			$purchase_ids = array();
				
			if ($query->num_rows > 0)
			{
				foreach($query->result as $row)
				{
					$purchase_ids[$row['purchase_id']] = $row['purchase_id'];
				}
			}
				
			if (sizeof($purchase_ids) == 0)
			{
				unset($_POST['purchase_id']);
				return $this->add_purchase($LANG->line('invalid_purchases'));
			}
		}
        
		foreach($purchase_ids as $id)
		{
			$error = array();
			
			/** ---------------------------
			/**  Blank Fields
			/** ---------------------------*/
			
			if (trim($_POST['purchase_date'][$id]) == '' OR trim($_POST['txn_id'][$id]) == '' OR trim($_POST['item_cost'][$id]) == '' OR ! is_numeric($_POST['item_id'][$id]))
			{
				$error[] = $LANG->line('fields_left_blank');
			}
			
			/** ---------------------------
			/**  Valid Member
			/** ---------------------------*/
			
			$query = $DB->query("SELECT member_id FROM exp_members WHERE screen_name = '".$DB->escape_str($_POST['screen_name'][$id])."'");
			
			if ($query->num_rows == 0)
			{
				$error[] = $LANG->line('member_not_found');
			}
			else
			{
				$member_id = $query->row['member_id'];
			}
			
			/** ---------------------------
			/**  Date Formatting
			/** ---------------------------*/
			
			$_POST['purchase_date'][$id] = $LOC->convert_human_date_to_gmt($_POST['purchase_date'][$id]);
                     
        	if ( ! is_numeric($_POST['purchase_date'][$id])) 
        	{ 
        	    $error[] = $LANG->line('invalid_date_formatting');
        	}
        	
        	/** ---------------------------
			/**  Item Cost
			/** ---------------------------*/
			
			$_POST['item_cost'][$id] = str_replace('$', '', $_POST['item_cost'][$id]);
			
			if ( ! is_numeric(str_replace('.', '', trim($_POST['item_cost'][$id]))))
			{
				$error[] = $LANG->line('invalid_amount');
			}
        	
        	/** ---------------------------------
			/**  Do we have an error to display?
			/** ---------------------------------*/
	
			 if (count($error) > 0)
			 {
				$msg = '';
				
				foreach($error as $val)
				{
					$msg .= $DSP->qdiv('itemWrapper', $val);  
				}
				
				return $DSP->error_message($msg);
			 }
		
			$data = array(
							'purchase_id'		=> $_POST['purchase_id'][$id],
							'txn_id'			=> $_POST['txn_id'][$id],
							'purchase_date'		=> $_POST['purchase_date'][$id],
							'member_id'			=> $member_id,
							'item_id'			=> $_POST['item_id'][$id],
							'item_cost'			=> $_POST['item_cost'][$id],
							);
					
			/** ---------------------------------
			/**  Do our insert or update
			/** ---------------------------------*/
							
			if ($new == 'y')
			{
				$data['purchase_id'] = '';
				$DB->query($DB->insert_string('exp_simple_commerce_purchases', $data));
				$DB->query("UPDATE exp_simple_commerce_items SET item_purchases = item_purchases + 1 WHERE item_id = '".$data['item_id']."'");
			}
			else
			{
            	$DB->query($DB->update_string('exp_simple_commerce_purchases', $data, "purchase_id = '$id'"));
            }
		}
        
        if ($this->perform_redirects === TRUE)
        {
			if ($new == 'y')
			{
				$FNS->redirect(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_purchases');
			}
			else
			{
				$FNS->redirect(BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_purchases');
			}
			
			exit;
		}
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Delete Purchases
    /** -------------------------------------------*/

	function delete_purchases()
	{
		global $DB, $IN, $LANG;
        
        /** -------------------------------------------
    	/**  Either Show Search Form or Process Entries
    	/** -------------------------------------------*/
        
        if ($IN->GBL('purchase_ids', 'POST') !== FALSE)
        {
        	$purchase_ids = array();
        	
        	foreach(explode('|', $IN->GBL('purchase_ids')) as $id)
        	{
        		$purchase_ids[] = $DB->escape_str($id);
        	}
        	
        	$query = $DB->query("SELECT DISTINCT item_id FROM exp_simple_commerce_purchases
        						 WHERE purchase_id IN ('".implode("','", $purchase_ids)."')");
        
        	if ($query->num_rows > 0)
        	{
        		$DB->query("DELETE FROM exp_simple_commerce_purchases
        					WHERE purchase_id IN ('".implode("','", $purchase_ids)."')");
        		
        		foreach($query->result as $row)
        		{
        			$result = $DB->query("SELECT COUNT(*) AS count FROM exp_simple_commerce_purchases WHERE item_id = ".$row['item_id']);
        			
        			$DB->query($DB->update_string('exp_simple_commerce_items', array('item_purchases' => $result->row['count']), "item_id = ".$row['item_id']));
        		}			
        	}
        				
	    }
	    
		$this->edit_purchases($LANG->line('purchases_deleted'));
	}
	/* END */
	
	
	/** -------------------------------------------
    /**  Edit Purchases
    /** -------------------------------------------*/

	function edit_purchases($msg = '')
	{
		global $DSP, $LANG, $PREFS, $FNS, $DB, $IN, $LOC;
                        
        $DSP->title  = $LANG->line('simple_commerce_module_name');
        $DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce', $LANG->line('simple_commerce_module_name'));
        $DSP->crumb .= $DSP->crumb_item($LANG->line('edit_purchases')); 
        
        $r = $DSP->qdiv('tableHeading', $LANG->line('simple_commerce_module_name') . ' - '.$LANG->line('edit_purchases')); 
        
        if ($msg != '')
        {
			$r .= $DSP->qdiv('successBox', $DSP->qdiv('success', $msg));
        }
        
		/** -------------------------------------------
    	/**  Either Show Search Form or Process Entries
    	/** -------------------------------------------*/
        
        if ($IN->GBL('toggle', 'POST') !== FALSE OR $IN->GBL('purchase_id') !== FALSE)
        {
        	$purchase_ids = array();
        	
        	if ($IN->GBL('purchase_id') !== FALSE)
        	{
        		$purchase_ids[] = $DB->escape_str($IN->GBL('purchase_id'));
        	}
        	else
        	{
        		foreach ($_POST as $key => $val)
        		{        
            		if (strstr($key, 'toggle') AND ! is_array($val))
            		{
            			if ($val != '' && is_numeric($val))
            			{
							$purchase_ids[] = $DB->escape_str($val);
						}
            		}        
        		}
        	}
        	
        	/** -------------------------------------------
    		/**  Weed Out Any Entries that are already items
    		/** -------------------------------------------*/
    		
    		$query = $DB->query("SELECT * FROM exp_simple_commerce_purchases WHERE purchase_id IN ('".implode("','", $purchase_ids)."')");
    		
    		if ($query->num_rows == 0)
        	{
        		unset($_POST['toggle']); 
        		unset($_POST['purchase_id']); 
        		unset($_GET['purchase_id']);
        		return $this->add_purchase($LANG->line('invalid_entries'));
        	}
        	
        	if ($IN->GBL('action') == 'delete')
        	{
        		$DSP->title  = $LANG->line('simple_commerce_module_name');
        		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=modules'.AMP.'M=simple_commerce', $LANG->line('simple_commerce_module_name'));
        		$DSP->crumb .= $DSP->crumb_item($LANG->line('delete_purchases_confirm'));
        		
				$DSP->body = $DSP->delete_confirmation(
												array(
														'url'		=> 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=delete_purchases',
														'heading'	=> 'delete_purchases_confirm',
														'message'	=> 'delete_purchases_confirm',
														'item'		=> '',
														'extra'		=> '',
														'hidden'	=> array('purchase_ids' => implode('|', $purchase_ids))
													)
												);	
												
				return;
        	}
        	else
        	{	
				/** -------------------------------------------
				/**  Finally!  We can do something!
				/** -------------------------------------------*/
			
				$r  = $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=update_purchases'));	
				$r .= $DSP->qdiv('tableHeading', $LANG->line((sizeof($purchase_ids) == 1) ? 'update_purchase' : 'update_purchases'));
					
				foreach ($query->result as $key => $row)
				{
					$r .= $this->modify_purchase_row($row, '', ($key == 0) ? TRUE : FALSE);
				}
			
				$r .= $DSP->qdiv('itemWrapperTop', 
								 $DSP->input_submit($LANG->line((sizeof($purchase_ids) == 1) ? 'update_purchase' : 'update_purchases' ))).
					  $DSP->form_close();
			}
        }
        else
        {
        	$DSP->right_crumb($LANG->line('add_purchase'), BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=add_purchase');
        	
			/** -------------------------------------------
			/**  Check for pagination
			/** -------------------------------------------*/
			
			$query = $DB->query("SELECT COUNT(*) AS count FROM exp_simple_commerce_purchases");
		
			$total = $query->row['count'];
		
			if ($total == 0)
			{
				$r .= $DSP->qdiv('box', $DSP->qdiv('highlight', $LANG->line('no_purchases')));
			
				$DSP->body .= $r;        
	
				return;
			}
			
			if ( ! $rownum = $IN->GBL('rownum', 'GP'))
			{        
				$rownum = 0;
			}
			
			$perpage = 100;
			
			$query = $DB->query("SELECT scp.*, m.screen_name, wt.title 
								 FROM exp_simple_commerce_purchases scp, exp_simple_commerce_items sci, exp_members m, exp_weblog_titles wt
								 WHERE scp.item_id = sci.item_id
								 AND sci.entry_id = wt.entry_id
								 AND scp.member_id = m.member_id
								 ORDER BY scp.purchase_date desc LIMIT $rownum, $perpage");
			
			$r .= $DSP->toggle();
			
			$r .= $DSP->form_open(array('action' => 'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_purchases', 'name' => 'target', 'id' => 'target'));
			
			$r .= $DSP->table('tableBorder', '0', '', '100%').
				  $DSP->tr().
				  $DSP->table_qcell('tableHeadingAlt', 
									array(  $LANG->line('item_purchased'),
											$LANG->line('purchaser_screen_name'),
											$LANG->line('date_purchased'),
											$LANG->line('item_cost'),
											$DSP->input_checkbox('toggleflag', '', '', "onclick=\"toggle(this);\""),
										 )
									).
				  $DSP->tr_c();
	
			$i = 0;
			
			foreach($query->result as $row)
			{
				$style = ($i++ % 2) ? 'tableCellOne' : 'tableCellTwo';
						  
				$r .= $DSP->tr().
						$DSP->table_qcell($style, $DSP->qdiv('defaultBold', 
															 $DSP->anchor(BASE.AMP.'C=modules'.
																			   AMP.'M=simple_commerce'.
																			   AMP.'P=edit_purchases'.
																			   AMP.'purchase_id='.$row['purchase_id'], 
																		 	   $row['title'])
															),
												  '30%').
						$DSP->table_qcell($style, $DSP->anchor(BASE.AMP.'C=myaccount'.AMP.'id='.$row['member_id'] , $row['screen_name']), '30%').
						$DSP->table_qcell($style, $LOC->set_human_time($row['purchase_date']), '20%').
						$DSP->table_qcell($style, $row['item_cost'], '10%').
						$DSP->table_qcell($style, $DSP->input_checkbox('toggle[]', $row['purchase_id']), '10%').
					  $DSP->tr_c();
			}
			
			$r .= $DSP->table_c();
				 
			$r .= $DSP->qdiv('defaultRight', 
							 $DSP->qdiv('itemWrapper', 
							 			$DSP->input_submit($LANG->line('submit')).NBS.
							 			$DSP->input_select_header('action').
							 			$DSP->input_select_option('edit', $LANG->line('edit_selected')).
							 			$DSP->input_select_option('delete', $LANG->line('delete_selected')).
							 			$DSP->input_select_footer()
							 			)
							 );         
			
			$r .= $DSP->form_close();     
	
			// Pass the relevant data to the paginate class so it can display the "next page" links
			
			$r .=  $DSP->div('itemWrapper').
				   $DSP->pager(
								BASE.AMP.'C=modules'.AMP.'M=simple_commerce'.AMP.'P=edit_purchases',
								$total,
								$perpage,
								$rownum,
								'rownum'
							  ).
				  $DSP->div_c();
		}
		
		$DSP->body .= $r;
	}
	/* END */
	
	/** -------------------------------------------
    /**  Export Functions
    /** -------------------------------------------*/
    
    function export_purchases() { $this->export('purchases'); }
    function export_items() 	{ $this->export('items'); }

	function export($which='purchases')
	{
		global $DB;
		
		$tab  = ($this->export_type == 'csv') ? ',' : "\t"; 
		$cr	  = "\n"; 
		$data = '';
		
		if ($which == 'items')
		{
			$query = $DB->query("SELECT wt.title as item_name, sc.* FROM exp_simple_commerce_items sc, exp_weblog_titles wt 
	        					 WHERE sc.entry_id = wt.entry_id
	        					 ORDER BY item_name");
		}
		else
		{
			$query = $DB->query("SELECT wt.title AS item_purchased, m.screen_name AS purchaser, scp.*
								 FROM exp_simple_commerce_purchases scp, exp_simple_commerce_items sci, exp_members m, exp_weblog_titles wt
								 WHERE scp.item_id = sci.item_id
								 AND sci.entry_id = wt.entry_id
								 AND scp.member_id = m.member_id
								 ORDER BY scp.purchase_date desc");
		}
		
		if ($query->num_rows > 0)
		{
			foreach($query->row as $key => $value)
			{
				if ($key == 'paypal_details') continue;
				
				$data .= $key.$tab;
			}
				
			$data = trim($data).$cr; // Remove end tab and add carriage
				
			foreach($query->result as $row)
			{
				$datum = '';
				
				foreach($row as $key => $value)
				{
					$datum .= $value.$tab;
				}
					
				$data .= trim($datum).$cr;
			}
		}
		
		if (strstr($_SERVER['HTTP_USER_AGENT'], "MSIE"))
   		{
        	header('Content-Type: application/octet-stream');
        	header('Content-Disposition: inline; filename="'.$which.'.'.$this->export_type.'"');
        	header('Expires: 0');
        	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        	header('Pragma: public');
    	} 
    	else 
    	{
        	header('Content-Type: application/octet-stream');
        	header('Content-Disposition: attachment; filename="'.$which.'.'.$this->export_type.'"');
        	header('Expires: 0');
        	header('Pragma: no-cache');
    	}
	
		echo $data;
		exit;
	}
	/* END */
	


    /** -------------------------------------------
    /**  Module installer
    /** -------------------------------------------*/

    function simple_commerce_module_install()
    {
        global $DB;        
        
        $sql[] = "INSERT INTO exp_modules 
        		  (module_id, module_name, module_version, has_cp_backend) 
        		  VALUES 
        		  ('', 'Simple_commerce', '$this->version', 'y')";
        		  
    	$sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Simple_commerce', 'incoming_ipn')";
    	
    	$sql[] = "CREATE TABLE IF NOT EXISTS `exp_simple_commerce_items` (
  `item_id` int(8) unsigned NOT NULL auto_increment,
  `entry_id` int(8) unsigned NOT NULL,
  `item_enabled` char(1) NOT NULL default 'y',
  `item_regular_price` decimal(7,2) NOT NULL default '0.00',
  `item_sale_price` decimal(7,2) NOT NULL default '0.00',
  `item_use_sale` char(1) NOT NULL default 'n',
  `item_purchases` int(8) NOT NULL default '0',
  `new_member_group` int(8) NOT NULL default '0',
  `admin_email_address` varchar(200) NOT NULL,
  `admin_email_template` int(5) NOT NULL default '0',
  `customer_email_template` int(5) NOT NULL default '0',
  PRIMARY KEY  (`item_id`),
  KEY `entry_id` (`entry_id`)
)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `exp_simple_commerce_purchases` (
  `purchase_id` int(8) unsigned NOT NULL auto_increment,
  `txn_id` varchar(20) NOT NULL default '',
  `member_id` varchar(50) NOT NULL default '',
  `item_id` int(8) unsigned NOT NULL default '0',
  `purchase_date` int(12) unsigned NOT NULL default '0',
  `item_cost` decimal(10,2) NOT NULL default '0.00',
  `paypal_details` TEXT	NOT NULL default '',
  PRIMARY KEY  (`purchase_id`),
  KEY `item_id` (`item_id`),
  KEY `member_id` (`member_id`),
  KEY `txn_id` (`txn_id`)
)";

		$sql[] = "CREATE TABLE IF NOT EXISTS `exp_simple_commerce_emails` (
  `email_id` int(8) unsigned NOT NULL auto_increment,
  `email_name` varchar(50) NOT NULL default '',
  `email_subject` varchar(125) NOT NULL default '',
  `email_body` text NOT NULL,
  PRIMARY KEY  (`email_id`))";
    	

        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
        /** ----------------------------------------
		/**  Add a couple items to the config file
		/** ----------------------------------------*/
	  
		if ( ! class_exists('Admin'))
		{
			require PATH_CP.'cp.admin'.EXT;
		}
		
		Admin::append_config_file(array('sc_paypal_account' 	=> '',
										'sc_encrypt_buttons' 	=> 'n',
										'sc_certificate_id'		=> '',
										'sc_public_certificate' => '', 
										'sc_private_key'		=> '',
										'sc_paypal_certificate' => '',
										'sc_temp_path'			=> '/tmp'));
        
        return TRUE;
    }
    /* END */
    
    
    /** -------------------------------------------
    /**  Module de-installer
    /** -------------------------------------------*/

    function simple_commerce_module_deinstall()
    {
        global $DB;    

        $query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Simple_commerce'"); 
                
        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";        
        $sql[] = "DELETE FROM exp_modules WHERE module_name = 'Simple_commerce'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Simple_commerce'";
        $sql[] = "DROP TABLE IF EXISTS exp_simple_commerce_items";
        $sql[] = "DROP TABLE IF EXISTS exp_simple_commerce_purchases";
        $sql[] = "DROP TABLE IF EXISTS exp_simple_commerce_emails";
        

        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
        /** ----------------------------------------
		/**  Remove a couple items to the config file
		/** ----------------------------------------*/
	  
		if ( ! class_exists('Admin'))
		{
			require PATH_CP.'cp.admin'.EXT;
		}
		
		Admin::append_config_file('', array('sc_paypal_account',
											'sc_encrypt_buttons',
											'sc_certificate_id',
											'sc_public_certificate', 
											'sc_private_key',
											'sc_paypal_certificate',
											'sc_temp_path'));

        return TRUE;
    }
    /* END */
    
    
    
    /** -------------------------------------------
    /**  Module Update
    /** -------------------------------------------*/

    function simple_commerce_update_module()
    {
        global $DB;
        
        return TRUE;
    }
    /* END */
    
    
    
    /*
================
     NOTES
================
    
REQUIREMENTS
PayPal Premeire or Business Account
The accounts are free to sign up for and are needed to use the IPN (below). Click here to sign up:
https://www.paypal.com/cgi-bin/webscr?cmd=_registration-run 

PayPal IPN (Instant Payment Notification) Activiation
This is needed for the user's account to be upgraded automatically. To activate your IPN:
- Log into your PayPal account
- Click on the "Profile" tab
- Then click "Selling Preferences"
- Instant Payment Notification Preferences'
- From there you have to enter a URL for the IPN to talk to. 
This URL must be on your web site (i.e.-http://www.yoursite.com/ipn.asp). 
*/


}
/* END */
?>