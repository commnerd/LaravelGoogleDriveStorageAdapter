<?php

namespace Tests;

use GoogleDriveStorage\GoogleDriveStorageAdapter;
use GoogleDriveStorage\GoogleDriveService;
use Illuminate\Support\Facades\Cache;

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
        $fileSetRoot = [
            new TestFile("lsdkfjlksdjf", "blah"),
            new TestFile("jfsdkfjdljls", "baz"),
        ];

        $this->driveService
            ->method("getFiles")
            ->willReturn($fileSetRoot);

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
        ], $this->adapter->allDirectories("biz/faz", true));
    }
}