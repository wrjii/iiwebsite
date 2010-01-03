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
 File: install.php
-----------------------------------------------------
 Purpose: Installation file
=====================================================
*/

error_reporting(E_ALL);
@set_magic_quotes_runtime(0);



if (isset($_GET) AND count($_GET) > 0)
{
	if ( ! isset($_GET['page']) OR ! is_numeric($_GET['page']) OR count($_GET) > 1)
	{
		exit("Disallowed GET request");
	}
}



$data = array(
				'app_full_version'		=> 'Version 1.6.8',
				'app_version'			=> '168',
				'doc_url'				=> 'http://expressionengine.com/docs/',
				'ext'					=> '.php',
				'ip'					=> '',
				'database'				=> 'mysql',
				'db_conntype'			=> '0',
				'system_dir'			=> 'system',
				'db_hostname'			=> 'localhost',
				'db_username'			=> '',
				'db_password'			=> '',
				'db_name'				=> '',
				'db_prefix'				=> 'exp',
				'encryption_type'		=> 'sha1',
				'site_name'				=> 	'',
				'site_url'				=> '',
				'site_index'			=> '',
				'cp_url'				=> '',
				'cp_index'				=> 'index.php',
				'username'				=> '',
				'password'				=> '',
				'screen_name'			=> '',
				'email'					=> '',
				'webmaster_email'		=> '',
				'deft_lang'				=> 'english',
				'template'				=> '01',
				'server_timezone'		=> 'UTC',
				'daylight_savings'		=> '',
				'redirect_method'		=> 'redirect',
				'upload_folder'			=> 'uploads/',
				'image_path'			=> '../images/',
				'cp_images'				=> 'cp_images/',
				'avatar_path'			=> '../images/avatars/',
				'avatar_url'			=> 'images/avatars/',
				'photo_path'			=> '../images/member_photos/',
				'photo_url'				=> 'images/member_photos/',
				'signature_img_path'	=> '../images/signature_attachments/',
				'signature_img_url'		=> 'images/signature_attachments/',
				'pm_path'				=> '../images/pm_attachments',
				'captcha_path'			=> '../images/captchas/',
				'theme_folder_path'		=> '../themes/',
			);



foreach ($_POST as $key => $val)
{
	if ( ! get_magic_quotes_gpc())
		$val = addslashes($val);

	if (isset($data[$key]))
	{
		$data[$key] = trim($val);	
	}
}

$data['site_url'] = rtrim($data['site_url'], '/').'/';

$data['system_dir'] = str_replace("/", "", $data['system_dir']);
define('EXT', $data['ext']);
$page = (! isset($_GET['page']) || $_GET['page'] > 7) ? 1 : $_GET['page'];


if (phpversion() < '4.1.0')
{
	$page = 0;
}


// HTML HEADER
page_head();


// Unsupported version of PHP
// --------------------------------------------------------------------
// --------------------------------------------------------------------

if ($page == 0)
{
?>
<div id='innercontent'>

<div class="error">Error:&nbsp;&nbsp;Unsupported PHP version</div>


<p><b>In order to install ExpressionEngine, your server must be running PHP version 4.1 or newer.</b></p>

<p><br />Your server is currently running PHP version: <?php echo phpversion(); ?></p>

<p><br />If you would like to switch to a host that provides more current software,
please consider <a href="http://www.enginehosting.com/">EngineHosting</a></p>
</div>

<?php
}




// PAGE ONE
// --------------------------------------------------------------------
// --------------------------------------------------------------------


if ($page == 1)
{
?>
     
<div id='innercontent'>

<h1>ExpressionEngine Installation Wizard</h1>

<div class="botBorder">
<div class="pad">
<p><b>Do you need assistance?</b>&nbsp; If you have questions or problems please visit our  <a href="http://expressionengine.com/support/">Support Resources</a> page</p>

</div>
</div>


<div class="botBorder">

<p><br /><span class='red'><b>Important:&nbsp;</b> Use this installation wizard <b>ONLY</b> if you are installing ExpressionEngine for the first time.&nbsp;
Do <b>NOT</b> run this installation routine if you are already using ExpressionEngine and are updating to a newer version.&nbsp; You'll find
update instructions in the user guide.</span><br />&nbsp;</p>

</div>


<div class="botBorder">

<h4>Installation Instructions</h4>

<p>Before proceeding please take a moment to read the 
<a href="http://expressionengine.com/docs/#install_docs" target="_blank">installation instructions</a> if you have not done so already.</p>





<br />
</div>

<form method="post" action="install.php?page=2">
<p class="center"><br />Are you ready to install ExpressionEngine?</p>
<p class="center"><input type="submit" class="submit" value=" Click here to begin! " /></p>
</form>

</div>
<?php
}


// PAGE TWO
// --------------------------------------------------------------------
// --------------------------------------------------------------------

elseif ($page == 2)
{
	license_agreement();
}

// PAGE THREE
// --------------------------------------------------------------------
// --------------------------------------------------------------------


elseif ($page == 3)
{
	if ( ! isset($_POST['agree']) OR $_POST['agree'] == 'no')
	{
		license_agreement();
	}
	else
	{
    	system_folder_form();
    }
}




// PAGE FOUR
// --------------------------------------------------------------------
// --------------------------------------------------------------------


elseif ($page == 4)
{

// Does the 'system' directory exist?
// --------------------------------------------------------------------

    if ($data['system_dir'] == '' OR ! is_dir('./'.$data['system_dir']))
    {
        ?><div class='error'>Error: Unable to locate the directory you submitted.</div><?php
        system_folder_form();
        page_footer();
    
        exit;
    }


// Are the various files and directories writable?
// --------------------------------------------------------------------
    
    $system_path = './'.trim($data['system_dir']).'/';
    
    $writable_things = array(
    							'path.php',
    							$system_path.'config.php', 
    							$system_path.'cache/'
    						);
    
    
    $not_writable = array();
    
    foreach ($writable_things as $val)
    {        
        if ( ! @is_writable($val))
        {
            $not_writable[] = $val;
        }
    }
    
    if ( ! @is_writable("./images/uploads"))
    {
        $not_writable[] = "images/uploads";
    }
    
    if ( ! @is_writable("./images/captchas"))
    {
        $not_writable[] = "images/captchas";
    }
    
    
    $i = count($not_writable);
    
    if ($i > 0)
    {
    	echo "<div id='innercontent'>";
    	
    	$d = ($i > 1) ? 'Directories or Files' : 'Directory or File';
    	
		echo "<div class='error'>Error: Incorrect Permissions for ".$d."</div>";
    
        $d = ($i > 1) ? 'directories or files' : 'directory or file';

        echo "<p>The following $d cannot be written to:</p>";
                
        foreach ($not_writable as $bad)
        {
			echo '<p>&nbsp;&nbsp;&nbsp;&nbsp;<strong>'.$bad.'</strong></p>';
        }
                
            $item = ($i > 1) ? 'items' : 'item';
        ?> 
                
        <p>In order to run this installation, the file permissions on the above <?php echo $item; ?> must be set as indicated in the instructions.</p>
        
        <p>If you are not sure how to set permissions, <a href='http://expressionengine.com/knowledge_base/article/how_do_you_set_permissions_on_files_and_directories/' target='_blank'>click here</a>.</p>
        
        <p><b>Once you are done, please <a href='javascript:history.go(-1)'>run</a> this installation script again.</b></p>
        
        </div>
        <?php
        
        page_footer();
        
        exit;
    }
    
	if ( ! @file_exists($system_path.'config'.$data['ext']))
	{
        echo "<div class='error'><br />Error: Unable to locate your config.php file.  Please make sure you have uploaded all components of this software.</div><br />";
	
        page_footer();
        
        exit;
    }
    else
    {
		require($system_path.'config'.$data['ext']);
    }
    
        
    if (isset($conf['install_lock']) AND $conf['install_lock'] == 1)
    {
    ?>
	<div id='innercontent'>
    <div class="error">Warning:  Your installation lock is set</div>
    
    <p>There already appears to be an installed instance of ExpressionEngine</p>
        
    <p>If you absolutely want to install this program you must locate the file called <b>config.php</b> and delete its contents.</b>
    
    <p>Once you've done this, <a href="install.php?page=3">click here</a> to continue</p>
    </div>
    <?php    
    }
    else
    {
		settings_form();
	}
}





// PAGE FIVE
// --------------------------------------------------------------------
// --------------------------------------------------------------------


elseif ($page == 5)
{

    if ($data['db_hostname'] == '' AND $data['db_username'] == '' AND $data['db_name'] == '')
    {
    	echo "<p>An errror occured.  <a href='install.php'>Click here</a> to return to the main page</a>";
		page_footer();
		exit;    
    } 


	$errors = array();
    $system_path = './'.trim($data['system_dir']).'/';
    
	if ( ! @file_exists($system_path.'config'.$data['ext']))
	{
     	$errors[] = "Unable to locate the file called \"config.php\".  Please make sure you have uploaded all components of this software.";
    }
    else
    {
		require($system_path.'config'.$data['ext']);
    }
	
    
    if (isset($conf['install_lock']) AND $conf['install_lock'] == 1)
    {
        $errors[] = "Your installation lock is set. Locate the file called <b>config.php</b> and delete its contents";
    }
	

    if (
        $data['db_hostname'] == '' ||
        $data['db_username'] == '' ||
        $data['db_name']     == '' ||
        $data['site_name']   == '' ||
        $data['username']    == '' ||
        $data['password']    == '' ||
        $data['email']       == '' 
       )
    {
        $errors[] = "You left some form fields empty";
    } 

    if (strlen($data['username']) < 4)
    {
        $errors[] = "Your username must be at least 4 characters in length";
    }
    
    
    if (strlen($data['password']) < 5)
    {
        $errors[] = "Your password must be at least 5 characters in length";
    }

	//  Is password the same as username?

	$lc_user = strtolower($data['username']);
	$lc_pass = strtolower($data['password']);
	$nm_pass = strtr($lc_pass, 'elos', '3105');


	if ($lc_user == $lc_pass || $lc_user == strrev($lc_pass) || $lc_user == $nm_pass || $lc_user == strrev($nm_pass))
	{
		$errors[] = "Your password can not be based on the username";
	}
	
	if (strpos($data['db_password'], '$') !== FALSE)
	{
		$errors[] = "Your MySQL password can not contain a dollar sign (\$)";
	}
	
	if ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $data['email']))
	{
		$errors[] = "The email address you submitted is not valid";
	}	
	
	if ($data['screen_name'] == '')
	{
		$data['screen_name'] = $data['username'];
	}


//  CONNECT TO DATABASE
// --------------------------------------------------------------------

	$db_prefix = ($data['db_prefix'] == '') ? 'exp' : $data['db_prefix'];
    
	if ( ! @file_exists($system_path.'db/db.'.$data['database'].$data['ext']))
	{
     	$errors[] = "Unable to locate the database file.  Please make sure you have uploaded all components of this software.";
    }
    else
    {
		require($system_path.'db/db.'.$data['database'].$data['ext']);
            
		$db_config = array(
							'hostname'  	=> $data['db_hostname'],
							'username'  	=> $data['db_username'],
							'password'  	=> $data['db_password'],
							'database'  	=> $data['db_name'],
							'conntype'		=> $data['db_conntype'],
							'prefix'    	=> $db_prefix,
							'enable_cache'	=> FALSE
						  );
						  
		$DB = new DB($db_config);
			
		if ( ! $DB->db_connect(0))
		{
			$errors[] = "Unable to connect to your database server. ";
			
			if ($data['db_conntype'] == 1)
			{
				$errors[] = "Try switching to a non-persistent connection since some servers do not support persistent connections.";
			}
		}
		
		if ( ! $DB->select_db())
		{
			$errors[] = "Unable to select the database";
		}
		
		// Check for "strict mode", some queries used are incompatible
		if ($DB->conn_id !== FALSE && version_compare(mysql_get_server_info(), '4.1-alpha', '>=') !== FALSE)
		{
			$mode_query = $DB->query("SELECT CONCAT(@@global.sql_mode, @@session.sql_mode) AS sql_mode");
	
			if (strpos(strtoupper($mode_query->row['sql_mode']), 'STRICT') !== FALSE)
			{
				$errors[] = "ExpressionEngine will not run on a MySQL server operating in strict mode";
			}
		}
	}
	
	if ( ! file_exists($system_path.'language/'.$data['deft_lang'].'/email_data'.$data['ext']))
	{
        $errors[] = "Unable to locate the file containing your email templates.  Make sure you have uploaded all components of this software.";
	}
	else
	{
		require($system_path.'language/'.$data['deft_lang'].'/email_data'.$data['ext']);
	}



//  DISPLAY ERRORS
// --------------------------------------------------------------------

	
	if (count($errors) > 0)
	{
		$er =  "<div class='error'>ERROR: The following Errors were encountered</div>";
		$er .= "<ol>";
		
		foreach ($errors as $doh)
		{
			$er .= "<li>".$doh."</li>";
		}
	
		$er .= "</ol>";
	
		$er .= "<div class='border'><p><b>Please correct the errors and resubmit the form</b><br /><br /></p></div>";
	
		settings_form($er);
		page_footer();
		exit;    
	}

//  Existing Install?
// --------------------------------------------------------------------

	// is the user trying to install to an existing installation?
	// This can happen if someone mistakenly copies over their config.php
	// during an update, and then trying to run the installer...
	
	$query = $DB->query("SHOW tables LIKE '".$DB->escape_str($db_prefix)."%'");

	if ($query->num_rows > 0 && ! isset($_POST['install_override']))
	{
		$fields = '';
		
		foreach($_POST as $key => $value)
		{
			if (get_magic_quotes_gpc())
			{
				$value = stripslashes($value);
			}
			
			$fields .= '<input type="hidden" name="'.str_replace("'", "&#39;", htmlspecialchars($key)).'" value="'.str_replace("'", "&#39;", htmlspecialchars($value)).'" />'."\n";
		}
	
	
		$er = '<div id="innercontent">
		<div class="error">Existing Installation Detected, Empty config.php File</div>

<p>ExpressionEngine appears to be installed to your database, but your configuration file is empty.
Continuing with this installation will destroy any information currently in your database.  
Are you sure you wish to perform a new installation?</p>

<form action="install.php?page=5" method="post">
'.$fields.'
<input type="hidden" name="install_override" value="y" />
<p><input type="submit" value="Continue Installation"></p>
</form>
<form action="install.php?page=6" method="post">
'.$fields.'
<input type="hidden" name="rebuild_config" value="y" />
<p><input type="submit" value="Do NOT Install, Fix Configuration"></p>
</form>

</div>
';
		echo $er;
		page_footer();
		exit;
	}
	
	

//  Prep user submitted data for DB insertion
// --------------------------------------------------------------------
// --------------------------------------------------------------------


    /** -------------------------------------
    /**  Get user's IP address
    /** -------------------------------------*/
        
    $CIP = ( ! isset($_SERVER['HTTP_CLIENT_IP']))       ? FALSE : $_SERVER['HTTP_CLIENT_IP'];
    $FOR = ( ! isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? FALSE : $_SERVER['HTTP_X_FORWARDED_FOR'];
    $RMT = ( ! isset($_SERVER['REMOTE_ADDR']))          ? FALSE : $_SERVER['REMOTE_ADDR'];    
    
    if ($CIP) 
    {
        $cip = explode('.', $CIP);
        
        $data['ip'] = ($cip['0'] != current(explode('.', $RMT))) ? implode('.', array_reverse($cip)) : $CIP;
    }
    elseif ($FOR) 
    {
        $data['ip'] = (strstr($FOR, ',')) ? end(explode(',', $FOR)) : $FOR;
    }
    else
        $data['ip'] = $RMT;


    /** -------------------------------------
    /**  Encrypt password and Unique ID 
    /** -------------------------------------*/
    
    if (phpversion() >= 4.2)
        mt_srand();
    else
        mt_srand(hexdec(substr(md5(microtime()), -8)) & 0x7fffffff);    
    
    $unique_id = uniqid(mt_rand());
    
    $password = stripslashes($data['password']);
    
    
    if ($data['encryption_type'] == 'sha1')
    {
		if ( ! function_exists('sha1'))
		{
			if ( ! function_exists('mhash'))
			{        
				require './'.$data['system_dir'].'/core/core.sha1'.$data['ext'];           
			
				$SH = new SHA;
				
				$unique_id = $SH->encode_hash($unique_id);            
				$password  = $SH->encode_hash($password);            
			}
			else
			{
				$unique_id = bin2hex(mhash(MHASH_SHA1, $unique_id));
				$password  = bin2hex(mhash(MHASH_SHA1, $password));
			}
		}
		else
		{
			$unique_id = sha1($unique_id);
			$password  = sha1($password);
		}
	}
	else
	{
		$unique_id = md5($unique_id);
		$password  = md5($password);
	}
      
    
    /** -------------------------------------
    /**  Fetch current time as GMT
    /** -------------------------------------*/
    
    $time	= time(); 
    $now	= mktime(gmdate("H", $time), gmdate("i", $time), gmdate("s", $time), gmdate("m", $time), gmdate("d", $time), gmdate("Y", $time));
    $year	= gmdate('Y', $now);
    $month	= gmdate('m', $now);
    $day	= gmdate('d', $now);
   
		    

//  DEFINE DB TABLES
// --------------------------------------------------------------------
// --------------------------------------------------------------------

// Sites

$D[] = "exp_sites";

$Q[] = "CREATE TABLE `exp_sites` (
	  `site_id` int(5) unsigned NOT NULL auto_increment,
	  `site_label` varchar(100) NOT NULL default '',
	  `site_name` varchar(50) NOT NULL default '',
	  `site_description` text NOT NULL,
	  `site_system_preferences` TEXT NOT NULL ,
	  `site_mailinglist_preferences` TEXT NOT NULL ,
	  `site_member_preferences` TEXT NOT NULL ,
	  `site_template_preferences` TEXT NOT NULL ,
	  `site_weblog_preferences` TEXT NOT NULL ,
	  PRIMARY KEY  (`site_id`),
	  KEY `site_name` (`site_name`))";



// Session data

$D[] = 'exp_sessions';

$Q[] = "CREATE TABLE exp_sessions (
  session_id varchar(40) default '0' NOT NULL,
  site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
  member_id int(10) default '0' NOT NULL,
  admin_sess tinyint(1) default '0' NOT NULL,
  ip_address varchar(16) default '0' NOT NULL,
  user_agent varchar(50) NOT NULL,
  last_activity int(10) unsigned DEFAULT '0' NOT NULL,
  PRIMARY KEY (session_id),
  KEY (`member_id`),
  KEY (`site_id`)
)";

// Throttle

$D[] = 'exp_throttle';

$Q[] = "CREATE TABLE exp_throttle (
  ip_address varchar(16) default '0' NOT NULL,
  last_activity int(10) unsigned DEFAULT '0' NOT NULL,
  hits int(10) unsigned NOT NULL,
  locked_out char(1) NOT NULL default 'n',
  KEY (ip_address),
  KEY (last_activity)
)";


// System stats

$D[] = 'exp_stats';

$Q[] = "CREATE TABLE exp_stats (
  weblog_id int(6) unsigned NOT NULL default '0',
  site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
  total_members mediumint(7) NOT NULL default '0',
  recent_member_id int(10) default '0' NOT NULL,
  recent_member varchar(50) NOT NULL,
  total_entries mediumint(8) default '0' NOT NULL,
  total_forum_topics mediumint(8) default '0' NOT NULL,
  total_forum_posts mediumint(8) default '0' NOT NULL,
  total_comments mediumint(8) default '0' NOT NULL,
  total_trackbacks mediumint(8) default '0' NOT NULL,
  last_entry_date int(10) unsigned default '0' NOT NULL,
  last_forum_post_date int(10) unsigned default '0' NOT NULL,
  last_comment_date int(10) unsigned default '0' NOT NULL,
  last_trackback_date int(10) unsigned default '0' NOT NULL,
  last_visitor_date int(10) unsigned default '0' NOT NULL, 
  most_visitors mediumint(7) NOT NULL default '0',
  most_visitor_date int(10) unsigned default '0' NOT NULL,
  last_cache_clear int(10) unsigned default '0' NOT NULL,
  KEY (weblog_id),
  KEY (site_id)
)";


// Online users

$D[] = 'exp_online_users';

$Q[] = "CREATE TABLE exp_online_users (
 weblog_id int(6) unsigned NOT NULL default '0',
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 member_id int(10) default '0' NOT NULL,
 in_forum char(1) NOT NULL default 'n',
 name varchar(50) default '0' NOT NULL,
 ip_address varchar(16) default '0' NOT NULL,
 date int(10) unsigned default '0' NOT NULL,
 anon char(1) NOT NULL,
 KEY (date),
 KEY (site_id)
)";


// Actions table
// Actions are events that require processing. Used by modules class.

$D[] = 'exp_actions';

$Q[] = "CREATE TABLE exp_actions (
 action_id int(4) unsigned NOT NULL auto_increment,
 class varchar(50) NOT NULL,
 method varchar(50) NOT NULL,
 PRIMARY KEY (action_id)
)";

// Modules table
// Contains a list of all installed modules

$D[] = 'exp_modules';

$Q[] = "CREATE TABLE exp_modules (
 module_id int(4) unsigned NOT NULL auto_increment,
 module_name varchar(50) NOT NULL,
 module_version varchar(12) NOT NULL,
 has_cp_backend char(1) NOT NULL default 'n',
 PRIMARY KEY (module_id)
)";

// Referrer tracking table

$D[] = 'exp_referrers';

$Q[] = "CREATE TABLE exp_referrers (
  ref_id int(10) unsigned NOT NULL auto_increment,
  site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
  ref_from varchar(120) NOT NULL,
  ref_to varchar(120) NOT NULL,
  ref_ip varchar(16) default '0' NOT NULL,
  ref_date int(10) unsigned default '0' NOT NULL,
  ref_agent varchar(100) NOT NULL,
  user_blog varchar(40) NOT NULL,
  PRIMARY KEY (ref_id),
  KEY (site_id)
)";

// Security Hashes
// Used to store hashes needed to process forms in 'secure mode'

$D[] = 'exp_security_hashes';

$Q[] = "CREATE TABLE exp_security_hashes (
 date int(10) unsigned NOT NULL,
 ip_address varchar(16) default '0' NOT NULL,
 hash varchar(40) NOT NULL,
 KEY (hash)
)";

// Captcha data

$D[] = 'exp_captcha';

$Q[] = "CREATE TABLE exp_captcha (
 captcha_id bigint(13) unsigned NOT NULL auto_increment,
 date int(10) unsigned NOT NULL,
 ip_address varchar(16) default '0' NOT NULL,
 word varchar(20) NOT NULL,
 PRIMARY KEY (captcha_id),
 KEY (word)
)";

// Password Lockout
// If password lockout is enabled, a user only gets
// four attempts to log-in within a specified period.
// This table holds the a list of locked out users

$D[] = 'exp_password_lockout';

$Q[] = "CREATE TABLE exp_password_lockout (
 login_date int(10) unsigned NOT NULL,
 ip_address varchar(16) default '0' NOT NULL,
 user_agent varchar(50) NOT NULL,
 KEY (login_date),
 KEY (ip_address),
 KEY (user_agent)
)";

// Reset password
// If a user looses their password, this table
// holds the reset code.

$D[] = 'exp_reset_password';

$Q[] = "CREATE TABLE exp_reset_password (
  member_id int(10) unsigned NOT NULL,
  resetcode varchar(12) NOT NULL,
  date int(10) NOT NULL
)";


$D[] = 'exp_mailing_lists';

$Q[] = "CREATE TABLE exp_mailing_lists (
 list_id int(7) unsigned NOT NULL auto_increment,
 list_name varchar(40) NOT NULL,
 list_title varchar(100) NOT NULL,
 list_template text NOT NULL,
 PRIMARY KEY (list_id),
 KEY (list_name)
)";


// Mailing list
// Notes: "authcode" is a random hash assigned to each member
// of the mailing list.  We use this code in the "usubscribe" link
// added to sent emails.

$D[] = 'exp_mailing_list';

$Q[] = "CREATE TABLE exp_mailing_list (
 user_id int(10) unsigned NOT NULL auto_increment,
 list_id int(7) unsigned default '0' NOT NULL,
 ip_address varchar(16) NOT NULL,
 authcode varchar(10) NOT NULL,
 email varchar(50) NOT NULL,
 KEY (list_id),
 KEY (user_id)
)";

// Mailing List Queue
// When someone signs up for the mailing list, they are sent
// a confirmation email.  This prevents someone from signing 
// up another person.  This table holds email addresses that
// are pending activation.

$D[] = 'exp_mailing_list_queue';

$Q[] = "CREATE TABLE exp_mailing_list_queue (
  email varchar(50) NOT NULL,
  list_id int(7) unsigned default '0' NOT NULL,
  authcode varchar(10) NOT NULL,
  date int(10) NOT NULL
)";

// Email Cache
// We store all email messages that are sent from the CP

$D[] = 'exp_email_cache';

$Q[] = "CREATE TABLE exp_email_cache (
  cache_id int(6) unsigned NOT NULL auto_increment,
  cache_date int(10) unsigned default '0' NOT NULL,
  total_sent int(6) unsigned NOT NULL,
  from_name varchar(70) NOT NULL,
  from_email varchar(70) NOT NULL,
  recipient text NOT NULL,
  cc text NOT NULL,
  bcc text NOT NULL,
  recipient_array mediumtext NOT NULL,
  subject varchar(120) NOT NULL,
  message mediumtext NOT NULL,
  `plaintext_alt` MEDIUMTEXT NOT NULL,
  mailinglist char(1) NOT NULL default 'n',
  mailtype varchar(6) NOT NULL,
  text_fmt varchar(40) NOT NULL,
  wordwrap char(1) NOT NULL default 'y',
  priority char(1) NOT NULL default '3',
  PRIMARY KEY (cache_id)
)";

// Cached Member Groups
// We use this table to store the member group assignments
// for each email that is sent.  Since you can send email
// to various combinations of members, we store the member
// group numbers in this table, which is joined to the 
// table above when we need to re-send an email from cache.

$D[] = 'exp_email_cache_mg';

$Q[] = "CREATE TABLE exp_email_cache_mg (
  cache_id int(6) unsigned NOT NULL,
  group_id smallint(4) NOT NULL,
  KEY (cache_id)
)";

// We do the same with mailing lists

$D[] = 'exp_email_cache_ml';

$Q[] = "CREATE TABLE exp_email_cache_ml (
  cache_id int(6) unsigned NOT NULL,
  list_id smallint(4) NOT NULL,
  KEY (cache_id)
)";


// Email Console Cache
// Emails sent from the member profile email console are saved here.

$D[] = 'exp_email_console_cache';

$Q[] = "CREATE TABLE exp_email_console_cache (
  cache_id int(6) unsigned NOT NULL auto_increment,
  cache_date int(10) unsigned default '0' NOT NULL,
  member_id int(10) unsigned NOT NULL,
  member_name varchar(50) NOT NULL,
  ip_address varchar(16) default '0' NOT NULL,
  recipient varchar(70) NOT NULL,
  recipient_name varchar(50) NOT NULL,
  subject varchar(120) NOT NULL,
  message mediumtext NOT NULL,
  PRIMARY KEY (cache_id)
)";

// Email Tracker
// This table is used by the Email module for flood control.

$D[] = 'exp_email_tracker';

$Q[] = "CREATE TABLE exp_email_tracker (
email_id int(10) unsigned NOT NULL auto_increment,
email_date int(10) unsigned default '0' NOT NULL,
sender_ip varchar(16) NOT NULL,
sender_email varchar(75) NOT NULL ,
sender_username varchar(50) NOT NULL ,
number_recipients int(4) unsigned default '1' NOT NULL,
PRIMARY  KEY (email_id) 
)";

// Member table
// Contains the member info

/*
Note: The following fields are intended for use
with the "user_blog" module.

  weblog_id int(6) unsigned NOT NULL default '0',
  template_id int(6) unsigned NOT NULL default '0',
  upload_id int(6) unsigned NOT NULL default '0',
*/


$D[] = 'exp_members';
 
$Q[] = "CREATE TABLE exp_members (
  member_id int(10) unsigned NOT NULL auto_increment,
  group_id smallint(4) NOT NULL default '0',
  weblog_id int(6) unsigned NOT NULL default '0',
  tmpl_group_id int(6) unsigned NOT NULL default '0',
  upload_id int(6) unsigned NOT NULL default '0',
  username varchar(50) NOT NULL,
  screen_name varchar(50) NOT NULL,
  password varchar(40) NOT NULL,
  unique_id varchar(40) NOT NULL,
  authcode varchar(10) NOT NULL,
  email varchar(50) NOT NULL,
  url varchar(75) NOT NULL,
  location varchar(50) NOT NULL,
  occupation varchar(80) NOT NULL,
  interests varchar(120) NOT NULL,
  bday_d int(2) NOT NULL,
  bday_m int(2) NOT NULL,
  bday_y int(4) NOT NULL,
  aol_im varchar(50) NOT NULL,
  yahoo_im varchar(50) NOT NULL,
  msn_im varchar(50) NOT NULL,
  icq varchar(50) NOT NULL,
  bio text NOT NULL,
  signature text NOT NULL,
  avatar_filename varchar(120) NOT NULL,
  avatar_width int(4) unsigned NOT NULL,
  avatar_height int(4) unsigned NOT NULL,  
  photo_filename varchar(120) NOT NULL,
  photo_width int(4) unsigned NOT NULL,
  photo_height int(4) unsigned NOT NULL,  
  sig_img_filename varchar(120) NOT NULL,
  sig_img_width int(4) unsigned NOT NULL,
  sig_img_height int(4) unsigned NOT NULL,
  ignore_list text NOT NULL,
  private_messages int(4) unsigned DEFAULT '0' NOT NULL,
  accept_messages char(1) NOT NULL default 'y',
  last_view_bulletins int(10) NOT NULL default 0,
  last_bulletin_date int(10) NOT NULL default 0,
  ip_address varchar(16) default '0' NOT NULL,
  join_date int(10) unsigned default '0' NOT NULL,
  last_visit int(10) unsigned default '0' NOT NULL, 
  last_activity int(10) unsigned default '0' NOT NULL, 
  total_entries smallint(5) unsigned NOT NULL default '0',
  total_comments smallint(5) unsigned NOT NULL default '0',
  total_forum_topics mediumint(8) default '0' NOT NULL,
  total_forum_posts mediumint(8) default '0' NOT NULL,
  last_entry_date int(10) unsigned default '0' NOT NULL,
  last_comment_date int(10) unsigned default '0' NOT NULL,
  last_forum_post_date int(10) unsigned default '0' NOT NULL,
  last_email_date int(10) unsigned default '0' NOT NULL,
  in_authorlist char(1) NOT NULL default 'n',
  accept_admin_email char(1) NOT NULL default 'y',
  accept_user_email char(1) NOT NULL default 'y',
  notify_by_default char(1) NOT NULL default 'y',
  notify_of_pm char(1) NOT NULL default 'y',
  display_avatars char(1) NOT NULL default 'y',
  display_signatures char(1) NOT NULL default 'y',
  smart_notifications char(1) NOT NULL default 'y',
  language varchar(50) NOT NULL,
  timezone varchar(8) NOT NULL,
  daylight_savings char(1) default 'n' NOT NULL,
  localization_is_site_default char(1) NOT NULL default 'n',
  time_format char(2) default 'us' NOT NULL,
  cp_theme varchar(32) NOT NULL,
  profile_theme varchar(32) NOT NULL,
  forum_theme varchar(32) NOT NULL,
  tracker text NOT NULL,
  template_size varchar(2) NOT NULL default '28',
  notepad text NOT NULL,
  notepad_size varchar(2) NOT NULL default '18',
  quick_links text NOT NULL,
  quick_tabs text NOT NULL,
  pmember_id int(10) NOT NULL default '0',
  PRIMARY KEY (member_id),
  KEY (`group_id`),
  KEY (`unique_id`),
  KEY (`password`)
)";

// CP homepage layout
// Each member can have their own control panel layout.
// We store their preferences here.

$D[] = 'exp_member_homepage';

$Q[] = "CREATE TABLE exp_member_homepage (
 member_id int(10) unsigned NOT NULL,
 recent_entries char(1) NOT NULL default 'l',
 recent_entries_order int(3) unsigned NOT NULL default '0',
 recent_comments char(1) NOT NULL default 'l',
 recent_comments_order int(3) unsigned NOT NULL default '0',
 recent_members char(1) NOT NULL default 'n',
 recent_members_order int(3) unsigned NOT NULL default '0',
 site_statistics char(1) NOT NULL default 'r',
 site_statistics_order int(3) unsigned NOT NULL default '0',
 member_search_form char(1) NOT NULL default 'n',
 member_search_form_order int(3) unsigned NOT NULL default '0',
 notepad char(1) NOT NULL default 'r',
 notepad_order int(3) unsigned NOT NULL default '0',
 bulletin_board char(1) NOT NULL default 'r',
 bulletin_board_order int(3) unsigned NOT NULL default '0',
 pmachine_news_feed char(1) NOT NULL default 'n',
 pmachine_news_feed_order int(3) unsigned NOT NULL default '0',
 KEY (member_id)
)";				


// Member Groups table

$D[] = 'exp_member_groups';

$Q[] = "CREATE TABLE exp_member_groups (
  group_id smallint(4) unsigned NOT NULL,
  site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
  group_title varchar(100) NOT NULL,
  group_description text NOT NULL,
  is_locked char(1) NOT NULL default 'y', 
  can_view_offline_system char(1) NOT NULL default 'n', 
  can_view_online_system char(1) NOT NULL default 'y', 
  can_access_cp char(1) NOT NULL default 'y', 
  can_access_publish char(1) NOT NULL default 'n',
  can_access_edit char(1) NOT NULL default 'n',
  can_access_design char(1) NOT NULL default 'n',
  can_access_comm char(1) NOT NULL default 'n',
  can_access_modules char(1) NOT NULL default 'n',
  can_access_admin char(1) NOT NULL default 'n',
  can_admin_weblogs char(1) NOT NULL default 'n',
  can_admin_members char(1) NOT NULL default 'n',
  can_delete_members char(1) NOT NULL default 'n',
  can_admin_mbr_groups char(1) NOT NULL default 'n',
  can_admin_mbr_templates char(1) NOT NULL default 'n',
  can_ban_users char(1) NOT NULL default 'n',
  can_admin_utilities char(1) NOT NULL default 'n',
  can_admin_preferences char(1) NOT NULL default 'n',
  can_admin_modules char(1) NOT NULL default 'n',
  can_admin_templates char(1) NOT NULL default 'n',
  can_edit_categories char(1) NOT NULL default 'n',
  can_delete_categories char(1) NOT NULL default 'n',
  can_view_other_entries char(1) NOT NULL default 'n',
  can_edit_other_entries char(1) NOT NULL default 'n',
  can_assign_post_authors char(1) NOT NULL default 'n',
  can_delete_self_entries char(1) NOT NULL default 'n',
  can_delete_all_entries char(1) NOT NULL default 'n',
  can_view_other_comments char(1) NOT NULL default 'n',
  can_edit_own_comments char(1) NOT NULL default 'n',
  can_delete_own_comments char(1) NOT NULL default 'n',
  can_edit_all_comments char(1) NOT NULL default 'n',
  can_delete_all_comments char(1) NOT NULL default 'n',
  can_moderate_comments char(1) NOT NULL default 'n',
  can_send_email char(1) NOT NULL default 'n',
  can_send_cached_email char(1) NOT NULL default 'n',
  can_email_member_groups char(1) NOT NULL default 'n',
  can_email_mailinglist char(1) NOT NULL default 'n',
  can_email_from_profile char(1) NOT NULL default 'n',
  can_view_profiles char(1) NOT NULL default 'n',
  can_delete_self char(1) NOT NULL default 'n',
  mbr_delete_notify_emails varchar(255) NOT NULL,
  can_post_comments char(1) NOT NULL default 'n', 
  exclude_from_moderation char(1) NOT NULL default 'n',
  can_search char(1) NOT NULL default 'n',
  search_flood_control mediumint(5) unsigned NOT NULL,
  can_send_private_messages char(1) NOT NULL default 'n',
  prv_msg_send_limit smallint unsigned NOT NULL default '20',
  prv_msg_storage_limit smallint unsigned NOT NULL default '60',
  can_attach_in_private_messages char(1) NOT NULL default 'n', 
  can_send_bulletins char(1) NOT NULL default 'n',
  include_in_authorlist char(1) NOT NULL default 'n',
  include_in_memberlist char(1) NOT NULL default 'y',
  include_in_mailinglists char(1) NOT NULL default 'y',
  KEY (group_id),
  KEY (site_id)
)";




// Weblog access privs
// Member groups assignment for each weblog

$D[] = 'exp_weblog_member_groups';

$Q[] = "CREATE TABLE exp_weblog_member_groups (
  group_id smallint(4) unsigned NOT NULL,
  weblog_id int(6) unsigned NOT NULL,
  KEY (group_id)
)";

// Module access privs
// Member Group assignment for each module

$D[] = 'exp_module_member_groups';

$Q[] = "CREATE TABLE exp_module_member_groups (
  group_id smallint(4) unsigned NOT NULL,
  module_id mediumint(5) unsigned NOT NULL,
  KEY (group_id)
)";


// Template Group access privs
// Member group assignment for each template group

$D[] = 'exp_template_member_groups';

$Q[] = "CREATE TABLE exp_template_member_groups (
  group_id smallint(4) unsigned NOT NULL,
  template_group_id mediumint(5) unsigned NOT NULL,
  KEY (group_id)
)";


// Member Custom Fields
// Stores the defenition of each field

$D[] = 'exp_member_fields';

$Q[] = "CREATE TABLE exp_member_fields (
 m_field_id int(4) unsigned NOT NULL auto_increment,
 m_field_name varchar(32) NOT NULL,
 m_field_label varchar(50) NOT NULL,
 m_field_description text NOT NULL, 
 m_field_type varchar(12) NOT NULL default 'text',
 m_field_list_items text NOT NULL,
 m_field_ta_rows tinyint(2) default '8',
 m_field_maxl smallint(3) NOT NULL,
 m_field_width varchar(6) NOT NULL,
 m_field_search char(1) NOT NULL default 'y',
 m_field_required char(1) NOT NULL default 'n',
 m_field_public char(1) NOT NULL default 'y',
 m_field_reg char(1) NOT NULL default 'n',
 m_field_fmt char(5) NOT NULL default 'none',
 m_field_order int(3) unsigned NOT NULL,
 PRIMARY KEY (m_field_id)
)";

// Member Data
// Stores the actual data

$D[] = 'exp_member_data';

$Q[] = "CREATE TABLE exp_member_data (
 member_id int(10) unsigned NOT NULL,
 KEY (member_id)
)";

// Weblog Table

$D[] = 'exp_weblogs';

// Note: The is_user_blog field indicates whether the blog is
// assigned as a "user blogs" weblog

$Q[] = "CREATE TABLE exp_weblogs (
 weblog_id int(6) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 is_user_blog char(1) NOT NULL default 'n',
 blog_name varchar(40) NOT NULL,
 blog_title varchar(100) NOT NULL,
 blog_url varchar(100) NOT NULL,
 blog_description varchar(225) NOT NULL,
 blog_lang varchar(12) NOT NULL,
 blog_encoding varchar(12) NOT NULL,
 total_entries mediumint(8) default '0' NOT NULL,
 total_comments mediumint(8) default '0' NOT NULL,
 total_trackbacks mediumint(8) default '0' NOT NULL,
 last_entry_date int(10) unsigned default '0' NOT NULL,
 last_comment_date int(10) unsigned default '0' NOT NULL,
 last_trackback_date int(10) unsigned default '0' NOT NULL,
 cat_group varchar(255) NOT NULL, 
 status_group int(4) unsigned NOT NULL,
 deft_status varchar(50) NOT NULL default 'open',
 field_group int(4) unsigned NOT NULL,
 search_excerpt int(4) unsigned NOT NULL,
 enable_trackbacks char(1) NOT NULL default 'n',
 trackback_use_url_title char(1) NOT NULL default 'n',
 trackback_max_hits int(2) unsigned NOT NULL default '5', 
 trackback_field int(4) unsigned NOT NULL,
 deft_category varchar(60) NOT NULL,
 deft_comments char(1) NOT NULL default 'y',
 deft_trackbacks char(1) NOT NULL default 'y',
 weblog_require_membership char(1) NOT NULL default 'y',
 weblog_max_chars int(5) unsigned NOT NULL,
 weblog_html_formatting char(4) NOT NULL default 'all',
 weblog_allow_img_urls char(1) NOT NULL default 'y',
 weblog_auto_link_urls char(1) NOT NULL default 'y', 
 weblog_notify char(1) NOT NULL default 'n',
 weblog_notify_emails varchar(255) NOT NULL,
 comment_url varchar(80) NOT NULL,
 comment_system_enabled char(1) NOT NULL default 'y',
 comment_require_membership char(1) NOT NULL default 'n',
 comment_use_captcha char(1) NOT NULL default 'n',
 comment_moderate char(1) NOT NULL default 'n',
 comment_max_chars int(5) unsigned NOT NULL,
 comment_timelock int(5) unsigned NOT NULL default '0',
 comment_require_email char(1) NOT NULL default 'y',
 comment_text_formatting char(5) NOT NULL default 'xhtml',
 comment_html_formatting char(4) NOT NULL default 'safe',
 comment_allow_img_urls char(1) NOT NULL default 'n',
 comment_auto_link_urls char(1) NOT NULL default 'y',
 comment_notify char(1) NOT NULL default 'n',
 comment_notify_authors char(1) NOT NULL default 'n',
 comment_notify_emails varchar(255) NOT NULL,
 comment_expiration int(4) unsigned NOT NULL default '0',
 search_results_url varchar(80) NOT NULL,
 tb_return_url varchar(80) NOT NULL,
 ping_return_url varchar(80) NOT NULL, 
 show_url_title char(1) NOT NULL default 'y',
 trackback_system_enabled char(1) NOT NULL default 'n',
 show_trackback_field char(1) NOT NULL default 'y',
 trackback_use_captcha char(1) NOT NULL default 'n',
 show_ping_cluster char(1) NOT NULL default 'y',
 show_options_cluster char(1) NOT NULL default 'y',
 show_button_cluster char(1) NOT NULL default 'y',
 show_forum_cluster char(1) NOT NULL default 'y',
 show_pages_cluster CHAR(1) NOT NULL DEFAULT 'y',
 show_show_all_cluster CHAR(1) NOT NULL DEFAULT 'y',
 show_author_menu char(1) NOT NULL default 'y',
 show_status_menu char(1) NOT NULL default 'y',
 show_categories_menu char(1) NOT NULL default 'y',
 show_date_menu char(1) NOT NULL default 'y', 
 rss_url varchar(80) NOT NULL,
 enable_versioning char(1) NOT NULL default 'n',
 enable_qucksave_versioning char(1) NOT NULL default 'n',
 max_revisions smallint(4) unsigned NOT NULL default 10,
 default_entry_title varchar(100) NOT NULL,
 url_title_prefix varchar(80) NOT NULL,
 live_look_template int(10) UNSIGNED NOT NULL default 0,
 PRIMARY KEY (weblog_id),
 KEY (cat_group),
 KEY (status_group),
 KEY (field_group),
 KEY (is_user_blog),
 KEY (site_id)
)";

// Weblog Titles
// We store weblog titles separately from weblog data

$D[] = 'exp_weblog_titles';

$Q[] = "CREATE TABLE exp_weblog_titles (
 entry_id int(10) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 weblog_id int(4) unsigned NOT NULL,
 author_id int(10) unsigned NOT NULL default '0',
 pentry_id int(10) NOT NULL default '0',
 forum_topic_id int(10) unsigned NOT NULL,
 ip_address varchar(16) NOT NULL,
 title varchar(100) NOT NULL,
 url_title varchar(75) NOT NULL,
 status varchar(50) NOT NULL,
 versioning_enabled char(1) NOT NULL default 'n',
 view_count_one int(10) unsigned NOT NULL default '0',
 view_count_two int(10) unsigned NOT NULL default '0',
 view_count_three int(10) unsigned NOT NULL default '0',
 view_count_four int(10) unsigned NOT NULL default '0',
 allow_comments varchar(1) NOT NULL default 'y',
 allow_trackbacks varchar(1) NOT NULL default 'n',
 sticky varchar(1) NOT NULL default 'n',
 entry_date int(10) NOT NULL,
 dst_enabled varchar(1) NOT NULL default 'n',
 year char(4) NOT NULL,
 month char(2) NOT NULL,
 day char(3) NOT NULL,
 expiration_date int(10) NOT NULL default '0',
 comment_expiration_date int(10) NOT NULL default '0',
 edit_date bigint(14),
 recent_comment_date int(10) NOT NULL,
 comment_total int(4) unsigned NOT NULL default '0',
 trackback_total int(4) unsigned NOT NULL default '0',
 sent_trackbacks text NOT NULL,
 recent_trackback_date int(10) NOT NULL,
 PRIMARY KEY (entry_id),
 KEY (weblog_id),
 KEY (author_id),
 KEY (url_title),
 KEY (status),
 KEY (entry_date),
 KEY (expiration_date),
 KEY (site_id)
)";


$D[] = 'exp_entry_versioning';

$Q[] = "CREATE TABLE exp_entry_versioning (
 version_id int(10) unsigned NOT NULL auto_increment,  
 entry_id int(10) unsigned NOT NULL,
 weblog_id int(4) unsigned NOT NULL,
 author_id int(10) unsigned NOT NULL,
 version_date int(10) NOT NULL,
 version_data mediumtext NOT NULL,
 PRIMARY KEY (version_id),
 KEY (entry_id)
)";


// Weblog Custom Field Groups

$D[] = 'exp_field_groups';

$Q[] = "CREATE TABLE exp_field_groups (
 group_id int(4) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 group_name varchar(50) NOT NULL,
 PRIMARY KEY (group_id),
 KEY (site_id)
)"; 

// Weblog Custom Field Definitions

$D[] = 'exp_weblog_fields';

$Q[] = "CREATE TABLE exp_weblog_fields (
 field_id int(6) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 group_id int(4) unsigned NOT NULL, 
 field_name varchar(32) NOT NULL,
 field_label varchar(50) NOT NULL,
 field_instructions TEXT NOT NULL,
 field_type varchar(12) NOT NULL default 'text',
 field_list_items text NOT NULL,
 field_pre_populate char(1) NOT NULL default 'n', 
 field_pre_blog_id int(6) unsigned NOT NULL,
 field_pre_field_id int(6) unsigned NOT NULL,
 field_related_to varchar(12) NOT NULL default 'blog',
 field_related_id int(6) unsigned NOT NULL,
 field_related_orderby varchar(12) NOT NULL default 'date',
 field_related_sort varchar(4) NOT NULL default 'desc',
 field_related_max smallint(4) NOT NULL,
 field_ta_rows tinyint(2) default '8',
 field_maxl smallint(3) NOT NULL,
 field_required char(1) NOT NULL default 'n',
 field_text_direction CHAR(3) NOT NULL default 'ltr',
 field_search char(1) NOT NULL default 'n',
 field_is_hidden char(1) NOT NULL default 'n',
 field_fmt varchar(40) NOT NULL default 'xhtml',
 field_show_fmt char(1) NOT NULL default 'y',
 field_order int(3) unsigned NOT NULL,
 PRIMARY KEY (field_id),
 KEY (group_id),
 KEY (site_id)
)";


// Relationships table

$D[] = 'exp_relationships';

$Q[] = "CREATE TABLE exp_relationships (
 rel_id int(6) unsigned NOT NULL auto_increment,
 rel_parent_id int(10) NOT NULL default '0',
 rel_child_id int(10) NOT NULL default '0',
 rel_type varchar(12) NOT NULL,
 rel_data mediumtext NOT NULL,
 reverse_rel_data mediumtext NOT NULL,
 PRIMARY KEY (rel_id),
 KEY (rel_parent_id),
 KEY (rel_child_id)
)";


// Field formatting definitions

$D[] = 'exp_field_formatting';

$Q[] = "CREATE TABLE exp_field_formatting (
 field_id int(10) unsigned NOT NULL,
 field_fmt varchar(40) NOT NULL,
 KEY (field_id)
)";


// Weblog data

$D[] = 'exp_weblog_data';

$Q[] = "CREATE TABLE exp_weblog_data (
 entry_id int(10) unsigned NOT NULL,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 weblog_id int(4) unsigned NOT NULL,
 field_id_1 text NOT NULL,
 field_ft_1 tinytext NULL,
 field_id_2 text NOT NULL,
 field_ft_2 tinytext NULL,
 field_id_3 text NOT NULL,
 field_ft_3 tinytext NULL,
 KEY (entry_id),
 KEY (weblog_id),
 KEY (site_id)
)";


// Ping Status
// This table saves the status of the xml-rpc ping buttons
// that were selected when an entry was submitted.  This
// enables us to set the buttons to the same state when editing

$D[] = 'exp_entry_ping_status';

$Q[] = "CREATE TABLE exp_entry_ping_status (
 entry_id int(10) unsigned NOT NULL,
 ping_id int(10) unsigned NOT NULL
)";

// Comment table

$D[] = 'exp_comments';

$Q[] = "CREATE TABLE exp_comments (
 comment_id int(10) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 entry_id int(10) unsigned NOT NULL default '0',
 weblog_id int(4) unsigned NOT NULL,
 author_id int(10) unsigned NOT NULL default '0',
 status char(1) NOT NULL default 'o',
 name varchar(50) NOT NULL,
 email varchar(50) NOT NULL,
 url varchar(75) NOT NULL,
 location varchar(50) NOT NULL, 
 ip_address varchar(16) NOT NULL,
 comment_date int(10) NOT NULL,
 edit_date timestamp(14),
 comment text NOT NULL,
 notify char(1) NOT NULL default 'n',
 PRIMARY KEY (comment_id),
 KEY (entry_id),
 KEY (weblog_id),
 KEY (author_id),
 KEY (status),
 KEY (site_id)
)";

// Trackback table.

$D[] = 'exp_trackbacks';

$Q[] = "CREATE TABLE exp_trackbacks (
 trackback_id int(10) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 entry_id int(10) unsigned NOT NULL default '0',
 weblog_id int(4) unsigned NOT NULL,
 title varchar(100) NOT NULL,
 content text NOT NULL,
 weblog_name varchar(100) NOT NULL,
 trackback_url varchar(200) NOT NULL,
 trackback_date int(10) NOT NULL,
 trackback_ip varchar(16) NOT NULL,
 PRIMARY KEY (trackback_id),
 KEY (entry_id),
 KEY (weblog_id),
 KEY (site_id)
)";


// Status Groups

$D[] = 'exp_status_groups';

$Q[] = "CREATE TABLE exp_status_groups (
 group_id int(4) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 group_name varchar(50) NOT NULL,
 PRIMARY KEY (group_id),
 KEY (site_id)
)"; 

// Status data

$D[] = 'exp_statuses';

$Q[] = "CREATE TABLE exp_statuses (
 status_id int(6) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 group_id int(4) unsigned NOT NULL,
 status varchar(50) NOT NULL,
 status_order int(3) unsigned NOT NULL,
 highlight varchar(30) NOT NULL,
 PRIMARY KEY (status_id),
 KEY (group_id),
 KEY (site_id)
)"; 

// Status "no access" 
// Stores groups that can not access certain statuses

$D[] = 'exp_status_no_access';

$Q[] = "CREATE TABLE exp_status_no_access (
 status_id int(6) unsigned NOT NULL,
 member_group smallint(4) unsigned NOT NULL
)";



// Category Groups
// Note: The is_user_blog field indicates whether the blog is
// assigned as a "user blogs" weblog

$D[] = 'exp_category_groups';

$Q[] = "CREATE TABLE exp_category_groups (
 group_id int(6) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 group_name varchar(50) NOT NULL,
 sort_order char(1) NOT NULL default 'a',
 `field_html_formatting` char(4) NOT NULL default 'all',
 `can_edit_categories` TEXT NOT NULL,
 `can_delete_categories` TEXT NOT NULL,
 is_user_blog char(1) NOT NULL default 'n',
 PRIMARY KEY (group_id),
 KEY (site_id)
)"; 

// Category data

$D[] = 'exp_categories';

$Q[] = "CREATE TABLE exp_categories (
 cat_id int(10) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 group_id int(6) unsigned NOT NULL,
 parent_id int(4) unsigned NOT NULL,
 cat_name varchar(100) NOT NULL,
 `cat_url_title` varchar(75) NOT NULL,
 cat_description text NOT NULL,
 cat_image varchar(120) NOT NULL,
 cat_order int(4) unsigned NOT NULL,
 PRIMARY KEY (cat_id),
 KEY (group_id),
 KEY (cat_name),
 KEY (site_id)
)"; 


$D[] = 'exp_category_fields';

$Q[] = "CREATE TABLE `exp_category_fields` (
		`field_id` int(6) unsigned NOT NULL auto_increment,
		`site_id` int(4) unsigned NOT NULL default 1,
		`group_id` int(4) unsigned NOT NULL,
		`field_name` varchar(32) NOT NULL default '',
		`field_label` varchar(50) NOT NULL default '',
		`field_type` varchar(12) NOT NULL default 'text',
		`field_list_items` text NOT NULL,
		`field_maxl` smallint(3) NOT NULL default 128,
		`field_ta_rows` tinyint(2) NOT NULL default 8,
		`field_default_fmt` varchar(40) NOT NULL default 'none',
		`field_show_fmt` char(1) NOT NULL default 'y',
		`field_text_direction` CHAR(3) NOT NULL default 'ltr',
		`field_required` char(1) NOT NULL default 'n',
		`field_order` int(3) unsigned NOT NULL,
		PRIMARY KEY (`field_id`),
		KEY `site_id` (`site_id`),
		KEY `group_id` (`group_id`)
		)";
		
$D[] = 'exp_category_field_data';
		
$Q[] = "CREATE TABLE `exp_category_field_data` (
		`cat_id` int(4) unsigned NOT NULL,
		`site_id` int(4) unsigned NOT NULL default 1,
		`group_id` int(4) unsigned NOT NULL,
		PRIMARY KEY (`cat_id`),
		KEY `site_id` (`site_id`),
		KEY `group_id` (`group_id`)				
		)";


// Category posts
// This table stores the weblog entry ID and the category IDs
// that are assigned to it

$D[] = 'exp_category_posts';

$Q[] = "CREATE TABLE exp_category_posts (
 entry_id int(10) unsigned NOT NULL,
 cat_id int(10) unsigned NOT NULL,
 KEY (entry_id),
 KEY (cat_id)
)"; 

// Control panel log

$D[] = 'exp_cp_log';

$Q[] = "CREATE TABLE exp_cp_log (
  id int(10) NOT NULL auto_increment,
  site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
  member_id int(10) unsigned NOT NULL,
  username varchar(32) NOT NULL,
  ip_address varchar(16) default '0' NOT NULL,
  act_date int(10) NOT NULL,
  action varchar(200) NOT NULL,
  PRIMARY KEY  (id),
  KEY (site_id)
)"; 

// HTML buttons
// These are the buttons that appear on the PUBLISH page.
// Each member can have their own set of buttons

$D[] = 'exp_html_buttons';

$Q[] = "CREATE TABLE exp_html_buttons (
  id int(10) unsigned NOT NULL auto_increment,
  site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
  member_id int(10) default '0' NOT NULL,
  tag_name varchar(32) NOT NULL,
  tag_open varchar(120) NOT NULL,
  tag_close varchar(120) NOT NULL,
  accesskey varchar(32) NOT NULL,
  tag_order int(3) unsigned NOT NULL,
  tag_row char(1) NOT NULL default '1',
  PRIMARY KEY (id),
  KEY (site_id)
)";


// Ping Servers
// Each member can have their own set ping server definitions

$D[] = 'exp_ping_servers';

$Q[] = "CREATE TABLE exp_ping_servers (
  id int(10) unsigned NOT NULL auto_increment,
  site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
  member_id int(10) default '0' NOT NULL,
  server_name varchar(32) NOT NULL,
  server_url varchar(150) NOT NULL,
  port varchar(4) NOT NULL default '80',
  ping_protocol varchar(12) NOT NULL default 'xmlrpc',
  is_default char(1) NOT NULL default 'y',
  server_order int(3) unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY (site_id)
)";


// Template Groups
// Note:  The 'is_user_blog' field is used to indicate
// whether a template group has been assigned to a
// specific user as part of the "user blogs" module

$D[] = 'exp_template_groups';

$Q[] = "CREATE TABLE exp_template_groups (
 group_id int(6) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 group_name varchar(50) NOT NULL,
 group_order int(3) unsigned NOT NULL,
 is_site_default char(1) NOT NULL default 'n',
 is_user_blog char(1) NOT NULL default 'n',
 PRIMARY KEY (group_id),
 KEY (site_id)
)";

// Template data

$D[] = 'exp_templates';

$Q[] = "CREATE TABLE exp_templates (
 template_id int(10) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 group_id int(6) unsigned NOT NULL,
 template_name varchar(50) NOT NULL,
 save_template_file char(1) NOT NULL default 'n',
 template_type varchar(16) NOT NULL default 'webpage',
 template_data mediumtext NOT NULL,
 template_notes text NOT NULL,
 edit_date int(10) NOT NULL DEFAULT 0,
 last_author_id int(10) UNSIGNED NOT NULL,
 cache char(1) NOT NULL default 'n',
 refresh int(6) unsigned NOT NULL,
 no_auth_bounce varchar(50) NOT NULL,
 enable_http_auth CHAR(1) NOT NULL default 'n',
 allow_php char(1) NOT NULL default 'n',
 php_parse_location char(1) NOT NULL default 'o',
 hits int(10) unsigned NOT NULL,
 PRIMARY KEY (template_id),
 KEY (group_id),
 KEY (site_id)
)"; 

// Template "no access"
// Since each template can be made private to specific member groups
// we store member IDs of people who can not access certain templates

$D[] = 'exp_template_no_access';

$Q[] = "CREATE TABLE exp_template_no_access (
 template_id int(6) unsigned NOT NULL,
 member_group smallint(4) unsigned NOT NULL,
 KEY (`template_id`)
)";

// Specialty Templates
// This table contains the various specialty templates, like:
// Admin notification of new members
// Admin notification of comments and trackbacks
// Membership activation instruction
// Member lost password instructions
// Validated member notification
// Remove from mailinglist notification

$D[] = 'exp_specialty_templates';

$Q[] = "CREATE TABLE exp_specialty_templates (
 template_id int(6) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 enable_template char(1) NOT NULL default 'y',
 template_name varchar(50) NOT NULL,
 data_title varchar(80) NOT NULL,
 template_data text NOT NULL,
 PRIMARY KEY (template_id),
 KEY (template_name),
 KEY (site_id)
)"; 

// Global variables
// These are user-definable variables

$D[] = 'exp_global_variables';

$Q[] = "CREATE TABLE exp_global_variables (
 variable_id int(6) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 variable_name varchar(50) NOT NULL,
 variable_data text NOT NULL,
 user_blog_id int(6) NOT NULL default '0',
 PRIMARY KEY (variable_id),
 KEY (variable_name),
 KEY (site_id)
)";

// Revision tracker
// This is our versioning table, used to store each
// change that is made to a template.

$D[] = 'exp_revision_tracker';

$Q[] = "CREATE TABLE exp_revision_tracker (
 tracker_id int(10) unsigned NOT NULL auto_increment,  
 item_id int(10) unsigned NOT NULL,
 item_table varchar(20) NOT NULL,
 item_field varchar(20) NOT NULL,
 item_date int(10) NOT NULL,
 item_author_id int(10) UNSIGNED NOT NULL,
 item_data mediumtext NOT NULL,
 PRIMARY KEY (tracker_id),
 KEY (item_id)
)";


// Upload preferences

// Note: The is_user_blog field indicates whether the blog is
// assigned as a "user blogs" weblog

$D[] = 'exp_upload_prefs';

$Q[] = "CREATE TABLE exp_upload_prefs (
 id int(4) unsigned NOT NULL auto_increment,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 is_user_blog char(1) NOT NULL default 'n',
 name varchar(50) NOT NULL,
 server_path varchar(100) NOT NULL,
 url varchar(100) NOT NULL,
 allowed_types varchar(3) NOT NULL default 'img',
 max_size varchar(16) NOT NULL,
 max_height varchar(6) NOT NULL,
 max_width varchar(6) NOT NULL,
 properties varchar(120) NOT NULL,
 pre_format varchar(120) NOT NULL,
 post_format varchar(120) NOT NULL,
 file_properties varchar(120) NOT NULL,
 file_pre_format varchar(120) NOT NULL,
 file_post_format varchar(120) NOT NULL,
 PRIMARY KEY (id),
 KEY (site_id)
)";

// Upload "no access"
// We store the member groups that can not access various upload destinations

$D[] = 'exp_upload_no_access';

$Q[] = "CREATE TABLE exp_upload_no_access (
 upload_id int(6) unsigned NOT NULL,
 upload_loc varchar(3) NOT NULL,
 member_group smallint(4) unsigned NOT NULL
)";


// Search results

$D[] = 'exp_search';

$Q[] = "CREATE TABLE exp_search (
 search_id varchar(32) NOT NULL,
 site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
 search_date int(10) NOT NULL,
 keywords varchar(60) NOT NULL,
 member_id int(10) unsigned NOT NULL,
 ip_address varchar(16) NOT NULL,
 total_results int(6) NOT NULL,
 per_page smallint(3) unsigned NOT NULL,
 query mediumtext NULL DEFAULT NULL,
 custom_fields mediumtext NULL DEFAULT NULL,
 result_page varchar(70) NOT NULL,
 PRIMARY KEY (search_id),
 KEY (site_id)
)";

// Search term log

$D[] = 'exp_search_log';

$Q[] = "CREATE TABLE exp_search_log (
  id int(10) NOT NULL auto_increment,
  site_id INT(4) UNSIGNED NOT NULL DEFAULT 1,
  member_id int(10) unsigned NOT NULL,
  screen_name varchar(50) NOT NULL,
  ip_address varchar(16) default '0' NOT NULL,
  search_date int(10) NOT NULL,
  search_type varchar(32) NOT NULL,
  search_terms varchar(200) NOT NULL,
  PRIMARY KEY (id),
  KEY (site_id)
)"; 

// Private messating tables

$D[] = 'exp_message_attachments';

$Q[] = "CREATE TABLE exp_message_attachments (
  attachment_id int(10) unsigned NOT NULL auto_increment,
  sender_id int(10) unsigned NOT NULL default '0',
  message_id int(10) unsigned NOT NULL default '0',
  attachment_name varchar(50) NOT NULL default '',
  attachment_hash varchar(40) NOT NULL default '',
  attachment_extension varchar(20) NOT NULL default '',
  attachment_location varchar(125) NOT NULL default '',
  attachment_date int(10) unsigned NOT NULL default '0',
  attachment_size int(10) unsigned NOT NULL default '0',
  is_temp char(1) NOT NULL default 'y',
  PRIMARY KEY (attachment_id)
)";

$D[] = 'exp_message_copies';

$Q[] = "CREATE TABLE exp_message_copies (
  copy_id int(10) unsigned NOT NULL auto_increment,
  message_id int(10) unsigned NOT NULL default '0',
  sender_id int(10) unsigned NOT NULL default '0',
  recipient_id int(10) unsigned NOT NULL default '0',
  message_received char(1) NOT NULL default 'n',
  message_read char(1) NOT NULL default 'n',
  message_time_read int(10) unsigned NOT NULL default '0',
  attachment_downloaded char(1) NOT NULL default 'n',
  message_folder int(10) unsigned NOT NULL default '1',
  message_authcode varchar(10) NOT NULL default '',
  message_deleted char(1) NOT NULL default 'n',
  message_status varchar(10) NOT NULL default '',
  PRIMARY KEY  (copy_id),
  KEY message_id (message_id),
  KEY recipient_id (recipient_id),
  KEY sender_id (sender_id)
)";

$D[] = 'exp_message_data';

$Q[] = "CREATE TABLE exp_message_data (
  message_id int(10) unsigned NOT NULL auto_increment,
  sender_id int(10) unsigned NOT NULL default '0',
  message_date int(10) unsigned NOT NULL default '0',
  message_subject varchar(255) NOT NULL default '',
  message_body text NOT NULL,
  message_tracking char(1) NOT NULL default 'y',
  message_attachments char(1) NOT NULL default 'n',
  message_recipients varchar(200) NOT NULL default '',
  message_cc varchar(200) NOT NULL default '',
  message_hide_cc char(1) NOT NULL default 'n',
  message_sent_copy char(1) NOT NULL default 'n',
  total_recipients int(5) unsigned NOT NULL default '0',
  message_status varchar(25) NOT NULL default '',
  PRIMARY KEY  (message_id),
  KEY sender_id (sender_id)
)";


$D[] = 'exp_message_folders';

$Q[] = "CREATE TABLE exp_message_folders (
  member_id int(10) unsigned NOT NULL default '0',
  folder1_name varchar(50) NOT NULL default 'InBox',
  folder2_name varchar(50) NOT NULL default 'Sent',
  folder3_name varchar(50) NOT NULL default '',
  folder4_name varchar(50) NOT NULL default '',
  folder5_name varchar(50) NOT NULL default '',
  folder6_name varchar(50) NOT NULL default '',
  folder7_name varchar(50) NOT NULL default '',
  folder8_name varchar(50) NOT NULL default '',
  folder9_name varchar(50) NOT NULL default '',
  folder10_name varchar(50) NOT NULL default '',
  KEY member_id (member_id)
)";

$D[] = 'exp_message_listed';

$Q[] = "CREATE TABLE exp_message_listed (
  listed_id int(10) unsigned NOT NULL auto_increment,
  member_id int(10) unsigned NOT NULL default '0',
  listed_member int(10) unsigned NOT NULL default '0',
  listed_description varchar(100) NOT NULL default '',
  listed_type varchar(10) NOT NULL default 'blocked',
  PRIMARY KEY (listed_id)
)";

$D[] = 'exp_extensions';

$Q[] = "CREATE TABLE `exp_extensions` (
	`extension_id` int(10) unsigned NOT NULL auto_increment,
	`class` varchar(50) NOT NULL default '',
	`method` varchar(50) NOT NULL default '',
	`hook` varchar(50) NOT NULL default '',
	`settings` text NOT NULL,
	`priority` int(2) NOT NULL default '10',
	`version` varchar(10) NOT NULL default '',
	`enabled` char(1) NOT NULL default 'y',
	PRIMARY KEY (`extension_id`)
)";


$D[] = 'exp_member_search';

$Q[] = "CREATE TABLE `exp_member_search` 
		 (
			 `search_id` varchar(32) NOT NULL,
			 `site_id` INT(4) UNSIGNED NOT NULL DEFAULT 1,
			 `search_date` int(10) unsigned NOT NULL,
			 `keywords` varchar(200) NOT NULL,
			 `fields` varchar(200) NOT NULL,
			 `member_id` int(10) unsigned NOT NULL,
			 `ip_address` varchar(16) NOT NULL,
			 `total_results` int(8) unsigned NOT NULL,
			 `query` text NOT NULL,
			 PRIMARY KEY  (`search_id`),
			 KEY `member_id` (`member_id`),
			 KEY `site_id` (`site_id`)
		 )";
		 
$D[] = 'exp_member_bulletin_board';
		 
$Q[] =	"CREATE TABLE `exp_member_bulletin_board`
		(
			`bulletin_id` int(10) unsigned NOT NULL auto_increment,
			`sender_id` int(10) unsigned NOT NULL,
			`bulletin_group` int(8) unsigned NOT NULL,
			`bulletin_date` int(10) unsigned NOT NULL,
			`hash` varchar(10) NOT NULL DEFAULT '',
			`bulletin_expires` int(10) unsigned NOT NULL DEFAULT 0,
			`bulletin_message` text NOT NULL,
			PRIMARY KEY  (`bulletin_id`),
			KEY `sender_id` (`sender_id`),
			KEY `hash` (`hash`)
		)";


//  Define default DB data
// --------------------------------------------------------------------
// --------------------------------------------------------------------

// Which version is being installed?
// This lets us conditionally add only the supported
// items to the templates.
$type = 'core';
if ($fp = @opendir($system_path.'modules/')) 
{ 
	while (false !== ($file = readdir($fp))) 
	{
		if ( ! stristr($file, '.'))
		{
			if ($file == 'mailinglist')
			{
				$type = 'full';
				break;
			}
		}
	} 
	closedir($fp); 
} 


if ( ! @include_once('./themes/site_themes/'.$data['template'].'/'.$data['template'].'.php'))
{
	$er =  "<div class='error'>Error: Unable to load the theme you have selected.  Please ensure the theme file's permissions are such that they are readable.</div>";
	settings_form($er);
	page_footer();
	exit;
}

// Template data

$Q[] = "insert into exp_template_groups(group_id, group_name, group_order, is_site_default) values ('', 'site',  '1', 'y')";

foreach ($template_matrix as $tmpl)
{
	$func = $tmpl['0'];
	
	// This allows old templates to be compatible
	$temp = str_replace("{stylesheet=weblog/weblog_css}", "{stylesheet=site/site_css}", $func());	
	foreach (array('index', 'comments', 'comment_preview', 'trackbacks', 'categories', 'archives', 'rss_1.0', 'rss_2.0', 'rss_atom', 'referrers', 'smileys') as $val)
	{
		$temp = str_replace("weblog/".$val, "site/".$val, $temp);		
	}
	$temp = str_replace('weblog1', 'default_site', $temp);
	
	if ($func == 'weblog_css')
		$func = 'site_css';
		
	// --------------------
	
	$Q[] = "insert into exp_templates(template_id, group_id, template_name, template_type, template_data, edit_date, last_author_id) values ('', '1', '".$func."', '".$tmpl['1']."', '".addslashes($temp)."', {$now}, 1)";
}
unset($template_matrix);

require './themes/site_themes/rss/rss.php';			

	// This allows old templates to be compatible
	$temp = str_replace("{stylesheet=weblog/weblog_css}", "{stylesheet=site/site_css}", rss_2());	
	foreach (array('index', 'comments', 'comment_preview', 'trackbacks', 'categories', 'archives', 'rss_1.0', 'rss_2.0', 'rss_atom', 'referrers', 'smileys') as $val)
	{
		$temp = str_replace("weblog/".$val, "site/".$val, $temp);}
		$temp = str_replace('weblog1', 'default_site', $temp);


$Q[] = "insert into exp_templates(template_id, group_id, template_name, template_type, template_data, edit_date, last_author_id) values ('', '1', 'rss_2.0', 'rss', '".addslashes($temp)."', {$now}, 1)";

	// This allows old templates to be compatible
	$temp = str_replace("{stylesheet=weblog/weblog_css}", "{stylesheet=site/site_css}", atom());	
	foreach (array('index', 'comments', 'comment_preview', 'trackbacks', 'categories', 'archives', 'rss_1.0', 'rss_2.0', 'rss_atom', 'referrers', 'smileys') as $val)
	{
	$temp = str_replace("weblog/".$val, "site/".$val, $temp);}
	$temp = str_replace('weblog1', 'default_site', $temp);

$Q[] = "insert into exp_templates(template_id, group_id, template_name, template_type, template_data, edit_date, last_author_id) values ('', '1', 'atom', 'rss', '".addslashes($temp)."', {$now}, 1)";


$Q[] = "insert into exp_template_groups(group_id, group_name, group_order) values ('', 'search', '3')";

unset($template_matrix);
require './themes/site_themes/search/search.php';			

foreach ($template_matrix as $tmpl)
{
	$name = ($tmpl['0'] == 'search_index') ? 'index' : $tmpl['0'];
	$func = $tmpl['0'];
	
	// This allows old templates to be compatible
	$temp = str_replace("{stylesheet=weblog/weblog_css}", "{stylesheet=site/site_css}", $func());	
	foreach (array('index', 'comments', 'comment_preview', 'trackbacks', 'categories', 'archives', 'rss_1.0', 'rss_2.0', 'rss_atom', 'referrers', 'smileys') as $val)
	{
	$temp = str_replace("weblog/".$val, "site/".$val, $temp);}
	$temp = str_replace('weblog1', 'default_site', $temp);

	$Q[] = "insert into exp_templates(template_id, group_id, template_name, template_type, template_data, edit_date, last_author_id) values ('', '2', '".$name."', '".$tmpl['1']."', '".addslashes($temp)."', {$now}, 1)";
}

// Default Site

$Q[] = $DB->insert_string('exp_sites',array('site_id' => 1, 'site_label' => $data['site_name'], 'site_name' => 'default_site'));
											

// Specialty templates

$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'offline_template', '', '".addslashes(offline_template())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'message_template', '', '".addslashes(message_template())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'admin_notify_reg', '".addslashes(trim(admin_notify_reg_title()))."', '".addslashes(admin_notify_reg())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'admin_notify_entry', '".addslashes(trim(admin_notify_entry_title()))."', '".addslashes(admin_notify_entry())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'admin_notify_mailinglist', '".addslashes(trim(admin_notify_mailinglist_title()))."', '".addslashes(admin_notify_mailinglist())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'admin_notify_comment', '".addslashes(trim(admin_notify_comment_title()))."', '".addslashes(admin_notify_comment())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'admin_notify_gallery_comment', '".addslashes(trim(admin_notify_gallery_comment_title()))."', '".addslashes(admin_notify_gallery_comment())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'admin_notify_trackback', '".addslashes(trim(admin_notify_trackback_title()))."', '".addslashes(admin_notify_trackback())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'mbr_activation_instructions', '".addslashes(trim(mbr_activation_instructions_title()))."', '".addslashes(mbr_activation_instructions())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'forgot_password_instructions', '".addslashes(trim(forgot_password_instructions_title()))."', '".addslashes(forgot_password_instructions())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'reset_password_notification', '".addslashes(trim(reset_password_notification_title()))."', '".addslashes(reset_password_notification())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'validated_member_notify', '".addslashes(trim(validated_member_notify_title()))."', '".addslashes(validated_member_notify())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'decline_member_validation', '".addslashes(trim(decline_member_validation_title()))."', '".addslashes(decline_member_validation())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'mailinglist_activation_instructions', '".addslashes(trim(mailinglist_activation_instructions_title()))."', '".addslashes(mailinglist_activation_instructions())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'comment_notification', '".addslashes(trim(comment_notification_title()))."', '".addslashes(comment_notification())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'gallery_comment_notification', '".addslashes(trim(gallery_comment_notification_title()))."', '".addslashes(gallery_comment_notification())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'private_message_notification', '".addslashes(trim(private_message_notification_title()))."', '".addslashes(private_message_notification())."')";
$Q[] = "insert into exp_specialty_templates(template_id, template_name, data_title, template_data) values ('', 'pm_inbox_full', '".addslashes(trim(pm_inbox_full_title()))."', '".addslashes(pm_inbox_full())."')";

// Mailing list data

$Q[] = "insert into exp_mailing_lists(list_id, list_name, list_title, list_template) values ('', 'default', 'Default Mailing List', '".addslashes(mailinglist_template())."')";

// Default weblog preference data

$Q[] = "insert into exp_weblogs (weblog_id, cat_group, blog_name, blog_title, blog_url, comment_url, search_results_url, tb_return_url, ping_return_url, blog_lang, blog_encoding, total_entries, last_entry_date, status_group, deft_status, field_group, deft_comments, deft_trackbacks, trackback_field, comment_max_chars, comment_require_email, comment_require_membership, weblog_require_membership, comment_text_formatting, search_excerpt)  values ('', '1', 'default_site', 'Default Site Weblog', '".$data['site_url'].$data['site_index']."/site/index/', '".$data['site_url'].$data['site_index']."/site/comments/', '".$data['site_url'].$data['site_index']."/site/comments/', '".$data['site_url'].$data['site_index']."/site/comments/', '".$data['site_url'].$data['site_index']."', 'en', 'utf-8', '1', '$now', '1', 'open', '1', 'y', 'y', '2', '5000', 'y', 'n', 'y', 'xhtml', '2')";

// Custom field and field group data

$Q[] = "insert into exp_field_groups(group_id, group_name) values ('', 'Default Field Group')";

$Q[] = "insert into exp_weblog_fields(field_id, group_id, field_name, field_label, field_type, field_list_items, field_ta_rows, field_search, field_order, field_is_hidden) values ('', '1', 'summary', 'Summary', 'textarea', '', '6', 'n', '1', 'y')";
$Q[] = "insert into exp_weblog_fields(field_id, group_id, field_name, field_label, field_type, field_list_items, field_ta_rows, field_search, field_order, field_is_hidden) values ('', '1', 'body', 'Body', 'textarea', '', '10', 'y', '2', 'n')";
$Q[] = "insert into exp_weblog_fields(field_id, group_id, field_name, field_label, field_type, field_list_items, field_ta_rows, field_search, field_order, field_is_hidden) values ('', '1', 'extended', 'Extended text', 'textarea', '', '12', 'n', '3', 'y')";

$Q[] = "insert into exp_field_formatting (field_id, field_fmt) values ('1', 'none')";
$Q[] = "insert into exp_field_formatting (field_id, field_fmt) values ('1', 'br')";
$Q[] = "insert into exp_field_formatting (field_id, field_fmt) values ('1', 'xhtml')";

$Q[] = "insert into exp_field_formatting (field_id, field_fmt) values ('2', 'none')";
$Q[] = "insert into exp_field_formatting (field_id, field_fmt) values ('2', 'br')";
$Q[] = "insert into exp_field_formatting (field_id, field_fmt) values ('2', 'xhtml')";

$Q[] = "insert into exp_field_formatting (field_id, field_fmt) values ('3', 'none')";
$Q[] = "insert into exp_field_formatting (field_id, field_fmt) values ('3', 'br')";
$Q[] = "insert into exp_field_formatting (field_id, field_fmt) values ('3', 'xhtml')";


// Custom statuses

$Q[] = "insert into exp_status_groups (group_id, group_name) values ('', 'Default Status Group')";

$Q[] = "insert into exp_statuses (status_id, group_id, status, status_order, highlight) values ('', '1', 'open', '1', '009933')";
$Q[] = "insert into exp_statuses (status_id, group_id, status, status_order, highlight) values ('', '1', 'closed', '2', '990000')";

// Member groups

$Q[] = "insert into exp_member_groups values ('1', 1, 'Super Admins', '', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', 'y', '', 'y', 'y', 'y', '0',  'y', '20', '60', 'y', 'y', 'y', 'y', 'y')";
$Q[] = "insert into exp_member_groups values ('2', 1, 'Banned',       '', 'y', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', '', 'n', 'n', 'n', '60', 'n', '20', '60', 'n', 'n', 'n', 'n', 'n')";
$Q[] = "insert into exp_member_groups values ('3', 1, 'Guests',       '', 'y', 'n', 'y', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'y', 'n', '', 'y', 'n', 'y', '15', 'n', '20', '60', 'n', 'n', 'n', 'n', 'n')";
$Q[] = "insert into exp_member_groups values ('4', 1, 'Pending',      '', 'y', 'n', 'y', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'y', 'n', '', 'y', 'n', 'y', '15', 'n', '20', '60', 'n', 'n', 'n', 'n', 'n')";
$Q[] = "insert into exp_member_groups values ('5', 1, 'Members',      '', 'y', 'n', 'y', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'n', 'y', 'y', 'n', '', 'y', 'n', 'y', '10', 'y', '20', '60', 'y', 'n', 'n', 'y', 'y')";

// Register the default admin

$quick_link = 'My Site|'.$data['site_url'].$data['site_index'].'|1';

$Q[] = "insert into exp_members (member_id, group_id, username, password, unique_id, email, screen_name, join_date, ip_address, timezone, daylight_savings, total_entries, last_entry_date, quick_links, language) values ('', '1', '".$DB->escape_str($data['username'])."', '".$password."', '".$unique_id."', '".$DB->escape_str($data['email'])."', '".$DB->escape_str($data['screen_name'])."', '".$now."', '".$data['ip']."', '".$data['server_timezone']."', '".$data['daylight_savings']."', '1', '".$now."', '$quick_link', '".$DB->escape_str($data['deft_lang'])."')";
$Q[] = "insert into exp_member_homepage (member_id, recent_entries_order, recent_comments_order, site_statistics_order, notepad_order, pmachine_news_feed) values ('1', '1', '2', '1', '2', 'l')";
$Q[] = "insert into exp_member_data (member_id) VALUES ('1')";


// Default system stats

$Q[] = "insert into exp_stats (total_members, total_entries, last_entry_date, recent_member, recent_member_id, last_cache_clear) values ('1', '1', '".$now."', '".$DB->escape_str($data['screen_name'])."', '1', '".$now."')";

// HTML formatting buttons

$Q[] = "insert into exp_html_buttons values ('', 1, '0', '<b>', '<b>', '</b>', 'b', '1', '1')";
$Q[] = "insert into exp_html_buttons values ('', 1, '0', '<bq>', '<blockquote>', '</blockquote>', 'q', '2', '1')";
$Q[] = "insert into exp_html_buttons values ('', 1, '0', '<del>', '<del>', '</del>', 'd', '3', '1')";
$Q[] = "insert into exp_html_buttons values ('', 1, '0', '<i>', '<i>', '</i>', 'i', '4', '1')";

// Ping servers

//$Q[] = "insert into exp_ping_servers values ('', '0', 'weblogs.com', 'http://rpc.weblogs.com/RPC2', '80', 'xmmlrpc', 'n', '1')";
//$Q[] = "insert into exp_ping_servers values ('', '0', 'blo.gs', 'http://ping.blo.gs/', '80', 'xmmlrpc', 'n', '2')";
//$Q[] = "insert into exp_ping_servers values ('', '0', 'blogrolling.com', 'http://rpc.blogrolling.com/pinger/', '80', 'xmmlrpc', 'n', '3')";
//$Q[] = "insert into exp_ping_servers values ('', '0', 'blogshares.com', 'http://www.blogshares.com/rpc.php', '80', 'xmmlrpc', 'n', '4')";

// Create default categories

$Q[] = "insert into exp_category_groups (group_id, group_name, is_user_blog) values ('', 'Default Category Group', 'n')";

$Q[] = "insert into exp_categories (cat_id, group_id, parent_id, cat_name, cat_url_title, cat_order) values ('1', '1', '0', 'Blogging', 'Blogging', '1')";
$Q[] = "insert into exp_categories (cat_id, group_id, parent_id, cat_name, cat_url_title, cat_order) values ('2', '1', '0', 'News', 'News', '2')";
$Q[] = "insert into exp_categories (cat_id, group_id, parent_id, cat_name, cat_url_title, cat_order) values ('3', '1', '0', 'Personal', 'Personal', '3')";

$Q[] = "insert into exp_category_field_data (cat_id, group_id, site_id) values ('1', '1', '1')";
$Q[] = "insert into exp_category_field_data (cat_id, group_id, site_id) values ('2', '1', '1')";
$Q[] = "insert into exp_category_field_data (cat_id, group_id, site_id) values ('3', '1', '1')";

$Q[] = "insert into exp_category_posts (entry_id, cat_id) values ('1', '1')";

// Create a default weblog entry

$body = <<<PLOPP
	Thank you for choosing ExpressionEngine! This entry contains helpful resources to help you <a href="http://expressionengine.com/docs/overview/get_most.html">get the most from ExpressionEngine</a> and the EllisLab Community.

	[b]Technical Support:[/b]

	All tech support is handled through our Community forums. Our staff and the community respond to issues in a timely manner. Please review the <a href="http://expressionengine.com/docs/overview/getting_help.html">Getting Help</a> section of the User Guide before posting in the forums.

	[b]Learning resources:[/b]

	<a href="http://expressionengine.com/docs/overview/">Getting Started Guide</a>
	<a href="http://expressionengine.com/docs/quick_start/">Quick Start Tutorial</a>
	<a href="http://expressionengine.com/tutorials/">Video Tutorials</a>

	[b]Additional Support Resources:[/b]

	<a href="http://expressionengine.com/docs/">ExpressionEngine User Guide</a>
	<a href="http://expressionengine.com/knowledge_base/">Knowledge Base</a>
	<a href="http://expressionengine.com/wiki/">ExpressionEngine Wiki</a>

	If you need to hire a web developer consider our <a href="http://expressionengine.com/professionals/">Professionals Network</a>. You can also place an ad on our <a href="http://expressionengine.com/forums/viewforum/47/">Job Board</a> if you prefer that professionals find you.

	Love ExpressionEngine?  Help spread the word and make some spare change with our <a href="http://expressionengine.com/affiliates/">Affiliates program</a>.

	See you on the boards,

	[size=4]The EllisLab Team[/size]
PLOPP;
$Q[] = "insert into exp_weblog_titles (entry_id, weblog_id, author_id, ip_address, entry_date, edit_date, year, month, day, title, url_title, status, dst_enabled) values ('', '1', '1',  '".$data['ip']."', '".$now."', '".date("YmdHis")."', '".$year."', '".$month."', '".$day."', 'Getting Started with ExpressionEngine', 'getting_started', 'open', '".$data['daylight_savings']."')";
$Q[] = "insert into exp_weblog_data (entry_id, weblog_id, field_id_2, field_ft_1, field_ft_2, field_ft_3) values ('1', '1', '".$DB->escape_str($body)."', 'xhtml', 'xhtml', 'xhtml')";

// Upload prefs


if (@realpath(str_replace('../', './', $data['image_path'])) !== FALSE)
{
	$data['image_path'] = str_replace('../', './', $data['image_path']);
	$data['image_path'] = str_replace("\\", "/", realpath($data['image_path'])).'/';
}

$props = "style=\"border: 0;\" alt=\"image\"";
$Q[] = "insert into exp_upload_prefs (id, name, server_path, url, allowed_types, properties) values ('', 'Main Upload Directory', '".$data['image_path'].$data['upload_folder']."', '".$data['site_url'].'images/'.$data['upload_folder']."', 'all', '$props')";

// Actions

// Comment module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Comment', '1.2', 'n')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Comment', 'insert_new_comment')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Comment_CP', 'delete_comment_notification')";

// Emoticon module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Emoticon', '1.0', 'n')";

// Mailing List module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Mailinglist', '2.0', 'y')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mailinglist', 'insert_new_email')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mailinglist', 'authorize_email')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Mailinglist', 'unsubscribe')";

// Member module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Member', '1.3', 'n')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'registration_form')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'register_member')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'activate_member')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'member_login')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'member_logout')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'retrieve_password')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'reset_password')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'send_member_email')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'update_un_pw')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'member_search')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Member', 'member_delete')";

// Query module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Query', '1.0', 'n')";

// Referrer module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Referrer', '1.3', 'y')";

// RSS module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Rss', '1.0', 'n')";

// Stats module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Stats', '1.0', 'n')";

// Trackback module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Trackback', '1.1', 'n')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Trackback_CP', 'receive_trackback')";

// Weblog module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Weblog', '1.2', 'n')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Weblog', 'insert_new_entry')";

// Search module
$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Search', '1.2', 'n')";
$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Search', 'do_search')";

// Email module
//$Q[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Email', '1.0', 'n')";
//$Q[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Email', 'send_email')";



//  Create DB tables and insert data
// --------------------------------------------------------------------
// --------------------------------------------------------------------

	foreach($D as $kill)
	{
		$DB->query('drop table if exists '.$kill);
	}
            
    foreach($Q as $sql)
    {    
        if ($DB->query($sql) === FALSE)
        {
        	foreach($D as $kill)
			{
				$DB->query('drop table if exists '.$kill);
			}
			
			//echo $sql; exit;
			
            $er =  "<div class='error'>Error: Unable to perform the SQL queries needed to install 
            		this program. Please make sure your MySQL account has the proper GRANT privileges:  
            		CREATE, DROP, ALTER, INSERT, and DELETE</div>";
            		
            settings_form($er);
            page_footer();
            exit;
        }
    }


// WRITE CONFIG FILE
// --------------------------------------------------------------------
// --------------------------------------------------------------------

	$data['cp_url'] = rtrim($data['cp_url'], '/').'/';

	$captcha_url = rtrim($data['site_url'], '/').'/';
	$captcha_url .= 'images/captchas/';   
	

	if (@realpath(str_replace('../', './', $data['avatar_path'])) !== FALSE)
	{
		$data['avatar_path'] = str_replace('../', './', $data['avatar_path']);
		$data['avatar_path'] = str_replace("\\", "/", realpath($data['avatar_path'])).'/';
	}

	if (@realpath(str_replace('../', './', $data['photo_path'])) !== FALSE)
	{
		$data['photo_path'] = str_replace('../', './', $data['photo_path']);
		$data['photo_path'] = str_replace("\\", "/", realpath($data['photo_path'])).'/';
	}
	
	if (@realpath(str_replace('../', './', $data['signature_img_path'])) !== FALSE)
	{
		$data['signature_img_path'] = str_replace('../', './', $data['signature_img_path']);
		$data['signature_img_path'] = str_replace("\\", "/", realpath($data['signature_img_path'])).'/';
	}
	
	if (@realpath(str_replace('../', './', $data['pm_path'])) !== FALSE)
	{
		$data['pm_path'] = str_replace('../', './', $data['pm_path']);
		$data['pm_path'] = str_replace("\\", "/", realpath($data['pm_path'])).'/';
	}
	
	if (@realpath(str_replace('../', './', $data['captcha_path'])) !== FALSE)
	{
		$data['captcha_path'] = str_replace('../', './', $data['captcha_path']);
		$data['captcha_path'] = str_replace("\\", "/", realpath($data['captcha_path'])).'/';
	}
	
	if (@realpath(str_replace('../', './', $data['theme_folder_path'])) !== FALSE)
	{
		$data['theme_folder_path'] = str_replace('../', './', $data['theme_folder_path']);
		$data['theme_folder_path'] = str_replace("\\", "/", realpath($data['theme_folder_path'])).'/';
	}

    $config = array(
					'app_version'					=>	$data['app_version'],
					'license_number'				=>	'',
                    'debug'                 		=>  '1',
                    'install_lock'          		=>  '1',
                    'db_hostname'           		=>  $data['db_hostname'],
                    'db_username'           		=>  $data['db_username'],
                    'db_password'           		=>  $data['db_password'],
                    'db_name'               		=>  $data['db_name'],
                    'db_type'               		=>  $data['database'],
                    'db_prefix'             		=>  ($data['db_prefix']  != '') ? $data['db_prefix']  : 'exp',
                    'db_conntype'           		=>  $data['db_conntype'],
                    'encryption_type'				=>  $data['encryption_type'],
                    'system_folder'         		=>  $data['system_dir'],
                    'cp_url'	            		=>  $data['cp_url'].$data['cp_index'],
                    'site_index'            		=>  $data['site_index'],
                    'site_name'              		=>  $data['site_name'],
                    'site_url'              		=>  $data['site_url'],
                    'theme_folder_url'				=>  $data['site_url'].'themes/',
                    'doc_url'              			=>  $data['doc_url'],
                    'webmaster_email'       		=>  $data['webmaster_email'],
                    'webmaster_name'				=> '',
					'weblog_nomenclature'			=> 'weblog',                    
					'max_caches'					=> '150',                    
					'captcha_url'					=>  $captcha_url,
					'captcha_path'					=> $data['captcha_path'],
                    'captcha_font'					=>  'y',
                    'captcha_rand'					=> 'y',
                    'captcha_require_members'		=>	'n',
                    'enable_db_caching'				=>  'y',
                    'enable_sql_caching'			=>  'n',
                    'force_query_string'     		=>  'n',
                    'show_queries'           		=>  'n',
                    'template_debugging'			=>	'n',
                    'include_seconds'				=>	'n',
                    'cookie_domain'         		=>  '',
                    'cookie_path'           		=>  '',
                    'cookie_prefix'         		=>  '',
                    'user_session_type'     		=>  'c', 
                    'admin_session_type'    		=>  'cs',
                    'allow_username_change' 		=>  'y',
                    'allow_multi_logins'    		=>  'y',
                    'password_lockout'				=>	'y',
                    'password_lockout_interval' 	=>  '1',
                    'require_ip_for_login'			=>  'y',
                    'require_ip_for_posting'		=>  'y',
                    'allow_multi_emails'    		=>  'n',
                    'require_secure_passwords'		=>  'n',
                    'allow_dictionary_pw'			=>  'y',
                    'name_of_dictionary_file'		=>	'',
                    'xss_clean_uploads'				=>	'y',
                    'redirect_method'       		=>  $data['redirect_method'],
                    'deft_lang'             		=>  $data['deft_lang'],
                    'xml_lang'              		=>  'en',
                    'charset'               		=>  'utf-8',
                    'send_headers'          		=>  'y',
                    'gzip_output'           		=>  'n',
                    'log_referrers'         		=>  'y',
                    'max_referrers'					=>	'500',
                    'is_system_on'          		=>  'y',
                    'time_format'           		=>  'us',
                    'server_timezone'       		=>  $data['server_timezone'],
                    'server_offset'         		=>  '',
                    'daylight_savings'      		=>  $data['daylight_savings'],
                    'default_site_timezone'			=>  $data['server_timezone'],
                    'default_site_dst'      		=>  $data['daylight_savings'],
                    'honor_entry_dst'				=>	'y',
                    'mail_protocol'         		=>  'mail',
                    'smtp_server'           		=>  '',
                    'smtp_username'         		=>  '',
                    'smtp_password'         		=>  '',
                    'email_debug'       			=>  'n',
                    'email_charset'       			=>  'utf-8',
                    'email_batchmode'       		=>  'n',
                    'email_batch_size'      		=>  '',
                    'mail_format'           		=>  'plain',
                    'word_wrap'             		=>  'y',
                    'email_console_timelock'		=>	'5',
                    'log_email_console_msgs'		=>	'y',
                    'cp_theme'              		=>  'default',
                    'email_module_captchas'			=>	'n',
                    'log_search_terms'				=>	'y',
                    'un_min_len'            		=>  '4',
                    'pw_min_len'            		=>  '5',
                    'allow_member_registration' 	=>  'y',
                    'allow_member_localization' 	=>  'y',
                    'req_mbr_activation'    		=>  'email',
                    'new_member_notification'		=>	'n',
                    'mbr_notification_emails'		=>	'',
                    'require_terms_of_service'		=>	'y',
                    'use_membership_captcha'		=>	'n',
                    'default_member_group'  		=>  '5',
                    'profile_trigger'				=>  'member',
                    'member_theme'			  		=>  'default',
					'enable_avatars' 				=> 'y',
					'allow_avatar_uploads' 			=> 'n',
					'avatar_url'					=> $data['site_url'].$data['avatar_url'],
					'avatar_path'					=> $data['avatar_path'],
					'avatar_max_width'				=> '100',
					'avatar_max_height'				=> '100',
					'avatar_max_kb'					=> '50',
					'enable_photos' 				=> 'n',
					'photo_url'						=> $data['site_url'].$data['photo_url'],
					'photo_path'					=> $data['photo_path'],
					'photo_max_width'				=> '100',
					'photo_max_height'				=> '100',
					'photo_max_kb'					=> '50',
                    'allow_signatures'          	=> 'y',
                    'sig_maxlength'        	  		=> '500',
                    'sig_allow_img_hotlink'        	=> 'n',
					'sig_allow_img_upload' 			=> 'n',
					'sig_img_url'					=> $data['site_url'].$data['signature_img_url'],
					'sig_img_path'					=> $data['signature_img_path'],
					'sig_img_max_width'				=> '480',
					'sig_img_max_height'			=> '80',
					'sig_img_max_kb'				=> '30',
					'prv_msg_upload_path'			=> $data['pm_path'],
					'prv_msg_max_attachments'		=> '3',
					'prv_msg_attach_maxsize'		=> '250',
					'prv_msg_attach_total'			=> '100',
					'prv_msg_html_format'			=> 'safe',
					'prv_msg_auto_links'			=> 'y',
					'prv_msg_max_chars'				=> '6000',
					'strict_urls'					=>	'n',
                    'site_404'						=>	'',
                    'save_tmpl_revisions'   		=>  'n',
                    'max_tmpl_revisions'			=>	'5',
                    'save_tmpl_files'   			=>  'n',
                    'tmpl_file_basepath'   			=>  '',
                    'secure_forms'          		=>  'y',
                    'deny_duplicate_data'       	=>  'y',
                    'redirect_submitted_links'		=>  'n',
                    'enable_censoring'      		=>  'n',
                    'censored_words'       			=>  '',
                    'censor_replacement'			=>  '',
                    'banned_ips'            		=>  '',
                    'banned_emails'         		=>  '',
                    'banned_usernames'				=>	'',
                    'banned_screen_names'			=>	'',
                    'ban_action'            		=>  'restrict',
                    'ban_message'           		=>  'This site is currently unavailable',
                    'ban_destination'       		=>  'http://www.yahoo.com/',
                    'enable_emoticons'      		=>  'y',
                    'emoticon_path'         		=>  $data['site_url'].'images/smileys/',
                    'recount_batch_total'   		=>  '1000',
                    'enable_image_resizing'			=>	'y',
                    'image_resize_protocol'			=>	'gd2',
                    'image_library_path'			=>	'',
                    'thumbnail_prefix'				=>	'thumb',
                    'word_separator'				=>	'underscore',
                    'use_category_name'				=>	'n',
                    'reserved_category_word'		=>	'category',
                    'auto_convert_high_ascii'		=>	'n',
                    'new_posts_clear_caches'		=>	'y',
                    'auto_assign_cat_parents'		=>	'y',
                    'remap_pm_urls'					=> 'n',
                    'remap_pm_dest'					=> '',
					'new_version_check' 			=> 'y',
					'publish_tab_behavior'			=> 'hover',
					'sites_tab_behavior'			=> 'hover',
					'enable_throttling' 			=> 'n',
					'banish_masked_ips'				=> 'y',
					'max_page_loads' 				=> '10',
					'time_interval' 				=> '8',
					'lockout_time' 					=> '30',
					'banishment_type'				=> 'message',
					'banishment_url'				=> '',
					'banishment_message'			=> 'You have exceeded the allowed page load frequency.',
					'enable_search_log'				=> 'y',
					'max_logged_searches'			=> '500',
					'allow_extensions'				=> 'n',
					'mailinglist_enabled'			=> 'y',
					'mailinglist_notify'			=> 'n',
					'mailinglist_notify_emails'		=> '',
					'memberlist_order_by'			=> "total_posts",
					'memberlist_sort_order'			=> "desc",
					'memberlist_row_limit'			=> "20",
					'is_site_on'          			=> 'y',
					'multiple_sites_enabled'		=> "n",
					'theme_folder_path'				=> $data['theme_folder_path'],
                  );

// --------------------------------------------------------------------
//  Writes Sites Database
// --------------------------------------------------------------------

		/** ---------------------------------------
		/**  Default Administration Prefs
		/** ---------------------------------------*/
		
		$admin_default = array( 'encryption_type',
								'site_index',
								'site_name',
								'site_url',
								'theme_folder_url',
								'webmaster_email',
								'webmaster_name',
								'weblog_nomenclature',
								'max_caches',
								'captcha_url',
								'captcha_path',
								'captcha_font',
								'captcha_rand',
								'captcha_require_members',
								'enable_db_caching',
								'enable_sql_caching',
								'force_query_string',
								'show_queries',
								'template_debugging',
								'include_seconds',
								'cookie_domain',
								'cookie_path',
								'user_session_type',
								'admin_session_type',
								'allow_username_change',
								'allow_multi_logins',
								'password_lockout',
								'password_lockout_interval',
								'require_ip_for_login',
								'require_ip_for_posting',
								'allow_multi_emails',
								'require_secure_passwords',
								'allow_dictionary_pw',
								'name_of_dictionary_file',
								'xss_clean_uploads',
								'redirect_method',
								'deft_lang',
								'xml_lang',
								'charset',
								'send_headers',
								'gzip_output',
								'log_referrers',
								'max_referrers',
								'time_format',
								'server_timezone',
								'server_offset',
								'daylight_savings',
								'default_site_timezone',
								'default_site_dst',
								'honor_entry_dst',
								'mail_protocol',
								'smtp_server',
								'smtp_username',
								'smtp_password',
								'email_debug',
								'email_charset',
								'email_batchmode',
								'email_batch_size',
								'mail_format',
								'word_wrap',
								'email_console_timelock',
								'log_email_console_msgs',
								'cp_theme',
								'email_module_captchas',
								'log_search_terms',
								'secure_forms',
								'deny_duplicate_data',
								'redirect_submitted_links',
								'enable_censoring',
								'censored_words',
								'censor_replacement',
								'banned_ips',
								'banned_emails',
								'banned_usernames',
								'banned_screen_names',
								'ban_action',
								'ban_message',
								'ban_destination',
								'enable_emoticons',
								'emoticon_path',
								'recount_batch_total',
								'remap_pm_urls',  		// Get out of Weblog Prefs
								'remap_pm_dest',		// Get out of Weblog Prefs
								'new_version_check',
								'publish_tab_behavior',
								'sites_tab_behavior',
								'enable_throttling',
								'banish_masked_ips',
								'max_page_loads',
								'time_interval',
								'lockout_time',
								'banishment_type',
								'banishment_url',
								'banishment_message',
								'enable_search_log',
								'max_logged_searches',
								'theme_folder_path',
								'is_site_on');
		
		$site_prefs = array();
		
		foreach($admin_default as $value)
		{
			$site_prefs[$value] = str_replace('\\', '\\\\', $config[$value]);
		}
			
		$DB->query($DB->update_string('exp_sites', array('site_system_preferences' => addslashes(serialize($site_prefs))), "site_id = 1"));
		
		/** ---------------------------------------
		/**  Default Mailinglists Prefs
		/** ---------------------------------------*/
		
		$mailinglist_default = array('mailinglist_enabled', 'mailinglist_notify', 'mailinglist_notify_emails');
		
		$site_prefs = array();
		
		foreach($mailinglist_default as $value)
		{
			$site_prefs[$value] = str_replace('\\', '\\\\', $config[$value]);
		}
		
		$DB->query($DB->update_string('exp_sites', array('site_mailinglist_preferences' => addslashes(serialize($site_prefs))), "site_id = 1"));

		/** ---------------------------------------
		/**  Default Members Prefs
		/** ---------------------------------------*/
		
		$member_default = array('un_min_len',
								'pw_min_len',
								'allow_member_registration',
								'allow_member_localization',
								'req_mbr_activation',
								'new_member_notification',
								'mbr_notification_emails',
								'require_terms_of_service',
								'use_membership_captcha',
								'default_member_group',
								'profile_trigger',
								'member_theme',
								'enable_avatars',
								'allow_avatar_uploads',
								'avatar_url',
								'avatar_path',
								'avatar_max_width',
								'avatar_max_height',
								'avatar_max_kb',
								'enable_photos',
								'photo_url',
								'photo_path',
								'photo_max_width',
								'photo_max_height',
								'photo_max_kb',
								'allow_signatures',
								'sig_maxlength',
								'sig_allow_img_hotlink',
								'sig_allow_img_upload',
								'sig_img_url',
								'sig_img_path',
								'sig_img_max_width',
								'sig_img_max_height',
								'sig_img_max_kb',
								'prv_msg_upload_path',
								'prv_msg_max_attachments',
								'prv_msg_attach_maxsize',
								'prv_msg_attach_total',
								'prv_msg_html_format',
								'prv_msg_auto_links',
								'prv_msg_max_chars',
								'memberlist_order_by',
								'memberlist_sort_order',
								'memberlist_row_limit');
		
		$site_prefs = array();
		
		foreach($member_default as $value)
		{
			$site_prefs[$value] = str_replace('\\', '\\\\', $config[$value]);
		}
		
		$DB->query($DB->update_string('exp_sites', array('site_member_preferences' => addslashes(serialize($site_prefs))), "site_id = 1"));
		
		/** ---------------------------------------
		/**  Default Templates Prefs
		/** ---------------------------------------*/
		
		$template_default = array('strict_urls',
								  'site_404',
								  'save_tmpl_revisions',
								  'max_tmpl_revisions',
								  'save_tmpl_files',
								  'tmpl_file_basepath');
		$site_prefs = array();
		
		foreach($template_default as $value)
		{
			$site_prefs[$value] = str_replace('\\', '\\\\', $config[$value]);
		}
		
		$DB->query($DB->update_string('exp_sites', array('site_template_preferences' => addslashes(serialize($site_prefs))), "site_id = 1"));
								  		
		/** ---------------------------------------
		/**  Default Weblogs Prefs
		/** ---------------------------------------*/

		$weblog_default = array('enable_image_resizing',
								'image_resize_protocol',
								'image_library_path',
								'thumbnail_prefix',
								'word_separator',
								'use_category_name',
								'reserved_category_word',
								'auto_convert_high_ascii',
								'new_posts_clear_caches',
								'auto_assign_cat_parents');
		
		$site_prefs = array();
		
		foreach($weblog_default as $value)
		{
			$site_prefs[$value] = str_replace('\\', '\\\\', $config[$value]);
		}
		
		$DB->query($DB->update_string('exp_sites', array('site_weblog_preferences' => addslashes(serialize($site_prefs))), "site_id = 1"));
		
		/** ---------------------------------------
		/**  Remove Site Prefs from Config
		/** ---------------------------------------*/
		
		foreach(array_merge($admin_default, $mailinglist_default, $member_default, $template_default, $weblog_default) as $value)
		{
			unset($config[$value]);
		}


// --------------------------------------------------------------------
// Write config file
// --------------------------------------------------------------------

 
	$conf  = '<?php';
	$conf .= "\n\nif ( ! defined('EXT')){\nexit('Invalid file request');\n}\n\n";
 
	foreach ($config as $key => $val)
	{
		$val = str_replace('\\', '\\\\', $val);
		$val = str_replace("'", "\\'", $val);
		$val = str_replace("\"", "\\\"", $val);

		$conf .= "\$conf['".$key."'] = \"".$val."\";\n";        
	} 
	
	$conf .= '?'.'>';
		 
	$cfile = './'.$data['system_dir'].'/config.php';

	if ( ! $fp = @fopen($cfile, 'wb'))
	{
		echo "<div class='error'>Error: unable to write the config file.</div>";
		page_footer();
		exit;
	}                

	fwrite($fp, $conf, strlen($conf));
	fclose($fp);
	
	$cbfile = './'.$data['system_dir'].'/config_bak.php';
	
	if ($fp = @fopen($cbfile, 'wb'))
	{
		fwrite($fp, $conf, strlen($conf));
		fclose($fp);
	}                


        
// Write the path.php file
// --------------------------------------------------------------------
// --------------------------------------------------------------------

	$path  = "<?php\n\n";
	$path .= '// ------------------------------------------------------'."\n";		
	$path .= '// DO NOT ALTER THIS FILE UNLESS YOU HAVE A REASON TO'."\n\n";
	$path .= '// ------------------------------------------------------'."\n";
	$path .= '// Path to the directory containing your backend files'."\n\n";
	$path .= '$system_path = "./'.$data['system_dir'].'/"'.";\n\n";
	$path .= '// ------------------------------------------------------'."\n";
	$path .= '// MANUALLY CONFIGURABLE VARIABLES'."\n";
	$path .= '// See user guide for more information'."\n";
	$path .= '// ------------------------------------------------------'."\n\n";
	$path .= '$template_group = "";'."\n";
	$path .= '$template = "";'."\n";
	$path .= '$site_url = "";'."\n";
	$path .= '$site_index = "";'."\n";
	$path .= '$site_404 = "";'."\n";
	$path .= '$global_vars = array(); // This array must be associative'."\n\n";
	$path .= '?'.'>';
			 
	if ( ! $fp = @fopen('path.php', 'wb'))
	{
		echo "<div class='error'>Error: unable to write the path.php file.</div>";
		page_footer();
		exit;
	}                
	
	fwrite($fp, $path);
	fclose($fp);
	@chmod('path.php', 0644);
	
// Create cache directories
// --------------------------------------------------------------------
// --------------------------------------------------------------------

	$cache_path = './'.$data['system_dir'].'/cache/';
	$cache_dirs = array('db_cache', 'page_cache', 'tag_cache', 'sql_cache');
	$errors = array();

	foreach ($cache_dirs as $dir)
	{
		if ( ! @is_dir($cache_path.$dir))
		{
			if ( ! @mkdir($cache_path.$dir, 0777))
			{
				$errors[] = $dir;
				
				continue;
			}
				
			@chmod($cache_path.$dir, 0777);
		}
   } 
        
       
// Show "success" page
// --------------------------------------------------------------------
// --------------------------------------------------------------------
    ?>        
    
	<div id='innercontent'>
    <h3>ExpressionEngine has been successfully installed!</h3>
    
    <?php
    
    if (count($errors) > 0)
    {
    ?>
    <p><span class="red">Please Note:  There was a problem creating your caching directories.  This is not a critical problem, but you may be unable to use the caching feature.</span></p>
    <?php
    }
    ?>
    
    <div class="border"><p><span class="red"><b>Important:</b>&nbsp; Using your FTP program, please delete the file called <b>install.php</b> from your server.<br />Leaving it on your server presents a security risk.</span></p></div>
    
    <p><br /><b>Please bookmark these two links:</b></p>
    
    <p><a href='./<?php echo $data['system_dir']; ?>/index.php' target="_blank">Click here to access your control panel</a></p>
  
    <p><a href='<?php echo $data['site_url'].$data['site_index']; ?>'  target="_blank">Click here to view your site</a></p>
    
    <p><br />We hope you enjoy ExpressionEngine!</p>
    
    </div>
    <?php
}




// PAGE SIX
// --------------------------------------------------------------------
// Pages 6 and 7 are for aborted installations due to accidentally
// overwritten config.php files
// --------------------------------------------------------------------

elseif ($page == 6)
{
	// this is the landing page for an aborted installation after the installer
	// detected existing ExpressionEngine tables
	
	$offer_update = FALSE;

	if ($data['system_dir'] != '' AND @is_dir('./'.trim($data['system_dir'])))
	{
		$system_path = './'.trim($data['system_dir']).'/';
		
		if (@file_exists($system_path.'config_bak'.$data['ext']))
		{
			require_once($system_path.'config_bak'.$data['ext']);
			
			$ver = isset($conf['app_version']) ? $conf['app_version'] : NULL;
			
			if ($ver >= 160)
			{
				// config_bak.php looks ok, and is for 160+ so it should be safe
				// to allow them to proceed to the update script if they want,
				// after using the backup to rebuild their config.php file.
				$offer_update = TRUE;
			}
		}
	}
?>
<div id='innercontent'>

<div class="error">Existing Installation Detected, Empty config.php File</div>

<p>Your configuration file is empty, or incomplete, but ExpressionEngine tables exist in your database
from an existing installation.  It may be that you inadvertently overwrote your config.php file
when uploading files to your server intending to perform an update.</p>

	<?php if ($offer_update == TRUE)
	{
	?>
		<p>If you intended to update your site, we can rebuild your config.php from what appears to be a
		recent copy (config_bak.php).  If you would like us to do so and then proceed with the update, please read the
		<a href="http://expressionengine.com/docs/installation/update.html">update instructions</a> carefully,
		and then click the Update button at the bottom of this page to proceed.
		</p>
	
		<p>If you do not wish us to use the backup configuration file for the update, and would like to restore
		the config.php file on your own, please do so at this time, and then proceed with the update instructions
		linked above.
		</p>
	
		<form action="install.php?page=7" method="post">
			<input type="hidden" name="system_dir" value="<?php echo addslashes($data['system_dir']); ?>" />
			<input type="hidden" name="rebuild_config" value="y" />
			<p><input type="submit" value="Update my installation"></p>
		</form>
	<?php
	
	}
	else
	{
	?>

	<p>If you intended to update your site, you will need to restore your config.php file.  If you
	do not have a backup, check to see if your config_bak.php file is also empty.  If it did not
	get overwritten as well, then you can use the contents of that file to restore your config.php file.</p>

	<p>After restoring your config.php file, please follow the
	<a href="http://expressionengine.com/docs/installation/update.html">update instructions</a> carefully.</p>

	<?php
	}
}


// PAGE SEVEN
// --------------------------------------------------------------------
// Pages 6 and 7 are for aborted installations due to accidentally
// overwritten config.php files
// --------------------------------------------------------------------

elseif ($page == 7)
{

	// this page rebuilds the config.php file from the config_bak.php file
	// and redirects to the update script

	$offer_update = FALSE;

	if ($data['system_dir'] != '' AND is_dir('./'.trim($data['system_dir'])) AND isset($_POST['rebuild_config']))
	{
		$system_path = './'.trim($data['system_dir']).'/';
		
		if (@file_exists($system_path.'config_bak'.$data['ext']))
		{
			require_once($system_path.'config_bak'.$data['ext']);
			
			$ver = isset($conf['app_version']) ? $conf['app_version'] : NULL;

			if ($ver >= 160 AND ($fp = @fopen($system_path.'config'.$data['ext'], 'wb')))
			{
				// config_bak.php looks ok, and is for 160+ so it should be safe
				// to allow them to proceed to the update script if they want,
				// after using the backup to rebuild their config.php file.
				$offer_update = TRUE;
			}
		}
	}
?>

	<div id='innercontent'>

	<div class="error">Configuration File Rebuild</div>

<?php
	if ($offer_update == FALSE)
	{
		?>
			<p><b>We're sorry but we are unable to perform this task.  Please refer to the User Guide
			instructions for assistance:</b><br /><br />

			<a href="http://expressionengine.com/docs/installation/installation.html">Installation Instructions</a><br /><br />
			<a href="http://expressionengine.com/docs/installation/update.html">Updating to the latest <em>Version</em></a><br /><br />
			<a href="http://expressionengine.com/docs/installation/update_build.html">Updating to the latest <em>Build</em>
			</p>
		<?php
	}
	else
	{
		/** -----------------------------------------
		/**  Write config file as a string
		/** -----------------------------------------*/
		
		$new  = "<?php\n\n";
		$new .= "if ( ! defined('EXT')){\nexit('Invalid file request');\n}\n\n";
	 
		foreach ($conf as $key => $val)
		{
			$val = str_replace("\\\"", "\"", $val);
			$val = str_replace("\\'", "'", $val);			
			$val = str_replace('\\\\', '\\', $val);
		
			$val = str_replace('\\', '\\\\', $val);
			$val = str_replace("'", "\\'", $val);
			$val = str_replace("\"", "\\\"", $val);

			$new .= "\$conf['".$key."'] = \"".$val."\";\n";
		} 
		
		$new .= '?'.'>';
		
		/** -----------------------------------------
		/**  Write config file
		/** -----------------------------------------*/

		flock($fp, LOCK_EX);
		fwrite($fp, $new, strlen($new));
		flock($fp, LOCK_UN);
		fclose($fp);

?>
	<p>Configuration file rebuilt from backup!</p>
	<p>Click this link to proceed to the <a href="<?php echo $data['system_dir']; ?>/update.php">update script</a>.</p>
	
	</div>
<?php
	}

}

// END PAGES
// --------------------------------------------------------------------
// --------------------------------------------------------------------




//  System folder form
// --------------------------------------------------------------------
// --------------------------------------------------------------------


function system_folder_form()
{
    global $data;
    
    
    $dir = ( ! isset($data['system_dir'])) ? 'system' : $data['system_dir'];    

?>

<div id='innercontent'>

<h1>ExpressionEngine Installation Wizard</h1>

    <h2>Name of your "system" folder</h2>
    
    <p>As a security precaution you may have renamed the "<b>system</b>" folder, as indicated in the installation instructions.</p>
    
    <p>If you have renamed it, please indicate the new name here.  Otherwise, leave it as "system"</p>
    
    <p>
    <form method='post' action='install.php?page=4'>
    <input type="hidden" name="nothing" value="0" />
    <input type='text' name='system_dir' value='<?php echo $dir; ?>' size='20' class='input'>
    </p>
    
    <p>
    <input type='submit' value='Submit' class='submit'>
    </p>
    
    </form>
    
    </p>
    
    </div>
<?php
}





//  Database Settings form
// --------------------------------------------------------------------
// --------------------------------------------------------------------

function settings_form($errors = '')
{
    global $_SERVER, $data;
   
    $pathinfo = pathinfo(__FILE__);

    $self = $pathinfo['basename']; 
    
    $host		= ( ! isset($_SERVER['HTTP_HOST'])) ? '' : $_SERVER['HTTP_HOST'];
    $phpself	= ( ! isset($_SERVER['PHP_SELF'])) ? '' : htmlentities($_SERVER['PHP_SELF']);
    
    
    $path = "http://" . $host.$phpself;  
    
    $path = substr($path, 0, - strlen($self));  

    $dir = ($data['system_dir'] == '') ? 'system'  : $data['system_dir'];  
    $cp_url = ($data['cp_url'] == '') ? $path.$dir.'/' : $data['cp_url'];   
    $site_url = ($data['site_url'] == '' OR $data['site_url'] == '/') ? $path : $data['site_url'];   
    $site_index = ($data['site_index'] == '') ? 'index.php' : $data['site_index'];   

    $db_hostname        = ($data['db_hostname'] == '')      	? 'localhost' : stripslashes($data['db_hostname']);   
    $db_username        = ($data['db_username'] == '')      	? ''  	: stripslashes($data['db_username']);   
    $db_password        = ($data['db_password'] == '')      	? ''  	: stripslashes($data['db_password']);   
    $db_name            = ($data['db_name'] == '')          	? ''  	: stripslashes($data['db_name']);       
    $db_prefix          = ($data['db_prefix'] == '')        	? 'exp' : stripslashes($data['db_prefix'] );       
    $db_conntype        = ($data['db_conntype'] == '')      	? '0' : stripslashes($data['db_conntype'] );       
    $username           = ($data['username'] == '')         	? ''  	: stripslashes($data['username']);   
    $password           = ($data['password'] == '')         	? ''  	: stripslashes($data['password']);   
    $email              = ($data['email'] == '')            	? ''  	: stripslashes($data['email']);     
    $screen_name        = ($data['screen_name'] == '')      	? ''  	: stripslashes($data['screen_name']);     
    $redirect_method    = ($data['redirect_method'] == '')  	? ''  	: stripslashes($data['redirect_method']);     
    $daylight_savings   = ($data['daylight_savings'] == '') 	? ''  	: stripslashes($data['daylight_savings']);     
    $webmaster_email    = ($data['webmaster_email'] == '')  	? ''  	: stripslashes($data['webmaster_email']);
    $template    		= ($data['template'] == '')  			? '01'	: stripslashes($data['template']);
    $site_name   		= ($data['site_name'] == '') 			? ''	: stripslashes($data['site_name']);     
    $deft_lang   		= ($data['deft_lang'] == '') 			? 'english'	: stripslashes($data['deft_lang']);     
	$timezone 			= ($data['server_timezone'] == '') 		? 'UTC' 		: $data['server_timezone'];
    $encryption_type	= ($data['encryption_type'] == '')  	? 'sha1' : stripslashes($data['encryption_type']);       



    if ($redirect_method == '' || $redirect_method == 'redirect')
    {
        $redirect = 'checked="checked"';
        $refresh  = '';
    }
    else
    {
        $refresh  = 'checked="checked"';
        $redirect = '';
    }
    
    if ($db_conntype == 1)
    {
        $persistent = 'checked="checked"';
        $nonpersistent  = '';
    }
    else
    {
		$persistent = '';
        $nonpersistent = 'checked="checked"';
    }
    
    
    if ($daylight_savings == 'y')
    {
        $dst1 = 'checked="checked"';
        $dst2  = '';
    }
    else
    {
        $dst2  = 'checked="checked"';
        $dst1 = '';
    }
    
    if ($encryption_type == '' || $encryption_type == 'sha1')
    {
        $sha1 = 'checked="checked"';
        $md5  = '';
    }
    else
    {
        $md5  = 'checked="checked"';
        $sha1 = '';
    }

?>
<div id='innercontent'>

<h1>ExpressionEngine Installation Wizard</h1>

<?php 
if ($errors != '')
{
	echo $errors;
}	
else
{
	echo "<h2>Enter Your Settings</h2>";
}
?>

<p><span class="red"><b>Note: </b> If you are not sure what any of these settings should be, please contact your hosting provider and ask them.</span></p>

<form method='post' action='install.php?page=5'>
<input type='hidden' name='system_dir' value='<?php echo $dir; ?>'>


<div class="shade">
<div class="settingHead">Server Settings</div>

<h5>Name of the index page of your ExpressionEngine site</h5>
<p>Unless you renamed the file, it will be called <b>index.php</b></p>
<p><input type='text' name='site_index' value='<?php echo $site_index; ?>' size='40'  class='input'></p>


<h5>URL to the directory where the above index page is located</h5>
<p>Typically this will be the root of your site (http://www.yourdomain.com/)
<br />Do not include the index page in the URL</p>

<p><input type='text' name='site_url' value='<?php echo $site_url; ?>' size='60'  class='input'></p>


<h5>URL to your "<?php echo $dir; ?>" directory</h5> 
<p><input type='text' name='cp_url' value='<?php echo $cp_url; ?>' size='60'  class='input'></p>


<h5>Email address of webmaster</h5>

<p><input type='text' name='webmaster_email' value='<?php echo $webmaster_email; ?>' size='40'  class='input'></p>



<h5>What type of server are your hosted on?</h5>
<p>If you don't know, choose <b>Unix</b></p>
<p>
<input type="radio" class='radio' name="redirect_method" value="redirect" <?php echo $redirect; ?> /> Unix (or Unix variant, like Linux, Mac OS X, BSD, Solaris, etc.)<br />
<input type="radio" class='radio' name="redirect_method" value="refresh"  <?php echo $refresh; ?> /> Windows (NT or IIs)
</p>

</div>



<div class="shade">
<div class="settingHead">Database Settings</div>

<h5>MySQL Server Address</h5>
<p>Usually you will use 'localhost', but your hosting provider may require something else</p>

<p><input type='text' name='db_hostname' value='<?php echo $db_hostname; ?>' size='40' class='input' /></p>


<h5>MySQL Username</h5>
<p>The username you use to access your MySQL database</p>
<p><input type='text' name='db_username' value='<?php echo $db_username; ?>' size='40' class='input' /></p>


<h5>MySQL Password</h5>
<p>The password you use to access your MySQL database</p>
<p><input type='text' name='db_password' value='<?php echo $db_password; ?>' size='40' class='input' /></p>


<h5>MySQL Database Name</h5>
<p>The name of the database where you want ExpressionEngine installed.</p>
<p class="red">Note: The installation wizard will not create the database for you so you must specify the name of a database that exists.</p>
<p><input type='text' name='db_name' value='<?php echo $db_name; ?>' size='40' class='input' /></p>


<h5>Database Prefix</h5>
<p>Use <b>exp</b> unless you need to use a different prefix</p>

<p><input type='text' name='db_prefix' value='<?php echo $db_prefix; ?>' size='12'  maxlength='30' class='input' /></p>

<h5>What type of database connection do you prefer?</h5>
<p>
A <b>non-persistent</b> connection is recommended.</p>
<p>
<input type="radio" class='radio' name="db_conntype" value="0"  <?php echo $nonpersistent; ?> /> Non-persistent<br />
<input type="radio" class='radio' name="db_conntype" value="1" <?php echo $persistent; ?> /> Persistent
</p>

</div>


<div class="shade">
<div class="settingHead">Encryption Settings</div>

<h5>What type of password encryption do you prefer?</h5>
<p><b>SHA1</b> is recommended since it is more secure, but MD5 can be used for more broad compatibility with other PHP applications.</p>
<p>
<input type="radio" class='radio' name="encryption_type" value="sha1" <?php echo $sha1; ?> /> SHA1<br />
<input type="radio" class='radio' name="encryption_type" value="md5"  <?php echo $md5; ?> /> MD5
</p>

</div>

<div class="shade">
<div class="settingHead">Create your admin account</div>

<p>You will use these settings to access  your ExpressionEngine control panel</p>

<h5>Username</h5>
<p><span class='red'>Use at least four characters</span></p>

<p><input type='text' name='username' value='<?php echo $username; ?>' size='40' maxlength='50' class='input' /></p>

<h5>Password</h5>
<p><span class='red'>Use at least five characters</span></p>

<p><input type='text' name='password' value='<?php echo $password; ?>' size='40' maxlength='32' class='input' /></p>

<h5>Your email address</h5>

<p><input type='text' name='email' value='<?php echo $email; ?>' size='40'  maxlength='80' class='input' /></p>


<h5>Screen Name</h5>
<p>This is the name that will appear on your entries
<br />If you leave this field blank, your username will be used as your screen name</p>
<p><input type='text' name='screen_name' value='<?php echo $screen_name; ?>' size='40' maxlength='50' class='input' /></p>



<h5>Name of your site</h5>

<p><input type='text' name='site_name' value='<?php echo $site_name; ?>' size='40' class='input'></p>


</div>


<div class="shade">
<div class="settingHead">Localization Settings</div>


<h5>Your Time Zone</h5>

<p>


<select name='server_timezone' class='select'>

<?php $selected = ($timezone == 'UM12') ? " selected" : ""; ?>
<option value='UM12'<?php echo $selected; ?>>(UTC - 12:00) Eniwetok, Kwajalein</option>
<?php $selected = ($timezone == 'UM11') ? " selected" : ""; ?>
<option value='UM11'<?php echo $selected; ?>>(UTC - 11:00) Nome, Midway Island, Samoa</option>
<?php $selected = ($timezone == 'UM10') ? " selected" : ""; ?>
<option value='UM10'<?php echo $selected; ?>>(UTC - 10:00) Hawaii</option>
<?php $selected = ($timezone == 'UM95') ? " selected" : ""; ?>
<option value='UM95'<?php echo $selected; ?>>(UTC - 9:30) Marquesas Islands</option>
<?php $selected = ($timezone == 'UM9') ? " selected" : ""; ?>
<option value='UM9'<?php echo $selected; ?>>(UTC - 9:00) Alaska</option>
<?php $selected = ($timezone == 'UM8') ? " selected" : ""; ?>
<option value='UM8'<?php echo $selected; ?>>(UTC - 8:00) Pacific Time</option>
<?php $selected = ($timezone == 'UM7') ? " selected" : ""; ?>
<option value='UM7'<?php echo $selected; ?>>(UTC - 7:00) Mountain Time</option>
<?php $selected = ($timezone == 'UM6') ? " selected" : ""; ?>
<option value='UM6'<?php echo $selected; ?>>(UTC - 6:00) Central Time, Mexico City</option>
<?php $selected = ($timezone == 'UM5') ? " selected" : ""; ?>
<option value='UM5'<?php echo $selected; ?>>(UTC - 5:00) Eastern Time, Bogota, Lima, Quito</option>
<?php $selected = ($timezone == 'UM45') ? " selected" : ""; ?>
<option value='UM45'<?php echo $selected; ?>>(UTC - 4:30) Venezuelan Standard Time</option>
<?php $selected = ($timezone == 'UM4') ? " selected" : ""; ?>
<option value='UM4'<?php echo $selected; ?>>(UTC - 4:00) Atlantic Time, Caracas, La Paz</option>
<?php $selected = ($timezone == 'UM35') ? " selected" : ""; ?>
<option value='UM35'<?php echo $selected; ?>>(UTC - 3:30) Newfoundland</option>
<?php $selected = ($timezone == 'UM3') ? " selected" : ""; ?>
<option value='UM3'<?php echo $selected; ?>>(UTC - 3:00) Brazil, Buenos Aires, Georgetown, Falkland Is.</option>
<?php $selected = ($timezone == 'UM2') ? " selected" : ""; ?>
<option value='UM2'<?php echo $selected; ?>>(UTC - 2:00) Mid-Atlantic, Ascention Is., St Helena</option>
<?php $selected = ($timezone == 'UM1') ? " selected" : ""; ?>
<option value='UM1'<?php echo $selected; ?>>(UTC - 1:00) Azores, Cape Verde Islands</option>
<?php $selected = ($timezone == 'UTC') ? " selected" : ""; ?>
<option value='UTC'<?php echo $selected; ?>>(UTC) Casablanca, Dublin, Edinburgh, London, Lisbon, Monrovia</option>
<?php $selected = ($timezone == 'UP1') ? " selected" : ""; ?>
<option value='UP1'<?php echo $selected; ?>>(UTC + 1:00) Berlin, Brussels, Copenhagen, Madrid, Paris, Rome</option>
<?php $selected = ($timezone == 'UP2') ? " selected" : ""; ?>
<option value='UP2'<?php echo $selected; ?>>(UTC + 2:00) Kaliningrad, South Africa, Warsaw</option>
<?php $selected = ($timezone == 'UP3') ? " selected" : ""; ?>
<option value='UP3'<?php echo $selected; ?>>(UTC + 3:00) Baghdad, Riyadh, Moscow, Nairobi</option>
<?php $selected = ($timezone == 'UP35') ? " selected" : ""; ?>
<option value='UP35'<?php echo $selected; ?>>(UTC + 3:30) Tehran</option>
<?php $selected = ($timezone == 'UP4') ? " selected" : ""; ?>
<option value='UP4'<?php echo $selected; ?>>(UTC + 4:00) Abu Dhabi, Baku, Muscat, Tbilisi</option>
<?php $selected = ($timezone == 'UP45') ? " selected" : ""; ?>
<option value='UP45'<?php echo $selected; ?>>(UTC + 4:30) Kabul</option>
<?php $selected = ($timezone == 'UP5') ? " selected" : ""; ?>
<option value='UP5'<?php echo $selected; ?>>(UTC + 5:00) Islamabad, Karachi, Tashkent</option>
<?php $selected = ($timezone == 'UP55') ? " selected" : ""; ?>
<option value='UP55'<?php echo $selected; ?>>(UTC + 5:30) Bombay, Calcutta, Madras, New Delhi</option>
<?php $selected = ($timezone == 'UP575') ? " selected" : ""; ?>
<option value='UP575'<?php echo $selected; ?>>(UTC + 5:45) Nepal Time</option>
<?php $selected = ($timezone == 'UP6') ? " selected" : ""; ?>
<option value='UP6'<?php echo $selected; ?>>(UTC + 6:00) Almaty, Colombo, Dhaka</option>
<?php $selected = ($timezone == 'UP65') ? " selected" : ""; ?>
<option value='UP65'<?php echo $selected; ?>>(UTC + 6:30) Cocos Islands, Myanmar</option>
<?php $selected = ($timezone == 'UP7') ? " selected" : ""; ?>
<option value='UP7'<?php echo $selected; ?>>(UTC + 7:00) Bangkok, Hanoi, Jakarta</option>
<?php $selected = ($timezone == 'UP8') ? " selected" : ""; ?>
<option value='UP8'<?php echo $selected; ?>>(UTC + 8:00) Beijing, Hong Kong, Perth, Singapore, Taipei</option>
<?php $selected = ($timezone == 'UP875') ? " selected" : ""; ?>
<option value='UP875'<?php echo $selected; ?>>(UTC + 8:45) Australian Central Western Time</option>
<?php $selected = ($timezone == 'UP9') ? " selected" : ""; ?>
<option value='UP9'<?php echo $selected; ?>>(UTC + 9:00) Osaka, Sapporo, Seoul, Tokyo, Yakutsk</option>
<?php $selected = ($timezone == 'UP95') ? " selected" : ""; ?>
<option value='UP95'<?php echo $selected; ?>>(UTC + 9:30) Adelaide, Darwin</option>
<?php $selected = ($timezone == 'UP10') ? " selected" : ""; ?>
<option value='UP10'<?php echo $selected; ?>>(UTC + 10:00) Melbourne, Papua New Guinea, Sydney, Vladivostok</option>
<?php $selected = ($timezone == 'UP105') ? " selected" : ""; ?>
<option value='UP105'<?php echo $selected; ?>>(UTC + 10:30) Lord Howe Island</option>
<?php $selected = ($timezone == 'UP11') ? " selected" : ""; ?>
<option value='UP11'<?php echo $selected; ?>>(UTC + 11:00) Magadan, New Caledonia, Solomon Islands</option>
<?php $selected = ($timezone == 'UP115') ? " selected" : ""; ?>
<option value='UP115'<?php echo $selected; ?>>(UTC + 11:30) Norfolk Island</option>
<?php $selected = ($timezone == 'UP12') ? " selected" : ""; ?>
<option value='UP12'<?php echo $selected; ?>>(UTC + 12:00) Auckland, Wellington, Fiji, Marshall Island</option>
<?php $selected = ($timezone == 'UP1275') ? " selected" : ""; ?>
<option value='UP1275'<?php echo $selected; ?>>(UTC + 12:45) Chatham Islands Standard Time</option>
<?php $selected = ($timezone == 'UP13') ? " selected" : ""; ?>
<option value='UP13'<?php echo $selected; ?>>(UTC + 13:00) Phoenix Islands Time, Tonga</option>
<?php $selected = ($timezone == 'UP14') ? " selected" : ""; ?>
<option value='UP14'<?php echo $selected; ?>>(UTC + 14:00) Line Islands</option>
</select>
</p>


<p>Are you currently observing Daylight Saving Time?<br />
<input  class='radio' type="radio" name="daylight_savings" value="y" <?php echo $dst1; ?> /> Yes &nbsp;&nbsp;<input type="radio"  class='radio' name="daylight_savings" value="n" <?php echo $dst2; ?>  /> No
</p>

</div>

<div class="shade">
<div class="settingHead">Choose your default template design</div>
<p><a href='http://expressionengine.com/templates/themes/category/site_themes/' target="_blank">Browse the templates</a></p>
<p>
<select name='template' class='select'>

<?php

    $system_path = './'.trim($data['system_dir']).'/';
				
	$themes = array();

	if ($fp = @opendir('./themes/site_themes/')) 
	{ 
		while (false !== ($folder = readdir($fp))) 
		{ 
			if (@is_dir('./themes/site_themes/'.$folder) && $folder !== '.' && $folder !== '..' && $folder !== '.svn' && $folder !== '.cvs') 
			{       		
				$themes[] = $folder;
			}
		} 
		closedir($fp); 
		sort($themes);
	}
	
	
	if (count($themes) > 0)
	{              			
		foreach ($themes as $val)
		{
			if ($val == 'rss' || $val == 'search')
				continue;
		
			$nval = str_replace("_", " ", $val);
			$nval = ucwords($nval);
			
			$selected = ($template == $val) ? " selected" : "";
			
			?><option value='<?php echo $val;?>'<?php echo $selected; ?>><?php echo  $nval; ?></option><?php echo "\n";
		}
	}
?>
</select>
</p>

</div>


<p><input type='submit' value=' Click Here to Install ExpressionEngine! '  class='submit'></p>

</form>

</div>

<?php
}



// HTML FOOTER

page_footer();





//  HTML HEADER
// --------------------------------------------------------------------
// --------------------------------------------------------------------

function page_head()
{
	global $data;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US">

<head>
<title>ExpressionEngine | Installation Wizard</title>

<meta http-equiv='content-type' content='text/html; charset=UTF-8' />
<meta http-equiv='expires' content='-1' />
<meta http-equiv= 'pragma' content='no-cache' />

<style type='text/css'>


body {
  margin:             0;
  padding:            0;
  font-family:        Verdana, Geneva, Helvetica, Trebuchet MS, Sans-serif;
  font-size:          12px;
  color:              #333;
  background-color:   #455087;
  }
  
 
a {
  font-size:          12px;
  text-decoration:    underline;
  font-weight:        bold;
  color:              #330099;
  background-color:   transparent;
  }
  
a:visited {
  color:              #330099;
  background-color:   transparent;
  }

a:active {
  color:              #ccc;
  background-color:   transparent;
  }

a:hover {
  color:              #000;
  text-decoration:    none;
  background-color:   transparent;
  }
  
#content {
background:   #fff;
width:        760px;
margin-top: 25px;
margin-right: auto;
margin-left: auto;
border: 1px solid #000;
}

#innercontent {
margin: 20px 30px 0 20px;
}

#pageheader {  
 background: #696EA4 url(./themes/cp_global_images/header_bg.jpg) repeat-x left top;
 border-bottom: 1px solid #000;
}
.solidLine { 
  border-top:  #999 1px solid;
}
.rightheader {  
 background-color:  transparent;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         12px;
 font-weight:		bold;
 color:				#fff;
 padding:			0 22px 0 20px;
}


.error {
  font-family:        Verdana, Trebuchet MS, Arial, Sans-serif;
  font-size:          15px;
  margin-bottom:      8px;
  font-weight:        bold;
  color:              #990000;
  border-bottom: 	  1px solid #990000;
}

.shade {
  background-color:   #f6f6f6;
  padding: 0 0 10px 22px;
  margin-top: 10px;
  margin-bottom: 20px;
  border:      #7B81A9 1px solid;
}

.stephead {
  font-family:        Arial, Trebuchet MS, Verdana, Sans-serif;
  font-size:          18px;
  font-weight:		  bold;
  color:              #999;
  letter-spacing:     2px;
  margin:      			0;
  background-color:   transparent;
}


.settingHead {
  font-family:        Arial, Trebuchet MS, Verdana, Sans-serif;
  font-size:          18px;
  font-weight:		  bold;
  color:              #990000;
  letter-spacing:     2px;
  margin-top:         10px;
  margin-bottom:      10px;
  background-color:   transparent;
}

h1 {
  font-family:        Verdana, Trebuchet MS, Arial, Sans-serif;
  font-size:          16px;
  font-weight:        bold;
  color:              #5B6082;
  margin-top:         15px;
  margin-bottom:      16px;
  background-color:   transparent;
  border-bottom:      #7B81A9 2px solid;
}

h2 {
  font-family:        Arial, Trebuchet MS, Verdana, Sans-serif;
  font-size:          14px;
  color:              #000;
  letter-spacing:     2px;
  margin-top:         6px;
  margin-bottom:      6px;
  background-color:   transparent;
}
h3 {
  font-family:        Arial, Trebuchet MS, Verdana, Sans-serif;
  font-size:          18px;
  color:              #000;
  letter-spacing:     2px;
  margin-top:         15px;
  margin-bottom:      15px;
  border-bottom:      #7B81A9 1px dashed;
  background-color:   transparent;
}

h4 {
  font-family:        Verdana, Geneva, Trebuchet MS, Arial, Sans-serif;
  font-size:          16px;
  font-weight:        bold;
  color:              #000;
  margin-top:         5px;
  margin-bottom:      14px;
  background-color:   transparent;
}
h5 {
  font-family:        Verdana, Geneva, Trebuchet MS, Arial, Sans-serif;
  font-size:          12px;
  font-weight:        bold;
  color:              #000;
  margin-top:         16px;
  margin-bottom:      0;
  background-color:   transparent;
}

p {
  font-family:        Verdana, Geneva, Trebuchet MS, Arial, Sans-serif;
  font-size:          12px;
  font-weight:        normal;
  color:              #333;
  margin-top:         4px;
  margin-bottom:      8px;
  background-color:   transparent;
}

.botBorder {
  margin-bottom:      8px;
  border-bottom:      #7B81A9 1px dashed;
  background-color:   transparent;
}


li {
  font-family:        Verdana, Trebuchet MS, Arial, Sans-serif;
  font-size:          11px;
  margin-bottom:      4px;
  color:              #000;
  margin-left:		  10px;
}

.pad {
padding:  1px 0 4px 0;
}
.center {
text-align: center;
}
strong {
  font-weight: bold;
}

i {
  font-style: italic;
}
  
.red {
  color:              #990000;
}
 
.copyright {
  text-align:         center;
  font-family:        Verdana, Geneva, Helvetica, Trebuchet MS, Sans-serif;
  font-size:          9px;
  color:              #999999;
  line-height:        15px;
  margin-top:         20px;
  margin-bottom:      15px;
  padding:            20px;
  }
  
.border {
  border-bottom:      #7B81A9 1px dashed;
}


form {
 margin:            0;
 padding:           0;
 border:            0;
}
.hidden {
 margin:            0;
 padding:           0;
 border:            0;
}
.input {
 border-top:        2px solid #979AC2;
 border-left:       2px solid #979AC2;
 border-bottom:     1px solid #979AC2;
 border-right:      1px solid #979AC2;
 color:             #333;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 height:            1.7em;
 padding:           0;
 margin:        	0;
} 
.textarea {
 border-top:        2px solid #979AC2;
 border-left:       2px solid #979AC2;
 border-bottom:     1px solid #979AC2;
 border-right:      1px solid #979AC2;
 color:             #333;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 padding:           0;
 margin:        	0;
}
.select {
 background-color:  #fff;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 font-weight:       normal;
 letter-spacing:    .1em;
 color:             #333;
 margin-top:        2px;
 margin-bottom:     2px;
} 
.multiselect {
 border-top:        2px solid #979AC2;
 border-left:       2px solid #979AC2;
 border-bottom:     1px solid #979AC2;
 border-right:      1px solid #979AC2;
 background-color:  #fff;
 color:             #333;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 margin-top:        2px;
 margin-top:        2px;
} 
.radio {
 color:             transparent;
 background-color:  transparent;
 margin-top:        4px;
 margin-bottom:     4px;
 padding:           0;
 border:            0;
}
.checkbox {
 background-color:  transparent;
 color:				transparent;
 padding:           0;
 border:            0;
}
.submit {
 background-color:  #fff;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 font-weight:       normal;
 border-top:		1px solid #989AB6;
 border-left:		1px solid #989AB6;
 border-right:		1px solid #434777;
 border-bottom:		1px solid #434777;
 letter-spacing:    .1em;
 padding:           1px 3px 2px 3px;
 margin:        	0;
 background-color:  #6C73B4;
 color:             #fff;
}  

</style>

</head>

<body>
<div id='content'>
<div id='pageheader'>
<table style="width:100%;" height="50" border="0" cellpadding="0" cellspacing="0">
<tr>
<td style="width:45%;"><img src="./themes/cp_global_images/ee_logo.jpg" width="260" height="80" border="0" alt="ExpressionEngine" /></td>
<td style="width:55%;" align="right" class="rightheader"><?php echo $data['app_full_version']; ?></td>
</tr>
</table>
</div>
<?php
}
/* END */




//  HTML FOOTER
// --------------------------------------------------------------------
// --------------------------------------------------------------------

function page_footer()
{
	global $data;
?>

<div class='copyright'>ExpressionEngine <?php echo $data['app_full_version']; ?> - &#169; Copyright 2003 - 2009 EllisLab, Inc. - All Rights Reserved</div>

</div>

</body>
</html>
<?php
}







// -----------------------------------------
//  Fetch names of installed languages
// -----------------------------------------
	
function language_pack_names($default)
{
	global $data;
	
    $source_dir = './'.trim($data['system_dir']).'/language/';

	$filelist = array();

	if ($fp = @opendir($source_dir)) 
	{ 
		while (false !== ($file = readdir($fp))) 
		{ 
			$filelist[count($filelist)] = $file;
		} 
	} 

	closedir($fp); 
	
	sort($filelist);

	$r  = "<div class='default'>";
	$r .= "<select name='deft_lang' class='select'>\n";
	
	$skip = array('.php', '.html', '.DS_Store', '.');
		
	for ($i =0; $i < sizeof($filelist); $i++) 
	{
		foreach($skip as $a)
		{
			if (stristr($filelist[$i], $a))
			{
				continue(2);
			}
		}
	
		$selected = ($filelist[$i] == $default) ? " selected='selected'" : '';
				
		$r .= "<option value='{$filelist[$i]}'{$selected}>".ucfirst($filelist[$i])."</option>\n";
	}        

	$r .= "</select>";
	$r .= "</div>";

	return $r;
}
/* END */




// -----------------------------------------
//  Member Profile template wrapper
// -----------------------------------------

function member_index()
{
return <<<EOF
{exp:member:manager}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}">
<head>
<title>{page_title}</title>

<meta http-equiv='content-type' content='text/html; charset={charset}' />

{stylesheet}

</head>
<body>

<div id="content">
<div class='header'><h1>{heading}</h1></div>

{breadcrumb}
{content}
{copyright}

</div>

</body>
</html>
{/exp:member:manager}
EOF;
}
/* END */


function license_agreement()
{
?>
<div id='innercontent'>

<h1>ExpressionEngine Installation Wizard</h1>

<h2>License Agreement</h2>
	
<form method='post' action='install.php?page=3'>

<p>
<textarea class="textarea" cols="50" rows="20" style="width:100%;" readonly="readonly"><?php echo trim(license()); ?></textarea>
</p>

<p><input type="radio" name="agree" value="yes" /> I agree to abide by the license Terms and Conditions as stated above.</p>
<p><input type="radio" name="agree" value="no" checked="checked" /> I do NOT agree to abide by the license Terms and Conditions as stated above</p>

<p><br />
<input type='submit' value='Submit' class='submit'>
</p>

</form>

</p>
</div>
<?php
}



function license() {
return <<<EOF
This license is a legal agreement between you and EllisLab, Inc. for the use of ExpressionEngine Software (the "Software"). By downloading ExpressionEngine or any ExpressionEngine Modules you agree to be bound by the terms and conditions of this license. EllisLab, Inc. reserves the right to alter this agreement at any time, for any reason, without notice.

Revised on: 26 March, 2007

PERMITTED USE

* Core License: Users of the Core License may use the Software only on a website engaging in personal, non-commercial, or non-profit activities.

* Personal License: Users of the Personal License may use the Software only on a website engaging in personal, non-commercial, or non-profit activities. One license grants the right to perform one installation of the Software. Each additional installation of the Software requires an additional purchased license.

* Commercial License: Users of the Commercial License may use the Software on a website engaging a commercial, or for-profit activities. Websites engaging in direct, or indirect commercial activities must purchase a Commercial License. One license grants the right to perform one installation of the Software. Each additional installation of the Software requires an additional purchased license.

EllisLab, Inc. will be the sole arbiter as to what constitutes commercial activities.

RESTRICTIONS
Unless you have been granted prior, written consent from EllisLab, Inc., you may not:

* Use the Software as the basis of a hosted weblogging service, or to provide hosting services to others.
* Reproduce, distribute, or transfer the Software, or portions thereof, to any third party.
* Sell, rent, lease, assign, or sublet the Software or portions thereof.
* Grant rights to any other person.
* Use the Software in violation of any U.S. or international law or regulation.

DISPLAY OF COPYRIGHT NOTICES
All copyright and proprietary notices and logos in the Control Panel and within the Software files must remain intact.

GIVING CREDIT
Core License Users are required to display a "powered by ExpressionEngine" link or graphic on their publicly accessible site, pointing to http://expressionengine.com/ . Users of a purchased Personal or Commercial license are exempt from this requirement.

MAKING COPIES
You may make copies of the Software for back-up purposes, provided that you reproduce the Software in its original form and with all proprietary notices on the back-up copy.

SOFTWARE MODIFICATION
You may alter, modify, or extend the Software for your own use, or commission a third-party to perform modifications for you, but you may not resell, redistribute or transfer the modified or derivative version without prior written consent from EllisLab, Inc. Components from the Software may not be extracted and used in other programs without prior written consent from EllisLab, Inc.

TECHNICAL SUPPORT
Technical support is available only through the Online Support Forums at ExpressionEngine.com. EllisLab does not provide direct email or phone support. No representations or guarantees are made regarding the response time in which support questions are answered.

REFUNDS
Due to the non-returnable nature of downloadable software, EllisLab, Inc. does not issue refunds once a transaction has been completed. All software offered by EllisLab is available for free evaluation prior to purchasing. We encourage you to thoroughly test the system you are interested in before purchasing to determine its suitability for your purposes and compatibility with your hosting account.

INDEMNITY
You agree to indemnify and hold harmless EllisLab, Inc. and EngineHosting for any third-party claims, actions or suits, as well as any related expenses, liabilities, damages, settlements or fees arising from your use or misuse of the Software, or a violation of any terms of this license.

DISCLAIMER OF WARRANTY
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, WARRANTIES OF QUALITY, PERFORMANCE, NON-INFRINGEMENT, MERCHANTABILITY, OR FITNESS FOR A PARTICULAR PURPOSE.  FURTHER, ELLISLAB, INC. DOES NOT WARRANT THAT THE SOFTWARE OR ANY RELATED SERVICE WILL ALWAYS BE AVAILABLE.
Limitations Of Liability

YOU ASSUME ALL RISK ASSOCIATED WITH THE INSTALLATION AND USE OF THE SOFTWARE. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS OF THE SOFTWARE BE LIABLE FOR CLAIMS, DAMAGES OR OTHER LIABILITY ARISING FROM, OUT OF, OR IN CONNECTION WITH THE SOFTWARE. LICENSE HOLDERS ARE SOLELY RESPONSIBLE FOR DETERMINING THE APPROPRIATENESS OF USE AND ASSUME ALL RISKS ASSOCIATED WITH ITS USE, INCLUDING BUT NOT LIMITED TO THE RISKS OF PROGRAM ERRORS, DAMAGE TO EQUIPMENT, LOSS OF DATA OR SOFTWARE PROGRAMS, OR UNAVAILABILITY OR INTERRUPTION OF OPERATIONS.
EOF;
}

?>