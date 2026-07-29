<?php
namespace Ceb\ModuleName\Plugin\Plugin;

use Psr\Log\LoggerInterface;
use Magento\Module\Model\Subject;

class Around
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function aroundSave(Subjec $subject, callable $proceed)
    {
        $someValue = $this->doSmthBeforeProductIsSaved();
        $returnValue = null;

        if ($this->canCallProceedCallable($someValue)) {
            /*If the around method does not call the callable, 
        it will prevent the execution of all the plugins next in the chain and the original method call.*/
            $returnValue = $proceed();
        }

        if ($returnValue) {
            $this->postProductToFacebook();
        }

        return $returnValue;
    }

    /*You must be careful to match the default parameters and type hints of the original signature of the method.*/
    public function aroundSaveTwo(Subjec $subject, callable $proceed, SomeType $obj = null)
    {
        //do something
    }
}