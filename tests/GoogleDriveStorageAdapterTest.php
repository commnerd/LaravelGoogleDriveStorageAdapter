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
            ->setMethods(["listFiles", "getFiles"])
            ->getMock();

        $this->driveService->files = $this->driveService;

        $this->driveService
            ->method("listFiles")
            ->willReturn($this->driveService);

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
            ->willReturn([
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
}
