<?php

namespace XHRM\Payroll\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Vue\Component;
use XHRM\Framework\Http\Request;

class ApprovePayrollController extends AbstractVueController
{
    public function preRender(Request $request): void
    {
        $component = new Component('payroll-approve-list');
        $this->setComponent($component);
    }
}
