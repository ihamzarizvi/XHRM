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

import ClaimEvent from '@/XHRMClaimPlugin/pages/ClaimEvent.vue';
import SaveClaimEvent from '@/XHRMClaimPlugin/pages/SaveClaimEvent.vue';
import EditClaimEvent from '@/XHRMClaimPlugin/pages/EditClaimEvent.vue';
import ClaimExpenseType from '@/XHRMClaimPlugin/pages/claimExpenseTypes/ClaimExpenseType.vue';
import SaveClaimExpenseType from '@/XHRMClaimPlugin/pages/claimExpenseTypes/SaveClaimExpenseType.vue';
import EditClaimExpenseType from '@/XHRMClaimPlugin/pages/claimExpenseTypes/EditClaimExpenseType.vue';
import SubmitClaimRequest from '@/XHRMClaimPlugin/pages/submitClaim/SubmitClaimRequest.vue';
import SubmitClaim from '@/XHRMClaimPlugin/pages/submitClaim/SubmitClaim.vue';
import MyClaims from '@/XHRMClaimPlugin/pages/myClaims/MyClaims.vue';
import AssignClaimRequest from '@/XHRMClaimPlugin/pages/assignClaim/AssignClaimRequest.vue';
import AssignClaim from '@/XHRMClaimPlugin/pages/assignClaim/AssignClaim.vue';
import EmployeeClaims from '@/XHRMClaimPlugin/pages/employeeClaims/EmployeeClaims.vue';

export default {
  'claim-event': ClaimEvent,
  'claim-event-create': SaveClaimEvent,
  'claim-event-edit': EditClaimEvent,
  'claim-expense-types': ClaimExpenseType,
  'claim-expense-type-create': SaveClaimExpenseType,
  'claim-expense-type-edit': EditClaimExpenseType,
  'submit-claim-request': SubmitClaimRequest,
  'submit-claim': SubmitClaim,
  'my-claim': MyClaims,
  'assign-claim-request': AssignClaimRequest,
  'assign-claim': AssignClaim,
  'employee-claim': EmployeeClaims,
};
