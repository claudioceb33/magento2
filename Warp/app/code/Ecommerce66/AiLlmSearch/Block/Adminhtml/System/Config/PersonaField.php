<?php
declare(strict_types=1);

namespace Ecommerce66\AiLlmSearch\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class PersonaField extends Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        
        $elementId = $element->getHtmlId();
        $loadUrl = $this->getUrl('aillmsearch/seller/loadpersona');
        $updateUrl = $this->getUrl('aillmsearch/seller/updatepersona');
        
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        
        $html .= '<div style="margin-top: 10px;">';
        $html .= '<button type="button" id="load-persona-btn" class="action-default scalable" style="margin-right: 10px;">';
        $html .= '<span>' . __('Load from API') . '</span>';
        $html .= '</button>';
        $html .= '<button type="button" id="update-persona-btn" class="action-default scalable primary">';
        $html .= '<span>' . __('Update to API') . '</span>';
        $html .= '</button>';
        $html .= '<span id="persona-status" style="margin-left: 15px; font-weight: bold;"></span>';
        $html .= '</div>';
        
        $html .= "
        <script>
        require([
            'jquery',
            'Magento_Ui/js/modal/alert',
            'mage/translate',
            'loader'
        ], function($, alert, \$t) {
            var elementId = '{$elementId}';
            var loadUrl = '{$loadUrl}';
            var updateUrl = '{$updateUrl}';
            var storeId = {$storeId};
            
            // Load Persona
            $('#load-persona-btn').on('click', function() {
                var btn = $(this);
                btn.prop('disabled', true);
                $('#persona-status').html('<span style=\"color: #1979c3;\">' + \$t('Loading...') + '</span>');
                
                $.ajax({
                    url: loadUrl,
                    type: 'POST',
                    data: { store_id: storeId, form_key: FORM_KEY },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#' + elementId).val(response.persona || '');
                            $('#persona-status').html('<span style=\"color: #79a22e;\">✓ ' + \$t('Loaded successfully!') + '</span>');
                            setTimeout(function() {
                                $('#persona-status').html('');
                            }, 3000);
                        } else {
                            $('#persona-status').html('<span style=\"color: #e02b27;\">✗ ' + (response.message || \$t('Failed to load')) + '</span>');
                        }
                    },
                    error: function(xhr) {
                        $('#persona-status').html('<span style=\"color: #e02b27;\">✗ ' + \$t('Error loading persona') + '</span>');
                        console.error('Load error:', xhr);
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });
            
            // Update Persona
            $('#update-persona-btn').on('click', function() {
                var btn = $(this);
                var persona = $('#' + elementId).val();
                
                btn.prop('disabled', true);
                $('#persona-status').html('<span style=\"color: #1979c3;\">' + \$t('Updating...') + '</span>');
                
                $.ajax({
                    url: updateUrl,
                    type: 'POST',
                    data: { 
                        persona: persona,
                        store_id: storeId,
                        form_key: FORM_KEY 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#persona-status').html('<span style=\"color: #79a22e;\">✓ ' + (response.message || \$t('Updated successfully!')) + '</span>');
                            setTimeout(function() {
                                $('#persona-status').html('');
                            }, 3000);
                        } else {
                            $('#persona-status').html('<span style=\"color: #e02b27;\">✗ ' + (response.message || \$t('Failed to update')) + '</span>');
                        }
                    },
                    error: function(xhr) {
                        $('#persona-status').html('<span style=\"color: #e02b27;\">✗ ' + \$t('Error updating persona') + '</span>');
                        console.error('Update error:', xhr);
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                    }
                });
            });
            
            // Auto-load on page load
            setTimeout(function() {
                $('#load-persona-btn').trigger('click');
            }, 500);
        });
        </script>
        ";
        
        return $html;
    }
}
