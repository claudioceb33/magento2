<?php
/**
 * @author Hexamarvel Team
 * @copyright Copyright (c) 2021 Hexamarvel (https://www.hexamarvel.com)
 * @package Hexamarvel_FlexibleForm
 */

namespace Hexamarvel\FlexibleForm\Block\Adminhtml\FieldSet\Tab\Renderer;

use Magento\Backend\Block\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\DataObject;

class Actions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row)
    {
        $actionRenderer = $this->getColumn()->getActions();
        if (empty($actionRenderer) || !is_array($actionRenderer)) {
            return '&nbsp;';
        }

        if (sizeof($actionRenderer) == 1 && !$this->getColumn()->getNoLink()) {
            foreach ($actionRenderer as $actionInner) {
                if (is_array($actionInner)) {
                    return $this->_toLinkHtml($actionInner, $row);
                }
            }
        }

        $outPut = '<select class="admin__control-select" onchange="varienGridAction.execute(this);">' . '<option value="">Select</option>';
        $i = 0;

        foreach ($actionRenderer as $actionInner) {
            $i++;
            if (is_array($actionInner)) {
                $outPut .= $this->_toOptionHtml($actionInner, $row);
            }
        }

        $outPut .= '</select>';
        return $outPut;
    }

    /**
     * @param array $action
     * @param DataObject $row
     * @return string
     */
    protected function _toOptionHtml($action, DataObject $row)
    {
        $actionAttr = new DataObject();
        $actionCaps = '';
        $this->_transformActionData($action, $actionCaps, $row);

        $jsonEncode = $this->escapeHtmlAttr($this->_jsonEncoder->encode($action), false);
        $htmlAttr = [
            'value' => $jsonEncode
        ];

        $actionAttr->setData($htmlAttr);
        return '<option ' . $actionAttr->serialize() . '>' . $actionCaps . '</option>';
    }

    /**
     * @param array $action
     * @param DataObject $row
     * @return string
     */
    protected function _toLinkHtml($action, DataObject $row)
    {
        $actionAttr = new DataObject();
        $actionCap = '';
        $this->_transformActionData($action, $actionCap, $row);

        if (isset($action['confirm'])) {
            $action['onclick'] = 'return window.confirm(\'' . addslashes(
                $this->escapeHtml($action['confirm'])
            ) . '\')';

            unset($action['confirm']);
        }

        $actionAttr->setData($action);
        return '<a ' . $actionAttr->serialize() . '>' . $actionCap . '</a>';
    }

    /**
     * @param array &$action
     * @param string &$actionCaption
     * @param DataObject $row
     * @return $this
     */
    protected function _transformActionData(&$action, &$actionCaption, DataObject $row)
    {
        foreach ($action as $attribute => $value) {
            if (isset($action[$attribute]) && !is_array($action[$attribute])) {
                $this->getColumn()->setFormat($action[$attribute]);
                if($attribute=='caption'){
                    $action[$attribute] = $action[$attribute]->getText();
                } else {
                    $action[$attribute] = parent::render($row);
                }
            } else {
                $this->getColumn()->setFormat(null);
            }

            switch ($attribute) {
                case 'caption':
                    $actionCaption = $action['caption'];

                    unset($action['caption']);
                    break;
                case 'url':
                    if (is_array($action['url']) && isset($action['field'])) {
                        $params = [$action['field'] => $this->_getValue($row)];
                        if (isset($action['url']['params'])) {
                            $params = array_merge($action['url']['params'], $params);
                        }
                        $action['href'] = $this->getUrl($action['url']['base'], $params);
                        unset($action['field']);
                    } else {
                        $action['href'] = $action['url'];
                    }

                    unset($action['url']);
                    break;
                case 'popup':
                    $action['onclick'] = 'popWin(this.href,\'_blank\',\'width=800,height=700,resizable=1,'
                        . 'scrollbars=1\');return false;';
                    break;
            }
        }

        return $this;
    }
}
