<?php

namespace Tests;


class TestFile {
    private $id;
    private $path;

    public function __construct($id, $path) {
        $this->id = $id;
        $this->path = $path;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->path;
    }

    public function getBody()
    {
        return "This is a test.";
    }

    public function getSize()
    {
        return sizeof($this->getBody());
    }
}