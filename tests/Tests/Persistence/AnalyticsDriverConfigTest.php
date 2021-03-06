<?php

namespace Dms\Package\Analytics\Tests\Persistence;

use Dms\Common\Structure\FileSystem\File;
use Dms\Common\Structure\FileSystem\InMemoryFile;
use Dms\Common\Structure\FileSystem\UploadAction;
use Dms\Common\Structure\Geo\Country;
use Dms\Core\File\UploadedFileProxy;
use Dms\Core\Persistence\Db\Mapping\IOrm;
use Dms\Core\Tests\Persistence\Db\Integration\Mapping\DbIntegrationTest;
use Dms\Package\Analytics\AnalyticsDriverConfig;
use Dms\Package\Analytics\Google\GoogleAnalyticsForm;
use Dms\Package\Analytics\Google\GoogleChartMode;
use Dms\Package\Analytics\Persistence\AnalyticsOrm;
use Dms\Package\Analytics\Persistence\DbAnalyticsDriverConfigRepository;

/**
 * @author Elliot Levin <elliotlevin@hotmail.com>
 */
class AnalyticsDriverConfigTest extends DbIntegrationTest
{
    /**
     * @return IOrm
     */
    protected function loadOrm()
    {
        return new AnalyticsOrm();
    }

    public function setUp()
    {
        parent::setUp();
        $this->repo = new DbAnalyticsDriverConfigRepository($this->connection, $this->orm);
    }

    public function testPersistence()
    {
        $driverConfig = new AnalyticsDriverConfig('google', GoogleAnalyticsForm::build([
            'service_account_email' => 'some@email.com',
            'private_key_data'      => [
                'file'   => new UploadedFileProxy($file = new InMemoryFile('abc123', 'some-name.p12')),
                'action' => 'store-new',
            ],
            'view_id'               => 123456,
            'location_chart_mode'   => GoogleChartMode::COUNTRY,
            'map_country'           => Country::AU,
            'tracking_code'         => 'UA-XXXXXX-Y',
        ]));

        $this->repo->save($driverConfig);

        $this->assertDatabaseDataSameAs([
            'analytics' => [
                [
                    'id'      => 1,
                    'driver'  => 'google',
                    'options' => json_encode([
                        'service_account_email' => 'some@email.com',
                        'private_key_data'      => [
                            'file'   => [
                                '__is_proxy'         => true,
                                '__file_path'        => $file->getFullPath(),
                                '__file_client_name' => 'key.p12',
                            ],
                            'action' => UploadAction::STORE_NEW,
                        ],
                        'view_id'               => 123456,
                        'location_chart_mode'   => GoogleChartMode::COUNTRY,
                        'map_country'           => Country::AU,
                        'tracking_code'         => 'UA-XXXXXX-Y',
                        '__class'               => GoogleAnalyticsForm::class,
                    ])
                ]
            ]
        ]);

        $loadedDriverConfig = $this->repo->get(1);

        $this->assertNotSame($driverConfig, $loadedDriverConfig);
        $this->assertEquals($driverConfig, $loadedDriverConfig);
    }
}