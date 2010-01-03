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
 Purpose: Member Profile Skin Elements
=====================================================
*/

if ( ! defined('EXT')){
	exit('Invalid file request');
}

class profile_theme {




//----------------------------------------
// 	Member Page Outer
//----------------------------------------

function member_page()
{
return <<< EOF
{include:html_header}

{if show_headings}
	{include:page_header}
	{include:page_subheader}
{/if}

<div id="content">
{include:member_manager}
</div>

{include:html_footer}
EOF;
}
/* END */



//-------------------------------------
//  Full Proile with menu
//-------------------------------------

function full_profile()
{
return <<< EOF

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileMenu' style='width:24%' valign='top'>{include:menu}</td>
<td style='width:1%'>&nbsp;</td>
<td class='tableborder' style='width:76%' valign='top'>{include:content}</td>
</tr>
</table>
EOF;
}
/* END */


//-------------------------------------
//  Basic Proile - no menu
//-------------------------------------

function basic_profile()
{
return <<< EOF
{include:content}
EOF;
}
/* END */



//----------------------------------------
// 	HTML Header
//----------------------------------------
function html_header()
{
return <<< EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" lang="{lang}">
<head>
<title>{site_name} | {page_title}</title>
<meta http-equiv="content-type" content="text/html; charset={charset}" />
<meta name='MSSmartTagsPreventParsing' content='TRUE' />
<meta http-equiv='expires' content='-1' />
<meta http-equiv= 'pragma' content='no-cache' />
<meta name='robots' content='all' />

{include:head_extra}

<style type='text/css'>{include:stylesheet}</style>

</head>

<body>
EOF;
}
/* END */


//----------------------------------------
//  Page Header
//----------------------------------------

function page_header()
{
return <<< EOF
<div id='pageheader'>
<table style="width:100%;" border="0" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="heading">{page_title}</div>
</td>
</tr>
</table>
</div>
EOF;
}
/* END */




//----------------------------------------
// 	Page Sub-Header
//----------------------------------------

function page_subheader()
{
return <<< EOF
<div id="subheader">
{include:breadcrumb}
</div>
EOF;
}
/* END */



//----------------------------------------
// HTML Footer
//----------------------------------------
function html_footer()
{
return <<< EOF
<div id="footer">
{lang:elapsed_time}<br />
<a href="http://expressionengine.com/">{lang:powered_by_ee}</a>
</div>

</body>
</html>
EOF;
}
/* END */




//-------------------------------------
//  breadcrumb
//-------------------------------------

function breadcrumb()
{
return <<< EOF
<table border='0' cellpadding='0' cellspacing='0' style="width:99%;">
<tr>
<td>
<div class='breadcrumb'>{breadcrumb_links}</div>
</td>

{if logged_in}
<td align="right">

{lang:logged_in_as}&nbsp; <span class="defaultBold"><a href="{profile_path=member/index}">{name}</a></span>

&nbsp;|&nbsp;

<span class="default"><a href="{path=member/profile}">{lang:your_control_panel}</a></span>

&nbsp;|&nbsp;

<span class="default"><a href="{path=member/memberlist}">{lang:memberlist}</a></span>

&nbsp;|&nbsp;

<span class="default"><a href="{path="LOGOUT"}">{lang:logout}</a></span>

&nbsp;&nbsp;

</td>
{/if}

</tr>
</table>
EOF;
}
/* END */


//----------------------------------------
// 	Breadcurmb trail
//----------------------------------------

function breadcrumb_trail()
{
return <<< EOF
<a href="{crumb_link}">{crumb_title}</a><span class="breadcrumbspacer">&nbsp;&nbsp;&gt;&nbsp;&nbsp;</span>
EOF;
}
/* END */


//----------------------------------------
// 	Breadcurmb Current Page
//----------------------------------------

function breadcrumb_current_page()
{
return <<< EOF
<span class="currentcrumb">{crumb_title}</span>
EOF;
}
/* END */


//-------------------------------------
//  copyright
//-------------------------------------

function copyright()
{
return <<< EOF
<div class='copyright'><a href="http://expressionengine.com/">{lang:powered_by_ee}</a><br />{elapsed_time}</div>
EOF;
}
/* END */


//-------------------------------------
//  menu
//-------------------------------------

function menu()
{
return <<< EOF

<div class='menuHeadingBG'><div class="tableHeading">{lang:menu}</div></div>

<div class='borderBot'><div class='profileHead'>{lang:personal_settings}</div></div>

<div class='profileMenuInner'>
<div class='menuItem'><a href='{path:profile}'>{lang:edit_profile}</a></div>
<div class='menuItem'><a href='{path:signature}'>{lang:edit_signature}</a></div>
<div class='menuItem'><a href='{path:avatar}'>{lang:edit_avatar}</a></div>
<div class='menuItem'><a href='{path:photo}'>{lang:edit_photo}</a></div>
<div class='menuItem'><a href='{path:email}'>{lang:email_settings}</a></div>
<div class='menuItem'><a href='{path:username}'>{lang:username_and_password}</a></div>
<div class='menuItem'><a href='{path:edit_preferences}'>{lang:edit_preferences}</a></div>

{if allow_localization}
<div class='menuItem'><a href='{path:localization}'>{lang:localization}</a></div>
{/if}

{if can_delete}
<div class="menuItem"><a href="{path:delete}">{lang:mbr_delete}</a></div>
{/if}
</div>

<div class='borderTopBot'><div class='profileHead'>{lang:utilities}</div></div>

<div class='profileMenuInner'>
<div class='menuItem'><a href='{path:subscriptions}' >{lang:edit_subscriptions}</a></div>
<div class='menuItem'><a href='{path:ignore_list}' >{lang:ignore_list}</a></div>
</div>

{include:messages_menu}


<div class='borderTopBot'><div class='profileHead'>{lang:extras}</div></div>

<div class='profileMenuInner'>
<div class='menuItem'><a href='{path:notepad}' >{lang:notepad}</a></div>
</div>

EOF;
}
/* END */


//-------------------------------------
//  success
//-------------------------------------

function success()
{
return <<< EOF
<div class='profileHeadingBG'><div class="tableHeading">{lang:heading}</div></div>
<div class='tableCellOne'><div class='success'>{lang:message}</div></div>
EOF;
}
/* END */



//-------------------------------------
//  Error
//-------------------------------------

function error()
{
return <<< EOF
<div class='profileHeadingBG'><div class="tableHeading">{lang:heading}</div></div>
<div class='tableCellOne'><div class='highlight'>{lang:message}</div></div>
EOF;
}
/* END */



//-------------------------------------
//  Profile Home page
//-------------------------------------

function home_page()
{
return <<< EOF
<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:your_stats}</div></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:email}</div></td>
<td class='tableCellTwo'><a href='mailto:{email}'><b>{email}</b></a></td>

</tr><tr>

<td class='tableCellOne'><div class='defaultBold'>{lang:join_date}</div></td>
<td class='tableCellOne'>{join_date}</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:last_visit}</div></td>
<td class='tableCellTwo'>{last_visit_date}</td>

{if forum_installed}

</tr><tr>

<td class='tableCellOne'><div class='defaultBold'>{lang:most_recent_forum_post}</div></td>
<td class='tableCellOne'>{recent_forum_post_date}</td>

{/if}

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:most_recent_entry}</div></td>
<td class='tableCellTwo'>{recent_entry_date}</td>

</tr><tr>

<td class='tableCellOne'><div class='defaultBold'>{lang:most_recent_comment}</div></td>
<td class='tableCellOne'>{recent_comment_date}</td>

{if forum_installed}

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:total_forum_topics}</div></td>
<td class='tableCellTwo'>{total_topics}</td>

</tr><tr>

<td class='tableCellOne'><div class='defaultBold'>{lang:total_forum_replies}</div></td>
<td class='tableCellOne'>{total_replies}</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:total_forum_posts}</div></td>
<td class='tableCellTwo'>{total_posts}</td>

{/if}

</tr><tr>

<td class='tableCellOne'><div class='defaultBold'>{lang:total_entries}</div></td>
<td class='tableCellOne'>{total_entries}</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:total_comments}</div></td>
<td class='tableCellTwo'>{total_comments}</td>

</tr>
</table>
EOF;
}
/* END */



//-------------------------------------
//  public_profile
//-------------------------------------

function public_profile()
{
return <<< EOF
<table class='tableborder' border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileTopBox' valign='top' style="width:50%;">
	
	<table border='0' cellspacing='0' cellpadding='0' >
	<tr>
	{if avatar}<td style="width:{avatar_width}px"><img class="avatar" src="{path:avatar_url}" width="{avatar_width}" height="{avatar_height}" border="0" alt="{name}" title="{name}" /></td>{/if}
	<td valign="top">
	<div class='profileTitle'>{name}</div>			
	<div class='itempad'>{lang:member_group}&nbsp; <b>{member_group}</b></div>
	<div class='itempadbig'><a href="{search_path}"><b>{lang:view_posts_by_member}</b></a></div>
	{if ignore}
	<div class='itempad'><b>{ignore_link}</b></div>
	{/if}
	</td>
	</tr>
	</table>

</td>
	<td class='profilePhoto' valign='middle'  style="width:50%;">
	<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
	<tr>
	<td>
	<div class="defaultCenter">
	{if photo}
		<div class="itempad"><img src="{path:photo_url}" class="photo" width="{photo_width}" height="{photo_height}" border="0" title="{name}" /></div>
		<div class="lighttext">{lang:my_photo}</div>
	{/if}
	{if not_photo}
		<div class="itempad"><img src="{path:image_url}icon_profile.gif" width="50" height="50" border="0" title="{name}" alt="{lang:no_photo}" /></div>
		<div class="lighttext">{lang:no_photo_exists}</div>
	{/if}
	</div>
	</td>
	</tr>
	</table>
</td>
</tr>
</table>


<div class='itempad'>&nbsp;</div>


<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td style="width:49%;" valign='top'>

<table class='tableborder' border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='tableRowHeadingBold' colspan='2'>{lang:communications}</td>
</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:url}</div></td>
<td class='tableCellOne'>{if url}<a href="{url}" target="_blank"><img src="{path:image_url}icon_www.gif" width="56" height="14" alt="{url}" title="{url}" border="0" /></a>{/if}</td>

</tr><tr>

<td class='tableCellTwo' style="width:42%;"><div class='defaultBold'>{lang:email}</div></td>
<td class='tableCellOne' style="width:58%;">
{if accept_email}
<a href="#" {email_console}><img src="{path:image_url}icon_email.gif" width="56" height="14" alt="{lang:email_console}" title="{lang:email_console}" border="0" /></a>
{/if}
</td>

</tr>

{if can_private_message}
<tr>

<td class='tableCellTwo' style="width:42%;"><div class='defaultBold'>{lang:private_message}</div></td>
<td class='tableCellOne' style="width:58%;">
<a href="{send_private_message}"><img src="{path:image_url}icon_pm.gif" width="56" height="14" alt="{lang:send_pm}" title="{lang:send_pm}" border="0" /></a>
</td>

</tr>
{/if}
<tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:aol_im}</div></td>
<td class='tableCellOne'>
{if aol_im}
<a href="#" {aim_console}><img src="{path:image_url}icon_aim.gif" width="56" height="14" border="0" alt="{lang:mbr_aim_console}" title="{lang:mbr_aim_console}" /></a>
{/if}
</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:icq}</div></td>
<td class='tableCellOne'>
{if icq}
<a href="#" {icq_console}><img src="{path:image_url}icon_icq.gif" width="56" height="14" border="0" alt="{lang:mbr_icq}" title="{lang:mbr_icq}" /></a>
{/if}
</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:yahoo}</div></td>
<td class='tableCellOne'>
{if yahoo_im}
<a href="{yahoo_console}" target="_blank"><img src="{path:image_url}icon_yim.gif" width="56" height="14" border="0" alt="{lang:mbr_yahoo}" title="{lang:mbr_yahoo}"></a>
{/if}
</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:msn}</div></td>
<td class='tableCellOne'>{msn_im}</td>

</td>
</tr>
</table>



<div class='itempad'>&nbsp;</div>

<table class='tableborder' border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='tableRowHeadingBold' colspan='2'>{lang:personal_info}</td>
</tr><tr>

<td class='tableCellTwo' style="width:42%;"><div class='defaultBold'>{lang:location}</div></td>
<td class='tableCellOne' style="width:58%;"><div class='default'>{location}</div></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:occupation}</div></td>
<td class='tableCellOne'><div class='default'>{occupation}</div></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:interests}</div></td>
<td class='tableCellOne'><div class='default'>{interests}</div></td>

{custom_profile_fields}

</tr>
</table>



</td>
<td style="width:2%;">&nbsp;</td>

<td style="width:49%;" valign='top'>


<table class='tableborder' border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='tableRowHeadingBold' colspan='2'>{lang:statistics}</td>

</tr><tr>

<td class='tableCellTwo' style="width:46%;"><div class='defaultBold'>{lang:member_group}</div></td>
<td class='tableCellOne' style="width:54%;"><div class='default'>{member_group}</div></td>


</tr><tr>

<td class='tableCellTwo' style="width:46%;"><div class='defaultBold'>{lang:total_entries}</div></td>
<td class='tableCellOne' style="width:54%;"><div class='default'>{total_entries}</div></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:total_comments}</div></td>
<td class='tableCellOne'><div class='default'>{total_comments}</div></td>

{if forum_installed}

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:total_forum_topics}</div></td>
<td class='tableCellOne'><div class='default'>{total_forum_topics}</div></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:total_forum_replies}</div></td>
<td class='tableCellOne'><div class='default'>{total_forum_replies}</div></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:total_forum_posts}</div></td>
<td class='tableCellOne'><div class='default'>{total_forum_posts}</div></td>

{/if}

</tr><tr>

<td class='tableCellTwo' style="width:46%;"><div class='defaultBold'>{lang:member_local_time}</div></td>
<td class='tableCellOne' style="width:54%;">{local_time format="%F %d, %Y &nbsp;%h:%i %A"}</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:last_visit}</div></td>
<td class='tableCellOne'>{last_visit format="%F %d, %Y &nbsp;%h:%i %A"}</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:join_date}</div></td>
<td class='tableCellOne'>{join_date format="%F %d, %Y &nbsp;%h:%i %A"}</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:most_recent_entry}</div></td>
<td class='tableCellOne'>{last_entry_date format="%F %d, %Y &nbsp;%h:%i %A"}</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:most_recent_comment}</div></td>
<td class='tableCellOne'>{last_comment_date format="%F %d, %Y &nbsp;%h:%i %A"}</td>


{if forum_installed}

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:most_recent_forum_post}</div></td>
<td class='tableCellOne'>{last_forum_post_date format="%F %d, %Y &nbsp;%h:%i %A"}</td>

{/if}

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:birthday}</div></td>
<td class='tableCellOne'>{birthday}</td>

</td>
</tr>
</table>

</td>
</tr>
</table>


<div class='itempad'>&nbsp;</div>


<table class='tableborder' border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
	<td class='tableRowHeadingBold'>{lang:bio}</td>
</tr><tr>
	<td>
		{if bio != ''}
		<div class="leftpad">{bio}</div>
		{/if}
		
		{if bio == ''}
		<div class="itempadbig"><div class="lighttext">&nbsp;{lang:no_info_available}</div></div>
		{/if}
	</td>
</tr>
</table>


EOF;
}
/* END */




//-------------------------------------
//  public_custom_profile_fields
//-------------------------------------

function public_custom_profile_fields()
{
return <<< EOF

</tr><tr>

<td class='tableCellTwo' valign='top'><div class='defaultBold'>{field_name}</div>{if field_description}<div class='default'>{field_description}</div>{/if}</td>
<td class='tableCellOne' valign='top'>{field_data}</td>

EOF;
}
/* END */



/* -------------------------------------
/*  delete_confirmation_form
/* -------------------------------------*/

function delete_confirmation_form()
{
return <<< EOF

{form_declaration}

<table class="tableborder" cellpadding="0" cellspacing="0" border="0" style="width:560px;" align="center">
<tr>
	<td class="profileAlertHeadingBG" colspan="2">{lang:mbr_delete}</td>
</tr>
<tr>
	<td class="tableRowHeadingBold" colspan="2">{lang:confirm_password}</td>
</tr>
<tr>
	<td class="tableCellOne" align="right"><b>{lang:password}</b></td>
	<td class="tableCellOne"><input type="password" style="width:80%" class="input" name="password" size="20" value="" maxlength="32" /></td>
</tr>
<tr>
	<td class="tableCellOne" colspan="2">
		<div class="itempadbig">{lang:mbr_delete_blurb}</div>
		<div class="itempadbig alert">{lang:mbr_delete_warning}</div>
	</td>
</tr>
<tr>
	<td class="tableCellTwo" colspan="2"><div class="itempadbig"><input type="submit" class="submit" value="{lang:submit}" /></div></td>
</tr>
</table>

</form>

EOF;
}
/* END */



//-------------------------------------
//  login_form
//-------------------------------------

function login_form()
{
return <<< EOF
{form_declaration}

<table class="tableborder" cellpadding="0" cellspacing="0" border="0" style="width:560px;" align="center">
<tr>
<td class="profileHeadingBG" colspan="2"><div class="tableHeading">{lang:login_required}</div>
</td>

</tr><tr>

<td class="tableRowHeadingBold" colspan="2">{lang:must_be_logged_in}</td>

</tr><tr>

<td class="tableCellOne" align="right"><div class="itempadbig"><a href="{path:register}">{lang:member_registration}</a></div></td>
<td class="tableCellOne"><div class="itempadbig"><a href="{path:forgot}">{lang:forgot_password}</a></div></td>

</tr><tr>

<td class="tableCellTwo" align="right" style="width:35%;"><b>{lang:username}</b></td>
<td class="tableCellTwo" style="width:75%;"><input type="text" style="width:80%" class="input" name="username" size="20" value="" maxlength="50" /></td>

</tr><tr>

<td class="tableCellTwo" align="right"><b>{lang:password}</b></td>
<td class="tableCellTwo"><input type="password" style="width:80%" class="input" name="password" size="20" value="" maxlength="32" /></td>

</tr><tr>

<td class="tableCellOne" align="right"><input type="submit" class="submit" value="{lang:submit}" /></td>
<td class="tableCellOne">
<input type="checkbox" class="checkbox" name="auto_login" value="1" checked="checked" /> {lang:auto_login}<br />
<input type="checkbox" class="checkbox" name="anon" value="1" checked="checked" /> {lang:show_name}
</td>

</tr>
</table>

</form>

EOF;
}
/* END */



//-------------------------------------
//  forgot_form
//-------------------------------------

function forgot_form()
{
return <<< EOF
{form_declaration}

<table class="tableborder" cellpadding="0" cellspacing="0" border="0" style="width:560px;" align="center">
<tr>
<td class="profileHeadingBG" colspan="2"><div class="tableHeading">{lang:forgotten_password}</div>
</td>

</tr><tr>

<td class="tableCellOne" colspan="2">


<h3>{lang:your_email}</h3>

<p><input type="text" name="email" value="" class="input" maxlength="120" size="40" style="width:100%" /></p>

<p><input type="submit" value="{lang:submit}" class="submit" /></p>

<p><br /><a href="{path:login}">{lang:back_to_login}</a>

</td>
</tr>
</table>

</form>

EOF;
}
/* END */



//-------------------------------------
//  update username/password form
//-------------------------------------

function update_un_pw_form()
{
return <<< EOF
{form_declaration}

<table class="tableborder" cellpadding="0" cellspacing="0" border="0" style="width:700px;" align="center">
<tr>
<td class="profileHeadingBG" colspan="2"><div class="tableHeading">{lang:settings_update}</div>
</td>

</tr><tr>

<td class="tableCellOne" colspan="2"><div class="itempadbig"><div class="alert">{lang:access_notice}</div></div></td>

</tr><tr>

<td class="tableCellOne" colspan="2"><div class="itempadbig">

{if invalid_username}<div class="itempad"><div class="highlight">{lang:username_length}</div></div>{/if}
{if invalid_password}<div class="itempad"><div class="highlight">{lang:password_length}</div></div>{/if}
</td>

</tr><tr>

{if invalid_username}
	<td class="tableCellTwo" align="right" style="width:35%;"><b>{lang:choose_new_un}</b></td>
	<td class="tableCellTwo" style="width:75%;"><input type="text" style="width:80%" class="input" name="new_username" size="20" value="" maxlength="50" /></td>
	</tr><tr>
{/if}

{if invalid_password}
	<td class="tableCellTwo" align="right"><b>{lang:choose_new_pw}</b></td>
	<td class="tableCellTwo"><input type="password" style="width:80%" class="input" name="new_password" size="20" value="" maxlength="32" /></td>
	</tr><tr>
	
	<td class="tableCellTwo" align="right"><b>{lang:confirm_new_pw}</b></td>
	<td class="tableCellTwo"><input type="password" style="width:80%" class="input" name="new_password_confirm" size="20" value="" maxlength="32" /></td>
	</tr><tr>
{/if}

</tr>
</table>

<div class="spacer"></div>

<table class="tableborder" cellpadding="0" cellspacing="0" border="0" style="width:700px;" align="center">
<tr>
<td class="profileHeadingBG" colspan="2"><div class="tableHeading">{lang:your_current_un_pw}</div>
</td>

</tr><tr>

<td class="tableCellTwo" align="right" style="width:35%;"><b>{lang:existing_username}</b></td>
<td class="tableCellTwo" style="width:75%;"><input type="text" style="width:80%" class="input" name="username" size="20" value="" maxlength="50" /></td>

</tr><tr>

<td class="tableCellTwo" align="right"><b>{lang:existing_password}</b></td>
<td class="tableCellTwo"><input type="password" style="width:80%" class="input" name="password" size="20" value="" maxlength="32" /></td>

</tr><tr>

<td class="tableCellOne" align="right"><div class="itempadbig"><input type="submit" class="submit" value="{lang:submit}" /></div></td>
<td class="tableCellTwo">&nbsp;</td>
</tr>
</table>

</form>

EOF;
}
/* END */




//-------------------------------------
//  edit_profile_form
//-------------------------------------

function edit_profile_form()
{
return <<< EOF

<form method="post" action="{path:update_profile}">

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG'colspan='2'><div class="tableHeading">{lang:edit_your_profile}</div></td>

</tr><tr>

<td class='tableCellTwo' style="width:25%;"><div class='defaultBold'>{lang:url}</div></td>
<td class='tableCellOne' style="width:75%;"><input type='text' class='input' name='url' value='{url}' maxlength='75' style='width:100%'/></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:birthday}</div></td>
<td class='tableCellOne'>{form:birthday_year} {form:birthday_month} {form:birthday_day}</td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:location}</div></td>
<td class='tableCellOne'><input type='text' class='input' name='location' value='{location}' maxlength='50' style='width:100%'/></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:occupation}</div></td>
<td class='tableCellOne'><input type='text' class='input' name='occupation' value='{occupation}' maxlength='80' style='width:100%'/></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:interests}</div></td>
<td class='tableCellOne'><input type='text' class='input' name='interests' value='{interests}' maxlength='120' style='width:100%'/></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:aol_im}</div></td>
<td class='tableCellOne'><input type='text' class='input' name='aol_im' value='{aol_im}' maxlength='50' style='width:100%'/></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:icq}</div></td>
<td class='tableCellOne'><input type='text' class='input' name='icq' value='{icq}' maxlength='50' style='width:100%'/></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:yahoo_im}</div></td>
<td class='tableCellOne'><input type='text' class='input' name='yahoo_im' value='{yahoo_im}' maxlength='50' style='width:100%'/></td>

</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'>{lang:msn_im}</div></td>
<td class='tableCellOne'><input type='text' class='input' name='msn_im' value='{msn_im}' maxlength='50' style='width:100%'/></td>

</tr><tr>

<td class='tableCellTwo' valign='top'><div class='defaultBold'>{lang:bio}</div></td>
<td class='tableCellOne'><textarea name='bio' style='width:100%' class='textarea' rows='12' cols='90'>{bio}</textarea></td>


{custom_profile_fields}


<tr>
<td class='tableCellTwo' colspan="2">
<div class='marginpad'>
<input type='submit' class='submit' value='{lang:update}' />
<br /><br />
<span class="alert">*</span> {lang:required}
</div>

</td>
</tr>
</table>

</form>
EOF;
}
/* END */




//-------------------------------------
//  custom_profile_fields
//-------------------------------------

function custom_profile_fields()
{
return <<< EOF

</tr><tr>

<td class='tableCellTwo' style="width:25%;"><div class='defaultBold'><label for="m_field_id_{field_id}">{lang:profile_field}</label></div><div class='default'>{lang:profile_field_description}</div></td>
<td class='tableCellOne' style="width:75%;">{form:custom_profile_field}</td>

EOF;
}
/* END */




//-------------------------------------
//  Edit Preferences
//-------------------------------------

function edit_preferences()
{
return <<< EOF

<form method="post" action="{path:update_edit_preferences}">

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:edit_preferences}</div></td>

</tr><tr>
<td class='tableCellOne' colspan='2'><div class='defaultBold'><input type='checkbox' name='accept_messages' value='y' {state:accept_messages} />&nbsp;&nbsp;{lang:accept_messages}</div></td>

</tr><tr>
<td class='tableCellOne' colspan='2'><div class='defaultBold'><input type='checkbox' name='display_avatars' value='y' {state:display_avatars} />&nbsp;&nbsp;{lang:display_avatars}</div></td>

</tr><tr>
<td class='tableCellOne' colspan='2'><div class='defaultBold'><input type='checkbox' name='display_signatures' value='y' {state:display_signatures} />&nbsp;&nbsp;{lang:display_signatures}</div></td>

</tr><tr>
<td class='tableCellOne' colspan='2'>

<div class='marginpad'>
<input type='submit' class='submit' value='{lang:update}' />
</div>

</td>
</tr>
</table>

</form>
EOF;
}
/* END */




//-------------------------------------
//  email_prefs_form
//-------------------------------------

function email_prefs_form()
{
return <<< EOF
<form method="post" action="{path:update_email_settings}">

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:email_settings}</div></td>

</tr><tr>

<td class='tableCellTwo' style="width:30%;"><div class='defaultBold'>{lang:email}</div></td>
<td class='tableCellTwo' style="width:70%;"><input type='text' class='input' name='email' value='{email}' maxlength='75' style='width:100%'/></td>

</tr><tr>
<td class='tableCellOne' colspan='2'><div class='defaultBold'><input type='checkbox' name='accept_admin_email' value='y' {state:accept_admin_email} />&nbsp;&nbsp;{lang:accept_admin_email}</div></td>

</tr><tr>
<td class='tableCellOne' colspan='2'><div class='defaultBold'><input type='checkbox' name='accept_user_email' value='y' {state:accept_user_email} />&nbsp;&nbsp;{lang:accept_user_email}</div></td>

</tr><tr>
<td class='tableCellOne' colspan='2'><div class='defaultBold'><input type='checkbox' name='notify_by_default' value='y' {state:notify_by_default} />&nbsp;&nbsp;{lang:notify_by_default}</div></td>

</tr><tr>
<td class='tableCellOne' colspan='2'><div class='defaultBold'><input type='checkbox' name='notify_of_pm' value='y' {state:notify_of_pm} />&nbsp;&nbsp;{lang:notify_of_pm}</div></td>

</tr><tr>
<td class='tableCellOne' colspan='2'><div class='defaultBold'><input type='checkbox' name='smart_notifications' value='y' {state:smart_notifications} />&nbsp;&nbsp;{lang:enable_smart_notifications}</div></td>

</tr><tr>

<td class='tableCellOne' colspan='2'>
<div class='itempadbig'>
<div class='defaultBold'><div class="itempad"><span class='alert'>*</span>&nbsp; {lang:existing_password}</div></div>
<div class='default'><div class="itempad"><span class='highlight'>{lang:existing_password_email}</span></div></div>
<input type='password' class='input' name='password' value='' maxlength='32' style='width:300px'/>
</div>
</td>

</tr><tr>

<td class='tableCellOne' colspan='2'>

<div class='marginpad'>
<input type='submit' class='submit' value='{lang:update}' />
<br /><br />
<span class="alert">*</span> {lang:required}
</div>

</td>
</tr>
</table>

</form>
EOF;
}
/* END */




//-------------------------------------
//  username_password_form
//-------------------------------------

function username_password_form()
{
return <<< EOF

<form method="post" action="{path:update_username_password}">

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:username_and_password}</div></td>

{row:username_form}


</tr><tr>

<td class='tableCellTwo'><div class='defaultBold'><span class='alert'>*</span>&nbsp; {lang:screen_name}</div></td>
<td class='tableCellTwo'><input type='text' class='input' name='screen_name' value='{screen_name}' maxlength='50' style='width:250px'/></td>

</tr><tr>

<td class='tableCellOne' colspan='2'>
<div class="itempadbig">
<div class='defaultBold'>{lang:password_change}</div>
<div class="itempad"><div class='highlight'>{lang:password_change_exp}</div></div>

<div class='defaultBold'>{lang:new_password}</div>
<input style='width:250px' type='password' name='password' value='' size='35' maxlength='32' class='input' />

<div class='defaultBold'>{lang:new_password_confirm}</div>
<input style='width:250px' type='password' name='password_confirm' value='' size='35' maxlength='32' class='input' />
</div>
</td>

</tr><tr>

<td class='tableCellOne' colspan='2'>
<div class="itempadbig">

<div class='defaultBold'><div class="itempad"><span class='alert'>*</span>&nbsp; {lang:existing_password}</div></div>
<div class='default'><div class="itempad"><span class='highlight'>{lang:existing_password_exp}</span></div></div>
<input type='password' class='input' name='current_password' value='' maxlength='32' style='width:300px'/>
</div>
</td>


</tr><tr>

<td class='tableCellTwo' colspan='2'>
<div class='marginpad'>

<input type='submit' class='submit' value='{lang:update}' />

<br /><br />

<span class="alert">*</span> {lang:required}

</div>
</td>
</tr>
</table>

</form>
EOF;
}
/* END */




//-------------------------------------
//  username_row
//-------------------------------------

function username_row()
{
return <<< EOF
</tr><tr>

<td class='tableCellTwo' style="width:30%;"><div class='defaultBold'><span class="alert">*</span>&nbsp; {lang:username}</div></td>
<td class='tableCellTwo'><input type='text' class='input' name='username' value='{username}' maxlength='50' size='35'style='width: 250px'/></td>
EOF;
}
/* END */




//-------------------------------------
//  username_change_disallowed
//-------------------------------------

function username_change_disallowed()
{
return <<< EOF

</tr><tr>

<td class='tableCellTwo' colspan='2' style="width:100%;">{lang:username_disallowed}</td>

EOF;
}
/* END */




//-------------------------------------
//  password_change_warning
//-------------------------------------

function password_change_warning()
{
return <<< EOF

<div class='alert'><br />{lang:password_change_warning}</div>

EOF;
}
/* END */




//-------------------------------------
//  localization_form
//-------------------------------------

function localization_form()
{
return <<< EOF

<form method="post" action="{path:update_localization}">

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:localization_settings}</div></td>

</tr><tr>

<td class='tableCellOne' style="width:30%;"><div class='defaultBold'>{lang:timezone}</div></td>
<td class='tableCellTwo' style="width:70%;">{form:localization}</td>

</tr><tr>

<td class='tableCellOne'>&nbsp;</td>
<td class='tableCellTwo' style="width:70%;">
<div class='defaultBold'><input type='checkbox' name='daylight_savings' value='y' {state:daylight_savings} /> {lang:daylight_savings_time}</div></td>

</tr><tr>

<td class='tableCellOne' style="width:30%;"><div class='defaultBold'>{lang:time_format}</div></td>
<td class='tableCellTwo' style="width:70%;">{form:time_format}</td>

</tr><tr>

<td class='tableCellOne' style="width:30%;"><div class='defaultBold'>{lang:language}</div></td>
<td class='tableCellTwo' style="width:70%;">{form:language}</td>

</tr><tr>

<td class='tableCellOne' colspan='2'>

<div class='marginpad'>

<input type='submit' class='submit' value='{lang:update}' />

</div>

</td>
</tr>
</table>

</form>
EOF;
}
/* END */




//-------------------------------------
//  notepad_form
//-------------------------------------

function notepad_form()
{
return <<< EOF

<form method="post" action="{path:update_notepad}">

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:notepad}</div></td>

</tr><tr>

<td class='tableCellOne' colspan='2'>{lang:notepad_blurb}</td>

</tr><tr>

<td class='tableCellTwo' colspan='2'><textarea name='notepad' style='width:100%' class='textarea' rows='{notepad_size}' cols='90'>{notepad_data}</textarea></td>

</tr><tr>

<td class='tableCellTwo' style="width:30%;"><div class='defaultBold'>{lang:notepad_size}</div></td>
<td class='tableCellTwo' style="width:70%;"><input type='text' class='input' name='notepad_size' value='{notepad_size}' maxlength='2' style='width:60px'/></td>

</tr><tr>

<td class='tableCellOne' colspan='2'>

<div class='marginpad'>

<input type='submit' class='submit' value='{lang:update}' />

</div>

</td>
</tr>
</table>

</form>
EOF;
}
/* END */




//-------------------------------------
//  signature form
//-------------------------------------

function signature_form()
{
return <<< EOF

<script type="text/javascript">
<!--

function textcounter()
{
	var max		= {maxchars};
	var base	= document.forms.submit_post;
	var cur		= base.body.value.length;

	if (cur > max)
	{
		base.body.value = base.body.value.substring(0, max);
	} 
	else
	{
		base.charsleft.value = max - cur
	}
}

//-->
</script>


{form_declaration}

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:edit_signature}</div></td>

</tr><tr>

<td class="tableCellTwo" style="width:18%;" valign="middle" align="right">
<div class='buttonMode'>{lang:guided}&nbsp;<input type='radio' name='mode' value='guided' onclick='setmode(this.value)' />&nbsp;{lang:normal}&nbsp;<input type='radio' name='mode' value='normal' onclick='setmode(this.value)' checked='checked'/></div></td>
<td class="tableCellTwo" style="width:82%;"><div class="itempadbig">{include:html_formatting_buttons}</div></td>

</tr><tr>

<td class="tableCellTwo" style="width:18%;" valign="middle" align="right"><div class='buttonMode'>{lang:font_formatting}</div></td>

<td class="tableCellTwo" style="width:82%;">

<select name="size" class="select" onchange="selectinsert(this, 'size')" >
<option value="0">{lang:size}</option>
<option value="1">{lang:small}</option>
<option value="3">{lang:medium}</option>
<option value="4">{lang:large}</option>
<option value="5">{lang:very_large}</option>
<option value="6">{lang:largest}</option>
</select>

<select name="color" class="select" onchange="selectinsert(this, 'color')">
<option value="0">{lang:color}</option>
<option value="blue">{lang:blue}</option>
<option value="green">{lang:green}</option>
<option value="red">{lang:red}</option>
<option value="purple">{lang:purple}</option>
<option value="orange">{lang:orange}</option>
<option value="yellow">{lang:yellow}</option>
<option value="brown">{lang:brown}</option>
<option value="pink">{lang:pink}</option>
<option value="gray">{lang:grey}</option>
</select>
</td>

</tr><tr>

<td class="tableCellTwo" style="width:18%;" valign="top" align="right">
<div><b>{lang:signature}</b></div>
<div class="itempadbig"><br /><a href='#' onclick="window.open('{path:smileys}', '_blank', 'width=700,height=220,scrollbars=yes,status=yes,screenx=40,screeny=120,resizable=yes');">{lang:smileys}</a></div>
</td>

<td class="tableCellTwo" style="width:82%;"><textarea name='body' style='width:100%' class='textarea' rows='8' cols='90' onkeydown="textcounter();" onkeyup="textcounter();" >{signature}</textarea></td>


</tr><tr>
<td class="tableCellTwo" style="width:18%;" align="right"><b>{lang:max_characters}</b></td>
<td class="tableCellTwo" style="width:82%;"><input type="text" class="input" name="charsleft" size="5" maxlength="4" value="{maxchars}" readonly="readonly"/></td>


{if image}
</tr><tr>
<td class='tableCellTwo' style="width:18%;" align="right"><div class="itempadbig"><div class='defaultBold'>{lang:signature_image}</div></div></td>
<td class='tableCellTwo' style="width:82%;">
<div class="itempad"><img src="{path:signature_image}" border="0" width="{signature_image_width}" height="{signature_image_height}" title="{lang:signature_image}" /></div>
{/if}

</tr><tr>
<td class="tableCellTwo" style="width:18%;" align="right" valign="top">
<div class="itempadbig"><b>{lang:upload_image}</b></div>
</td>
<td class="tableCellTwo" style="width:82%;">
	{if upload_allowed}
		<div class="itempad"><input type="file" name="userfile" size="20" class="input" /></div>
		<div class="lighttext">{lang:max_image_size}</div>
		<div class="lighttext">{lang:allowed_image_types}</div>
	{/if}
	{if upload_not_allowed}
		<div class="lighttext">{lang:uploads_not_allowed}</div>
	{/if}
</td>


</tr><tr>


<td class='tableCellOne' colspan='2'>
<div class='marginpad'>
<input type='submit' class='submit' value='{lang:update_signature}' name="submit" /> 
{if image}&nbsp;&nbsp;<input type='submit' class='submit' value='{lang:remove_image}' name="remove" />{/if}
</div>
</td>
</tr>
</table>

</form>
EOF;
}
/* END */



//-------------------------------------
//  Avatar Overview Page
//-------------------------------------

function edit_avatar()
{
return <<< EOF

{form_declaration}

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:edit_avatar}</div></td>

</tr><tr>

<td class='tableCellTwo' style="width:35%;"><div class="itempadbig"><div class='defaultBold'>{lang:current_avatar}</div></div></td>
<td class='tableCellTwo' style="width:65%;">
{if avatar}<img src="{path:avatar_image}" border="0" width="{avatar_width}" height="{avatar_height}" title="{lang:my_avatar}" alt="{lang:my_avatar}" />{/if}
{if no_avatar}{lang:no_avatar}{/if}
</td>

{if installed_avatars}
	</tr><tr>
	<td class='tableCellTwo' style="width:35%;"><div class="itempadbig"><div class='defaultBold'>{lang:choose_installed_avatar}</div></div></td>
	<td class='tableCellTwo' style="width:65%;">{include:avatar_folder_list}</td>
{/if}


{if can_upload_avatar}
	</tr><tr>
	<td class="tableCellTwo" style="width:35%;" valign="top">
	<div class="itempadbig"><b>{lang:upload_an_avatar}</b></div>
	</td>
	<td class="tableCellTwo" style="width:65%;" valign="bottom">
	<div class="itempad"><input type="file" name="userfile" size="20" class="input" /></div>
	</td>
	
	</tr><tr>
	<td class="tableCellTwo" style="width:35%;" valign="top">
	<div class="lighttext">{lang:max_image_size}</div>
	</td>
	<td class="tableCellTwo" style="width:65%;" valign="bottom">
	<div class="lighttext">{lang:allowed_image_types}</div>
	</td>	
{/if}

</tr><tr>

<td class='tableCellOne' colspan='2'>
<div class='marginpad'>{if can_upload_avatar}<input type='submit' class='submit' value='{lang:upload_avatar}' name="submit" />{/if} &nbsp;&nbsp;<input type='submit' class='submit' value='{lang:remove_avatar}' name="remove" /></div>
</td>
</tr>
</table>

</form>
EOF;
}
/* END */



//-------------------------------------
//  Listing of avatar folders
//-------------------------------------

function avatar_folder_list()
{
return <<< EOF
<div class="itempad"><a href="{path:folder_path}">{folder_name}</a></div>
EOF;
}
/* END */



//-------------------------------------
//  Browser Avatars Page
//-------------------------------------

function browse_avatars()
{
return <<< EOF

{form_declaration}

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG'><div class="tableHeading">{lang:browse_avatars}</div></td>

</tr><tr>

<td class='tableRowHeadingBold'>{lang:current_avatar_set}&nbsp; {avatar_set}</td>

</tr><tr>

<td class="tableCellTwo">

<table border="0" cellpadding="3" cellspacing="3" style="width:100%;">
{avatar_table_rows}
</table>

</td>

{if pagination}
</tr><tr>
<td class="tableCellTwo"><div class='itempad'><div class='defaultCenter'>{pagination}</div></div></td>
{/if}

</tr><tr>

<td class='tableCellOne'>
<div class='marginpad'><input type='submit' class='submit' value='{lang:choose_selected}' /></div>
</td>
</tr>
</table>

</form>
EOF;
}
/* END */



//-------------------------------------
//  Edit Member Photo Page
//-------------------------------------

function edit_photo()
{
return <<< EOF

{form_declaration}

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:edit_photo}</div></td>

</tr><tr>

<td class='tableCellTwo' style="width:35%;"><div class="itempadbig"><div class='defaultBold'>{lang:current_photo}</div></div></td>
<td class='tableCellTwo' style="width:65%;">

{if photo}
<div class="itempad"><img src="{path:member_photo}" border="0" width="{photo_width}" height="{photo_height}" title="{lang:my_photo}" alt="{lang:my_photo}" /></div>
{/if}
{if no_photo}
<div class="itempad"><img src="{path:image_url}icon_profile.gif" width="50" height="50" border="0" title="{name}" alt="{lang:no_photo}" /></div>
<div class="lighttext">{lang:no_photo_exists}</div>
{/if}

</td>

</tr><tr>
<td class="tableCellTwo" style="width:35%;" valign="top">
<div class="itempadbig"><b>{lang:upload_photo}</b></div>
</td>
<td class="tableCellTwo" style="width:65%;" valign="bottom">
<div class="itempad"><input type="file" name="userfile" size="20" class="input" /></div>
</td>

</tr><tr>
<td class="tableCellTwo" style="width:35%;" valign="top">
<div class="lighttext">{lang:max_image_size}</div>
</td>
<td class="tableCellTwo" style="width:65%;" valign="bottom">
<div class="lighttext">{lang:allowed_image_types}</div>
</td>	

</tr><tr>

<td class='tableCellOne' colspan='2'>
<div class='marginpad'><input type='submit' class='submit' value='{lang:upload_photo}' name="submit" /> &nbsp;&nbsp;<input type='submit' class='submit' value='{lang:remove_photo}' name="remove" /></div>
</td>
</tr>
</table>

</form>
EOF;
}
/* END */





//-------------------------------------
//  subscriptions_form
//-------------------------------------

function subscriptions_form()
{
return <<< EOF

<script type="text/javascript"> 
<!--

function toggle(thebutton)
{
	if (thebutton.checked) 
	{
	   val = true;
	}
	else
	{
	   val = false;
	}
				
	var len = document.target.elements.length;

	for (var i = 0; i < len; i++) 
	{
		var button = document.target.elements[i];
		
		var name_array = button.name.split("["); 
		
		if (name_array[0] == "toggle") 
		{
			button.checked = val;
		}
	}
	
	document.target.toggleflag.checked = val;
}
//-->
</script>

<form method="post" action="{path:update_subscriptions}" name="target">

<div class='profileHeadingBG'><div class="tableHeading">{lang:subscriptions}</div></div>

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>

{subscription_results}

</table>
</form>

EOF;
}
/* END */




//-------------------------------------
//  no_subscriptions_message
//-------------------------------------

function no_subscriptions_message()
{
return <<< EOF

<tr><td class='tableCellOne'><div class='highlight'>{lang:no_subscriptions}</div></td></tr>

EOF;
}
/* END */




//-------------------------------------
//  subscription_result_heading
//-------------------------------------

function subscription_result_heading()
{
return <<< EOF
<tr>
<td class='tableCellOne' style="width:56%;"><b>{lang:title}</b></td>
<td class='tableCellOne' style="width:22%;"><b>{lang:type}</b></td>
<td class='tableCellOne' style="width:22%;"><b><input type="checkbox" name="toggleflag" value="" onclick="toggle(this);" />&nbsp;{lang:unsubscribe}</b></td>
</tr>
EOF;
}
/* END */




//-------------------------------------
//  subscription_result_rows
//-------------------------------------

function subscription_result_rows()
{
return <<< EOF
<tr>
<td class='{class}'><a href="{path}" target="_blank">{title}</a></td>
<td class='{class}'>{type}</td>
<td class='{class}'><input type="checkbox" name="toggle[]" value="{id}" /></td>
</tr>
EOF;
}
/* END */




//-------------------------------------
//  subscription_pagination
//-------------------------------------

function subscription_pagination()
{
return <<< EOF
<tr>
<td class='{class}'>&nbsp;{pagination}</td>
<td class='{class}'>&nbsp;</td>
<td class='{class}'><div class='itempad'><input type="submit" name="submit" value="{lang:unsubscribe}" /></div></td>
</tr>
EOF;
}
/* END */







//-------------------------------------
//  registration_form
//-------------------------------------

function registration_form()
{
return <<< EOF

<table class="tableBorder" border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG' colspan='2'><div class="tableHeading">{lang:member_registration}</div></td>

</tr><tr>

<td class='tableCellTwo' style="width:45%;"><div class='defaultBold'><span class="highlight">*</span> {lang:username}</div><div class='itempad'>{lang:username_length}</div></td>
<td class='tableCellOne' style="width:55%;"><input type="text" name="username" value="" maxlength="32" class="input" size="25" style="width:300px" /></td>

</tr><tr>

<td class='tableCellTwo' style="width:45%;"><div class='defaultBold'><span class="highlight">*</span> {lang:password}</div><div class='itempad'>{lang:password_length}</div></td>
<td class='tableCellOne' style="width:55%;"><input type="password" name="password" value="" maxlength="32" class="input" size="25" style="width:300px" /></td>

</tr><tr>

<td class='tableCellTwo' style="width:45%;"><div class='defaultBold'><span class="highlight">*</span> {lang:password_confirm}</div></td>
<td class='tableCellOne' style="width:55%;"><input type="password" name="password_confirm" value="" maxlength="32" class="input" size="25" style="width:300px" /></td>


</tr><tr>

<td class='tableCellTwo' style="width:45%;">
<div class='defaultBold'><span class="highlight">*</span> {lang:screen_name}</div>
<div class='itempad'>{lang:screen_name_explanation}</div>
</td>
<td class='tableCellOne' style="width:55%;"><input type="text" name="screen_name" value="" maxlength="100" class="input" size="25" style="width:300px" /></td>

</tr><tr>

<td class='tableCellTwo' style="width:45%;"><div class='defaultBold'><span class="highlight">*</span> {lang:email}</div></td>
<td class='tableCellOne' style="width:55%;"><input type="text" name="email" value="" maxlength="120" class="input" size="40" style="width:300px" /></td>

</tr><tr>

<td class='tableCellTwo' style="width:45%;"><div class='defaultBold'>{lang:url}</div></td>
<td class='tableCellOne' style="width:55%;"><input type="text" name="url" value="" maxlength="100" class="input" size="25" style="width:300px" /></td>

{custom_fields}
</tr><tr>
<td class='tableCellTwo' style="width:45%;">
<div class='defaultBold'>{required}<span class="highlight">*</span>{/required} {field_name}</div>
{if field_description}
<div class='default'>{field_description}</div>
{/if}
</td>
<td class='tableCellOne' style="width:55%;">{field}</td>
{/custom_fields}

</tr><tr>

<td colspan='2' class='tableCellOne'>
<div class="itempadbig">

<div class="itempad"><div class='defaultBold'>{lang:terms_of_service}</div></div>

<textarea name='rules' style='width:100%' class='textarea' rows='8' cols='90' readonly>
{lang:terms_of_service_text}
</textarea>
</div>
</td>

</tr><tr>

<td colspan='2' class='tableCellOne'>

{if captcha}
<p><span class="highlight">*</span> {lang:captcha}</p>
<p>
{captcha}
<br />
<input type="text" name="captcha" value="" size="20" maxlength="20" style="width:140px;" />
</p>
{/if}

<p><input type='checkbox' name='accept_terms' value='y'  />&nbsp;&nbsp;<span class="alert">{lang:terms_accepted}</span></p>
<p><input type="submit" value="{lang:submit}" class="submit" /></p>
<p><span class="highlight">*</span> {lang:required_fields}</p>

</td>
</tr>
</table>

EOF;
}
/* END */



//-------------------------------------
//  memberlist
//-------------------------------------

function memberlist()
{
return <<< EOF
{form_declaration}

<table class='tableborder' border="0" cellpadding="0" cellspacing="0" style="width:100%;">
<tr>
<td class='memberlistHead' style="width:21%;">{lang:name}</td>
<td class='memberlistHead' style="width:13%;">{lang:forum_posts}</td>
<td class='memberlistHead' style="width:8%;">{lang:email_short}</td>
<td class='memberlistHead' style="width:8%;">{lang:url}</td>
<td class='memberlistHead' style="width:8%;">{lang:aol}</td>
<td class='memberlistHead' style="width:8%;">{lang:icq}</td>
<td class='memberlistHead' style="width:8%;">{lang:yahoo_short}</td>
<td class='memberlistHead' style="width:13%;">{lang:join_date}</td>
<td class='memberlistHead' style="width:13%;">{lang:last_visit}</td>
</tr>

{member_rows}

<tr>
<td class='memberlistFooter' colspan="9" align='center' valign='middle'>

<div class="defaultSmall">
<b>{lang:show}</b>

<select name='group_id' class='select'>
{group_id_options}
</select>

&nbsp; <b>{lang:sort}</b>

<select name='order_by' class='select'>
{order_by_options}
</select> 

&nbsp;  <b>{lang:order}</b>

<select name='sort_order' class='select'>
{sort_order_options}
</select> 

&nbsp; <b>{lang:rows}</b>

<select name='row_limit' class='select'>
{row_limit_options}
</select> 


&nbsp; <input type='submit' value='{lang:submit}' class='submit' />

</div>
</td>
</tr>
</table>

{if paginate}
<div class="itempadbig">
	<table cellpadding="0" cellspacing="0" border="0" class="paginateBorder">
	<tr>
	<td><div class="paginateStat">{current_page} {lang:of} {total_pages}</div></td>
	{pagination_links}
	</tr>
	</table>
</div>
{/if}

</form>

<!--- Begin Member Search -->

<script type="text/javascript">
<!--

var searchFieldCount = 1;

function add_search_field()
{
	if (document.getElementById('search_field_1'))
	{
		// Find last search field
		var originalSearchField = document.getElementById('search_field_1');
		searchFieldCount++;
		
		// Clone it, change the id
		var newSearchField = originalSearchField.cloneNode(true);
		newSearchField.id = 'search_field_' + searchFieldCount;
		
		// Zero the input and change the names of fields
		var newFieldInputs = newSearchField.getElementsByTagName('input');
		newFieldInputs[0].value = '';
		newFieldInputs[0].name = 'search_keywords_' + searchFieldCount;
		
		var newFieldSelects = newSearchField.getElementsByTagName('select');
		newFieldSelects[0].name = 'search_field_' + searchFieldCount;
		
		// Append it and we're done
		originalSearchField.parentNode.appendChild(newSearchField);
	}
}

function delete_search_field(obj)
{
	if (obj.parentNode && obj.parentNode.id != 'search_field_1')
	{
		obj.parentNode.parentNode.removeChild(obj.parentNode)
	}
}

//-->
</script>

<table class='tableborder' border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
	<td class='memberlistHead'>{lang:member_search}</td>
</tr>
<tr>
	<td class='tableCellOne'>
		{form:form_declaration:do_member_search}
		
		<div id="member_search_fields">
		
		<div id="search_field_1" class="itempadbig">
		<input type="text" name="search_keywords_1" />
		<select name='search_field_1' class='select' >
		<option value='screen_name'>{lang:search_field}</option>
		<option value='screen_name'>{lang:mbr_screen_name}</option>
		<option value='email'>{lang:mbr_email_address}</option>
		<option value='url'>{lang:mbr_url}</option>
		<option value='location'>{lang:mbr_location}</option>
		{custom_profile_field_options}
		</select>
		<a href="#" onclick="add_search_field(); return false;" class="defaultBold">+</a>
		<a href="#" onclick="delete_search_field(this); return false;" class="defaultBold">-</a>
		</div>
		
		</div>
		
		<select name='search_group_id' class='select' >
		{group_id_options}
		</select>
		
		<div class="itempadbig">&nbsp; <input type='submit' value='{lang:search}' class='submit' /></div>
		
		</form>
	</td>
</tr>
</table>

EOF;
}
/* END */




//-------------------------------------
//  memberlist_rows
//-------------------------------------

function memberlist_rows()
{
return <<< EOF
<tr>

<td class='{member_css}' style="width:20%;">
<span class="defaultBold"><a href="{path:profile}">{name}</a></span>
</td>

<td class='{member_css}'>{total_combined_posts}</td>

<td class='{member_css}'>
{if accept_email}
<a href="#" {email_console}><img src="{path:image_url}icon_email.gif" width="56" height="14" alt="{lang:email_console}" title="{lang:email_console}" border="0" /></a>
{/if}
</td>

<td class='{member_css}'>
{if url}
<a href="{url}" target="_blank"><img src="{path:image_url}icon_www.gif" width="56" height="14" border="0" alt="{url}" title="{url}" /></a>
{/if}
</td>

<td class='{member_css}'>
{if aol_im}
<a href="#" {aim_console}><img src="{path:image_url}icon_aim.gif" width="56" height="14" border="0" alt="{lang:mbr_aim_console}" title="{lang:mbr_aim_console}" /></a>
{/if}
</td>

<td class='{member_css}'>
{if icq}
<a href="#" {icq_console}><img src="{path:image_url}icon_icq.gif" width="56" height="14" border="0" alt="{lang:mbr_icq}" title="{lang:mbr_icq}" /></a>
{/if}
</td>

<td class='{member_css}'>
{if yahoo_im}
<a href="{yahoo_console}" target="_blank"><img src="{path:image_url}icon_yim.gif" width="56" height="14" border="0" alt="{lang:mbr_yahoo}" title="{lang:mbr_yahoo}" /></a>
{/if}
</td>


<td class='{member_css}'>{join_date  format="%m/%d/%Y"}</td>

<td class='{member_css}'>{last_visit  format="%m/%d/%Y"}</td>

</tr>
EOF;
}
/* END */



//-------------------------------------
//  aim_console
//-------------------------------------

function aim_console()
{
return <<< EOF
<div>&nbsp;</div>
<table style="width:118px;" cellspacing="0" cellpadding="0" border="0" align="center">
<tr>
<td style="width:118px;"><img src="{path:image_url}aim_head.gif" style="width:118px;" height="46" border="0" /></td>
</tr>
<tr><td style="width:118px;"><a href="aim:goim?screenname={aol_im}&message=Hi.+Are+you+there?"><img src="{path:image_url}aim_im.gif" style="width:118px;" height="40" border="0" alt="{lang:am_online}" /></a></td>
</tr><tr>
<td style="width:118px;"><a href="aim:addbuddy?screenname={aol_im}"><img src="{path:image_url}aim_buddy.gif" style="width:118px;" height="40" border="0" alt="{lang:add_to_buddy}" /></a></td>
</tr><tr>
<td style="width:118px;"><a href="http://aim.aol.com/aimnew/NS/congratsd2.adp"><img src="{path:image_url}aim_footer.gif" style="width:118px;" height="33" border="0" /></a></td>
</tr>
</table>
<div>&nbsp;</div>
<div class="marginpad"><a href="JavaScript:window.close();">{lang:close_window}</a></div>
EOF;
}
/* END */




//-------------------------------------
//  icq_console
//-------------------------------------

function icq_console()
{
return <<< EOF
{form_declaration}

<table class="tableborder" cellpadding="0" cellspacing="0" border="0" style="width:560px;" align="center">
<tr>
<td class="profileHeadingBG" colspan="2"><div class="tableHeading">{lang:icq_console}</div>
</td>

</tr>
<tr>
<td class="tableCellOne">

<h3>{lang:recipient}&nbsp; {name}</h3>
<h3>{lang:icq_number}&nbsp; {icq}</h3>

<h3>{lang:subject}</h3>
<p>
<input type="text" name="subject" value="" style='width:100%' maxlength="80" class="input" size="70" /></p>
</p>

<h3>{lang:message}</h3>
<p>
<textarea name='body' style='width:100%' class='textarea' rows='8' cols='90'></textarea>
</p>

<div class="innerShade">
<p>{lang:message_disclaimer}</p>
<p class='highlight'>{lang:message_logged}</p>
</div>
<p><input type='checkbox' name='self_copy' value='y' />&nbsp;&nbsp;{lang:send_self_copy}</p>

<p><input type="submit" value="{lang:submit}" class="submit" /></p>
<div class="marginpad"><a href="JavaScript:window.close();">{lang:close_window}</a></div>
</td>
</tr>
</table>

</form>
EOF;
}
/* END */






//-------------------------------------
//  email_form
//-------------------------------------

function email_form()
{
return <<< EOF
{form_declaration}

<table class="tableborder" cellpadding="0" cellspacing="0" border="0" style="width:560px;" align="center">
<tr>
<td class="profileHeadingBG" colspan="2"><div class="tableHeading">{lang:email_console}</div>
</td>

</tr>
<tr>
<td class="tableCellOne">

<h3>{lang:recipient}&nbsp; {name}</h3>

<h3>{lang:subject}</h3>
<p>
<input type="text" name="subject" value="" style='width:100%' maxlength="80" class="input" size="70" />
</p>

<h3>{lang:message}</h3>
<p>
<textarea name='message' style='width:100%' class='textarea' rows='8' cols='90'></textarea>
</p>

<div class="innerShade">
<p>{lang:message_disclaimer}</p>
<p class='highlight'>{lang:message_logged}</p>
</div>
<p><input type='checkbox' name='self_copy' value='y' />&nbsp;&nbsp;{lang:send_self_copy}</p>

<p><input type="submit" value="{lang:submit}" class="submit" /></p>
<div class="marginpad"><a href="JavaScript:window.close();">{lang:close_window}</a></div>
</td>
</tr>
</table>

</form>

EOF;
}
/* END */




//-------------------------------------
//  email_user_message
//-------------------------------------

function email_user_message()
{
return <<< EOF
<div class='tableborder'>
<div class='tableCellOne'>

<br />

<div class="innerShade">
<p class='{css_class}'>{lang:message}</p>
</div>

<div class="marginpad"><br /><br /><a href="JavaScript:window.close();">{lang:close_window}</a></div>

</form>

</div>
</div>
EOF;
}
/* END */



//-------------------------------------
//  Emoticon Page
//-------------------------------------

function emoticon_page()
{
return <<< EOF
<div id="content">
<div  class="tableBorderTopRight">
<table cellpadding="3" cellspacing="0" border="0" style="width:100%;" class="tableBG">
{include:smileys}
</table>
</div>
</div>
EOF;
}
/* END */




//----------------------------------------
//  CSS Stylesheet
//----------------------------------------

function stylesheet()
{
return <<< EOF

/*
    Default Body
------------------------------------------------------ */ 
body {
 margin:            0;
 padding:           0;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #38394B;
 background-color:  #fff;
}
/*
    Default Links
------------------------------------------------------ */ 
a {
 text-decoration:   none;
 color:             #330099;
 background-color:  transparent;
}
  
a:visited {
 color:             #330099;
 background-color:  transparent;
}

a:hover {
 color:             #A0A4C1;
 text-decoration:   underline;
 background-color:  transparent;
}

/*
    Main Content Wrapper
------------------------------------------------------ */ 
#content {
 left:              0px;
 right:             10px;
 margin:            15px 20px 0 20px;
 padding:           0;
 width:             auto;
}
* html #content {
 width:             100%;
 w\idth:            auto;
}

/*
    Basic stuff
------------------------------------------------------ */ 

p {
 background:		transparent;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #38394B;
}

.default, .defaultBold, .defaultRight, .defaultCenter {
 background:		transparent;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #38394B;
}

.defaultBold {
 font-weight: bold;
}

.defaultRight {
 text-align: right;
}

.defaultCenter {
text-align: center;
}

.header {
 background: 		#74779D url({path:image_url}bg_profile_heading.jpg) repeat-x left top;
 color:             #fff;
 padding:           5px;
 border:            1px solid #7B81A9;
 margin: 			0 0 10px 0;
}

h1 {  
 font-family:		Georgia, Times New Roman, Times, Serif, Arial;
 font-size: 		16px;
 font-weight:		bold;
 letter-spacing:	.05em;
 color:				#fff;
 margin: 			0;
 padding:			0;
}

h2 {
 background:		transparent;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         13px;
 color:             #38394B;
 margin:			0 0 6px 0;
}

h3 {
 background:		transparent;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         12px;
 color:             #38394B;
 margin:			3px 0 3px 0;
}

.lighttext {
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         10px;
 color:             #73769D;
 padding:           4px 0 2px 0;
 background-color:  transparent;  
}

.success {
 font-family:		Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:			11px;
 color:				#009933;
 font-weight:		bold;
 padding:			3px 0 3px 0;
 background-color:	transparent; 
}

/*
    Page Header 
------------------------------------------------------ */ 

#pageheader {  
 background: #4C5286 url({path:image_url}bg_header.jpg) repeat-x left top;
 border-top: 1px solid #fff;
 border-bottom: 1px solid #fff;
 padding:  20px 0 20px 0;
}

.heading {  
 font-family:		Georgia, Times New Roman, Times, Serif, Arial;
 font-size: 		16px;
 font-weight:		bold;
 letter-spacing:	.05em;
 color:				#fff;
 margin: 			0;
 padding:			0 0 0 28px;
}


/*
    Sub-header Bar
    Contains the breadcrumb links
------------------------------------------------------ */ 
#subheader {
 background: 		#F0F0F2 url({path:image_url}bg_breadcrumb.jpg) repeat-x left top;
 padding: 			4px 40px 3px 27px;
 border-top:		1px solid #767A9E;
 border-bottom:		1px solid #979AC2;
}

/*
    Breadcrumb Links
------------------------------------------------------ */ 
.breadcrumb {  
 background-color:  transparent;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 margin:			3px 0 3px 0;
}
.breadcrumb a:link { 
 color:             #330099;
 background:        transparent;
 text-decoration:   none;
} 
.breadcrumb a:visited { 
 color:             #330099;
 background:        transparent;
 text-decoration:   none;
}
.breadcrumb a:hover { 
 color:             #B9BDD4;    
 background:        transparent;
 text-decoration:   underline;
}

.currentcrumb {
 color:  #38394B;
 font-weight: bold;
}
.breadcrumbspacer {
 color:  #6B6B85;
}

/*
    Misc. Formatting Items
------------------------------------------------------ */ 

.spacer {
 margin-bottom:     12px;
}

.itempad {
padding: 2px 0 2px 0;
}

.itempadbig {
padding: 5px 0 5px 0;
}

.bottompad {
padding: 0 0 2px 0;
}
.marginpad {
 margin: 12px 0 10px 3px;
 background: transparent;
}

.leftpad {
padding: 0 0 0 4px;
}

/*
    Member Profile Page
------------------------------------------------------ */ 

.profileHeadingBG {
 background: 		#74779D url({path:image_url}bg_profile_heading.jpg) repeat-x left top;
 color:             #fff;
 padding:           6px 6px 6px 6px;
 border-bottom:     #585C9C 1px solid;
}

.profileAlertHeadingBG {
 background:		#6e0001 url({path:image_url}bg_alert.jpg) repeat-x left top;
 color:				#fff;
 padding:			6px 6px 6px 6px;
 border-bottom:		#585C9C 1px solid;
}

.profileTopBox {
 background:	#F0F0F2 url({path:image_url}bg_profile_box.jpg) repeat-x left top;
 margin:		0;
 padding:		7px 5px 5px 5px;
}

.profileTitle {
 font-family:		Tahoma, Verdana, Geneva, Trebuchet MS, Arial, Sans-serif;
 font-size:			14px;
 font-weight:		bold;
 color:				#000;
 padding: 			3px 5px 3px 0;
 margin:			0;
 background-color: transparent;  
}

.profilePhoto {
 background:		#F0F0F2 url({path:image_url}bg_profile_box.jpg) repeat-x left top;
 border-left:       1px solid #B2B3CE;
 padding:			1px;
 margin-top:        1px;
 margin-bottom:     3px;
}

.avatar {
 background:	transparent;
 margin:		3px 14px 0 3px;
}

.photo {
 background:	transparent;
 margin:		6px 14px 0 3px;
}

.profileItem {
 background:		transparent;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #38394B;
 margin: 			2px 0 2px 0;
 background-color: transparent;  
}

.profileHead {
 font-family:		Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:			10px;
 font-weight:		bold;
 text-transform:	uppercase;
 color:				#fff;
 padding:			3px 4px 3px 10px;
 background-color:	#4C5286;  
 border-top:		1px solid #fff;
 border-bottom:		1px solid #fff;
 margin:			0 0 0 0;
}

.menuHeadingBG {
 background: 		#74779D url({path:image_url}bg_profile_heading.jpg) repeat-x left top;
 color:             #fff;
 padding:           6px 6px 6px 6px;
 border-bottom:     #585C9C 1px solid;
}

.profileMenu {
 background: 		#EDECEE;
 border:            1px solid #7B81A9;
 padding:			1px;
 margin-top:        1px;
 margin-bottom:     3px;
}

.profileMenuInner {
 padding-left:		10px;
 padding-right:		8px;
 margin-bottom:		4px;
 margin-top:		4px;
}

.menuItem {
 font-family:		Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:			11px;
 padding:			3px 0 3px 0;
 background-color:	transparent;  
}

.borderTopBot {
 border-top:	1px solid #585C9C;
 border-bottom:	1px solid #585C9C;
}

.borderBot {
 border-bottom:	1px solid #585C9C;
}

.altLinks { 
 color:             #fff;
 background:        transparent;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
}
.altLinks a:link { 
 color:             #fff;
 background:        none;
 text-decoration:   underline;
}
.altLinks a:visited { 
 color:             #fff;
 background:        transparent;
 text-decoration:   none;
}
.altLinks a:hover { 
 color:             #B8BDED;    
 background:        transparent;
 text-decoration:   underline;
}


.memberlistRowOne {
 background: #FBFBFC url({path:image_url}bg_table_td_one.jpg) repeat-x left top;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #38394B;
 padding:           3px 6px 3px 6px;
 border-top:        1px solid #fff;
 border-bottom:     1px solid #B2B3CE;
 border-left:       1px solid #B2B3CE;
 border-right:      1px solid #fff;
}
.memberlistRowTwo {
 background: #F0F0F0 url({path:image_url}bg_table_td_two.jpg) repeat-x left top;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #38394B;
 padding:           3px 6px 3px 6px;
 border-top:        1px solid #fff;
 border-bottom:     1px solid #B2B3CE;
 border-left:       1px solid #B2B3CE;
 border-right:      1px solid #fff;
}

.memberlistHead {
 font-family:		Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size: 		11px;
 font-weight: 		bold; 
 background: 		#74779D url({path:image_url}bg_profile_heading.jpg) repeat-x left top;
 color:             #fff;
 border-bottom:     #585C9C 1px solid;
 padding: 			8px 0 8px 8px;
}

.memberlistFooter {
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #000;
 padding:           6px 10px 6px 6px;
 border-top:        1px solid #fff;
 border-bottom:     1px solid #999;
 border-right:      1px solid #fff;
 background-color:  #B8B9D1;  
}

.innerShade {
 background-color:	#DDE1E7;
 border:      	 	1px solid #74779D;
 margin:			0;
 padding:			10px;
}

/*
    Table Formatting
------------------------------------------------------ */ 

.tablePad {
 padding:  0 2px 4px 2px;
}

.tableborder {
 border:            1px solid #7B81A9;
 padding:			1px;
 margin-top:        1px;
 margin-bottom:     3px;
}
.tableBorderTopRight {
 border-top:     	1px solid #B2B3CE;
 border-right:     	1px solid #B2B3CE;
 padding:			0;
 margin-top:        1px;
 margin-bottom:     3px;
}
.tableBorderRight {
 border-right:      1px solid #B2B3CE;
 padding:			0;
 margin-top:        1px;
 margin-bottom:     3px;
}

.tableBG {
 background-color: #F0F0F0;
}
.tableHeadingBG {
 background: 		#74779D url({path:image_url}bg_table_heading.jpg) repeat-x left top;
 color:             #fff;
 padding:           6px 6px 6px 6px;
 border-bottom:     1px solid #fff;
}
.tableHeading {
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         12px;
 letter-spacing:    .1em;
 font-weight:		bold;
 color:             #fff;
 padding:           0;
 margin:			0;
 background-color:  transparent;  
}
.tableRowHeading, .tableRowHeadingBold {
 background: #C9CAE2 url({path:image_url}bg_table_row_heading.jpg) repeat-x left top;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #404055;
 padding:           8px 10px 8px 6px;
 border-top:        1px solid #A7A9C7;
 border-bottom:     1px solid #A7A9C7;
 border-left:       1px solid #A7A9C7;
 border-right:      1px solid #fff;
}
.tableRowHeadingBold {
font-weight: bold;
}
.tableCellOne {
 background: #F0F0F2 url({path:image_url}bg_table_td_one.jpg) repeat-x left top;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #38394B;
 padding:           3px 6px 3px 6px;
 border-top:        1px solid #fff;
 border-bottom:     1px solid #B2B3CE;
 border-left:       1px solid #B2B3CE;
 border-right:      1px solid #fff;
}
.tableCellTwo {
 background: #EDEEF3 url({path:image_url}bg_table_td_two.jpg) repeat-x left top;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #38394B;
 padding:           3px 6px 3px 6px;
 border-top:        1px solid #fff;
 border-bottom:     1px solid #B2B3CE;
 border-left:       1px solid #B2B3CE;
 border-right:      1px solid #fff;
}

/*
    Pagination Links
------------------------------------------------------ */ 
.paginateBorder {
 background-color:  transparent;
 border-top:        1px solid #7B81A9;
 border-right:      1px solid #7B81A9;
 border-bottom:     1px solid #7B81A9;
 }
.paginate {
 background: 		#FBFBFC url({path:image_url}bg_table_td_one.jpg) repeat-x left top;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #000;
 border-right:      1px solid #fff;
 border-left:      	1px solid #7B81A9;
 padding:           2px 4px 2px 4px;
 margin:		 	0;
 }
.paginateStat {
 background: 		#74779D;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #fff;
 border-left:      	1px solid #7B81A9;
 padding:           2px 10px 2px 10px;
 margin:			0;
 white-space: 		nowrap; 
 }
.paginateCur {
 background: 		#FBFBFC url({path:image_url}bg_table_td_one.jpg) repeat-x left top;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #ccc;
 border-left:      	1px solid #7B81A9;
 padding:           2px 6px 2px 6px;
 margin:			0;
 }
 
.paginate a:link {
 text-decoration:   none;
 color:             #330099;
 text-decoration:   none;
 background-color:  transparent;
}
  
.paginatea:visited {
 color:             #330099;
 text-decoration:   none;
 background-color:  transparent;
}

.paginate a:hover {
 color:             #A0A4C1;
 text-decoration:   none;
 background-color:  transparent;
}

/*

    Form Field Formatting
------------------------------------------------------ */ 

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
 border-top:        1px solid #8386AC;
 border-left:       1px solid #8386AC;
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
 border-top:        1px solid #8386AC;
 border-left:       1px solid #8386AC;
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
 border-top:        1px solid #999999;
 border-left:       1px solid #999999;
 background-color:  #fff;
 color:             #333;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 margin-top:        2px;
 margin-top:        2px;
} 
.radio {
 background-color:  transparent;
 margin-top:        4px;
 margin-bottom:     4px;
 padding:           0;
 border:            0;
}
.checkbox {
 background-color:  transparent;
 padding:           0;
 border:            0;
}
.buttons {
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 font-weight:       bold;
 border-top:		1px solid #9EA3D5;
 border-left:		1px solid #9EA3D5;
 border-right:		1px solid #000;
 border-bottom:		1px solid #000;
 letter-spacing:    .1em;
 margin:        	0;
 padding:			1px 6px 3px 6px;
 background-color:  #3F4471;
 color:             #fff;
 cursor: pointer;
}

.submit {
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 font-weight:       bold;
 border-top:		1px solid #9EA3D5;
 border-left:		1px solid #9EA3D5;
 border-right:		1px solid #000;
 border-bottom:		1px solid #000;
 letter-spacing:    .1em;
 margin:        	0;
 padding:			1px 4px 1px 4px;
 background-color:  #3F4471;
 color:             #fff;
}  
/*
    Error messages
------------------------------------------------------ */ 

.alert {
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         11px;
 color:             #990000;
 font-weight:		bold;
}

.highlight {
 color:             #990000;
}

/*
    Page Footer
------------------------------------------------------ */ 
#footer {
 clear: 			both;
 text-align:        center;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         9px;
 color:             #999;
 line-height:       15px;
 margin-top:        20px;
 margin-bottom:     15px;
}
/*
    Copyright notice
------------------------------------------------------ */ 
.copyright {
 text-align:        center;
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         9px;
 color:             #999;
 margin-top:        15px;
 margin-bottom:     15px;
}

/*
    Formatting Buttons
------------------------------------------------------ */ 

.buttonMode {
 font-family:       Verdana, Geneva, Tahoma, Trebuchet MS, Arial, Sans-serif;
 font-size:         10px;
 color:             #73769D;
 background-color:  transparent; 
 white-space: 		nowrap;
}

.htmlButtonOuter, .htmlButtonOuterL {
 background-color:  #f6f6f6;  
 padding:           0;
 border-top:        #333 1px solid;
 border-right:      #333 1px solid;
 border-bottom:     #333 1px solid;
}
.htmlButtonOuterL  {
 border-left:       #333 1px solid;
}
.htmlButtonInner {
 background-color:  transparent; 
 text-align:		center;
 padding:			0 3px 0 3px;
 border-left:       #fff 1px solid;
 border-top:        #fff 1px solid;
 border-right:      #ccc 1px solid;
 border-bottom:     #ccc 1px solid;
}
.htmlButtonOff {
 font-family:       Verdana, Arial, Trebuchet MS, Tahoma, Sans-serif;
 font-size:         11px;
 font-weight:       bold;
 padding:           1px 2px 2px 2px;
 white-space:       nowrap;
}
.htmlButtonOff a:link { 
 color:             #000;
 text-decoration:   none;
 white-space:       nowrap;
}
.htmlButtonOff  a:visited { 
 text-decoration:   none;
}
.htmlButtonOff a:active { 
 text-decoration:   none;
 color:             #999;
}
.htmlButtonOff a:hover { 
 text-decoration:   none;
 color:             #999;
}
.htmlButtonOn {
 font-family:       Verdana, Arial, Trebuchet MS, Tahoma, Verdana, Sans-serif;
 font-size:         11px;
 font-weight:       bold;
 background:        #f6f6f6;
 padding:           1px 2px 2px 2px;
 white-space:       nowrap;
}
.htmlButtonOn a:link { 
 color:             #990000;
 text-decoration:   none;
 white-space:       nowrap;
}  
.htmlButtonOn  a:visited { 
 text-decoration:   none;
} 
.htmlButtonOn a:active { 
 text-decoration:   none;
 color:             #999;
}
.htmlButtonOn a:hover { 
 color:             #999;
 text-decoration:   none;
}

/*
    SPELL CHECK CSS
--------------------------------------------------------------- */

.iframe { border:1px solid #6666CC;}

.wordSuggestion
{
	background-color: #f4f4f4; 
	border: 1px solid #ccc; 
	padding: 4px; 
}

.wordSuggestion a, .wordSuggestion a:active
{
	cursor: pointer;
}

.spellchecked_word
{
	cursor: pointer;
	background-color: #fff;
	border-bottom: 1px dashed #ff0000;
}

.spellchecked_word_selected
{
	cursor: pointer;
	background-color: #ADFF98;
}


EOF;
}
/* END */





// ---------------------------------------------------
// ---------------------------------------------------
//  PRIVATE MESSAGES TEMPLATES
// ---------------------------------------------------
// ---------------------------------------------------


// -----------------------------------
//  Success Message - USER
// -----------------------------------
    
function message_success()
{
	return <<<WHOA
    	
<div class='tableCellOne'><div class='success'>&nbsp;{lang:message}</div></div>

WHOA;
    
}
/* END */
    
	
    
// -----------------------------------
//  Error Message - USER
// -----------------------------------
    
function message_error()
{
	return <<<WHOA
    	
<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG'><div class="tableHeading">{lang:heading}</div></td>

</tr><tr>

<td class='tableCellTwo'><div class='highlight'>
<p>{lang:message}</p>
<p><a href='javascript:history.go(-1)' style='text-transform:uppercase;'>&#171; {lang:back}</a></p>
</div></td>

</tr>
</table>

WHOA;
    
}
/* END */

 	
 	
// -----------------------------------
//   Message Menu - User
// -----------------------------------   

function message_menu()
{
	return <<<EOT

{include:hide_menu_js}
<div class='borderTopBot'><div class='profileHead'>{lang:private_messages} {include:hide_menu_link}</div></div>

<div id="extText1" style='{include:hide_menu_style}'>
<div class='profileMenuInner'>
{include:menu_items}
</div>
</div>
 
EOT;
 }
 /* END */
 
 
 
// -----------------------------------
//   Message Menu Rows
// -----------------------------------   

function message_menu_rows()
{
	return <<<EOT
    
<div class='menuItem'>
<a href='{link}' onmouseover="window.status='{title}';" onmouseout="window.status='';" >{title}</a>
</div>   
 
EOT;
 }
 /* END */
 
 
 
// -----------------------------------
//  Preview Template for User
// -----------------------------------   

function preview_message()
{
	return <<<EOT
    	
<table class="bottompad" style='width:100%;'  cellspacing='0'  cellpadding='0'  border='0' >
<tr>
<td class='menuHeadingBG'>
<div class='tableHeading'>{lang:preview_message}</div>
</td>
</tr>
<tr>
<td class='tableCellOne'>{include:parsed_message}</td>
</tr>
</table>
EOT;
    	
}
/* END */
 	
 	
 	
 	
// -----------------------------------
//  Compose Template for User
// -----------------------------------   

function message_compose()
{
    return <<<DOC

{include:hidden_js}
{include:search_js}
{include:spellcheck_js}
{include:text_counter_js}
{include:dynamic_address_book}

<!-- Hidden Emoticon Popup -->

<div id="emoticons" class="tableCellOne" style="border: 1px solid #666; position:absolute;visibility:hidden;">

<script type="text/javascript"> 
//<![CDATA[
function add_smiley(smiley)
{
	taginsert('other', " " + smiley + " ", '');
	//document.getElementById('submit_message').body.value += " " + smiley + " ";
	emoticonspopup.hidePopup('emoticons');
	document.getElementById('submit_message').body.focus();
}
//]]>
</script>

<form method='post' action='' >
<table border='0' cellspacing='0' cellpadding='10' style='width:200px'>

{include:emoticons}

</table>
</form>
<div class="defaultCenter">
<p><a href="" onclick="emoticonspopup.hidePopup(); return false;">{lang:close_window}</a></p>
</div>
</div>

<script type="text/javascript"> 
//<![CDATA[
var emoticonspopup = new PopupWindow("emoticons");
emoticonspopup.offsetY=0;
emoticonspopup.offsetX=0;
emoticonspopup.autoHide();
//]]>
</script>

<!-- End Hidden Emoticon Popup -->


{include:submission_error}
{include:preview_message}

{form:form_declaration:messages}

<table cellpadding="0" cellspacing="0" border="0" width="100%" >

<tr>
<td class="menuHeadingBG" colspan="2"><div class="tableHeading">{lang:new_message}</div></td>
</tr>

<tr>
<td class="tableCellTwo" style="width:25%" align="right"><b>{lang:message_recipients}</b> &nbsp;{include:search:recipients}</td>
<td class="tableCellTwo" style="width:75%">
<textarea name='recipients' id='recipients' style='width:100%' class='textarea' rows='2' cols='90' onkeyup='buddy_list(this.name);'>{input:recipients}</textarea>
<div id="recipients_buddies"></div>
</td>
</tr>

<tr>
<td class="tableCellTwo" style="width:25%" align="right"><b>{lang:cc}</b> &nbsp;{include:search:cc}</td>
<td class="tableCellTwo" style="width:75%">
<textarea name='cc' id='cc' style='width:100%' class='textarea' rows='2' cols='90' onkeyup='buddy_list(this.name);'>{input:cc}</textarea>
<div id="cc_buddies"></div>
</td>
</tr>

<tr>
<td class="tableCellTwo" style="width:25%" align="right"><b>{lang:message_subject}</b></td>
<td class="tableCellTwo" style="width:75%">
<input type="text" class="input" name="subject" style='width:100%' size="90" value="{input:subject}" />
</td>
</tr>

<tr>
<td class="tableCellTwo" style="width:25%" valign="middle" align="right">
<b>{lang:guided}&nbsp;<input type='radio' name='mode' value='guided' onclick='setmode(this.value)' />&nbsp;
{lang:normal}&nbsp;<input type='radio' name='mode' value='normal' onclick='setmode(this.value)' checked='checked'/></b>
</td>
<td class="tableCellTwo" style="width:75%">
<div class="default">
 
 {include:html_formatting_buttons}

</div>
</td>
</tr>



<tr>
<td class="tableCellTwo" style="width:25%" valign="middle" align="right">
<b>{lang:font_formatting}</b>
</td>
<td class="tableCellTwo" style="width:75%">
<div class="default">
 
<select name="size" class="select" onchange="selectinsert(this, 'size')" >
<option value="0">{lang:size}</option>
<option value="1">{lang:small}</option>
<option value="3">{lang:medium}</option>
<option value="4">{lang:large}</option>
<option value="5">{lang:very_large}</option>
<option value="6">{lang:largest}</option>
</select>

<select name="color" class="select" onchange="selectinsert(this, 'color')">
<option value="0">{lang:color}</option>
<option value="blue">{lang:blue}</option>
<option value="green">{lang:green}</option>
<option value="red">{lang:red}</option>
<option value="purple">{lang:purple}</option>
<option value="orange">{lang:orange}</option>
<option value="yellow">{lang:yellow}</option>
<option value="brown">{lang:brown}</option>
<option value="pink">{lang:pink}</option>
<option value="gray">{lang:grey}</option>
</select>

</div>
</td>
</tr>


<tr>
<td class="tableCellTwo" style="width:25%" valign="top" align="right">
<div><b>{lang:message}</b></div>
<div class="default"><br /><a href='javascript:void(0);' onclick='dynamic_emoticons();return false;'>{lang:smileys}</a></div>
</td>
<td class="tableCellTwo" style="width:75%">
<textarea name='body' id="body" style='width:100%' class='textarea' rows='20' cols='90' onkeydown='text_counter();' onkeyup='text_counter();'>{input:body}</textarea>
</td>
</tr>

{if spellcheck}

<tr>
<td class="tableCellTwo" style="width:25%" valign="top" align="right"><div class="default"><div class="itempadbig"><b>{lang:spell_check}</b></div></div></td>
<td class="tableCellTwo" style="width:75%" valign="top">
<div class="default">
<div class="itempadbig">
&nbsp;&nbsp;<a href="javascript:void(0);" onclick="eeSpell.getResults('body');return false;">{lang:check_spelling}</a>
<span id="spellcheck_hidden_body" style="visibility:hidden;">&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="SP_saveSpellCheck();return false">{lang:save_spellcheck}</a>&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:void(0);" onclick="SP_revertToOriginal();return false">{lang:revert_spellcheck}</a></span>
</div>
</div>

<iframe src="{path:spellcheck_iframe}" width="100%" style="display:none;" class="iframe" id="spellcheck_frame_body" name="spellcheck_frame_body"></iframe>
<div id="spellcheck_popup" class="wordSuggestion" style="position:absolute;visibility:hidden;"></div>

</td>
</tr>

{/if}


<tr>
<td class='tableCellTwo' style='width:25%;' align='right'>
<div class='defaultBold'>{lang:characters}</div>
</td>
<td class='tableCellTwo' style='width:75%;' valign='top'><input type="text" class="input" name="charsleft" size="5" maxlength="4" value="{lang:max_chars}" readonly="readonly" /></td>
</tr>

<tr>
<td class="tableCellTwo" style="width:25%" valign="top" align="right"><div class="itempad"><b>{lang:message_options}</b></div></td>
<td class="tableCellTwo" style="width:75%" valign="top">
<div class="default"><input type="checkbox" class="checkbox" name="sent_copy" value="y" {input:sent_copy_checked} /> {lang:sent_copy}</div>
<div class="default"><input type="checkbox" class="checkbox" name="hide_cc" value="y" {input:hide_cc_checked} /> {lang:hide_cc}</div>
</td>
</tr>

{if attachments_allowed}

<tr>
<td class="tableCellTwo" style="width:25%" valign="top" align="right">
<div class="default"><b>{lang:attachments}</b></div>
<div class="lighttext">{lang:max_size}&nbsp;{lang:max_file_size} {lang:kb}</div>
</td>
<td class="tableCellTwo">
<div class="default"><input type="file" name="userfile" size="20" class="input" /></div>
<div class="lighttext">{lang:click_preview_to_attach}</div>
</td>
</tr>

{/if}


{if attachments_exist}

<tr>
<td class="tableCellTwo" style="width:25%" valign="top" align="right">
<div class="default"><b>{lang:attachments}</b></div>
</td>
<td class="tableCellTwo" valign="bottom">
<div class="default">{include:attachments}</div>
</td>
</tr>

{/if}

<tr>
<td class="tableCellTwo" style="width:25%">&nbsp;</td>
<td class="tableCellTwo" style="width:75%"><div class="itempadbig">
<input type="submit" name="preview" class="submit" value="{lang:preview_message}" />&nbsp;&nbsp;&nbsp;
<input type="submit" name="draft" class="submit" value="{lang:draft_message}" />&nbsp;&nbsp;&nbsp;
<input type="submit" name="submit" class="submit" value="{lang:send_message}" />
</div>
</td>
</tr>

</table>

</form>
 
DOC;
 
 }
 /* END */
 	
 	
// -----------------------------------
//  View Message - USER
// ----------------------------------- 
    
function view_message()
{
	return <<< EOF
		
{form:form_declaration:view_message}

{include:hidden_js}
{include:folder_pulldown:move}
{include:folder_pulldown:copy}


<!--- Action Buttons for Message Top -->

<table cellpadding="0" cellspacing="3" border="0" width="100%">
<tr>
<td valign="middle">

{form:reply_button} {form:reply_all_button} {form:forward_button} {form:move_button} {form:copy_button} {form:delete_button}

</td>
</tr>
</table>

<!--- Contents of Message Message -->


<table class="tableBorderTopRight" cellpadding="0" cellspacing="0" border="0" width="100%" >
<tr>
<td class="tablePad" colspan="2"><div class='menuHeadingBG'><div class="tableHeading">{lang:private_message}</div></div></td>
</tr>

<tr>
<td class="tableCellTwo" style="width:130px; text-align:right;"><div class="defaultBold">{lang:message_sender}:</div></td>
<td class="tableCellOne">{include:sender}</td>
</tr>

<tr>
<td class="tableCellTwo" style="width:130px; text-align:right;"><div class="defaultBold">{lang:message_subject}:</div></td>
<td class="tableCellOne">{include:subject}</td>
</tr>

<tr>
<td class="tableCellTwo" style="width:130px; text-align:right;"><div class="defaultBold">{lang:message_date}:</div></td>
<td class="tableCellOne">{include:date}</td>
</tr>

<tr>
<td class="tableCellTwo" style="width:130px; text-align:right;"><div class="defaultBold">{lang:message_recipients}:</div></td>
<td class="tableCellOne">{include:recipients}</td>
</tr>


{if show_cc}

<tr>
<td class="tableCellTwo" style="width:130px; text-align:right;"><div class="defaultBold">{lang:cc}:</div></td>
<td class="tableCellOne">{include:cc}</td>
</tr>

{/if}



{if attachments_exist}

<tr>
<td class="tableCellTwo" style="width:130px; text-align:right;"><div class="defaultBold">{lang:attachments}:</div></td>
<td class="tableCellOne">{include:attachment_links}</td>
</tr>

{/if}


<tr>
<td colspan="2" class="tableCellOne" style="text-align:left;"><div class="itempadbig">{include:parsed_message}</div></td>
</tr>

</table>


</form>

EOF;
	
}
/* END */
	
	
	
// ------------------------------
//  Core Folder Template - User
// ------------------------------
function message_folder()
{
	return <<<EOT
				
{include:hidden_js}
{include:toggle_js}

<div class='menuHeadingBG'><div class="tableHeading">{lang:folder_name}</div></div>

<table border='0'  cellspacing='10' cellpadding='0' style='width:100%;' >
<tr>
<td  class='tablePad'>

<table border='0'  cellspacing='0' cellpadding='0' style='width:300px;' class="tableBorderTopRight">
<tr>
<td  class='tableCellOne'  style='width:100%;' colspan='3'>
{lang:storage_percentage}
</td>
</tr>
<tr>
<td class='tableCellOne'  style='width:100%;' colspan='3'>
<div style="width:{image:messages_graph:width}px; height:{image:messages_graph:height}px; background-color: #666699; border:1px solid #000;"></div>
</td>
</tr>
<tr>
<td class='tableCellOne' >0%</td>
<td class='tableCellOne'>
<div class='defaultCenter'>50%</div>
</td>
<td class='tableCellOne'>
<div class='defaultRight'>100%</div>
</td>
</tr>
</table>

</td>
<td class='tablePad' valign='top'>

<div class='defaultRight'>
<span class='defaultBold'>
<a href='{path:compose_message}'  onmouseover="window.status='{lang:compose_messages}';" onmouseout="window.status='';" >{lang:compose_message}</a>
</span>

</div>

<div class='defaultRight'>
<span class='defaultBold'>
<a href='{path:erase_messages}' onclick='if(!confirm("{lang:erase_popup}")) return false;'>{lang:erase_messages}</a>
</span>

</div>

<div class='defaultRight'>
<br />{lang:switch_folder} {include:folder_pulldown:change}
</div>

</td>
</tr>
</table>


{form:form_declaration:modify_messages}


{if paginate}
<table border='0'  cellspacing='5' cellpadding='0' class='tablePad' >
<tr>
<td  class='default' >
{include:pagination_link}
</td>
</tr>
</table>
{/if}

<div class="tablePad">

<table border='0' cellspacing='0' cellpadding='0' style='width:100%;'  class='tableBorderTopRight' >
<tr>
<td  class='tableCellOne'  style='width:5%;'>

<div class='defaultBold'>&nbsp;</div>

</td>
<td  class='tableCellOne'  style='width:40%;'>

<div class='defaultBold'>{lang:message_subject}</div>

</td>
<td  class='tableCellOne'  style='width:25%;'>

<div class='defaultBold'>{lang:message_sender}</div>

</td>
<td  class='tableCellOne'  style='width:25%;'>

<div class='defaultBold'>{lang:message_date}</div>

</td>
<td  class='tableCellOne'  style='width:5%;'>

<div class='defaultBold'><input class='checkbox' type='checkbox' name='toggleflag' value='' onclick="toggle(this);" />
</div>

</td>
</tr>

{include:folder_rows}
</table>

</div>

{if paginate}
<table border='0'  cellspacing='5' cellpadding='0' class='tablePad' >
<tr>
<td  class='default' >
<div>{include:pagination_link}</div>
</td>
</tr>
</table>

{/if}
{include:folder_pulldown:move}

{include:folder_pulldown:copy}

<div class='defaultPad'>&nbsp;{form:copy_button}{form:move_button}{form:delete_button}</div>
</form>

<div class='defaultRight'>{lang:storage_status}&nbsp;</div>

EOT;

}
/* END */



// ----------------------------------------
//  Folder Rows - USER
// ----------------------------------------

function message_folder_rows()
{
	return <<<EOT
		
<tr>
<td class='{style}' style='width:5%;'><div class="defaultCenter"><strong style="font-size:14px;">{message_status}</strong></div></td>
<td class='{style}' style='width:40%;'><a href="{message_url}">{message_subject}</a></td>
<td class='{style}' style='width:25%;'>{sender}</td>
<td class='{style}' style='width:25%;'>{message_date}</td>
<td class='{style}' style='width:5%;'><input type="checkbox" name="toggle[]" value="{msg_id}" /></td>
</tr>
		
EOT;

}
/* END */
	
	
	
// ----------------------------------------
//  No Folder Rows Template for Users
// ----------------------------------------

function message_no_folder_rows()
{
	return <<<EOT
<tr>
<td class='tableCellTwo' colspan="5" style='width:100%;'>
<div class="defaultCenter"><div class="defaultBold">{lang:no_messages}</div></div></td>
</tr>
EOT;
}
/* END */

	
	
	
// ---------------------------------
//  Search Members Template - User
// ---------------------------------
	
function search_members()
{
	return <<<EOT
	
{form:form_declaration:do_member_search}

<div class="spacer">&nbsp;</div>

<table class="tableBorderTopRight" cellpadding="0" cellspacing="0" border="0" width="400" align="center">
<tr>
<td class="profileHeadingBG" colspan="2"><div class="tableHeading">{lang:member_search}</div>
</td>

</tr>
<tr>
<td class="tableCellOne">

{if message}
<div class="highlight">
{include:message}
</div>
{/if}

<h3>{lang:screen_name}</h3>
<p>
<input style='width:100%' type='text' name='screen_name' id='screen_name' value='' size='35' maxlength='100' class='input'  />
</p>

<h3>{lang:email}</h3>
<p>
<input style='width:100%' type='text' name='email' id='email' value='' size='35' maxlength='100' class='input'  />
</p>


<h3>{lang:member_group}</h3>
<p>

<select name='group_id' class='select' >
<option value='any'>{lang:any}</option>
{include:member_group_options}</select>
</p>


<p><input type="submit" value="{lang:submit}" class="submit" /></p>
<div class="marginpad"><a href="JavaScript:window.close();">{lang:close_window}</a></div>

</td>
</tr>
</table>

</form>

	
EOT;
}
/* END */
	
	

// -----------------------------------
//  Member Results Template - USER
// -----------------------------------   
    
function member_results()
{
	return <<<DOT
    
<script type="text/javascript"> 
//<![CDATA[
        
function insert_name(name)
{
	if (opener.document.getElementById('submit_message').{which_field}.value != '')
	{
		opener.document.getElementById('submit_message').{which_field}.value += ', ';
}
	
	opener.document.getElementById('submit_message').{which_field}.value += name;
}

//]]>
</script>

<table class="tableBorderTopRight" cellpadding="0" cellspacing="0" border="0" width="400" align="center">
<tr>
<td class="profileHeadingBG" colspan="2"><div class="tableHeading">{lang:search_results}</div>
</td>

</tr>
<tr>
<td class="tableCellOne">

<p>
{include:search_results}
</p>

<div class="innerShade">{lang:insert_member_instructions}</div>


</td>
</tr>

<tr>
<td class="tableCellOne">
<div class="itempad">
<div align="center"><a href="{path:new_search_url}"><b>{lang:new_search}</b></a></div><br />
<div align="center"><a href="JavaScript:window.close();opener.document.getElementById('submit_message').{which_field}.focus();"><b>{lang:close_window}</b></a></div>
</div>
</td>
</tr>
</table>

DOT;

}
/* END */


// -----------------------------------
//  Member Results Row Template - USER
// -----------------------------------   
    
function member_results_row()
{
	return <<<DOT
<div class="itempad">{item}</div>
DOT;

}
/* END */
	
	
//-------------------------------------
//  Submission Error Message - USER
//-------------------------------------

function message_submission_error()
{
	return <<< EOF
		
<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>
<tr>
<td class='profileHeadingBG'><div class="tableHeading">{lang:error}</div></td>

</tr><tr>

<td class='tableCellOne'><div class='highlight'><div class="itempadbig">{lang:error_message}</div></div></td>

</tr>
</table>
EOF;

}
/* END */

	
	
//----------------------------------------
// 	Attachment Links - USER
//----------------------------------------

function message_attachment_link()
{
	return <<<EOT
<div class='default'>
<img src="{path:image_url}marker_file.gif" width="9" height="9" border="0" alt="" title="" />
<a href='{path:download_link}' title='{input:attachment_name}'>{input:attachment_name} ({input:attachment_size} {lang:file_size_unit})</a>
</div>
	
EOT;
}
/* END */
	
	
	
//----------------------------------------
// 	Attachments CP
//----------------------------------------

function message_attachments()
{
		return <<< EOF
		
<table class="tableBorderRight" cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td class="tableRowHeadingBold" width="40%">{lang:file_name}</td>
<td class="tableRowHeadingBold" width="30%">{lang:file_size}</td>
<td class="tableRowHeadingBold" width="30%">{lang:remove}</td>
</tr>
{include:attachment_rows}

</table>

EOF;

}
/* END */


//----------------------------------------
// 	Attachment Rows CP
//----------------------------------------

function message_attachment_rows()
{
		return <<< EOF
<tr>
<td class="tableCellOne">{input:attachment_name}</td>
<td class="tableCellOne">{input:attachment_size} {lang:file_size_unit}</td>
<td class="tableCellOne"><input type="submit" name="remove[{input:attachment_id}]" class="submit" value="{lang:remove}" /></td>
</tr>
EOF;

}
/* END */



// -----------------------------------
//  Edit Folders Form - USER
// -----------------------------------
    
function message_edit_folders()
{	
	return <<<DUDE
    	
{include:success_message}

<div class='menuHeadingBG'><div class="tableHeading">{lang:edit_folders}</div></div>

{form:form_declaration:edit_folders}


<table border='0'  cellspacing='0' cellpadding='0' style='width:100%;' >

<tr>
<td  class='tableCellOne' >
<div class='defaultBold'><div class="itemPad">{lang:folder_name}</div></div>
</td>
</tr>

{include:current_folders}
{include:new_folder}

</table>


<div class='itempad'>
<div class='innerShade'>&nbsp;{lang:folder_directions}</div>
</div>

<div class='itempadbig'>&nbsp;<input  type='submit' class='submit' value='{lang:submit}'  /></div>
</form>

DUDE;
}
/* END */
    
    
    
// -----------------------------------
//  Display Folder Template - USER
// -----------------------------------
    
function message_edit_folders_row()
{
	return <<<WHOA
<tr>
<td  class='{style}' style='width:100%;'>
<input type='text' name='folder_{input:folder_id}' id='folder_{input:folder_id}' value='{input:folder_name}' size='20' maxlength='20' class='input'  /> <span class="highlight">{lang:required}</span>
</td>
</tr>
    
WHOA;
    
}
	
	
	
// -----------------------------------
//  Block and Buddy List - USER
// -----------------------------------   
    
function buddies_block_list()
{
	return <<<JACK

{include:toggle_js}
{include:buddy_search_js}

<div class='menuHeadingBG'><div class="tableHeading">{include:member_search} {lang:list_title}</div></div>

{form:form_declaration:list}

<table border='0'  cellspacing='0' cellpadding='0' style='width:100%;'  class='tableBorderTopRight' >

<tr>

<td  class='tableCellOne'  style='width:20%;'>
<div class='defaultBold'>{lang:screen_name}</div>
</td>

<td  class='tableCellOne'  style='width:60%;'>
<div class='defaultBold'>{lang:member_description}</div>
</td>

<td  class='tableCellOne'  style='width:5%;'>
<div class='defaultBold'><input class='checkbox' type='checkbox' name='toggleflag' value='' onclick="toggle(this);" />
</div>
</td>

</tr>

{include:list_rows}
</table>

<div class="itempad">
<div class='defaultRight'>{form:add_button}&nbsp;&nbsp;{form:delete_button}&nbsp;&nbsp;</div>
</div>

</form>
    	
JACK;

}
/* END */
    
    
    
    
// -----------------------------------
//  Block and Buddy List Rows - USER
// -----------------------------------   
    
function buddies_block_row()
{
	return <<<DOG
<tr>
<td class='{style}' style='width:15%;'>
<a href="{path:send_pm}" title="{lang:private_message} - {screen_name}">{screen_name}</a>
</td>

<td class='{style}' style='width:60%;'>
{member_description}
</td>
<td class='{style}' style='width:5%;'>
<input class='checkbox' type='checkbox' name='toggle[]' value='{listed_id}'  />
</td>
</tr>
    	
DOG;

}
/* END */
	
	
// ----------------------------------------
//  Empty List - USER
// ----------------------------------------

function empty_list()
{
	return <<<TOY
<tr>
<td  class='tableCellTwo'  style='width:100%;' colspan='3'>

<div class='defaultCenter'>
<span class='defaultBold'>
{lang:empty_list}
</span>

</div>

</td>
</tr>
  	
TOY;

}
/* END */



// -----------------------------------
//  Bulletin Board - USER
// -----------------------------------   
    
function bulletin_board()
{
	return <<<ONEIL
	
{if message}
<div class='tableCellOne'><div class='success'>{include:message}</div></div>
{/if}
	
<div class='menuHeadingBG'><div class="tableHeading">{lang:bulletin_board}</div></div>

{if can_post_bulletin}
<table border='0'  cellspacing='0' cellpadding='0' style='width:100%;' >
<tr><td class='tableCellOne'>
<span class="defaultBold">&#187; <a href='{path:send_bulletin}' >{lang:send_bulletin}</a></span>
</td></tr>
</table>
{/if}

{if no_bulletins}
<div class="tableCellOne">
<span class="defaultBold">{lang:message_no_bulletins}</span>
</div>
{/if}


{if bulletins}
{include:bulletins}
{/if}

{if paginate}
<table border='0'  cellspacing='5' cellpadding='0' class='tablePad' >
<tr>
<td  class='default' >
{include:pagination_link}
</td>
</tr>
</table>
{/if}
    	
ONEIL;

}
/* END */


// -----------------------------------
//  Single Bulletin
// -----------------------------------   
    
function bulletin()
{
	return <<<JAFFA

<div class="{style}" id="bulletin_div_{bulletin_id}">

<span class="defaultBold">{lang:message_sender}</span>: {bulletin_sender}<br />
<span class="defaultBold">{lang:message_date}</span>: {bulletin_date}<br />
{if can_delete_bulletin}
<span class='defaultBold'>{lang:delete_bulletin}:&nbsp;<a href='{path:delete_bulletin}' onclick='if(!confirm("{lang:delete_bulletin_popup}")) return false;'>{lang:yes}</a></span><br />
{/if}

<div class="itempadbig">
<textarea name='bulletin_{bulletin_id}' readonly='readonly' style='width:100%' class='textarea' rows='8' cols='90'>{bulletin_message}</textarea>
</div>

</div>
    	
JAFFA;

}
/* END */



//-------------------------------------
//  Bulletin Sending Form
//-------------------------------------

function bulletin_form()
{
return <<< EOF

{form:form_declaration:sending_bulletin}

{if message}
<div class='tableCellOne'><div class='success'>{include:message}</div></div>
{/if}

<table border='0' cellspacing='0' cellpadding='0' style='width:100%'>

<tr>
<td class='profileHeadingBG' colspan="2"><div class="tableHeading">{lang:send_bulletin}</div></td>
</tr>

<tr>
<td class='tableCellOne' style="width:20%;"><div class='defaultBold'>{lang:member_group}</div></td>
<td class='tableCellOne' style="width:80%;">
<select name="group_id">
{group_id_options}
</select>
</td>
</tr>

<tr>
<td class='tableCellTwo' style="width:20%;"><div class='defaultBold'>{lang:bulletin_message}</div></td>
<td class='tableCellTwo' style="width:80%;"><textarea name='bulletin_message' style='width:100%' class='textarea' rows='10' cols='90'></textarea></td>
</tr>

<tr>
<td class='tableCellOne' style="width:20%;"><div class='defaultBold'>{lang:bulletin_date}</div></td>
<td class='tableCellOne' style="width:80%;">
<input type="text" style="width:80%" class="input" name="bulletin_date" size="20" value="{input:bulletin_date}" maxlength="50" />
</td>
</tr>


<tr>
<td class='tableCellOne' style="width:20%;"><div class='defaultBold'>{lang:bulletin_expires}</div></td>
<td class='tableCellOne' style="width:80%;">
<input type="text" style="width:80%" class="input" name="bulletin_expires" size="20" value="{input:bulletin_expires}" maxlength="50" />
</td>
</tr>

<tr>
<td class='tableCellTwo' colspan="2">
<div class='marginpad'>
<input type='submit' class='submit' value='{lang:submit}' />
</div>
</td>
</tr>

</table>

</form>
EOF;
}
/* END */
	

/* -------------------------------------
/* Edit Ignore List Form
/* -------------------------------------*/

function edit_ignore_list_form()
{
return <<<PHARLEY

{include:toggle_js}

<div class='menuHeadingBG'><div class="tableHeading">{include:member_search} {lang:ignore_list}</div></div>

{if success_message}<div class='tableCellOne'><div class='success'>{lang:message}</div></div>{/if}

{form:form_declaration}

<table border='0' cellspacing='0' cellpadding='0' style='width:100%;' class='tableBorderTopLeft' >

<tr>

<td class='tableCellOne' style='width:80%;'>
<div class='defaultBold'>{lang:screen_name}</div>
</td>

<td class='tableCellOne' style='width:5%;'>
<div class='defaultBold'><input class='checkbox' type='checkbox' name='toggleflag' value='' onclick="toggle(this);" />
</div>
</td>

</tr>

{include:edit_ignore_list_rows}

</table>

<div class="itempad">
<div class='defaultRight'>{form:add_button}&nbsp;&nbsp;{form:delete_button}&nbsp;&nbsp;</div>
</div>

</form>
PHARLEY;
}
/* END */


/* -------------------------------------
/* Edit Ignore List Rows
/* -------------------------------------*/

function edit_ignore_list_rows()
{
return <<<PHARLEY
<tr>
    <td class="{class}"><a href="{path:profile_link}">{name}</a></td>
    <td class="{class}"><input type='checkbox' name='toggle[]' value='{member_id}' /> </td>
</tr>
PHARLEY;
}
/* END */



}
// END CLASS
?>