<?php
namespace Ceb\ModuleName\Plugin\Plugin;

use Psr\Log\LoggerInterface;
use Magento\Module\Model\Subject;

class After
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * You can use these methods to change the result of an observed method by modifying the original result and returning it at the end of the method.
     */
    public function afterGetName(Subject $subject, $result, $name)
    {
        return '|' . $result . '|';
        $this->logger->debug('Name original: ' . $name);
    }
}