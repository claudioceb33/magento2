# How to use

Add a text field like in example in your system xml:

    <field id="categories" translate="label" type="text" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
        <label>Categories</label>
        <frontend_model>Ecommerce66\Core\Block\Adminhtml\System\Config\CategoryTree</frontend_model>
        <backend_model>Magento\Config\Model\Config\Backend\Serialized\ArraySerialized</backend_model>
    </field>
                
Values in database will be stored as json array:
 
    ["217","257","975","976","986","977"]                