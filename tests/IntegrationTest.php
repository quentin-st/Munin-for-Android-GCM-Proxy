<?php

namespace App\Tests;

use App\Service\FirebaseService;
use App\Tests\Service\FakeFirebaseService;
use Generator;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class IntegrationTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/');
        self::assertSame(302, $this->client->getResponse()->getStatusCode());
        self::assertSame('https://www.munin-for-android.com', $this->client->getResponse()->headers->get('Location'));
    }

    public function testPing(): void
    {
        $this->client->request('GET', '/ping');
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        self::assertSame('pong', $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider dataProvider_testDeclareAlertMissingParam
     */
    public function testDeclareAlertMissingParam(array $parameters, string $expectedMessage): void
    {
        $this->client->request('POST', '/trigger/declareAlert', $parameters);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame([
            'success' => false,
            'message' => $expectedMessage,
        ], $response);
    }

    public function dataProvider_testDeclareAlertMissingParam(): Generator
    {
        yield [
            [],
            'Missing param: reg_ids',
        ];
        yield [
            ['reg_ids' => []],
            'Missing param: data',
        ];
    }

    public function testDeclareAlert(): void
    {
        // Test with an invalid XML input
        $this->client->request('POST', '/trigger/declareAlert', [
            'reg_ids' => json_encode(['abcdef123456789']),
            'data' => 'not valid xml',
            'contribute_to_stats' => false,
        ]);
        /** @var FakeFirebaseService $fakeFirebaseService */
        $fakeFirebaseService = $this->client->getContainer()->get(FirebaseService::class);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame([
            'success' => false,
            'message' => 'Error processing input: ',
        ], $response);
        self::assertNull($fakeFirebaseService->getLastPayload());

        $alertsData = <<<XML
<a>
	<alert 
	  group="disk"
	  host="demo.munin-monitoring.org"
	  graph_category="disk" 
	  graph_title="Disk usage in percent"
    >
		<warning label="/dev/sda1" value="50" w="40" c="80" extra="" />
		<critical label="/dev/sda2" value="90" w="40" c="80" extra="" />
		<unknown label="/boot/efi" value="" w="" c="" extra="" />
	</alert>
</a>
XML;
        $this->client->request('POST', '/trigger/declareAlert', [
            'reg_ids' => json_encode(['abcdef123456789']),
            'data' => $alertsData,
            'contribute_to_stats' => false,
        ]);
        /** @var FakeFirebaseService $fakeFirebaseService */
        $fakeFirebaseService = $this->client->getContainer()->get(FirebaseService::class);
        self::assertSame(200, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame([
            'success' => true,
        ], $response);
        self::assertSame([
            'alerts' => [
                [
                    'group' => 'disk',
                    'host' => 'demo.munin-monitoring.org',
                    'category' => 'disk',
                    'plugin' => 'Disk usage in percent',
                    'fields' => [
                        [
                            'label' => '/dev/sda1',
                            'value' => '50',
                            'w' => '40',
                            'c' => '80',
                            'extra' => '',
                            'level' => 'w',
                        ],
                        [
                            'label' => '/dev/sda2',
                            'value' => '90',
                            'w' => '40',
                            'c' => '80',
                            'extra' => '',
                            'level' => 'c',
                        ],
                        [
                            'label' => '/boot/efi',
                            'value' => '',
                            'w' => '',
                            'c' => '',
                            'extra' => '',
                            'level' => 'u',
                        ],
                    ],
                ],
            ],
        ], $fakeFirebaseService->getLastPayload());
    }
}
