<?php

if ( ! defined('EXT'))
{
    exit('Invalid file request');
}



/* ----------------------------------------
/*  Template - Page
/* ----------------------------------------*/

function wiki_page()
{
	ob_start();
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" lang="{lang}">
<head>
<title>{title} | {wiki_name}</title>
<meta http-equiv="content-type" content="text/html; charset={charset}" />
<meta name='MSSmartTagsPreventParsing' content='TRUE' />
<meta http-equiv='expires' content='-1' />
<meta http-equiv= 'pragma' content='no-cache' />
<meta name='robots' content='all' />



<style type='text/css'>

/*---------------------------------
	Default Body Attributes
---------------------------------*/
html, body {
	margin:            0;
	padding:           0;
}

body {
	font-family:       Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:         12px;
	color:             #38394B;
	background-color:  #F3FBFE;
}

/*---------------------------------
	Main Layout Positioning
---------------------------------*/
#sidebar {
	float:				left;
	width:				200px;
	padding:			10px 0px 10px 15px;
}

#wrapper {
	float:				right;
	width:				100%;
	margin-left:		-220px;
}

#mainContent {
	margin-left:		220px;
}

#content {
	margin:				0 12px;
	padding:			10px 15px;
	background-color:	#fff;
	border-left:		1px dotted #96B7DD;
	border-right:		1px dotted #96B7DD;
	border-bottom:		1px dotted #96B7DD;
}

/*---------------------------------
	Default Link Styles
---------------------------------*/

a, a:visited {
	text-decoration:   none;
	color:             #3B6CB5;
	background-color:  transparent;
}

a:hover {
	color:             #8AD5F5;
	text-decoration:   underline;
	background-color:  #000;
}

a.noArticle, a.noArticle:visited {
	background:			url({path:image_url}broken_link.gif) center left no-repeat;
	padding-left:		10px;
}

/*---------------------------------
	Default Tag and Misc Styles
---------------------------------*/

p {
	background:			transparent;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			12px;
	color:				#38394B;
	margin:				4px 0 8px 0;
}

img {
	border:				none;
}

p,li,dt,dd {
	margin-left:		0;
	padding-left:		0;
	line-height:		1.7em;
}

p {
	margin-bottom:		0.8em;
	margin-top:			0;
}

ul {
	padding-left:		24px;
}

form p {
	margin:				0;
}

.hidden {
	display:			none;
	visibility:			hidden;
}

hr {
	height:				0px;
	border:				dotted 0px;
	border-top-width:	1px;
}

h3 {
	font-weight:		normal;
	font-size:			14px;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	color:				#0F3A7F;
	margin:				20px 0 7px 0;
	padding:			8px 0 8px 0;
	border-top:			1px solid #0F3A7F;
	border-bottom:		1px solid #0F3A7F;
}

.lighttext {
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			10px;
	color:				#73769D;
	padding:			4px 0 2px 0;
	background-color:	transparent;  
}

code {
	white-space:		normal;
}

pre {
	background:			#E8F7FD;
	border:				1px solid #96B7DD;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	color:				#38394B;
	margin:				8px 2px 10px 6px;
	padding:			10px 10px 10px 6px;
	white-space:		normal;
}

.codeblock {
	background:			#E8F7FD;
	border-left:		1px solid #96B7DD;
	border-top:			1px solid #96B7DD;
	border-bottom:		1px solid #96B7DD;
	border-right:		1px solid #96B7DD;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	color:				#38394B;
	margin:				8px 4px 10px 3px;
	padding:			10px 10px 10px 6px;
	overflow:			auto;
}

blockquote {
	background:			#E8F7FD;
	border-left:		4px solid #5087C6;
	border-top:			1px solid #96B7DD;
	border-bottom:		1px solid #96B7DD;
	border-right:		1px solid #96B7DD;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	color:				#38394B;
	margin:				8px 2px 10px 6px;
	padding:			5px 10px 10px 6px;
}

/*---------------------------------
		Header Presentation
---------------------------------*/

#topBar {  
	background:			#3c6695 url({path:image_url}header_bg.gif) repeat-x left top;
}

.topBarLinks { 
	color:				#fff;
	background:			transparent;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	text-align:			center;
	padding:			15px 30px 6px 20px;
}

.topBarLinks a:link { 
	color:				#fff;
	background:			transparent;
	text-decoration:	underline;
} 

.topBarLinks a:visited { 
	color:				#fff;
	background:			transparent;
	text-decoration:	underline;
}

.topBarLinks a:hover { 
	color:				#B8BDED;    
	background:			transparent;
	text-decoration:	none;
}

#pageheader {  
	background:			#3F4585 url({path:image_url}bg_header.jpg) repeat-x left top;
	border-bottom:		1px solid #000;
}

/*---------------------------------
	Sub-Header Presentation
---------------------------------*/

#subheader {
	background-color:	#000;
	color:				#fff;
	padding:			4px 25px 3px 27px;
	border-bottom:		1px solid #8285AE;
	margin:				0 0 10px 0;
}

.breadcrumb {  
	background-color:	transparent;
	font-weight:		bold;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			10px;
}

.breadcrumb a, .breadcrumb a:link { 
	color:				#8AD5F5;
	font-weight:		normal;
	background:			transparent;
	text-decoration:	none;
} 

.breadcrumb a:visited { 
	color:				#8AD5F5;
	background:			transparent;
	text-decoration:	none;
}

.breadcrumb a:hover { 
	color:				#C5EAFA;    
	background:			transparent;
	text-decoration:	underline;
}

.breadcrumbspacer {
	color:				#fff;
}

/*---------------------------------
		Nav Menu Styles
---------------------------------*/

.navMenu {
	margin:				0;
	padding:			0;
}

.navMenu ul {
	list-style:			none;
	text-align:			center;
	margin:				0;
	padding:			0;
}

.navMenu li {
	padding: 			0;
	margin: 			0;
	display:			inline;
}

.navMenu a, .navMenu a:link, .navMenu a:visited {
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	font-weight:		bold;
	display: 			block;
	color: 				#2C3467;
	border-top: 		1px solid #8EBDE2;
	border-bottom: 		2px solid #DCF2FC;
	background-color: 	#C5EAFA;
	padding:			2px;
	text-decoration: 	none;
}

.navMenu a:hover {
	color:				#fff;
	background-color:	#68A7D9;
	border-top:			1px solid #68A7D9;
	border-bottom:		2px solid #5F9CD2;
}

/*---------------------------------
		Sidebar Item Styles
---------------------------------*/

#wikiLogo {
	text-align:			center;
	margin:				0 0 15px 0;

}

.sideItem {
	margin: 			0 0 15px 0;
	background-color:	#C5EAFA;
	border-right:		1px solid #5087C6;
	border-left:		1px solid #5087C6;
	border-bottom:		1px solid #5087C6;
}

ul.activeMembers {
	margin:				0;
	padding:			10px 10px 10px 30px;
	list-style:			square;
}

.itemHeader {
	background:			url({path:image_url}item_head_bg_right.gif) top right no-repeat;
	clear: both;
	margin: 0;
}

.itemHeader h2 {
	background:			url({path:image_url}item_head_bg_left.gif) top left no-repeat;
}

/*---------------------------------
	Article Action Menu Styles
---------------------------------*/

#actMenu {
	float:				right;
	clear:				both;
}

#actMenu ul {
	list-style: 		none;
	margin: 			0;
	padding: 			0;
}

#actMenu li {
	float:				left;
	text-align:			center;
	margin:				0;
	padding:			0;
	background: 		url({path:image_url}act_menu_bg_right.gif) top right no-repeat;
}

#actMenu a, #actMenu:link, #actMenu:visited {
	display:			block;
	padding:			5px 18px 5px 15px;
	min-width:			80px;
	background:			url({path:image_url}act_menu_bg_left.gif) top left no-repeat;
	text-decoration:	none;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	font-weight:		bold;
	color: 				#2C3467;
}

#actMenu a:hover {
	color:				#fff;
}

/*---------------------------------
		Article Titles
---------------------------------*/

#articleTitle {
	background:			#5087C6 url({path:image_url}title_bg_top.gif) top left no-repeat;
	clear:				both;
}


h2 {
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			14px;
	font-weight:		bold;
	color: 				#fff;
	margin:				0;
	padding:			8px 20px;
	background:			url({path:image_url}title_bg_bottom.gif) bottom left no-repeat;
	border:				0;
}

/*---------------------------------
		Footer Presentation
---------------------------------*/

#footer {
	border:				1px solid #eee;
	padding:			5px 0 5px 0;
	clear:				both;
	text-align:			center;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			9px;
	color:				#889;
	line-height:		15px;
	margin-top:			20px;
	margin-bottom:		15px;
}

/*---------------------------------
		Form Styles
---------------------------------*/

form {
	margin:				0;
	padding:			10px;
	border:				0;
}

.input {
	border-top:			2px solid #979AC2;
	border-left:		2px solid #979AC2;
	border-bottom:		1px solid #979AC2;
	border-right:		1px solid #979AC2;
	color:				#333;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	height:				1.7em;
	padding:			0;
	margin:				0;
}

.textarea {
	border-top:			2px solid #979AC2;
	border-left:		2px solid #979AC2;
	border-bottom:		1px solid #979AC2;
	border-right:		1px solid #979AC2;
	color:				#333;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	padding:			0;
	margin:				5px;
}

.checkbox {
	background-color:	transparent;
	padding:			0;
	border:				0;
}

.submit {
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	font-weight:		bold;
	border-top:			1px solid #9EA3D5;
	border-left:		1px solid #9EA3D5;
	border-right:		1px solid #000;
	border-bottom:		1px solid #000;
	letter-spacing:		.1em;
	margin:				0;
	padding:			1px 4px 1px 4px;
	background-color:	#3F4471;
	color:				#fff;
	cursor:				pointer;
}

/*---------------------------------
		Misc Class Styles
---------------------------------*/

.spacer {
	margin-bottom:		12px;
}

.itempad {
	padding:			2px 0 2px 0;
}

.itempadbig {
	padding:			5px 0 5px 0;
}

.bottompad {
	padding:			0 0 2px 0;
}

.marginpad {
	margin:				12px 0 10px 3px;
}

.leftpad {
	margin:				0;
	padding:			0 0 0 4px;
}

.default, .defaultBold, .defaultRight, .defaultCenter {
	background:			transparent;
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	color:				#38394B;
}

.defaultBold {
	font-weight:		bold;
}

.defaultRight {
	text-align:			right;
}

.defaultCenter {
	text-align:			center;
}  

.alert {
	font-family:		Lucida Grande, Lucida Sans Unicode, Sans-serif;
	font-size:			11px;
	color:				#990000;
	font-weight:		bold;
}

.highlight {
	color:				#990000;
}

</style>

</head>
<body>
<div id='topBar'>
<div class='topBarLinks'>

{if logged_out}
	<a href="{path:login}">Login</a>	
	&nbsp;&nbsp;&middot;&nbsp;&nbsp;
	<a href="{path:register}">Register</a>	
	&nbsp;&nbsp;&middot;&nbsp;&nbsp;
	<a href="{path:memberlist}">Member List</a>
{/if}

{if logged_in}
	Logged in as:&nbsp;&nbsp;<b>{screen_name}</b>
	&nbsp;&nbsp;&middot;&nbsp;&nbsp;
	<a href="{path:your_profile}">Your Profile</a>
	&nbsp;&nbsp;&middot;&nbsp;&nbsp;
	<a href="{path:your_control_panel}">Your Control Panel</a>
	&nbsp;&nbsp;&middot;&nbsp;&nbsp;
	<a href="{path:memberlist}">Member List</a>
	&nbsp;&nbsp;&middot;&nbsp;&nbsp;
	<a href="{path:logout}">Log-Out</a>
{/if}

</div>
</div>

<!-- END Subheader -->

<div id="subheader">
<div class="itempadbig"><div class="breadcrumb">

<a href="{path:wiki_home}">Wiki Home</a>
<span class="breadcrumbspacer">&nbsp;&nbsp;&gt;&nbsp;&nbsp;</span>

{if edit_article}
<a href="{path:view_article}">{title}</a>
{if can_edit}<span class="breadcrumbspacer">&nbsp;&nbsp;&gt;&nbsp;&nbsp;</span>Editing{/if}
{if cannot_edit}<span class="breadcrumbspacer">&nbsp;&nbsp;&gt;&nbsp;&nbsp;</span>Source{/if}

{if:elseif article_history}
<a href="{path:view_article}">{title}</a>
<span class="breadcrumbspacer">&nbsp;&nbsp;&gt;&nbsp;&nbsp;</span>History

{if:elseif revision}
<a href="{path:view_article}">{title}</a>
<span class="breadcrumbspacer">&nbsp;&nbsp;&gt;&nbsp;&nbsp;</span>Revision - {revision_id}

{if:else}
{title}
{/if}

</div></div>
</div>

<!-- END Subheader -->


<div id="wrapper">
<div id="mainContent">


<div id="actMenu">
<ul>
{if special_page}
<li><a href="{path:view_article}">Special Page</a></li>
{if:elseif file_page}
<li><a href="{path:view_article}">File Page</a></li>
{if:else}
<li><a href="{path:view_article}">View</a></li>
{if can_edit}
<li><a href="{path:edit_article}{if revision}revision/{revision_id}/{/if}">Edit</a></li>
<li><a href="{path:article_history}">History</a></li>
{/if}
{/if}
</ul>
</div>


<div id="articleTitle">
<h2>
{if edit_article}
{if can_edit}Editing: {/if}
{if cannot_edit}View Source: {/if}
{/if}

{if article_history}
Viewing History: 
{/if}
{if revision}
Revision: 
{/if}

{if topic == 'index'}
{wiki_name}
{if:else}
{title}
{/if}
</h2>
</div>

<!-- Begin Main Content of Page -->

<div id="content">

{if redirected}
<div class="lighttext">Redirected from: <a href="{path:original_page}">{original_page}</a></div>
{/if}

{if redirect_page}
<div class="lighttext">Page Redirects To: <a href="{path:redirect_page}">{redirect_page}</a></div>
{/if}

{if new_article}
<strong>This Article Does Not Exist Yet.</strong><br /><br />
{if can_edit}
Click Edit to Create!
{/if}
{if cannot_edit}
You are unable to add articles to the wiki. Sorry!
{/if}
{/if}


{wiki:page}
</div>

</div>
</div>
 

<div id="sidebar">

<div id="wikiLogo">
<a href="{path:wiki_home}"><img src="{path:image_url}wiki_logo.gif" height="100" width="150" alt="Wiki Home" /></a>
</div>

<div class="itemHeader">
<h2>Navigation</h2>
</div>
<div class="navMenu sideItem">
<ul>
<li><a href="{path:wiki_home}">Wiki Home</a></li>
<li><a href="http://expressionengine.com/docs/modules/wiki/getting_started.html">Wiki Help</a></li>
<li><a href="{path:wiki_home}{special_namespace}:Categories" title="{special_namespace}:Categories">Categories</a></li>
<li><a href="{path:wiki_home}{special_namespace}:Titles" title="List of Titles">Title List</a></li>
<li><a href="{path:wiki_home}{special_namespace}:Uncategorized" title="Uncategorized Pages">Uncategorized Pages</a></li>
<li><a href="{path:wiki_home}{special_namespace}:Random_page" title="Random Page">Random Page</a></li>
{if uploads}
<li><a href="{path:wiki_home}{special_namespace}:Uploads" title="Uploads">File Upload</a></li>
<li><a href="{path:wiki_home}{special_namespace}:Files" title="Uploaded Files">Uploaded Files</a></li>
{/if}
<li><a href="{path:wiki_home}{special_namespace}:Recentchanges" title="Recent Changes Page">Recent Changes</a></li>
<li><a href="{path:wiki_home}{special_namespace}:Recentchanges_RSS" title="Recent Changes in RSS">RSS</a></li>
<li><a href="{path:wiki_home}{special_namespace}:Recentchanges_Atom" title="Recent Changes in Atom">Atom</a></li>
{if article}<li><a href="{path:wiki_home}{special_namespace}:Associated_Pages/{url_title}/" title="What Links Here">What Links Here</a></li>{/if}
</ul>
</div>

<div class="itemHeader">
<h2>Search:</h2>
</div>

<div class="sideItem">
<form method="post" action="{path:wiki_home}{special_namespace}:Search_results/">
<div><input type="hidden" name="XID" value="{XID_HASH}" /></div>
<input type="text" class="input" name="keywords" size="20" value="" maxlength="80" style="width:110px" />
&nbsp;<input type="submit" class="submit" value="Search" />
</form>
</div>

<div class="itemHeader">
<h2>Create or Find Page:</h2>
</div>

<div class="sideItem">
<form method="post" action="{path:wiki_home}{special_namespace}:Find_page/">
<div><input type="hidden" name="XID" value="{XID_HASH}" /></div>
<input type="text" class="input" name="title" size="20" value="" maxlength="80" style="width:110px" />
&nbsp;<input type="submit" class="submit" value="Go" />

</form>
</div>


<div class="itemHeader">
<h2>Active Members:</h2>
</div>

<div class="sideItem">
<ul class="activeMembers">
{wiki:active_members}
<li><a href="{path:member_profile}">{name}</a></li>
{/wiki:active_members}
</ul>
</div>

</div>


<div id="footer">
<a href="http://expressionengine.com/">Powered by ExpressionEngine</a><br />
ExpressionEngine Wiki - Version {module_version}<br />
Script Executed in {elapsed_time} seconds
</div>

</body>
</html>
	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */


/** Variables List for wiki_page():

CONDITIONAL VARIABLES: 
{if new_article}		- New Article
{if article}			- Article being displayed
{if redirected}			- Redirect to this page
{if redirect_page}		- Page redirects to another page
{if revision}			- Revision page being displayed
{if edit_article}		- Editing page for article
{if article_history}	- History page for article
{if special_page}		- Special page in Wiki
{if file_page}			- File page in Wiki
{if can_edit}			- Can Edit Article
{if cannot_edit}		- Cannot edit article
{if can_admin}			- User is Administrator
{if cannot_admin}		- User is not Administrator
{if uploads}			- Uploads are allowed and upload info is valid

SINGLE VARIABLES:
{elapsed_time}	- Time to render page
{XID_HASH}		- Required value for hidden form field 'XID', for secure forms
{wiki_name}		- Your Wiki's Name
{title}			- Title/Name of page (works with Special and File pages too)
{url_title}		- The URL title of page
{revision_id}	- If viewing a revision, the revision id for that revision
{original_page}	- If redirected, the name of the original page. Use inside 'redirected' conditional
{redirect_page} - If page redirects, the page to which it redirects.  Use inside 'redirect_page' conditional

NAMESPACE VARIABLES:
{namespace}				- Current namespace for page, if any
{special_namespace}		- The 'Special' namespace
{category_namespace}	- The 'Category' namespace
{file_namespace}		- The 'File' namespace
{image_namespace}		- The 'Image' namespace

PATH VARIABLES:
{path:wiki_home}		- Homepage for wiki, useful for links, ex: {path:wiki_home}Special:Find_page
{path:view_article}		- If on article pages, path to view article
{path:edit_article}		- If on article pages, path to edit article
{path:article_history}	- If on article pages, path to see article history

MEMBER VARIABLES:
{if logged_out}			- Conditional for seeing if person IS NOT logged in
{if logged_in}			- Conditional for seeing if person IS logged in
{screen_name}			- Screen Name for Logged in User
{path:login}			- Path to Login page
{path:register}			- Path to Register page
{path:memberlist}		- Path to Member List
{path:your_profile}		- Path to Your Profile page for logged in user
{path:your_control_panel} - Path to Your Control Page for logged in user
{path:logout}			- Path to Logout

NOTE:  The {exp:wiki} tag has a possible parameter called profile_path="".  When
this parameter is used, you can direct the member paths from the default member area
of EE to the forum member area.  Obviously, this requires the availablity of the 
Discussion Forum module.  Simply enter the template_group/template or trigger word 
for your forum and the path will be create automatically.

TAG VARIABLES:
{wiki:custom_namespaces_list}	- Used for displaying list of custom namespaces including the Categories namespace
	--- SINGLE VARIABLES:
		{namespace_short_name}	- Short Name for the Namespace (used internally, in forms, and URLs)
		{namespace_label}		- Label for the Namespace
		{namespace_selected}	- Outputs selected="selected" if the short name found in the URL after the article/page
*/

/* ----------------------------------------
/*  Template - Article
/* ----------------------------------------*/

function wiki_article()
{
	ob_start();
	?>
{if revision}
<span class="lighttext">Revision from: {revision_date format="%H:%i, %j %M %Y"}</span><br /><br />
{/if}

{article}

<!-- BEGIN 'Category' Namespace Stuff -->

{if namespace == category_namespace}

<div>
{wiki:category_subcategories style="nested"}

{header}
<h3>Subcategories:</h3>
There {if subcategory_total == '1'}is 1 subcategory{if:else}are {subcategory_total} subcategories{/if} for this category
{/header}

<a href="{path:view_category}">{category_name}</a>

{/wiki:category_subcategories}
</div>



<div>
{wiki:category_articles limit="100"}

{header}
<h3>Articles in category "{topic}":</h3>
<div>There {if articles_total == '1'}is 1 article{if:else}are {articles_total} articles{/if} for this category</div>
<div class="itempadbig">
{/header}

{letter_header}<div class="defaultBold">{letter}</div>{/letter_header}

<p> - <a href="{path:view_article}">{title}</a></p>

{footer}</div>{/footer}
{/wiki:category_articles}
</div>

{/if}

<!-- END 'Category' Namespace Stuff -->


<!-- BEGIN Categories for Page -->

{if categories}
<h3>Categories:</h3>
{wiki:categories style="nested"}
<a href="{path:view_category}">{category_name}</a>
{/wiki:categories}
{/if}

<!-- END Categories for Page -->
	
	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_article():

Along with the variables from wiki_page(), this template also has:

SINGLE VARIABLES:
{author}					- Displays the screen name of the member who wrote article
{article} 					- Display the fully rendered article's text
{revision_date format=""}	- If revision, the date of this revision

CONDITIONAL VARIABLES:
{if categories} 			- Categories for this article?

TAG VARIABLES:
{wiki:categories}	- Used for displaying list of available categories
	--- PARAMETERS
		backspace="#"			- Removes # number of characters from output at end of loop.  Useful for removing commans and <br />'s.
		show_empty="n"			- Show categories that have no articles assigned to them?
		style="nested"			- Automatically nests categories for you.
	--- CONDITIONAL VARIABLES:
		{if depth == '0'}
		{if children}
		{if first_child}
		{if last_child}
	--- SINGLE VARIABLES:
		{path:view_category}		- Link to view category page
		{category_name}				- Category name, duh...
		{depth}						- How many nested layers down the category is
		{count}						- "count" of the current category being displayed
		{switch="one|two|three"}	- Rotate between any number of values, separated by a pipe symbol

CATEGORY PAGE RELATED TAG VARIABLES:
{wiki:category_subcategories}	- Used for displaying list of subcategories (and their children) for a category
	--- PARAMETERS
		backspace="#"			- Removes # number of characters from output at end of loop.  Useful for removing commans and <br />'s.
		style="nested"			- Automatically nests categories for you.
	--- PAIR VARIABLES:
		{header}{/header}		- Content to go before this tag's content
		{footer}{/footer}		- Content to go after this tag's content
	--- CONDITIONAL VARIABLES:
		{if subcategory_total == '0'}
	--- SINGLE VARIABLES:
		{path:view_category}		- Link to view category page
		{category_name}				- Category name, duh...
		{subcategory_total}			- How many subcategories for this category
		{count}						- "count" of the current category being displayed
		{switch="one|two|three"}	- Rotate between any number of values, separated by a pipe symbol
		
{wiki:category_articles}		- Used for displaying list of articles with this category
	--- PARAMETERS
		backspace="#"			- Removes # number of characters from output at end of loop.  Useful for removing commans and <br />'s.
		limit="10"				- Limit the number of articles to show (will do pagination)
	--- PAIR VARIABLES:
		{header}{/header}		- Content to go before this tag's content
		{footer}{/footer}		- Content to go after this tag's content
		{letter_header}{/letter_header} - Looks at first character of each article, when there is a new character it will create a header.  Good for separating and organizing content by first letter
	--- CONDITIONAL VARIABLES:
		{if articles_total == '0'}
	--- SINGLE VARIABLES:
		{path:view_category}		- Link to view category page
		{category_name}				- Category name, duh...
		{articles_total}			- How many articles with this category
		{count}						- "count" of the current article being displayed
		{switch="one|two|three"}	- Rotate between any number of values, separated by a pipe symbol

*/


/* ----------------------------------------
/*  Template - History
/* ----------------------------------------*/

function wiki_history()
{
	ob_start();
	?>
	
{if no_history}
There is no history for this page.
{/if}

{if history}
{wiki:revisions}
<div class="itempad">
<a href="{path:revision_link}">{revision_date format="%H:%i, %j %M %Y"}</a> 
&nbsp; - &nbsp;
<a href="{path:member_profile}">{revision_author}</a> 
{if notes}({revision_notes}){/if} 

{if can_admin}
{if revision_status == 'closed'} [<a href="{path:open_revision}">Closed</a>] {/if}{if revision_status == 'open'} [<a href="{path:close_revision}">Open</a>] {/if}
{/if}
</div>
{/wiki:revisions}
{/if}

	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_history():

Along with the variables from wiki_page(), this template also has:

CONDITIONAL VARIABLES
{if no_history}		- If no history for this article
{if history}		- If history for this article

TAG VARIABLES:
{wiki:revisions}	- Used for displaying revisions for this article
	--- SINGLE VARIABLES:
		{revision_date format=""}	- The date of this revision
		{revision_author}			- Screen Name of Author of this revision
		{revision_notes}			- Any notes submitted for this revision
		{revision_status}			- Revision open or closed
		{count}						- "count" of the current article being displayed
		{switch="one|two|three"}	- Rotate between any number of values, separated by a pipe symbol
	--- CONDITIONAL VARIABLES
		{if notes}					- If revision notes exist for this revision
		{if revision_status}		- Use to determine if status is 'open' or 'closed'
*/

/* ----------------------------------------
/*  Template - Edit
/* ----------------------------------------*/

function wiki_edit()
{
	ob_start();
	?>

{if can_edit}

{if moderated && cannot_admin}
<div class="defaultBold">Submissions to this topic are moderated and will not go live until approved by an Admin.</div><br />
{/if}

{if old_revision}
<div class="defaultBold">WARNING: You are editing an out of date revision and submitting will overwrite more recent versions of this article.</div><br />
{/if}

{if preview}
<div class="defaultBold">WARNING: You are viewing a preview of your submission.  No changes to the article have been made.</div>
<div class="preview">{preview}</div>
{/if}

{form_declaration:wiki:edit}
<textarea style='width:98%;' name='article_content' id='article_content' cols='90' rows='25' class='textarea'>{content}</textarea>

<!-- Little Help for Uploading/Linking Files -->

{if uploads}
<script type="text/javascript">

function show_uploads()
{
	var obj = document.getElementById('recent_uploads');
	
	if (obj.style.display == "block")
		obj.style.display = "none";
	else
		obj.style.display = "block";
}

function add_tag (ourTag)
{
	var theField = document.getElementById('article_content');
	
	if (document.selection) 
	{
		theSelection = document.selection.createRange().text;
		theField.focus();
		document.selection.createRange().text = ourTag;
		theSelection = '';
		theField.blur();
		theField.focus();
		return;
	}
	else if ( ! isNaN(theField.selectionEnd))
	{
		var selLength = theField.textLength;
		var selStart = theField.selectionStart;
		var selEnd = theField.selectionEnd;
		if (selEnd <= 2 && typeof(selLength) != 'undefined')
			selEnd = selLength;

		var s1 = (theField.value).substring(0,selStart);
		var s2 = (theField.value).substring(selStart, selEnd)
		var s3 = (theField.value).substring(selEnd, selLength);
		
		var newStart = selStart + ourTag.length;
		theField.value = s1 + ourTag + s3;
		
		theField.focus();
		theField.selectionStart = newStart;
		theField.selectionEnd = newStart;
		return;
	}
	
	theField.value += ourTag;
}

</script>

&nbsp;<a href="javascript:void(0);" onclick="show_uploads();return false;">View 10 Most Recent File Uploads</a>

<div class="codeblock" id="recent_uploads" style="display:none;">
<div class="defaultBold">Recent Files:</div>
{wiki:files limit="10" orderby="upload_date" sort="desc"}
<div class="itempad"><a href="{path:view_file}">{file_name}</a> - Uploaded: {upload_date format="%j %M %Y"}: 
[<a href="javascript:void(0);" onclick="add_tag('[[{file_namespace}:{file_name}]]');return false;">Add File</a>]
{if is_image} - [<a href="javascript:void(0);" onclick="add_tag('[[{image_namespace}:{file_name}]]');return false;">Add Image</a>]{/if}</div>
{/wiki:files}
</div>
{/if}

<div class="codeblock">
Notes:&nbsp; <input class="input" name="revision_notes" id="revision_notes" value="{revision_notes}" type="text" size="50" maxlength="80" />
</div>

<div class="codeblock">
Redirect:&nbsp; <input class="input" name="redirect" id="redirect" type="text" value="{redirect_page}" size="50" maxlength="80" />
</div>

{if can_admin}
<div class="codeblock">
Rename:&nbsp; <input class="input" name="rename" id="rename" type="text" value="{rename}" size="50" maxlength="80" />
</div>

<div class="codeblock">
<input name="lock_article" id="lock_article" class="checkbox" type="checkbox" value="y" {if locked}checked="checked"{/if} /> Lock Article
&nbsp;&nbsp;&nbsp;
<input name="moderate_article" id="moderate_article" class="checkbox" type="checkbox" value="y" {if moderated}checked="checked"{/if} /> Moderate Article
&nbsp;&nbsp;&nbsp;
<input name="delete_article" id="delete_article" class="checkbox" type="checkbox" value="y" /> {if namespace == category_namespace}Delete Category{if:else}Delete Article{/if}
</div>
{/if}

<input name="submit" class="submit" type="submit" value="Submit" onclick="is_preview = false;" />
<input name="preview" class="submit" type="submit" value="Preview" onclick="is_preview = true;" />
{/if}


{if cannot_edit}
<p><strong>You are not allowed to edit this page.</strong></p>

<textarea style='width:98%;' cols='90' rows='25' class='textarea' readonly="readonly">{content}</textarea>

{/if}


</form>
	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_edit():

Along with the variables from wiki_page(), this template also has:

CONDITIONAL VARIABLES:
{if moderated}		- Is this article moderated (NOTE:  Admins are never moderated, so cannot_admin is sometimes used with this)
{if old_revision}	- Are they editing an old revision for this article? 
					  Old revisions can be edit and submitted as fresh revisions
{if locked}			- Is this article locked. Used for when an admin is editing an article

FORM VARIABLES:
{form_declaration:wiki:edit}	- Used to create the necessary opening <form> tag with hidden fields

SINGLE VARIABLES:
{content}			- The current content for this article
{redirect_page}		- Redirect page, if one specified

TAG VARIABLES
{wiki:files}
	--- PARAMETERS
		limit=""	- Limit how many files to display
		paginate=""	- Paginate on "top", "bottom", or "both"
	--- SINGLE VARIABLES:
		{file_name}					- File name for file
		{file_type}					- MIME Type for file
		{summary}					- Summary given when file uploaded
		{path:view_file}			- URL to view file's page
		{upload_date format=""}		- Date file was uploaded
		{author}					- Screen name of person who uploaded file
		{email}						- Email of Author
		{url}						- URL for Author
		{path:author_profile}		- Path to Author's Profile
		{count}						- "count" of the current file being displayed
		{switch="one|two|three"}	- Rotate between any number of values, separated by a pipe symbol
		{preview}					- The parsed preview of the submission
	--- CONDITIONAL VARIABLES:
		{if is_image}				- Is file an image?
		{if is_preview}				- Are we previewing a page?
		
PAGINATION: http://expressionengine.com/docs/modules/weblog/pagination_page.html

*/


/* ----------------------------------------
/*  Template - Special Page: RSS
/* ----------------------------------------*/

function wiki_special_rss()
{
	ob_start();
	
	echo '<?xml version="1.0" encoding="{charset}"?>';
	
	?>
	
<rss version="2.0"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:admin="http://webns.net/mvcb/"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:content="http://purl.org/rss/1.0/modules/content/">

    <channel>
    
    <title>{exp:xml_encode}{wiki_name}{/exp:xml_encode}</title>
    <link>{path:wiki_home}</link>
    <description>{exp:xml_encode}{wiki_name}{/exp:xml_encode}</description>
    <dc:language>{language}</dc:language>
    <dc:creator>{webmaster_email}</dc:creator>
    <dc:rights>Copyright 2009</dc:rights>
    <dc:date>{gmt_last_updated format="%Y-%m-%dT%H:%i:%s%Q"}</dc:date>
    <admin:generatorAgent rdf:resource="http://expressionengine.com/" />
    
{wiki:recent_changes}
    <item>
      <title>{exp:xml_encode}{title}{/exp:xml_encode}</title>
      <link>{exp:xml_encode}{path:view_article}{/exp:xml_encode}</link>
      <guid>{exp:xml_encode}{path:view_article}{/exp:xml_encode}</guid>
      <description><![CDATA[{article}]]></description>
      <dc:subject>{exp:xml_encode}{title}{/exp:xml_encode}</dc:subject>
      <dc:date>{gmt_revision_date format="%Y-%m-%dT%H:%i:%s%Q"}</dc:date>
    </item>
{/wiki:recent_changes}
    
    </channel>
</rss>
	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_rss():

See wiki_special_recent_changes();

*/


/* ----------------------------------------
/*  Template - Special Page: Atom
/* ----------------------------------------*/

function wiki_special_atom()
{
	ob_start();

	echo '<?xml version="1.0" encoding="{charset}"?>';
	
	?>

<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="{language}">

    <title type="text">{exp:xml_encode}{wiki_name}{/exp:xml_encode}</title>
    <subtitle type="text">{exp:xml_encode}{wiki_name}{/exp:xml_encode}</subtitle>
    <link rel="alternate" type="text/html" href="{path:wiki_home}" />
    <link rel="self" type="application/atom+xml" href="{path:wiki_home}{special_namespace}:Recentchanges_Atom" />
    <updated>{gmt_last_updated format='%Y-%m-%dT%H:%i:%sZ'}</updated>
    <rights>Copyright (c) 2009, {webmaster_email}</rights>
    <generator uri="http://expressionengine.com/" version="{version}">ExpressionEngine</generator>
    <id>tag:{trimmed_url},{gmt_last_updated format="%Y:%m:%d"}:wiki</id>

{wiki:recent_changes}
    <entry>
      <title>{exp:xml_encode}{title}{/exp:xml_encode}</title>
      <link rel="alternate" type="text/html" href="{path:view_article}" />
      <id>tag:{trimmed_url},{gmt_revision_date format="%Y"}:wiki:{exp:xml_encode}{title}{/exp:xml_encode}/{page_id}.{revision_id}</id>
      <published>{gmt_revision_date format="%Y-%m-%dT%H:%i:%sZ"}</published>
      <updated>{gmt_revision_date format='%Y-%m-%dT%H:%i:%sZ'}</updated>
      <author>
            <name>{author}</name>
            <email></email>
      </author>
      <content type="html"><![CDATA[
        {article}
      ]]></content>
    </entry>
{/wiki:recent_changes}

</feed>


	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_atom():

See wiki_special_recent_changes();

*/


/* ----------------------------------------
/*  Template - Special Page: Associated Pages
/* ----------------------------------------*/

function wiki_special_associated_pages()
{
	ob_start();

	?>

{wiki:associated_pages}

{if no_results}
<h3>No Articles Link to {article_title}</h3>
{/if}

{header}<h3>Articles that link to {article_title}</h3>
<p>The following pages link to <a href="{path:view_orig_article}">{article_title}</a>:</p>
<ul>
{/header}

<li><a href="{path:view_article}">{title}</a></li>

{footer}</ul>{/footer}

{/wiki:associated_pages}

	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_associated_articles():

Along with the variables from wiki_page(), this template also has:

TAG VARIABLES:
{wiki:associated_articles}	- Used for displaying list of recent changes
	--- SINGLE VARIABLES:
		{title}							- Page Title
		{path:view_article}				- Link to View Article
		{article_title}					- Page Title of the originating article
		{path:view_orig_article}		- Link to view the originating article
		{count}							- "count" of the current revision being displayed
		{switch="one|two|three"}		- Rotate between any number of values, separated by a pipe symbol
	--- PAIR VARIABLES:
		{header}{/header}				- Content to go before this tag's content
		{footer}{/footer}				- Content to go after this tag's content
	--- CONDITIONAL VARIABLES:
		{if no_results}					-- Content to display if there are no associated articles

*/



/* ----------------------------------------
/*  Template - Special Page: Recent Changes
/* ----------------------------------------*/

function wiki_special_recent_changes()
{
	ob_start();

	?>


{wiki:recent_changes limit="20" paginate="bottom"}

<a href="{path:view_article}">{title}</a> &nbsp; - &nbsp; {revision_date format="%H:%i, %j %M %Y"} &nbsp; - &nbsp; <a href="{path:author_profile}">{author}</a> {if notes}({notes}){/if}<br />

{paginate}
<br /><div>Page {current_page} of {total_pages} pages {pagination_links}</div>
{/paginate}

{/wiki:recent_changes}

	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_recent_changes():

Along with the variables from wiki_page(), this template also has:

SINGLE VARIABLES:
{gmt_last_updated format=""}	- The GMT time for when the wiki was last updated
{last_updated format=""}		- The localized time (for user) when the wiki was last updated
{language}						- Language being used (XML Language format)

TAG VARIABLES:
{wiki:recent_changes}	- Used for displaying list of recent changes
	--- PARAMETERS
		limit=""	- Limit how many files to display
		paginate=""	- Paginate on "top", "bottom", or "both"
	--- SINGLE VARIABLES:
		{gmt_revision_date format=""}	- The GMT time for when the article was last revised
		{revision_date format=""}		- The localized time for when the article was last revised
		{title}							- Page Title
		{revision_id}					- Revision ID 
		{page_id}						- Page ID (used for identifying purposes in RSS/Atom Feeds)
		{author}						- Screen Name of revision author
		{email}							- Email for Revision Author
		{url}							- URL for Revision Author
		{revision_notes}				- Notes about revision, if any
		{path:view_article}				- Link to View Article
		{content}						- Content of revision
		{article}						- Fully rendered article
		{count}							- "count" of the current revision being displayed
		{absolute_count}				- Running count, takes pagination into consideration
		{switch="one|two|three"}		- Rotate between any number of values, separated by a pipe symbol
		
PAGINATION: http://expressionengine.com/docs/modules/weblog/pagination_page.html

*/



/* ----------------------------------------
/*  Template - Special Page: Categories
/* ----------------------------------------*/

function wiki_special_categories()
{
	ob_start();

	?>

{wiki:categories style="nested"}
<a href="{path:view_category}">{if depth == '0'}<strong>{/if}{category_name}{if depth == '0'}</strong>{/if}</a>
{/wiki:categories}

	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_categories():

Along with the variables from wiki_page(), this template also has:

TAG VARIABLES:
{wiki:categories}	- Used for displaying list of available categories
	--- PARAMETERS
		backspace="#"			- Removes # number of characters from output at end of loop.  Useful for removing commans and <br />'s.
		show_empty="n"			- Show categories that have no articles assigned to them?
		style="nested"			- Automatically nests categories for you.
	--- CONDITIONAL VARIABLES:
		{if depth == '0'}
		{if children}
		{if first_child}
		{if last_child}
	--- SINGLE VARIABLES:
		{path:view_category}	- Link to view category page
		{category_name}			- Category name, duh...
		{depth}					- How many nested layers down the category is
		{count}						- "count" of the current category being displayed
		{switch="one|two|three"}	- Rotate between any number of values, separated by a pipe symbol
		

*/



/* ----------------------------------------
/*  Template - Special Page: Title List
/* ----------------------------------------*/

function wiki_special_titles()
{
	ob_start();

	?>
	
<div class='defaultLeft'>
Choose Namespace:  <select onchange="location.href=this.value">
<option value="{path:wiki_home}{special_namespace}:Titles/">{main_namespace}</option>
{wiki:custom_namespaces_list}
<option value="{path:wiki_home}{special_namespace}:Titles/{namespace_short_name}/" {namespace_selected}>{namespace_label}</option>
{/wiki:custom_namespaces_list}
</select>
</div>

{wiki:title_list columns="3"}

{if no_results}
<div class="marginpad"><strong>Sorry, there seem to be no articles for this namespace.</strong></div>
{/if}

<table style="width:98%;">

{articles}
{row_start}<tr>{/row_start}

{row_column}
<td style="width:33%">
<a href="{path:view_article}">{title}</a><br />
</td>
{/row_column}

{row_blank}<td>&nbsp;</td>{/row_blank}

{row_end}</tr>{/row_end}

{/articles}

</table>

{/wiki:title_list}

	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_titles():

Along with the variables from wiki_page(), this template also has:

TAG VARIABLES:
{wiki:title_list}	 - Used for displaying list of titles
	--- CONDITIONALS
		{if no_results}	- If there are no articles to show
	--- PARAMETERS
		columns=""	 - How many columns to display
	-- VARIABLE PAIRS
		{articles}	 - Encloses the repeating portion of the tag
		{row_start}	 - What content to use at start of a row
		{row_blank}	 - What content to use if blank column
		{row_end}	 - What content to use at end of a row
		{row_column} - Used for each column in a row
		--- SINGLE VARIABLES
			{title}						- Title of article
			{last_updated format=""}	- When Article was last updated
			{author}					- Screen Name of revision author
			{email}						- Email for Revision Author
			{url}						- URL for Revision Author
			{revision_notes}			- Notes about revision, if any
			{path:view_article}			- Link to View Article
			{content}					- Content of revision
			{article}					- Fully rendered article
			{count}						- "count" of the current article being displayed
			{switch="one|two|three"}	- Rotate between any number of values, separated by a pipe symbol

*/


/* ----------------------------------------
/*  Template - Special Page: Uncategorized Pages
/* ----------------------------------------*/

function wiki_special_uncategorized_pages()
{
	ob_start();

	?>
	
<div class='defaultLeft'>
Choose Namespace:  <select onchange="location.href=this.value">
<option value="{path:wiki_home}{special_namespace}:Uncategorized/">{main_namespace}</option>
{wiki:custom_namespaces_list}
<option value="{path:wiki_home}{special_namespace}:Uncategorized/{namespace_short_name}/" {namespace_selected}>{namespace_label}</option>
{/wiki:custom_namespaces_list}
</select>
</div>

{wiki:title_list columns="3"}

{if no_results}
<div class="marginpad"><strong>Sorry, there seem to be no uncategorized articles for this namespace.</strong></div>
{/if}

<table style="width:98%;">

{articles}
{row_start}<tr>{/row_start}

{row_column}
<td style="width:33%">
<a href="{path:view_article}">{title}</a><br />
</td>
{/row_column}

{row_blank}<td>&nbsp;</td>{/row_blank}

{row_end}</tr>{/row_end}

{/articles}

</table>

{/wiki:title_list}

	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_uncategorized_pages():

Along with the variables from wiki_page(), this template also has:

TAG VARIABLES:
{wiki:title_list}	 - Used for displaying list of titles
	--- CONDITIONALS
		{if no_results}	- If there are no articles to show
	--- PARAMETERS
		columns=""	 - How many columns to display
	-- VARIABLE PAIRS
		{articles}	 - Encloses the repeating portion of the tag
		{row_start}	 - What content to use at start of a row
		{row_blank}	 - What content to use if blank column
		{row_end}	 - What content to use at end of a row
		{row_column} - Used for each column in a row
		--- SINGLE VARIABLES
			{title}						- Title of article
			{last_updated format=""}	- When Article was last updated
			{author}					- Screen Name of revision author
			{email}						- Email for Revision Author
			{url}						- URL for Revision Author
			{revision_notes}			- Notes about revision, if any
			{path:view_article}			- Link to View Article
			{content}					- Content of revision
			{article}					- Fully rendered article
			{count}						- "count" of the current article being displayed
			{switch="one|two|three"}	- Rotate between any number of values, separated by a pipe symbol

*/

/* ----------------------------------------
/*  Template - Special Page: Search Results
/* ----------------------------------------*/

function wiki_special_search_results()
{
	ob_start();

	?>

<div class="marginpad">
{if no_results}
<strong>No Results for Your Search:</strong>  {keywords}
{/if}

{if results}
<strong>Keywords:</strong> {keywords}
{/if}
</div>

<div class="marginpad">
{wiki:search_results limit="10"}

<a href="{path:view_article}">{title}</a> - Last Updated: {revision_date format="%j %M %Y"} by <a href="{path:author_profile}">{author}</a> {if notes}({notes}){/if}<br />
({excerpt})<br /><br />

{paginate}
<br /><div>Page {current_page} of {total_pages} pages {pagination_links}</div>
{/paginate}

{/wiki:search_results}
</div>

	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_search_results():

Along with the variables from wiki_page(), this template also has:

CONDITIONAL VARIABLES:
{if no_results}		- If no results from search
{if results}		- If there were results from search

SINGLE VARIABLES:
{keywords}			- Keywords used in search

TAG VARIABLES:
{wiki:search_results}
	--- PARAMETERS
		limit=""	- Limit how many files to display
		paginate=""	- Paginate on "top", "bottom", or "both"
		switch=""	- Alternate styles, switch="tableCellOne|tableCellTwo"
	--- SINGLE VARIABLES:
		{gmt_revision_date format=""}	- The GMT time for when the article was last revised
		{revision_date format=""}		- The localized time for when the article was last revised
		{title}							- Article Title
		{revision_id}					- Revision ID 
		{page_id}						- Page ID (used for identifying purposes in RSS/Atom Feeds)
		{author}						- Screen Name of revision author
		{email}							- Email for Revision Author
		{url}							- URL for Revision Author
		{revision_notes}				- Notes about revision, if any
		{path:view_article}				- Link to View Article
		{content}						- Content of revision
		{article}						- Fully rendered article
		{count}							- "count" of the current result being displayed
		{absolute_count}				- Running count, takes pagination into consideration
		{switch="one|two|three"}		- Rotate between any number of values, separated by a pipe symbol

PAGINATION: http://expressionengine.com/docs/modules/weblog/pagination_page.html

*/



/* ----------------------------------------
/*  Template - Special Page: Files
/* ----------------------------------------*/

function wiki_special_files()
{
	ob_start();

	?>

<div class="marginpad">
{if no_files}
<strong>No Files Have Been Uploaded</strong>
{/if}

{if files}

<div class="marginpad">

{wiki:files limit="10" paginate="bottom"}
<a href="{path:view_file}">{file_name}</a> - Uploaded: {upload_date format="%j %M %Y"} by <a href="{path:author_profile}">{author}</a><br />

{paginate}
<br /><div>Page {current_page} of {total_pages} pages {pagination_links}</div>
{/paginate}

{/wiki:files}

</div>

{/if}
</div>

	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_files():

Along with the variables from wiki_page(), this template also has:

TAG VARIABLES
{wiki:files}
	--- PARAMETERS
		limit=""	- Limit how many files to display
		paginate=""	- Paginate on "top", "bottom", or "both"
	--- SINGLE VARIABLES:
		{file_name}					- File name for file
		{file_type}					- MIME Type for file
		{summary}					- Summary given when file uploaded
		{path:view_file}			- URL to view file's page
		{upload_date format=""}		- Date file was uploaded
		{author}					- Screen name of person who uploaded file
		{email}						- Email of Author
		{url}						- URL for Author
		{path:author_profile}		- Path to Author's Profile
		{count}						- "count" of the current file being displayed
		{absolute_count}			- Running count, takes pagination into consideration
		{switch="one|two|three"}	- Rotate between any number of values, separated by a pipe symbol
	--- CONDITIONAL VARIABLES:
		{if is_image}			- Is file an image?
		
PAGINATION: http://expressionengine.com/docs/modules/weblog/pagination_page.html

*/




/* ----------------------------------------
/*  Template - Special Page: Upload form
/* ----------------------------------------*/

function wiki_special_upload_form()
{
	ob_start();

	?>

{if uploads}

<script type="text/javascript">

// ---------------------------
//  Choose File, Update Filename
// ---------------------------

function update_destination(obj)
{
	if (document.getElementById('new_filename'))
	{
		var name   = obj.value;
		var path_x = name.lastIndexOf('\/');
		var path_y = name.lastIndexOf('\\');
		
		if (path_x != -1)
		{
			name = name.substring(path_x+1);
		}
		else if (path_y != -1)
		{
			name = name.substring(path_y+1);
		}
	
		document.getElementById('new_filename').value = name.replace(/ /g, '_');
	}
}
</script>


{if can_edit}
{form_declaration:wiki:uploads}

<div class="codeblock"><input type="file" name="userfile" size="20" onchange='update_destination(this)' /></div>

{if file_types == 'images'}
<div class="codeblock"><div class="defaultBold">Only Image Uploads Are Allowed</div></div>
{if:else}
<div class="codeblock"><div class="defaultBold">Allowed File Types:</div> {file_types}</div>
{/if}

<div class="codeblock">
<span class="defaultBold">New Filename: </span>
&nbsp;&nbsp;<input class="input" name="new_filename" id="new_filename" type="text" size="50" maxlength="80" />
</div>

<div class="codeblock">
<div class="defaultBold">Summary: </div>
<textarea style='width:98%;' name='summary' id='summary' cols='90' rows='10' class='textarea'></textarea>
</div>

<input name="submit" class="submit" type="submit" value="Submit" />
</form>
{/if}

{if cannot_edit}
<p><strong>You are not allowed to upload images.</strong></p>
{/if}

{if:else}

<p><strong>File Uploading is not allowed for this wiki.</strong></p>

{/if}


	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_upload_form():

Along with the variables from wiki_page(), this template also has:

CONDITIONAL VARIABLES:
{if file_types}		- Two values: 'images' or a list of available file types

FORM VARIABLES:
{form_declaration:wiki:uploads} - Creates the opening <form> tag and hidden fields needed for upload

*/



/* ----------------------------------------
/*  Template - File
/* ----------------------------------------*/

function wiki_file()
{
	ob_start();
	?>
	
{if file_exists}

{if is_image}
<p><img src="{file_url}" height="{image_height}" width="{image_width}" /></p>
{if:else}
<p><a href="{file_url}">Download File</a></p>
{/if}

<div class="lighttext">{file_name} - MIME Type: {file_type} {if can_admin}[<a href="{delete_url}">Delete?</a>]{/if}</div>

<div class="codeblock">
{if summary}
{summary}
{if:else}
- No Summary -
{/if}
</div>

<div class="codeblock">
<span class="defaultBold">Quick Links:</span>&nbsp;&nbsp;
<input type="text" class="input" size="25" value="[[{file_namespace}:{file_name}]]" maxlength="100" />
{if is_image}
&nbsp;<input type="text" class="input" size="25" value="[[{image_namespace}:{file_name}]]" maxlength="100" />
{/if}
</div>


{if:else}
<p><strong>No file by this name exists.</strong></p>
{/if}

	<?php
	
	$buffer = ob_get_contents();
	ob_end_clean();
	
	return $buffer;
}
/* END */

/** Variables List for wiki_special_file():

Along with the variables from wiki_page(), this template also has:

SINGLE VARIABLES:
{file_name}				- File name for file
{file_type}				- MIME Type for file
{summary}				- Summary given when file uploaded
{path:view_file}		- URL to view file's page
{upload_date format=""}	- Date file was uploaded
{author}				- Screen name of person who uploaded file
{email}					- Email of Author
{url}					- URL for Author
{path:author_profile}	- Path to Author's Profile

CONDITIONAL VARIABLES:
{if is_image}			- Is file an image?

*/


/* ----------------------------------------
/*  Template - Email - Moderation Subject
/* ----------------------------------------*/

function wiki_email_moderation_subject()
{
	return <<<EOT
Moderated Wiki Revision: {title}
EOT;
}
/* END */

/* ----------------------------------------
/*  Template - Email - Moderation Message
/* ----------------------------------------*/

function wiki_email_moderation_message()
{
	return <<<EOT
A new revision has been posted for title: {title}

URL to View Revision:  {path:view_revision}
URL to Open Revision:  {path:open_revision}
View Topic: {path:view_article}

Revision Content:

{content}

EOT;
}
/* END */

/** Variables List for wiki_email_moderation_XXX():

{revision_date format=""}		- The localized time for when the article was last revised
{title}							- Article Title
{revision_id}					- Revision ID 
{author}						- Screen Name of revision author
{email}							- Email for Revision Author
{revision_notes}				- Notes about revision, if any
{path:view_article}				- Link to View Article
{path:view_revision}			- Link to View Revision
{path:open_revision}			- Link to Open Revision
{path:close_revision}			- Link to Close Revision
{content}						- Content of revision
{article}						- Fully rendered article

*/





?>