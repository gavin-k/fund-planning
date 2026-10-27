<?php
namespace backend\tests\functional;

use backend\tests\FunctionalTester;
use common\fixtures\User as UserFixture;
use common\models\User;

class FinancialAccessCest
{
    public function _before(FunctionalTester $I)
    {
        $I->haveFixtures([
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'login_data.php',
            ],
        ]);
    }

    public function guestsCannotReadExportOrMutateFinance(FunctionalTester $I)
    {
        foreach (['fund', 'product', 'income', 'investment', 'return', 'statistics'] as $controller) {
            $actions = $controller === 'statistics' ? ['index'] : ['index', 'view', 'create', 'update', 'delete'];
            if (in_array($controller, ['fund', 'income', 'investment', 'return'], true)) {
                $actions[] = 'export';
            }
            if ($controller === 'investment') {
                $actions[] = 'withdraw';
            }
            foreach ($actions as $action) {
                // Even invalid/missing record IDs must not leak a financial page
                // or reach a mutation before authenticating the caller.
                $I->amOnPage('/' . $controller . '/' . $action . '?id=1');
                $I->seeInCurrentUrl('site%2Flogin');
                $I->see('Username', 'label');
            }
        }
    }

    public function guestPostRequestsAreRejected(FunctionalTester $I)
    {
        foreach (['fund/delete', 'product/delete', 'income/delete', 'investment/delete', 'return/delete', 'investment/withdraw'] as $route) {
            $I->amOnPage('/site/login');
            $I->haveHttpHeader('Accept', 'application/json');
            $I->sendAjaxPostRequest('/' . $route . '?id=2147483647', [
                \Yii::$app->request->csrfParam => \Yii::$app->request->getCsrfToken(),
            ]);
            $I->seeResponseCodeIs(403);
            $I->haveHttpHeader('Accept', 'text/html');
        }
    }

    public function authenticatedUsersCanReadFinance(FunctionalTester $I)
    {
        $I->amLoggedInAs(User::findOne(['username' => 'erau']));
        foreach (['fund', 'product', 'income', 'investment', 'return', 'statistics'] as $controller) {
            $I->amOnPage('/' . $controller . '/index');
            $I->seeResponseCodeIs(200);
            $I->dontSeeElement('#login-form');
        }
    }
}
