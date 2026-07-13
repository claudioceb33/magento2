<?php
/**
 * Plugin for class Magento\Store\Model\Store
 */
namespace Swissup\Hreflang\Plugin\Model;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;

class Store extends \Swissup\Hreflang\Plugin\AbstractPlugin
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $queryParamsToRemove;

    /**
     * Save call arguments for after plugin. Compatibility with Magento 2.1.x.
     *
     * @param  \Magento\Store\Model\Store $subject
     * @param  string                     $type
     * @param  boolean                    $secure
     */
    public function beforeGetBaseUrl(
        \Magento\Store\Model\Store $subject,
        $type = UrlInterface::URL_TYPE_LINK,
        $secure = null
    ) {
        $this->type = $type; // Compatibility with Magento 2.1.x.

        return null;
    }

    /**
     * After method getBaseUrl; replace store code with locale.
     *
     * @param  \Magento\Store\Model\Store $subject
     * @param  string                     $result
     * @param  string $type
     * @param  boolena $secure
     * @return string
     */
    public function afterGetBaseUrl(
        \Magento\Store\Model\Store $subject,
        $result,
        $type = null,
        $secure = null
    ) {
        if ($this->helper->isAdmin($subject)) {
            // magento admin area
            return $result;
        }

        $type = $type ?: $this->type; // Compatibility with Magento 2.1.x.
        if ($type == UrlInterface::URL_TYPE_LINK
            && $this->helper->isLocaleInUrl($subject)
        ) {
            // replace store code with locale
            $result = str_replace(
                '/' . $subject->getCode() . '/',
                '/' . $this->helper->getHreflang($subject) . '/',
                $result
            );
        }

        return $result;
    }

    /**
     * After method 'isUseStoreInUrl'; always true when locale in URL enabled
     *
     * @param  \Magento\Store\Model\Store $subject
     * @param  boolean $result
     * @return boolean
     */
    public function afterIsUseStoreInUrl(
        \Magento\Store\Model\Store $subject,
        $result
    ) {
        if (!$this->helper->isAdmin($subject) // magento admin area
            && $this->helper->isLocaleInUrl($subject)
        ) {
            return true;
        }

        return $result;
    }

    /**
     * Save call arguments for after plugin. Compatibility with Magento 2.1.x.
     *
     * @param  \Magento\Store\Model\Store $subject
     * @param  boolean                    $fromStore
     * @param  array                      $queryParamsToRemove
     */
    public function beforeGetCurrentUrl(
        \Magento\Store\Model\Store $subject,
        $fromStore = true,
        $queryParamsToRemove = [] // my custom parameter
    ) {
        // Compatibility with Magento 2.1.x.
        $this->queryParamsToRemove = $queryParamsToRemove;

        return null;
    }

    /**
     * After method 'getCurrentUrl'.
     *
     * Remove param '___store' from query when isUseStoreInUrl
     *
     * @param  \Magento\Store\Model\Store $subject
     * @param  string                     $result
     * @param  boolean                    $fromStore
     * @param  array                      $queryParamsToRemove
     * @return string
     */
    public function afterGetCurrentUrl(
        \Magento\Store\Model\Store $subject,
        $result,
        $fromStore = null,
        $queryParamsToRemove = null
    ) {
        // Compatibility with Magento 2.1.x.
        $queryParamsToRemove = $queryParamsToRemove ?: $this->queryParamsToRemove;

        $url = htmlspecialchars_decode(rtrim($result, '?'));
        if ($subject->isUseStoreInUrl()
            || $this->helper->isRemoveStorecode($subject)
        ) {
            $queryParamsToRemove[] = '___store';
        }

        if (!empty($queryParamsToRemove)) {
            $parsedUrl = parse_url($url);
            $parsedQuery = [];
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $parsedQuery);
                $initQueryCount = count($parsedQuery);
                foreach ($queryParamsToRemove as $param) {
                    unset($parsedQuery[$param]);
                }

                if ($initQueryCount > count($parsedQuery)) {
                    $url = $parsedUrl['scheme']
                        . '://'
                        . $parsedUrl['host']
                        . (isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '')
                        . $parsedUrl['path']
                        . ($parsedQuery ? '?' . http_build_query($parsedQuery, '', '&') : '');
                }
            }
        }

        return $url;
    }

    /**
     * Prevent warning:
     * 'Illegal offset type in magento/framework/App/MutableScopeConfig.php on line 32'
     * Occurs when 'Locale as subfolder in URLs' enabled during sending email.
     *
     * @param  \Magento\Store\Model\Store $subject
     */
    public function beforeGetFrontendName(
        \Magento\Store\Model\Store $subject
    ) {
        $refProperty = new \ReflectionProperty($subject, '_frontendName');
        $refProperty->setAccessible(true);
        $frontendName = $refProperty->getValue($subject);
        if (null === $frontendName) {
            $storeGroupName = (string)$subject->getValue(Information::XML_PATH_STORE_INFO_NAME);
            $frontendName = !empty($storeGroupName) ? $storeGroupName : $subject->getGroup()->getName();
            $refProperty->setValue($subject, $frontendName);
        }
    }
}
