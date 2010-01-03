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
 File: mod.simple_commerce.php
-----------------------------------------------------
 Purpose: Simple Commerce Output
=====================================================

*/


if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Simple_commerce {

    var $return_data		= '';
    var $debug				= FALSE;
    var $possible_post;
    var $post				= array();
    
    var $encrypt			= FALSE;
    var $certificate_id		= '';
    var $public_certificate	= '';
	var $private_key		= '';
	var $paypal_certificate	= '';
	var $temp_path			= '';

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function Simple_commerce()
    {
    	global $PREFS;
    	
    	$this->possible_post = array('business', 'receiver_email', 'receiver_id', 'item_name', 
    								 'item_number', 'quantity', 'invoice', 'custom', 'memo', 
    								 'tax', 'option_name1', 'option_selection1', 'option_name2', 
    								 'option_selection2', 'num_cart_items', 'mc_gross', 'mc_fee', 
    								 'mc_currency', 'payment_gross', 'payment_fee', 
    								 'payment_status', 'pending_reason', 'reason_code', 
    								 'payment_date', 'txn_id', 'txn_type', 'payment_type', 
    								 'first_name', 'last_name', 
    								 'payer_business_name', 'address_name', 'address_street', 
    								 'address_city', 'address_state', 'address_zip', 'address_country_code',
    								 'address_country', 'address_status', 'payer_email', 
    								 'payer_id', 'payer_status', 'member_id',
    								 'verify_sign', 'test_ipn');
    								 
    	if ($PREFS->ini('sc_encrypt_buttons') === 'y' && function_exists('openssl_pkcs7_sign'))
    	{
    		$this->encrypt = TRUE;
    		
    		foreach(array('certificate_id', 'public_certificate', 'private_key', 'paypal_certificate') as $val)
    		{
    			if ($PREFS->ini('sc_'.$val) === FALSE OR $PREFS->ini('sc_'.$val) == '')
    			{
    				$this->encrypt = FALSE;
    				break;
    			}
    			else
    			{
    				$this->$val = $PREFS->ini('sc_'.$val);
    			}
    		}
    		
    		// Not required
    		if ($this->encrypt === TRUE && $PREFS->ini('sc_temp_path') !== FALSE)
    		{
    			$this->temp_path = $PREFS->ini('sc_temp_path');
    		}
    		
    	}
    }
    /* END */
    
	/** ----------------------------------------
    /**  Output Item Info
    /** ----------------------------------------*/

    function purchase()
    {
    	global $TMPL, $DB, $FNS, $PREFS, $REGX, $SESS;
    	
    	if (($entry_id = $TMPL->fetch_param('entry_id')) === FALSE) return;
    	if (($success = $TMPL->fetch_param('success')) === FALSE) return;
    	
		$paypal_account = ( ! $PREFS->ini('sc_paypal_account')) ? $PREFS->ini('webmaster_email') : $PREFS->ini('sc_paypal_account');
		$cancel	 		= ( ! $TMPL->fetch_param('cancel'))  ? $FNS->fetch_site_index() : $TMPL->fetch_param('cancel');
		$currency		= ( ! $TMPL->fetch_param('currency'))  ? 'USD' : $TMPL->fetch_param('currency');
		$country_code	= ( ! $TMPL->fetch_param('country_code')) ? 'US' : strtoupper($TMPL->fetch_param('country_code'));
		$show_disabled  = ( $TMPL->fetch_param('show_disabled') == 'yes') ? TRUE : FALSE;
		
		if (substr($success, 0, 4) !== 'http')
		{
			$success = $FNS->create_url($success);
    	}
    	
    	if (substr($cancel, 0, 4) !== 'http')
		{
			$cancel = $FNS->create_url($cancel);
    	}
    	
		if ($show_disabled === TRUE)
		{
			$addsql = '';
		}
		else
		{
			$addsql = "AND sci.item_enabled = 'y' ";
		}
		
    	$query = $DB->query("SELECT wt.title AS item_name, sci.* 
    						 FROM exp_simple_commerce_items sci, exp_weblog_titles wt
    						 WHERE sci.entry_id = '".$DB->escape_str($entry_id)."'
							 {$addsql}
    						 AND sci.entry_id = wt.entry_id
    						 LIMIT 1");
    	
    	if ($query->num_rows == 0) return;
    	
    	$tagdata = $TMPL->tagdata;
    	
    	/** ----------------------------------------
		/**  Conditionals
		/** ----------------------------------------*/
		
		$tagdata = $FNS->prep_conditionals($tagdata, $query->row);
		
		if ($this->encrypt !== TRUE)
		{
			$query->row['item_name'] = str_replace(	array("&","<",">","\"", "'", "-"),
        				   							array("&amp;", "&lt;", "&gt;", "&quot;", "&#39;", "&#45;"),
        				   							$query->row['item_name']);
        }
		
		/* ----------------------------------------
		/*	SINGLE VARIABLES:
		/*		item_id
		/*		item_name
		/*		item_enabled
		/*		item_regular_price
		/*		item_sale_price
		/*		item_use_sale
		/*		item_purchases
		/*		buy_now_url
		/*		view_cart_url
		/*		add_to_cart_url
		/* ----------------------------------------*/
		
		foreach($query->row as $key => $value)
		{
			if (stristr($key, '_price'))
			{
				$tagdata = str_replace(LD.$key.RD, $this->round_money($value), $tagdata);
			}
			else
			{
				$tagdata = str_replace(LD.$key.RD, $value, $tagdata);
			}
		}
		
		/*
			_xclick => Buy Now
				- Only one item with item_name, item_number, and amount
			_cart => Add to a cart
				- One or more items with sequential fields:  item_name_1, item_number_1, amount_1
		*/
		
		/** ----------------------------------------
		/**  Buy Now
		/** ----------------------------------------*/
		
		$buy_now['action']			= ($this->debug === TRUE) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$buy_now['hidden_fields']	= array(
										'cmd'  				=> '_xclick',
										'upload'			=> "1",
										'business'			=> $paypal_account,
										'return'			=> str_replace(SLASH, '/', $success),
										'cancel_return'		=> str_replace(SLASH, '/', $cancel),
										'item_name'			=> $query->row['item_name'],
										'item_number'		=> $query->row['item_id'],
										'amount'			=> ($query->row['item_use_sale'] == 'y') ? $query->row['item_sale_price'] : $query->row['item_regular_price'],
										'lc'				=> $country_code,
										'currency_code'		=> $currency,
										'custom'			=> $SESS->userdata['member_id']
										);
		
		if ($this->encrypt === TRUE)
		{
			$url = $buy_now['action'].'?cmd=_s-xclick&amp;encrypted='.urlencode($this->encrypt_data($buy_now['hidden_fields']));
		}
		else
		{
			$url = $buy_now['action'];
			
			foreach($buy_now['hidden_fields'] as $k => $v)
			{
				$url .= ($k == 'cmd') ? '?'.$k.'='.$v : '&amp;'.$k.'='.$this->prep_val($v);
			}
		}

		$tagdata = str_replace(LD.'buy_now_url'.RD, $url, $tagdata);
		
		/** ----------------------------------------
		/**  Add to Cart 
		/** ----------------------------------------*/
		
		$add_to_cart['action']				= ($this->debug === TRUE) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$add_to_cart['hidden_fields']	= array(
												'cmd'				=> '_cart',
												'add'				=> "1",
												'business'			=> $paypal_account,
												'return'			=> str_replace(SLASH, '/', $success),
												'cancel_return'		=> str_replace(SLASH, '/', $cancel),
												'item_name'			=> $query->row['item_name'],
												'item_number'		=> $query->row['item_id'],
												'quantity'			=> '1',
												'amount'			=> ($query->row['item_use_sale'] == 'y') ? $query->row['item_sale_price'] : $query->row['item_regular_price'],
												'lc'				=> $country_code,
												'currency_code'		=> $currency,
												'custom'			=> $SESS->userdata['member_id']
												);
										
		if ($this->encrypt === TRUE)
		{
			$url = $add_to_cart['action'].'?cmd=_s-xclick&amp;encrypted='.urlencode($this->encrypt_data($add_to_cart['hidden_fields']));
		}
		else
		{
			$url = $add_to_cart['action'];
			
			foreach($add_to_cart['hidden_fields'] as $k => $v)
			{	
				$url .= ($k == 'cmd') ? '?'.$k.'='.$v : '&amp;'.$k.'='.$this->prep_val($v);
			}
		}
		
		$tagdata = str_replace(LD.'add_to_cart_url'.RD, $url, $tagdata);
		
		/** ----------------------------------------
		/**  View Cart
		/** ----------------------------------------*/
		
		if ($this->debug === TRUE)
		{
			$view_cart['action'] = 'https://www.sandbox.paypal.com/cart/display=1&amp;bn=tm_gl_2.0&amp;business='.$paypal_account;
		}
		else
		{
			$view_cart['action'] = 'https://www.paypal.com/cart/display=1&amp;bn=tm_gl_2.0&amp;business='.$paypal_account;
		}
		
		$tagdata = str_replace(LD.'view_cart_url'.RD, $view_cart['action'], $tagdata);
		
		/** ----------------------------------------
		/**  Parse the Buttons
		/** ----------------------------------------*/
		
		if ($this->encrypt === TRUE)
		{
			$buy_now['hidden_fields'] = array('cmd' => '_s-xclick',
											  'encrypted' => $this->encrypt_data($buy_now['hidden_fields']));
			
			$add_to_cart['hidden_fields'] = array('cmd' => '_s-xclick',
												  'encrypted' => $this->encrypt_data($add_to_cart['hidden_fields']));
		}
		
		foreach ($TMPL->var_pair as $key => $val)
		{     
			$data = array();
			
			if ($key == 'buy_now_button')
            {
            	$data = $buy_now;
			}
			elseif ($key == 'add_to_cart_button')
            {
            	$data = $add_to_cart;
			}
			elseif ($key == 'view_cart_button')
            {
            	$data = $view_cart;
			}
			else
			{
				$tagdata = $TMPL->delete_var_pairs($key, $key, $tagdata);
				continue;
			}
			
			$data['id']		= 'paypal_form_'.$query->row['item_id'].'_'.$key;
			$data['secure'] = FALSE;
			
			$form	= $FNS->form_declaration($data).
					  '<input type="submit" name="submit" value="\\1" class="paypal_button" />'."\n".
					  '</form>'."\n\n";
					  
			$tagdata = preg_replace("/".LD.preg_quote($key).RD."(.*?)".LD.SLASH.$key.RD."/s", $form, $tagdata);
		}
		
		$this->return_data = $tagdata;
		
		return $this->return_data;
    }
    /* END */
    
    
    
	/** -------------------------------------
    /**  Round Money
    /** -------------------------------------*/
    
    function round_money($value, $dec=2)
    {
    	global $TMPL;
    	
    	$decimal = ($TMPL->fetch_param('decimal') == ',')  ? ',' : '.';
    	
    	$value += 0.0;
    	$unit	= floor($value * pow(10, $dec+1)) / 10;
    	$round	= round($unit);
    	return str_replace('.', $decimal, sprintf("%01.2f", ($round / pow(10, $dec))));
    }
    /* END */
    
    
    
    
    /** ----------------------------------------
    /**  Process an Incoming IPN From PayPal
    /** ----------------------------------------*/

    function incoming_ipn()
    {
    	global $EXT, $PREFS, $IN, $DB, $REGX;
    	
    	if (empty($_POST))
    	{
    		@header("HTTP/1.0 404 Not Found");
            @header("HTTP/1.1 404 Not Found");
            exit('No Data Sent');
    	}
    	elseif($this->debug !== TRUE && isset($_POST['test_ipn']) && $_POST['test_ipn'] == 1)
    	{
    		@header("HTTP/1.0 404 Not Found");
            @header("HTTP/1.1 404 Not Found");
            exit('Not Debugging Right Now');
    	}
    	
    	$paypal_account = ( ! $PREFS->ini('sc_paypal_account')) ? $PREFS->ini('webmaster_email') : $PREFS->ini('sc_paypal_account');
    	
    	/** ----------------------------------------
    	/**  Prep, Prep, Prep
    	/** ----------------------------------------*/
    	
    	foreach($this->possible_post as $value)
    	{
    		$this->post[$value] = '';
    	}
    	
    	foreach($_POST as $key => $value)
    	{
    		$this->post[$key] = $REGX->xss_clean($value);
    	}
    	
    	if ($this->debug === TRUE)
    	{
    		$url = ( ! function_exists('openssl_open')) ? 'http://www.sandbox.paypal.com/cgi-bin/webscr' :  'https://www.sandbox.paypal.com/cgi-bin/webscr';
    	}
    	else
    	{
    		$url = ( ! function_exists('openssl_open')) ? 'http://www.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
    	}
    	
    	/** ----------------------------------------
    	/**  Ping Them Back For Confirmation
    	/** ----------------------------------------*/
    	
    	if ( function_exists('curl_init'))
    	{
    		$result = $this->curl_process($url); 
		}
		else
		{
			$result = $this->fsockopen_process($url);
    	}
    	
    	/** ----------------------------------------
    	/**  Evaluate PayPal's Response
    	/** ----------------------------------------*/
		
		/* -------------------------------------
		/*  'simple_commerce_evaluate_ipn_response' hook.
		/*  - Take over processing of PayPal's response to an
		/*  - IPN confirmation
		/*  - Added EE 1.5.1
		*/  
			if ($EXT->active_hook('simple_commerce_evaluate_ipn_response') === TRUE)
			{
				$result = $EXT->universal_call_extension('simple_commerce_evaluate_ipn_response', $this, $result);
				if ($EXT->end_script === TRUE) return;
			}
		/*
		/* -------------------------------------*/
		
    	if (stristr($result, 'VERIFIED'))
    	{
    		// Payment is not completed yet, so we will wait for the IPN when it is.
    		// -OR-
    		// Not our paypal account receiving money, so invalid
    		if (strtolower(trim($this->post['payment_status'])) != 'completed' OR $paypal_account != trim($this->post['receiver_email']))
    		{
    			return FALSE;
    		}
    		
    		/** -----------------------------------
    		/**  Is this a repeat, perhaps?
    		/** ----------------------------------*/
    		
    		$query = $DB->query("SELECT COUNT(*) AS count FROM exp_simple_commerce_purchases
    							 WHERE txn_id = '".$DB->escape_str($this->post['txn_id'])."'");
    							 
    		if ($query->row['count'] > 0) return FALSE;
    		
    		/** -----------------------------------
    		/**  User Valid?
    		/** ----------------------------------*/
    		
    		$query = $DB->query("SELECT screen_name FROM exp_members
    							 WHERE member_id = '".$DB->escape_str($this->post['custom'])."'");
    							 
    		if ($query->num_rows == 0) return FALSE;
    		
    		$this->post['screen_name'] = $query->row['screen_name'];
    		
    		/** -----------------------------------
    		/**  Successful Purchase!
    		/** ----------------------------------*/
    		
    		if ($this->post['num_cart_items'] != '' && $this->post['num_cart_items'] > 0 && isset($_POST['item_number1']))
    		{
    			for($i=1; $i <= $this->post['num_cart_items']; ++$i)
    			{
    				if (($item_id = $IN->GBL('item_number'.$i, 'POST')) !== FALSE)
    				{
    					$qnty	  = (isset($_POST['quantity'.$i]) && is_numeric($_POST['quantity'.$i])) ? $_POST['quantity'.$i] : 1;
    					$subtotal = (isset($_POST['mc_gross_'.$i]) && is_numeric(str_replace('.', '', $_POST['mc_gross_'.$i]))) ? $_POST['mc_gross_'.$i] : 0;
    					
    					if ($subtotal == 0)
    					{
    						continue;
    					}
    					
    					$this->perform_actions($item_id, $qnty, $subtotal, $i);
    				}
    			}
    		}
    		elseif(isset($this->post['item_number']) && $this->post['item_number'] != '' && is_numeric($this->post['mc_gross']) && $this->post['mc_gross'] > 0)
    		{
    			$this->perform_actions($this->post['item_number'], $this->post['quantity'], $this->post['mc_gross']);
    		}

    		/** ------------------------------
    		/**  Paypal Suggests Sending a 200 OK Response Back
    		/** ------------------------------*/
    		
    		@header("HTTP/1.0 200 OK");
            @header("HTTP/1.1 200 OK");
            
            exit('Success');
    	}
    	elseif (stristr($result, 'INVALID'))
		{
			// Error Checking?
		
			@header("HTTP/1.0 200 OK");
            @header("HTTP/1.1 200 OK");
            
            exit('Invalid');
		}
    } 
    /* END */
    
    
    
    /** ----------------------------------------
    /**  Sing a Song, Have a Dance
    /** ----------------------------------------*/
    
	function curl_process($url)
	{
		$postdata = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value)
		{
			// str_replace("\n", "\r\n", $value)
			// put line feeds back to CR+LF as that's how PayPal sends them out
			// otherwise multi-line data will be rejected as INVALID
			$postdata .= "&$key=".urlencode(stripslashes(str_replace("\n", "\r\n", $value)));
		}

		$ch=curl_init(); 
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_URL,$url); 
		curl_setopt($ch,CURLOPT_POST,1); 
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postdata); 

		// Start ob to prevent curl_exec from displaying stuff. 
		ob_start(); 
		curl_exec($ch);

		//Get contents of output buffer 
		$info=ob_get_contents(); 
		curl_close($ch);

		//End ob and erase contents.  
		ob_end_clean(); 

		return $info; 
	}
	/* END */
	
	
	/** ----------------------------------------
    /**  Drinking with Friends is Fun!
    /** ----------------------------------------*/
	
	function fsockopen_process($url)
	{ 
		$parts	= parse_url($url);
		$host	= $parts['host'];
		$path	= (!isset($parts['path'])) ? '/' : $parts['path'];
		$port	= ($parts['scheme'] == "https") ? '443' : '80';
		$ssl	= ($parts['scheme'] == "https") ? 'ssl://' : '';
		
		
		if (isset($parts['query']) && $parts['query'] != '')
		{
			$path .= '?'.$parts['query'];
		}
		
		$postdata = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value)
		{
			// str_replace("\n", "\r\n", $value)
			// put line feeds back to CR+LF as that's how PayPal sends them out
			// otherwise multi-line data will be rejected as INVALID
			$postdata .= "&$key=".urlencode(stripslashes(str_replace("\n", "\r\n", $value)));
		}
		
		$info = '';

		$fp = @fsockopen($ssl.$host, $port, $error_num, $error_str, 8); 

		if (is_resource($fp))
		{
			fputs($fp, "POST {$path} HTTP/1.0\r\n"); 
			fputs($fp, "Host: {$host}\r\n"); 
			fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n"); 
			fputs($fp, "Content-Length: ".strlen($postdata)."\r\n"); 
			fputs($fp, "Connection: close\r\n\r\n"); 
			fputs($fp, $postdata . "\r\n\r\n");
			
			while($datum = fread($fp, 4096))
			{
				$info .= $datum;
			}

			@fclose($fp); 
		}
		
		return $info; 
	}
	/* END */
    
    
	/** ----------------------------------------
    /**  Perform Store Item Actions
    /** ----------------------------------------*/

    function perform_actions($item_id, $qnty, $subtotal, $num_in_cart='')
    {
    	global $DB, $EXT, $REGX, $LOC, $PREFS;
    	
    	$query = $DB->query("SELECT wt.title as item_name, sc.* 
    						 FROM exp_simple_commerce_items sc, exp_weblog_titles wt
    						 WHERE sc.entry_id = wt.entry_id 
    						 AND sc.item_id = '".$DB->escape_str($item_id)."'");

    	if ($query->num_rows > 0)
    	{
    		$this->post['item_name']	= $query->row['item_name'];
    		$this->post['item_number']	= $item_id;
    		$this->post['quantity']		= $qnty;
    		$this->post['mc_gross']		= $subtotal;
    		$this->post['member_id']	= $this->post['custom'];
    		
    		/* -------------------------------------
			/*  'simple_commerce_perform_actions_start' hook.
			/*  - After a purchase is recorded, do more processing before EE's processing
			/*  - Added EE 1.5.1
			*/  
				if ($EXT->active_hook('simple_commerce_perform_actions_start') === TRUE)
				{
					$edata = $EXT->universal_call_extension('simple_commerce_perform_actions_start', $this, $query->row);
					if ($EXT->end_script === TRUE) return;
				}
			/*
			/* -------------------------------------*/	
    		
    		/* --------------------------------
    		/*  Check Price
    		/*	- There is a small chance the Admin changed the price between
    		/*	purchase and the receipt of the IP, so we give a small bit of
    		/* 	wiggle room.  About 10%...
    		/* --------------------------------*/
    		
    		$price = ($query->row['item_use_sale'] == 'y') ? $query->row['item_sale_price'] : $query->row['item_regular_price'];
    		$cost  = $subtotal/$qnty;
    		
    		if ($cost < ($price * 0.9))
    		{	
    			return;
    		}
    	
    		$data = array('purchase_id' 	=> "",
    					  'txn_id' 			=> $this->post['txn_id'],
    					  'member_id' 		=> $this->post['custom'],
    					  'item_id'			=> $query->row['item_id'],
    					  'purchase_date'	=> $LOC->now,
    					  'item_cost'		=> $cost,
    					  'paypal_details'	=> serialize($this->post));
    		
    		if ( ! is_numeric($qnty) OR $qnty == 1)
    		{
    			$DB->query($DB->insert_string('exp_simple_commerce_purchases', $data));
    			$DB->query("UPDATE exp_simple_commerce_items SET item_purchases = item_purchases + 1 WHERE item_id = '".$DB->escape_str($item_id)."'");
    		}
    		else
    		{
    			for($i=0;  $i < $qnty; ++$i)
    			{
    				$DB->query($DB->insert_string('exp_simple_commerce_purchases', $data));
    			}
    			
    			$DB->query("UPDATE exp_simple_commerce_items SET item_purchases = item_purchases + {$qnty} WHERE item_id = '".$DB->escape_str($item_id)."'");
    		}
    		
    		/** --------------------------------
    		/**  New Member Group
    		/** --------------------------------*/
    		
    		if ($query->row['new_member_group'] != '' && $query->row['new_member_group'] != 0)
    		{
    			$DB->query($DB->update_string('exp_members', 
    										  array('group_id' => $query->row['new_member_group']) , 
    										  "member_id = '".$DB->escape_str($this->post['custom'])."' AND group_id != '1'"));
    		}
    		
    		/** --------------------------------
    		/**  Send Emails!
    		/** --------------------------------*/
    		
    		if ($query->row['customer_email_template'] != '' && $query->row['customer_email_template'] != 0)
    		{
    			$result = $DB->query("SELECT email FROM exp_members WHERE member_id = '".$DB->escape_str($this->post['custom'])."'");
				$to = $result->row['email'];
				
				if ( ! class_exists('EEmail'))
				{
					require(PATH_CORE.'core.email.php');  
				}	
				
				$result = $DB->query("SELECT email_subject, email_body 
									 FROM exp_simple_commerce_emails WHERE email_id = '".$DB->escape_str($query->row['customer_email_template'])."'");
				
				if ($result->num_rows > 0)
				{
					$subject = $result->row['email_subject'];
					$message = $result->row['email_body'];
					
					foreach($this->post as $key => $value)
					{
						$subject = str_replace(LD.$key.RD, $value, $subject);
						$message = str_replace(LD.$key.RD, $value, $message);
					}
					
					$MAIL = new EEmail;
					$MAIL->from($PREFS->ini('webmaster_email'));
					$MAIL->to($to);
					$MAIL->subject($subject);
					$MAIL->message($REGX->entities_to_ascii($message));
					$MAIL->Send();
					$MAIL->initialize();
				}
			}
			
			if ($query->row['admin_email_address'] != '' && $query->row['admin_email_template'] != '' && $query->row['admin_email_template'] != 0)
    		{	
				if ( ! class_exists('EEmail'))
				{
					require(PATH_CORE.'core.email.php');  
				}	
				
				$result = $DB->query("SELECT email_subject, email_body 
									  FROM exp_simple_commerce_emails WHERE email_id = '".$DB->escape_str($query->row['admin_email_template'])."'");
				
				if ($result->num_rows > 0)
				{
					$subject = $result->row['email_subject'];
					$message = $result->row['email_body'];
					
					foreach($this->post as $key => $value)
					{
						$subject = str_replace(LD.$key.RD, $value, $subject);
						$message = str_replace(LD.$key.RD, $value, $message);
					}
					
					$MAIL = new EEmail;
					$MAIL->from($PREFS->ini('webmaster_email'));
					$MAIL->to($query->row['admin_email_address']);
					$MAIL->subject($subject);
					$MAIL->message($REGX->entities_to_ascii($message));
					$MAIL->Send();
					$MAIL->initialize();
				}
			}
			
			/* -------------------------------------
			/*  'simple_commerce_perform_actions_end' hook.
			/*  - After a purchase is recorded, do more processing
			/*  - Added EE 1.5.1
			*/  
				if ($EXT->active_hook('simple_commerce_perform_actions_end') === TRUE)
				{
					$edata = $EXT->universal_call_extension('simple_commerce_perform_actions_end', $this, $query->row);
					if ($EXT->end_script === TRUE) return;
				}
			/*
			/* -------------------------------------*/			
    	}		
    } 
    /* END */
    
	/** ----------------------------------------
    /**  Encrypt Button
    /** ----------------------------------------*/
    
	function encrypt_data($params = array(), $type='button')
    {	
    	/** -----------------------------
    	/**  Certificates, Keys, and TMP Files
    	/** -----------------------------*/
    
		$public_certificate	= file_get_contents($this->public_certificate);
		$private_key		= file_get_contents($this->private_key);
		$paypal_certificate	= file_get_contents($this->paypal_certificate);
		
		$tmpin_file  = tempnam($this->temp_path, 'paypal_');
		$tmpout_file = tempnam($this->temp_path, 'paypal_');
		$tmpfinal_file = tempnam($this->temp_path, 'paypal_');
		
		/** -----------------------------
    	/**  Prepare Our Data
    	/** -----------------------------*/
		
		$rawdata = '';
		$params['cert_id'] = $this->certificate_id;
		
		foreach ($params as $name => $value)
		{
			$rawdata .= "$name=$value\n";
		}
		
		if ( ! $fp = fopen($tmpin_file, 'w'))
		{
			exit('failure');
		}
		
		fwrite($fp, rtrim($rawdata));
		fclose($fp);
		
		/** -----------------------------
    	/**  Sign Our File
    	/** -----------------------------*/
		
		if ( ! openssl_pkcs7_sign($tmpin_file, $tmpout_file, $public_certificate, $private_key, array(), PKCS7_BINARY))
		{
			exit("Could not sign encrypted data: " . openssl_error_string());
		}
		
		$data = explode("\n\n", file_get_contents($tmpout_file));
		
		$data = base64_decode($data['1']);
		
		if ( ! $fp = fopen($tmpout_file, 'w'))
		{
			exit("Could not open temporary file '$tmpin_file')");
		}
		
		fwrite($fp, $data);
		fclose($fp);
		
		/** -----------------------------
    	/**  Encrypt Our Data
    	/** -----------------------------*/
		
		if ( ! openssl_pkcs7_encrypt($tmpout_file, $tmpfinal_file, $paypal_certificate, array(), PKCS7_BINARY))
		{
			exit("Could not encrypt data:" . openssl_error_string());
		}
		
		$encdata = file_get_contents($tmpfinal_file, FALSE);
		
		if (empty($encdata))
		{
			exit("Encryption and signature of data failed.");
		}
		
		$encdata = explode("\n\n", $encdata);
		$encdata = trim(str_replace("\n", '', $encdata['1']));
		$encdata = "-----BEGIN PKCS7-----".$encdata."-----END PKCS7-----";
		
		@unlink($tmpfinal_file);
		@unlink($tmpin_file);
		@unlink($tmpout_file);
		
		/** -----------------------------
    	/**  Return The Encrypted Data String
    	/** -----------------------------*/
		
		return $encdata;

	}
	/* END */
 

   /** -------------------------------------
   /**  Clean the values for use in URLs
   /** -------------------------------------*/

	function prep_val($str)
	{
		global $REGX;
		
		// Oh, PayPal, the hoops I must jump through to woo thee...
		// PayPal is displaying its cart as UTF-8, sending UTF-8 headers, but when
		// processing the form data, is obviously wonking with it.  This will force
		// accented characters in item names to display properly on the shopping cart
		// but alas only for unencrypted data.  PayPal won't accept this same
		// workaround for encrypted form data.
		
		$str = str_replace('&amp;', '&', $str);
		$str = urlencode(utf8_decode($REGX->_html_entity_decode($str, 'utf-8')));
		
		return $str;
	}
	/* END */
	
}
/* END */

?>