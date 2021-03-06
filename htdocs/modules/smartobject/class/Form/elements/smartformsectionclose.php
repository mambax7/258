<?php
// defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Class SmartFormSectionClose
 */
class SmartFormSectionClose extends XoopsFormElement
{
    /**
     * Text
     * @var string
     * @access  private
     */
    public $_value;

    /**
     * SmartFormSectionClose constructor.
     * @param      $sectionname
     * @param bool $value
     */
    public function __construct($sectionname, $value = false)
    {
        $this->setName($sectionname);
        $this->_value = $value;
    }

    /**
     * Get the text
     *
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Prepare HTML for output
     *
     * @return string
     */
    public function render()
    {
        return $this->getValue();
    }
}
