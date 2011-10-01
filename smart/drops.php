<?php
defined( '_JEXEC' ) or die;

/**
 * An helper class for generating dropdowns in Joomla.
 *
 * @package default
 * @author Joseph LeBlanc
 */
class MissingSmartDrops
{
	public $default_value = 0;
	public $default_label = '-select-';
	public $onchange = '';
	public $css_class = '';

	private $value_source;
	private $input_name;
	private $value;
	private $source_type;

	/**
	 * Expects the name of the dropdown you're generating, along with a value
	 * source. The value source can be an array of values, or an SQL query. If
	 * an SQL query is used, it MUST return the columns 'value' and 'text' for
	 * the option values and labels, repsectively. Also allows the value to be
	 * preset.
	 *
	 * @param string $input_name
	 * @param mixed $value_source
	 * @param mixed $value
	 * @throws Exception
	 * @author Joseph LeBlanc
	 */
	function __construct($input_name, $value_source, $value = 0, $source_type = 'single')
	{
		if (!is_array($value_source) && !is_string($value_source) && !($value_source instanceof JDatabaseQuery)) {
			throw new Exception("The value source for SmartDrops must either be an SQL query or array of values");
		}

		$this->value_source = $value_source;
		$this->input_name = $input_name;
		$this->value = $value;
		$this->source_type = $source_type;
	}

	/**
	 * Generates the dropdown based on the preset value source and input name.
	 *
	 * @return void
	 * @author Joseph LeBlanc
	 */
	public function generateDropdown()
	{
		// If SQL, run it and get values. Otherwise, make from array.
		if (is_string($this->value_source) || $this->value_source instanceof JDatabaseQuery) {
			$options = $this->getOptionsFromSQL();
		} else {
			$options = $this->getOptionsFromArray();
		}

		$option = JHTML::_('select.option', $this->default_value, $this->default_label);
		$options = array_merge(array($option), $options);

		$attribs = array();

		if (!count($options)) {
			$attribs['disabled'] = 'disabled';
		}

		if ($this->onchange) {
			$attribs['onchange'] = $this->onchange;
		}

		if ($this->css_class) {
			$attribs['class'] = $this->css_class;
		}

		$dropdown = JHTML::_('select.genericlist', $options, $this->input_name, $attribs, 'value', 'text', $this->value);

		return $dropdown;
	}

	/**
	 * Assumes the value_source object property is a valid query, returning
	 * value and text as columns
	 *
	 * @return array
	 * @author Joseph LeBlanc
	 */
	private function getOptionsFromSQL()
	{
		$db = JFactory::getDBO();
		$db->setQuery($this->value_source);
 		$rows = $db->loadObjectList();

		return $rows;
	}

	/**
	 * Assumes the value_source object property is an array. If the keyed
	 * option has been set, the array keys will be used as the dropdown values,
	 * while the array values will be used as the labels. Otherwise, the array
	 * values will be used for the values as well as the labels.
	 *
	 * @return array
	 * @author Joseph LeBlanc
	 */
	private function getOptionsFromArray()
	{
		$options = array();

		foreach ($this->value_source as $key => $label) {
			if ($this->source_type == 'keyed') {
				$options[] = JHTML::_('select.option', $key, $label);
			} else {
				// single elements
				$options[] = JHTML::_('select.option', $label, $label);
			}
		}

		return $options;
	}

	/**
	 * PHP magic method: if this object is converted to a string,
	 * generateDropdown() is called and the result is returned.
	 *
	 * @return string
	 * @author Joseph LeBlanc
	 */
	public function __tostring()
	{
		return $this->generateDropdown();
	}
}
