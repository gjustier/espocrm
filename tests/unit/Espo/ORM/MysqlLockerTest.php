<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM – Open Source CRM application.
 * Copyright (C) 2014-2025 Yurii Kuznietsov, Taras Machyshyn, Oleksii Avramenko
 * Website: https://www.espocrm.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace tests\unit\Espo\ORM;

use Espo\ORM\{
    TransactionManager,
    QueryComposer\MysqlQueryComposer,
    EntityFactory,
    Metadata,
    Locker\MysqlLocker,
};

use PDO;

class MysqlLockerTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp() : void
    {
        $this->pdo = $this->getMockBuilder(PDO::class)->disableOriginalConstructor()->getMock();

        $entityFactory = $this->getMockBuilder(EntityFactory::class)->disableOriginalConstructor()->getMock();

        $metadata = $this->getMockBuilder(Metadata::class)->disableOriginalConstructor()->getMock();

        $this->transactionManager = $this->getMockBuilder(TransactionManager::class)
            ->disableOriginalConstructor()->getMock();

        $composer = new MysqlQueryComposer($this->pdo, $entityFactory, $metadata);

        $this->locker = new MysqlLocker($this->pdo, $composer, $this->transactionManager);
    }

    public function testLockCommit()
    {
        $this->pdo
            ->expects($this->exactly(3))
            ->method('exec')
            ->withConsecutive(
                ['LOCK TABLES `account` WRITE'],
                ['LOCK TABLES `contact` READ'],
                ['UNLOCK TABLES'],
            );

        $this->locker->lockExclusive('Account');
        $this->locker->lockShare('Contact');

        $this->assertTrue($this->locker->isLocked());

        $this->locker->commit();

        $this->assertFalse($this->locker->isLocked());
    }

    public function testLockRollback()
    {
        $this->pdo
            ->expects($this->exactly(3))
            ->method('exec')
            ->withConsecutive(
                ['LOCK TABLES `account` WRITE'],
                ['LOCK TABLES `contact` READ'],
                ['UNLOCK TABLES'],
            );

        $this->locker->lockExclusive('Account');
        $this->locker->lockShare('Contact');

        $this->assertTrue($this->locker->isLocked());

        $this->locker->commit();

        $this->assertFalse($this->locker->isLocked());
    }
}
