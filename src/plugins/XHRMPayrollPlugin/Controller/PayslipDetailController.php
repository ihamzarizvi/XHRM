<?php

namespace XHRM\Payroll\Controller;

use XHRM\Core\Controller\AbstractVueController;
use XHRM\Core\Vue\Component;
use XHRM\Core\Vue\Prop;
use XHRM\Framework\Http\Request;

class PayslipDetailController extends AbstractVueController
{
    public function preRender(Request $request): void
    {
        $component = new Component('payroll-payslip-detail');
        $id = $request->attributes->getInt('id');
        $component->addProp(new Prop('payslip-id', Prop::TYPE_NUMBER, $id));
        $this->setComponent($component);
    }
}
