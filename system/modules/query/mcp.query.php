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
 File: mcp.query.php
-----------------------------------------------------
 Purpose: Query class - CP
=====================================================
*/

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}



class Query_CP {

    var $version = '1.0';


    /** ----------------------------------------
    /**  Module installer
    /** ----------------------------------------*/

    function query_module_install()
    {
        global $DB;        
        
        $sql[] = "INSERT INTO exp_modules (module_id, module_name, module_version, has_cp_backend) VALUES ('', 'Query', '$this->version', 'n')";
    
        foreach ($sql as $query)
        {
            $DB->query($query);
        }
        
        return true;
    }
    /* END */
    
    
    /** ----------------------------------------
    /**  Module de-installer
    /** ----------------------------------------*/

    function query_module_deinstall()
    {
        global $DB;    

        $query = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Query'"); 
                
        $sql[] = "DELETE FROM exp_module_member_groups WHERE module_id = '".$query->row['module_id']."'";        
        $sql[] = "DELETE FROM exp_modules WHERE module_name = 'Query'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Query'";
        $sql[] = "DELETE FROM exp_actions WHERE class = 'Query_CP'";

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