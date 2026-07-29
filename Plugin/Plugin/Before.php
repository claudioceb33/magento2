<?php
namespace Ceb\Plugin\Plugin;

use Psr\Log\LoggerInterface;
use Magento\Module\Model\Subject;

class Before
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
     * You can use before methods to change the arguments of an observed method by returning a modified argument. 
     * If there are any arguments, the method should return an array of those arguments. If the method does not change the argument for the observed method, it should return a null value.
     */
    public function beforeSetName(Subject $subject, $name)
    {
        return ['(' . $name . ')'];
    }

}