<?php

namespace App\Tests\Application\Controller;

use App\Tests\Application\AuthenticatedWebTestCase;

class CaseControllerTest extends AuthenticatedWebTestCase
{
    protected static array $userCriteria = ['email' => 'caseworker@example.com'];

    public function testSomething(): void
    {
        $client = static::createClient();
        $client->request('GET', '/case/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Sager');
    }
}
