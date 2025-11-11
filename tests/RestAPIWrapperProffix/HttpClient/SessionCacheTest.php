<?php

namespace Pitwch\RestAPIWrapperProffix\Tests\RestAPIWrapperProffix\HttpClient;

use Pitwch\RestAPIWrapperProffix\HttpClient\SessionCache;
use PHPUnit\Framework\TestCase;

class SessionCacheTest extends TestCase
{
    private SessionCache $cache;
    private string $testSessionId = 'test-session-id-12345';

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new SessionCache('testuser', 'testdb', 'https://test.example.com:1500');
        // Clear any existing cache before each test
        $this->cache->clear();
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        $this->cache->clear();
        parent::tearDown();
    }

    public function testCanSaveAndLoadSession(): void
    {
        // Save a session
        $result = $this->cache->save($this->testSessionId);
        $this->assertTrue($result, 'Session save should return true');

        // Load the session
        $loadedSession = $this->cache->load();
        $this->assertEquals($this->testSessionId, $loadedSession, 'Loaded session should match saved session');
    }

    public function testLoadReturnsNullWhenNoSessionExists(): void
    {
        $loadedSession = $this->cache->load();
        $this->assertNull($loadedSession, 'Load should return null when no session exists');
    }

    public function testCanClearSession(): void
    {
        // Save a session
        $this->cache->save($this->testSessionId);
        $this->assertNotNull($this->cache->load(), 'Session should exist after save');

        // Clear the session
        $result = $this->cache->clear();
        $this->assertTrue($result, 'Clear should return true');

        // Verify it's cleared
        $loadedSession = $this->cache->load();
        $this->assertNull($loadedSession, 'Session should be null after clear');
    }

    public function testClearReturnsSuccessWhenNoSessionExists(): void
    {
        $result = $this->cache->clear();
        $this->assertTrue($result, 'Clear should return true even when no session exists');
    }

    public function testDifferentUsersHaveDifferentCaches(): void
    {
        $cache1 = new SessionCache('user1', 'db1', 'https://server1.com:1500');
        $cache2 = new SessionCache('user2', 'db1', 'https://server1.com:1500');

        $session1 = 'session-for-user1';
        $session2 = 'session-for-user2';

        $cache1->save($session1);
        $cache2->save($session2);

        $this->assertEquals($session1, $cache1->load());
        $this->assertEquals($session2, $cache2->load());

        // Cleanup
        $cache1->clear();
        $cache2->clear();
    }

    public function testDifferentDatabasesHaveDifferentCaches(): void
    {
        $cache1 = new SessionCache('user1', 'db1', 'https://server1.com:1500');
        $cache2 = new SessionCache('user1', 'db2', 'https://server1.com:1500');

        $session1 = 'session-for-db1';
        $session2 = 'session-for-db2';

        $cache1->save($session1);
        $cache2->save($session2);

        $this->assertEquals($session1, $cache1->load());
        $this->assertEquals($session2, $cache2->load());

        // Cleanup
        $cache1->clear();
        $cache2->clear();
    }

    public function testDifferentUrlsHaveDifferentCaches(): void
    {
        $cache1 = new SessionCache('user1', 'db1', 'https://server1.com:1500');
        $cache2 = new SessionCache('user1', 'db1', 'https://server2.com:1500');

        $session1 = 'session-for-server1';
        $session2 = 'session-for-server2';

        $cache1->save($session1);
        $cache2->save($session2);

        $this->assertEquals($session1, $cache1->load());
        $this->assertEquals($session2, $cache2->load());

        // Cleanup
        $cache1->clear();
        $cache2->clear();
    }
}
