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
 File: mcp.contact_form.php
-----------------------------------------------------
 Purpose: Email class - CP
-----------------------------------------------------
 Last Updated:  2004-03-09 14:27:00 
=====================================================
*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}


class Email_CP {

    var $version = '1.1';
    
    
    function Email_CP( $switch = TRUE )
    {
    }
    /* END */

    /** --------------------------------
    /**  Module installer
    /** --------------------------------*/

    function email_module_install()
    {
        global $DB;        
        
        $sql[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Email', '$this->version', 'n')";
        $sql[] = "INSERT INTO exp_actions (action_id, class, method) VALUES ('', 'Email', 'send_email')";
		$sql[] = "CREATE TABLE IF NOT EXISTS exp_email_tracker (
		email_id int(10) unsigned NOT NULL auto_increment,
		email_date int(10) unsigned default '0' NOT NULL,
		sender_ip varchar(16) NOT NULL,
		sender_email varchar(75) NOT NULL ,
		sender_username varchar(50) NOT NULL ,
		number_recipients int(4) unsigned default '1' NOT NULL,
		PRIMARY KEY (email_id) 
		)";
		
        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
        return true;
    }
    /* END */
    
    
    /** -------------------------
    /**  Module de-installer
    /** -------------------------*/

    function email_module_deinstall()
    {
        global $DB;   
        
        $query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Email'"); 
                
        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";
        $sql[] = "DELETE FROM exp_modules WHERE module_name = 'Email'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Email'";
		$sql[] = "DROP TABLE IF EXISTS exp_email_tracker";
    
        foreach ($sql as $query)
        {
            $DB->query($query);
        }

        return true;
    }
    /* END */


}
// END CLASS
?>