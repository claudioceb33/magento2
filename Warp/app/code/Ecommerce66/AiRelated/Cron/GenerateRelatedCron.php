<?php
namespace Ecommerce66\AiRelated\Cron;

use Psr\Log\LoggerInterface;
use Ecommerce66\AiRelated\Model\Generator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Ecommerce66\AiRelated\Helper\Data as AiHelper;

class GenerateRelatedCron
{
    private $logger;
    private $generator;
    private $scopeConfig;
    private $aiHelper;

    public function __construct(LoggerInterface $logger, Generator $generator, ScopeConfigInterface $scopeConfig, AiHelper $aiHelper)
    {
        $this->logger = $logger;
        $this->generator = $generator;
        $this->scopeConfig = $scopeConfig;
        $this->aiHelper = $aiHelper;
    }

    public function execute()
    {
        $enabled = $this->scopeConfig->getValue('ecommerce66_ai/ai_related/cron_enable');
        if (!$enabled) {
            return;
        }
        $batch = (int)$this->scopeConfig->getValue('ecommerce66_ai/ai_related/cron_batch_size') ?: 50;
        if ($this->aiHelper->isInfoLoggingEnabled()) {
            $this->logger->info('AI Related Cron: starting generation with batch ' . $batch);
        }
        $result = $this->generator->generate($batch);
        if ($this->aiHelper->isInfoLoggingEnabled()) {
            $this->logger->info('AI Related Cron: finished. ' . json_encode($result));
        }
    }
}
