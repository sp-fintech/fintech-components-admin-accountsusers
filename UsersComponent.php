<?php

namespace Apps\Fintech\Components\Accounts\Users;

use Apps\Fintech\Packages\Accounts\Balances\AccountsBalances;
use Apps\Fintech\Packages\Accounts\Users\AccountsUsers;
use Apps\Fintech\Packages\Adminltetags\Traits\DynamicTable;
use System\Base\BaseComponent;

class UsersComponent extends BaseComponent
{
    use DynamicTable;

    protected $accountsUsersPackage;

    protected $accountsBalancesPackage;

    public function initialize()
    {
        $this->accountsUsersPackage = $this->usePackage(AccountsUsers::class);

        $this->accountsBalancesPackage = $this->usePackage(AccountsBalances::class);
    }

    /**
     * @acl(name=view)
     */
    public function viewAction()
    {
        $this->view->currencySymbol = '$';
        if (isset($this->access->auth->account()['profile']['locale_country_id'])) {
            $country = $this->basepackages->geoCountries->getById((int) $this->access->auth->account()['profile']['locale_country_id']);

            if ($country && isset($country['currency_symbol'])) {
                $this->view->currencySymbol = $country['currency_symbol'];
            }
        }

        if (isset($this->getData()['id'])) {
            if ($this->getData()['id'] != 0) {
                $user = $this->accountsUsersPackage->getAccountsUserById((int) $this->getData()['id']);

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
                'conditions'                => '-|account_id|equals|' . $this->access->auth->account()['id'] . '&'
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

        $replaceColumns =
            function ($dataArr) {
                if ($dataArr && is_array($dataArr) && count($dataArr) > 0) {
                    foreach ($dataArr as $key => &$data) {
                        if ($data['account_id'] !== $this->access->auth->account()['id']) {
                            unset($dataArr[$key]);
                        }
                    }
                }

                return $dataArr;
            };

        $this->generateDTContent(
            package: $this->accountsUsersPackage,
            postUrl: 'accounts/users/view',
            postUrlParams: $conditions,
            columnsForTable: ['account_id', 'first_name', 'last_name'],
            withFilter : true,
            columnsForFilter : ['first_name', 'last_name'],
            controlActions : $controlActions,
            dtNotificationTextFromColumn: 'first_name',
            excludeColumns : ['account_id'],
            dtReplaceColumns: $replaceColumns
        );

        $this->view->pick('users/list');
    }

    /**
     * @acl(name=add)
     */
    public function addAction()
    {
        $this->requestIsPost();

        $this->accountsUsersPackage->addAccountsUser($this->postData());

        $this->addResponse(
            $this->accountsUsersPackage->packagesData->responseMessage,
            $this->accountsUsersPackage->packagesData->responseCode,
            $this->accountsUsersPackage->packagesData->responseData ?? []
        );
    }

    /**
     * @acl(name=update)
     */
    public function updateAction()
    {
        $this->requestIsPost();

        $this->accountsUsersPackage->updateAccountsUser($this->postData());

        $this->addResponse(
            $this->accountsUsersPackage->packagesData->responseMessage,
            $this->accountsUsersPackage->packagesData->responseCode,
            $this->accountsUsersPackage->packagesData->responseData ?? []
        );
    }
}