<?php

namespace Swissup\RichSnippets\Block;

class FAQPages extends LdJson
{
    /**
     * @return int|false
     */
    private function getFaqId()
    {
        return $this->getRequest()->getParam(\Swissup\KnowledgeBase\Controller\Router::FAQ_ID_PARAM, false);
    }

    /**
     * @return int|false
     */
    private function getFaqCategoryId()
    {
        return $this->getRequest()->getParam(\Swissup\KnowledgeBase\Controller\Router::FAQ_CATEGORY_ID_PARAM, false);
    }

    /**
     * @return array|false|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getItems()
    {
        /** @var \Swissup\KnowledgeBase\Model\Resolver\DataProvider\Faqs $dataProvider */
        $dataProvider = $this->getData('data_provider');

        if (!$dataProvider) {
            return [];
        }

        $dataProvider->setStores([
            \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            $this->_storeManager->getStore()->getId()
        ]);

        $categoryId = $this->getFaqCategoryId();
        if ($categoryId) {
            $dataProvider->setCategories([$categoryId]);
        }

        $id = $this->getFaqId();
        if ($id) {
            $dataProvider->setIds([$id]);
        }
        $data = $dataProvider->getData();

        if ($data['total_count'] < 1) {
            return [];
        }

        return isset($data['items']) ? $data['items'] : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getLdJson()
    {
        if (!$this->getStoreConfig('richsnippets/general/enabled')) {
            return;
        }

        $items = $this->getItems();
        if (empty($items)) {
            return;
        }

        $questions = [];
        foreach($items as $item) {
            $questions[] = [
                "@type" => "Question",
                "name" => $item['title'],
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => $item['content']
                ]
            ];
        }

        $data = [
            '@context' => 'http://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions
        ];
        return $this->prepareJsonString($data);
    }
}
