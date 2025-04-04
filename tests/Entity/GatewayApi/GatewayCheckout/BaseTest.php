<?php

namespace App\Tests\Entity\GatewayApi\GatewayCheckout;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Factory\Gateway\ChargeFactory;
use App\Factory\Gateway\CheckoutFactory;
use App\Factory\Project\ProjectFactory;
use App\Factory\User\UserFactory;
use App\Gateway\CheckoutStatus;
use App\Tests\Traits\TestHelperTrait;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class BaseTest extends ApiTestCase
{
    use TestHelperTrait;
    use ResetDatabase;
    use Factories;

    protected const USER_EMAIL = 'testuser@example.com';
    protected const USER_PASSWORD = 'projectapitestuserpassword';

    protected const BASE_URI = '/v4/gateway_checkouts';

    protected const PAGE_SIZE = 30;
    protected const PAGES_TO_FILL = 1;

    public function setUp(): void
    {
        self::bootKernel();

        self::loadCheckouts();
    }

    private static function loadCheckouts(int $count = self::PAGE_SIZE * self::PAGES_TO_FILL + 1)
    {
        $user = UserFactory::createOne([
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);

        $otherUser = UserFactory::createOne([
            'handle' => 'other_user_test',
            'email' => 'otheruser@test.com',
        ]);

        $project = ProjectFactory::createOne(['owner' => $otherUser]);
        $charge = ChargeFactory::createOne(['target' => $project->getAccounting()]);

        CheckoutFactory::createMany($count, [
            'origin' => $user->getAccounting(),
            'charges' => [$charge],
        ]);
    }

    private function assertChargeIsCorrect($charge)
    {
        $this->assertArrayHasKey('money', $charge);

        $money = $charge['money'];

        $this->assertIsInt($money['amount']);

        $this->assertArrayHasKey('currency', $money);
        $this->assertIsString($money['currency']);
    }

    private function assertArrayKeysExist(array $array, ?array $keys = null)
    {
        $keys ??= [
            'id',
            'gateway',
            'origin',
            'charges',
            'returnUrl',
            'status',
            'links',
            'trackings',
        ];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $array);
        }
    }

    protected function assertCheckoutIsCorrect($checkout)
    {
        $this->assertNotEmpty($checkout);

        $this->assertArrayKeysExist($checkout);

        $this->assertIsInt($checkout['id']);
        $this->assertIsString($checkout['gateway']);
        $this->assertIsString($checkout['origin']);

        $this->assertIsArray($checkout['charges']);

        if (!empty($checkout['charges'])) {
            $this->assertChargeIsCorrect($checkout['charges'][0]);
        }

        $this->assertIsString($checkout['returnUrl']);
        $this->assertContains(
            $checkout['status'],
            [CheckoutStatus::Pending->value, CheckoutStatus::Charged->value]
        );

        $this->assertIsArray($checkout['links']);
        $this->assertIsArray($checkout['trackings']);
    }
}
