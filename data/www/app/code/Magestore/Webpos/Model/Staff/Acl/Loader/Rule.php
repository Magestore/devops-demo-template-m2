<?php

/**
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */
namespace Magestore\Webpos\Model\Staff\Acl\Loader;

use Magento\Framework\App\ResourceConnection;

class Rule extends \Magento\Authorization\Model\Acl\Loader\Rule
{
    public function populateAcl(\Magento\Framework\Acl $acl)
    {
        $webposRuleTable = $this->_resource->getTableName("webpos_authorization_rule");

        $connection = $this->_resource->getConnection();

        $select = $connection->select()->from(['r' => $webposRuleTable]);

        $rulesArray = $connection->fetchAll($select);

        foreach ($rulesArray as $webposRule) {
            $webposRole = $webposRule['role_id'];
            $resource = $webposRule['resource_id'];
            $privileges = !empty($webposRule['privileges']) ? explode(',', $webposRule['privileges']) : null;

            if ($acl->has($resource)) {
                if ($webposRule['permission'] == 'allow') {
                    if ($resource === $this->_rootResource->getId()) {
                        $acl->allow($webposRole, null, $privileges);
                    }
                    $acl->allow($webposRole, $resource, $privileges);
                } elseif ($webposRule['permission'] == 'deny') {
                    $acl->deny($webposRole, $resource, $privileges);
                }
            }
        }
    }
}
