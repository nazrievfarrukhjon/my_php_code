<?php

namespace Cache;

use App\Cache\FileCache;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    private string $cacheFile;
    private FileCache $cache;

    protected function setUp(): void
    {
        // create a temp file for each test
        $this->cacheFile = sys_get_temp_dir() . '/filecache_test_' . uniqid() . '.php';
        $this->cache = new FileCache($this->cacheFile);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }

    public function testSetAndGet()
    {
        $this->cache->set('foo', 'bar');
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    public function testGetReturnsDefaultWhenNotFound()
    {
        $this->assertEquals('default', $this->cache->get('missing', 'default'));
    }

    public function testHas()
    {
        $this->cache->set('foo', 'bar');
        $this->assertTrue($this->cache->has('foo'));
        $this->assertFalse($this->cache->has('missing'));
    }

    public function testDelete()
    {
        $this->cache->set('foo', 'bar');
        $this->assertTrue($this->cache->delete('foo'));
        $this->assertFalse($this->cache->has('foo'));
    }

    public function testClear()
    {
        $this->cache->set('a', 1);
        $this->cache->set('b', 2);
        $this->assertTrue($this->cache->clear());
        $this->assertFalse($this->cache->has('a'));
        $this->assertFalse($this->cache->has('b'));
    }

    public function testGetMultiple()
    {
        $this->cache->set('a', 1);
        $this->cache->set('b', 2);

        $values = $this->cache->getMultiple(['a', 'b', 'c'], 'default');

        $this->assertEquals(['a' => 1, 'b' => 2, 'c' => 'default'], $values);
    }

    public function testSetMultiple()
    {
        $this->cache->setMultiple(['a' => 1, 'b' => 2]);

        $this->assertEquals(1, $this->cache->get('a'));
        $this->assertEquals(2, $this->cache->get('b'));
    }

    public function testDeleteMultiple()
    {
        $this->cache->setMultiple(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->cache->deleteMultiple(['a', 'c']);

        $this->assertFalse($this->cache->has('a'));
        $this->assertTrue($this->cache->has('b'));
        $this->assertFalse($this->cache->has('c'));
    }
}
