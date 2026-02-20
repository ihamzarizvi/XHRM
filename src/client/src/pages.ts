/**
 * XHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 XHRM Inc., http://www.XHRM.com
 *
 * XHRM is free software: you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * XHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with XHRM.
 * If not, see <https://www.gnu.org/licenses/>.
 */

import CorePages from '@/core/pages';
import AdminPages from '@/XHRMAdminPlugin';
import PimPages from '@/XHRMPimPlugin';
import HelpPages from '@/XHRMHelpPlugin';
import TimePages from '@/XHRMTimePlugin';
import LeavePages from '@/XHRMLeavePlugin';
import OAuthPages from '@/XHRMCoreOAuthPlugin';
import AttendancePages from '@/XHRMAttendancePlugin';
import MaintenancePages from '@/XHRMMaintenancePlugin';
import RecruitmentPages from '@/XHRMRecruitmentPlugin';
import PerformancePages from '@/XHRMPerformancePlugin';
import CorporateDirectoryPages from '@/XHRMCorporateDirectoryPlugin';
import authenticationPages from '@/XHRMAuthenticationPlugin';
import languagePages from '@/XHRMAdminPlugin';
import dashboardPages from '@/XHRMDashboardPlugin';
import buzzPages from '@/XHRMBuzzPlugin';
import systemCheckPages from '@/XHRMSystemCheckPlugin';
import claimPages from '@/XHRMClaimPlugin';
import PasswordManagerPages from '@/XHRMPasswordManagerPlugin';
import PayrollPages from '@/XHRMPayrollPlugin';

export default {
  ...AdminPages,
  ...PimPages,
  ...CorePages,
  ...HelpPages,
  ...TimePages,
  ...OAuthPages,
  ...LeavePages,
  ...AttendancePages,
  ...MaintenancePages,
  ...RecruitmentPages,
  ...PerformancePages,
  ...CorporateDirectoryPages,
  ...authenticationPages,
  ...languagePages,
  ...dashboardPages,
  ...buzzPages,
  ...systemCheckPages,
  ...claimPages,
  ...PasswordManagerPages,
  ...PayrollPages,
};
