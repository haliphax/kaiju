<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
function compress()
{
	$CI =& get_instance();
	$buffer = $CI->output->get_output();
	$search = array(
		'/\n/',			// replace end of line by a space
		'/\>[^\S ]+/s',	// strip whitespaces after tags, except space
		'/[^\S ]+\</s',	// strip whitespaces before tags, except space
		'/(\s)+/s',		// shorten multiple whitespace sequences
		'/<!--(.|\s)*?-->/s',	//strip HTML comments
		'#(?://)?<!\[CDATA\[(.*?)(?://)?\]\]>#s' //leave CDATA alone
	);

	$replace = array(
		' ',
		'>',
		'<',
		'\\1',
		'',
		"//&lt;![CDATA[\n".'\1'."\n//]]>"
	);

	$buffer = preg_replace($search, $replace, $buffer); 
	$CI->output->set_output($buffer);
	$CI->output->_display();
}
 
/* End of file compress.php */
/* Location: ./application/hooks/compress.php */
