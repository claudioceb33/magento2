<?php

namespace Swissup\SeoCrossLinks\Controller\Adminhtml\Link;

class Disable extends Enable
{
    /**
     * @var string
     */
    protected $msgSuccess = 'Link "%1" was disabled.';

    /**
     * @var integer
     */
    protected $newStatusCode = 0;

}
