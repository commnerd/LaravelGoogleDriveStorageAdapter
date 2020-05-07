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
}