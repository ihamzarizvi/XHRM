<?php

namespace OrangeHRM\Attendance\Controller;

use OrangeHRM\Attendance\Service\AttendanceService;
use OrangeHRM\Core\Controller\AbstractController;
use OrangeHRM\Framework\Http\Request;
use OrangeHRM\Framework\Http\Response;

class ImportAttendanceController extends AbstractController
{
    /**
     * @var AttendanceService
     */
    private $attendanceService;

    /**
     * @param AttendanceService $attendanceService
     */
    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            if ($request->files->has('attendance_file')) {
                $file = $request->files->get('attendance_file');
                try {
                    $results = $this->attendanceService->importAttendanceData($file);

                    $message = "Import processing completed. Successfully imported: " . $results['successCount'] . ".";

                    if (!empty($results['errors'])) {
                        $message .= " Errors encountered: " . implode(" ", $results['errors']);
                        $this->getUser()->setFlash('warning', $message);
                    } else {
                        $this->getUser()->setFlash('success', $message);
                    }

                } catch (\Exception $e) {
                    $this->getUser()->setFlash('error', $e->getMessage());
                }
            } else {
                $this->getUser()->setFlash('error', 'Please select a file to upload.');
            }
        }

        return $this->render('orangehrmAttendancePlugin:attendance:importAttendance.html.twig');
    }
}
