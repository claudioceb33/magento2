<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2020 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Info extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);
        $html .= $this->getImplementationNotice($element);
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    /**
     * @param AbstractElement $fieldset
     *
     * @return string
     */
    private function getImplementationNotice($fieldset)
    {
        $content = '<fieldset class="config admin__collapsible-block" id="flexibleforms_options_implementation_help">
                <legend>Implementation Code</legend>
                <h2>(Recommended for developers) If you want to display Contact Form Builder Flexibleforms extension features on specific section (sidebar, pages, etc...) then you can choose one of the below options. (Please replace value "1" with your form id.)
                </h2>
                <div class="message info">
                    <div>Add below code to a CMS Page or a Static Block</div>
                </div><br>
                <div>
                    {{widget type="Pixlogix\Flexibleforms\Block\Forms\Widget" form_id="1"}}
                </div><br>
                <div class="message info">
                    <div>Add below code to a template file</div>
                </div><br>
                <div>
                    $this->getLayout()->createBlock("Pixlogix\Flexibleforms\Block\Forms\Widget")->setWidgetFormId(1)->setTemplate("view.phtml")->toHtml();
                </div><br>
                <div class="message info">
                    <div>Add below code to a layout file</div>
                </div><br>
                <div>
                   &lt;block class="Pixlogix\Flexibleforms\Block\Forms\Widget" name="form_id"&gt;&lt;arguments&gt;&lt;argument name="form_id" xsi:type="string"&gt;1&lt;/argument&gt;&lt;/arguments&gt;&lt;/block&gt;
                </div><br>
            </fieldset>';

        return $content;
    }
}
