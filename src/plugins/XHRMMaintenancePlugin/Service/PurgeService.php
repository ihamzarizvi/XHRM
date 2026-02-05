<?php

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

namespace XHRM\Maintenance\Service;

use Exception;
use XHRM\Core\Traits\EventDispatcherTrait;
use XHRM\Core\Traits\ORM\EntityManagerHelperTrait;
use XHRM\Maintenance\Dao\PurgeDao;
use XHRM\Maintenance\Dto\InfoArray;
use XHRM\Maintenance\Event\MaintenanceEvent;
use XHRM\Maintenance\Event\PurgeEmployee;
use XHRM\Maintenance\PurgeStrategy\PurgeStrategy;
use XHRM\ORM\Exception\TransactionException;
use XHRM\Pim\Service\EmployeePictureService;
use Symfony\Component\Yaml\Yaml;

class PurgeService
{
    use EntityManagerHelperTrait;
    use EventDispatcherTrait;

    private const GDPR_PURGE_EMPLOYEE = 'gdpr_purge_employee_strategy';
    private const GDPR_PURGE_CANDIDATE = 'gdpr_purge_candidate_strategy';

    private ?PurgeDao $purgeDao = null;
    private ?EmployeePictureService $employeePictureService = null;
    private ?array $purgeableEntities = null;

    /**
     * @return PurgeDao
     */
    public function getPurgeDao(): PurgeDao
    {
        return $this->purgeDao ??= new PurgeDao();
    }

    public function getEmployeePictureService(): EmployeePictureService
    {
        return $this->employeePictureService ??= new EmployeePictureService();
    }

    /**
     * @param int $empNumber
     * @throws TransactionException
     */
    public function purgeEmployeeData(int $empNumber): void
    {
        $this->beginTransaction();
        try {
            $purgeableEntities = $this->getPurgeableEntities(self::GDPR_PURGE_EMPLOYEE);
            foreach ($purgeableEntities as $purgeableEntityClassName => $purgeStrategies) {
                foreach ($purgeStrategies['PurgeStrategy'] as $strategy => $strategyInfoArray) {
                    $infoArray = new InfoArray($strategyInfoArray);
                    $purgeStrategy = $this->getPurgeStrategy(
                        $purgeableEntityClassName,
                        $strategy,
                        $infoArray
                    );
                    $purgeStrategy->purge($empNumber);
                }
            }
            $this->getEntityManager()->flush();

            $this->getEmployeePictureService()->deleteEmpPictureETagByEmpNumber($empNumber);

            $this->getEventDispatcher()->dispatch(
                new PurgeEmployee($empNumber),
                MaintenanceEvent::PURGE_EMPLOYEE_END
            );
            $this->commitTransaction();

            $this->getEventDispatcher()->dispatch(
                new PurgeEmployee($empNumber),
                MaintenanceEvent::PURGE_EMPLOYEE_FINISHED
            );
        } catch (Exception $exception) {
            $this->rollBackTransaction();
            throw new TransactionException($exception);
        }
    }

    /**
     * @param int $vacancyId
     * @throws TransactionException
     */
    public function purgeCandidateData(int $vacancyId): void
    {
        $this->beginTransaction();
        try {
            $purgeableEntities = $this->getPurgeableEntities(self::GDPR_PURGE_CANDIDATE);
            foreach ($purgeableEntities as $purgeableEntityClassName => $purgeStrategies) {
                foreach ($purgeStrategies['PurgeStrategy'] as $strategy => $strategyInfoArray) {
                    $infoArray = new InfoArray($strategyInfoArray);
                    $purgeStrategy = $this->getPurgeStrategy(
                        $purgeableEntityClassName,
                        $strategy,
                        $infoArray
                    );
                    $purgeStrategy->purge($vacancyId);
                }
            }
            $this->getEntityManager()->flush();
            $this->commitTransaction();
        } catch (Exception $exception) {
            $this->rollBackTransaction();
            throw new TransactionException($exception);
        }
    }

    /**
     * @param string $fileName
     * @return array
     */
    public function getPurgeableEntities(string $fileName): array
    {
        if (is_null($this->purgeableEntities)) {
            $path = realpath(dirname(__FILE__, 2)) . '/config/' . $fileName . '.yaml';
            $this->purgeableEntities = Yaml::parseFile($path);
        }
        return $this->purgeableEntities['Entities'];
    }

    /**
     * @param string $purgeableEntityClassName
     * @param string $strategy
     * @param InfoArray $infoArray
     * @return PurgeStrategy
     */
    public function getPurgeStrategy(
        string $purgeableEntityClassName,
        string $strategy,
        InfoArray $infoArray
    ): PurgeStrategy {
        $purgeStrategyClass = 'XHRM\\Maintenance\\PurgeStrategy\\' . $strategy . 'PurgeStrategy';
        return new $purgeStrategyClass($purgeableEntityClassName, $infoArray);
    }
}

