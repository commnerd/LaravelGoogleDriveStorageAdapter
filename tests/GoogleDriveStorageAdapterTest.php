<?php

namespace Tests;

use GoogleDriveStorage\GoogleDriveStorageAdapter;
use GoogleDriveStorage\GoogleDriveService;

class GoogleDriveStorageAdapterTest extends TestCase
{
    private $driveService;

    public function setUp(): void
    {
        parent::setUp();

        $this->driveService = $this->getMockBuilder(GoogleDriveService::class)
            ->disableOriginalConstructor()
            ->setMethods(["listFiles", "getFiles", "get", "create", "update", "delete"])
            ->getMock();

        $this->driveService->files = $this->driveService;

        $this->driveService
            ->method("listFiles")
            ->willReturn($this->driveService);

        $this->driveService
            ->method("get")
            ->willReturn(new TestFile("", ""));

        $this->driveService
            ->method("create")
            ->willReturn(new TestFile("a1b2c3", "dummy.txt"));

        $this->driveService
            ->method("update")
            ->willReturn(new TestFile("a1b2c3", "dummy.txt"));

        $this->driveService
            ->method("delete")
            ->willReturn(new TestFile("a1b2c3", "dummy.txt"));

        $this->adapter = new GoogleDriveStorageAdapter($this->driveService, $this->config);

    }

    public function testRootDirectory()
    {
        $this->driveService
            ->method("getFiles")
            ->willReturn([
                new TestFile("lsdkfjlksdjf", "blah"),
                new TestFile("jfsdkfjdljls", "baz"),
            ]);

        $this->assertEquals([
            "lsdkfjlksdjf" => "blah",
            "jfsdkfjdljls" => "baz",
        ], $this->adapter->directories());
    }

    public function testAllDirectories()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("lsdkfjlksdjf", "bar"),
                    new TestFile("jfsdkfjdljls", "baz"),
                    new TestFile("lskdflsldjfa", "biz"),
                ],
                [],
                [
                    new TestFile("flslkdkjflld", "boss"),
                ],
                [
                    new TestFile("ldkfjsldkfjs", "blah"),
                ],
                [],
                [
                    new TestFile("fjlskdjflskj", "faz"),
                ],
                [
                    new TestFile("lkjsdfasdffa", "fizz"),
                ],
                [
                    new TestFile("asdfjklldkjf", "foss"),
                ],
                [],
                []
            );

        $this->assertEquals([
            "lsdkfjlksdjf" => "bar",
            "jfsdkfjdljls" => "baz",
            "flslkdkjflld" => "baz/boss",
            "ldkfjsldkfjs" => "baz/boss/blah",
            "lskdflsldjfa" => "biz",
            "fjlskdjflskj" => "biz/faz",
            "lkjsdfasdffa" => "biz/faz/fizz",
            "asdfjklldkjf" => "biz/faz/fizz/foss",
        ], $this->adapter->allDirectories());
    }

    public function testAllSubDirectory()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("lskdflsldjfa", "biz"),
                ],
                [
                    new TestFile("fjlskdjflskj", "faz"),
                ],
                [
                    new TestFile("lkjsdfasdffa", "fizz"),
                ],
                [
                    new TestFile("asdfjklldkjf", "foss"),
                ],
                []
            );

        $this->assertEquals([
            "lkjsdfasdffa" => "biz/faz/fizz",
            "asdfjklldkjf" => "biz/faz/fizz/foss",
        ], $this->adapter->allDirectories("biz/faz"));
    }

    public function testSubdir()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("lskdflsldjfa", "biz"),
                ],
                [
                    new TestFile("jkl", "baz"),
                    new TestFile("pqr", "blah"),
                    new TestFile("stu", "bar"),
                ]);

        $this->assertEquals([
            "jkl" => "biz/baz",
            "pqr" => "biz/blah",
            "stu" => "biz/bar",
        ], $this->adapter->directories("biz"));
    }

    public function testRootFiles()
    {
        $this->driveService
            ->method("getFiles")
            ->willReturn([
                new TestFile("lsdkfjlksdjf", "blah.txt"),
                new TestFile("jfsdkfjdljls", "baz.txt"),
            ]);

        $this->assertEquals([
            "tuv lsdkfjlksdjf" => "blah.txt",
            "tuv jfsdkfjdljls" => "baz.txt",
        ], $this->adapter->files());
    }

    public function testAllFiles()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("123", "blah.txt"),
                    new TestFile("456", "blaz.txt"),
                ],
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [],
                [],
                [
                    new TestFile("789", "blah.txt"),
                    new TestFile("102", "blaz.txt"),
                    new TestFile("103", "bliz.txt"),
                ]
            );

        $this->assertEquals([
            "tuv 123" => "blah.txt",
            "tuv 456" => "blaz.txt",
            "def 789" => "baz/blah/blah.txt",
            "def 102" => "baz/blah/blaz.txt",
            "def 103" => "baz/blah/bliz.txt",
        ], $this->adapter->allFiles());
    }

    public function testSubdirFiles()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [
                    new TestFile("789", "blah.txt"),
                    new TestFile("102", "blaz.txt"),
                    new TestFile("103", "bliz.txt"),
                ]
            );

        $this->assertEquals([
            "def 789" => "baz/blah/blah.txt",
            "def 102" => "baz/blah/blaz.txt",
            "def 103" => "baz/blah/bliz.txt",
        ], $this->adapter->files("baz/blah"));
    }

    public function testSubdirAllFiles()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("123", "blitz.txt"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [],
                [
                    new TestFile("789", "blah.txt"),
                    new TestFile("102", "blaz.txt"),
                    new TestFile("103", "bliz.txt"),
                ]
            );

        $this->assertEquals([
            "abc 123" => "baz/blitz.txt",
            "def 789" => "baz/blah/blah.txt",
            "def 102" => "baz/blah/blaz.txt",
            "def 103" => "baz/blah/bliz.txt",
        ], $this->adapter->allFiles("baz"));
    }

    public function testExistsInRoot()
    {
        $this->driveService
        ->method('getFiles')
        ->willReturn(
            [
                new TestFile("789", "blah.txt"),
                new TestFile("102", "blaz.txt"),
                new TestFile("103", "bliz.txt"),
            ]
        );

        $this->assertTrue($this->adapter->exists("blaz.txt"));
    }

    public function testNotExistsInRoot()
    {
        $this->driveService
        ->method('getFiles')
        ->willReturn(
            [
                new TestFile("789", "blah.txt"),
                new TestFile("102", "blaz.txt"),
                new TestFile("103", "bliz.txt"),
            ]
        );

        $this->assertTrue(!$this->adapter->exists("boz.txt"));
    }

    public function testExistsInSubdir()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [
                    new TestFile("789", "blah.txt"),
                    new TestFile("102", "blaz.txt"),
                    new TestFile("103", "bliz.txt"),
                ]
            );

        $this->assertTrue($this->adapter->exists("baz/blah/blaz.txt"));
    }

    public function testNotExistsInSubdir()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [
                    new TestFile("789", "blah.txt"),
                    new TestFile("102", "blaz.txt"),
                    new TestFile("103", "bliz.txt"),
                ]
            );

        $this->assertTrue(!$this->adapter->exists("baz/blah/boz.txt"));
    }

    public function testGet()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [
                    new TestFile("102", "blaz.txt"),
                ]
            );

        $this->assertEquals("This is a test.", $this->adapter->get("baz/blah/blaz.txt"));
    }

    public function testReadStream()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [
                    new TestFile("102", "blaz.txt"),
                ]
            );

        $this->assertEquals("This is a test.", $this->adapter->readStream("baz/blah/blaz.txt"));
    }

    public function testPut()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [
                    new TestFile("102", "blaz.txt"),
                ]
            );

        $this->assertTrue($this->adapter->put("baz/blah/blaz.txt", "some information"));
    }

    public function testFailedPut()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("102", "blaz.txt"),
                ]
            );

        $this->assertTrue(!$this->adapter->put("baz/blah/blaz.txt", "some information"));
    }

    public function testWriteStream()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [
                    new TestFile("102", "blaz.txt"),
                ]
            );

        $this->assertTrue($this->adapter->writeStream("baz/blah/blaz.txt", "some information"));
    }

    public function testPrepend()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [
                    new TestFile("102", "blaz.txt"),
                ],
                [
                    new TestFile("", ""),
                ],
            );

        $this->assertTrue($this->adapter->prepend("baz/blah/blaz.txt", "some information"));
    }

    public function testAppend()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("def", "blah"),
                ],
                [
                    new TestFile("102", "blaz.txt"),
                ],
                [
                    new TestFile("", ""),
                ]
            );

        $this->assertTrue($this->adapter->append("baz/blah/blaz.txt", "some information"));
    }

    public function testDeleteSingleFile()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("abc", "baz"),
                ],
                [
                    new TestFile("102", "blah.txt"),
                ]
            );
        $this->assertTrue($this->adapter->delete("baz/blah.txt"));
    }

    public function testDeleteArrayOfFiles()
    {
        $this->driveService
            ->method('getFiles')
            ->willReturnOnConsecutiveCalls(
                [
                    new TestFile("foo.txt", "100"),
                ],
                [
                    new TestFile("baz", "abc"),
                ],
                [
                    new TestFile("102", "blah.txt"),
                ]
            );

        $this->assertTrue($this->adapter->delete([
            "foo.txt",
            "baz/blah.txt",
        ]));
    }
}