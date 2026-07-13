<?php

namespace Swissup\RichSnippets\Block;

class QAPage extends LdJson
{
    /**
     * @return bool
     */
    private function isProductPage()
    {
        return 'catalog_product_view' === $this->getRequest()->getFullActionName();
    }

    /**
     * Get product id
     *
     * @return int|false
     */
    private function getProductId()
    {
        $productId = $this->getRequest()->getParam('product_id', false);
        if (!$productId && $this->isProductPage()) {
            $productId = $this->getRequest()->getParam('id', false);
        }

        return $productId;
    }

    /**
     * @return array|false|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getItems()
    {
        $dataProvider = $this->getData('data_provider');

        if (!$dataProvider) {
            return [];
        }

        $dataProvider->setStores([
            \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            $this->_storeManager->getStore()->getId()
        ]);

        $productId = $this->getProductId();
        if ($this->isProductPage() && !$productId) {
            return [];
        }
        if ($productId) {
            $dataProvider->setProductId($productId);
        }

        $data = $dataProvider->getData();

        if ($data['total_count'] < 1) {
            return [];
        }

        return isset($data['questions']) ? $data['questions'] : [];
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

            $answers = [];
            if (isset($item['answers'])) {
                foreach($item['answers'] as $answer) {
                    $answers[] = [
                        '@type' => 'Answer',
                        'text' => $answer['text'],
                        'upvoteCount' => (int) $answer['hint'],
                        //  'url' => 'https://example.com/question1#acceptedAnswer' //strongly recommended
                    ];
                }
            }
            $answerCount = count($answers);
            $acceptedAnswer = $answerCount > 0 ? array_pop($answers) : false;
            $upvoteCount = (int) $item['hint'];

            $question = [
                '@type' => 'Question',
                'name' => $item['text'],
                'text' => $item['text'],
                'answerCount' => $answerCount,
                'upvoteCount' => $upvoteCount,
            ];
            if ($acceptedAnswer) {
                $question['acceptedAnswer'] = $acceptedAnswer;
            }
            if (!empty($answers)) {
                $question['suggestedAnswer'] = $answers;
            }
            $questions[] = $question;
        }
        $mainEntity = array_shift($questions);
        foreach($questions as $question) {
            if ($mainEntity['upvoteCount'] < $question['upvoteCount']) {
                $mainEntity = $question;
            }
        }

        $data = [
            '@context' => 'http://schema.org',
            '@type' => 'QAPage',
            'mainEntity' => $mainEntity
        ];

        return $this->prepareJsonString($data);
    }
}
