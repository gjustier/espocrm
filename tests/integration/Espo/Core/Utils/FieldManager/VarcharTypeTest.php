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

namespace tests\integration\Espo\Core\Utils\FieldManager;

use tests\integration\Core\BaseTestCase;

class VarcharTypeTest extends BaseTestCase
{
    private $jsonFieldDefs = '{
        "type":"varchar",
        "required":true,
        "trim":true,
        "dynamicLogicVisible":null,
        "dynamicLogicRequired":null,
        "dynamicLogicReadOnly":null,
        "name":"testVarchar",
        "label":"TestVarchar",
        "tooltipText":"",
        "isPersonalData":false,
        "default":"",
        "maxLength":null,
        "audited":false,
        "readOnly":false,
        "tooltip":false
    }';

    protected function createFieldManager($app = null)
    {
        if (!$app) {
            $app = $this;
        }

        return $app->getContainer()->get('injectableFactory')->create(
            'Espo\\Tools\\FieldManager\\FieldManager'
        );
    }

    public function testCreate()
    {
        $fieldManager = $this->createFieldManager();

        $fieldDefs = get_object_vars(json_decode($this->jsonFieldDefs));

        $fieldManager->create('Account', 'testVarchar', $fieldDefs);
        $this->getContainer()->get('dataManager')->rebuild(['Account']);

        $app = $this->createApplication();

        $metadata = $app->getContainer()->get('metadata');
        $savedFieldDefs = $metadata->get('entityDefs.Account.fields.cTestVarchar');

        $this->assertArrayHasKey('type', $savedFieldDefs);
        $this->assertArrayHasKey('isCustom', $savedFieldDefs);
        $this->assertEquals('varchar', $savedFieldDefs['type']);
        $this->assertTrue($savedFieldDefs['required']);
        $this->assertTrue($savedFieldDefs['isCustom']);

        $entityManager = $app->getContainer()->get('entityManager');
        $account = $entityManager->getEntity('Account');
        $account->set([
            'name' => 'Test',
            'cTestVarchar' => 'test-value'
        ]);

        $entityManager->saveEntity($account);

        $account = $entityManager->getEntity('Account', $account->getId());
        $this->assertEquals('test-value', $account->get('cTestVarchar'));
    }

    public function testUpdate()
    {
        $this->testCreate();

        $app = $this->createApplication();

        $fieldManager = $this->createFieldManager($app);

        $fieldDefs = get_object_vars(json_decode($this->jsonFieldDefs));
        $fieldDefs['required'] = false;
        $fieldDefs['default'] = 'default-value';

        $fieldManager->update('Account', 'cTestVarchar', $fieldDefs);
        $this->getContainer()->get('dataManager')->rebuild(['Account']);

        $app = $this->createApplication();

        $metadata = $app->getContainer()->get('metadata');
        $savedFieldDefs = $metadata->get('entityDefs.Account.fields.cTestVarchar');

        $this->assertFalse($savedFieldDefs['required']);
        $this->assertEquals('default-value', $savedFieldDefs['default']);

        $entityManager = $app->getContainer()->get('entityManager');

        $account = $entityManager->getEntity('Account');
        $account->set([
            'name' => 'New Test',
        ]);

        $entityManager->saveEntity($account);

        $account = $entityManager->getEntity('Account', $account->getId());
        $this->assertEquals('default-value', $account->get('cTestVarchar'));
    }
}
