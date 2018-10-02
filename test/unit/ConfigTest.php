<?php

namespace CompalexTest;

use Compalex\Config\Config;
use Compalex\Config\Driver;
use Compalex\Config\Exception;

class ConfigTest extends BaseTestCase
{
    const CFG = [
        'debug' => true,
        'sample_row_count' => 100,
        'left' => [
            'host' => 'localhost',
            'database' => 'test_left',
        ],
        'right' => [
            'host' => 'localhost',
            'database' => 'test_right',
        ],
    ];

    function testMissingKeyException()
    {
        $this->expectException(Exception::class);
        new Config([]);
    }

    function testDefaultValues()
    {
        $c = self::CFG;
        unset($c['debug']);
        $config = new Config($c);
        $this->assertEquals(false, $config->debug);
        $this->assertEquals(false, $config['debug']);
    }

    function testCreate()
    {
        $config = new Config(self::CFG);
        $this->assertEquals(self::CFG['debug'], $config->debug);
        $this->assertEquals(self::CFG['sample_row_count'], $config->sample_row_count);
        $this->assertInstanceOf(Driver::class, $config->left);
        $this->assertInstanceOf(Driver::class, $config->right);
        $this->assertEquals(self::CFG['left']['database'], $config->left->database);
        $this->assertEquals(self::CFG['right']['database'], $config->right->database);
        $this->assertEquals(self::CFG['left']['database'], $config['left']['database']);
        $this->assertEquals(self::CFG['right']['database'], $config['right']['database']);
        $this->assertEquals('mysql:host=localhost;port=3306;dbname=test_left;charset=utf8', $config->left->buildDsn());
        $this->assertEquals('mysql:host=localhost;port=3306;dbname=test_right;charset=utf8', $config->right->buildDsn());
    }
}