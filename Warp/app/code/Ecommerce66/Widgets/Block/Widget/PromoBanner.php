<?php
declare(strict_types=1);

namespace Ecommerce66\Widgets\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;

class PromoBanner extends Base
{
    /**
     * @var string
     */
    protected $_template = 'Ecommerce66_Widgets::widget/promobanners.phtml';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_file;

    /**
     * PromoBanner constructor.
     *
     * @param Template\Context                          $context
     * @param \Magento\Framework\Filesystem\Driver\File $file
     * @param array                                     $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Framework\Filesystem\Driver\File $file,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $data
        );

        $this->_filesystem = $context->getFilesystem();
        $this->_file = $file;
    }

    /**
     * @return array|mixed|string|null
     */
    public function getBannerUrl()
    {
        if ($this->hasData('url') && ($this->getData('url')!='')) {
            if (filter_var($this->getData('url'), FILTER_VALIDATE_URL)) {
                return $this->getData('url');
            }
            return $this->getUrl($this->getData('url'));
        }
        return '#';
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        $filePath = '';
        if ($this->getData('banner_image')!='') {
            $filePath = $this->getMediaUrl() . 'mgs/fbuilder/promobanners' . $this->getData('banner_image');

            return $filePath;
        }
        return $filePath;
    }

}
