<?php

namespace App\Tests\Application\Controller;

use App\Tests\Application\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testAnonymousSagsoverblik(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseRedirects('/login');
    }

    public function testSagsoverblik(): void
    {
        $client = static::createAuthenticatedClient(['email' => 'caseworker@example.com']);

        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Sagsoverblik');

        $client->clickLink('Sagsoverblik');
        $this->assertResponseIsSuccessful();
        $this->assertSame('/', $client->getRequest()->getPathInfo());
        $client->back();

        $client->clickLink('Sagsliste');
        $this->assertResponseIsSuccessful();
        $this->assertSame('/case/', $client->getRequest()->getPathInfo());
        $client->back();

        $client->clickLink('Dagsordensliste');
        $this->assertResponseIsSuccessful();
        $this->assertSame('/agenda/', $client->getRequest()->getPathInfo());
        $client->back();

        $client->clickLink('Indstillinger');
        $this->assertResponseRedirects();
        $this->assertSame('/admin', $client->getRequest()->getPathInfo());
        $client->back();
    }

    public function testSagsoverblikCreateCase(): void
    {
        $client = static::createAuthenticatedClient(['email' => 'caseworker@example.com']);

        $client->request('GET', '/');

        $client->clickLink('Ny sag');
        $this->assertResponseIsSuccessful();
        $this->assertSame('/case/new', $client->getRequest()->getPathInfo());
        $client->back();

        $client->clickLink('Ny dagsorden');
        $this->assertResponseIsSuccessful();
        $this->assertSame('/agenda/create', $client->getRequest()->getPathInfo());
        $client->back();
    }
}
