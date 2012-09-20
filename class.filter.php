<?php

/*
  	http://codeigniter.com/user_guide/libraries/form_validation.html
  	
	Rule			Param	Details
	// validation - codeigniter //
	required		No	Returns FALSE if the form element is empty.	 
	matches			Yes	Returns FALSE if the form element does not match the one in the parameter.	matches[form_item]
	is_unique		Yes	Returns FALSE if the form element is not unique to the table and field name in the parameter.	is_unique[table.field]
	min_length		Yes	Returns FALSE if the form element is shorter then the parameter value.	min_length[6]
	max_length		Yes	Returns FALSE if the form element is longer then the parameter value.	max_length[12]
	exact_length	Yes	Returns FALSE if the form element is not exactly the parameter value.	exact_length[8]
	greater_than	Yes	Returns FALSE if the form element is less than the parameter value or not numeric.	greater_than[8]
	less_than		Yes	Returns FALSE if the form element is greater than the parameter value or not numeric.	less_than[8]
	alpha			No	Returns FALSE if the form element contains anything other than alphabetical characters.	 
	alpha_numeric	No	Returns FALSE if the form element contains anything other than alpha-numeric characters.	 
	alpha_dash		No	Returns FALSE if the form element contains anything other than alpha-numeric characters, underscores or dashes.	 
	numeric			No	Returns FALSE if the form element contains anything other than numeric characters.	 
	integer			No	Returns FALSE if the form element contains anything other than an integer.	 
	decimal			Yes	Returns FALSE if the form element is not exactly the parameter value.	 
	boolean			No	Returns FALSE if the form element contains anything other than a boolean.
	is_natural		No	Returns FALSE if the form element contains anything other than a natural number: 0, 1, 2, 3, etc.	 
	is_natural_no_zero	No	Returns FALSE if the form element contains anything other than a natural number, but not zero: 1, 2, 3, etc.	 
	valid_email		No	Returns FALSE if the form element does not contain a valid email address.	 
	valid_emails	No	Returns FALSE if any value provided in a comma separated list is not a valid email.	 
	valid_ip		No	Returns FALSE if the supplied IP is not valid. Accepts an optional parameter of "IPv4" or "IPv6" to specify an IP format.	 
	valid_base64	No	Returns FALSE if the supplied string contains anything other than valid Base64 characters.
	// validation - custom //
	valid_email_dns	No	Returns FALSE if the form element does not contain a valid email address or has no MX record.
	valid_url 		No	Returns FALSE if the form element does not contain a valid URL.
	*valid_mail_code	Yes	Returns FALSE if the form element does not contain a valid mail code for a given country.	valid_mail_code[CA]  country code
	*valid_phone 	Yes	Returns FALSE if the form element does not contain a valid phone/fax number.	valid_phone[+] // + = international
	
	// sanitize - php functions //
	trim			No		
	strip_tags		Yes		strip_tags[whitelist,a,b,i]
	// sanitize - custom //
	cast_boolean	No	converts popular boolean strings into boolean (true/false,1/0,yes/no,on/off)
	prep_url		No	Adds "http://" to URLs if missing.
	encode_php_tags	No	Converts PHP tags to entities.
	*sanitize_string	No	filter_var($str, FILTER_SANITIZE_STRING)
*/

/*
USE CASE

require_once('fct/class.filter.php');



// no groups, single var
$this->filter->set_request_data('keyword', $keyword);
$this->filter->set_rules('keyword', 'trim');
if(!$this->filter->run()) {
	$return["errors"] = $this->filter->get_errors();
	$return["alerts"] = $this->filter->get_alerts('error');
	return $return;
}
$keyword = $this->filter->get_request_data('keyword');



// with groups and array of inputs
$this->filter->set_request_data($request_data);
$this->filter->set_group_rules('group_a,group_b');
$this->filter->set_key_rules(array('key_a', 'key_2'), 'required');
$this->filter->set_all_rules('trim|sanitize_string', true);	// apply to all

if(!$this->filter->run()) {
	$return["errors"] = $this->filter->get_errors();
	return $return;
}
$request_data = $this->filter->get_request_data();



*/

require_once 'inc.filter.php';	// config file
//require_once 'test.filter.php';

class Filter {
	var $form = array();
	
	// rule messages
	protected $_request_data		= array();
	protected $_field_data			= array();	// params about a field
	protected $_config_rules		= array();
	protected $_defaut_messages 	= array(
		// CI_Form_validation rules
		'required' 				=> 'is empty',
		'matches' 				=> 'does not match',
		'is_unique' 			=> 'is already taken',
		'min_length' 			=> 'is too short',
		'max_length' 			=> 'is too long',
		'exact_length' 			=> 'is not the right length',
		'greater_than' 			=> 'is too small',
		'less_than' 			=> 'is too large',
		'alpha' 				=> 'contains non alphabetical characters',
		'alpha_numeric' 		=> 'contains non alpha-numeric characters',
		'alpha_dash' 			=> 'contains non alpha-numeric characters, underscores or dashes',
		'numeric' 				=> 'contains non numeric characters',
		'boolean' 				=> 'is not a boolean',
		'integer' 				=> 'is not an integer',
		'decimal' 				=> 'is not a decimal number',
		'is_natural' 			=> 'is not zero or a positive integer',				// array values
		'is_natural_no_zero' 	=> 'is not a positive integer',						// DB ID values
		'valid_email' 			=> 'is not a valid email',
		'valid_emails' 			=> 'are not a valid emails',
		'valid_ip' 				=> 'is not a valid IP',
		'valid_base64' 			=> 'is not in Base64',
		// custom
		'valid_email_dns'		=> 'is not a valid email domain',
		'valid_url' 			=> 'is not a valid url',
		'valid_mail_code' 		=> 'is not a valid mail code',
		'valid_phone' 			=> 'is not a valid phone number',
	);
	protected $_error_array			= array();
	protected $_error_messages		= array();
	protected $_error_prefix		= '<p>';
	protected $_error_suffix		= '</p>';
	protected $_safe_form_data		= FALSE;
	
	function __construct($rules = array()){
		global $database;
        $this->db = $database;
        
        $this->_config_rules = $rules;
        
        // copy sent params
        if 		(isset($_GET) && count($_GET)) 		$this->_request_data = $_GET;
        else if	(isset($_POST) && count($_POST)) 	$this->_request_data = $_POST;
        else if (isset($_PUT) && count($_PUT)) 		$this->_request_data = $_PUT;
        
        // set default error messages
        foreach ($this->_defaut_messages as $key => $value) {
        	$this->set_message($key, $value);
        }
        
    }
	
	function __destruct() {
		  
  	}
  	
  	function get_request_data($key = NULL) {
  		if ($key != NULL) {
  			return $this->_request_data[$key];
  		} else {
	  		return $this->_request_data;
  		}
  	}
  	
  	function set_request_data($request_data, $value = NULL) {
  		if (is_array($request_data)) {
  			$this->_request_data = $request_data;
  		} else {
	  		$key = $request_data;
	  		$this->_request_data[$key] = $value;
  		}
  	}
  	
  	function get_error_array() {
  		return $this->_error_array;
  	}
  	
  	function get_errors($class = 'error') {
  		$alerts = array();
  		foreach ($this->_field_data as $key => $value) {
	  		//if ($value['error']) $errors[$key] = $value['error'];
	  		if ($value['error']) $alerts[$key] = array('class' => $class, 'label' => $value['label'], 'message' => $value['error']);
  		}
  		
	  	return $alerts;
  	}
  	
  	/**
	 * Set Rules for an array of keys
	 *
	 * This function takes an array of field names and validation
	 * rules as input, validates the info, and stores it
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @param	bool
	 * @return	void
	 */
  	function set_key_rules($keys, $rules, $pos = false) {
  		if (is_array($keys)) {
  			foreach ($keys as $key) {
  				$spacer = ($this->_field_data[$key]['rules'] == '') ? '' : '|';
  				$this->_field_data[$key]['rules'] = $pos ? $this->_field_data[$key]['rules'].$spacer.$rules : $rules.$spacer.$this->_field_data[$key]['rules'];
	  		}
  		}
  	}
  	
  	function set_all_rules($rules, $pos = false) {
		foreach ($this->_field_data as $key => $value) {
			$spacer = ($value['rules'] == '') ? '' : '|';
			$this->_field_data[$key]['rules'] = $pos ? $value['rules'].$spacer.$rules : $rules.$spacer.$value['rules'];
  		}
  	}
  	
  	/**
	 * Set Rules from config groups
	 *
	 * @access	public
	 * @param	mixed
	 * @return	void
	 */
  	function set_group_rules($groups) {
  		// Is there a validation rule for the particular group being accessed?
		$groups = ($groups == '') ? '' : explode(",", $groups);
		
		if (is_array($groups))
		{
			foreach($groups as $group) {
				if ($groups != '' AND isset($this->_config_rules[$group]))
				{
					$this->set_rules($this->_config_rules[$group]);
				}
			}
		}
	}
  	
  	
  	// --------------------------------------------------------------------

	/**
	 * Set Rules
	 *
	 * This function takes an array of field names and validation
	 * rules as input, validates the info, and stores it
	 *
	 * @access	public
	 * @param	mixed
	 * @param	string
	 * @return	void
	 */
	public function set_rules($field, $label = '', $rules = '')
	{
		// No reason to set rules if we have no POST data
		if (count($this->_request_data) == 0)
		{
			return $this;
		}

		// If an array was passed via the first parameter instead of indidual string
		// values we cycle through it and recursively call this function.
		if (is_array($field))
		{
			foreach ($field as $row)
			{
				// Houston, we have a problem...
				if ( ! isset($row['field']) OR ! isset($row['rules']))
				{
					continue;
				}

				// If the field label wasn't passed we use the field name
				$label = ( ! isset($row['label'])) ? $row['field'] : $row['label'];

				// Here we go!
				$this->set_rules($row['field'], $label, $row['rules']);
			}
			return $this;
		}

		// No fields? Nothing to do...
		if ( ! is_string($field) OR  ! is_string($rules) OR $field == '')
		{
			return $this;
		}

		// If the field label wasn't passed we use the field name
		$label = ($label == '') ? $field : $label;

		// Is the field name an array?  We test for the existence of a bracket "[" in
		// the field name to determine this.  If it is an array, we break it apart
		// into its components so that we can fetch the corresponding POST data later
		if (strpos($field, '[') !== FALSE AND preg_match_all('/\[(.*?)\]/', $field, $matches))
		{
			// Note: Due to a bug in current() that affects some versions
			// of PHP we can not pass function call directly into it
			$x = explode('[', $field);
			$indexes[] = current($x);

			for ($i = 0; $i < count($matches['0']); $i++)
			{
				if ($matches['1'][$i] != '')
				{
					$indexes[] = $matches['1'][$i];
				}
			}

			$is_array = TRUE;
		}
		else
		{
			$indexes	= array();
			$is_array	= FALSE;
		}

		// Build our master array
		$this->_field_data[$field] = array(
			'field'				=> $field,
			'label'				=> $label,
			'rules'				=> $rules,
			'is_array'			=> $is_array,
			'keys'				=> $indexes,
			'postdata'			=> NULL,
			'error'				=> ''
		);

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Error Message
	 *
	 * Lets users set their own error messages on the fly.  Note:  The key
	 * name has to match the  function name that it corresponds to.
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function set_message($lang, $val = '')
	{
		if ( ! is_array($lang))
		{
			$lang = array($lang => $val);
		}

		$this->_error_messages = array_merge($this->_error_messages, $lang);

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set The Error Delimiter
	 *
	 * Permits a prefix/suffix to be added to each error message
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	public function set_error_delimiters($prefix = '<p>', $suffix = '</p>')
	{
		$this->_error_prefix = $prefix;
		$this->_error_suffix = $suffix;

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Error Message
	 *
	 * Gets the error message associated with a particular field
	 *
	 * @access	public
	 * @param	string	the field name
	 * @return	void
	 */
	public function error($field = '', $prefix = '', $suffix = '')
	{
		if ( ! isset($this->_field_data[$field]['error']) OR $this->_field_data[$field]['error'] == '')
		{
			return '';
		}

		if ($prefix == '')
		{
			$prefix = $this->_error_prefix;
		}

		if ($suffix == '')
		{
			$suffix = $this->_error_suffix;
		}

		return $prefix.$this->_field_data[$field]['error'].$suffix;
	}

	// --------------------------------------------------------------------

	/**
	 * Error String
	 *
	 * Returns the error messages as a string, wrapped in the error delimiters
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	str
	 */
	public function error_string($prefix = '', $suffix = '')
	{
		// No errrors, validation passes!
		if (count($this->_error_array) === 0)
		{
			return '';
		}

		if ($prefix == '')
		{
			$prefix = $this->_error_prefix;
		}

		if ($suffix == '')
		{
			$suffix = $this->_error_suffix;
		}

		// Generate the error string
		$str = '';
		foreach ($this->_error_array as $val)
		{
			if ($val != '')
			{
				$str .= $prefix.$val.$suffix."\n";
			}
		}

		return $str;
	}
  	
  	// --------------------------------------------------------------------

	/**
	 * Run the Validator
	 *
	 * This function does all the work.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function run($groups = '')
	{
		// Do we even have any data to process?  Mm?
		if (count($this->_request_data) == 0)
		{
			return FALSE;
		}

		// Does the _field_data array containing the validation rules exist?
		// If not, we look to see if they were assigned via a config file
		if (count($this->_field_data) == 0)
		{
			// No validation rules?  We're done...
			if (count($this->_config_rules) == 0)
			{
				return FALSE;
			}

			// Is there a validation rule for the particular group being accessed?
			$groups = ($groups == '') ? '' : explode(",", $groups);
			
			if (is_array($groups))
			{
				foreach($groups as $group) {
					if ($groups != '' AND isset($this->_config_rules[$group]))
					{
						$this->set_rules($this->_config_rules[$group]);
					}
				}
			}
			else
			{
				$this->set_rules($this->_config_rules);
			}

			// We're we able to set the rules correctly?
			if (count($this->_field_data) == 0)
			{
				//log_message('debug', "Unable to find validation rules");
				return FALSE;
			}
		}

		// Load the language file containing error messages
		//$this->CI->lang->load('form_validation');

		// Cycle through the rules for each field, match the
		// corresponding $_POST item and test for errors
		foreach ($this->_field_data as $field => $row)
		{
			// Fetch the data from the corresponding $_POST array and cache it in the _field_data array.
			// Depending on whether the field name is an array or a string will determine where we get it from.
			
			if ($row['is_array'] == TRUE)
			{
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($this->_request_data, $row['keys']);
			}
			else
			{
				if (isset($this->_request_data[$field]) AND $this->_request_data[$field] != "")
				{
					$this->_field_data[$field]['postdata'] = $this->_request_data[$field];
				}
			}

			$this->_execute($row, explode('|', $row['rules']), $this->_field_data[$field]['postdata']);
		}

		// Did we end up with any errors?
		$total_errors = count($this->_error_array);

		if ($total_errors > 0)
		{
			$this->_safe_form_data = TRUE;
		}

		// Now we need to re-set the POST data with the new, processed data
		$this->_reset_post_array();

		// No errors, validation passes!
		if ($total_errors == 0)
		{
			return TRUE;
		}

		// Validation fails
		return FALSE;
	}

	// --------------------------------------------------------------------

	/**
	 * Traverse a multidimensional $this->_request_data array index until the data is found
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	integer
	 * @return	mixed
	 */
	protected function _reduce_array($array, $keys, $i = 0)
	{
		if (is_array($array))
		{
			if (isset($keys[$i]))
			{
				if (isset($array[$keys[$i]]))
				{
					$array = $this->_reduce_array($array[$keys[$i]], $keys, ($i+1));
				}
				else
				{
					return NULL;
				}
			}
			else
			{
				return $array;
			}
		}

		return $array;
	}

	// --------------------------------------------------------------------

	/**
	 * Re-populate the _POST array with our finalized and processed data
	 *
	 * @access	private
	 * @return	null
	 */
	protected function _reset_post_array()
	{
		foreach ($this->_field_data as $field => $row)
		{
			if ( ! is_null($row['postdata']))
			{
				if ($row['is_array'] == FALSE)
				{
					if (isset($this->_request_data[$row['field']]))
					{
						$this->_request_data[$row['field']] = $this->prep_for_form($row['postdata']);
					}
				}
				else
				{
					// start with a reference
					$post_ref =& $this->_request_data;

					// before we assign values, make a reference to the right POST key
					if (count($row['keys']) == 1)
					{
						$post_ref =& $post_ref[current($row['keys'])];
					}
					else
					{
						foreach ($row['keys'] as $val)
						{
							$post_ref =& $post_ref[$val];
						}
					}

					if (is_array($row['postdata']))
					{
						$array = array();
						foreach ($row['postdata'] as $k => $v)
						{
							$array[$k] = $this->prep_for_form($v);
						}

						$post_ref = $array;
					}
					else
					{
						$post_ref = $this->prep_for_form($row['postdata']);
					}
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Executes the Validation routines
	 *
	 * @access	private
	 * @param	array
	 * @param	array
	 * @param	mixed
	 * @param	integer
	 * @return	mixed
	 */
	protected function _execute($row, $rules, $postdata = NULL, $cycles = 0)
	{
		// If the $this->_request_data data is an array we will run a recursive call
		if (is_array($postdata))
		{
			foreach ($postdata as $key => $val)
			{
				$this->_execute($row, $rules, $val, $cycles);
				$cycles++;
			}

			return;
		}

		// --------------------------------------------------------------------

		// If the field is blank, but NOT required, no further tests are necessary
		$callback = FALSE;
		if ( ! in_array('required', $rules) AND is_null($postdata))
		{
			// Before we bail out, does the rule contain a callback?
			if (preg_match("/(callback_\w+(\[.*?\])?)/", implode(' ', $rules), $match))
			{
				$callback = TRUE;
				$rules = (array('1' => $match[1]));
			}
			else
			{
				return;
			}
		}
		
		// --------------------------------------------------------------------

		// Isset Test. Typically this rule will only apply to checkboxes.
		if (is_null($postdata) AND $callback == FALSE)
		{
			if (in_array('isset', $rules, TRUE) OR in_array('required', $rules))
			{
				// Set the message type
				$type = (in_array('required', $rules)) ? 'required' : 'isset';

				if ( ! isset($this->_error_messages[$type]))
				{
					/*if (FALSE === ($line = $this->CI->lang->line($type)))
					{*/
						$line = 'The field was not set';
					/*}*/
				}
				else
				{
					$line = $this->_error_messages[$type];
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']));

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}
			}

			return;
		}

		// --------------------------------------------------------------------

		// Cycle through each rule and run it
		foreach ($rules As $rule)
		{
			$_in_array = FALSE;

			// We set the $postdata variable with the current data in our master array so that
			// each cycle of the loop is dealing with the processed data from the last cycle
			if ($row['is_array'] == TRUE AND is_array($this->_field_data[$row['field']]['postdata']))
			{
				// We shouldn't need this safety, but just in case there isn't an array index
				// associated with this cycle we'll bail out
				if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
				{
					continue;
				}

				$postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
				$_in_array = TRUE;
			}
			else
			{
				$postdata = $this->_field_data[$row['field']]['postdata'];
			}

			// --------------------------------------------------------------------

			// Is the rule a callback?
			$callback = FALSE;
			if (substr($rule, 0, 9) == 'callback_')
			{
				$rule = substr($rule, 9);
				$callback = TRUE;
			}

			// Strip the parameter (if exists) from the rule
			// Rules can contain a parameter: max_length[5]
			$param = FALSE;
			if (preg_match("/(.*?)\[(.*)\]/", $rule, $match))
			{
				$rule	= $match[1];
				$param	= $match[2];
			}

			// Call the function that corresponds to the rule
			if ($callback === TRUE)
			{
				
				// Run the function and grab the result
				$result = $this->$rule($postdata, $param);

				// Re-assign the result to the master data array
				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}

				// If the field isn't required and we just processed a callback we'll move on...
				if ( ! in_array('required', $rules, TRUE) AND $result !== FALSE)
				{
					continue;
				}
			}
			else
			{
				if ( ! method_exists($this, $rule))
				{
					// If our own wrapper function doesn't exist we see if a native PHP function does.
					// Users can use any native PHP function call that has one param. Now two, $value must be the first param
					if (function_exists($rule))
					{
						if ($param) $result = $rule($postdata, $param);
						else $result = $rule($postdata);

						if ($_in_array == TRUE)
						{
							$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
						}
						else
						{
							$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
						}
					}
					else
					{
						//log_message('debug', "Unable to find validation rule: ".$rule);
						echo "Unable to find validation rule: ".$rule;
					}

					continue;
				}

				$result = $this->$rule($postdata, $param);
				if ($_in_array == TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = (is_bool($result)) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = (is_bool($result)) ? $postdata : $result;
				}
				
				// spaecial case
				if ($rule == 'cast_boolean') {
					$this->_field_data[$row['field']]['postdata'] = $result;
					$result = "";
				}
				
			}

			// Did the rule test negatively?  If so, grab the error.
			if ($result === FALSE)
			{
				if ( ! isset($this->_error_messages[$rule]))
				{
					//if (FALSE === ($line = $this->CI->lang->line($rule)))
					//{
						$line = 'Unable to access an error message corresponding to your field name.';
					//}
				}
				else
				{
					$line = $this->_error_messages[$rule];
				}

				// Is the parameter we are inserting into the error message the name
				// of another field?  If so we need to grab its "field label"
				if (isset($this->_field_data[$param]) AND isset($this->_field_data[$param]['label']))
				{
					$param = $this->_translate_fieldname($this->_field_data[$param]['label']);
				}

				// Build the error message
				$message = sprintf($line, $this->_translate_fieldname($row['label']), $param);

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					$this->_error_array[$row['field']] = $message;
				}

				return;
			}
		}
	}
  	
	// --------------------------------------------------------------------

	/**
	 * Translate a field name
	 *
	 * @access	private
	 * @param	string	the field name
	 * @return	string
	 */
	protected function _translate_fieldname($fieldname)
	{
		// Do we need to translate the field name?
		// We look for the prefix lang: to determine this
		if (substr($fieldname, 0, 5) == 'lang:')
		{
			// Grab the variable
			$line = substr($fieldname, 5);

			// Were we able to translate the field name?  If not we use $line
			if (FALSE === ($fieldname = $this->CI->lang->line($line)))
			{
				return $line;
			}
		}

		return $fieldname;
	}
	
  	// --------------------------------------------------------------------

	/**
	 * Required
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function required($str)
	{
		if ( ! is_array($str))
		{
			return (trim($str) == '') ? FALSE : TRUE;
		}
		else
		{
			return ( ! empty($str));
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Performs a Regular Expression match test.
	 *
	 * @access	public
	 * @param	string
	 * @param	regex
	 * @return	bool
	 */
	public function regex_match($str, $regex)
	{
		if ( ! preg_match($regex, $str))
		{
			return FALSE;
		}

		return  TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @return	bool
	 */
	public function matches($str, $field)
	{
		if ( ! isset($this->_request_data[$field]))
		{
			return FALSE;
		}

		$field = $this->_request_data[$field];

		return ($str !== $field) ? FALSE : TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @return	bool
	 */
	public function is_unique($str, $field)
	{
		list($table, $field)=explode('.', $field);
		$query = $this->db->select($table, array($field => $str));
		
		return $query->num_rows() === 0;
    }

	// --------------------------------------------------------------------

	/**
	 * Minimum Length
	 *
	 * @access	public
	 * @param	string
	 * @param	value
	 * @return	bool
	 */
	public function min_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}

		if (function_exists('mb_strlen'))
		{
			return (mb_strlen($str) < $val) ? FALSE : TRUE;
		}

		return (strlen($str) < $val) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Max Length
	 *
	 * @access	public
	 * @param	string
	 * @param	value
	 * @return	bool
	 */
	public function max_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}

		if (function_exists('mb_strlen'))
		{
			return (mb_strlen($str) > $val) ? FALSE : TRUE;
		}

		return (strlen($str) > $val) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Exact Length
	 *
	 * @access	public
	 * @param	string
	 * @param	value
	 * @return	bool
	 */
	public function exact_length($str, $val)
	{
		if (preg_match("/[^0-9]/", $val))
		{
			return FALSE;
		}

		if (function_exists('mb_strlen'))
		{
			return (mb_strlen($str) != $val) ? FALSE : TRUE;
		}

		return (strlen($str) != $val) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Email
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_email($str)
	{
		return filter_var($str, FILTER_VALIDATE_EMAIL);
		// practical implementation of RFC 2822
		return ( ! preg_match("/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9][a-z0-9-]*[a-z0-9]/ix", $str)) ? FALSE : TRUE;
		// old php version
		return ( ! preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/ix", $str)) ? FALSE : TRUE;
		// CI version
		return ( ! preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,7}$/ix", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Email DNS
	 * 
	 * Checks teh MX record of an email domain
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_email_dns($str)
	{
		if ($this->valid_email($str)) {
			$host = substr($str, strpos($str, "@")+1);
			return checkdnsrr($host, "MX");
		} else {
			return FALSE;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Emails
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_emails($str)
	{
		if (strpos($str, ',') === FALSE)
		{
			return $this->valid_email(trim($str));
		}

		foreach (explode(',', $str) as $email)
		{
			if (trim($email) != '' && $this->valid_email(trim($email)) === FALSE)
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------
	
	/**
	 * Valid URL
	 * 
	 * Validates value as URL (according to » http://www.faqs.org/rfcs/rfc2396),
	 * optionally with required components. Note that the function will only find ASCII URLs
	 * to be valid; internationalized domain names (containing non-ASCII characters) will fail.
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_url($str)
	{
		return filter_var($str, FILTER_VALIDATE_URL);
	}

	// --------------------------------------------------------------------
	
	/**
	* Validate IP Address
	*
	* @access	public
	* @param	string
	* @param	string	"ipv4" or "ipv6" to validate a specific ip format
	* @return	bool
	*/
	public function valid_ip($ip, $which = '')
	{
		$which = strtolower($which);

		// First check if filter_var is available
		if (is_callable('filter_var'))
		{
			switch ($which) {
				case 'ipv4':
					$flag = FILTER_FLAG_IPV4;
					break;
				case 'ipv6':
					$flag = FILTER_FLAG_IPV6;
					break;
				default:
					$flag = '';
					break;
			}

			return (bool) filter_var($ip, FILTER_VALIDATE_IP, $flag);
		}

		if ($which !== 'ipv6' && $which !== 'ipv4')
		{
			if (strpos($ip, ':') !== FALSE)
			{
				$which = 'ipv6';
			}
			elseif (strpos($ip, '.') !== FALSE)
			{
				$which = 'ipv4';
			}
			else
			{
				return FALSE;
			}
		}

		$func = '_valid_'.$which;
		return $this->$func($ip);
	}

	// --------------------------------------------------------------------

	/**
	* Validate IPv4 Address
	*
	* Updated version suggested by Geert De Deckere
	*
	* @access	protected
	* @param	string
	* @return	bool
	*/
	protected function _valid_ipv4($ip)
	{
		$ip_segments = explode('.', $ip);

		// Always 4 segments needed
		if (count($ip_segments) !== 4)
		{
			return FALSE;
		}
		// IP can not start with 0
		if ($ip_segments[0][0] == '0')
		{
			return FALSE;
		}

		// Check each segment
		foreach ($ip_segments as $segment)
		{
			// IP segments must be digits and can not be
			// longer than 3 digits or greater then 255
			if ($segment == '' OR preg_match("/[^0-9]/", $segment) OR $segment > 255 OR strlen($segment) > 3)
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	* Validate IPv6 Address
	*
	* @access	protected
	* @param	string
	* @return	bool
	*/
	protected function _valid_ipv6($str)
	{
		// 8 groups, separated by :
		// 0-ffff per group
		// one set of consecutive 0 groups can be collapsed to ::

		$groups = 8;
		$collapsed = FALSE;

		$chunks = array_filter(
			preg_split('/(:{1,2})/', $str, NULL, PREG_SPLIT_DELIM_CAPTURE)
		);

		// Rule out easy nonsense
		if (current($chunks) == ':' OR end($chunks) == ':')
		{
			return FALSE;
		}

		// PHP supports IPv4-mapped IPv6 addresses, so we'll expect those as well
		if (strpos(end($chunks), '.') !== FALSE)
		{
			$ipv4 = array_pop($chunks);

			if ( ! $this->_valid_ipv4($ipv4))
			{
				return FALSE;
			}

			$groups--;
		}

		while ($seg = array_pop($chunks))
		{
			if ($seg[0] == ':')
			{
				if (--$groups == 0)
				{
					return FALSE;	// too many groups
				}

				if (strlen($seg) > 2)
				{
					return FALSE;	// long separator
				}

				if ($seg == '::')
				{
					if ($collapsed)
					{
						return FALSE;	// multiple collapsed
					}

					$collapsed = TRUE;
				}
			}
			elseif (preg_match("/[^0-9a-f]/i", $seg) OR strlen($seg) > 4)
			{
				return FALSE; // invalid segment
			}
		}

		return $collapsed OR $groups == 1;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Boolean
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function cast_boolean($str)
	{
		
		switch ($str) {
			case "true": 	return TRUE; break;
			case "false": 	return FALSE; break;
			case "1": 		return TRUE; break;
			case "0": 		return FALSE; break;
			case "yes": 	return TRUE; break;
			case "no": 		return FALSE; break;
			case "on": 		return TRUE; break;
			case "off": 	return FALSE; break;
			default: 		return $str;
		}
	}
	
	/**
	 * Boolean
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function boolean($str)
	{
		return (bool) is_bool($str);
		//return filter_var($str, FILTER_VALIDATE_BOOLEAN);
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha($str)
	{
		return ( ! preg_match("/^([a-z])+$/i", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_numeric($str)
	{
		return ( ! preg_match("/^([a-z0-9])+$/i", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_dash($str)
	{
		return ( ! preg_match("/^([-a-z0-9_-])+$/i", $str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function numeric($str)
	{
		return (bool)preg_match( '/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

	}

	// --------------------------------------------------------------------

	/**
	 * Is Numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function is_numeric($str)
	{
		return ( ! is_numeric($str)) ? FALSE : TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Integer
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function integer($str)
	{
		return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Decimal number
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function decimal($str)
	{
		return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Greather than
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function greater_than($str, $min)
	{
		if ( ! is_numeric($str))
		{
			return FALSE;
		}
		return $str > $min;
	}

	// --------------------------------------------------------------------

	/**
	 * Less than
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function less_than($str, $max)
	{
		if ( ! is_numeric($str))
		{
			return FALSE;
		}
		return $str < $max;
	}

	// --------------------------------------------------------------------

	/**
	 * Is a Natural number  (0,1,2,3, etc.)
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function is_natural($str)
	{
		return (bool) preg_match( '/^[0-9]+$/', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Is a Natural number, but not a zero  (1,2,3, etc.)
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function is_natural_no_zero($str)
	{
		if ( ! preg_match( '/^[0-9]+$/', $str))
		{
			return FALSE;
		}

		if ($str == 0)
		{
			return FALSE;
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function valid_base64($str)
	{
		return (bool) ! preg_match('/[^a-zA-Z0-9\/\+=]/', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Mail Code
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public function valid_mail_code($str, $country_code)
	{
		return TRUE;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Valid Phone Number
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public function valid_phone($str, $type)
	{
		return TRUE;
		switch ($type) {
	  		case "+":				// +1 (XXX) XXX-XXXX
	  			return $this->match("/(\d)/", $str);
	  		default:				// (XXX) XXX-XXXX
	  			return $this->match("/(\d)/", $str);
  		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * Prep data for form
	 *
	 * This function allows HTML to be safely shown in a form.
	 * Special characters are converted.
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function prep_for_form($data = '')
	{
		if (is_array($data))
		{
			foreach ($data as $key => $val)
			{
				$data[$key] = $this->prep_for_form($val);
			}

			return $data;
		}

		if ($this->_safe_form_data == FALSE OR $data === '')
		{
			return $data;
		}

		return str_replace(array("'", '"', '<', '>'), array("&#39;", "&quot;", '&lt;', '&gt;'), stripslashes($data));
	}

	// --------------------------------------------------------------------

	/**
	 * Prep URL
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function prep_url($str = '')
	{
		if ($str == 'http://' OR $str == '')
		{
			return '';
		}

		if (substr($str, 0, 7) != 'http://' && substr($str, 0, 8) != 'https://')
		{
			$str = 'http://'.$str;
		}

		return $str;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Strip Image Tags
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	/*public function strip_image_tags($str)
	{
		return $this->CI->input->strip_image_tags($str);
	}*/

	// --------------------------------------------------------------------

	/**
	 * XSS Clean
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	/*public function xss_clean($str)
	{
		return $this->CI->security->xss_clean($str);
	}*/

	// --------------------------------------------------------------------

	/**
	 * Convert PHP tags to entities
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function encode_php_tags($str)
	{
		return str_replace(array('<?php', '<?PHP', '<?', '?>'),  array('&lt;?php', '&lt;?PHP', '&lt;?', '?&gt;'), $str);
	}
	
	// --------------------------------------------------------------------

	/**
	 * sanitize strings
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 */
	public function sanitize_string($str)
	{
		return filter_var($str, FILTER_SANITIZE_STRING);
	}
}

$filter = new Filter($config);


?>