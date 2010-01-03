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
 File: mod.member_images.php
=====================================================

*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Member_images extends Member {


    /** ----------------------------------
    /**  Member_settings Profile Constructor
    /** ----------------------------------*/

	function Member_images()
	{
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Signature Edit Form
    /** ----------------------------------------*/
	
	function edit_signature()
	{
		global $DB, $SESS, $LANG, $PREFS, $FNS;
	
		/** -------------------------------------
		/**  Are signatures allowed?
		/** -------------------------------------*/
		
		if ($PREFS->ini('allow_signatures') == 'n')
		{
			return $this->_trigger_error('edit_signature', 'signatures_not_allowed');
		}
		
		/** -------------------------------------
		/**  Create the HTML formatting buttons
		/** -------------------------------------*/

		$buttons = '';
		if ( ! class_exists('Html_buttons'))
		{
			if (include_once(PATH_LIB.'html_buttons'.EXT))
			{
				$BUTT = new Html_buttons();
				$BUTT->allow_img = ($PREFS->ini('sig_allow_img_hotlink') == 'y') ? TRUE : FALSE;
				
				
				$buttons = $BUTT->create_buttons();
			}
		}
				
		$query = $DB->query("SELECT signature, sig_img_filename, sig_img_width, sig_img_height FROM exp_members WHERE member_id = '".$SESS->userdata('member_id')."'");
				
		$template = $this->_load_element('signature_form');
		
		if ($PREFS->ini('sig_allow_img_upload') == 'y')
		{
			$template = $this->_allow_if('upload_allowed', $template);			
			$template = $this->_deny_if('upload_not_allowed', $template);			
		}
		else
		{
			$template = $this->_allow_if('upload_not_allowed', $template);			
			$template = $this->_deny_if('upload_allowed', $template);
		}
		if ($query->row['sig_img_filename'] == '' OR $PREFS->ini('sig_allow_img_upload') == 'n')
		{
			$template = $this->_deny_if('image', $template);
			$template = $this->_allow_if('no_image', $template);
		}
		else
		{
			$template = $this->_allow_if('image', $template);
			$template = $this->_deny_if('no_image', $template);
		}
		
	
		$max_kb = ($PREFS->ini('sig_img_max_kb') == '' OR $PREFS->ini('sig_img_max_kb') == 0) ? 50 : $PREFS->ini('sig_img_max_kb');
		$max_w  = ($PREFS->ini('sig_img_max_width') == '' OR $PREFS->ini('sig_img_max_width') == 0) ? 100 : $PREFS->ini('sig_img_max_width');
		$max_h  = ($PREFS->ini('sig_img_max_height') == '' OR $PREFS->ini('sig_img_max_height') == 0) ? 100 : $PREFS->ini('sig_img_max_height');
		$max_size = str_replace('%x', $max_w, $LANG->line('max_image_size'));
		$max_size = str_replace('%y', $max_h, $max_size);
		$max_size .= ' - '.$max_kb.'KB';
		
		$data = array(
						'action' 		=> $this->_member_path('update_signature'),
						'enctype'		=> 'multi',
						'id'			=> 'submit_post'
					);
					
		return $this->_var_swap($template,
								array(
										'form_declaration'		=> $FNS->form_declaration($data),								
										'path:signature_image'		=> 	$PREFS->ini('sig_img_url', TRUE).$query->row['sig_img_filename'],
										'signature_image_width'		=> 	$query->row['sig_img_width'],
										'signature_image_height'	=> 	$query->row['sig_img_height'],
										'signature'					=>	$query->row['signature'],
										'lang:max_image_size'		=>  $max_size,
										'maxchars'					=> ($PREFS->ini('sig_maxlength') == 0) ? 10000 : $PREFS->ini('sig_maxlength'),
										'include:html_formatting_buttons' => $buttons,
									 )
								);
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Update Signature
    /** ----------------------------------------*/
	
	function update_signature()
	{
		global $FNS, $IN, $SESS, $PREFS, $DB, $LANG, $REGX, $OUT;
		
		/** -------------------------------------
		/**  Are signatures allowed?
		/** -------------------------------------*/
		
		if ($PREFS->ini('allow_signatures') == 'n')
		{
			return $this->_trigger_error('edit_signature', 'signatures_not_allowed');
		}
				
		$_POST['body'] = $DB->escape_str($REGX->xss_clean($_POST['body']));
		
		$maxlength = ($PREFS->ini('sig_maxlength') == 0) ? 10000 : $PREFS->ini('sig_maxlength');
		
		if (strlen($_POST['body']) > $maxlength)
		{
			return $OUT->show_user_error('submission', str_replace('%x', $maxlength, $LANG->line('sig_too_big')));
		}
	
        $DB->query("UPDATE exp_members SET signature = '".$_POST['body']."' WHERE member_id ='".$SESS->userdata('member_id')."'");
        
		/** ----------------------------------------
		/**  Is there an image to upload or remove?
		/** ----------------------------------------*/
				
		if ((isset($_FILES['userfile']) AND $_FILES['userfile']['name'] != '') OR isset($_POST['remove']))
		{
			return $this->upload_signature_image();
		}
        
        /** -------------------------------------
        /**  Success message
        /** -------------------------------------*/
	
		return $this->_var_swap($this->_load_element('success'),
								array(
										'lang:heading'	=>	$LANG->line('signature'),
										'lang:message'	=>	$LANG->line('signature_updated')
									 )
								);
	}
	/* END */
	
	

    /** ----------------------------------------
    /**  Avatar Edit Form
    /** ----------------------------------------*/
	
	function edit_avatar()
	{
		global $DB, $LANG, $PREFS, $SESS, $FNS;
		
		/** ----------------------------------------
		/**  Are avatars enabled?
		/** ----------------------------------------*/
		
		if ($PREFS->ini('enable_avatars') == 'n')
		{
			return $this->_trigger_error('edit_avatar', 'avatars_not_enabled');
		}
		
		/** ----------------------------------------
		/**  Fetch the avatar template
		/** ----------------------------------------*/

		$template = $this->_load_element('edit_avatar');
				
		/** ----------------------------------------
		/**  Does the current user have an avatar?
		/** ----------------------------------------*/
		
		$query = $DB->query("SELECT avatar_filename, avatar_width, avatar_height FROM exp_members WHERE member_id = '".$SESS->userdata('member_id')."'");
		
		if ($query->row['avatar_filename'] == '')
		{
			$template = $this->_deny_if('avatar', $template);
			$template = $this->_allow_if('no_avatar', $template);
			
			$cur_avatar_url = '';
			$avatar_width 	= '';
			$avatar_height 	= '';
		}
		else
		{
			$template = $this->_allow_if('avatar', $template);
			$template = $this->_deny_if('no_avatar', $template);
			
			$cur_avatar_url = $PREFS->ini('avatar_url', TRUE).$query->row['avatar_filename'];
			$avatar_width 	= $query->row['avatar_width'];
			$avatar_height 	= $query->row['avatar_height'];
		}
		
		/** ----------------------------------------
		/**  Can users upload their own images?
		/** ----------------------------------------*/

		if ($PREFS->ini('allow_avatar_uploads') == 'y')
		{
			$template = $this->_allow_if('can_upload_avatar', $template);
		}
		else
		{
			$template = $this->_deny_if('can_upload_avatar', $template);
		}
		
		/** ----------------------------------------
		/**  Are there pre-installed avatars?
		/** ----------------------------------------*/
		
		// We'll make a list of all folders in the "avatar" folder,
		// then check each one to see if they contain images.  If so
		// we will add it to the list
		
		$avatar_path = $PREFS->ini('avatar_path', TRUE);
		
		$extensions = array('.gif', '.jpg', '.jpeg', '.png');
				
		if ( ! @is_dir($avatar_path) OR ! $fp = @opendir($avatar_path))
		{
			$template = $this->_deny_if('installed_avatars', $template);
		}
		else
		{
			$tmpl = $this->_load_element('avatar_folder_list');
		
		 	$folders = '';
		 	
			while (FALSE !== ($file = readdir($fp))) 
			{ 
				if (is_dir($avatar_path.$file) AND $file != 'uploads' AND $file != '.' AND $file != '..')
				{
					if ($np = @opendir($avatar_path.$file))
					{
						while (FALSE !== ($innerfile = readdir($np))) 
						{ 
							if (FALSE !== ($pos = strpos($innerfile, '.')))
							{
								if (in_array(substr($innerfile, $pos), $extensions))
								{
									$name = ucwords(str_replace("_", " ", $file));
									
									$temp = $tmpl;
									
									$temp = str_replace('{path:folder_path}', $this->_member_path('browse_avatars/'.$file.'/'), $temp);
									$temp = str_replace('{folder_name}', $name, $temp);
									
									$folders .= $temp;
									
									break;
								}
							}							
						}
						
						closedir($np); 
					}
				}
			} 
		
			closedir($fp); 
		
			if ($folders == '')
			{
				$template = $this->_deny_if('installed_avatars', $template);
			}
			else
			{
				$template = $this->_allow_if('installed_avatars', $template);
			}
			
			$template = str_replace('{include:avatar_folder_list}', $folders, $template);
		}
		
		
		/** ----------------------------------------
		/**  Set the default image meta values
		/** ----------------------------------------*/
		
		$max_kb = ($PREFS->ini('avatar_max_kb') == '' OR $PREFS->ini('avatar_max_kb') == 0) ? 50 : $PREFS->ini('avatar_max_kb');
		$max_w  = ($PREFS->ini('avatar_max_width') == '' OR $PREFS->ini('avatar_max_width') == 0) ? 100 : $PREFS->ini('avatar_max_width');
		$max_h  = ($PREFS->ini('avatar_max_height') == '' OR $PREFS->ini('avatar_max_height') == 0) ? 100 : $PREFS->ini('avatar_max_height');
		$max_size = str_replace('%x', $max_w, $LANG->line('max_image_size'));
		$max_size = str_replace('%y', $max_h, $max_size);
		$max_size .= ' - '.$max_kb.'KB';

		/** ----------------------------------------
		/**  Finalize the template
		/** ----------------------------------------*/
		
		return $this->_var_swap($template,
								array(
										'form_declaration'		=> $FNS->form_declaration(
																							array(
																									'action' 		=> $this->_member_path('upload_avatar'),
																									'enctype'		=> 'multi'
																								)
																						),
										'lang:max_image_size'	=>  $max_size,
										'path:avatar_image'		=> 	$cur_avatar_url,
										'avatar_width'			=> 	$avatar_width,
										'avatar_height'			=>	$avatar_height
									 )
								);
	}
	/* END */
	

    /** ----------------------------------------
    /**  Browse Avatars
    /** ----------------------------------------*/
	
	function browse_avatars()
	{
		global $DB, $LANG, $PREFS, $SESS, $FNS;
		
		/** ----------------------------------------
		/**  Are avatars enabled?
		/** ----------------------------------------*/
		
		if ($PREFS->ini('enable_avatars') == 'n')
		{
			return $this->_trigger_error('edit_avatar', 'avatars_not_enabled');
		}
	
		/** ----------------------------------------
		/**  Define the paths and get the images
		/** ----------------------------------------*/
		
		$avatar_path = $PREFS->ini('avatar_path', TRUE).$FNS->filename_security($this->cur_id).'/';
		$avatar_url  = $PREFS->ini('avatar_url', TRUE).$FNS->filename_security($this->cur_id).'/';
		
		$avatars = $this->_get_avatars($avatar_path);
		
		/** ----------------------------------------
		/**  Did we succeed?
		/** ----------------------------------------*/
		
		if (count($avatars) == 0)
		{
			return $this->_trigger_error('edit_avatar', 'avatars_not_found');
		}
		
		/** ----------------------------------------
		/**  Pagination anyone?
		/** ----------------------------------------*/
	
		$pagination = '';
		$max_rows	= 8;
		$max_cols	= 3;
		$col_ct		= 0;
		$perpage 	= $max_rows * $max_cols;
		$total_rows = count($avatars);
		$rownum 	= ($this->uri_extra == '') ? 0 : $this->uri_extra;
		$base_url	= $this->_member_path('browse_avatars/'.$this->cur_id.'/');
		
		if ($rownum > count($avatars)) 
			$rownum = 0;
				
		if ($total_rows > $perpage)
		{		
			$avatars = array_slice($avatars, $rownum, $perpage);
			
			if ( ! class_exists('Paginate'))
			{
				require PATH_CORE.'core.paginate'.EXT;
			}
			
			$PGR = new Paginate();
				
			$PGR->path			= $base_url;
			$PGR->prefix		= '';
			$PGR->total_count 	= $total_rows;
			$PGR->per_page		= $perpage;
			$PGR->cur_page		= $rownum;			
			$pagination	= $PGR->show_links();
			
			// We add this for use later
			
			if ($rownum != '')
			{
				$base_url .= $rownum.'/';
			}
		}
		
		/** ----------------------------------------
		/**  Build the table rows
		/** ----------------------------------------*/
		
		$avstr = '';
		foreach ($avatars as $image)
		{
			if ($col_ct == 0)
			{
				$avstr .= "<tr>\n";
			}
					
			$avstr .= "<td align='center'><img src='".$avatar_url.$image."' border='0' alt='".$image."'/><br /><input type='radio' name='avatar' value='".$image."' /></td>\n";
			$col_ct++;
			
			if ($col_ct == $max_cols)
			{
				$avstr .= "</tr>";
				$col_ct = 0;
			}			
		}
		
		if ($col_ct < $max_cols AND count($avatars) >= $max_cols)
		{
			for ($i = $col_ct; $i < $max_cols; $i++)
			{
				$avstr .= "<td>&nbsp;</td>\n";
			}
			
			$avstr .= "</tr>";
		}
		
		if ( ! preg_match("#\<\/tr\>$#", $avstr))
		{
			$avstr .= "</tr>";
		}
				
		/** ----------------------------------------
		/**  Finalize the output
		/** ----------------------------------------*/
			
		$template = $this->_load_element('browse_avatars');
		
		if ($pagination == '')
		{
			$template = $this->_deny_if('pagination', $template);
		}
		else
		{
			$template = $this->_allow_if('pagination', $template);
		}
		
		
		return $this->_var_swap($template,
								array(
										'form_declaration'		=> $FNS->form_declaration(
																							array(
																									'action' 		=> $this->_member_path('select_avatar'),
																									'hidden_fields'	=> array('referrer' => $base_url, 'folder' => $this->cur_id)
																								)
																						),
										'avatar_set'			=> ucwords(str_replace("_", " ", $this->cur_id)),
										'avatar_table_rows'		=> $avstr,
										'pagination'			=> $pagination
									 )
								);	
	}
	/* END */
	

    /** ----------------------------------------
    /**  Select Avatar From  Library
    /** ----------------------------------------*/
	
	function select_avatar()
	{
		global $FNS, $IN, $PREFS, $DB, $LANG, $SESS;
		
		/** ----------------------------------------
		/**  Are avatars enabled?
		/** ----------------------------------------*/
		
		if ($PREFS->ini('enable_avatars') == 'n')
		{
			return $this->_trigger_error('edit_avatar', 'avatars_not_enabled');
		}

		if ($IN->GBL('avatar') === FALSE OR $IN->GBL('folder') === FALSE)
		{
			return $FNS->redirect($IN->GBL('referrer'));
		}
		
		$folder	= $FNS->filename_security($IN->GBL('folder'));
		$file	= $FNS->filename_security($IN->GBL('avatar'));

		$basepath = $PREFS->ini('avatar_path', TRUE);		
		$avatar	= $folder.'/'.$file;

		$allowed = $this->_get_avatars($basepath.$folder);

		if ( ! in_array($file, $allowed) OR $folder == 'upload')
		{
			return $this->_trigger_error('edit_avatar', 'avatars_not_found');
		}

		/** ----------------------------------------
		/**  Fetch the avatar meta-data
		/** ----------------------------------------*/

		if ( ! function_exists('getimagesize')) 
		{
			return $this->_trigger_error('edit_avatar', 'image_assignment_error');
		}
		
		$vals = @getimagesize($basepath.$avatar);
		$width	= $vals['0'];
		$height	= $vals['1'];

		/** ----------------------------------------
		/**  Update DB
		/** ----------------------------------------*/
		
		$DB->query($DB->update_string('exp_members', array('avatar_filename' => $avatar, 'avatar_width' => $width, 'avatar_height' => $height), array('member_id' => $SESS->userdata['member_id'])));
	
		return $this->_var_swap($this->_load_element('success'),
								array(
										'lang:heading'	=>	$LANG->line('edit_avatar'),
										'lang:message'	=>	$LANG->line('avatar_updated')
									 )
								);
	}
	/* END */

	/** ----------------------------------------
    /**  List all Images in a Folder
    /** ----------------------------------------*/

	function _get_avatars($avatar_path)
	{
	    /** ----------------------------------------
	    /**  Is this a valid avatar folder?
	    /** ----------------------------------------*/

	    $extensions = array('.gif', '.jpg', '.jpeg', '.png');

	    if ( ! @is_dir($avatar_path) OR ! $fp = @opendir($avatar_path))
	    {
	        return array();
	    }

	    /** ----------------------------------------
	    /**  Grab the image names
	    /** ----------------------------------------*/

	    $avatars = array();

	    while (FALSE !== ($file = readdir($fp))) 
	    { 
	        if (FALSE !== ($pos = strpos($file, '.')))
	        {
	            if (in_array(substr($file, $pos), $extensions))
	            {
	                $avatars[] = $file;
	            }
	        }                            
	    }

	    closedir($fp);

	    return $avatars;
	}
	/* END */

    /** ----------------------------------------
    /**  Upload Avatar or Profile Photo
    /** ----------------------------------------*/
    	
	function upload_avatar()
	{    
    	return $this->_upload_image('avatar');
    }

	function upload_photo()
	{    
    	return $this->_upload_image('photo');
    }

	function upload_signature_image()
	{    
    	return $this->_upload_image('sig');
    }
	
	function _upload_image($type = 'avatar')
	{
		global $FNS, $IN, $PREFS, $DB, $LANG, $SESS, $OUT;
		
		
		switch ($type)
		{
			case 'avatar'	:	
								$edit_image		= 'edit_avatar';
								$enable_pref	= 'allow_avatar_uploads';
								$not_enabled	= 'avatars_not_enabled';
								$remove			= 'remove_avatar';
								$removed		= 'avatar_removed';
								$updated		= 'avatar_updated';
				break;
			case 'photo'	:	
								$edit_image 	= 'edit_photo';
								$enable_pref	= 'enable_photos';
								$not_enabled	= 'photos_not_enabled';
								$remove			= 'remove_photo';
								$removed		= 'photo_removed';
								$updated		= 'photo_updated';
								
				break;
			case 'sig'		:	
								$edit_image 	= 'edit_signature';
								$enable_pref	= 'sig_allow_img_upload';
								$not_enabled	= 'sig_img_not_enabled';
								$remove			= 'remove_sig_image';
								$removed		= 'sig_img_removed';
								$updated		= 'signature_updated';
				break;		
		}
		
		
		/** ----------------------------------------
		/**  Is this a remove request?
		/** ----------------------------------------*/
		
		if ( ! isset($_POST['remove']))
		{
			//  Is image uploading enabled?
			if ($PREFS->ini($enable_pref) == 'n')
			{
				return $this->_trigger_error($not_enabled, $not_enabled);
			}
		}
		else
		{
			if ($type == 'avatar')
			{
				$query = $DB->query("SELECT avatar_filename FROM exp_members WHERE member_id = '".$SESS->userdata('member_id')."'");
				
				if ($query->row['avatar_filename'] == '')
				{
					return $FNS->redirect($this->_member_path($edit_image));
				}
				
				$DB->query("UPDATE exp_members SET avatar_filename = '', avatar_width='', avatar_height='' WHERE member_id = '".$SESS->userdata('member_id')."' ");
			
				if (strncmp($query->row['avatar_filename'], 'uploads/', 8) == 0)
				{
					@unlink($PREFS->ini('avatar_path', TRUE).$query->row['avatar_filename']);
				}
			}
			elseif ($type == 'photo')
			{
				$query = $DB->query("SELECT photo_filename FROM exp_members WHERE member_id = '".$SESS->userdata('member_id')."'");
				
				if ($query->row['photo_filename'] == '')
				{
					return $FNS->redirect($this->_member_path($edit_image));
				}
				
				$DB->query("UPDATE exp_members SET photo_filename = '', photo_width='', photo_height='' WHERE member_id = '".$SESS->userdata('member_id')."' ");
			
				@unlink($PREFS->ini('photo_path', TRUE).$query->row['photo_filename']);
			}
			else
			{
				$query = $DB->query("SELECT sig_img_filename FROM exp_members WHERE member_id = '".$SESS->userdata('member_id')."'");
				
				if ($query->row['sig_img_filename'] == '')
				{
					return $FNS->redirect($this->_member_path($edit_image));
				}
				
				$DB->query("UPDATE exp_members SET sig_img_filename = '', sig_img_width='', sig_img_height='' WHERE member_id = '".$SESS->userdata('member_id')."' ");
			
				@unlink($PREFS->ini('sig_img_path', TRUE).$query->row['sig_img_filename']);			
			}
			
			
			return $this->_var_swap($this->_load_element('success'),
									array(
											'lang:heading'	=>	$LANG->line($remove),
											'lang:message'	=>	$LANG->line($removed)
										 )
									);
		}
		
				
		/** ----------------------------------------
		/**  Do the have the GD library?
		/** ----------------------------------------*/

		if ( ! function_exists('getimagesize')) 
		{
			return $this->_trigger_error($edit_image, 'gd_required');		
		}
										
		/** ----------------------------------------
		/**  Is there $_FILES data?
		/** ----------------------------------------*/
				
		if ( ! isset($_FILES['userfile']))
		{
			return $FNS->redirect($this->_member_path($edit_image));
		}

		/** ----------------------------------------
		/**  Check the image size
		/** ----------------------------------------*/
		
		$size = ceil(($_FILES['userfile']['size']/1024));
		
		if ($type == 'avatar')
		{
			$max_size = ($PREFS->ini('avatar_max_kb') == '' OR $PREFS->ini('avatar_max_kb') == 0) ? 50 : $PREFS->ini('avatar_max_kb');
		}
		elseif ($type == 'photo')
		{
			$max_size = ($PREFS->ini('photo_max_kb') == '' OR $PREFS->ini('photo_max_kb') == 0) ? 50 : $PREFS->ini('photo_max_kb');
		}
		else
		{
			$max_size = ($PREFS->ini('sig_img_max_kb') == '' OR $PREFS->ini('sig_img_max_kb') == 0) ? 50 : $PREFS->ini('sig_img_max_kb');
		}

		
		$max_size = preg_replace("/(\D+)/", "", $max_size);

		if ($size > $max_size)
		{
			return $OUT->show_user_error('submission', str_replace('%s', $max_size, $LANG->line('image_max_size_exceeded')));
		}
		
		/** ----------------------------------------
		/**  Is the upload path valid and writable?
		/** ----------------------------------------*/
		
		if ($type == 'avatar')
		{
			$upload_path = $PREFS->ini('avatar_path', TRUE).'uploads/';
		}
		elseif ($type == 'photo')
		{
			$upload_path = $PREFS->ini('photo_path', TRUE);
		}
		else
		{
			$upload_path = $PREFS->ini('sig_img_path', TRUE);
		}

		if ( ! @is_dir($upload_path) OR ! is_writable($upload_path))
		{
			return $this->_trigger_error($edit_image, 'image_assignment_error');
		}

		/** -------------------------------------
		/**  Set some defaults
		/** -------------------------------------*/
		
		$filename = $_FILES['userfile']['name'];
		
		if ($type == 'avatar')
		{
			$max_width	= ($PREFS->ini('avatar_max_width') == '' OR $PREFS->ini('avatar_max_width') == 0) ? 100 : $PREFS->ini('avatar_max_width');
			$max_height	= ($PREFS->ini('avatar_max_height') == '' OR $PREFS->ini('avatar_max_height') == 0) ? 100 : $PREFS->ini('avatar_max_height');	
			$max_kb		= ($PREFS->ini('avatar_max_kb') == '' OR $PREFS->ini('avatar_max_kb') == 0) ? 50 : $PREFS->ini('avatar_max_kb');	
		}
		elseif ($type == 'photo')
		{
			$max_width	= ($PREFS->ini('photo_max_width') == '' OR $PREFS->ini('photo_max_width') == 0) ? 100 : $PREFS->ini('photo_max_width');
			$max_height	= ($PREFS->ini('photo_max_height') == '' OR $PREFS->ini('photo_max_height') == 0) ? 100 : $PREFS->ini('photo_max_height');	
			$max_kb		= ($PREFS->ini('photo_max_kb') == '' OR $PREFS->ini('photo_max_kb') == 0) ? 50 : $PREFS->ini('photo_max_kb');
		}
		else
		{
			$max_width	= ($PREFS->ini('sig_img_max_width') == '' OR $PREFS->ini('sig_img_max_width') == 0) ? 100 : $PREFS->ini('sig_img_max_width');
			$max_height	= ($PREFS->ini('sig_img_max_height') == '' OR $PREFS->ini('sig_img_max_height') == 0) ? 100 : $PREFS->ini('sig_img_max_height');	
			$max_kb		= ($PREFS->ini('sig_img_max_kb') == '' OR $PREFS->ini('sig_img_max_kb') == 0) ? 50 : $PREFS->ini('sig_img_max_kb');
		}

		/** ----------------------------------------
		/**  Does the image have a file extension?
		/** ----------------------------------------*/
		
		if (strpos($filename, '.') === FALSE)
		{
			return $OUT->show_user_error('submission', $LANG->line('invalid_image_type'));
		}
		
		/** ----------------------------------------
		/**  Is it an allowed image type?
		/** ----------------------------------------*/
		
		$xy = explode('.', $filename);
		$extension = '.'.end($xy);
		
		// We'll do a simple extension check now.
		// The file upload class will do a more thorough check later
		
		$types = array('.jpg', '.jpeg', '.gif', '.png');
		
		if ( ! in_array(strtolower($extension), $types))
		{
			return $OUT->show_user_error('submission', $LANG->line('invalid_image_type'));
		}

		/** -------------------------------------
		/**  Assign the name of the image
		/** -------------------------------------*/
		
		$new_filename = $type.'_'.$SESS->userdata('member_id').strtolower($extension);
		
		/** -------------------------------------
		/**  Do they currently have an avatar or photo?
		/** -------------------------------------*/
		
		if ($type == 'avatar')
		{
			$query = $DB->query("SELECT avatar_filename FROM exp_members WHERE member_id = '".$SESS->userdata('member_id')."'");
			$old_filename = ($query->row['avatar_filename'] == '') ? '' : $query->row['avatar_filename'];
			
			if (strpos($old_filename, '/') !== FALSE)
			{
				$xy = explode('/', $old_filename);
				$old_filename =  end($xy);
			}
		}
		elseif ($type == 'photo')
		{
			$query = $DB->query("SELECT photo_filename FROM exp_members WHERE member_id = '".$SESS->userdata('member_id')."'");
			$old_filename = ($query->row['photo_filename'] == '') ? '' : $query->row['photo_filename'];
		}
		else
		{
			$query = $DB->query("SELECT sig_img_filename FROM exp_members WHERE member_id = '".$SESS->userdata('member_id')."'");
			$old_filename = ($query->row['sig_img_filename'] == '') ? '' : $query->row['sig_img_filename'];
		}
		
		/** -------------------------------------
		/**  Upload the image
		/** -------------------------------------*/

        require PATH_CORE.'core.upload'.EXT;
  
        $UP = new Upload();
       
        $UP->new_name = $new_filename;
        
		$UP->set_upload_path($upload_path);
        $UP->set_allowed_types('img');
   
        if ( ! $UP->upload_file())
        {
			@unlink($UP->new_name);
			
			$info = ($UP->error_msg == 'invalid_filetype') ? "<div class='itempadbig'>".$LANG->line('invalid_image_type')."</div>" : '';
			return $OUT->show_user_error('submission', $LANG->line($UP->error_msg).$info);
        }
		
		/** -------------------------------------
		/**  Do we need to resize?
		/** -------------------------------------*/
		
		$vals	= @getimagesize($UP->new_name);		
		$width	= $vals['0'];
		$height	= $vals['1'];
		
		if ($width > $max_width OR $height > $max_height)
		{
			/** -------------------------------------
			/**  Was resizing successful?
			/** -------------------------------------*/
			
			// If not, we'll delete the uploaded image and
			// issue an error saying the file is to big
		
			if ( ! $this->_image_resize($new_filename, $type))
			{
				@unlink($UP->new_name);

				$max_size = str_replace('%x', $max_width, $LANG->line('max_image_size'));
				$max_size = str_replace('%y', $max_height, $max_size);
				$max_size .= ' - '.$max_kb.'KB';

				return $OUT->show_user_error('submission', $max_size);
			}
		}
		
		/** -------------------------------------
		/**  Check the width/height one last time
		/** -------------------------------------*/
	
		// Since our image resizing class will only reproportion
		// based on one axis, we'll check the size again, just to 
		// be safe.  We need to make absolutely sure that if someone
		// submits a very short/wide image it'll contrain properly
	
		$vals	= @getimagesize($UP->new_name);		
		$width	= $vals['0'];
		$height	= $vals['1'];
		
		if ($width > $max_width OR $height > $max_height)
		{
			$this->_image_resize($new_filename, $type, 'height');
			$vals	= @getimagesize($UP->new_name);		
			$width	= $vals['0'];
			$height	= $vals['1'];
		}
		
		/** -------------------------------------
		/**  Delete the old file if necessary
		/** -------------------------------------*/
		
		if ($old_filename != $new_filename)
		{
			@unlink($upload_path.$old_filename);
		}
		
		/** ----------------------------------------
		/**  Update DB
		/** ----------------------------------------*/

		if ($type == 'avatar')
		{
			$avatar = 'uploads/'.$new_filename;
			$DB->query("UPDATE exp_members SET avatar_filename = '{$avatar}', avatar_width='{$width}', avatar_height='{$height}' WHERE member_id = '".$SESS->userdata('member_id')."' ");
		}
		elseif ($type == 'photo')
		{
			$DB->query("UPDATE exp_members SET photo_filename = '{$new_filename}', photo_width='{$width}', photo_height='{$height}' WHERE member_id = '".$SESS->userdata('member_id')."' ");
		}
		else
		{
			$DB->query("UPDATE exp_members SET sig_img_filename = '{$new_filename}', sig_img_width='{$width}', sig_img_height='{$height}' WHERE member_id = '".$SESS->userdata('member_id')."' ");
		}
        
        /** -------------------------------------
        /**  Success message
        /** -------------------------------------*/
	
		return $this->_var_swap($this->_load_element('success'),
								array(
										'lang:heading'	=>	$LANG->line($edit_image),
										'lang:message'	=>	$LANG->line($updated)
									 )
								);
	}
	/* END */
	
	
    /** ----------------------------------------
    /**  Image Resizing
    /** ----------------------------------------*/
	
	function _image_resize($filename, $type = 'avatar', $axis = 'width')
	{
		global $PREFS;
		
		if ( ! class_exists('Image_lib'))
		{
			require PATH_CORE.'core.image_lib'.EXT;
		}
		
		$IM = new Image_lib();
		
		if ($type == 'avatar')
		{
			$max_width	= ($PREFS->ini('avatar_max_width') == '' OR $PREFS->ini('avatar_max_width') == 0) ? 100 : $PREFS->ini('avatar_max_width');
			$max_height	= ($PREFS->ini('avatar_max_height') == '' OR $PREFS->ini('avatar_max_height') == 0) ? 100 : $PREFS->ini('avatar_max_height');	
			$image_path = $PREFS->ini('avatar_path', TRUE).'uploads/';
		}
		elseif ($type == 'photo')
		{
			$max_width	= ($PREFS->ini('photo_max_width') == '' OR $PREFS->ini('photo_max_width') == 0) ? 100 : $PREFS->ini('photo_max_width');
			$max_height	= ($PREFS->ini('photo_max_height') == '' OR $PREFS->ini('photo_max_height') == 0) ? 100 : $PREFS->ini('photo_max_height');	
			$image_path = $PREFS->ini('photo_path', TRUE);		
		}
		else
		{
			$max_width	= ($PREFS->ini('sig_img_max_width') == '' OR $PREFS->ini('sig_img_max_width') == 0) ? 100 : $PREFS->ini('sig_img_max_width');
			$max_height	= ($PREFS->ini('sig_img_max_height') == '' OR $PREFS->ini('sig_img_max_height') == 0) ? 100 : $PREFS->ini('sig_img_max_height');	
			$image_path = $PREFS->ini('sig_img_path', TRUE);		
		}

		$res = $IM->set_properties(			
									array(
											'resize_protocol'	=> $PREFS->ini('image_resize_protocol'),
											'libpath'			=> $PREFS->ini('image_library_path'),
											'maintain_ratio'	=> TRUE,
											'master_dim'		=> $axis,
											'file_path'			=> $image_path,
											'file_name'			=> $filename,
											'quality'			=> 75,
											'dst_width'			=> $max_width,
											'dst_height'		=> $max_height
											)
									);
		if ( ! $IM->image_resize())
		{
			return FALSE;
		}
	
		return TRUE;
	}
	/* END */
	

    /** ----------------------------------------
    /**  Photo Edit Form
    /** ----------------------------------------*/
	
	function edit_photo()
	{
		global $DB, $LANG, $PREFS, $SESS, $FNS;
		
		/** ----------------------------------------
		/**  Are photos enabled?
		/** ----------------------------------------*/
		
		if ($PREFS->ini('enable_photos') == 'n')
		{
			return $this->_trigger_error('edit_photo', 'photos_not_enabled');
		}
		
		/** ----------------------------------------
		/**  Fetch the photo template
		/** ----------------------------------------*/

		$template = $this->_load_element('edit_photo');
				
		/** ----------------------------------------
		/**  Does the current user have a photo?
		/** ----------------------------------------*/
		
		$query = $DB->query("SELECT photo_filename, photo_width, photo_height FROM exp_members WHERE member_id = '".$SESS->userdata('member_id')."'");
		
		if ($query->row['photo_filename'] == '')
		{
			$template = $this->_deny_if('photo', $template);
			$template = $this->_allow_if('no_photo', $template);
			
			$cur_photo_url = '';
			$photo_width 	= '';
			$photo_height 	= '';
		}
		else
		{
			$template = $this->_allow_if('photo', $template);
			$template = $this->_deny_if('no_photo', $template);
			
			$cur_photo_url = $PREFS->ini('photo_url', TRUE).$query->row['photo_filename'];
			$photo_width 	= $query->row['photo_width'];
			$photo_height 	= $query->row['photo_height'];
		}
		
		/** ----------------------------------------
		/**  Set the default image meta values
		/** ----------------------------------------*/
		
		$max_kb = ($PREFS->ini('photo_max_kb') == '' OR $PREFS->ini('photo_max_kb') == 0) ? 50 : $PREFS->ini('photo_max_kb');
		$max_w  = ($PREFS->ini('photo_max_width') == '' OR $PREFS->ini('photo_max_width') == 0) ? 100 : $PREFS->ini('photo_max_width');
		$max_h  = ($PREFS->ini('photo_max_height') == '' OR $PREFS->ini('photo_max_height') == 0) ? 100 : $PREFS->ini('photo_max_height');
		$max_size = str_replace('%x', $max_w, $LANG->line('max_image_size'));
		$max_size = str_replace('%y', $max_h, $max_size);
		$max_size .= ' - '.$max_kb.'KB';

		/** ----------------------------------------
		/**  Finalize the template
		/** ----------------------------------------*/
		
		return $this->_var_swap($template,
								array(
										'form_declaration'		=> $FNS->form_declaration(
																							array(
																									'action' 		=> $this->_member_path('upload_photo'),
																									'enctype'		=> 'multi'
																								)
																						),
										'lang:max_image_size'	=>  $max_size,
										'path:member_photo'		=> 	$cur_photo_url,
										'photo_width'			=> 	$photo_width,
										'photo_height'			=>	$photo_height,
										'name'					=>  $query->row['photo_filename']
									 )
								);
	}
	/* END */
	
}
// END CLASS
?>