<?php

namespace XHRM\Payroll\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Vue\Component;
use XHRM\Framework\Http\Request;

class AttendanceRulesController extends AbstractVueController
{
    public function preRender(Request $request): void
    {
        $component = new Component('payroll-attendance-rules');
        $this->setComponent($component);
    }
}
