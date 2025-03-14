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

                if (!$user['balances']) {
                    $user['balances'] = [];
                }

                $user['equity_balance'] =
                    str_replace('EN_ ', '', (new \NumberFormatter('en_IN', \NumberFormatter::CURRENCY))->formatCurrency($user['equity_balance'], 'en_IN'));

                if (count($user['balances']) > 0) {
                    foreach ($user['balances'] as &$balance) {
                        $balance['amount'] =
                            str_replace('EN_ ', '', (new \NumberFormatter('en_IN', \NumberFormatter::CURRENCY))->formatCurrency($balance['amount'], 'en_IN'));
                    }
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
                        } else {
                            $data['equity_balance'] = $this->view->currencySymbol .
                                str_replace('EN_ ', '', (new \NumberFormatter('en_IN', \NumberFormatter::CURRENCY))->formatCurrency($data['equity_balance'], 'en_IN'));
                        }
                    }
                }

                return $dataArr;
            };

        $this->generateDTContent(
            package: $this->accountsUsersPackage,
            postUrl: 'accounts/users/view',
            postUrlParams: $conditions,
            columnsForTable: ['account_id', 'first_name', 'last_name', 'equity_balance'],
            withFilter : true,
            columnsForFilter : ['first_name', 'last_name', 'equity_balance'],
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

    public function addAccountsBalanceAction()
    {
        $this->requestIsPost();

        $this->accountsBalancesPackage->addAccountsBalances($this->postData());

        $this->addResponse(
            $this->accountsBalancesPackage->packagesData->responseMessage,
            $this->accountsBalancesPackage->packagesData->responseCode,
            $this->accountsBalancesPackage->packagesData->responseData ?? []
        );
    }

    public function updateAccountsBalanceAction()
    {
        $this->requestIsPost();

        $this->accountsBalancesPackage->updateAccountsBalances($this->postData());

        $this->addResponse(
            $this->accountsBalancesPackage->packagesData->responseMessage,
            $this->accountsBalancesPackage->packagesData->responseCode,
            $this->accountsBalancesPackage->packagesData->responseData ?? []
        );
    }

    public function removeAccountsBalanceAction()
    {
        $this->requestIsPost();

        $this->accountsBalancesPackage->removeAccountsBalances($this->postData());

        $this->addResponse(
            $this->accountsBalancesPackage->packagesData->responseMessage,
            $this->accountsBalancesPackage->packagesData->responseCode,
            $this->accountsBalancesPackage->packagesData->responseData ?? []
        );
    }

    public function recalculateAccountsBalanceAction()
    {
        $this->requestIsPost();

        $this->accountsBalancesPackage->recalculateUserEquity($this->postData());

        $this->addResponse(
            $this->accountsBalancesPackage->packagesData->responseMessage,
            $this->accountsBalancesPackage->packagesData->responseCode,
            $this->accountsBalancesPackage->packagesData->responseData ?? []
        );
    }
}