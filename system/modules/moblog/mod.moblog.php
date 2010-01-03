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
 File: mod.moblog.php
-----------------------------------------------------
 Purpose: Moblog checking class
=====================================================
*/


if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Moblog {

	var $cache_name		= 'moblog_cache';		// Name of cache directory
	var $url_title_word = 'moblog';				// If duplicate url title, this is added along with number	
	var $message_array  = array();				// Array of return messages
	var $return_data 	= ''; 					// When silent mode is off
	var $silent			= ''; 					// true/false - Returns error information
	var $moblog_array	= array(); 				// Row information for moblog being processed
	var $gallery_prefs	= array();				// Gallery preferences
	var $gallery_cat	= '';					// Gallery Category, if set in email
	
	var $fp				= ''; 					// fopen resource
	var $pop_newline	= "\n";					// Newline for POP Server. Switch to \r\n for Microsoft servers
	var $total_size		= 0;					// Total size of emails being checked in bytes
	var $checked_size	= 0;					// Accumulated size of emails checked thus far in bytes
	var $max_size		= 5;					// Maximum amount of email to check, in MB
	var $email_sizes	= array();				// The sizes of the new emails being checked, in bytes
	
	var $boundary 		= false; 				// Boundary marker in emails
	var $multi_boundary = '';					// Boundary for multipart content types
	var	$newline		= '1n2e3w4l5i6n7e8'; 	// Newline replacement
	var $charset		= 'auto';				// Character set for main body of email
	
	var $author  		= '';					// Author of current email being processed
	var $body			= '';					// Main text contents of email being processed
	var $sender_email	= '';					// Email address that sent email
	var $uploads		= 0;					// Number of file uploads for this check
	var $email_files	= array();				// Array containing filenames of uploads for this email
	var $emails_done	= 0;					// Number of emails processed
	var $entries_added	= 0;					// Number of entries added
	var $pings_sent		= 0;					// Number of servers pinged
	var $upload_dir_code = '';					// {filedir_2} for entry's
	var $upload_path	= '';					// Server path for upload directory
	var $entry_data		= array();				// Data for entry's custom fields
	var $post_data		= array();				// Post data retrieved from email being processed: Subject, IP, Categories, Status
	var $template		= '';					// Moblog's template
	var $sticky			= 'n';					// Default Sticky Value	
	
	// These settings are for a specific problem with AT&T phones
	var $attach_as_txt	= FALSE;				// Email's Message as txt file?
	var $attach_text	= '';					// If $attach_as_txt is true, this is the text
	var $attach_name	= '';					// If $attach_as_txt is true, this is the name
	
	var $time_offset	= '5';					// Number of seconds entries are offset by negatively, higher if you are putting in many entries
	
	var $movie			= array();				// Suffixes for accepted movie files
	var $audio			= array();				// Suffixes for accepted audio files
	var $image			= array();				// Suffixes for accepted image files
	var $files			= array();				// Suffixes for other types of accepted files
	
	var $txt_override	= FALSE;				// When set to TRUE, all .txt files are treated as message text

    /** -------------------------------------
    /**  Constructor
    /** -------------------------------------*/

    function Moblog()
    {
    	/** -----------------------------
    	/**  Default file formats
    	/** -----------------------------*/
    	
    	$this->movie = array('3gp','mov','mpg','avi','movie');
		$this->audio = array('mid','midi','mp2','mp3','aac','mp4','aif','aiff','aifc','ram','rm','rpm','wav','ra','rv','wav');
		$this->image = array('bmp','gif','jpeg','jpg','jpe','png','tiff','tif');
		$this->files = array('doc','xls','zip','tar','tgz','swf','sit','php','txt','html','asp','js','rtf', 'pdf');		
		
    	if ( ! defined('LD'))
        	define('LD', '{');
			
		if ( ! defined('RD'))
        	define('RD', '}');
	
		if ( ! defined('SLASH'))
        	define('SLASH',	'&#47;');
        	
        $this->max_size = $this->max_size * 1024 * 1000;
    }
    /* END */

    
    /** -------------------------------------
    /**  Check for expired moblog(s)
    /** -------------------------------------*/

    function check()
    {
        global $TMPL, $DB, $FNS, $LANG;
        
        $which 			= ( ! $TMPL->fetch_param('which'))	? '' : $TMPL->fetch_param('which');
        $this->silent	= ( ! $TMPL->fetch_param('silent'))	? 'true' : $TMPL->fetch_param('silent');

        if ($which == '')
        {
        	$this->return_data = ($this->silent == 'true') ? '' : 'No Moblog Indicated';
        	return $this->return_data ;
        }
        
        $LANG->fetch_language_file('moblog');           
        
        $sql = "SELECT * FROM exp_moblogs WHERE moblog_enabled = 'y'";
        $sql .= ($which == 'all') ? '' : $FNS->sql_andor_string($which, 'moblog_short_name', 'exp_moblogs');
        $query = $DB->query($sql);
        
        if ($query->num_rows == 0)
        {
        	$this->return_data = ($this->silent == 'true') ? '' : $LANG->line('no_moblogs');
        	return $this->return_data;
        }
        
        /** --------------------------
        /**  Check Cache
        /** --------------------------*/
        
        if ( ! @is_dir(PATH_CACHE.$this->cache_name))
        {
        	if ( ! @mkdir(PATH_CACHE.$this->cache_name, 0777))
        	{
        		$this->return_data = ($this->silent == 'true') ? '' : $LANG->line('no_cache');
        		return $this->return_data;
        	}
        }
        
        @chmod(PATH_CACHE.$this->cache_name, 0777);
        
        //$FNS->delete_expired_files(PATH_CACHE.$this->cache_name);
        
        $expired = array();
        
        foreach($query->result as $row)
        {
        	$cache_file = PATH_CACHE.$this->cache_name.'/t_moblog_'.$row['moblog_id'];
        	
        	if ( ! file_exists($cache_file) OR (time() > (filemtime($cache_file) + ($row['moblog_time_interval'] * 60))))
        	{
        		$this->set_cache($row['moblog_id']);
        		$expired[] = $row['moblog_id'];
        	}
        	elseif ( ! $fp = @fopen($cache_file, 'r+b'))
        	{
        		if ($this->silent == 'false')
        		{
        			$this->return_data .= '<p><strong>'.$row['moblog_full_name'].'</strong><br />'.
        							$LANG->line('no_cache')."\n</p>";
        		}
        	}
        }	
        
        if (sizeof($expired) == 0)
        {
        	$this->return_data = ($this->silent == 'true') ? '' : $LANG->line('moblog_current');         
        	return $this->return_data;
        }
        
        /** ------------------------------
        /**  Process Expired Moblogs
        /** ------------------------------*/
        
        foreach($query->result as $row)
        {
        	if (in_array($row['moblog_id'],$expired))
        	{
        		$this->moblog_array = $row;
        		
        		if ($this->moblog_array['moblog_email_type'] == 'imap')
        		{
        			if ( ! $this->check_imap_moblog())
        			{        		
        				if ($this->silent == 'false' && sizeof($this->message_array) > 0)
        				{
        					$this->return_data .= '<p><strong>'.$this->moblog_array['moblog_full_name'].'</strong><br />'.
        								$this->errors()."\n</p>";
        				}
        			}
       			}
        		else
        		{
        			if ( ! $this->check_pop_moblog())
        			{
        				if ($this->silent == 'false' && sizeof($this->message_array) > 0)
        				{
        					$this->return_data .= '<p><strong>'.$this->moblog_array['moblog_full_name'].'</strong><br />'.
        								$this->errors()."\n</p>";
        				}
        			}
        		}
        		
        		$this->message_array = array();
        	}
        }    
        
        if ($this->silent == 'false')
        {
        	$this->return_data .= $LANG->line('moblog_successful_check')."<br />\n";
        	$this->return_data .= $LANG->line('emails_done')." {$this->emails_done}<br />\n";
        	$this->return_data .= $LANG->line('entries_added')." {$this->entries_added}<br />\n";
        	$this->return_data .= $LANG->line('attachments_uploaded')." {$this->uploads}<br />\n";
        	$this->return_data .= $LANG->line('pings_sent')." {$this->pings_sent}<br />\n";
		}
		
		return $this->return_data ;        
    }
    /* END */
    
    
	/** -------------------------------------
    /**  Set cache
    /** -------------------------------------*/

    function set_cache($moblog_id)
    {
    	$cache_file = PATH_CACHE.$this->cache_name.'/t_moblog_'.$moblog_id;
    	
    	if ($fp = @fopen($cache_file, 'wb'))
    	{
    		flock($fp, LOCK_EX);
        	fwrite($fp, 'hi');
        	flock($fp, LOCK_UN);
        	fclose($fp);
    	}
    	
    	@chmod($cache_file, 0777);
    
    }
    /* END */
    
    /** -------------------------------------
    /**  Return errors
    /** -------------------------------------*/

    function errors()
    {
    	global $LANG;
    	
    	$message = '';
    	
    	if (sizeof($this->message_array) == 0 || $this->silent == 'true')
    	{
    		return $message;
    	}
    	
    	foreach($this->message_array as $row)
    	{
    		$message .= ($message == '') ? '' : "<br />\n";
    		$message .= ( ! $LANG->line($row)) ? $row : $LANG->line($row);
    	}
    	
    	return $message;
    
    }
    /* END */
    
	
	/** -------------------------------------
    /**  Check POP3 moblog
    /** -------------------------------------*/

    function check_pop_moblog()
    {
        global $FNS, $DB, $REGX, $PREFS;
        
        /** ------------------------------
        /**  Email Login Check
        /** ------------------------------*/
        
        if ( ! $this->fp = @fsockopen($this->moblog_array['moblog_email_server'], 110, $errno, $errstr, 20))
		{
			$this->message_array[] = 'no_server_connection'; 
        	return false;
		}		
		
		if ( ! preg_match("#^\+OK#i",fgets($this->fp, 1024)))
		{
			$this->message_array[] = 'invalid_server_response'; 
			@fclose($this->fp);
        	return false;
		}
		if ( ! preg_match("#^\+OK#i",$this->pop_command("USER ".base64_decode($this->moblog_array['moblog_email_login']))))
		{
			// Windows servers something require a different line break.
			// So, we change the line break and try again.
			
			$this->pop_newline = "\r\n";
			
			if ( ! preg_match("#^\+OK#i",$this->pop_command("USER ".base64_decode($this->moblog_array['moblog_email_login']))))
			{
				$this->message_array[] = 'invalid_username'; 
				$line = $this->pop_command("QUIT");
				@fclose($this->fp);
        		return false;
        	}
		}
		
		if ( ! preg_match("#^\+OK#i",$this->pop_command("PASS ".base64_decode($this->moblog_array['moblog_email_password']))))
		{
			$this->message_array[] = 'invalid_password'; 
			$line = $this->pop_command("QUIT");
			@fclose($this->fp);
        	return false;
		}
		
		
		/** ------------------------------
        /**  Got Mail?
        /** ------------------------------*/
        
        if (!$line = $this->pop_command("STAT"))
		{
			$this->message_array[] = 'unable_to_retrieve_emails'; 
			$line = $this->pop_command("QUIT");
			@fclose($this->fp);
        	return false;
		}
		
		$stats = explode(" ", $line);		
		$total = ( ! isset($stats['1'])) ? 0 : $stats['1'];
		$this->total_size = ( ! isset($stats['2'])) ? 0 : $stats['2'];
		
		if ($total == 0)
		{
			$this->message_array[] = 'no_valid_emails'; 
			$line = $this->pop_command("QUIT");
			@fclose($this->fp);
        	return;
		}
		
		/** ------------------------------
        /**  Determine Sizes of Emails
        /** ------------------------------*/
        
        if ($this->total_size > $this->max_size)
        {
        	if (!$line = $this->pop_command("LIST"))
			{
				$this->message_array[] = 'unable_to_retrieve_emails'; 
				$line = $this->pop_command("QUIT");
				@fclose($this->fp);
        		return false;
			}
			
			do{
				$data = fgets($this->fp, 1024);			
				$data = $this->iso_clean($data);
					
				if(empty($data) OR trim($data) == '.')
				{
					break;
				}
				
				$x = explode(' ', $data);
				
				if (sizeof($x) == 1) break;
						
				$this->email_sizes[$x['0']] = $x['1'];
            	
			} while (!preg_match("#^\.\r\n#",$data));
        }
		
		
		/** ------------------------------
        /**  Find Valid Emails
        /** ------------------------------*/
		
		$valid_emails = array();
		$valid_froms = explode("|",$this->moblog_array['moblog_valid_from']);
		
		for ($i=1; $i <= $total; $i++) 
		{
			if ( ! preg_match("#^\+OK#", $this->pop_command("TOP $i 0")))
			{
				$line = $this->pop_command("QUIT");
				@fclose($this->fp);
				return false;
			}
			
			$valid_subject = 'n';
			$valid_from = ($this->moblog_array['moblog_valid_from'] != '') ? 'n' : 'y';
			$str = fgets($this->fp, 1024);
			
			while (!preg_match("#^\.\r\n#",$str))
			{            	
            	$str = fgets($this->fp, 1024);
            	$str = $this->iso_clean($str);
            	
            	if (empty($str))
            	{
            		break;
            	}
            	
            	// ------------------------
            	// Does email contain correct prefix? (if prefix is set)
            	// Liberal interpretation of prefix location
            	// ------------------------
            	
            	if($this->moblog_array['moblog_subject_prefix'] == '')
            	{
            		$valid_subject = 'y';
            	}
            	elseif (preg_match("/Subject:(.*)/", $str, $subject))
            	{
            		if(strpos(trim($subject['1']), $this->moblog_array['moblog_subject_prefix']) !== false)
            		{
            			$valid_subject = 'y';
            		}
            	}
            	
            	if ($this->moblog_array['moblog_valid_from'] != '')
            	{
            		if (preg_match("/From:\s*(.*)\s*\<(.*)\>/", $str, $from) || preg_match("/From:\s*(.*)\s*/", $str, $from))
            		{
            			$address = ( ! isset($from['2'])) ? $from['1'] : $from['2'];
            			
            			if(in_array(trim($address),$valid_froms))
            			{
            				$valid_from = 'y';
            			}
            		}
            	}
			}
			
			if ($valid_subject == 'y' && $valid_from == 'y')
			{
				$valid_emails[] = $i;
			}
		}
		
		unset($subject);
		unset($str);
		
		if (sizeof($valid_emails) == 0)
		{
			$this->message_array[] = 'no_valid_emails'; 
			$line = $this->pop_command("QUIT");
			@fclose($this->fp);
        	return;
		}
		
		/** ------------------------------
        /**  Load Photo Gallery Prefs
        /** ------------------------------*/
		
		if ( isset($this->moblog_array['moblog_type']) && $this->moblog_array['moblog_type'] == 'gallery')
		{
			$query = $DB->query("SELECT gallery_upload_path, gallery_image_url, 
								 gallery_image_protocal, gallery_image_lib_path,
								 gallery_create_thumb, gallery_thumb_width,
								 gallery_thumb_height, gallery_thumb_quality,
								 gallery_thumb_prefix, gallery_create_medium, 
								 gallery_medium_width, gallery_medium_height, 
								 gallery_medium_quality, gallery_medium_prefix,
								 gallery_wm_type, gallery_wm_image_path,
								 gallery_wm_test_image_path, gallery_wm_use_font, 
								 gallery_wm_font, gallery_wm_font_size, 
								 gallery_wm_text, gallery_wm_vrt_alignment, 
								 gallery_wm_hor_alignment, gallery_wm_padding, 
								 gallery_wm_opacity, gallery_wm_x_offset, 
								 gallery_wm_y_offset, gallery_wm_x_transp, 
								 gallery_wm_y_transp, gallery_wm_text_color, 
								 gallery_wm_use_drop_shadow, gallery_wm_shadow_distance,
								 gallery_wm_shadow_color, gallery_wm_apply_to_thumb,
								 gallery_wm_apply_to_medium, gallery_id,
								 gallery_url, gallery_full_name, gallery_maintain_ratio
								 FROM exp_galleries
								 WHERE gallery_id = '".$this->moblog_array['moblog_gallery_id']."'");
								  
			if ($query->num_rows == 0)
			{
				$this->message_array[] = 'invalid_gallery'; 
				$line = $this->pop_command("QUIT");
				@fclose($this->fp);
        		return false;
			}
			
			$this->gallery_prefs = $query->row;
		}
		
		
		/** ------------------------------
        /**  Process Valid Emails
        /** ------------------------------*/
        
        foreach ($valid_emails as $email_id)
        {			
        	// Reset Variables
        	$this->post_data = array();
        	$this->email_files = array();
        	$this->body = '';
        	$this->sender_email = '';
        	$this->entry_data = array();
        	$email_data = '';
        	$this->attach_as_txt = false;
        	
        	/** ------------------------------------------
        	/**  Do Not Exceed Max Size During a Moblog Check
        	/** ------------------------------------------*/
        	
        	if ($this->total_size > $this->max_size && isset($this->email_sizes[$email_id]))
        	{
        		if ($this->checked_size + $this->email_sizes[$email_id] > $this->max_size)
        		{
        			continue;
        		}
        	
        		$this->checked_size += $this->email_sizes[$email_id];
        	}
        	
        	/** ---------------------------------------
        	/**  Failure does happen at times
        	/** ---------------------------------------*/
        	
			if ( ! preg_match("#^\+OK#i", $this->pop_command("RETR ".$email_id)))
			{
				continue;
			}
			
			// Under redundant, see redundant
			$this->post_data['subject'] = 'Moblog Entry';
			$this->post_data['ip'] = '127.0.0.1';
			$format_flow = 'n';
			
			/** ------------------------------
			/**  Retrieve Email data
			/** ------------------------------*/
			
			do{
				
				$data = fgets($this->fp, 1024);			
				$data = $this->iso_clean($data);
				
				if(empty($data))
				{
					break;
				}
				
				if ($format_flow == 'n' && stristr($data,'format=flowed'))
				{
					$format_flow = 'y';
				}
				
				$email_data .= $data;
            	
			} while (!preg_match("#^\.\r\n#",$data));
			
			//echo $email_data."<br /><br />\n\n";
			
			if (preg_match("/charset=(.*?)(\s|".$this->newline.")/is", $email_data, $match))
			{
				$this->charset = trim(str_replace(array("'", '"', ';'), '', $match['1']));
			}
			
			/** --------------------------
			/**  Set Subject, Remove Moblog Prefix
			/** --------------------------*/
			
            if (preg_match("/Subject:(.*)/", trim($email_data), $subject))
            {	
            	if($this->moblog_array['moblog_subject_prefix'] == '')
            	{
            		$this->post_data['subject'] = (trim($subject['1']) != '') ? trim($subject['1']) : 'Moblog Entry';
            	}
            	elseif (strpos(trim($subject['1']), $this->moblog_array['moblog_subject_prefix']) !== false)
            	{
            		$str_subject = str_replace($this->moblog_array['moblog_subject_prefix'],'',$subject['1']);
            		$this->post_data['subject'] = (trim($str_subject) != '') ? trim($str_subject) : 'Moblog Entry';
            	}
				
				// If the subject header was read with imap_utf8() in the iso_clean() method, then
				// we don't need to do anything further
				if (! function_exists('imap_utf8'))
				{
					// If subject header was processed with MB or Iconv functions, then the internal encoding
					// must be used to decode the subject, not the charset used by the email
	           		if (function_exists('mb_convert_encoding'))
	           		{	
	           			$this->post_data['subject'] = mb_convert_encoding($this->post_data['subject'], strtoupper($PREFS->ini('charset')), mb_internal_encoding());
	           		}
	           		elseif(function_exists('iconv'))
	           		{
	           			$this->post_data['subject'] = iconv(iconv_get_encoding('internal_encoding'), strtoupper($PREFS->ini('charset')), $this->post_data['subject']);
	           		}
	           		elseif(strtolower($PREFS->ini('charset')) == 'utf-8' && strtolower($this->charset) == 'iso-8859-1')
	           		{
	           			$this->post_data['subject'] = utf8_encode($this->post_data['subject']);
	           		}
	           		elseif(strtolower($PREFS->ini('charset')) == 'iso-8859-1' && strtolower($this->charset) == 'utf-8')
	           		{
	           			$this->post_data['subject'] = utf8_decode($this->post_data['subject']);
	           		}					
				}
            }            	
            	
            /** --------------------------
			/**  IP Address of Sender
			/** --------------------------*/
				
            if (preg_match("/Received:\s*from\s*(.*)\[+(.*)\]+/", $email_data, $subject))
            {
            	if (isset($subject['2']) && $REGX->valid_ip(trim($subject['2'])))
            	{
            		$this->post_data['ip'] = trim($subject['2']);
            	}
            }
            	
            /** --------------------------
			/**  Check if AT&T email
			/** --------------------------*/
				
            if (preg_match("/From:\s*(.*)\s*\<(.*)\>/", $email_data, $from) || preg_match("/From:\s*(.*)\s*/", $email_data, $from))
           	{
            	$this->sender_email = ( ! isset($from['2'])) ? $from['1'] : $from['2'];
            		
            	if (strpos(trim($this->sender_email),'mobile.att.net') !== false)
            	{
            		$this->attach_as_txt = true;
            	}
            }
			
			/** -------------------------------------
			/**  Eliminate new line confusion
			/** -------------------------------------*/
			
			$email_data = $this->remove_newlines($email_data,$this->newline);
			
			/** -------------------------------------
			/**  Determine Boundary
			/** -------------------------------------*/
			
			if ( ! $this->find_boundary($email_data) OR 
				($this->moblog_array['moblog_upload_directory'] == '0' && ! isset($this->gallery_prefs['gallery_upload_path'])))
			{

				/** -------------------------
				/**  No files, just text
				/** -------------------------*/
				
				$duo = $this->newline.$this->newline;
				$this->body = $this->find_data($email_data, $duo,$duo.'.'.$this->newline);		
				
				if ($this->body == '')
				{
					$this->body = $this->find_data($email_data, $duo,$this->newline.'.'.$this->newline);	
				}
				
				// Check for Quoted-Printable and Base64 encoding
				if (stristr($email_data,'Content-Transfer-Encoding'))
				{
					$encoding = $this->find_data($email_data, "Content-Transfer-Encoding: ", $this->newline);
					
					if (! stristr(trim($encoding), "quoted-printable") AND ! stristr(trim($encoding), "base64"))
					{
						// try it without the space after the colon...
						$encoding = $this->find_data($email_data, "Content-Transfer-Encoding:", $this->newline);
					}
					
					if(stristr(trim($encoding),"quoted-printable"))
					{
						$this->body = str_replace($this->newline,"\n",$this->body);
						$this->body = quoted_printable_decode($this->body);
						$this->body = (substr($this->body,0,1) != '=') ? $this->body : substr($this->body,1);
						$this->body = (substr($this->body,-1) != '=') ? $this->body : substr($this->body,0,-1);
						$this->body = $this->remove_newlines($this->body,$this->newline);
					}
					elseif(stristr(trim($encoding),"base64"))
					{
						$this->body = str_replace($this->newline,"\n",$this->body);
						$this->body = base64_decode(trim($this->body));
						$this->body = $this->remove_newlines($this->body,$this->newline);
					}
				}
				
				if ($this->charset != $PREFS->ini('charset'))
            	{
            		if (function_exists('mb_convert_encoding'))
            		{
            			$this->body = mb_convert_encoding($this->body, strtoupper($PREFS->ini('charset')), strtoupper($this->charset));
            		}
            		elseif(function_exists('iconv') AND ($iconvstr = @iconv(strtoupper($this->charset), strtoupper($PREFS->ini('charset')), $this->body)) !== FALSE)
            		{
            			$this->body = $iconvstr;
            		}
            		elseif(strtolower($PREFS->ini('charset')) == 'utf-8' && strtolower($this->charset) == 'iso-8859-1')
            		{
            			$this->body = utf8_encode($this->body);
            		}
            		elseif(strtolower($PREFS->ini('charset')) == 'iso-8859-1' && strtolower($this->charset) == 'utf-8')
            		{
            			$this->body = utf8_decode($this->body);
            		}
            	}
				
				
			}
			else
			{
				if ( ! $this->parse_email($email_data))
				{
					$this->message_array[] = 'unable_to_parse';
					return false;
				}
				
				// Email message as .txt file?
				// Make the email body the attachment's contents
				// Unset attachment from files array.
				if ($this->attach_as_txt === true && trim($this->body) == '' && $this->attach_text != '')
				{
					$this->body = $this->attach_text;
					$this->attach_text = '';
					
					foreach ($this->post_data['files'] as $key => $value)
					{
						if ($value == $this->attach_name)
						{
							unset($this->post_data['files'][$key]);
						}
					}
				}
				
			}
			
			/** ---------------------------
			/**  Authorization Check
			/** ---------------------------*/
				
			if ( ! $this->check_login())
			{
				if ($this->moblog_array['moblog_auth_required'] == 'y')
				{
					/** -----------------------------
					/**  Delete email?
					/** -----------------------------*/
					
					if ($this->moblog_array['moblog_auth_delete'] == 'y')
					{
						if ( ! preg_match("#^\+OK#i",$this->pop_command("DELE {$email_id}")))
						{
							$this->message_array[] = 'undeletable_email'; //.$email_id;
							return false;
						}					
					}				
					
					/** -----------------------------
					/**  Delete any uploaded images
					/** -----------------------------*/
					
					if (sizeof($this->email_files) > 0)
					{
						foreach ($this->email_files as $axe)
						{
							@unlink($this->upload_path.$axe);
						}					
					}
					
					// Error...
					$this->message_array[] = 'authorization_failed';
					$this->message_array[] = $this->post_data['subject'];
					continue;
				}
			}
			
			/** -----------------------------
			/**  Format Flow Fix - Oh Joy!
			/** -----------------------------*/
			
			if ($format_flow == 'y')
			{
				$x = explode($this->newline,$this->body);
				$wrap_point = 10;
				
				if (sizeof($x) > 1)
				{
					$this->body = '';
					
					// First, find wrap point
					for($p=0; $p < sizeof($x); $p++)
					{
						$wrap_point = (strlen($x[$p]) > $wrap_point) ? strlen($x[$p]) : $wrap_point;
					}
					
					// Unwrap the Content
					for($p=0; $p < sizeof($x); $p++)
					{
						$next = (isset($x[$p+1]) && sizeof($y = explode(' ',$x[$p+1]))) ? $y['0'] : '';
						$this->body .= (strlen($x[$p]) < $wrap_point && strlen($x[$p].$next) <= $wrap_point) ? $x[$p].$this->newline : $x[$p];
					}					
				}			
			}
			
			$allow_overrides = ( ! isset($this->moblog_array['moblog_allow_overrides'])) ? 'y' : $this->moblog_array['moblog_allow_overrides'];
			
			/** -----------------------------
			/**  Image Archive set in email?
			/** -----------------------------*/
			
			if ($allow_overrides == 'y' && 
				(preg_match("/\{file_archive\}(.*)\{\/file_archive\}/", $this->body, $matches) OR 
				 preg_match("/\<file_archive\>(.*)\<\/file_archive\>/", $this->body, $matches)))
			{
				$matches['1'] = trim($matches['1']);
				
				if ($matches['1'] == 'y' || $matches['1'] == 'true' || $matches['1'] == '1')
				{
					$this->moblog_array['moblog_file_archive'] = 'y';
				}
				else
				{
					$this->moblog_array['moblog_file_archive'] = 'n';
				}
				
				$this->body = str_replace($matches['0'],'',$this->body);
			}
			
			/** -----------------------------
			/**  Categories set in email?
			/** -----------------------------*/
			
			if ($allow_overrides == 'n' OR ( ! preg_match("/\{category\}(.*)\{\/category\}/", $this->body, $cats) && 
											 ! preg_match("/\<category\>(.*)\<\/category\>/", $this->body, $cats)))
			{
				$this->post_data['categories'] = trim($this->moblog_array['moblog_categories']);
			}
			else
			{
				$cats['1'] = str_replace(':','|',$cats['1']);
				$cats['1'] = str_replace(',','|',$cats['1']);
				$this->post_data['categories'] = $cats['1'];
				$this->body = str_replace($cats['0'],'',$this->body);
			}
			
			/** -----------------------------
			/**  Status set in email
			/** -----------------------------*/
			
			if ($allow_overrides == 'n' OR ( ! preg_match("/\{status\}(.*)\{\/status\}/", $this->body, $cats) && 
											 ! preg_match("/\<status\>(.*)\<\/status\>/", $this->body, $cats)))
			{
				if ( isset($this->moblog_array['moblog_type']) && $this->moblog_array['moblog_type'] == 'gallery')
				{
					$this->post_data['status'] = trim($this->moblog_array['moblog_gallery_status']);
				}
				else
				{
					$this->post_data['status'] = trim($this->moblog_array['moblog_status']);
				}
			}
			else
			{
				$this->post_data['status'] = $cats['1'];
				$this->body = str_replace($cats['0'],'',$this->body);
			}
			
			/** -----------------------------
			/**  Sticky Set in Email
			/** -----------------------------*/
			
			if ($allow_overrides == 'n' OR ( ! preg_match("/\{sticky\}(.*)\{\/sticky\}/", $this->body, $mayo) && 
											 ! preg_match("/\<sticky\>(.*)\<\/sticky\>/", $this->body, $mayo)))
			{
				$this->post_data['sticky'] = ( ! isset($this->moblog_array['moblog_sticky_entry'])) ? $this->sticky : $this->moblog_array['moblog_sticky_entry'];
			}
			else
			{
				$this->post_data['sticky'] = (trim($mayo['1']) == 'yes' OR trim($mayo['1']) == 'y') ? 'y' : 'n';
				$this->body = str_replace($mayo['0'],'',$this->body);
			}
			
			
			
			/** -----------------------------
			/**  Default Field set in email?
			/** -----------------------------*/
			
			if ($allow_overrides == 'y' && (preg_match("/\{field\}(.*)\{\/field\}/", $this->body, $matches) OR 
											preg_match("/\<field\>(.*)\<\/field\>/", $this->body, $matches)))
			{
				/* -------------------------------------
				/*  Hidden Configuration Variable
				/*  - moblog_allow_nontextareas => Removes the textarea only restriction
				/*	for custom fields in the moblog module (y/n)
				/* -------------------------------------*/
				
				$xsql = ($PREFS->ini('moblog_allow_nontextareas') == 'y') ? "" : " AND exp_weblog_fields.field_type = 'textarea' ";
			
				$results = $DB->query("SELECT field_id FROM exp_weblog_fields, exp_weblogs 
									   WHERE exp_weblogs.field_group = exp_weblog_fields.group_id
									   AND exp_weblogs.weblog_id = '".$this->moblog_array['moblog_weblog_id']."'
									   AND exp_weblog_fields.group_id = '".$query->row['field_group']."'
									   AND (exp_weblog_fields.field_name = '".$matches['1']."'
									   OR exp_weblog_fields.field_label = '".$matches['1']."')
									   {$xsql}");
				
				if ($results->num_rows > 0)
				{
					$this->moblog_array['moblog_field_id'] = trim($results->row['field_id']);
				}
				
				$this->body = str_replace($matches['0'],'',$this->body);
			}
			
			
			/** -----------------------------
			/**  Set Entry Title in Email
			/** -----------------------------*/
			
			if (preg_match("/\{entry_title\}(.*)\{\/entry_title\}/", $this->body, $matches) || preg_match("/\<entry_title\>(.*)\<\/entry_title\>/", $this->body, $matches))
			{
				if (strlen($matches['1']) > 1)
				{
					$this->post_data['subject'] = trim(str_replace($this->newline,"\n",$matches['1']));
				}
				
				$this->body = str_replace($matches['0'],'',$this->body);
			}
			
			
			/** ----------------------------
			/**  Set Caption in Email
			/** ----------------------------*/
			
			if ( isset($this->moblog_array['moblog_type']) && $this->moblog_array['moblog_type'] == 'gallery' && $this->moblog_array['moblog_file_archive'] == 'n')
			{
				if (preg_match("/\{caption\}(.*)\{\/caption\}/si", $this->body, $matches) || preg_match("/\<caption\>(.*)\<\/caption\>/si", $this->body, $matches))
				{
					if (strlen($matches['1']) > 1)
					{
						$this->body = trim($matches['1']);
					}
				}
			}
			
			/** ----------------------------
			/**  Post Entry
			/** ----------------------------*/
			
			if ( isset($this->moblog_array['moblog_type']) && $this->moblog_array['moblog_type'] == 'gallery' && $this->moblog_array['moblog_file_archive'] == 'n')
			{
				if (isset($this->post_data['images']) && sizeof($this->post_data['images']) > 0)
				{
					foreach($this->post_data['images'] as $value)
					{
						$this->post_gallery($value['filename']);
					}
				}
			}
			elseif ($this->moblog_array['moblog_weblog_id'] != '0' && $this->moblog_array['moblog_file_archive'] == 'n')
			{				
				$this->template = str_replace('/', SLASH, $this->moblog_array['moblog_template']);
		
				$tag = 'field';
			
				if($this->moblog_array['moblog_field_id'] != 'none' OR 
				   preg_match("/".LD.'field:'."(.*?)".RD."(.*?)".LD.SLASH.'field:'."(.*?)".RD."/s", $this->template, $matches) OR
				   preg_match("/[\<\{]field\:(.*?)[\}\>](.*?)[\<\{]\/field\:(.*?)[\}\>]/", $this->body, $matches)
				   )
        		{
        			$this->post_entry();
        		}
        		else
        		{
        			$this->emails_done++;
        			continue;
        		}
			}
			
			
			/** -------------------------
			/**  Delete Email
			/** -------------------------*/
			
			
			if ( ! preg_match("#^\+OK#",$this->pop_command("DELE {$email_id}")))
			{
				$this->message_array[] = 'undeletable_email'; //.$email_id;
				return false;
			}
			
			
			/** -------------------------
			/**  Send Pings
			/** -------------------------*/
			
			if (isset($this->moblog_array['moblog_ping_servers']) && $this->moblog_array['moblog_ping_servers'] != '')
			{
				if($pings_sent = $this->send_pings($this->moblog_array['blog_title'], $this->moblog_array['blog_url'], $this->moblog_array['rss_url']))
				{
					$this->pings_sent = $this->pings_sent + sizeof($pings_sent);
				}
			}
			
			
			$this->emails_done++;
		}
		
		/** -----------------------------
		/**  Close Email Connection
		/** -----------------------------*/
		
		$line = $this->pop_command("QUIT");
		
		@fclose($this->fp);
		
		/** ---------------------------------
        /**  Clear caches if needed
        /** ---------------------------------*/
		
		if ($this->emails_done > 0)
		{
			if ($PREFS->ini('new_posts_clear_caches') == 'y')
        	{
				$FNS->clear_caching('all');
			}
			else
			{
				$FNS->clear_caching('sql_cache');
			}
		}
		
		/** -----------------------------
		/**  Done
		/** -----------------------------*/
		
		return true;
    }
    /* END */

    
	/** -------------------------------------
	/**  Post Gallery Entry
	/** -------------------------------------*/

	function post_gallery($image_name)
	{
    	global $DB, $REGX, $LOC, $PREFS;
    	
    	
		/** ---------------------------------
		/**  Is Entry's Title Unique?
		/** ---------------------------------*/
		
		$this->post_data['subject'] = strip_tags($this->post_data['subject']);
		$entry_title = ($PREFS->ini('auto_convert_high_ascii') == 'y') ? $REGX->ascii_to_entities($this->post_data['subject']) : $this->post_data['subject'];
		
		$query = $DB->query("SELECT COUNT(*) AS count 
							  FROM exp_gallery_entries 
							  WHERE title = '".$DB->escape_str($entry_title)."' 
							  AND gallery_id = '".$this->gallery_prefs['gallery_id']."'");
		
		// Already have default title
		if ($query->row['count'] > 0)
		{
			// Give it a moblog title
			$entry_title .= ' '.$this->url_title_word;
		
			/** ------------------------------------------------
			/**  Check for similar title
			/** ------------------------------------------------*/
			
			$results = $DB->query("SELECT COUNT(*) AS count 
							  		FROM exp_gallery_entries 
							  		WHERE title LIKE '".$DB->escape_like_str($entry_title)."%' 
							  		AND gallery_id = '".$this->gallery_prefs['gallery_id']."'");
							  		
			$entry_title .= ($results->row['count'] + 1);
		}						
				
		/** --------------------------------
		/**  Remove Ignore Text Out
		/** --------------------------------*/
		
		$this->body = preg_replace("#<img\s+src=\s*[\"']cid:(.*?)\>#si", '', $this->body);  // embedded images
		
		$this->moblog_array['moblog_ignore_text'] = $this->remove_newlines($this->moblog_array['moblog_ignore_text'],$this->newline);
		
		// One biggo chunk
		if ($this->moblog_array['moblog_ignore_text'] != '' && stristr($this->body,$this->moblog_array['moblog_ignore_text']) !== FALSE)
		{
			$this->body = str_replace($this->moblog_array['moblog_ignore_text'], '',$this->body);
		}
		elseif($this->moblog_array['moblog_ignore_text'] != '')
		{
			// By line
			$delete_text	= $this->remove_newlines($this->moblog_array['moblog_ignore_text'],$this->newline);
			$delete_array	= explode($this->newline,$delete_text);
		
			if (sizeof($delete_array) > 0)
			{
				foreach($delete_array as $ignore)
				{
					if (trim($ignore) != '')
					{
						$this->body = str_replace(trim($ignore), '',$this->body);
					}		
				}		
			}
		}		
			
		/** --------------------------------
		/**  Return New Lines
		/** --------------------------------*/
		
		$this->body = str_replace($this->newline, "\n",$this->body);
		
		/** --------------------------------
		/**  Compile Thumb data
		/** --------------------------------*/
		
		if ($this->gallery_prefs['gallery_create_thumb'] == 'y')
			$thumbs['thumb'] = array($this->gallery_prefs['gallery_thumb_prefix'],  
									  $this->gallery_prefs['gallery_thumb_width'],  
									  $this->gallery_prefs['gallery_thumb_height'], 
									  $this->gallery_prefs['gallery_thumb_quality']);
		
		if ($this->gallery_prefs['gallery_create_medium'] == 'y')
			$thumbs['med'] = array($this->gallery_prefs['gallery_medium_prefix'], 
									$this->gallery_prefs['gallery_medium_width'], 
									$this->gallery_prefs['gallery_medium_height'], 
									$this->gallery_prefs['gallery_medium_quality']);		


		/** --------------------------------
		/**  Invoke the Image Lib Class
		/** --------------------------------*/
		
		if ( ! class_exists('Image_lib'))
		{ 
			require PATH_CORE.'core.image_lib'.EXT;
		}
		
		$IM = new Image_lib();
		
		$vals = $IM->get_image_properties($this->upload_path.$image_name, TRUE);
		
		$width  		= $vals['width'];
		$height 		= $vals['height'];
		$t_width		= 0;
		$t_height		= 0;
		$m_width		= 0;
		$m_height		= 0;		
		
		/** --------------------------------
		/**  Do the thumbs require watermark?
		/** --------------------------------*/
		
		// We need to determine if ether the thumbnail
		// or the medium sized image require the watermark.
		// If the do, we will add the watermark to the full-sized
		// image and create our thumbs from it.  If not, we will
		// create a temporary copy of the full-sized image without
		// the watermark and use it as the basis for the thumbs.
		// Since the thumb and medium image can have separate prefs
		// we have to test for each individually.
		
		$temp_marker		= '58fdhCX9ZXd0guhh';
		$create_tmp_copy	= FALSE;
		$tmp_thumb_name		= $image_name;
		$tmp_medium_name	= $image_name;
		
		if ($this->gallery_prefs['gallery_create_thumb'] == 'y' AND $this->gallery_prefs['gallery_wm_apply_to_thumb'] == 'n')
		{
			$create_tmp_copy = TRUE;
			$tmp_thumb_name = $temp_marker.$image_name;			
		}
		
		if ($this->gallery_prefs['gallery_create_medium'] == 'y' AND $this->gallery_prefs['gallery_wm_apply_to_medium'] == 'n')
		{
			$create_tmp_copy = TRUE;
			$tmp_medium_name = $temp_marker.$image_name;			
		}
		
		if ($create_tmp_copy == TRUE)
		{
			@copy($this->upload_path.$image_name, $this->upload_path.$temp_marker.$image_name);
		}
	
		/** --------------------------------
		/**  Apply Watermark to main image
		/** --------------------------------*/
	
		if ($this->gallery_prefs['gallery_wm_type'] != 'n' && in_array($this->gallery_prefs['gallery_image_protocal'], array('gd', 'gd2')))
		{		
			$res = $IM->set_properties(	
									array (
										'resize_protocol'		=> 	$this->gallery_prefs['gallery_image_protocal'],
										'libpath'				=>  $this->gallery_prefs['gallery_image_lib_path'],
										'file_path'				=>	$this->upload_path,
										'file_name'				=>	$image_name,
										'wm_image_path'			=>	$this->gallery_prefs['gallery_wm_image_path'],	
										'wm_use_font'			=>	($this->gallery_prefs['gallery_wm_use_font'] == 'y') ? TRUE : FALSE,
										'dynamic_output'		=>	FALSE,
										'wm_font'				=>	$this->gallery_prefs['gallery_wm_font'],
										'wm_font_size'			=>	$this->gallery_prefs['gallery_wm_font_size'],	
										'wm_text_size'			=>	5,
										'wm_text'				=>	$this->gallery_prefs['gallery_wm_text'],
										'wm_vrt_alignment'		=>	$this->gallery_prefs['gallery_wm_vrt_alignment'],	
										'wm_hor_alignment'		=>	$this->gallery_prefs['gallery_wm_hor_alignment'],
										'wm_padding'			=>	$this->gallery_prefs['gallery_wm_padding'],
										'wm_x_offset'			=>	$this->gallery_prefs['gallery_wm_x_offset'],
										'wm_y_offset'			=>	$this->gallery_prefs['gallery_wm_y_offset'],
										'wm_x_transp'			=>	$this->gallery_prefs['gallery_wm_x_transp'],
										'wm_y_transp'			=>	$this->gallery_prefs['gallery_wm_y_transp'],
										'wm_text_color'			=>	$this->gallery_prefs['gallery_wm_text_color'],
										'wm_use_drop_shadow'	=>	($this->gallery_prefs['gallery_wm_use_drop_shadow']) ? TRUE : FALSE,
										'wm_shadow_color'		=>	$this->gallery_prefs['gallery_wm_shadow_color'],
										'wm_shadow_distance'	=>	$this->gallery_prefs['gallery_wm_shadow_distance'],
										'wm_opacity'			=>	$this->gallery_prefs['gallery_wm_opacity']
								  )
							);
			
			$type = ($this->gallery_prefs['gallery_wm_type']	 == 't') ? 'text_watermark' : 'image_watermark';
												
			if ( ! $res)
			{
				$this->message_array[] = implode("\n", $IM->error_msg);
				return false;
			}
			if ( ! $IM->$type())
			{  
				$this->message_array[] = implode("\n", $IM->error_msg);
				return false;
			}			
		}

		/** --------------------------------
		/**  Create the thumbnails
		/** --------------------------------*/
		
		if (isset($thumbs) AND count($thumbs) > 0)
		{
			foreach ($thumbs as $key => $val)
			{
				$res = $IM->set_properties(			
											array(
													'resize_protocol'	=> $this->gallery_prefs['gallery_image_protocal'],
													'libpath'			=> $this->gallery_prefs['gallery_image_lib_path'],
													'maintain_ratio'	=> ($this->gallery_prefs['gallery_maintain_ratio'] == 'y') ? TRUE : FALSE,
													'thumb_prefix'		=> $val['0'],
													'file_path'			=> $this->upload_path,
													'file_name'			=> ($key == 'thumb') ? $tmp_thumb_name : $tmp_medium_name,
													'new_file_name'		=> $image_name,
													'quality'			=> $val['3'],
													'dst_width'			=> $val['1'],
													'dst_height'		=> $val['2']
													)
											);
											
				if ($res === FALSE OR ! $IM->image_resize())
				{
					$this->message_array[] = implode("\n", $IM->error_msg);
					return false;
				}
				
				if ($key == 'thumb')
				{
					$t_width  = $IM->dst_width;
					$t_height = $IM->dst_height;
				}
				else
				{
					$m_width  = $IM->dst_width;
					$m_height = $IM->dst_height;
				}
				
				$IM->initialize();
			}		
		}

        /** --------------------------------
        /**  Remove the temporary image
        /** --------------------------------*/
		
		if ($create_tmp_copy == TRUE)
		{
			unlink($this->upload_path.$temp_marker.$image_name);
		}
		
		/** ------------------------------------
        /**  Email Status Check - open/closed
        /** ------------------------------------*/
			
		if ($this->post_data['status'] != 'open' && $this->post_data['status'] != 'closed')
		{
			$this->post_data['status'] = 'open';
		}
			
        /** --------------------------------
        /**  Insert New Entry
        /** --------------------------------*/
        
        $xy = explode(".", $image_name);
		$extension	= '.'.end($xy);
		$filename	= str_replace($extension, '', $image_name);

		$data = array(
						'entry_id'			=> '',
						'gallery_id'		=> $this->gallery_prefs['gallery_id'],
						'cat_id'			=> $this->moblog_array['moblog_gallery_category'],
						'author_id'			=> ($this->author != '') ? $this->author : $this->moblog_array['moblog_gallery_author'],
						'filename'			=> $filename,
						'extension'			=> $extension,
						'title'				=> $entry_title,
						'caption'			=> $this->body,
						'status'			=> ($this->post_data['status'] == 'closed') ? 'c' : 'o',
						'width'				=> $width,
						'height'			=> $height,
						't_width'			=> $t_width,
						't_height'			=> $t_height,
						'm_width'			=> $m_width,
						'm_height'			=> $m_height,
						'entry_date'		=> ($LOC->now + $this->entries_added - $this->time_offset),
						'allow_comments'	=> $this->moblog_array['moblog_gallery_comments'] 
					);


        $DB->query($DB->insert_string('exp_gallery_entries', $data));
        $insert_id = $DB->insert_id;

        $this->update_cat_total($this->moblog_array['moblog_gallery_category']);
		
    	$this->entries_added++;
	}
	/* END */
    
    
    
    /** -------------------------------------
	/**  Post Weblog Entry
	/** -------------------------------------*/

	function post_entry()
	{
    	global $DB, $REGX, $LOC, $PREFS, $FNS, $SESS;
		
		/** --------------------------------
		/**  Default Weblog Data
		/** --------------------------------*/
    	
    	$weblog_id = $this->moblog_array['moblog_weblog_id'];
    	$query = $DB->query("SELECT site_id, blog_title, blog_url, rss_url, ping_return_url, comment_url, deft_comments, deft_trackbacks, cat_group, field_group, weblog_notify, weblog_notify_emails 
    						 FROM exp_weblogs 
    						 WHERE weblog_id = '$weblog_id'"); 
    	
    	if ($query->num_rows == 0)
        {
            $this->message_array[] = 'invalid_weblog'; // How the hell did this happen?
            return false;
        }
        
		$site_id = $query->row['site_id'];
		
        $notify_address = ($query->row['weblog_notify'] == 'y' AND $query->row['weblog_notify_emails'] != '') ? $query->row['weblog_notify_emails'] : '';

		/** ---------------------------------
		/**  Is URL title unique?
		/** ---------------------------------*/
		
		$this->post_data['subject'] = strip_tags($this->post_data['subject']);
		
		$url_title = $REGX->create_url_title($this->post_data['subject'], TRUE);
		
		if ($url_title == '' OR is_numeric($url_title))
		{
			$url_title = $this->url_title_word;
		}
		
		$sql = "SELECT count(*) AS count FROM exp_weblog_titles WHERE url_title = '".$DB->escape_str($url_title)."' AND weblog_id = '$weblog_id'";
		$results = $DB->query($sql);
		
		// Already have default title
		if ($results->row['count'] > 0)
		{
			// Give it a moblog title
			$inbetween = ($PREFS->ini('word_separator') == 'dash') ? '-' : '_';
			$url_title .= $inbetween.$this->url_title_word;
		
			/** ------------------------------------------------
			/**  Check for multiple moblog titles for default title
			/** ------------------------------------------------*/
			
			$sql = "SELECT count(*) AS count FROM exp_weblog_titles WHERE url_title LIKE '".$DB->escape_like_str($url_title)."%' AND weblog_id = '$weblog_id'";
			$results = $DB->query($sql);
			$url_title .= $results->row['count']+1;
		}		
		
		$this->moblog_array['moblog_author_id'] = ($this->moblog_array['moblog_author_id'] == 'none') ? '1' : $this->moblog_array['moblog_author_id'];
		$author_id = ($this->author != '') ? $this->author : $this->moblog_array['moblog_author_id'];
		
		if ( ! is_numeric($author_id) || $author_id == '0')
		{
			$author_id = '1';
		}
		
		$entry_date = $LOC->now + $this->entries_added - $this->time_offset;
    	
		/** ---------------------------------
        /**  Build our query string
        /** ---------------------------------*/
        
        $edata = array(  
                        'entry_id'          => '',
                        'weblog_id'         => $weblog_id,
						'site_id'			=> $site_id,
                        'author_id'         => $author_id,
                        'title'             => ($PREFS->ini('auto_convert_high_ascii') == 'y') ? $REGX->ascii_to_entities($this->post_data['subject']) : $this->post_data['subject'],
                        'url_title'         => $url_title,
                        'ip_address'		=> $this->post_data['ip'],
                        'entry_date'        => $entry_date,
						'edit_date'			=> gmdate("YmdHis", $entry_date),
                        'year'              => gmdate('Y', $entry_date),
                        'month'             => gmdate('m', $entry_date),
                        'day'               => gmdate('d', $entry_date),
                        'sticky'            => (isset($this->post_data['sticky'])) ? $this->post_data['sticky'] : $this->sticky,
                        'status'            => ($this->post_data['status'] == 'none') ? 'open' : $this->post_data['status'],
                        'allow_comments'    => $query->row['deft_comments'],
                        'allow_trackbacks'  => $query->row['deft_trackbacks']
                     );

		if ($PREFS->ini('honor_entry_dst') == 'y')
        {
        	$edata['dst_enabled'] = ($PREFS->ini('daylight_savings') == 'y') ? 'y' : 'n';
        }
        
        /** ---------------------------------
        /**  Insert the entry
        /** ---------------------------------*/
        
        $sql = $DB->insert_string('exp_weblog_titles', $edata);
		    
		$DB->query($sql); 
		
		$entry_id = $DB->insert_id;  
		
				
		/** --------------------------------
		/**  Remove ignore text
		/** --------------------------------*/
		
		$this->body = preg_replace("#<img\s+src=\s*[\"']cid:(.*?)\>#si", '', $this->body);  // embedded images
		
		$this->moblog_array['moblog_ignore_text'] = $this->remove_newlines($this->moblog_array['moblog_ignore_text'],$this->newline);
		
		// One biggo chunk
		if ($this->moblog_array['moblog_ignore_text'] != '' && stristr($this->body,$this->moblog_array['moblog_ignore_text']) !== FALSE)
		{
			$this->body = str_replace($this->moblog_array['moblog_ignore_text'], '',$this->body);
		}
		elseif($this->moblog_array['moblog_ignore_text'] != '')
		{
			// By line
			$delete_text	= $this->remove_newlines($this->moblog_array['moblog_ignore_text'],$this->newline);
			$delete_array	= explode($this->newline,$delete_text);
		
			if (sizeof($delete_array) > 0)
			{
				foreach($delete_array as $ignore)
				{
					if (trim($ignore) != '')
					{
						$this->body = str_replace(trim($ignore), '',$this->body);
					}		
				}		
			}
		}	
		
		
		/** -------------------------------------
		/**  Specified Fields for Email Text
		/** -------------------------------------*/
		
		if (preg_match_all("/[\<\{]field\:(.*?)[\}\>](.*?)[\<\{]\/field\:(.*?)[\}\>]/", $this->body, $matches))
		{
			/* -------------------------------------
			/*  Hidden Configuration Variable
			/*  - moblog_allow_nontextareas => Removes the textarea only restriction
			/*	for custom fields in the moblog module (y/n)
			/* -------------------------------------*/
		
			$xsql = ($PREFS->ini('moblog_allow_nontextareas') == 'y') ? "" : " AND exp_weblog_fields.field_type = 'textarea' ";
			
			$results = $DB->query("SELECT exp_weblog_fields.field_id, exp_weblog_fields.field_name, 
								   exp_weblog_fields.field_label, exp_weblog_fields.field_fmt
								   FROM exp_weblogs, exp_weblog_fields
								   WHERE exp_weblogs.field_group = exp_weblog_fields.group_id
								   AND exp_weblogs.weblog_id = '".$this->moblog_array['moblog_weblog_id']."'
								   {$xsql}");				   
			
			if ($results->num_rows > 0)
			{
				$field_name  = array();
				$field_label = array();
				$field_format = array();
				
				foreach($results->result as $row)
				{
					$field_name[$row['field_id']]	= $row['field_name'];
					$field_label[$row['field_id']]	= $row['field_label'];
					$field_format[$row['field_id']] = $row['field_fmt'];
				}
				
				unset($results);
				
				for($i=0; $i < sizeof($matches['0']); $i++)
				{
					$x = preg_split("/[\s]+/", $matches['1'][$i]);
					
					if ($key = array_search($x['0'],$field_name) OR $key = array_search($x['0'],$field_label))
					{
						$format = ( ! isset($x['1']) || !stristr($x['1'],"format")) ? $field_format[$key] : preg_replace("/format\=[\"\'](.*?)[\'\"]/","$1",trim($x['1']));
						
						$matches['2'][$i] = str_replace($this->newline, "\n",$matches['2'][$i]);
						
						if ( ! isset($this->entry_data[$key]))
						{
							$this->entry_data[$key] = array('data' => $matches['2'][$i],
															'format' => $format);
						}
						else
						{
							$this->entry_data[$key] = array('data' => $matches['2'][$i].$this->entry_data[$key]['data'],
															'format' => $format);						
						}
						
						$this->body = str_replace($matches['0'][$i], '', $this->body);
					}					
				}
			}
		}
				
			
		/** --------------------------------
		/**  Return New Lines
		/** --------------------------------*/
		
		$this->body = str_replace($this->newline, "\n",$this->body);
		
		/** ------------------------------------
		/**  Parse template
		/** ------------------------------------*/
		
		$this->template = str_replace('/', SLASH, $this->moblog_array['moblog_template']);
		
		$tag = 'field';
		
		if( ! preg_match_all("/".LD.$tag."(.*?)".RD."(.*?)".LD.SLASH.$tag.RD."/s", $this->template, $matches))
        {
        	$this->parse_field($this->moblog_array['moblog_field_id'],$this->template, $query->row['field_group']);
        }
        else
        { 
        	for($i=0; $i < sizeof($matches['0']) ; $i++)
        	{
        		$params = $this->assign_parameters($matches['1'][$i]);
			
				$params['format']	= ( ! isset($params['format'])) ? '' : $params['format'];
				$params['name'] 	= ( ! isset($params['name'])) 	? '' : $params['name'];
			
				$this->parse_field($params,$matches['2'][$i], $query->row['field_group']); 				
				$this->template = str_replace($matches['0'],'',$this->template);
			}
			
			if (trim($this->template) != '')
			{
				$this->parse_field($this->moblog_array['moblog_field_id'],$this->template, $query->row['field_group']);		
			}
        }
		
		/** ------------------------------------
		/**  Insert entry data
		/** ------------------------------------*/
		
		$data = array(	'entry_id' 	=> $entry_id,
						'weblog_id'	=> $weblog_id,
						'site_id'	=> $site_id);		
		
		if (sizeof($this->entry_data) > 0)
		{		
			foreach($this->entry_data as $key => $value)
			{
				// ----------------------------------------
				//  Put this in here in case some one has
				//  {field:body}{/field:body} in their email
				//  and yet has their default field set to none
				// ----------------------------------------
				
				if ($key == 'none')
				{
					continue;
				}
				
				$combined_data = str_replace(SLASH, '/', $value['data']);
				$combined_data = ($PREFS->ini('auto_convert_high_ascii') == 'y') ? $REGX->ascii_to_entities(trim($combined_data)) : trim($combined_data);
				
				$data['field_id_'.$key] = $combined_data;
				$data['field_ft_'.$key] = $value['format'];				
			}
		}
		
		$DB->query($DB->insert_string('exp_weblog_data', $data));
		
				
		/** ------------------------------------
		/**  Insert Categories
		/** ------------------------------------*/
		
		if (isset($this->post_data['categories']) && $this->post_data['categories']  == 'all')
		{
			$cat_array = array();
        	$results = $DB->query("SELECT cat_id FROM exp_categories WHERE group_id IN ('".str_replace('|', "','", $DB->escape_str($query->row['cat_group']))."')");
        		
        	if ($results->num_rows > 0)
        	{
        		foreach($results->result as $row)
        		{
        			$cat_array[] = $row['cat_id'];
        		}
        		
        		$this->post_data['categories'] = implode('|',$cat_array);
        	}
        	else
        	{
        		$this->post_data['categories'] = '';
        	}
		}
		
		if (isset($this->post_data['categories']) && strlen($this->post_data['categories']) > 0 && $this->post_data['categories'] != 'none')
        {         	
        	$cats = explode('|',$this->post_data['categories']);
        	$cats = array_unique($cats);
        	
        	$results = $DB->query("SELECT cat_id, parent_id FROM exp_categories 
        							WHERE (cat_id IN ('".implode("','",$cats)."') OR cat_name IN ('".implode("','",$cats)."'))
        							AND group_id IN ('".str_replace('|', "','", $DB->escape_str($query->row['cat_group']))."')");
        	
        	if ($results->num_rows > 0)
        	{
            	foreach($results->result as $row)
            	{
            		if ($PREFS->ini('auto_assign_cat_parents') == 'y' && $row['parent_id'] != '0')
            		{
            			$DB->query("INSERT INTO exp_category_posts (entry_id, cat_id) VALUES ('$entry_id', '".$row['parent_id']."')");
            		}
            		
            		$DB->query("INSERT INTO exp_category_posts (entry_id, cat_id) VALUES ('$entry_id', '".$row['cat_id']."')");
                }
            }
        }
        
		/** ----------------------------
		/**  Send admin notification
		/** ----------------------------*/
				
		if ($notify_address != '')
		{         
			$comment_url = ($query->row['comment_url'] == '') ? $query->row['blog_url'] : $query->row['comment_url'];
			
			$results = $DB->query("SELECT screen_name, email FROM exp_members WHERE member_id = '".$DB->escape_str($edata['author_id'])."'");
		
			$swap = array(
							'name'				=> $results->row['screen_name'],
							'email'				=> $results->row['email'],
							'weblog_name'		=> $query->row['blog_title'],
							'entry_title'		=> $edata['title'],
							'entry_url'			=> $FNS->remove_double_slashes($query->row['blog_url'].'/'.$edata['url_title'].'/'),
							'comment_url'		=> $FNS->remove_double_slashes($comment_url.'/'.$edata['url_title'].'/')
						 );
			
			$template = $FNS->fetch_email_template('admin_notify_entry');

			$email_tit = $FNS->var_swap($template['title'], $swap);
			$email_msg = $FNS->var_swap($template['data'], $swap);
							   
			// We don't want to send a notification if the person
			// leaving the entry is in the notification list
			$notify_address = str_replace($results->row['email'],'', $notify_address);
			
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
	
	
		/** ------------------------------------
		/**  Update member stats
		/** ------------------------------------*/
						
		$DB->query("UPDATE exp_members 
					SET total_entries = total_entries + 1, last_entry_date = '".($LOC->now + $this->entries_added - $this->time_offset)."' 
					WHERE member_id = '".$author_id."'");		 
    	
    	if (isset($this->moblog_array['moblog_ping_servers']) && $this->moblog_array['moblog_ping_servers'] != '')
		{
			$this->moblog_array['blog_title'] = $REGX->ascii_to_entities($query->row['blog_title']);
			$this->moblog_array['blog_url'] = ($query->row['ping_return_url'] == '') ? $query->row['blog_url'] : $query->row['ping_return_url'];
			$this->moblog_array['rss_url']	= $query->row['rss_url'];
		}
    	
    	$this->entries_added++;
    	
	}
	/* END */
	
	
	
	/** -------------------------------------
    /**  Send Pings
    /** -------------------------------------*/
    
    function send_pings($title, $url)
    {
    	global $DB, $PREFS;
    	
    	$ping_servers = explode('|', $this->moblog_array['moblog_ping_servers']);
    	
    	$sql = "SELECT server_name, server_url, port FROM exp_ping_servers WHERE id IN (";
    	
    	foreach ($ping_servers as $id)
    	{
    		$sql .= "'$id',";    	
    	}
    	
    	$sql = substr($sql, 0, -1).') ';
    	
		$query = $DB->query($sql);
    	
    	if ($query->num_rows == 0)
    	{
			return false;    	
    	}
    	
		if ( ! class_exists('XML_RPC'))
		{
			require PATH_CORE.'core.xmlrpc'.EXT;
		}
		
		$XRPC = new XML_RPC;
		
		$result = array();
    	
		foreach ($query->result as $row)
		{
			if ($XRPC->weblogs_com_ping($row['server_url'], $row['port'], $title, $url))
			{
				$result[] = $row['server_name'];
			}
		}		
		
		return $result;
    }
    /* END */
	
	
	
	/** -------------------------------------
    /**  Return parameters as an array - Use TMPL one eventually
    /** -------------------------------------*/
    
    //  Creates an associative array from a string
    //  of parameters: sort="asc" limit="2" etc.
    
    function assign_parameters($str)
    {                        
        if ($str == "")
            return false;

		// \047 - Single quote octal
		// \042 - Double quote octal
		
		// I don't know for sure, but I suspect using octals is more reliable than ASCII.
		// I ran into a situation where a quote wasn't being matched until I switched to octal.
		// I have no idea why, so just to be safe I used them here. - Rick
		
		if (preg_match_all("/(\S+?)\s*=[\042\047](\s*.+?\s*)[\042\047]\s*/", $str, $matches))
		{
			$result = array();
		
			for ($i = 0; $i < count($matches['1']); $i++)
			{
				$result[$matches['1'][$i]] = $matches['2'][$i];
			}
			
			return $result;
		}
  
        return false;
    }
    /* END */
    
    
	/** -------------------------------------
	/**  Parse Field
	/** -------------------------------------*/
	
    function parse_field($params,$field_data, $field_group)
    {
    	global $DB, $PREFS;	
    	
    	$field_id = '1';
    	$format = 'none';
    	
    	/** -----------------------------
    	/**  Determine Field Id and Format
    	/** -----------------------------*/
    	
    	if ( ! is_array($params))
    	{
    		$field_id = $params;
    		
    		$results = $DB->query(	"SELECT field_fmt FROM exp_weblog_fields 
    								 WHERE field_id = '{$field_id}'");    
    		
    		$format = ($results->num_rows > 0) ? $results->row['field_fmt'] : 'none';
    	}
    	else
    	{
    		if ($params['name'] != '' && $params['format'] == '')
    		{
    			$xsql = ($PREFS->ini('moblog_allow_nontextareas') == 'y') ? "" : " AND exp_weblog_fields.field_type = 'textarea' ";
    			
    			$results = $DB->query(	"SELECT field_id, field_fmt FROM exp_weblog_fields 
    									WHERE group_id = '".$field_group."'
    								 	AND (field_name = '".$params['name']."'
    								 	OR field_label = '".$params['name']."')
    								 	{$xsql}"); 
    								 	
    			$field_id	= ($results->num_rows > 0) ? $results->row['field_id'] : $this->moblog_array['moblog_field_id'];
    			$format 	= ($results->num_rows > 0) ? $results->row['field_fmt'] : 'none';
    		}
    		elseif($params['name'] == '' && $params['format'] == '')
    		{
    			$field_id = $this->moblog_array['moblog_field_id'];
    			$results  = $DB->query(	"SELECT field_fmt FROM exp_weblog_fields 
    						 			 WHERE field_id = '{$field_id}'"); 
    								 	
    			$format   = $results->row['field_fmt'];    			
    		}
    		elseif($params['name'] == '' && $params['format'] != '')
    		{
    			$field_id	= $this->moblog_array['moblog_field_id'];
    			$format		= $params['format'];
    		}
    		elseif($params['name'] != '' && $params['format'] != '')
    		{
    			$xsql = ($PREFS->ini('moblog_allow_nontextareas') == 'y') ? "" : " AND exp_weblog_fields.field_type = 'textarea' ";
    			
    			$results = $DB->query(	"SELECT field_id FROM exp_weblog_fields 
    										WHERE group_id = '".$field_group."'
    									 	AND (field_name = '".$params['name']."'
    									 	OR field_label = '".$params['name']."')
    									 	{$xsql}"); 
    									 	
    			$field_id	= ($results->num_rows > 0) ? $results->row['field_id'] : $this->moblog_array['moblog_field_id'];
    			$format		= $params['format'];
    		}
    	}    	
    	
    	/** -----------------------------
    	/**  Parse Content
    	/** -----------------------------*/
    		
    	$pair_array = array('images','audio','movie','files');  
    	$float_data = $this->post_data;
    	$params = array();
    	
		foreach ($pair_array as $type)
        {
        	if ( ! preg_match_all("/".LD.$type."(.*?)".RD."(.*?)".LD.SLASH.$type.RD."/s", $field_data, $matches))
        	{
        		continue;
        	}        	
        	
        	if(sizeof($matches['0']) == 0)
        	{
        		continue;
        	}
        	
        	for ($i=0; $i < sizeof($matches['0']) ; $i++)
        	{	
        		$template_data = '';
        		
        		if ($type != 'files' && ( ! isset($float_data[$type]) || sizeof($float_data[$type]) == 0))
        		{
        			$field_data = str_replace($matches['0'][$i],'',$field_data); 
        			continue;
        		}        	        	
        	
        		// Assign parameters, if any
        		if(isset($matches['1'][$i]) && trim($matches['1'][$i]) != '')
        		{
        			$params = $this->assign_parameters(trim($matches['1'][$i]));
        		}
				
				$params['match'] = ( ! isset($params['match'])) ? '' : $params['match'];
        		
        		/** ----------------------------
        		/**  Parse Pairs 
        		/** ----------------------------*/
        	
        		// Files is a bit special.  It goes last and will clear out remaining files.  Has match parameter
        		if ($type == 'files' && $params['match'] != '')
        		{
        			if (sizeof($float_data) > 0)
					{
						foreach ($float_data as $ftype => $value)
						{
							if (in_array($ftype,$pair_array) && ($params['match'] == 'all' || stristr($params['match'],$ftype)))
							{  
								foreach($float_data[$ftype] as $k => $file)
								{
									if ( ! is_array($file))
									{
										$template_data .= str_replace('{file}',$this->upload_dir_code.$file,$matches['2'][$i]);
									}
									elseif(is_array($file) && $ftype == 'images')
									{
										$temp_data = '';
										$details = array();
										$filename					= ( ! isset($file['filename'])) ? '' : $this->upload_dir_code.$file['filename'];
										$details['width']			= ( ! isset($file['width'])) ? '' : $file['width'];
										$details['height']			= ( ! isset($file['height'])) ? '' : $file['height'];
										$details['thumbnail']		= ( ! isset($file['thumbnail'])) ? '' : $this->upload_dir_code.$file['thumbnail'];
										$details['thumb_width']		= ( ! isset($file['thumb_width'])) ? '' : $file['thumb_width'];
										$details['thumb_height']	= ( ! isset($file['thumb_height'])) ? '' : $file['thumb_height'];
										
										$temp_data = str_replace('{file}',$filename,$matches['2'][$i]);
										
										foreach($details as $d => $dv)
										{
											$temp_data = str_replace('{'.$d.'}',$dv,$temp_data);
										}				
										
										$template_data .= $temp_data;									
									}
									
									//unset($float_data[$ftype][$k]);	
								}
							}
						}			
					}	
        		}
        		elseif(isset($float_data[$type]))
        		{
        			foreach($float_data[$type] as $k => $file)
					{
						if ( ! is_array($file))
						{
							$template_data .= str_replace('{file}',$this->upload_dir_code.$file,$matches['2'][$i]);
						}
						elseif(is_array($file) && $type == 'images')
						{
							$temp_data = '';
							$details = array();
							$filename					= ( ! isset($file['filename'])) ? '' : $this->upload_dir_code.$file['filename'];
							$details['width']			= ( ! isset($file['width'])) ? '' : $file['width'];
							$details['height']			= ( ! isset($file['height'])) ? '' : $file['height'];
							$details['thumbnail']		= ( ! isset($file['thumbnail'])) ? '' : $this->upload_dir_code.$file['thumbnail'];
							$details['thumb_width']		= ( ! isset($file['thumb_width'])) ? '' : $file['thumb_width'];
							$details['thumb_height']	= ( ! isset($file['thumb_height'])) ? '' : $file['thumb_height'];
									
							$temp_data = str_replace('{file}',$filename,$matches['2'][$i]);
										
							foreach($details as $d => $dv)
							{
								$temp_data = str_replace('{'.$d.'}',$dv,$temp_data);
							}				
									
							$template_data .= $temp_data;						
						}
						
						//unset($float_data[$type][$k]);	
					}  	
        		}
        	
        		// Replace tag pair with template data
        		$field_data = str_replace($matches['0'][$i],$template_data,$field_data);
        		
        		// Unset member of float data array
        		if (isset($float_data[$type]) && sizeof($float_data[$type]) == 0)
        		{
        			unset($float_data[$type]);
        		}
        	}	
		}
		
		/** ------------------------------
		/**  Variable Single:  text
		/** ------------------------------*/
		
		$field_data = str_replace(array('{text}', '{sender_email}'), array($this->body, $this->sender_email), $field_data);
		
		$this->entry_data[$field_id]['data'] 	= ( ! isset($this->entry_data[$field_id])) ? $field_data : $this->entry_data[$field_id]['data']."\n".$field_data;
		$this->entry_data[$field_id]['format'] 	= $format;
	}
    /* END */
	
	
	/** -------------------------------------
	/**  Parse Email
	/** -------------------------------------*/
	
	function parse_email($email_data,$type='norm')
	{		
		global $DB, $PREFS, $REGX, $FNS;
		
		$boundary = ($type != 'norm') ? $this->multi_boundary : $this->boundary;
		$email_data = str_replace('boundary='.substr($boundary,2),'BOUNDARY_HERE',$email_data);
		
		$email_parts = explode($boundary, $email_data);
		
		if (sizeof($email_parts) < 2)
		{
			$boundary = str_replace("+","\+", $boundary);
			$email_parts = explode($boundary, $email_data);
		}		
		
		if (sizeof($email_parts) < 2)
		{
			return false;
			unset($email_parts);
			unset($email_data);
		}		
		
		/** ---------------------------
		/**  Determine Upload Path
		/** ---------------------------*/
		
		if ( isset($this->moblog_array['moblog_type']) && $this->moblog_array['moblog_type'] == 'gallery')
		{
			$this->upload_path = $this->gallery_prefs['gallery_upload_path'];
			
			/** --------------------------------
        	/**  Category Specified in Email
        	/** --------------------------------*/
			
			if (preg_match("/\{category\}(.*)\{\/category\}/", $email_data, $cats) 
				OR preg_match("/\<category\>(.*)\<\/category\>/", $email_data, $cats))
			{
				$results = $DB->query("SELECT cat_id, cat_folder 
										FROM exp_gallery_categories
										WHERE (cat_id ='".trim($cats['1'])."' 
										OR cat_name = '".trim($cats['1'])."')
										AND gallery_id = '".$this->gallery_prefs['gallery_id']."'");
		
				if ($results->num_rows > 0)
				{
					$this->moblog_array['moblog_gallery_category'] = $results->row['cat_id'];
					$cat_folder = ($results->row['cat_folder'] != '') ? $results->row['cat_folder'] : '';
				}
			}			
			
			/** --------------------------------
        	/**  Default Category Folder
        	/** --------------------------------*/
        	
        	if ( ! isset($cat_folder))
        	{
       			$result = $DB->query("SELECT cat_folder 
       								   FROM exp_gallery_categories 
       								   WHERE cat_id = '".$this->moblog_array['moblog_gallery_category']."' 
       								   AND gallery_id = '".$this->gallery_prefs['gallery_id']."'");
       							   
       							   
				$cat_folder = ($result->row['cat_folder'] != '') ? $result->row['cat_folder'] : '';
    		}
    		
        	$this->upload_path = $FNS->remove_double_slashes($this->upload_path.'/'.$cat_folder.'/');			
		}
		else
		{
			$query = $DB->query("SELECT server_path FROM exp_upload_prefs
								  WHERE id = '".$DB->escape_str($this->moblog_array['moblog_upload_directory'])."'");
								  
			if ($query->num_rows == 0)
			{
				$this->message_array[] = 'invalid_upload_directory';
				return false;
			}
			
			$this->upload_path = $query->row['server_path'];			
		}
		
		if ( ! is_writable($this->upload_path))
		{
			$system_absolute = str_replace('/modules/moblog/mod.moblog.php','',__FILE__);
			$addon = (substr($this->upload_path,0,2) == './') ? substr($this->upload_path,2) : $this->upload_path;
		
			while(substr($addon,0,3) == '../')
			{
				$addon = substr($addon,3);
			
				$p1 = (strrpos($system_absolute,'/') !== false) ? strrpos($system_absolute,'/') : strlen($system_absolute);
				$system_absolute = substr($system_absolute,0,$p1);
			}
			
			if (substr($system_absolute,-1) != '/')
			{
				$system_absolute .= '/';
			}
		
			$this->upload_path = $system_absolute.$addon;	

			if ( ! is_writable($this->upload_path))
			{
				$this->message_array[] = 'upload_directory_unwriteable';
				return false;
			}
		}
		
		$this->upload_path = rtrim($this->upload_path, '/').'/';
		$this->upload_dir_code = '{filedir_'.$this->moblog_array['moblog_upload_directory'].'}';
		
		/** ---------------------------
		/**  Find Attachments
		/** ---------------------------*/
		
		foreach($email_parts as $key => $value)
		{
			// Skip headers and those with no content-type
			if ($key == '0' || ! preg_match("#".preg_quote('Content-Type:')."#i", $value))
			{
				continue;	
			}
			
			$contents		= $this->find_data($value, "Content-Type:", $this->newline);
			$x				= explode(';',$contents);
			$content_type	= $x['0'];			
			
			$content_type	= strtolower($content_type);
			$pieces			= explode('/',trim($content_type));
			$type			= trim($pieces['0']);
			$subtype		= ( ! isset($pieces['1'])) ? '0' : trim($pieces['1']);
			
			$charset		= 'auto';
			
			/** --------------------------
			/**  Outlook Exception 
			/** --------------------------*/
			if ($type == 'multipart' && $subtype != 'appledouble')
			{	
				if( ! stristr($value,'boundary='))
				{
					continue;
				}
				
				$this->multi_boundary = "--".$this->find_data($value, "boundary=", $this->newline);
				$this->multi_boundary = trim(str_replace('"','',$this->multi_boundary));
				
				if (strlen($this->multi_boundary) == 0)
				{
					continue;
				}
				
				$this->parse_email($value,'multi');
				$this->multi_boundary = '';
				continue;				
			}
			
			
			/** --------------------------
			/**  Quick Grab of Headers
			/** --------------------------*/
			$headers = $this->find_data($value, '', $this->newline.$this->newline);
			
			/** ---------------------------
			/**  Text : plain, html, rtf
			/** ---------------------------*/
			if ($type == 'text' && $headers != '' && 
			   (($this->txt_override === TRUE && $subtype == 'plain') OR ! stristr($headers,'name=')))
			{
				$duo   =  $this->newline.$this->newline;
				$text  = $this->find_data($value, $duo,'');
				
				if ($text == '')
				{
					$text = $this->find_data($value, $this->newline,'');
				}
				
				/** ------------------------------------
				/**  Charset Available?
				/** ------------------------------------*/
				
				if (preg_match("/charset=(.*?)(\s|".$this->newline.")/is", $headers, $match))
				{
					$charset = trim(str_replace(array("'", '"', ';'), '', $match['1']));
				}
				
				/** ------------------------------------
				/**  Check for Encoding of Text
				/** ------------------------------------*/
				if (stristr($value,'Content-Transfer-Encoding'))
				{
					$encoding = $this->find_data($value, "Content-Transfer-Encoding:", $this->newline);
					
					/** ------------------------------------
					/**  Check for Quoted-Printable encoding
					/** ------------------------------------*/
				
					if(stristr($encoding,"quoted-printable"))
					{
						$text = str_replace($this->newline,"\n",$text);
						$text = quoted_printable_decode($text);
						$text = (substr($text,0,1) != '=') ? $text : substr($text,1);
						$text = (substr($text,-1) != '=') ? $text : substr($text,0,-1);
						$text = $this->remove_newlines($text,$this->newline);
					}
					
					/** ------------------------------------
					/**  Check for Base 64 encoding:  MIME
					/** ------------------------------------*/
					
					elseif(stristr($encoding,"base64"))
					{
						$text = str_replace($this->newline,"\n", $text);
						$text = base64_decode(trim($text));
						$text = $this->remove_newlines($text,$this->newline);
					}
					
				}
				
				/** ----------------------------------
				/**  Spring PCS - Picture Share
				/** ----------------------------------*/
				
				// http://pictures.sprintpcs.com//shareImage/13413001858_235.jpg
				// http://pictures.sprintpcs.com/mi/8516539_30809087_0.jpeg?inviteToken=sETr4TJ9m85YizVzoka0
				
				if (trim($text) != '' && strpos($text, 'pictures.sprintpcs.com') !== false)
				{
					// Find Message
					$sprint_msg = $this->find_data($value, '<b>Message:</b>', '</font>');
					
					// Find Image
					if ($this->sprint_image($text) && $sprint_msg != '')
					{
						$text = $sprint_msg;
					}
					else
					{
						continue;
					}
				}	
				
				/** ----------------------------------
				/**  Bell Canada - Episode Two, Attack of the Sprint Clones
				/** ----------------------------------*/
				
				// http://mypictures.bell.ca//i/99376001_240.jpg?invite=SELr4RJHhma1cknzLQoU
				
				if (trim($text) != '' && strpos($text, 'mypictures.bell.ca') !== false)
				{
					// Find Message
					$bell_msg = $this->find_data($value, 'Vous avez re&ccedil;u une photo de <b>5147103855', '<img');
					$bell_msg = $this->find_data($bell_msg, '<p>', '</p>');
					
					// Find Image
					if ($this->bell_image($text) && $bell_msg != '')
					{
						$text = trim($bell_msg);
					}
					else
					{
						continue;
					}
				}	
				
				
				/** ----------------------------------
				/**  T-Mobile - In cyberspace, no one can hear you cream.
				/** ----------------------------------*/
								
				if (trim($text) != '' && stristr($text, 'This message was sent from a T-Mobile wireless phone') !== false)
				{
					$text = '';
				}
				
				if ($this->charset != $PREFS->ini('charset'))
            	{
            		if (function_exists('mb_convert_encoding'))
            		{
            			$text = mb_convert_encoding($text, strtoupper($PREFS->ini('charset')), strtoupper($this->charset));
            		}
            		elseif(function_exists('iconv') AND ($iconvstr = @iconv(strtoupper($this->charset), strtoupper($PREFS->ini('charset')), $text)) !== FALSE)
            		{
            			$text = $iconvstr;
            		}
            		elseif(strtolower($PREFS->ini('charset')) == 'utf-8' && strtolower($this->charset) == 'iso-8859-1')
            		{
            			$text = utf8_encode($text);
            		}
            		elseif(strtolower($PREFS->ini('charset')) == 'iso-8859-1' && strtolower($this->charset) == 'utf-8')
            		{
            			$text = utf8_decode($text);
            		}
            	}
				
				
				// RTF and HTML are considered alternative text
				$subtype = ($subtype != 'html' && $subtype != 'rtf') ? 'plain' : 'alt';
				
				// Same content type, then join together
				$this->post_data[$type][$subtype] = (isset($this->post_data[$type][$subtype])) ? $this->post_data[$type][$subtype]." $text" : $text; 
				
				// Plain text takes priority for body data.
				$this->body = ( ! isset($this->post_data[$type]['plain'])) ? $this->post_data[$type]['alt'] : $this->post_data[$type]['plain'];
				
			}
			elseif($type == 'image' || $type == 'application' || $type == 'audio' || $type == 'video' || $subtype == 'appledouble' || $type == 'text') // image or application
			{				
				if ($subtype == 'appledouble')
				{
					if ( ! $data = $this->appledouble($value))
					{
						continue;
					}
					else
					{
						$value 		= $data['value'];
						$subtype 	= $data['subtype'];
						$type		= $data['type'];
						unset($data);
					}
				}
			
				/** ------------------------------
				/**  Determine Filename
				/** ------------------------------*/
				$contents = $this->find_data($value, "name=", $this->newline);
				
				if ($contents == '')
				{
					$contents = $this->find_data($value, 'Content-Location:', $this->newline);
				}
				
				if ($contents == '')
				{
					$contents = $this->find_data($value, 'Content-ID:', $this->newline);
					$contents = str_replace('<','', $contents);
					$contents = str_replace('<','', $contents);
				}
				
				$x = explode(';',trim($contents));
				$filename = ($x['0'] == '') ? 'moblogfile' : $x['0'];
				
				$filename = trim(str_replace('"','',$filename));
				$filename = str_replace($this->newline,'',$filename);
				$filename = $this->safe_filename($filename);
				
				if (stristr($filename, 'dottedline') OR stristr($filename, 'spacer.gif') OR stristr($filename, 'masthead.jpg'))
				{
					continue;
				}
				
				
				/** ------------------------------
				/**  Check and adjust for multiple files with same file name
				/** ------------------------------*/
				
				$filename = $this->unique_filename($filename, $subtype);
				
				/** --------------------------------
				/**  File/Image Code and Cleanup
				/** --------------------------------*/
				
				$duo = $this->newline.$this->newline;
				$file_code = $this->find_data($value, $duo,'');
				
				if ($file_code == '')
				{
					$file_code = $this->find_data($value, $this->newline,'');
					
					if ($file_code == '')
					{
						$this->message_array = 'invalid_file_data';
						return false;
					}
				}				
				
				/** --------------------------------
				/**  Determine Encoding
				/** --------------------------------*/
				
				$contents = $this->find_data($value, "Content-Transfer-Encoding:", $this->newline);
				$x = explode(';',$contents);
				$encoding = $x['0'];
				$encoding = trim(str_replace('"','',$encoding));
				$encoding = str_replace($this->newline,'',$encoding);
				
				if ( ! stristr($encoding,"base64") &&  ! stristr($encoding,"7bit") &&  ! stristr($encoding,"8bit") && ! stristr($encoding,"quoted-printable"))
				{
					if ($type == 'text')
					{
						// RTF and HTML are considered alternative text
						$subtype = ($subtype != 'html' && $subtype != 'rtf') ? 'plain' : 'alt';
				
						// Same content type, then join together
						$this->post_data[$type][$subtype] = (isset($this->post_data[$type][$subtype])) ? $this->post_data[$type][$subtype].' '.$file_code : $file_code; 
				
						// Plain text takes priority for body data.
						$this->body = ( ! isset($this->post_data[$type]['plain'])) ? $this->post_data[$type]['alt'] : $this->post_data[$type]['plain'];
					}
					
					continue;
				}
				
				// Eudora and Mail.app use this by default
				if(stristr($encoding,"quoted-printable"))
				{
					$file_code = quoted_printable_decode($file_code);
				}
				
				// Base64 gets no space and no line breaks
				$replace = ( ! stristr($encoding,"base64")) ? "\n" : '';
				$file_code = trim(str_replace($this->newline,$replace,$file_code));
				
				// PHP function sometimes misses opening and closing equal signs
				if(stristr($encoding,"quoted-printable"))
				{
					$file_code = (substr($file_code,0,1) != '=') ? $file_code : substr($file_code,1);
					$file_code = (substr($file_code,-1) != '=') ? $file_code : substr($file_code,0,-1);
				}
				
				// Clean out 7bit and 8bit files.
				if ( ! stristr($encoding,"base64"))
				{
					$file_code = str_replace(SLASH, '/', $file_code);
					$file_code = $REGX->xss_clean($file_code);
				}
				
				/** ------------------------------
				/**  Check and adjust for multiple files with same file name
				/** ------------------------------*/
				
				$filename = $this->unique_filename($filename, $subtype);
				
				/** ---------------------------
				/**  Put Info in Post Data array
				/** ---------------------------*/
				
				if (in_array(substr($filename,-3),$this->movie) || in_array(substr($filename,-5),$this->movie)) // Movies
				{
					$this->post_data['movie'][] = $filename;
				}
				elseif (in_array(substr($filename,-3),$this->audio) || in_array(substr($filename,-4),$this->audio) || in_array(substr($filename,-2),$this->audio)) // Audio
				{
					$this->post_data['audio'][] = $filename;
				}
				elseif (in_array(substr($filename,-3),$this->image) || in_array(substr($filename,-4),$this->image)) // Images
				{					
					$this->post_data['images'][] = array('filename' => $filename);
					
					$key = sizeof($this->post_data['images']) - 1;
					
					$type = 'image'; // For those crazy application/octet-stream images
				}
				elseif (in_array(substr($filename,-2),$this->files) || in_array(substr($filename,-3),$this->files) || in_array(substr($filename,-4),$this->files)) // Files
				{
					$this->post_data['files'][] = $filename;
				}
				else
				{
					// $this->post_data['files'][] = $filename;
					continue;
				}
				
				
				// AT&T phones send the message as a .txt file
				// This checks to see if this email is from an AT&T phone, 
				// not an encoded file, and has a .txt file extension in the filename
				
				if ($this->attach_as_txt === true && !stristr($encoding,"base64"))
				{
					if(stristr($filename,'.txt') && preg_match("/Content-Disposition:\s*inline/i",$headers,$found))
					{
						$this->attach_text = $file_code;	
						$this->attach_name = $filename;
						continue; // No upload of file.
					}
				}
				
				/** ------------------------------
				/**  Write File to Upload Directory
				/** ------------------------------*/
				
				if (!$fp = @fopen($this->upload_path.$filename,'wb'))
				{
					$this->message_array[] = 'error_writing_attachment'; //.$this->upload_path.$filename;
					return false;
				}				
				
				$attachment = ( ! stristr($encoding,"base64")) ? $file_code : base64_decode($file_code);
				fwrite($fp,$attachment);
				fclose($fp);
				
				@chmod($this->upload_path.$filename, 0777);
				
				unset($attachment);
				unset($file_code);
				
				$this->email_files[] = $filename;
				$this->uploads++;
				
				
				// Only images beyond this point.
				if ($type != 'image')
				{
					continue;
				}
				
				if ( ! isset($this->moblog_array['moblog_type']) OR (isset($this->moblog_array['moblog_type']) && $this->moblog_array['moblog_type'] != 'gallery'))
				{
					$this->image_resize($filename, $key);
				}				
				
			} // End files/images section
			
		} // End foreach
		
		return true;
    }
    /* END */
    
    
	/** -------------------------------------
	/**  Retrieve Sprint Images
	/** -------------------------------------*/
	
	// <img src="http://pictures.sprintpcs.com//shareImage/13413001858_235.jpg?border=1,255,255,255,1,0,0,0&amp;invite=OEKJJD5XYYhMZ5hY8amx" border="0" />

    function sprint_image($text)
    {
    	if (preg_match_all("|(http://pictures.sprintpcs.com(.*?))\?inviteToken\=(.*?)&|i", str_replace($this->newline,"\n",$text), $matches))
    	{
    		for($i = 0; $i < sizeof($matches['0']); $i++)
    		{	
    			/*
    			if (stristr($matches['1'][$i], 'jpeg') === FALSE && stristr($matches['1'][$i], 'jpg') === FALSE)
    			{
    				continue;
    			}
    			*/
    			
    			/** ------------------------------
    			/**  Filename Creation
    			/** ------------------------------*/
    			 
    			$x = explode('/', $matches['1'][$i]);
    			
    			$filename = array_pop($x);
    			
    			if (strlen($filename) < 4)
    			{
    				$filename .= array_pop($x);
    			}
    			
    			if (stristr($filename, 'jpeg') === FALSE && stristr($filename, 'jpg') === FALSE)
    			{
    				$filename .= '.jpg';
    			} 
    			
    			/** -------------------------------
    			/**  Download Image
    			/** -------------------------------*/
    			
    			$image_url	= $matches['1'][$i];
    			$invite		= $matches['3'][$i];
    			
    			$r = "\r\n";
				$bits = parse_url($image_url);
			
				if ( ! isset($bits['path'])) 
				{	
					return false;
				}
			
				$host = $bits['host'];
				$path = ( ! isset($bits['path'])) ? '/' : $bits['path'];
				$path .= "?inviteToken={$invite}";
				
				if ( ! $fp = @fsockopen ($host, 80))
				{
					continue;
				}
				
				fputs ($fp, "GET " . $path . " HTTP/1.0\r\n" ); 
        		fputs ($fp, "Host: " . $bits['host'] . "\r\n" ); 
        		fputs ($fp, "Content-type: application/x-www-form-urlencoded\r\n" ); 
        		fputs ($fp, "User-Agent: EE/EllisLab PHP/" . phpversion() . "\r\n");
        		fputs ($fp, "Connection: close\r\n\r\n" ); 
        		
        		$this->external_image($fp, $filename);	
    		}
    	}
    	
    	if(preg_match_all("#<img\s+src=\s*[\"']http://pictures.sprintpcs.com/+shareImage/(.+?)[\"'](.*?)\s*\>#si", $text, $matches))
    	{	
    		for($i = 0; $i < sizeof($matches['0']); $i++)
    		{
    			$parts = explode('jpg',$matches['1'][$i]);
    			
    			if ( ! isset($parts['1']))
    			{
    				continue;
    			}
    			
    			$filename = $parts['0'].'jpg';
    			$image_url = 'http://pictures.sprintpcs.com/shareImage/'.$filename;

    			$invite = $this->find_data($parts['1'], 'invite=','');
    			
    			if ($invite == '')
    			{
    				$invite = $this->find_data($parts['1'], 'invite=','&');
    				
    				if ($invite == '')
    				{
    					continue;
    				}
    			}
    			
    			/** -------------------------------
    			/**  Download Image
    			/** -------------------------------*/
    			
    			$r = "\r\n";
				$bits = parse_url($image_url);
			
				if ( ! isset($bits['path'])) 
				{	
					return false;
				}
			
				$host = $bits['host'];
				$path = ( ! isset($bits['path'])) ? '/' : $bits['path'];
				$data = "invite={$invite}";
				
				if ( ! $fp = @fsockopen ($host, 80))
				{
					continue;
				}
				
				fputs ($fp, "GET " . $path . " HTTP/1.0\r\n" ); 
        		fputs ($fp, "Host: " . $bits['host'] . "\r\n" ); 
        		fputs ($fp, "Content-type: application/x-www-form-urlencoded\r\n" ); 
        		fputs ($fp, "User-Agent: EE/EllisLab PHP/" . phpversion() . "\r\n");
        		fputs ($fp, "Content-length: " . strlen($data) . "\r\n" ); 
        		fputs ($fp, "Connection: close\r\n\r\n" ); 
        		fputs ($fp, $data);
        		
        		$this->external_image($fp, $filename);	
    		}	
    	}
    	
    	return true;
    	
    }
    /* END */
    
    
    /** -------------------------------------
	/**  Retrieve Bell Images
	/** -------------------------------------*/
	
	// <img src="http://mypictures.bell.ca//i/99376001_240.jpg?invite=SELr4RJHhma1cknzLQoU" alt="Retrieving picture..."/>

    function bell_image($text)
    {
    	$text = trim(str_replace($this->newline,"\n",$text));
    	
    	if(preg_match_all("#<img\s+src=\"http://mypictures.bell.ca(.*?)\"(.*?)alt=\"Retrieving picture\.\.\.\"(.*?)\/\>#i", $text, $matches))
    	{    		
    		for($i = 0; $i < sizeof($matches['0']); $i++)
    		{
    			$parts = explode('jpg',$matches['1'][$i]);
    			
    			if ( ! isset($parts['1']))
    			{
    				continue;
    			}
    			else
    			{
    				$pos = strrpos($parts['0'], '/');
    				
    				if ($pos === false)
 					{
 						continue;
 					}
 					
 					$parts['0'] = substr($parts['0'], $pos+1, strlen($parts['0'])-$pos-1);
    			}
    			
    			
    			$filename = $parts['0'].'jpg';
    			$image_url = 'http://mypictures.bell.ca'.$matches['1'][$i];
    			
    			/** -------------------------------
    			/**  Download Image
    			/** -------------------------------*/
    			
    			$r = "\r\n";
				$bits = parse_url($image_url);
			
				if ( ! isset($bits['path'])) 
				{	
					return false;
				}
			
				$host = $bits['host'];
				$path = ( ! isset($bits['path'])) ? '/' : $bits['path'];
				
				if ( ! $fp = @fsockopen ($host, 80))
				{
					continue;
				}
				
				fputs ($fp, "GET " . $path.'?'.$bits['query'] . " HTTP/1.0\r\n" ); 
        		fputs ($fp, "Host: " . $bits['host'] . "\r\n" ); 
        		fputs ($fp, "User-Agent: EE/EllisLab PHP/" . phpversion() . "\r\n");
        		fputs ($fp, "Connection: close\r\n\r\n" ); 
        		
        		$this->external_image($fp, $filename);
    		}	
    	}
    	
    	return true;
    	
    }
    /* END */
    
    
    
    /** -------------------------------------
	/**  Get Images From External Server
	/** -------------------------------------*/

    function external_image($fp, $filename)
    {
    	$data = '';
		$headers = '';
		$getting_headers = TRUE;
		
		while (!feof($fp))
		{
			$line = fgets($fp, 4096);
				
			if ($getting_headers == false)
			{
				$data .= $line;
			}
			elseif (trim($line) == '')
			{
				$getting_headers = false;
			}
			else
			{
				$headers .= $line;
			}
		}	
    			
    			
    	/** -------------------------------
    	/**  Save Image
    	/** -------------------------------*/
    			
    	$filename = $this->safe_filename($filename);
    	$filename = $this->unique_filename($filename);
				
		$this->post_data['images'][] = array( 'filename' => $filename);
		$key = sizeof($this->post_data['images']) - 1;
				
		if (!$fp = @fopen($this->upload_path.$filename,'wb'))
		{
			$this->message_array[] = 'error_writing_attachment'; //.$this->upload_path.$filename;
			return false;
		}				
				
		@fwrite($fp,$data);
		@fclose($fp);
		
		@chmod($this->upload_path.$filename, 0777);
				
		$this->email_files[] = $filename;
		$this->uploads++;
				
		/** -------------------------------
    	/**  Image Resizing
    	/** -------------------------------*/
    	
    	if ( ! isset($this->moblog_array['moblog_type']) OR $this->moblog_array['moblog_type'] != 'gallery')
		{
			$this->image_resize($filename,$key);    
		}
		
    	return true;
    	
    }
    /* END */
    
    
    
    
    
	/** -------------------------------------
	/**  appledouble crap
	/** -------------------------------------*/
	
	function appledouble($data)
	{		
		if ( ! preg_match("#".preg_quote('boundary=')."#i",$data))
		{
			return false;
		}
		
		$boundary		= "--".$this->find_data($data, "boundary=", $this->newline);
		$boundary		= trim(str_replace('"','',$boundary));
		$boundary		= str_replace("+","\+", $boundary);
		$email_parts	= explode($boundary, $data);
		
		if (sizeof($email_parts) < 2)
		{
			return false;
		}
		
		foreach($email_parts as $value)
		{
			$content_type	= $this->find_data($value, "Content-Type:", ";");
			$pieces			= explode('/',trim($content_type));
			$type			= trim($pieces['0']);
			$subtype		= ( ! isset($pieces['1'])) ? '0' : trim($pieces['1']);
			
			if ($type == 'image' || $type == 'audio' || $type == 'video')
			{
				$data = array( 'value' => $value,
								'type' => $type,
								'subtype' => $subtype);
								
				return $data;
			}	
		}
		
		return false;	
	}


	/** -------------------------------------
	/**  Check Login
	/** -------------------------------------*/
	
	function check_login()
	{		
		global $DB, $FNS;
		
		$this->body	= trim($this->body);
		$login		= $this->find_data($this->body, '', $this->newline);
		
		if ($login == '' || !stristr($login,':'))
		{
			$login = $this->find_data($this->body, 'AUTH:', $this->newline);
		}
		
		if ($login == '' || !stristr($login,':'))
		{
			return false;
		}		
		
		$x = explode(":", $login);
		
		$username = (isset($x['1']) && $x['0'] == 'AUTH') ? $x['1'] : $x['0'];
		$password = (isset($x['2']) && $x['0'] == 'AUTH') ? $x['2'] : $x['1'];
		
		/** --------------------------------------
		/**  Check Username and Password, First
		/** --------------------------------------*/
		
		$sql = "SELECT member_id, group_id FROM exp_members 
				WHERE username = '".$DB->escape_str($username)."'
				AND password = '".$FNS->hash(stripslashes($password))."'";
				
		$query = $DB->query($sql);
        
        if ($query->num_rows == 0)
        {
        	return false;
        }
        elseif($query->row['group_id'] == '1')
        {
        	$this->author	=  $query->row['member_id'];
			$this->body		= str_replace($login,'',$this->body);
			return true;
        }
		
		
		if ( ! isset($this->moblog_array['moblog_type']) OR $this->moblog_array['moblog_type'] != 'gallery')
		{
			$sql = "SELECT COUNT(*) AS count
            	   FROM exp_weblog_member_groups 
            	   WHERE group_id = '".$query->row['group_id']."'
        	       AND weblog_id = '".$DB->escape_str($this->moblog_array['moblog_weblog_id'])."'";
        }
        else
        {
        	$results = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Gallery'");
        	
        	if ($results->num_rows == 0)
        	{
        		return false;
        	}
        	
        	$sql = "SELECT COUNT(*) AS count
            	   FROM exp_module_member_groups 
            	   WHERE module_id = '".$results->row['module_id']."'
        	       AND group_id = '".$query->row['group_id']."'";                	       
        }
               
        $results = $DB->query($sql);
        
        if ($results->row['count'] == 0)
        {
        	return false;
        }
		
		$this->author	=  $query->row['member_id'];
		$this->body		= str_replace($login,'',$this->body);
		
		return true;
    }
    

    /** -------------------------------------
	/**  Determine Boundary
	/** -------------------------------------*/
	
	function find_boundary($email_data)
	{		
		if (!preg_match("#".preg_quote('boundary=')."#i",$email_data))
		{
			return false;
		}
		else
		{
			$this->boundary = "--".$this->find_data($email_data, "boundary=", $this->newline);
			$x = explode(';',$this->boundary);
			$this->boundary = trim(str_replace('"','',$x['0']));

			return true;
		}	
    }
    
	/** -------------------------------------
	/**  Send POP3 Command to server
	/** -------------------------------------*/

	function pop_command($cmd = "")
	{
		if (!$this->fp)
		{
			return false;
		}

		if ($cmd != "")
		{
			fwrite($this->fp, $cmd.$this->pop_newline);
		}
			
		$line = $this->remove_newlines(fgets($this->fp, 1024));
		
		return $line;
	}
	/* END */
	
	/** -------------------------------------
	/**  Strip line-breaks via callback
	/** -------------------------------------*/

    function remove_newlines($str,$replace='')
    {
        return preg_replace("/(\r\n)|(\r)|(\n)/", $replace, $str);    
    }
    /* END */
    
    
	/** -------------------------------------
	/**  Clear iso info
	/** -------------------------------------*/
	function iso_clean($str)
	{
		global $PREFS;
		
		if (stristr($str, '=?') === FALSE)
		{
			return $str;
		}
		
		// -------------------------------------------------
		//  There exists two functions that do this for us
		//  but they are not available on all servers and some
		//  seem to work better than others I have found. The 
		//  base64_decode() method works for many encodings
		//  but I am not sure how well it handles non Latin
		//  characters.
		//
		//  The mb_decode_mimeheader() function seems to trim
		//  any line breaks off the end of the str, so we put
		//  those back because we need it for the Header
		//  matching stuff.  I added it on for the imap_utf8()
		//  function just in case.
		// -------------------------------------------------

		
		if (function_exists('imap_utf8') && $PREFS->ini('charset') == 'utf-8')
		{
			return rtrim(imap_utf8($str))."\r\n";
		}
	
		if (function_exists('mb_decode_mimeheader'))
		{
			// mb_decode_mimeheader() doesn't replace underscores
			return str_replace('_', ' ', rtrim(mb_decode_mimeheader($str)))."\r\n";
		}
		
		if (function_exists('iconv_mime_decode'))
		{
			return rtrim(iconv_mime_decode($str))."\r\n";
		}
		
		if (substr(trim($str), -2) != '?=')
		{
			$str = trim($str).'?=';
		}
		
		if (preg_match("|\=\?iso\-(.*?)\?[A-Z]{1}\?(.*?)\?\=|i", trim($str), $mime))
		{
			if ($mime['1'] == '8859-1')
			{	
				$charHex = array('0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F');
				
				for ($z=0, $sz=sizeof($charHex); $z < $sz; ++$z)
				{
					for ($i=0, $si=sizeof($charHex); $i < $si; ++$i)
					{
						$mime['2'] = str_replace('='.$charHex[$z].$charHex[$i], chr(hexdec($charHex[$z].$charHex[$i])), $mime['2']);
					}
				}
				
				$str = str_replace($mime['0'], $mime['2'], $str);
			}
			else
			{
				$str = str_replace($mime['0'], base64_decode($mime['2']), $str);
			}
			
			$str = str_replace('_', ' ', $str);
		}
		
		return ltrim($str);
	}
	/* END */
	
	
	/** -------------------------------------
	/**  Find Data
	/** -------------------------------------*/
	
	function find_data($str, $begin, $end)
	{
		$new = '';
		
		if ($begin == '')
		{
			$p1 = 0;
		}
		else
		{
			if (strpos(strtolower($str), strtolower($begin)) === false)
			{
				return $new;
			}
			
			$p1 = strpos(strtolower($str), strtolower($begin)) + strlen($begin);
		}
		
		if ($end == '')
		{
			$p2 = strlen($str);
		}
		else
		{
			if (strpos(strtolower($str), strtolower($end), $p1) === false)
			{
				return $new;
			}
			
			$p2 = strpos(strtolower($str), strtolower($end), $p1);
		}
		
		$new = substr($str, $p1, ($p2-$p1));
		return $new;
	}
	
	
	/** -------------------------------------
    /**  Set image properties
    /** -------------------------------------*/

    function image_properties($file)
    {
    	if (function_exists('getimagesize')) 
        {
            if ( ! $D = @getimagesize($file))
            {
            	return FALSE;
            }
            
            $parray = array();
            
            $parray['width']   = $D['0'];
            $parray['height']  = $D['1'];
            $parray['imgtype'] = $D['2'];
                       
            return $parray;
        }

        return FALSE;
    }
    /* END */
    
    
	/** -------------------------------------
    /**  Safe Filenames
    /** -------------------------------------*/

    function safe_filename($str)
    {
    	global $PREFS;
		
		$str = strip_tags(strtolower($str));
		$str = preg_replace('/\&#\d+\;/', "", $str);
		
		// Use dash as separator		

		if ($PREFS->ini('word_separator') == 'dash')
		{
			$trans = array(
							"_"									=> '-',
							"\&\#\d+?\;"                        => '',
							"\&\S+?\;"                          => '',
							"['\"\?\!*\$\#@%;:,\_=\(\)\[\]]"  	=> '',
							"\s+"                               => '-',
							"\/"                                => '-',
							"[^a-z0-9-_\.]"						=> '',
							"-+"                                => '-',
							"\&"                                => '',
							"-$"                                => '',
							"^_"                                => ''
						   );
		}
		else // Use underscore as separator
		{
			$trans = array(
							"-"									=> '_',
							"\&\#\d+?\;"                        => '',
							"\&\S+?\;"                          => '',
							"['\"\?\!*\$\#@%;:,\-=\(\)\[\]]"  => '',
							"\s+"                               => '_',
							"\/"                                => '_',
							"[^a-z0-9-_\.]"						=> '',
							"_+"                                => '_',
							"\&"                                => '',
							"_$"                                => '',
							"^_"                                => ''
						   );
		}
					   
		foreach ($trans as $key => $val)
		{
			$str = preg_replace("#".$key."#", $val, $str);
		} 
		
		$str = trim(stripslashes($str));

		return $str;
    }
    /* END */
    
        
    /** -------------------------------------
	/**  Resizing of Images
	/** -------------------------------------*/
    
    function image_resize($filename, $key)
    {
    	global $PREFS;
		
		/** --------------------------
		/**  Set Properties for Image
		/** --------------------------*/
		
		if( ! $properties = $this->image_properties($this->upload_path.$filename))
		{
			$properties = array('width'	  => $this->moblog_array['moblog_image_width'],
								'height'  => $this->moblog_array['moblog_image_height']);
		}
		
		$this->post_data['images'][$key]['width']  = $properties['width'];
		$this->post_data['images'][$key]['height'] = $properties['height'];
		
		
		/** ---------------------------
		/**  Resizing Set Up Check
		/** ---------------------------*/
		
		if ($PREFS->ini('enable_image_resizing') != 'y')
		{
			return;
		}
		
		/** --------------------------------
		/**  Invoke the Image Lib Class
		/** --------------------------------*/

		if ( ! class_exists('Image_lib'))
		{ 
			require PATH_CORE.'core.image_lib'.EXT;
		}
		
		$IM = new Image_lib();
		
		/** ------------------------------
		/**  Resize Image
		/** ------------------------------*/
		
		if ($this->moblog_array['moblog_resize_image'] == 'y')
		{
			if ($this->moblog_array['moblog_resize_width'] != 0 || $this->moblog_array['moblog_resize_height'] != 0)
			{
				// Temp vars
				$resize_width	= $this->moblog_array['moblog_resize_width'];
				$resize_height	= $this->moblog_array['moblog_resize_height'];
				
				/** ----------------------------
				/**  Calculations based on one side?
				/** ----------------------------*/
				
				if ($this->moblog_array['moblog_resize_width'] == 0 && $this->moblog_array['moblog_resize_height'] != 0)
				{
					// Resize based on height, calculate width
					$resize_width = ceil(($this->moblog_array['moblog_resize_height']/$properties['height']) * $properties['width']);				
				}
				elseif ($this->moblog_array['moblog_resize_width'] != 0 && $this->moblog_array['moblog_resize_height'] == 0)
				{
					// Resize based on width, calculate height
					$resize_height = ceil(($this->moblog_array['moblog_resize_width']/$properties['width']) * $properties['height']);			
				}
					
				$res = $IM->set_properties(			
											array(
													'resize_protocol'	=> $PREFS->ini('image_resize_protocol'),
													'libpath'			=> $PREFS->ini('image_library_path'),
													'file_path'			=> $this->upload_path,
													'file_name'			=> $filename,
													'quality'			=> '90',
													'dst_width'			=> $resize_width,
													'dst_height'		=> $resize_height
												)
											);
											
				if ($res === FALSE OR ! $IM->image_resize())
				{
					$this->message_array[] = 'unable_to_resize';
					$this->message_array = array_merge($this->message_array,$IM->error_msg);
					return false;
				}
				
				$this->post_data['images'][$key]['width']  = $IM->dst_width;
				$this->post_data['images'][$key]['height'] = $IM->dst_height;
				
				if( ! $properties = $this->image_properties($this->upload_path.$filename))
				{
					$properties = array('width'	  => $IM->dst_width,
										'height'  => $IM->dst_height);
				}
			}		
		}
		
		/** ------------------------------
		/**  Create Thumbnail
		/** ------------------------------*/
		
		if ($this->moblog_array['moblog_create_thumbnail'] == 'y')
		{				
			if ($this->moblog_array['moblog_thumbnail_width'] != 0 OR $this->moblog_array['moblog_thumbnail_height'] != 0)
			{				
				// Temp vars
				$resize_width	= $this->moblog_array['moblog_thumbnail_width'];
				$resize_height	= $this->moblog_array['moblog_thumbnail_height'];
					
				/** ----------------------------
				/**  Calculations based on one side?
				/** ----------------------------*/
				
				if ($this->moblog_array['moblog_thumbnail_width'] == 0 && $this->moblog_array['moblog_thumbnail_height'] != 0)
				{
					// Resize based on height, calculate width
					$resize_width = ceil(($this->moblog_array['moblog_thumbnail_height']/$properties['height']) * $properties['width']);
				}
				elseif ($this->moblog_array['moblog_thumbnail_width'] != 0 && $this->moblog_array['moblog_thumbnail_height'] == 0)
				{
					// Resize based on width, calculate height
					$resize_height = ceil(($this->moblog_array['moblog_thumbnail_width']/$properties['width']) * $properties['height']);			
				}
				
				$res = $IM->set_properties(			
										array(
												'resize_protocol'	=> $PREFS->ini('image_resize_protocol'),
												'libpath'			=> $PREFS->ini('image_library_path'),
												'file_path'			=> $this->upload_path,
												'file_name'			=> $filename,
												'thumb_prefix'		=> 'thumb',
												'quality'			=> '90',
												'dst_width'			=> $resize_width,
												'dst_height'		=> $resize_height
											  )
										);
			
					
				if ($res === FALSE OR ! $IM->image_resize())
				{
					$this->message_array[] = 'unable_to_resize';
					$this->message_array = array_merge($this->message_array,$IM->error_msg);
					return false;
				}
				
				$name = substr($filename, 0, strpos($filename, "."));
				$ext  = substr($filename, strpos($filename, "."));	
				
				$this->post_data['images'][$key]['thumbnail']  = $name.'_thumb'.$ext;
				$this->post_data['images'][$key]['thumb_width']  = $resize_width;
				$this->post_data['images'][$key]['thumb_height'] = $resize_height;
				$this->email_files[] = $name.'_thumb'.$ext;
				$this->uploads++;
				
			}	// End thumbnail resize conditional			
		}	// End thumbnail  
  	}  
  	/* END */
  	
  	/** --------------------------------
  	/**  Guarantees Unique Filename
  	/** --------------------------------*/
  	
  	function unique_filename($filename, $subtype='0')
  	{
  		$i = 0;
  		
  		$subtype = ($subtype != '0') ? '.'.$subtype : '';
  		
  		/** ----------------------------
  		/**  Strips out _ and - at end of name part of file name
  		/** ----------------------------*/
  		$x			= explode('.',$filename);
		$name		=  ( ! isset($x['1'])) ? $filename : $x['0'];
		$sfx		=  ( ! isset($x['1']) OR is_numeric($x[sizeof($x) - 1])) ? $subtype : '.'.$x[sizeof($x) - 1];
		$name		=  (substr($name,-1) == '_' || substr($name,-1) == '-') ? substr($name,0,-1) : $name;
  		$filename	= $name.$sfx;
  		
		while (file_exists($this->upload_path.$filename))
		{
			$i++;
			$n			=  ($i > 10) ? -2 : -1;
			$x			= explode('.',$filename);
			$name		=  ( ! isset($x['1'])) ? $filename : $x['0'];
			$sfx		=  ( ! isset($x['1'])) ? '' : '.'.$x[sizeof($x) - 1];
			$name		=  ($i==1) ? $name : substr($name,0,$n);
			$name		=  (substr($name,-1) == '_' || substr($name,-1) == '-') ? substr($name,0,-1) : $name;
			$filename	=  $name."$i".$sfx;
		}
		
		return $filename;
	}
	
	
	/** ------------------------------------------------
    /**  Update Number of Entries in Gallery Category
    /** ------------------------------------------------*/
  
  	function update_cat_total($cat_id)
  	{
  		global $DB;
  		
  		if ($cat_id != '')
  		{
			$query = $DB->query("SELECT COUNT(*) AS count FROM exp_gallery_entries WHERE cat_id= '".$DB->escape_str($cat_id)."'");
			$tot = $query->row['count'];
			
			$query = $DB->query("SELECT MAX(entry_date) AS max_date FROM exp_gallery_entries WHERE cat_id= '".$DB->escape_str($cat_id)."'");
			$date = ($query->num_rows == 0 OR ! is_numeric($query->row['max_date'])) ? 0 : $query->row['max_date'];
			
			$query = $DB->query("SELECT views FROM exp_gallery_entries WHERE cat_id= '".$DB->escape_str($cat_id)."'");
			
			$views = 0;
			
			if ($query->num_rows > 0)
			{
				foreach($query->result as $row)
				{
					$views = $views + $row['views'];
				}
			}
						
			$DB->query("UPDATE exp_gallery_categories SET total_files = '{$tot}', total_views = '{$views}', recent_entry_date = '{$date}' WHERE cat_id = '".$DB->escape_str($cat_id)."'");
  		}
  	}
	/* END */
  	
}
// END CLASS
?>