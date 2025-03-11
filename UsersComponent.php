<?php

namespace Apps\Fintech\Components\Accounts\Users;

use Apps\Fintech\Packages\Accounts\Users\AccountsUsers;
use Apps\Fintech\Packages\Adminltetags\Traits\DynamicTable;
use System\Base\BaseComponent;

class UsersComponent extends BaseComponent
{
    use DynamicTable;

    protected $accountsUsersPackage;

    public function initialize()
    {
        $this->accountsUsersPackage = $this->usePackage(AccountsUsers::class);
    }

    /**
     * @acl(name=view)
     */
    public function viewAction()
    {
        if (isset($this->getData()['id'])) {
            if ($this->getData()['id'] != 0) {
                $user = $this->accountsUsersPackage->getById((int) $this->getData()['id']);

                if (!$user) {
                    return $this->throwIdNotFound();
                }

                $this->view->user = $user;
            }

            $this->view->pick('users/view');

            return;
        }

        $conditions =
            [
                'conditions'    => '-|user_id|equals|' . $this->access->auth->account()['id'] . '&'
            ];

        $controlActions =
            [
                // 'disableActionsForIds'  => [1],
                'actionsToEnable'       =>
                [
                    'edit'      => 'accounts/users',
                    'remove'    => 'accounts/users/remove'
                ]
            ];

        $this->generateDTContent(
            $this->accountsUsersPackage,
            'accounts/users/view',
            $conditions,
            ['first_name', 'last_name', 'equity_balance'],
            true,
            ['first_name', 'last_name', 'equity_balance'],
            $controlActions,
            null,
            null,
            'first_name'
        );

        $this->view->pick('users/list');
    }

    /**
     * @acl(name=add)
     */
    public function addAction()
    {
        $this->requestIsPost();

        //$this->package->add{?}($this->postData());

        $this->addResponse(
            $this->package->packagesData->responseMessage,
            $this->package->packagesData->responseCode
        );
    }

    /**
     * @acl(name=update)
     */
    public function updateAction()
    {
        $this->requestIsPost();

        //$this->package->update{?}($this->postData());

        $this->addResponse(
            $this->package->packagesData->responseMessage,
            $this->package->packagesData->responseCode
        );
    }

    /**
     * @acl(name=remove)
     */
    public function removeAction()
    {
        $this->requestIsPost();

        //$this->package->remove{?}($this->postData());

        $this->addResponse(
            $this->package->packagesData->responseMessage,
            $this->package->packagesData->responseCode
        );
    }
}