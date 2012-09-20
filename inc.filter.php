<?php

/*
Actions as groups
login, forgot

DB tables as groups
companies*, locations*, users, tenders, ndas

*/
$config = array(
	//-- Actions --//
	'login' => array(
		array(
	    	'field'   => 'email', 
	        'label'   => 'Email', 
	        'rules'   => 'required'
	    ),
	    array(
	        'field'   => 'password', 
	        'label'   => 'Password', 
	        'rules'   => 'required'
	    ),
    ),
    'email' => array(
		array(
	    	'field'   => 'email', 
	        'label'   => 'Email', 
	        'rules'   => 'trim|required|valid_email|valid_email_dns'
	    ),
    ),
    'password' => array(
		array(
	    	'field'   => 'password', 
	        'label'   => 'Password', 
	        'rules'   => 'required|min_length[8]'
	    ),
    ),
    'signature' => array(
		array(
	    	'field'   => 'signature', 
	        'label'   => 'Signature', 
	        'rules'   => 'required'
	    ),
    ),
    'approve_company' => array(
		array(
	    	'field'   => 'tender_ID', 
	        'label'   => 'Tender ID', 
	        'rules'   => 'required|is_natural_no_zero'
	    ),
	    array(
	    	'field'   => 'company_ID', 
	        'label'   => 'Company ID', 
	        'rules'   => 'required|is_natural_no_zero'
	    ),
	    array(
	    	'field'   => 'approve', 
	        'label'   => 'Approve', 
	        'rules'   => 'required|cast_boolean|boolean'
	    ),
    ),
    'keyword' => array(
		array(
	    	'field'   => 'keyword', 
	        'label'   => 'Keyword', 
	        'rules'   => 'trim'
	    ),
    ),
    
    //-- DB Tables --//
    'companies' => array(
		array(
	        'field'   => 'company_ID', 
	        'label'   => 'Company ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	    	'field'   => 'username', 
	        'label'   => 'Username', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'company_name', 
	        'label'   => 'Company Name', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'company_url', 
	        'label'   => 'URL', 
	        'rules'   => 'prep_url|valid_url'
	    ),
	    array(
	        'field'   => 'company_phone', 
	        'label'   => 'Company Phone', 
	        'rules'   => 'valid_phone'
	    ),
	    array(
	        'field'   => 'company_fax', 
	        'label'   => 'Company Fax', 
	        'rules'   => 'valid_phone'
	    ),
	    array(
	        'field'   => 'company_details', 
	        'label'   => 'Company Details', 
	        'rules'   => 'strip_tags[b,strong,i,strike,u,ul,li]'
	    ),
	   array(
	        'field'   => 'categories', 
	        'label'   => 'Categories', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'tags', 
	        'label'   => 'Tags', 
	        'rules'   => ''
	    ),
	    array(
	    	'field'   => 'user_ID', 
	        'label'   => 'User ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	     array(
	        'field'   => 'location_ID', 
	        'label'   => 'Location', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'company_type', 
	        'label'   => 'Company Type', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    
	    array(
	        'field'   => 'timestamp_create', 
	        'label'   => 'Create Timestamp', 
	        'rules'   => 'integer'
	    ),
	    array(
	        'field'   => 'timestamp_update', 
	        'label'   => 'Update Timestamp',  
	        'rules'   => 'integer'
	    ),
    ),
    'locations' => array(
		 array(
	        'field'   => 'location_ID', 
	        'label'   => 'Location', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'company_ID', 
	        'label'   => 'Company ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	    	'field'   => 'location_name', 
	        'label'   => 'Location Name', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'address_1', 
	        'label'   => 'Address', 
	        'rules'   => 'trim'
	    ),
	    array(
	        'field'   => 'address_2', 
	        'label'   => 'Address', 
	        'rules'   => 'trim'
	    ),
	    array(
	        'field'   => 'city', 
	        'label'   => 'City', 
	        'rules'   => 'trim'
	    ),
	    array(
	        'field'   => 'region_code', 
	        'label'   => 'Region Code', 
	        'rules'   => 'exact_length[2]'
	    ),
	    array(
	        'field'   => 'country_code', 
	        'label'   => 'Country Code', 
	        'rules'   => 'exact_length[2]'
	    ),
	    array(
	        'field'   => 'mail_code', 
	        'label'   => 'Mail Code', 
	        'rules'   => 'valid_mail_code'
	    ),
	    array(
	        'field'   => 'logitude', 
	        'label'   => 'Longitude', 
	        'rules'   => 'number'
	    ),
	    array(
	        'field'   => 'latitude', 
	        'label'   => 'Latitude', 
	        'rules'   => 'number'
	    ),
	    array(
	        'field'   => 'phone', 
	        'label'   => 'Location Phone', 
	        'rules'   => 'valid_phone'
	    ),
	    array(
	        'field'   => 'fax', 
	        'label'   => 'Location Fax', 
	        'rules'   => 'valid_phone'
	    ),
    ),
    'users' => array(
		array(
	    	'field'   => 'user_ID', 
	        'label'   => 'User ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'company_ID', 
	        'label'   => 'Company ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'user_level', 
	        'label'   => 'Level', 
	        'rules'   => 'is_natural'
	    ),
	    array(
	        'field'   => 'user_name', 
	        'label'   => 'Name', 
	        'rules'   => 'trim'
	    ),
	    array(
	        'field'   => 'user_email', 
	        'label'   => 'User Email', 
	        'rules'   => 'valid_email|valid_email_dns',//|is_unique[users.user_email]' use when creating
	    ),
	    array(
	        'field'   => 'user_cell', 
	        'label'   => 'Cell', 
	        'rules'   => 'valid_phone'
	    ),
	    array(
	        'field'   => 'user_phone', 
	        'label'   => 'Phone', 
	        'rules'   => 'valid_phone'
	    ),
	    array(
	        'field'   => 'user_fax', 
	        'label'   => 'Fax', 
	        'rules'   => 'valid_phone'
	    ),
	    array(
	        'field'   => 'user_function', 
	        'label'   => 'Function', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'password', 
	        'label'   => 'Password', 
	        'rules'   => 'min_length[8]'
	    ),
	    array(
	        'field'   => 'password_timestamp', 
	        'label'   => 'Passowrd Timestamp', 
	        'rules'   => 'integer'
	    ),
	    
	    array(
	        'field'   => 'timestamp_create', 
	        'label'   => 'Create Timestamp', 
	        'rules'   => 'integer'
	    ),
	    array(
	        'field'   => 'timestamp_update', 
	        'label'   => 'Update Timestamp',  
	        'rules'   => 'integer'
	    ),
	    
	    // not in table, but needed for confirms
	    array(
	        'field'   => 'user_email_confirm', 
	        'label'   => 'User Email Confirm', 
	        'rules'   => 'matches[user_email]',
	    ),
	    array(
	        'field'   => 'password_confirm', 
	        'label'   => 'Password Confirm', 
	        'rules'   => 'matches[password]',
	    ),
    ),
    'tenders' => array(
		array(
	    	'field'   => 'tender_ID', 
	        'label'   => 'Tender ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'company_ID', 
	        'label'   => 'Company ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'title', 
	        'label'   => 'Title', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'ref_ID', 
	        'label'   => 'Reference ID', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'tender_type', 
	        'label'   => 'Tender Type', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'details', 
	        'label'   => 'Details', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'categories', 
	        'label'   => 'Categories', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'tags', 
	        'label'   => 'Tags', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'users', 
	        'label'   => 'Users', 
	        'rules'   => ''
	    ),
	    array(
	        'field'   => 'location_ID', 
	        'label'   => 'Location', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'post_timestamp', 
	        'label'   => 'Post Date &amp; Time', 
	        'rules'   => 'integer'
	    ),
	    array(
	        'field'   => 'question_timestamp', 
	        'label'   => 'Question Date &amp; Time', 
	        'rules'   => 'integer|greater_than[post_timestamp]'
	    ),
	    array(
	        'field'   => 'close_timestamp', 
	        'label'   => 'Close Date &amp; Time', 
	        'rules'   => 'integer|greater_than[question_timestamp]'
	    ),
	    array(
	        'field'   => 'url', 
	        'label'   => 'URL', 
	        'rules'   => 'prep_url|valid_url'
	    ),
	    array(
	        'field'   => 'nda_ID', 
	        'label'   => 'NDA ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'approval_required', 
	        'label'   => 'Approval Required', 
	        'rules'   => 'cast_boolean|boolean'
	    ),
	    array(
	        'field'   => 'timestamp_create', 
	        'label'   => 'Create Timestamp', 
	        'rules'   => 'integer'
	    ),
	    array(
	        'field'   => 'timestamp_update', 
	        'label'   => 'Update Timestamp',  
	        'rules'   => 'integer'
	    ),
    ),
    'ndas' => array(
		array(
	    	'field'   => 'nda_ID', 
	        'label'   => 'NDA ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'company_ID', 
	        'label'   => 'Company ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'nda_title', 
	        'label'   => 'Title', 
	        'rules'   => 'trim'
	    ),
	    array(
	        'field'   => 'nda_details', 
	        'label'   => 'Details', 
	        'rules'   => 'trim'
	    ),
	    array(
	        'field'   => 'timestamp_create', 
	        'label'   => 'Create Timestamp', 
	        'rules'   => 'integer'
	    ),
	    array(
	        'field'   => 'timestamp_update', 
	        'label'   => 'Update Timestamp',  
	        'rules'   => 'integer'
	    ),
    ),
    'bids' => array(
		array(
	    	'field'   => 'bid_ID', 
	        'label'   => 'Bid ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'tender_ID', 
	        'label'   => 'Tender ID', 
	        'rules'   => 'is_natural_no_zero'
	    ),
	    array(
	        'field'   => 'bid_value', 
	        'label'   => 'Value', 
	        'rules'   => 'trim|decimal'
	    ),
	    array(
	        'field'   => 'bid_details', 
	        'label'   => 'Details', 
	        'rules'   => 'trim'
	    ),
	    array(
	        'field'   => 'bid_timestamp', 
	        'label'   => 'Bid Timestamp', 
	        'rules'   => 'integer'
	    ),
	    array(
	        'field'   => 'bid_awarded', 
	        'label'   => 'Awarded', 
	        'rules'   => 'cast_boolean|boolean'
	    ),
    ),
);

?>