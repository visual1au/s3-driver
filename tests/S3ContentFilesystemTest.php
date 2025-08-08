<?php

namespace Statamic\S3Filesystem\Tests;

use Statamic\S3Filesystem\Filesystem\S3ContentFilesystem;
use Illuminate\Support\Facades\Storage;

class S3ContentFilesystemTest extends TestCase
{
    private S3ContentFilesystem $filesystem;
    private $mockDisk;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the S3 disk for testing
        $this->mockDisk = Storage::fake('s3-test');
        $this->filesystem = new S3ContentFilesystem();
    }

    /** @test */
    public function it_can_store_and_retrieve_content()
    {
        $path = 'collections/posts.yaml';
        $content = 'title: Posts';
        
        $this->filesystem->put($path, $content);
        
        $this->assertTrue($this->filesystem->exists($path));
        $this->assertEquals($content, $this->filesystem->get($path));
    }

    /** @test */
    public function it_can_delete_content()
    {
        $path = 'collections/posts.yaml';
        $content = 'title: Posts';
        
        $this->filesystem->put($path, $content);
        $this->assertTrue($this->filesystem->exists($path));
        
        $this->filesystem->delete($path);
        $this->assertFalse($this->filesystem->exists($path));
    }

    /** @test */
    public function it_can_list_files_in_directory()
    {
        $this->filesystem->put('collections/posts.yaml', 'title: Posts');
        $this->filesystem->put('collections/pages.yaml', 'title: Pages');
        $this->filesystem->put('collections/posts/first-post.md', '# First Post');
        
        $files = $this->filesystem->files('collections');
        
        $this->assertCount(2, $files);
        $this->assertContains('collections/posts.yaml', $files);
        $this->assertContains('collections/pages.yaml', $files);
    }

    /** @test */
    public function it_can_list_all_files_recursively()
    {
        $this->filesystem->put('collections/posts.yaml', 'title: Posts');
        $this->filesystem->put('collections/posts/first-post.md', '# First Post');
        $this->filesystem->put('collections/posts/second-post.md', '# Second Post');
        
        $files = $this->filesystem->allFiles('collections');
        
        $this->assertCount(3, $files);
        $this->assertContains('collections/posts.yaml', $files);
        $this->assertContains('collections/posts/first-post.md', $files);
        $this->assertContains('collections/posts/second-post.md', $files);
    }

    /** @test */
    public function it_handles_prefixed_paths()
    {
        $path = 'collections/posts.yaml';
        $content = 'title: Posts';
        
        $this->filesystem->put($path, $content);
        
        // The file should be stored with the configured prefix
        $this->mockDisk->assertExists('test-content/collections/posts.yaml');
    }
}