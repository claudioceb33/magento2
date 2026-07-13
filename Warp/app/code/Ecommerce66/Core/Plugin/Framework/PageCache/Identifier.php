<?php

namespace Ecommerce66\Core\Plugin\Framework\PageCache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Page unique identifier
 */
class Identifier
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context $context
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $context,
        Json $serializer = null
    ) {
        $this->request = $request;
        $this->context = $context;
        // todo: remove object manager
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * @return string
     */
    public function afterGetValue()
    {
        $pattern = [
            '/&?utm_.+?(&|$)$/',
            '/&?fbclid.+?(&|$)$/',
            '/&?gclid.+?(&|$)$/',
            '/&?dclid.+?(&|$)$/',
            '/&?mc_.+?(&|$)$/',
            '/&?trk_.+?(&|$)$/',
            '/&?siteurl.+?(&|$)$/',
            '/&?eventName=View.+?(&|$)$/',
            '/&?eventName=Search.+?(&|$)$/',
            '/&?sections=apptrian.+?(&|$)$/'
        ];
        $url = preg_replace($pattern, '', $this->request->getUriString());
        $data = [
            $this->request->isSecure(),
            $url ,
            $this->request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)
                ?: $this->context->getVaryString()
        ];

        return sha1($this->serializer->serialize($data));
    }
}
