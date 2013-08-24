<?

/**
 * The HTML helper class.
 * 
 * @author Yarick.
 * @version 0.1
 */
class HTML
{
	
	/**
	 * The function generates select box options.
	 *
	 * @static
	 * @access public
	 * @param array $array The associative array of select box options.
	 * @param mixed $curret The selected value(s).
	 * @param bool $optionGroups If TRUE then optgroup is enabled, if disabled
	 * - FALSE.
	 * @return string The html code of  generated select box.
	 */
	public static function options( $array, $current = null, $optionGroups = false )
	{
		$result = '';
		$group = null;		
		foreach ( $array as $key => $value )
		{
			if ( $optionGroups && $key === $value )
			{
				if ( $group )
				{
					$result .= "</optgroup>\n";
				}
				$group = $key;
				$result .= '<optgroup label="'.htmlspecialchars( $group )."\">\n";
				continue;
			}
			if ( $optionGroups && $group === null )
			{
				//continue;
			}
			if ( $optionGroups && $value === null && $group )
			{
				$group = null;
				$result .= "</optgroup>";
				continue;
			}
			$text = '';
			if ( is_array( $value ) )
			{
				if ( isset( $value['text'] ) )
				{
					$text = $value['text'];
					unset( $value['text'] );
				}
			}
			else
			{
				$text = $value;
				$value = array();
			}
			if ( !isset( $value['value'] ) )
			{
				$value['value'] = $key;				
			}
			$result .= '<option';
			foreach ( $value as $name => $val )
			{
				$result .= ' '.$name.'="'.htmlspecialchars( $val ).'"';
			}
			if ( is_array( $current ) && in_array( $value['value'], $current )
				|| $current == $value['value'] )
			{
				$result .= ' selected="selected"';				
			}
			$result .= '>'.htmlspecialchars( $text )."</option>\n";
		}
		if ( $group )
		{
			$result .= "</optgroup>\n";
		}
		return $result;
	}
	
	/**
	 * The function generates html argument for checked inputs.
	 * 
	 * @static
	 * @access public
	 * @param bool $bool The value.
	 * @return string The argument html code.
	 */
	public static function checked( $bool )
	{
		return (bool)$bool ? 'checked="checked"' : '';
	}

	/**
	 * The function generates html argument for selected options.
	 * 
	 * @static
	 * @access public
	 * @param bool $bool The value.
	 * @return string The argument html code.
	 */
	public static function selected( $bool )
	{
		return (bool)$bool ? 'selected="selected"' : '';
	}

	/**
	 * The function generates html argument for disabled inputs.
	 * 
	 * @static
	 * @access public
	 * @param bool $bool The value.
	 * @return string The argument html code.
	 */
	public static function disabled( $bool )
	{
		return (bool)$bool ? 'disabled="disabled"' : '';
	}
	
	public static function link( $url, $title = null )
	{
		$link = $url;
		if ( strpos( $url, '@' ) !== false )
		{
			$link = 'mailto:'.$link;
		}
		return '<a href="'.$link.'">'.( $title === null ? $url : $title ).'</a>';
	}
	
	/**
	 * The function returns attributes string.
	 * 
	 * @static
	 * @access private
	 * @param array $attrs The attributes.
	 * @return string The attributes string.
	 */
	private static function getAttrString( array $attrs = array() )
	{
		$str = '';
		foreach ( $attrs as $key => $value )
		{
			$str .= $key.'="'.$value.'" ';
		}
		return $str;
	}
	
	/**
	 * The function returns HTML code for current tag.
	 * 
	 * @static
	 * @access public
	 * @param string $name The tag name.
	 * @param string $content The tag content.
	 * @param array $attrs The attributes.
	 * @return string The HTML code.
	 */
	public static function tag( $name, $content = '', array $attrs = array() )
	{
		$single = array('img', 'input');
		$name = strtolower( $name );
		$result = '<'.$name.' '.self::getAttrString( $attrs );
		if ( in_array( $name, $single ) )
		{
			$result .= '/>';
		}
		else
		{
			$result .= '>'.$content.'</'.$name.'>';
		}
		return $result;
	}

}
