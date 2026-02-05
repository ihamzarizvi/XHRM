<?php

namespace XHRM\Admin\Traits\Service;

use XHRM\Admin\Service\PayGradeService;
use XHRM\Core\Traits\ServiceContainerTrait;
use XHRM\Framework\Services;

trait PayGradeServiceTrait
{
    use ServiceContainerTrait;

    /**
     * @return PayGradeService
     */
    public function getPayGradeService(): PayGradeService
    {
        return $this->getContainer()->get(Services::PAY_GRADE_SERVICE);
    }
}

